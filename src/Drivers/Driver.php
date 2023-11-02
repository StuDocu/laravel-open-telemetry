<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Drivers;

use Spatie\OpenTelemetry\Support\Span;

interface Driver
{
    /** @param list<Span> $spans */
    public function sendSpans(array $spans): void;
}
