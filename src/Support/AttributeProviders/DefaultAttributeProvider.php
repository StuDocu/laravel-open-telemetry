<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Support\AttributeProviders;

use function gethostname;

class DefaultAttributeProvider implements AttributeProvider
{
    public function attributes(): array
    {
        return [
            'host.name' => gethostname(),
        ];
    }
}
