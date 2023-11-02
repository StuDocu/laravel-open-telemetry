<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Drivers;

use Spatie\OpenTelemetry\Support\Span;

class MemoryDriver implements Driver
{
    /** @var array<int, Span> */
    public array $sentSpans = [];

    public function sendSpans(array $spans): void
    {
        $this->sentSpans += $spans;
    }

    /** @return array<int, Span> */
    public function allPayloads(): array
    {
        return $this->sentSpans;
    }
}
