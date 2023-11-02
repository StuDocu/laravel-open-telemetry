<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Drivers;

class MultiDriver implements Driver
{
    /** @var array<int, Driver> */
    protected array $drivers = [];

    public function addDriver(Driver $driver): self
    {
        $this->drivers[] = $driver;

        return $this;
    }

    public function sendSpans(array $spans): void
    {
        foreach ($this->drivers as $driver) {
            $driver->sendSpans($spans);
        }
    }
}
