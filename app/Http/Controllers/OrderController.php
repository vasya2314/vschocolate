<?php

namespace App\Http\Controllers;

use App\Actions\Client\UpdateClientAction;
use App\Actions\Order\DeleteOrderAction;
use App\Actions\Order\GroupStoreOrderAndClientAction;
use App\Actions\Order\RestoreOrderAction;
use App\Actions\Order\UpdateOrderAction;
use App\Http\Filters\Models\OrderFilter;
use App\Http\Requests\Order\IndexRequest;
use App\Http\Requests\Order\StoreRequest;
use App\Http\Requests\Order\UpdateRequest;
use App\Http\Requests\Order\UpdateStatusRequest;
use App\Models\Client;
use App\Models\FromPlatform;
use App\Models\Order;
use App\Models\Product;
use App\View\Components\Alert;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(IndexRequest $request): View|Application|Factory
    {
        $ordersFilter = app()->make(OrderFilter::class, ['queryParams' => array_filter($request->validated())]);

        $orders = Order::withTrashed()
            ->with(['client' => fn($q) => $q->withTrashed()])
            ->with(['products' => fn($q) => $q->withTrashed()])
            ->filter($ordersFilter)
            ->orderByRaw('deleted_at IS NOT NULL ASC')
            ->orderByRaw("
                FIELD(status, ?, ?) DESC,
                CASE
                    WHEN status IN (?, ?) THEN UNIX_TIMESTAMP(date_issue)
                    ELSE -UNIX_TIMESTAMP(date_issue)
                END ASC
            ", [
                Order::STATUS_ACCEPT,
                Order::STATUS_PROGRESS,
                Order::STATUS_ACCEPT,
                Order::STATUS_PROGRESS
            ])
            ->paginate(25);

        $statuses = Order::statuses();

        $products = Product::all()
            ->select('id', 'name')
            ->pluck('name', 'id')->toArray();

        return view('pages.orders.index', compact('orders', 'statuses', 'products'));
    }

    public function create(): View|Application|Factory
    {
        $clients = Client::all()
            ->select('id', 'name')
            ->pluck('name', 'id')
            ->toArray();

        $statuses = Order::statuses();

        $fromPlatforms = FromPlatform::all()
            ->select('id', 'name')
            ->pluck('name', 'id')->toArray();

        $products = Product::all()
            ->select('id', 'name')
            ->pluck('name', 'id')->toArray();

        return view('pages.orders.create', compact('clients', 'statuses', 'fromPlatforms', 'products'));
    }

    public function store(StoreRequest $request, GroupStoreOrderAndClientAction $groupStoreOrderAndClientAction): RedirectResponse
    {
        try {
            $data = $request->validated(); // order fields

            $clientFields = $data['client'];
            unset($data['client']);

            $groupStoreOrderAndClientAction->handle($data, $clientFields);

            return redirect()->route('order.index')
                ->with(Alert::TYPE_SUCCESS, 'Добавлен новый заказ');
        } catch (\Exception $e) {
            return back()->with(Alert::TYPE_ERROR, $e->getMessage())
                ->withInput();
        }
    }

    public function edit(Order $order): View|Application|Factory
    {
        $order->load(['client' => function ($query) {
            $query->withTrashed();
        }])->load(['products' => function ($query) {
            $query->withTrashed();
        }]);

        $statuses = Order::statuses();

        $products = Product::all()
            ->select('id', 'name')
            ->pluck('name', 'id')->toArray();

        return view('pages.orders.edit', compact('order', 'statuses', 'products'));
    }

    public function update(UpdateRequest $request, Order $order, UpdateOrderAction $updateOrderAction): RedirectResponse
    {
        try {
            $updateOrderAction->handle($request->validated(), $order);

            return redirect()->route('order.edit', $order->id)
                ->with(Alert::TYPE_SUCCESS, 'Заказ обновлен');
        } catch (\Exception $e) {
            return back()->with(Alert::TYPE_ERROR, $e->getMessage())
                ->withInput();
        }
    }

    public function updateStatus(UpdateStatusRequest $request, Order $order, UpdateOrderAction $updateOrderAction): RedirectResponse
    {
        $updateOrderAction->handle($request->validated(), $order);

        return back()->with(Alert::TYPE_SUCCESS, 'Заказ №' . $order->id . ': статус «' . $order->status_view . '»');
    }

    public function calendar(Request $request): View|Application|Factory
    {
        $mode = $request->query('view') === 'week' ? 'week' : 'month';

        $date = Carbon::today();
        $dateParam = $request->query('date');
        if (is_string($dateParam) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateParam) && strtotime($dateParam)) {
            $date = Carbon::createFromFormat('!Y-m-d', $dateParam);
        }

        if ($mode === 'week') {
            $periodStart = $date->copy()->startOfWeek();
            $periodEnd = $date->copy()->endOfWeek();
            $gridStart = $periodStart->copy();
            $gridEnd = $periodEnd->copy();
            $prev = $date->copy()->subWeek();
            $next = $date->copy()->addWeek();
            $title = $periodStart->locale('ru')->isoFormat('D MMM') . ' — ' . $periodEnd->locale('ru')->isoFormat('D MMM YYYY');
        } else {
            $periodStart = $date->copy()->startOfMonth();
            $periodEnd = $date->copy()->endOfMonth();
            // Сетка месяца — полные недели с понедельника по воскресенье
            $gridStart = $periodStart->copy()->startOfWeek();
            $gridEnd = $periodEnd->copy()->endOfWeek();
            $prev = $date->copy()->subMonthNoOverflow();
            $next = $date->copy()->addMonthNoOverflow();
            $title = Str::ucfirst($periodStart->locale('ru')->isoFormat('MMMM YYYY'));
        }

        $orders = Order::with(['client' => fn ($q) => $q->withTrashed()])
            ->whereBetween('date_issue', [$gridStart->copy()->startOfDay(), $gridEnd->copy()->endOfDay()])
            ->orderBy('date_issue')
            ->get()
            ->groupBy(fn (Order $order) => Carbon::parse($order->date_issue)->format('Y-m-d'));

        $today = Carbon::today()->format('Y-m-d');
        $days = [];
        foreach (CarbonPeriod::create($gridStart, $gridEnd->copy()->startOfDay()) as $day) {
            $key = $day->format('Y-m-d');
            $dayOrders = $orders->get($key, collect());
            $active = $dayOrders->where('status', '!=', Order::STATUS_CANCELED);

            $days[] = [
                'date' => $key,
                'day' => $day->day,
                'label' => $day->copy()->locale('ru')->isoFormat('D MMMM, dd'),
                'weekday' => $day->copy()->locale('ru')->isoFormat('dd'),
                'in_period' => $day->between($periodStart, $periodEnd),
                'is_today' => $key === $today,
                'is_weekend' => $day->isWeekend(),
                'orders' => $dayOrders,
                'sum' => kopToRubView((int) round($active->sum('amount_total'))),
            ];
        }

        return view('pages.orders.calendar', [
            'mode' => $mode,
            'title' => $title,
            'date' => $date->format('Y-m-d'),
            'prev' => $prev->format('Y-m-d'),
            'next' => $next->format('Y-m-d'),
            'days' => $days,
            'statuses' => Order::statuses(),
        ]);
    }

    public function delete(Order $order, DeleteOrderAction $deleteOrderAction): RedirectResponse
    {
        $deleteOrderAction->handle($order);

        return back()->with(Alert::TYPE_SUCCESS, 'Заказ удален');
    }

    public function restore(Order $order, RestoreOrderAction $restoreOrderAction): RedirectResponse
    {
        $restoreOrderAction->handle($order);

        return back()->with(Alert::TYPE_SUCCESS, 'Заказ восстановлен');
    }

}
