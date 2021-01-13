<?php

namespace Spatie\LaravelRay\Payloads;

use Illuminate\Testing\TestResponse;
use Spatie\Ray\ArgumentConverter;
use Spatie\Ray\Payloads\Payload;

class ResponsePayload extends Payload
{
    protected int $statusCode;

    protected array $headers;

    protected ?string $content;

    protected ?array $json;

    public static function fromTestResponse(TestResponse $testResponse): self
    {
        return new static(
            $testResponse->getStatusCode(),
            $testResponse->headers->all(),
            $testResponse->content(),
            $json = rescue(fn () => $testResponse->json(), null, false)
        );
    }

    public function __construct(int $statusCode, array $headers, string $content, ?array $json = null)
    {
        $this->statusCode = $statusCode;

        $this->headers = $this->normalizeHeaders($headers);

        $this->content = $content;

        $this->json = $json;
    }

    public function getType(): string
    {
        return 'response';
    }

    public function getContent(): array
    {
        return [
            'status_code' => $this->statusCode,
            'headers' => ArgumentConverter::convertToPrimitive($this->headers),
            'content' => $this->content,
            'json' => ArgumentConverter::convertToPrimitive($this->json),
        ];
    }

    protected function normalizeHeaders(array $headers): array
    {
        return collect($headers)
            ->map(fn (array $values) => $values[0] ?? null)
            ->filter()
            ->toArray();
    }
}
