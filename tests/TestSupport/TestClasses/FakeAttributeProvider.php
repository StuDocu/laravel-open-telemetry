<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Tests\TestSupport\TestClasses;

use Spatie\OpenTelemetry\Support\AttributeProviders\AttributeProvider;

class FakeAttributeProvider implements AttributeProvider
{
    public function attributes(): array
    {
        return [
            'host.name' => 'static.host.name.for.tests',
        ];
    }
}
