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
use App\Models\Client;
use App\Models\FromPlatform;
use App\Models\Order;
use App\Models\Product;
use App\View\Components\Alert;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    public function index(IndexRequest $request): View|Application|Factory
    {
        $ordersFilter = app()->make(OrderFilter::class, ['queryParams' => array_filter($request->validated())]);

        $orders = Order::withTrashed()
            ->with(['client' => function ($query) {
                $query->withTrashed();
            }])
            ->with(['products' => function ($query) {
                $query->withTrashed();
            }])
            ->filter($ordersFilter)
            ->orderByRaw("FIELD(status, ?, ?) desc", [
                Order::STATUS_ACCEPT,
                Order::STATUS_PROGRESS
            ])
            ->orderBy('date_issue', 'desc')
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
