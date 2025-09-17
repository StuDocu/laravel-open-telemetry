<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Spatie\OpenTelemetry\Support\Samplers\AlwaysSampler;
use Spatie\OpenTelemetry\Support\Samplers\LotterySampler;
use Spatie\OpenTelemetry\Support\Samplers\NeverSampler;
use Spatie\OpenTelemetry\Support\Samplers\TraceparentHeaderSampler;

test('the AlwaysSampler always returns true', function () {
    expect(app(AlwaysSampler::class)->shouldSample())->toBe(true);
});

test('the NeverSampler always returns false', function () {
    expect(app(NeverSampler::class)->shouldSample())->toBe(false);
});

test('the LotterySampler returns a boolean', function () {
    expect(new LotterySampler([50, 50]))->shouldSample()->toBeBool();
});

test('the TraceparentHeaderSampler returns false when no request is available', function () {
    $sampler = new TraceparentHeaderSampler(null);

    expect($sampler->shouldSample())->toBe(false);
});

test('the TraceparentHeaderSampler sampling behavior', function (?string $traceparentHeader, bool $expectedResult) {
    $request = Request::create('/test');

    if ($traceparentHeader !== null) {
        $request->headers->set('traceparent', $traceparentHeader);
    }

    $sampler = new TraceparentHeaderSampler($request);

    expect($sampler->shouldSample())->toBe($expectedResult);
})->with([
    'no traceparent header' => [null, false],
    'invalid traceparent header' => ['invalid-header', false],
    'wrong version' => ['01-80e1afed08e019fc1110464cfa66635c-7a085853722dc6d2-01', false],
    'invalid trace id' => ['00-invalid_trace_id-7a085853722dc6d2-01', false],
    'invalid span id' => ['00-80e1afed08e019fc1110464cfa66635c-invalid_span_id-01', false],
    'too few segments' => ['00-80e1afed08e019fc1110464cfa66635c', false],
    'too many segments' => ['00-80e1afed08e019fc1110464cfa66635c-7a085853722dc6d2-01-extra', false],
    'valid traceparent header' => ['00-80e1afed08e019fc1110464cfa66635c-7a085853722dc6d2-01', true],
]);
