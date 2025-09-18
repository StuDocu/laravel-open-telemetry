<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Support;

use OpenTelemetry\API\Common\Time\Clock;
use Spatie\OpenTelemetry\Drivers\Driver;

use function array_keys;
use function config;

class Measure
{
    protected ?Trace $trace = null;

    protected ?Span $parentSpan = null;

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

    public function traceId(): ?string
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

    public function setParentSpanId(string $parentSpanId): self
    {
        if (! $this->trace) {
            return $this;
        }

        // Create a remote parent span with the specific ID from the traceparent header
        $this->parentSpan = new Span(
            name: 'remote-parent', // This name is not used, but it's required by the Span constructor
            trace: $this->trace,
            attributeProviders: [],
            spanId: $parentSpanId
        );

        return $this;
    }

    public function setDriver(Driver $driver): self
    {
        $this->driver = $driver;

        return $this;
    }

    public function trace(): ?Trace
    {
        return $this->trace;
    }

    /** @param array<string, mixed> $attributes */
    public function start(string $name, ?int $starTime = null, array $attributes = []): ?Span
    {
        if (! $this->shouldSample || $this->trace === null) {
            return null;
        }

        $span = new Span(
            $name,
            $this->trace,
            config('open-telemetry.span_attribute_providers'),
            $this->parentSpan,
            $starTime,
            $attributes,
        );

        $this->startedSpans[$name] = $span;

        $this->parentSpan = $span;

        return $span;
    }

    public function getSpan(string $name): ?Span
    {
        return $this->startedSpans[$name] ?? null;
    }

    public function currentSpan(): ?Span
    {
        return $this->parentSpan;
    }

    /**
     * @return array<string>
     */
    public function startedSpanNames(): array
    {
        return array_keys($this->startedSpans);
    }

    /** @param array<string, mixed> $attributes */
    public function stop(string $name, ?int $stopTime = null, array $attributes = []): ?Span
    {
        if (! $this->shouldSample) {
            return null;
        }

        $span = $this->startedSpans[$name] ?? null;

        if (! $span) {
            return null;
        }

        $span->stop($stopTime, $attributes);

        unset($this->startedSpans[$name]);
        $this->parentSpan = $span->parentSpan;

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

    /** @param array<string, mixed> $attributes */
    public function manual(string $name, int $durationInNs, array $attributes = []): void
    {
        $nowInNs = Clock::getDefault()->now();

        $this->start($name, $nowInNs - $durationInNs, $attributes);

        $this->stop($name, $nowInNs);
    }

    /** @param array<string, mixed> $attributes */
    public function manualStartAndEnd(string $name, int $startTime, int $endTime, array $attributes = []): void
    {
        $this->start($name, $startTime, $attributes);

        $this->stop($name, $endTime);
    }
}
