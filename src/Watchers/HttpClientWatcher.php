<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Watchers;

use Illuminate\Foundation\Application;
use Illuminate\Http\Client\Events\RequestSending;
use Illuminate\Http\Client\Events\ResponseReceived;
use Illuminate\Support\Facades\Event;

class HttpClientWatcher extends Watcher
{
    public function register(Application $app): void
    {
        Event::listen(RequestSending::class, static function (RequestSending $event): void {
            // to do implement
        });

        Event::listen(ResponseReceived::class, static function (ResponseReceived $event): void {
            // to do implement
        });
    }
}
