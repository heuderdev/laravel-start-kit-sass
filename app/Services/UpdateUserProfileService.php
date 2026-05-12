<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

class UpdateUserProfileService
{
    /**
     * @param array<string, mixed> $data
     */
    public function handle(User $user, array $data): void
    {
        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
    }
}
