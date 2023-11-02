<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Support\Samplers;

use Illuminate\Support\Lottery;

class LotterySampler implements Sampler
{
    public function shouldSample(): bool
    {
        return Lottery::odds(2, 100)->choose();
    }
}
