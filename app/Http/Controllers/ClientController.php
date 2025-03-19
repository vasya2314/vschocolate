<?php

namespace App\Http\Controllers;


use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function checkClient(Request $request): JsonResponse
    {
        $phone = $request->input('phone');

        $client = Client::where('phone', $phone)->firstOrFail();

        return response()->json($client);
    }
}
