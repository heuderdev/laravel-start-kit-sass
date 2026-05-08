<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantAccessDeniedException extends Exception
{
    public function __construct(
        string $message = 'Acesso negado ao tenant informado.',
        int $code = Response::HTTP_FORBIDDEN
    ) {
        parent::__construct($message, $code);
    }

    public function render(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'type'   => 'https://httpstatuses.io/403',
                'title'  => 'Tenant Access Denied',
                'status' => Response::HTTP_FORBIDDEN,
                'detail' => $this->getMessage(),
            ], Response::HTTP_FORBIDDEN);
        }

        return redirect()
            ->back()
            ->with('error', $this->getMessage());
    }
}
