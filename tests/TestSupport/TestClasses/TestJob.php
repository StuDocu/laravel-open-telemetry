<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Tests\TestSupport\TestClasses;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Spatie\OpenTelemetry\Facades\Measure;
use Spatie\Valuestore\Valuestore;

class TestJob implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(public Valuestore $valuestore) {}

    public function handle()
    {
        $this->valuestore->put('traceIdInPayload', $this->job->payload()['traceId'] ?? null);
        $this->valuestore->put('activeTraceIdInJob', Measure::traceId());
        $this->valuestore->put('startedSpansInJob', Measure::startedSpanNames());
    }
}
