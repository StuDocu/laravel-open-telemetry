<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Watchers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Spatie\OpenTelemetry\Facades\Measure;

class QueryWatcher extends Watcher
{
    public function register(Application $app): void
    {
        DB::listen(static function (QueryExecuted $query): void {
            $queryTimeInMs = $query->time;

            Measure::manual('query', $queryTimeInMs, [
                'attributes' => [
                    'query' => $query->sql,
                ],
                'description' => $query->sql,
                'otel.status.description' => 'query executed',
            ]);
        });
    }
}
