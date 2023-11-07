<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Support\Samplers;

use Illuminate\Support\Lottery;

class LotterySampler implements Sampler
{
    /** @param array{0: int, 1: int} $odds */
    public function __construct(private readonly array $odds)
    {
    }

    public function shouldSample(): bool
    {
        return Lottery::odds(...$this->odds)->choose();
    }
}
