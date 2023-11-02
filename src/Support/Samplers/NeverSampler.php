<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Support\Samplers;

class NeverSampler implements Sampler
{
    public function shouldSample(): bool
    {
        return false;
    }
}
