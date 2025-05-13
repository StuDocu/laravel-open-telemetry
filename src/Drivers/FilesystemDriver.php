<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Drivers;

use Illuminate\Support\Facades\Storage;
use Spatie\OpenTelemetry\Support\Formatters\SpanFormatter;

class FilesystemDriver implements Driver
{
    /** @param array<string, mixed> $options */
    public function __construct(private readonly SpanFormatter $spanFormatter, private readonly array $options) {}

    /** {@inheritDoc} */
    public function sendSpans(array $spans): void
    {
        $payload = $this->spanFormatter->format($spans);

        Storage::disk($this->options['disk'] ?? 'local')->put($this->options['path'] ?? 'opentelemetry.json', $payload);
    }
}
