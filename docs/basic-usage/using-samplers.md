---
title: Using samplers
weight: 5
---

By default, the package will start traces in every request handled by your Laravel app. In production, you'll likely don't want this as it might hurt performance a little.

Instead of measuring every request, you can measure only a small portion of requests using a sampler. A sampler is a class that determines which requests should be measured. By default, the package uses the `Spatie\OpenTelemetry\Support\Samplers\AlwaysSampler` sampler. This can be configured using the `sampler` key of the `open-telemetry.php` config file.

If you don't want to measure every request, you can use one of the samplers that ship with the package:

- `Spatie\OpenTelemetry\Support\Samplers\LotterySampler` - measures performance for roughly every 2 out of 100 requests
- `Spatie\OpenTelemetry\Support\Samplers\TraceparentHeaderSampler` - only samples requests that contain a valid `traceparent` header
- `Spatie\OpenTelemetry\Support\Samplers\NeverSampler` - never samples any requests

To use any of these samplers, simply specify its class name in the `sampler` key of the `open-telemetry.php` config file.

## Creating your own sampler

A sampler is any class that extends `Spatie\OpenTelemetry\Support\Samplers`. This abstract class requires you to implement a method `shouldSample` that should return a boolean. Here's an example where we create a `CustomLotterySampler` that will measure performance for roughly every 5 out of 1000 requests.

```php
namespace App\Support\Samplers;

use Illuminate\Support\Lottery;

class LotterySampler extends Sampler
{
    public function shouldSample(): bool
    {
        return Lottery::odds(5, 1000)->choose();
    }
}
```

After creating your sampler, don't forget to put it's class name in the `sampler` key of the `open-telemetry.php` config file.

## Using the TraceparentHeaderSampler

The `TraceparentHeaderSampler` is particularly useful when you want to enable tracing only for specific requests that are part of a distributed tracing system. It will only sample requests that contain a valid `traceparent` header, which follows the [W3C Trace Context specification](https://www.w3.org/TR/trace-context/).

To use it, update your `config/open-telemetry.php` file:

```php
'sampler' => \Spatie\OpenTelemetry\Support\Samplers\TraceparentHeaderSampler::class,
```

With this sampler configured:
- Requests without a `traceparent` header will not be sampled
- Requests with an invalid `traceparent` header will not be sampled
- Only requests with a valid `traceparent` header (proper format, valid trace ID, span ID, etc.) will be sampled

This is especially useful in microservice architectures where you want to trace requests only when they're part of an existing trace from another service.
