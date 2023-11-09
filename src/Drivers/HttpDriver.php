<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Drivers;

use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;
use Spatie\OpenTelemetry\Support\Formatters\SpanFormatter;

use function array_key_exists;

class HttpDriver implements Driver
{
    /** @param array{headers?: array<string, string>, basic_auth?: array{username: string, password: string}, url: string} $options */
    public function __construct(private readonly SpanFormatter $spanFormatter, private readonly array $options)
    {
    }

    public function sendSpans(array $spans): void
    {
        $payload = $this->spanFormatter->format($spans);

        Http::asJson()
            ->withBody($payload, 'application/json')
            ->withHeaders($this->options['headers'] ?? [])
            ->when(array_key_exists('basic_auth', $this->options), function (Factory $client): void {
                $client->withBasicAuth(
                    $this->options['basic_auth']['username'],
                    $this->options['basic_auth']['password'],
                );
            })
            ->post($this->options['url']);
    }
}
