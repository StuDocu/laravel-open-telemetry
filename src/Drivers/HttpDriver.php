<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Drivers;

use Illuminate\Support\Facades\Http;
use Spatie\OpenTelemetry\Support\Formatters\SpanFormatter;

class HttpDriver implements Driver
{
    /** @param array{headers?: array<string, string>, basic_auth?: array{username: string, password: string}, url: string} $options */
    public function __construct(
        protected readonly SpanFormatter $spanFormatter,
        protected readonly array $options,
    ) {}

    public function sendSpans(array $spans): void
    {
        $payload = $this->spanFormatter->format($spans);

        $basicAuth = $this->options['basic_auth'] ?? null;

        Http::asJson()
            ->withBody($payload, 'application/json')
            ->withHeaders($this->options['headers'] ?? [])
            ->when(
                $basicAuth,
                function (\Illuminate\Http\Client\PendingRequest $client) use ($basicAuth) {
                    /** @var array{username: string, password: string} $basicAuth */
                    $client->withBasicAuth(
                        $basicAuth['username'],
                        $basicAuth['password'],
                    );
                }
            )
            ->post($this->options['url']);
    }
}
