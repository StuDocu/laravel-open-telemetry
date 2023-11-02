<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Support;

use Spatie\OpenTelemetry\Support\AttributeProviders\AttributeProvider;

use function app;
use function collect;
use function config;

class Trace
{
    /** @var array<string, mixed> */
    protected array $attributes = [];
    
    public static function start(string|null $id = null, string $name = ''): self
    {
        return new self($id, $name, config('open-telemetry.trace_attribute_providers'));
    }

    /** @param  array<AttributeProvider> $attributeProviders */
    public function __construct(
        protected string|null $id,
        protected string|null $name,
        array $attributeProviders,
    ) {
        $this->id ??= app(IdGenerator::class)->traceId();

        $this->attributes = collect($attributeProviders)
            ->map(static fn (string $attributeProvider) => app($attributeProvider))
            ->flatMap(static fn (AttributeProvider $attributeProvider) => $attributeProvider->attributes())
            ->toArray();
    }

    public function setId(string $traceId): void
    {
        $this->id = $traceId;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
