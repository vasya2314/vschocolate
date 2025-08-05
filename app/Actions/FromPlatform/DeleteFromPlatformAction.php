<?php

namespace App\Actions\FromPlatform;

use App\Models\FromPlatform;

class DeleteFromPlatformAction
{
    public function handle(FromPlatform $fromPlatform): ?bool
    {
        return $fromPlatform->delete();
    }
}
