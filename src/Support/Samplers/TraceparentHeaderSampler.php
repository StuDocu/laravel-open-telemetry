<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Support\Samplers;

use Illuminate\Http\Request;
use Spatie\OpenTelemetry\Support\ParsedTraceParentHeaderValue;

class TraceparentHeaderSampler implements Sampler
{
    public function __construct(
        private readonly ?Request $request = null
    ) {}

    public function shouldSample(): bool
    {
        if (! $this->request) {
            return false;
        }

        if (! $this->request->hasHeader('traceparent')) {
            return false;
        }

        $headerValue = $this->request->header('traceparent');

        // Validate that the traceparent header is properly formatted and valid
        return ParsedTraceParentHeaderValue::make($headerValue) !== null;
    }
}
