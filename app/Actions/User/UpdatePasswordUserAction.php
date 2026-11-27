<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UpdatePasswordUserAction
{
    public function handle(string $password, User $user): User
    {
        $user->update(['password' => Hash::make($password)]);

        return $user;
    }
}
