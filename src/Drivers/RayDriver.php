<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Drivers;

use Illuminate\Support\Facades\Http;

class RayDriver extends HttpDriver
{
    public function sendSpans(array $spans): void
    {
        $payload = $this->spanFormatter->format($spans);

        Http::asJson()->post($this->options['url'] ?? 'http://localhost:23517/otel', json_decode($payload));
    }
}
