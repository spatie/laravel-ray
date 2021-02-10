<?php

namespace Spatie\LaravelRay\Payloads;

use Dotenv\Dotenv;
use Illuminate\Support\Env;
use Spatie\Ray\ArgumentConverter;
use Spatie\Ray\Payloads\Payload;

class EnvironmentPayload extends Payload
{
    /** @var array */
    protected $values;

    /** @var string */
    protected $path;

    /** @var string */
    protected $filename;

    public function __construct(string $environmentPath, string $environmentFile)
    {
        $this->path = $environmentPath;

        $this->filename = $environmentFile;

        $this->values = $this->loadDotEnv();
    }

    public function getType(): string
    {
        return 'table';
    }

    public function getContent(): array
    {
        $values = array_map(function ($value) {
            $value = $this->decorateSpecialValues($value);

            return ArgumentConverter::convertToPrimitive($value);
        }, $this->values);

        return [
            'values' => $values,
            'label' => '.env',
        ];
    }

    protected function decorateSpecialValues($value)
    {
        if ($value === '') {
            return '<div class="text-gray-400">(empty)</div>';
        }

        if ($value === 'null') {
            return '<div class="text-gray-400">NULL</div>';
        }

        if ($value === 'true' || $value === 'false') {
            $color = $value === 'true' ? 'green' : 'red';

            return "<div class=\"text-{$color}-600\">{$value}</div>";
        }

        if (preg_match('~^https?://~', $value) === 1) {
            return "<a href=\"{$value}\" class=\"text-blue-600 hover:underline\">{$value}</a>";
        }

        if (strpos($value, 'base64:') === 0) {
            return "<div class=\"text-gray-400\">{$value}</div>";
        }

        if (preg_match('~(\d{1,3}\.){3}\d{1,3}~', $value) === 1) {
            return "<div href=\"{$value}\" class=\"text-indigo-700\">{$value}</div>";
        }

        return $value;
    }

    protected function loadDotEnv(): array
    {
        return Dotenv::create(
            Env::getRepository(),
            $this->path,
            $this->filename
        )->safeLoad();
    }
}
