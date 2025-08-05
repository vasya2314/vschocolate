<?php

namespace App\Http\Controllers;

use App\Actions\FromPlatform\DeleteFromPlatformAction;
use App\Actions\FromPlatform\RestoreFromPlatformAction;
use App\Actions\FromPlatform\StoreFromPlatformAction;
use App\Actions\FromPlatform\UpdateFromPlatformAction;
use App\Http\Requests\FromPlatform\StoreRequest;
use App\Http\Requests\FromPlatform\UpdateRequest;
use App\Models\FromPlatform;
use App\View\Components\Alert;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;

class FromPlatformController extends Controller
{
    public function store(StoreRequest $request, StoreFromPlatformAction $storeFromPlatformAction): RedirectResponse
    {
        try {
            $storeFromPlatformAction->handle($request->input('name'));

            return redirect()->route('settings.index')
                ->with(Alert::TYPE_SUCCESS, 'Добавлена новая платформа');
        } catch (\Exception $e) {
            return back()->with(Alert::TYPE_ERROR, $e->getMessage())
                ->onlyInput('name');
        }
    }

    public function edit(FromPlatform $fromPlatform): View|Application|Factory
    {
        return view('pages.from-platforms.edit', compact('fromPlatform'));
    }

    public function update(UpdateRequest $request, FromPlatform $fromPlatform, UpdateFromPlatformAction $updateFromPlatformAction): RedirectResponse
    {
        try {
            $updateFromPlatformAction->handle(
                $request->validated(),
                $fromPlatform
            );

            return redirect()->route('settings.index')
                ->with(Alert::TYPE_SUCCESS, 'Платформа обновлена');
        } catch (\Exception $e) {
            return back()->with(Alert::TYPE_ERROR, $e->getMessage())
                ->onlyInput('name');
        }
    }

    public function delete(FromPlatform $fromPlatform, DeleteFromPlatformAction $deleteFromPlatformAction): RedirectResponse
    {
        $deleteFromPlatformAction->handle($fromPlatform);

        return redirect()->route('settings.index')
            ->with(Alert::TYPE_SUCCESS, 'Платформа удалена');
    }

    public function restore(FromPlatform $fromPlatform, RestoreFromPlatformAction $restoreFromPlatformAction): RedirectResponse
    {
        $restoreFromPlatformAction->handle($fromPlatform);

        return redirect()->route('settings.index')
            ->with(Alert::TYPE_SUCCESS, 'Платформа восстановлена');
    }

}
