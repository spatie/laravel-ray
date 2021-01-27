<?php

namespace Spatie\LaravelRay;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelRay\Payloads\ExecutedQueryPayload;

class QueryLogger
{
    /** @var bool */
    protected $listenForQueries = false;

    /** @var bool */
    protected $queryListenerRegistered = false;

    public function isLoggingQueries(): bool
    {
        return $this->listenForQueries;
    }

    public function startLoggingQueries(): self
    {
        DB::enableQueryLog();

        $this->listenForQueries = true;

        if (! $this->queryListenerRegistered) {
            DB::listen(function (QueryExecuted $query) {
                if ($this->listenForQueries) {
                    $payload = new ExecutedQueryPayload($query);

                    app(Ray::class)->sendRequest($payload);
                }
            });

            $this->queryListenerRegistered = true;
        }


        return $this;
    }

    public function stopLoggingQueries(): self
    {
        DB::disableQueryLog();

        $this->listenForQueries = false;

        return $this;
    }
}
