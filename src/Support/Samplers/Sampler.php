<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Support\Samplers;

interface Sampler
{
    public function shouldSample(): bool;
}
