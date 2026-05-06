<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ProvisionNewAccount;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function __construct(private ProvisionNewAccount $provisioner) {}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $this->provisioner->handle(
            name: $validated['name'],
            email: $validated['email'],
            password: $validated['password'],
        );

        $tenant = $user->defaultTenant();

        session(['active_tenant_id' => $tenant->id]);

        auth()->login($user);

        return redirect('/dashboard');
    }
}
