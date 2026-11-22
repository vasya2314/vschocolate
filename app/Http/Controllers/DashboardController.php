<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): Factory|Application|View
    {
        // Месяц в формате Y-m, по умолчанию — текущий
        $from = Carbon::now()->startOfMonth();
        $month = $request->query('month');
        if (is_string($month) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $from = Carbon::createFromFormat('!Y-m', $month);
        }

        return view('pages.dashboard', [
            'month' => $from->format('Y-m'),
            'from' => $from,
            'to' => $from->copy()->endOfMonth(),
        ]);
    }
}
