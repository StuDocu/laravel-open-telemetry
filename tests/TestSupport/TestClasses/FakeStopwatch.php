<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Tests\TestSupport\TestClasses;

use Carbon\Carbon;
use Spatie\OpenTelemetry\Support\Stopwatch;

class FakeStopwatch extends Stopwatch
{
    public function start(): self
    {
        $this->startTime = Carbon::now();

        return $this;
    }

    public function stop(): self
    {
        $this->stopTime = Carbon::now();

        return $this;
    }
}
