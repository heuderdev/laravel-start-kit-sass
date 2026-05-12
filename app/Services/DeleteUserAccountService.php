<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeleteUserAccountService
{
    public function handle(Request $request, User $user): void
    {
        Auth::guard('web')->logout();

        $user->forceDelete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
