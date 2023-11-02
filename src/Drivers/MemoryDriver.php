<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Drivers;

use Spatie\OpenTelemetry\Support\Span;

use function collect;

class MemoryDriver implements Driver
{
    /** @var array<int, Span> */
    public array $sentSpans = [];

    public function sendSpans(array $spans): void
    {
        $this->sentSpans += $spans;
    }

    public function allPayloads(): array
    {
        return [
            'sentSpans' => collect($this->sentSpans)->map->toArray(),
        ];
    }
}
