<?php

namespace App\Http\Controllers;

use App\Actions\User\UpdatePasswordUserAction;
use App\Actions\User\UpdateProfileUserAction;
use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Requests\Profile\UpdateRequest;
use App\View\Components\Alert;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('pages.profile', ['user' => $request->user()]);
    }

    public function update(UpdateRequest $request, UpdateProfileUserAction $updateProfileUserAction): RedirectResponse
    {
        try {
            $updateProfileUserAction->handle($request->validated(), $request->user());

            return redirect()->route('profile.edit')
                ->with(Alert::TYPE_SUCCESS, 'Профиль обновлен');
        } catch (\Exception $e) {
            return back()->with(Alert::TYPE_ERROR, $e->getMessage())
                ->onlyInput('firstname', 'lastname', 'email');
        }
    }

    public function updatePassword(UpdatePasswordRequest $request, UpdatePasswordUserAction $updatePasswordUserAction): RedirectResponse
    {
        try {
            $updatePasswordUserAction->handle($request->validated('password'), $request->user());

            return redirect()->route('profile.edit')
                ->with(Alert::TYPE_SUCCESS, 'Пароль изменен');
        } catch (\Exception $e) {
            return back()->with(Alert::TYPE_ERROR, $e->getMessage());
        }
    }
}
