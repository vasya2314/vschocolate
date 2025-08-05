<?php

namespace App\Actions\User;

use App\Exceptions\BusinessException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StoreUserAction
{
    /**
     * @throws BusinessException
     */
    public function handle(array $fields): User
    {
        $data = [
            'firstname' => $fields['firstname'] ?? null,
            'lastname' => $fields['lastname'] ?? null,
            'email' => $fields['email'],
            'password' => Hash::make($fields['password']),
            'email_verified_at' => $fields['email_verified_at'] ?? null,
        ];

        if(User::findByEmail($data['email'])->exists())
        {
            throw new BusinessException(sprintf('Пользователь с email: %s уже существует', $data['email']));
        }

        return User::create($data);
    }
}
