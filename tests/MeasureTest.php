<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Spatie\OpenTelemetry\Facades\Measure;
use Spatie\OpenTelemetry\Support\Samplers\NeverSampler;
use Spatie\OpenTelemetry\Tests\TestSupport\TestClasses\FakeClock;
use Spatie\OpenTelemetry\Tests\TestSupport\TestClasses\FakeIdGenerator;
use Spatie\TestTime\TestTime;

use function Spatie\Snapshots\assertMatchesSnapshot;

beforeEach(function () {
    TestTime::freeze('Y-m-d H:i:s', '2022-01-01 00:00:00');

    Http::fake();
});

it('can measure a single span', function () {
    FakeClock::setTimestampQueue([
        1747753696266177483, // first start
        1747753696266257650, // first end
    ]);

    Measure::start('first');

    TestTime::addSecond();

    Measure::stop('first');

    Measure::send();

    $payloads = $this->sentRequestPayloads();

    assertMatchesSnapshot($payloads);
});

it('can measure multiple spans', function () {
    FakeIdGenerator::reset();
    FakeClock::setTimestampQueue([
        1747753696269910233, // first start
        1747753696269969150, // first end
        1747753696269972900, // second start
        1747753696270018650, // second end
    ]);

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
    FakeClock::setTimestampQueue([
        1747753696242089317, // parent start
        1747753696242241900, // child start  
        1747753696242295775, // child end
        1747753696242331692, // parent end
    ]);

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

    expect($payloads)->toHaveCount(0);
});
