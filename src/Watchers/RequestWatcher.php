<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Watchers;

use Illuminate\Foundation\Application;
use Spatie\OpenTelemetry\Facades\Measure;

use function now;

class RequestWatcher extends Watcher
{
    public function register(Application $app): void
    {
        Measure::start('request');

        $app->terminating(static function (): void {
            $start = (int) LARAVEL_START * 1_000_000;

            $duration = now()->getPreciseTimestamp() - $start;

            Measure::stop('request', attributes: [
                'timestamp' => $start,
                'duration' => $duration,
            ]);
        });
    }
}
