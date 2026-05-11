<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantNotFoundException extends Exception
{
    public function __construct(
        string $message = 'Tenant não encontrado.',
        int $code = Response::HTTP_NOT_FOUND,
    ) {
        parent::__construct($message, $code);
    }

    public function render(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'type'   => 'https://httpstatuses.io/404',
                'title'  => 'Tenant Not Found',
                'status' => Response::HTTP_NOT_FOUND,
                'detail' => $this->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }

        return redirect()
            ->route('tenants.index')
            ->with('error', 'Workspace não encontrado. Selecione um workspace para continuar.');
    }
}
