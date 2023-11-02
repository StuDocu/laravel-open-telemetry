<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Facades;

use Illuminate\Support\Facades\Facade;

/** @mixin \Spatie\OpenTelemetry\Support\Measure */
class Measure extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Spatie\OpenTelemetry\Support\Measure::class;
    }
}
