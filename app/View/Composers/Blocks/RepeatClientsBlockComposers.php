<?php

namespace App\View\Composers\Blocks;

use App\Models\Client;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\View\View;

/**
 * Повторные клиенты: доля заказов периода от клиентов, у которых больше одного заказа за всё время,
 * и топ клиентов по сумме всех заказов. Отменённые заказы не учитываются.
 */
class RepeatClientsBlockComposers
{
    private const LIMIT = 10;

    public function compose(View $view): void
    {
        $data = $view->getData();
        $from = $data['from'] ?? Carbon::now()->startOfMonth();
        $to = $data['to'] ?? Carbon::now()->endOfMonth();

        $notCanceled = fn ($query) => $query->notCanceled();

        $clients = Client::withTrashed()
            ->withCount(['orders as active_orders_count' => $notCanceled])
            ->withSum(['orders as active_orders_sum' => $notCanceled], 'amount_total')
            ->get()
            ->keyBy('id');

        $isRepeat = fn ($clientId) => ($clients->get($clientId)?->active_orders_count ?? 0) > 1;

        $periodClientIds = Order::notCanceled()->issuedBetween($from, $to)->pluck('client_id');
        $periodOrders = $periodClientIds->count();
        $repeatOrders = $periodClientIds->filter($isRepeat)->count();
        $periodClients = $periodClientIds->unique();

        $top = $clients
            ->where('active_orders_count', '>', 0)
            ->sortByDesc('active_orders_sum')
            ->take(self::LIMIT)
            ->values()
            ->map(fn (Client $client) => [
                'client' => $client,
                'count' => $client->active_orders_count,
                'total' => (int) $client->active_orders_sum,
            ]);

        $view->with([
            'periodOrders' => $periodOrders,
            'repeatOrders' => $repeatOrders,
            'repeatShare' => $periodOrders > 0 ? (int) round($repeatOrders / $periodOrders * 100) : 0,
            'periodClients' => $periodClients->count(),
            'repeatClients' => $periodClients->filter($isRepeat)->count(),
            'top' => $top,
        ]);
    }
}
