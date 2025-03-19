<?php

namespace App\Http\Controllers;

use App\DTO\ClientDTO;
use App\DTO\OrderDTO;
use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Services\ClientService;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(
        protected ClientService $clientService,
        protected OrderService $orderService
    ) {}

    public function store(OrderRequest $request): JsonResponse
    {
        $data = $request->validated();
        $clientId = $data['client_id'] ?? null;

        if($clientId == null)
        {
            // Создаем сначала клиента
            $client = $this->clientService->createClient(
                new ClientDTO(
                    $data['client_name'],
                    $data['client_phone'],
                    $data['client_from'],
                    $data['client_comment'],
                )
            );

            $clientId = $client->id;
        }

        $order = $this->orderService->createOrder(
            new OrderDTO(
                Carbon::createFromFormat('Y-m-d\TH:i', $data['date_issue']),
                $clientId,
                $data['description'],
                rubToKop($data['amount_total']),
                rubToKop($data['amount_payed']),
                Order::STATUS_ACCEPT,
                $data['contents']
            )
        );

        return response()->json(
            [
                'message' => 'Сохранено',
                'messageType' => 'success',
                'redirect' => route('moonshine.resource.page', [
                    'resourceUri' => 'order-resource',
                    'pageUri' => 'form-order',
                    'resourceItem' => $order->id,
                ]),
            ]
        );
    }

    public function update(OrderRequest $request, Order $order): JsonResponse
    {
        $data = $request->validated();

        $this->orderService->updateOrder(
            $order,
            new OrderDTO(
                Carbon::createFromFormat('Y-m-d\TH:i', $data['date_issue']) ?? null,
                null,
                $data['description'] ?? null,
                rubToKop($data['amount_total']) ?? null,
                rubToKop($data['amount_payed']) ?? null,
                $data['status'] ?? null,
                $data['contents'] ?? null
            )
        );

        return response()->json(
            [
                'message' => 'Сохранено',
                'messageType' => 'success'
            ]
        );
    }
}
