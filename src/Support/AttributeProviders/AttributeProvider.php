<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Support\AttributeProviders;

/**
 * @phpstan-type AttributesArray array<string, mixed>
 */
interface AttributeProvider
{
    /** @return AttributesArray */
    public function attributes(): array;
}
