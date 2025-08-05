<?php

namespace App\Actions\User;

use App\Models\User;
use Carbon\Carbon;

class VerifyEmailUserAction
{
    public function handle(User $user, Carbon $emailVerifiedAt): bool
    {
        $user->forceFill([
            'email_verified_at' => $emailVerifiedAt,
        ]);

        return $user->save();
    }
}
