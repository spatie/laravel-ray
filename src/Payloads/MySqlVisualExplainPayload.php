<?php

namespace Spatie\LaravelRay\Payloads;

use Spatie\Ray\Payloads\Payload;

class MySqlVisualExplainPayload extends Payload
{
    protected string $url;

    public function __construct(string $url)
    {
        $this->url = $url;

    }

    public function getType(): string
    {
        return 'mysql_visual_explain';
    }

    public function getContent(): array
    {
        return [
            'url' => $this->url,
        ];
    }
}
