<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Support;

use Spatie\OpenTelemetry\Support\AttributeProviders\AttributeProvider;

use function app;
use function collect;
use function config;

/**
 * @phpstan-import-type AttributesArray from AttributeProvider
 */
class Trace
{
    /** @var AttributesArray */
    protected array $attributes = [];

    protected string $id;

    public static function start(?string $id = null, string $name = ''): self
    {
        return new self($id, $name, config('open-telemetry.trace_attribute_providers'));
    }

    /** @param  array<AttributeProvider|class-string<AttributeProvider>> $attributeProviders */
    public function __construct(
        ?string $id,
        protected ?string $name,
        array $attributeProviders,
    ) {
        $this->id = $id ?? app(IdGenerator::class)->traceId();

        $this->attributes = collect($attributeProviders)
            ->map(static fn (AttributeProvider|string $attributeProvider) => is_string($attributeProvider) ? app($attributeProvider) : $attributeProvider)
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

    public function getName(): ?string
    {
        return $this->name;
    }

    /** @return AttributesArray */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
