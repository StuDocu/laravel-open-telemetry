<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Support;

use OpenTelemetry\SDK\Common\Time\ClockFactory;
use Spatie\OpenTelemetry\Support\AttributeProviders\AttributeProvider;

use function app;
use function array_merge;
use function collect;

class Span
{
    public readonly string $id;

    private int|null $endTime = null;

    /**
     * @param array<AttributeProvider> $attributeProviders
     * @param array<string, scalar>    $attributes         Custom key-value attributes to be attached to the span.
     */
    public function __construct(
        public readonly string $name,
        public readonly Trace $trace,
        public readonly array $attributeProviders,
        public readonly Span|null $parentSpan = null,
        private int|null $startTime = null,
        private array $attributes = [],
    ) {
        $this->startTime = $startTime ?? ClockFactory::getDefault()->now();

        $this->id = app(IdGenerator::class)->spanId();

        $this->attributes = collect($this->attributeProviders)
            ->map(static fn (string $attributeProvider) => app($attributeProvider))
            ->flatMap(static fn (AttributeProvider $attributeProvider) => $attributeProvider->attributes())
            ->toArray();
    }

    public function flags(): int
    {
        return 0x01;
    }

    /** @param array<string, scalar> $attributes */
    public function stop(array $attributes = []): self
    {
        $this->endTime = ClockFactory::getDefault()->now();

        $this->attributes = array_merge($this->attributes, $attributes);

        return $this;
    }

    /** @param array<string, scalar> $attributes */
    public function attributes(array $attributes): self
    {
        $this->attributes = array_merge($this->attributes, $attributes);

        return $this;
    }

    /** @return array<string, scalar> */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getStartTime(): int|null
    {
        return $this->startTime;
    }

    public function getEndTime(): int|null
    {
        return $this->endTime;
    }
}
