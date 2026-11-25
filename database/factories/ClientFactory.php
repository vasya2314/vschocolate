<?php

namespace Database\Factories;

use App\Models\FromPlatform;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->firstName(),
            'phone' => '+7' . fake()->unique()->numerify('9#########'),
            'from_platform_id' => FromPlatform::factory(),
            'comment' => fake()->optional(0.25)->randomElement([
                'Постоянный клиент',
                'Предпочитает связь в мессенджере',
                'Аллергия на орехи',
                'Просит звонить заранее',
            ]),
        ];
    }
}
