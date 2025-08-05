<?php

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Auth;

class LoginUserAction
{
    public function handle(string $email, string $password): bool
    {
        $credentials = [
            'email' => $email,
            'password' => $password,
        ];

        if (Auth::attempt($credentials))
        {
            request()->session()->regenerate();

            return true;
        }

        return false;
    }
}
