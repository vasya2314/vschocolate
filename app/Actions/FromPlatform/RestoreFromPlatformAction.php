<?php

namespace App\Actions\FromPlatform;

use App\Models\FromPlatform;

class RestoreFromPlatformAction
{
    public function handle(FromPlatform $fromPlatform): ?bool
    {
        return $fromPlatform->restore();
    }
}
