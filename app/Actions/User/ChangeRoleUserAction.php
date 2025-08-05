<?php

namespace App\Actions\User;

use App\Models\User;

class ChangeRoleUserAction
{
    public function handle(User $user, int $role): bool
    {
        $user->forceFill([
            'role' => $role,
        ]);

        return $user->save();
    }
}
