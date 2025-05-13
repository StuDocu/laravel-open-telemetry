<?php

declare(strict_types=1);

namespace Spatie\OpenTelemetry\Support\Formatters;

use Spatie\OpenTelemetry\Support\Span;

use function array_map;
use function gettype;
use function json_encode;

class SpanFormatter
{
    /**
     * Format the given spans into a string representation.
     * The result string is in OpenTelemetry protocol HTTP JSON format.
     *
     * @param list<Span> $spans
     */
    public function format(array $spans): string
    {
        if ($spans === []) {
            return '';
        }

        $serviceName = $spans[0]->trace->getAttributes()['service.name'] ?? 'backend';
        $scopeName = $spans[0]->getAttributes()['scope.name'] ?? 'scope-name';

        $formattedSpans = array_map(fn (Span $span) => $this->formatSpan($span), $spans);

        return json_encode([
            'resourceSpans' => [
                [
                    'resource' => [
                        'attributes' => [
                            [
                                'key' => 'service.name',
                                'value' => ['stringValue' => $serviceName],
                            ],
                        ],
                    ],
                    'scopeSpans' => [
                        [
                            'scope' => [
                                'name' => $scopeName,
                                'version' => '1.0.0',
                                'attributes' => [
                                    [
                                        'key' => 'request.id',
                                        'value' => ['stringValue' => '123456'],
                                    ],
                                ],
                            ],
                            'spans' => $formattedSpans,
                        ],
                    ],
                ],
            ],
        ]) ?: '';
    }

    /** @return array<string, mixed> */
    private function formatSpan(Span $span): array
    {
        $formattedSpan = [
            'traceId' => $span->trace->id(),
            'spanId' => $span->id,
            'parentSpanId' => $span->parentSpan?->id ?? '',
            'name' => $span->name,
            'startTimeUnixNano' => $span->getStartTime(),
            'endTimeUnixNano' => $span->getEndTime(),
            'kind' => 2, // Defaults to 2 which is server
        ];

        $attributes = [];

        foreach ($span->getAttributes() as $attributeName => $attributeValue) {
            $formattedAttribute = $this->formatAttribute($attributeName, $attributeValue);

            if ($formattedAttribute === []) {
                continue;
            }

            $attributes[] = $formattedAttribute;
        }

        if ($attributes !== []) {
            $formattedSpan['attributes'] = $attributes;
        }

        return $formattedSpan;
    }

    /** @return array{key?: string, value?: array<string, scalar>} */
    private function formatAttribute(string $attributeName, mixed $attributeValue): array
    {
        $key = match (gettype($attributeValue)) {
            'boolean' => 'boolValue',
            'integer' => 'intValue',
            'double' => 'doubleValue',
            'string' => 'stringValue',
            default => null,
        };

        if ($key === null) {
            return [];
        }

        return [
            'key' => $attributeName,
            'value' => [$key => $attributeValue],
        ];
    }
}
