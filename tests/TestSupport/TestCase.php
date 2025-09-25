<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Tests\TestSupport;

use Illuminate\Testing\TestResponse;
use OpenTelemetry\API\Common\Time\Clock;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\OpenTelemetry\Drivers\MemoryDriver;
use Spatie\OpenTelemetry\Facades\Measure;
use Spatie\OpenTelemetry\OpenTelemetryServiceProvider;
use Spatie\OpenTelemetry\Support\IdGenerator;
use Spatie\OpenTelemetry\Support\Samplers\AlwaysSampler;
use Spatie\OpenTelemetry\Support\Samplers\Sampler;
use Spatie\OpenTelemetry\Support\Stopwatch;
use Spatie\OpenTelemetry\Tests\TestSupport\TestClasses\FakeAttributeProvider;
use Spatie\OpenTelemetry\Tests\TestSupport\TestClasses\FakeClock;
use Spatie\OpenTelemetry\Tests\TestSupport\TestClasses\FakeIdGenerator;
use Spatie\OpenTelemetry\Tests\TestSupport\TestClasses\FakeStopwatch;

use function config;

class TestCase extends Orchestra
{
    protected MemoryDriver $memoryDriver;

    protected static ?TestResponse $latestResponse = null;

    protected function setUp(): void
    {
        parent::setUp();

        FakeIdGenerator::reset();
        FakeClock::reset();

        // Set up the fake clock using OpenTelemetry's Clock
        Clock::setDefault(new FakeClock);

        config()->set('open-telemetry.id_generator', FakeIdGenerator::class);
        config()->set('open-telemetry.stopwatch', FakeStopwatch::class);
        config()->set('open-telemetry.trace_attribute_providers', [FakeAttributeProvider::class]);

        $this->app->bind(IdGenerator::class, config('open-telemetry.id_generator'));
        $this->app->bind(Stopwatch::class, config('open-telemetry.stopwatch'));
        $this->app->bind(Sampler::class, AlwaysSampler::class);

        $this->memoryDriver = new MemoryDriver;

        Measure::setDriver($this->memoryDriver);
    }

    protected function getPackageProviders($app)
    {
        return [
            OpenTelemetryServiceProvider::class,
        ];
    }

    public function tempFile(string $fileName): string
    {
        return __DIR__."/temp/{$fileName}";
    }

    public function sentRequestPayloads(): array
    {
        return $this->memoryDriver->allPayloads();
    }

    public function rebindClasses()
    {
        Measure::clearResolvedInstances();
        (new OpenTelemetryServiceProvider($this->app))->bootingPackage();
    }
}
