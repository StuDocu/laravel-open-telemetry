<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Support;

use Carbon\Carbon;
use Carbon\CarbonInterval;

use function now;

class Stopwatch
{
    private ?Carbon $startTime = null;

    private ?Carbon $stopTime = null;

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

    public function startTime(): ?Carbon
    {
        return $this->startTime;
    }

    public function stopTime(): ?Carbon
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
