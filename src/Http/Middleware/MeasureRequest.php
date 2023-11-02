<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\OpenTelemetry\Facades\Measure;

class MeasureRequest
{
    public function handle(Request $request, Closure $next)
    {
        Measure::start('request');

        return $next($request);
    }

    public function terminate($request, $response): void
    {
        Measure::stop('request');
    }
}
