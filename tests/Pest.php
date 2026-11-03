<?php

declare(strict_types=1);

use Spatie\OpenTelemetry\Support\Span;
use Spatie\OpenTelemetry\Tests\TestSupport\TestCase;

uses(TestCase::class)->in(__DIR__);

/**
 * Turns sent spans into JSON that only contains what their public API exposes.
 *
 * Dumping the span objects directly depends on how the snapshot libraries treat protected and private properties,
 * which differs between their versions.
 *
 * @param  array<Span>  $spans
 */
function snapshotablePayloads(array $spans): string
{
    return json_encode(array_map(static fn (Span $span): array => [
        'id' => $span->id,
        'name' => $span->name,
        'startTime' => $span->getStartTime(),
        'endTime' => $span->getEndTime(),
        'attributes' => $span->getAttributes(),
        'trace' => [
            'id' => $span->trace->id(),
            'name' => $span->trace->getName(),
            'attributes' => $span->trace->getAttributes(),
        ],
        'parentSpanId' => $span->parentSpan?->id,
    ], $spans), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
}
