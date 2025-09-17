<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\OpenTelemetry\Facades\Measure;
use Spatie\OpenTelemetry\Support\ParsedTraceParentHeaderValue;

class ContinueTrace
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (! $request->hasHeader('traceparent')) {
            return $next($request);
        }

        $headerValue = $request->header('traceparent');

        if (! $parsedHeader = ParsedTraceParentHeaderValue::make($headerValue)) {
            return $next($request);
        }

        Measure::setTraceId($parsedHeader->traceId);
        Measure::setParentSpanId($parsedHeader->spanId);

        return $next($request);
    }
}
