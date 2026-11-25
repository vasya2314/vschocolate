<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\FromPlatform>
 */
class FromPlatformFactory extends Factory
{
    public const NAMES = ['Instagram', 'VK', 'Telegram', 'Авито', 'Сарафан'];

    public function definition(): array
    {
        return [
            'name' => mb_substr(fake()->unique()->company(), 0, 30),
        ];
    }
}
