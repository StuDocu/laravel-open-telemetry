<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry;

use Illuminate\Http\Client\PendingRequest;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\OpenTelemetry\Actions\MakeQueueTraceAwareAction;
use Spatie\OpenTelemetry\Drivers\Driver;
use Spatie\OpenTelemetry\Drivers\MultiDriver;
use Spatie\OpenTelemetry\Support\IdGenerator;
use Spatie\OpenTelemetry\Support\Measure;
use Spatie\OpenTelemetry\Support\Samplers\Sampler;
use Spatie\OpenTelemetry\Support\Stopwatch;
use Spatie\OpenTelemetry\Watchers\Watcher;

use function collect;
use function config;
use function sprintf;

class OpenTelemetryServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-open-telemetry')
            ->hasConfigFile()
            ->hasInstallCommand(static function (InstallCommand $command): void {
                $command
                    ->publishConfigFile()
                    ->copyAndRegisterServiceProviderInApp();
            });
    }

    public function bootingPackage(): void
    {
        $this->app->singleton(Sampler::class, function () {
            $sampler = config('open-telemetry.sampler');

            return $this->app->make($sampler);
        });

        $this->app->bind(IdGenerator::class, config('open-telemetry.id_generator'));
        $this->app->bind(Stopwatch::class, config('open-telemetry.stopwatch'));

        $this->app->singleton(Measure::class, function () {
            $shouldSample = $this->app->make(Sampler::class)->shouldSample();
            $configuredMultiDriver = $this->getMultiDriver();

            return new Measure($configuredMultiDriver, $shouldSample);
        });

        if (config('open-telemetry.queue.make_queue_trace_aware')) {
            /** @var MakeQueueTraceAwareAction $action */
            $action = $this->app->make(config('open-telemetry.actions.make_queue_trace_aware'));

            $action->execute();
        }

        /** @var class-string<Watcher> $watcher */
        foreach (config('open-telemetry.watchers') as $watcher) {
            $this->app->make($watcher)->register($this->app);
        }

        $this->addWithTraceMacro();
    }

    protected function getMultiDriver(): MultiDriver
    {
        $multiDriver = new MultiDriver;

        /** @var array<class-string<Driver>, array<string, scalar>> $drivers */
        $drivers = config('open-telemetry.drivers');

        collect($drivers)
            ->map(function ($value, string $key) {
                $driverClass = $key;
                $config = $value;

                return $this->app->make($driverClass, ['options' => $config]);
            })
            ->each(static fn (Driver $driver) => $multiDriver->addDriver($driver));

        return $multiDriver;
    }

    protected function addWithTraceMacro(): self
    {
        PendingRequest::macro('withTrace', function () {
            if ($span = app(Measure::class)->currentSpan()) {
                $headers['traceparent'] = sprintf(
                    '%s-%s-%s-%02x',
                    '00',
                    $span->trace->id(),
                    $span->id,
                    $span->flags(),
                );

                /** @var PendingRequest $this */
                $this->withHeaders($headers);
            }

            return $this;
        });

        return $this;
    }
}
