<?php

namespace App\Http\Controllers;

use App\Actions\Client\DeleteClientAction;
use App\Actions\Client\FindByPhoneClientAction;
use App\Actions\Client\RestoreClientAction;
use App\Actions\Client\StoreClientAction;
use App\Actions\Client\UpdateClientAction;
use App\Http\Filters\Models\ClientFilter;
use App\Http\Requests\Client\FindRequest;
use App\Http\Requests\Client\IndexRequest;
use App\Http\Requests\Client\StoreRequest;
use App\Http\Requests\Client\UpdateRequest;
use App\Models\Client;
use App\Models\FromPlatform;
use App\Models\Order;
use App\View\Components\Alert;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ClientController extends Controller
{

    public function index(IndexRequest $request): View|Application|Factory
    {
        $clientsFilter = app()->make(ClientFilter::class, ['queryParams' => array_filter($request->validated())]);

        $clients = Client::withTrashed()
            ->with('fromPlatform')
            ->filter($clientsFilter)
            ->orderBy('deleted_at')
            ->latest()
            ->paginate(25);

        $fromPlatforms = FromPlatform::all()
            ->pluck('name', 'id')->toArray();

        return view('pages.clients.index', compact('clients', 'fromPlatforms'));
    }

    public function create(): View|Application|Factory
    {
        $fromPlatforms = FromPlatform::all()
            ->select('id', 'name')
            ->pluck('name', 'id')->toArray();

        return view('pages.clients.create', compact('fromPlatforms'));
    }

    public function store(StoreRequest $request, StoreClientAction $storeClientAction): RedirectResponse
    {
        try {
            $storeClientAction->handle($request->validated());

            return redirect()->route('client.index')
                ->with(Alert::TYPE_SUCCESS, 'Добавлен новый клиент');
        } catch (\Exception $e) {
            return back()->with(Alert::TYPE_ERROR, $e->getMessage())
                ->onlyInput('name', 'phone', 'from_platform_id', 'comment');
        }
    }

    public function edit(Client $client): View|Application|Factory
    {
        $fromPlatforms = FromPlatform::all()
            ->pluck('name', 'id')->toArray();

        $orders = $client->orders()
            ->with(['products' => fn ($q) => $q->withTrashed()])
            ->orderByDesc('date_issue')
            ->paginate(20);

        $stats = $this->getClientStats($client);

        return view('pages.clients.edit', compact('client', 'fromPlatforms', 'orders', 'stats'));
    }

    public function update(UpdateRequest $request, Client $client, UpdateClientAction $updateClientAction): RedirectResponse
    {
        try {
            $updateClientAction->handle($request->validated(), $client);

            return redirect()->route('client.edit', $client->id)
                ->with(Alert::TYPE_SUCCESS, 'Клиент обновлен');
        } catch (\Exception $e) {
            return back()->with(Alert::TYPE_ERROR, $e->getMessage())
                ->onlyInput('name', 'phone', 'from_platform_id', 'comment');
        }
    }

    public function delete(Client $client, DeleteClientAction $deleteClientAction): RedirectResponse
    {
        $deleteClientAction->handle($client);

        return back()->with(Alert::TYPE_SUCCESS, 'Клиент удален');
    }

    public function restore(Client $client, RestoreClientAction $restoreClientAction): RedirectResponse
    {
        $restoreClientAction->handle($client);

        return back()->with(Alert::TYPE_SUCCESS, 'Клиент восстановлен');
    }

    public function getClientByPhone(FindRequest $request, FindByPhoneClientAction $findByPhoneClientAction): JsonResponse
    {
        if($client = $findByPhoneClientAction->handle($request->input('phone'), true))
        {
            return response()->json($client, 200);
        }

        return response()->json(null, 404);
    }


    /**
     * Сводка по заказам клиента. Отменённые заказы в суммах не учитываются.
     */
    private function getClientStats(Client $client): array
    {
        $row = $client->orders()
            ->toBase()
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw('SUM(status != ?) as active_count', [Order::STATUS_CANCELED])
            ->selectRaw('SUM(CASE WHEN status != ? THEN amount_total ELSE 0 END) as amount_total', [Order::STATUS_CANCELED])
            ->selectRaw('SUM(CASE WHEN status != ? THEN amount_payed ELSE 0 END) as amount_payed', [Order::STATUS_CANCELED])
            ->selectRaw('SUM(CASE WHEN status != ? AND amount_total > amount_payed THEN amount_total - amount_payed ELSE 0 END) as debt', [Order::STATUS_CANCELED])
            ->selectRaw('MIN(date_issue) as first_date, MAX(date_issue) as last_date')
            ->first();

        $activeCount = (int) $row->active_count;
        $amountTotal = (int) round((float) $row->amount_total);

        return [
            'total_count' => (int) $row->total_count,
            'active_count' => $activeCount,
            'canceled_count' => (int) $row->total_count - $activeCount,
            'amount_total' => $amountTotal,
            'amount_payed' => (int) round((float) $row->amount_payed),
            'debt' => (int) round((float) $row->debt),
            'average' => $activeCount > 0 ? (int) round($amountTotal / $activeCount) : 0,
            'first_date' => $row->first_date ? Carbon::parse($row->first_date) : null,
            'last_date' => $row->last_date ? Carbon::parse($row->last_date) : null,
        ];
    }
}
