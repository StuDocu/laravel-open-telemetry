<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\OpenTelemetry\Facades\Measure;

class MeasureRequest
{
    public function handle(Request $request, Closure $next): mixed
    {
        Measure::start('request');

        return $next($request);
    }

    public function terminate(Request $request, mixed $response): void
    {
        Measure::stop('request');
    }
}
