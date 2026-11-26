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

        $active = fn () => Order::query()->where('status', '!=', Order::STATUS_CANCELED);

        // client_id => число заказов и сумма за всё время
        $allTime = $active()
            ->toBase()
            ->selectRaw('client_id, COUNT(*) as cnt, SUM(amount_total) as total')
            ->groupBy('client_id')
            ->get()
            ->keyBy('client_id');

        $periodClientIds = $active()->whereBetween('date_issue', [$from, $to])->pluck('client_id');
        $isRepeat = fn ($clientId) => ($allTime->get($clientId)->cnt ?? 0) > 1;

        $periodOrders = $periodClientIds->count();
        $repeatOrders = $periodClientIds->filter($isRepeat)->count();
        $periodClients = $periodClientIds->unique();

        $top = $allTime->sortByDesc('total')->take(self::LIMIT);
        $clients = Client::withTrashed()->whereIn('id', $top->keys())->get()->keyBy('id');

        $view->with([
            'periodOrders' => $periodOrders,
            'repeatOrders' => $repeatOrders,
            'repeatShare' => $periodOrders > 0 ? (int) round($repeatOrders / $periodOrders * 100) : 0,
            'periodClients' => $periodClients->count(),
            'repeatClients' => $periodClients->filter($isRepeat)->count(),
            'top' => $top->map(fn ($row) => [
                'client' => $clients->get($row->client_id),
                'count' => (int) $row->cnt,
                'total' => (int) $row->total,
            ])->filter(fn ($row) => $row['client'] !== null)->values(),
        ]);
    }
}
