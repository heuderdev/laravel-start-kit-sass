<?php

namespace App\Exceptions;

use Exception;

class TenantAccessDeniedException extends Exception
{
    public function __construct(string $message = 'Access denied to this tenant.')
    {
        parent::__construct($message);
    }

    public function render()
    {
        return response()->json([
            'type'   => 'https://httpstatuses.io/403',
            'title'  => 'Tenant Access Denied',
            'status' => 403,
            'detail' => $this->message,
        ], 403);
    }
}
