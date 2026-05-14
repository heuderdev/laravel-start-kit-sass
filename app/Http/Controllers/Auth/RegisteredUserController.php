<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ProvisionNewAccount;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['confirmed', Rules\Password::defaults()],
        ]);

        // $user = User::create([
        //     'name' => $request->name,
        //     'email' => $request->email,
        //     'password' => Hash::make($request->password),
        // ]);

        $user = app(ProvisionNewAccount::class)->handle(
            name: $request->name,
            email: $request->email,
            password: $request->password,
        );

        $tenant = $user->defaultTenant();

        event(new Registered($user));

        Auth::login($user);

        session(['active_tenant_id' => $tenant->id]);

        return redirect(route('dashboard', absolute: false));
    }

    public function storeApi(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate(
                [
                    'name' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'string', 'email', 'unique:' . User::class],
                    'password' => ['required', 'confirmed', Rules\Password::defaults()],
                ],
                [
                    'name.required' => 'O nome é obrigatório.',
                    'email.required' => 'O e-mail é obrigatório.',
                    'email.email' => 'Informe um e-mail válido.',
                    'email.unique' => 'Este e-mail já está cadastrado.',
                    'password.required' => 'A senha é obrigatória.',
                    'password.confirmed' => 'A confirmação da senha não confere.',
                ]
            );

            $user = app(ProvisionNewAccount::class)->handle(
                name: $validated['name'],
                email: $validated['email'],
                password: $validated['password'],
            );

            $tenant = $user->defaultTenant();
            $token = $user->createToken('api')->plainTextToken;

            return response()->json([
                'message' => 'Usuário cadastrado com sucesso.',
                'token' => $token,
                'tenant_id' => $tenant->id,
                'user' => $user,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação.',
                'errors' => $e->errors(),
            ], 422);
        }
    }
}
