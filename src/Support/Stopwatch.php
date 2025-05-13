<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Support;

use Carbon\Carbon;
use Carbon\CarbonInterval;

use function now;

class Stopwatch
{
    private Carbon|null $startTime = null;

    private Carbon|null $stopTime = null;

    public function start(): self
    {
        $this->startTime = now();

        return $this;
    }

    public function stop(): self
    {
        $this->stopTime = now();

        return $this;
    }

    public function startTime(): Carbon|null
    {
        return $this->startTime;
    }

    public function stopTime(): Carbon|null
    {
        return $this->stopTime;
    }

    public function elapsedTime(): CarbonInterval
    {
        if ($this->startTime === null || $this->stopTime === null) {
            return new CarbonInterval(0);
        }

        return $this->stopTime->diffAsCarbonInterval($this->startTime);
    }
}
