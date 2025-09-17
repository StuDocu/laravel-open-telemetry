<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Tests\TestSupport\TestClasses;

use OpenTelemetry\API\Common\Time\ClockInterface;

class FakeClock implements ClockInterface
{
    private static array $timestampQueue = [];
    private static int $index = 0;

    public function now(): int
    {
        if (isset(self::$timestampQueue[self::$index])) {
            $timestamp = self::$timestampQueue[self::$index];
            self::$index++;
            return $timestamp;
        }

        // Fallback - shouldn't happen in normal tests
        return 1641024000000000000 + (self::$index * 1000000000);
    }

    public static function reset(): void
    {
        self::$index = 0;
        self::$timestampQueue = [];
    }

    public static function setTimestampQueue(array $timestamps): void
    {
        self::$timestampQueue = $timestamps;
        self::$index = 0;
    }
}
