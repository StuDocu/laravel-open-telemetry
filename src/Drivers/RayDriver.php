<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Drivers;

use Illuminate\Support\Facades\Http;

use function collect;

class RayDriver extends HttpDriver
{
    protected array $options = [];

    public function sendSpans(array $spans): void
    {
        $payload = collect($spans)->map->toArray();

        Http::asJson()->post($this->options['url'] ?? 'http://localhost:23517/otel', $payload);
    }
}
