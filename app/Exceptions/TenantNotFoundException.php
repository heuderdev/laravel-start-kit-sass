<?php

namespace App\Exceptions;

use Exception;

class TenantNotFoundException extends Exception
{
    public function __construct(string $message = 'Tenant not found.')
    {
        parent::__construct($message);
    }

    public function render()
    {
        return response()->json([
            'type'   => 'https://httpstatuses.io/404',
            'title'  => 'Tenant Not Found',
            'status' => 404,
            'detail' => $this->message,
        ], 404);
    }
}
