<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Support;

use Spatie\OpenTelemetry\Drivers\Driver;

use function array_keys;
use function array_merge;
use function config;
use function now;

class Measure
{
    protected Trace|null $trace = null;

    protected Span|null $parentSpan = null;

    /** @var array<string, Span> */
    protected array $startedSpans = [];

    /** @var list<Span> */
    protected array $spansToSend = [];

    public function __construct(private Driver $driver, private readonly bool $shouldSample = true)
    {
        $this->startTrace();
    }

    public function startTrace(): self
    {
        if (! $this->shouldSample) {
            return $this;
        }

        $traceName = config('open-telemetry.default_trace_name') ?? config('app.name');

        $this->trace = Trace::start(name: $traceName);

        return $this;
    }

    public function traceId(): string|null
    {
        return $this->trace?->id();
    }

    public function hasTraceId(): bool
    {
        $traceId = $this->traceId();

        if ($traceId === null) {
            return false;
        }

        return $traceId !== '0';
    }

    public function setTraceId(string $traceId): self
    {
        if (! $this->trace) {
            return $this;
        }

        $this->trace->setId($traceId);

        return $this;
    }

    public function setDriver(Driver $driver): self
    {
        $this->driver = $driver;

        return $this;
    }

    public function trace(): Trace|null
    {
        return $this->trace;
    }

    public function start(string $name, array $mergeProperties = []): Span|null
    {
        if (! $this->shouldSample) {
            return null;
        }

        $span = new Span(
            $name,
            $this->trace,
            config('open-telemetry.span_attribute_providers'),
            $this->parentSpan,
            $mergeProperties,
        );

        $this->startedSpans[$name] = $span;

        $this->parentSpan = $span;

        return $span;
    }

    public function getSpan(string $name): Span|null
    {
        return $this->startedSpans[$name] ?? null;
    }

    public function currentSpan(): Span|null
    {
        return $this->parentSpan;
    }

    public function startedSpanNames(): array
    {
        return array_keys($this->startedSpans);
    }

    public function stop(string $name, array $mergeProperties = []): Span|null
    {
        if (! $this->shouldSample) {
            return null;
        }

        $span = $this->startedSpans[$name] ?? null;

        if (! $span) {
            return null;
        }

        $span->stop($mergeProperties);

        unset($this->startedSpans[$name]);
        $this->parentSpan = $span->parentSpan();

        $this->spansToSend[] = $span;

        return $span;
    }

    public function send(): bool
    {
        if (! $this->shouldSample) {
            return false;
        }

        $this->driver->sendSpans($this->spansToSend);

        return true;
    }

    public function manual(string $name, float $durationInMs, array $mergeProperties = []): void
    {
        $this->start($name);

        $endTime = now()->getPreciseTimestamp();

        $durationInMicroseconds = $durationInMs * 1000;

        $startTime = $endTime - $durationInMicroseconds;

        $mergeProperties = array_merge([
            'timestamp' => $startTime,
            'duration' => $durationInMicroseconds,
        ], $mergeProperties);

        $this->stop($name, $mergeProperties);
    }
}
