<?php

namespace Spatie\OpenTelemetry\Watchers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Spatie\OpenTelemetry\Facades\Measure;

class QueryWatcher extends Watcher
{
    public function register(Application $app)
    {
        DB::listen(function (QueryExecuted $query) {
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
