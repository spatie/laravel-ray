<?php

namespace Spatie\LaravelRay\Payloads;

use Illuminate\Database\Events\QueryExecuted;
use Spatie\Ray\Payloads\Payload;

class ExecutedQueryPayload extends Payload
{
    /** @var \Illuminate\Database\Events\QueryExecuted */
    protected $query;

    public function __construct(QueryExecuted $query)
    {
        $this->query = $query;
    }

    public function getType(): string
    {
        return 'executed_query';
    }

    public function getContent(): array
    {
        return [
            'sql' => $this->query->sql,
            'bindings' => $this->query->bindings,
            'connection_name' => $this->query->connectionName,
            'time' => $this->query->time,
        ];
    }
}
