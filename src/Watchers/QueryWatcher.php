<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Watchers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenTelemetry\SemConv\TraceAttributes;
use Spatie\OpenTelemetry\Facades\Measure;

class QueryWatcher extends Watcher
{
    public function register(Application $app): void
    {
        DB::listen(static function (QueryExecuted $query): void {
            $operationName = Str::upper(Str::before($query->sql, ' '));
            $queryTimeInNs = (int) ($query->time * 1_000_000);

            $attributes = [
                TraceAttributes::DB_SYSTEM => $query->connection->getDriverName(),
                TraceAttributes::DB_NAME => $query->connection->getDatabaseName(),
                TraceAttributes::DB_OPERATION => $operationName,
                TraceAttributes::DB_USER => $query->connection->getConfig('username'),
                TraceAttributes::DB_STATEMENT => $query->sql,
            ];

            Measure::manual('database query - '.$operationName, $queryTimeInNs, $attributes);
        });
    }
}
