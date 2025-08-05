<?php

namespace App\Http\Controllers;

use App\Actions\Auth\LoginUserAction;
use App\Actions\Auth\LogoutUserAction;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        $user = $request->user();

        if($user && $user->isAdmin())
        {
            return redirect()->route('dashboard.index');
        }

        return redirect()->route('login');
    }

    public function getLoginView(): Factory|Application|View
    {
        return view('login');
    }

    public function login(LoginRequest $request, LoginUserAction $loginUserAction): RedirectResponse
    {
        if($loginUserAction->handle($request->input('email'), $request->input('password')))
        {
            return redirect()->route('dashboard.index');
        }

        return back()->withErrors([
            'email' => 'Предоставленные учетные данные не соответствуют нашим записям',
        ])->onlyInput('email');
    }

    public function logout(LogoutUserAction $logoutUserAction): RedirectResponse
    {
        $logoutUserAction->handle();

        return redirect()->route('login');
    }
}
