<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\FromPlatform;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Database\Factories\FromPlatformFactory;
use Database\Factories\ProductFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Демо-данные для локальной разработки: платформы, товары, клиенты и заказы
 * за прошлый, текущий и следующий месяц.
 *
 * Запуск: php artisan db:seed --class=DemoDataSeeder
 *
 * Платформы и товары из справочников фабрик создаются, только если их ещё нет.
 * Клиенты и заказы при каждом запуске добавляются заново.
 */
class DemoDataSeeder extends Seeder
{
    private const CLIENTS = 30;

    public function run(): void
    {
        DB::transaction(function () {
            $platforms = $this->ensureNamed(FromPlatform::class, FromPlatformFactory::NAMES);
            $products = $this->ensureNamed(Product::class, ProductFactory::NAMES);

            $clients = Client::factory()
                ->count(self::CLIENTS)
                ->recycle($platforms)
                ->create();

            $from = Carbon::now()->subMonthNoOverflow()->startOfMonth();
            $to = Carbon::now()->addMonthNoOverflow()->endOfMonth()->startOfDay();
            $today = Carbon::today();
            $created = 0;

            foreach (CarbonPeriod::create($from, $to) as $date) {
                $count = fake()->numberBetween(0, $date->isWeekend() ? 7 : 4);
                if ($date->gt($today)) {
                    $count = intdiv($count, 2);
                }

                if ($count === 0) {
                    continue;
                }

                Order::factory()
                    ->count($count)
                    ->onDate($date)
                    ->recycle($clients)
                    ->withProducts($products)
                    ->create();

                $created += $count;
            }

            $this->command?->info(sprintf(
                'Клиентов: %d, заказов: %d (%s — %s)',
                $clients->count(), $created, $from->format('d.m.Y'), $to->format('d.m.Y'),
            ));
        });
    }

    /**
     * Создаёт через фабрику записи с недостающими именами и возвращает все записи справочника.
     *
     * @param class-string<FromPlatform|Product> $model
     */
    private function ensureNamed(string $model, array $names)
    {
        $existing = $model::withTrashed()->whereIn('name', $names)->pluck('name')->all();
        $missing = array_values(array_diff($names, $existing));

        if ($missing) {
            $model::factory()
                ->count(count($missing))
                ->sequence(...array_map(fn (string $name) => ['name' => $name], $missing))
                ->create();
        }

        return $model::whereIn('name', $names)->get();
    }
}
