<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateMemberRequest;
use App\Http\Resources\MemberResource;
use App\Models\User;
use App\Services\ListTenantMembersService;
use App\Services\ManageTenantMemberService;
use App\Services\TenantContext;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function __construct(
        private readonly TenantContext $context,
        private readonly ListTenantMembersService $listTenantMembersService,
        private readonly ManageTenantMemberService $manageTenantMemberService,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $tenant = $this->context->get();
        $members = $this->listTenantMembersService->handle($tenant);

        if ($request->expectsJson()) {
            return response()->json([
                'data' => MemberResource::collection($members),
            ]);
        }

        return view('members.index', compact('members', 'tenant'));
    }

    public function update(UpdateMemberRequest $request, User $user): JsonResponse|RedirectResponse
    {
        try {
            $this->manageTenantMemberService->updateRole(
                requester: $request->user(),
                targetUser: $user,
                tenant: $this->context->get(),
                role: $request->validated('role'),
            );
        } catch (DomainException $exception) {
            return $this->respondError(
                request: $request,
                message: $exception->getMessage(),
                status: $exception->getCode() > 0 ? $exception->getCode() : 422,
            );
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Papel atualizado com sucesso.',
            ]);
        }

        return back()->with('success', 'Papel do membro atualizado.');
    }

    public function destroy(Request $request, User $user): JsonResponse|RedirectResponse
    {
        try {
            $this->manageTenantMemberService->removeMember(
                requester: $request->user(),
                targetUser: $user,
                tenant: $this->context->get(),
            );
        } catch (DomainException $exception) {
            return $this->respondError(
                request: $request,
                message: $exception->getMessage(),
                status: $exception->getCode() > 0 ? $exception->getCode() : 422,
            );
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Membro removido com sucesso.',
            ]);
        }

        return back()->with('success', 'Membro removido do workspace.');
    }

    private function respondError(
        Request $request,
        string $message,
        int $status,
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ], $status);
        }

        if ($status === 403) {
            abort(403, $message);
        }

        return back()->with('error', $message);
    }
}
