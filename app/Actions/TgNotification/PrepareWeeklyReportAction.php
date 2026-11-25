<?php

namespace App\Actions\TgNotification;

use App\Models\Client;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Еженедельный отчёт (пн–вс недели, в которую входит $date) со сравнением с прошлой неделей.
 */
class PrepareWeeklyReportAction
{
    public function handle(?Carbon $date = null): string
    {
        $start = ($date ?? Carbon::today())->copy()->startOfWeek();
        $end = $start->copy()->endOfWeek();
        $prevStart = $start->copy()->subWeek();
        $prevEnd = $end->copy()->subWeek();
        $nextStart = $start->copy()->addWeek();
        $nextEnd = $end->copy()->addWeek();

        $completed = $this->completed($start, $end);
        $prevCompleted = $this->completed($prevStart, $prevEnd);

        $revenue = (int) round($completed->sum('amount_total'));
        $prevRevenue = (int) round($prevCompleted->sum('amount_total'));
        $count = $completed->count();

        $created = Order::whereBetween('created_at', [$start, $end])->count();
        $canceled = Order::whereBetween('date_issue', [$start, $end])
            ->where('status', Order::STATUS_CANCELED)
            ->count();
        $newClients = Client::whereBetween('created_at', [$start, $end])->count();

        $topProducts = DB::table('order_product')
            ->join('orders', 'orders.id', '=', 'order_product.order_id')
            ->join('products', 'products.id', '=', 'order_product.product_id')
            ->whereNull('orders.deleted_at')
            ->where('orders.status', '!=', Order::STATUS_CANCELED)
            ->whereBetween('orders.date_issue', [$start, $end])
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('cnt')
            ->limit(3)
            ->get(['products.name', DB::raw('COUNT(*) as cnt')]);

        $debtors = Order::where('status', Order::STATUS_COMPLETE)
            ->whereColumn('amount_payed', '<', 'amount_total')
            ->get(['amount_total', 'amount_payed']);

        $next = Order::whereBetween('date_issue', [$nextStart, $nextEnd])
            ->where('status', '!=', Order::STATUS_CANCELED)
            ->get(['amount_total']);

        $lines = [];
        $lines[] = '📊 <b>Отчёт за неделю ' . $start->format('d.m') . ' — ' . $end->format('d.m.Y') . '</b>';
        $lines[] = '';
        $lines[] = '💰 Выручка (выдано): <b>' . kopToRubView($revenue) . '</b>' . $this->delta($revenue, $prevRevenue);
        $lines[] = '📦 Выдано заказов: <b>' . $count . '</b>' . $this->delta($count, $prevCompleted->count());
        $lines[] = '🧾 Средний чек: ' . kopToRubView($count ? (int) round($revenue / $count) : 0);
        $lines[] = '🆕 Принято новых заказов: ' . $created;
        $lines[] = '👤 Новых клиентов: ' . $newClients;
        if ($canceled > 0) {
            $lines[] = '❌ Отменено: ' . $canceled;
        }

        if ($topProducts->isNotEmpty()) {
            $lines[] = '';
            $lines[] = '🏆 Популярное:';
            foreach ($topProducts as $i => $product) {
                $lines[] = '   ' . ($i + 1) . '. ' . e($product->name) . ' — ' . $product->cnt;
            }
        }

        $lines[] = '';
        if ($debtors->isNotEmpty()) {
            $debt = $debtors->sum(fn ($o) => $o->amount_total - $o->amount_payed);
            $lines[] = '💸 Долг клиентов по выданным: <b>' . kopToRubView((int) round($debt)) . '</b> (' . $debtors->count() . ' зак.)';
        }
        $lines[] = '📅 На следующей неделе к выдаче: <b>' . $next->count() . '</b> на ' . kopToRubView((int) round($next->sum('amount_total')));

        return implode("\n", $lines);
    }

    private function completed(Carbon $from, Carbon $to)
    {
        return Order::where('status', Order::STATUS_COMPLETE)
            ->whereBetween('date_issue', [$from, $to])
            ->get(['amount_total']);
    }

    /** « (▲ 12% к прошлой неделе)» */
    private function delta(int $current, int $previous): string
    {
        if ($previous === 0) return '';

        $percent = (int) round(($current - $previous) / $previous * 100);
        $arrow = $percent > 0 ? '▲' : ($percent < 0 ? '▼' : '=');

        return ' (' . $arrow . ' ' . abs($percent) . '% к прошлой неделе)';
    }
}
