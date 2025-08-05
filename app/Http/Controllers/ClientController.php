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
use App\View\Components\Alert;
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

        return view('pages.clients.edit', compact('client', 'fromPlatforms'));
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

}
