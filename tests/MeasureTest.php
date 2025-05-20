<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Spatie\OpenTelemetry\Facades\Measure;
use Spatie\OpenTelemetry\Support\Samplers\NeverSampler;
use Spatie\OpenTelemetry\Tests\TestSupport\TestClasses\FakeIdGenerator;
use Spatie\TestTime\TestTime;

use function Spatie\Snapshots\assertMatchesSnapshot;

beforeEach(function () {
    TestTime::freeze('Y-m-d H:i:s', '2022-01-01 00:00:00');

    Http::fake();
});

it('can measure a single span', function () {
    Measure::start('first');

    TestTime::addSecond();

    Measure::stop('first');

    Measure::send();

    $payloads = $this->sentRequestPayloads();

    assertMatchesSnapshot($payloads);
});

it('can measure multiple spans', function () {
    FakeIdGenerator::reset();

    Measure::start('first');

    TestTime::addSecond();

    Measure::stop('first');

    Measure::start('second');

    TestTime::addSecond();

    Measure::stop('second');

    Measure::send();

    $payloads = $this->sentRequestPayloads();

    assertMatchesSnapshot($payloads);
});

it('can measure nested spans', function () {
    Measure::start('parent');

    TestTime::addSecond();

    Measure::start('child');

    TestTime::addSecond();

    Measure::stop('child');

    TestTime::addSecond();

    Measure::stop('parent');

    Measure::send();

    $payloads = $this->sentRequestPayloads();

    assertMatchesSnapshot($payloads);
});

it('will not send any payloads when we are not sampling', function () {
    config()->set('open-telemetry.sampler', NeverSampler::class);

    $this->rebindClasses();

    Measure::start('my-measure');

    Measure::stop('my-measure');

    Measure::send();

    $payloads = $this->sentRequestPayloads();

    expect($payloads['sentSpans'])->toHaveCount(0);
});
