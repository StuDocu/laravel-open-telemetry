<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Watchers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenTelemetry\SemConv\Attributes\DbAttributes;
use Spatie\OpenTelemetry\Facades\Measure;

class QueryWatcher extends Watcher
{
    public function register(Application $app): void
    {
        DB::listen(static function (QueryExecuted $query): void {
            $operationName = Str::upper(Str::before($query->sql, ' '));
            $queryTimeInNs = (int) ($query->time * 1_000_000);

            $attributes = [
                DbAttributes::DB_SYSTEM_NAME => $query->connection->getDriverName(),
                DbAttributes::DB_NAMESPACE => $query->connection->getDatabaseName(),
                DbAttributes::DB_OPERATION_NAME => $operationName,
                DbAttributes::DB_QUERY_TEXT => $query->sql,
            ];

            Measure::manual('database query - '.$operationName, $queryTimeInNs, $attributes);
        });
    }
}
