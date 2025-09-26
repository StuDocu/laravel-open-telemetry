<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Support;

use OpenTelemetry\API\Common\Time\Clock;
use Spatie\OpenTelemetry\Support\AttributeProviders\AttributeProvider;

use function app;
use function array_merge;
use function collect;

/**
 * @phpstan-import-type AttributesArray from AttributeProvider
 */
class Span
{
    public readonly string $id;

    private ?int $endTime = null;

    /**
     * @param  array<class-string<AttributeProvider>>  $attributeProviders
     * @param  array<string, scalar>  $attributes  Custom key-value attributes to be attached to the span.
     */
    public function __construct(
        public readonly string $name,
        public readonly Trace $trace,
        public readonly array $attributeProviders,
        public readonly ?Span $parentSpan = null,
        private ?int $startTime = null,
        private array $attributes = [],
        ?string $spanId = null,
    ) {
        $this->startTime = $startTime ?? Clock::getDefault()->now();

        $this->id = $spanId ?? app(IdGenerator::class)->spanId();

        $this->attributes = array_merge(collect($this->attributeProviders)
            ->map(static fn (string $attributeProvider): AttributeProvider => app($attributeProvider))
            ->flatMap(static fn (AttributeProvider $attributeProvider) => $attributeProvider->attributes())
            ->toArray(), $attributes);
    }

    public function flags(): int
    {
        return 0x01;
    }

    /** @param array<string, scalar> $attributes */
    public function stop(?int $stopTime = null, array $attributes = []): self
    {
        $this->endTime = $stopTime ?? Clock::getDefault()->now();

        $this->attributes = array_merge($this->attributes, $attributes);

        return $this;
    }

    /** @param AttributesArray $attributes */
    public function attributes(array $attributes): self
    {
        $this->attributes = array_merge($this->attributes, $attributes);

        return $this;
    }

    /** @return AttributesArray */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getStartTime(): ?int
    {
        return $this->startTime;
    }

    public function getEndTime(): ?int
    {
        return $this->endTime;
    }
}
