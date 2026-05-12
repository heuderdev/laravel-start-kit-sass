<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class ApplicationException extends Exception
{
    protected int $status;
    protected string $errorCode;
    protected array $context;
    protected array $origin;

    public function __construct(
        string $message = 'Ocorreu um erro na aplicação.',
        int $status = 400,
        string $errorCode = 'APPLICATION_ERROR',
        array $context = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);

        $this->status = $status;
        $this->errorCode = $errorCode;
        $this->context = $context;
        $this->origin = $this->resolveOrigin();
    }

    public static function make(
        string $message,
        int $status = 400,
        string $errorCode = 'APPLICATION_ERROR',
        array $context = [],
        ?Throwable $previous = null
    ): self {
        return new self($message, $status, $errorCode, $context, $previous);
    }

    public function status(): int
    {
        return $this->status;
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }

    public function context(): array
    {
        return $this->context;
    }

    public function origin(): array
    {
        return $this->origin;
    }

    public function toArray(bool $debug = false): array
    {
        $data = [
            'success' => false,
            'message' => $this->getMessage(),
            'error' => [
                'code' => $this->errorCode(),
                'status' => $this->status(),
            ],
        ];

        if (!empty($this->context())) {
            $data['error']['context'] = $this->context();
        }

        if ($debug) {
            $data['error']['origin'] = $this->origin();
        }

        return $data;
    }

    protected function resolveOrigin(): array
    {
        $trace = $this->getTrace();

        $firstAppTrace = collect($trace)
            ->first(function (array $item) {
                $file = $item['file'] ?? null;

                if (!$file) {
                    return false;
                }

                return str_contains($file, DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR);
            });

        return [
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'class' => $firstAppTrace['class'] ?? null,
            'method' => $firstAppTrace['function'] ?? null,
        ];
    }
}
