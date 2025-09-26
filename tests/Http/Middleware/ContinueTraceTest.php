<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Spatie\OpenTelemetry\Facades\Measure;
use Spatie\OpenTelemetry\Http\Middleware\ContinueTrace;
use Spatie\OpenTelemetry\Support\Formatters\SpanFormatter;

beforeEach(function () {
    Route::any('test-route', function () {
        if (! Measure::hasTraceId()) {
            return response()->json(['error' => 'did not start trace']);
        }

        // Trace some example operations
        Measure::start('database-query');

        Measure::start('cache-operation');
        Measure::stop('cache-operation', attributes: ['operation' => 'get', 'key' => 'user:123']);

        Measure::stop('database-query', attributes: ['query' => 'SELECT * FROM users']);

        Measure::send();

        // Get the raw formatted span data that would be sent to the telemetry server
        $spans = $this->sentRequestPayloads();
        $formatter = app(SpanFormatter::class);

        return response()->json(json_decode($formatter->format($spans), true));
    })->middleware(ContinueTrace::class);
});

it('will continue a trace when the traceparent header is set to a valid value', function () {
    $traceParentHeader = '00-80e1afed08e019fc1110464cfa66635c-7a085853722dc6d2-01';
    $expectedTraceId = '80e1afed08e019fc1110464cfa66635c';
    $parentSpanId = '7a085853722dc6d2';

    $response = $this->post('test-route', headers: ['traceparent' => $traceParentHeader]);

    $responseData = $response->json();

    // Verify we have the OpenTelemetry HTTP JSON format structure
    expect($responseData)->toBeArray();
    expect($responseData)->toHaveKey('resourceSpans');

    // Navigate to the spans in the OpenTelemetry structure
    $resourceSpans = $responseData['resourceSpans'][0];
    $spans = $resourceSpans['scopeSpans'][0]['spans'];

    expect($spans)->toHaveCount(2);

    // Verify all spans have the same trace ID from the traceparent header
    foreach ($spans as $span) {
        expect($span['traceId'])->toBe($expectedTraceId);
    }

    // First span should be cache-operation (nested span, finished first)
    expect($spans[0]['name'])->toBe('cache-operation');
    expect($spans[0]['traceId'])->toBe($expectedTraceId);
    expect($spans[0]['parentSpanId'])->toBe('0'); // Parent is the database-query span with ID "0"

    // Find the operation attribute
    $operationAttr = collect($spans[0]['attributes'])->firstWhere('key', 'operation');
    expect($operationAttr['value']['stringValue'])->toBe('get');

    // Find the key attribute
    $keyAttr = collect($spans[0]['attributes'])->firstWhere('key', 'key');
    expect($keyAttr['value']['stringValue'])->toBe('user:123');

    // Second span should be database-query (parent span)
    expect($spans[1]['name'])->toBe('database-query');
    expect($spans[1]['traceId'])->toBe($expectedTraceId);
    expect($spans[1]['spanId'])->toBe('0'); // This span gets ID "0" from the fake ID generator
    // According to W3C traceparent standard, this span should inherit the parent span ID from the traceparent header
    expect($spans[1]['parentSpanId'])->toBe($parentSpanId); // Should be the span ID from traceparent header

    $queryAttr = collect($spans[1]['attributes'])->firstWhere('key', 'query');
    expect($queryAttr['value']['stringValue'])->toBe('SELECT * FROM users');
});

it('will not continue a trace when the traceparent header is set to a invalid value', function () {
    $response = $this->post('test-route', headers: ['traceparent' => '00-80e1afed08e019fc1110464cfa66635cxxx-7a085853722dc6d2-01']);

    $responseData = $response->json();

    expect($responseData['error'])->toBe('did not start trace');
});
