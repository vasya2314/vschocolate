<?php

namespace App\Actions\User;

use App\Models\User;

class UpdateProfileUserAction
{
    public function handle(array $fields, User $user): User
    {
        $user->fill([
            'firstname' => $fields['firstname'] ?? null,
            'lastname' => $fields['lastname'] ?? null,
            'email' => $fields['email'],
        ]);

        $user->save();

        return $user->refresh();
    }
}
