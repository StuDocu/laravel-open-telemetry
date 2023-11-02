<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Support\AttributeProviders;

interface AttributeProvider
{
    /** @return array<string, mixed> */
    public function attributes(): array;
}
