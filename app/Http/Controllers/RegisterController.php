<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\RegisterUserRequest;
use App\Services\ProvisionNewAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function __construct(
        private readonly ProvisionNewAccount $provisioner,
    ) {}

    public function store(RegisterUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = $this->provisioner->handle(
            name: $validated['name'],
            email: $validated['email'],
            password: $validated['password'],
        );

        $tenant = $user->defaultTenant();

        Auth::login($user);

        $request->session()->regenerate();
        $request->session()->put('active_tenant_id', $tenant->id);

        return redirect('/dashboard');
    }
}
