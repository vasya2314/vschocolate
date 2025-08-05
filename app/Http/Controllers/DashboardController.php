<?php

namespace App\Http\Controllers;

use App\Actions\Auth\LoginUserAction;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Client;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(): Factory|Application|View
    {
        return view('pages.dashboard');
    }
}
