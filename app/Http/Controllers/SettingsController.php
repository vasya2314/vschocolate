<?php

namespace App\Http\Controllers;

use App\Actions\Auth\LoginUserAction;
use App\Actions\TgNotification\GetTgChatIdAction;
use App\Actions\TgNotification\GetTgStatusAction;
use App\Actions\TgNotification\GetTgTokenAction;
use App\Actions\TgNotification\SetTgChatIdAction;
use App\Actions\TgNotification\SetTgStatusAction;
use App\Actions\TgNotification\SetTgTokenAction;
use App\Constants\TgStatus;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\TgSettings\TgSettingsRequest;
use App\Models\FromPlatform;
use App\Models\Product;
use App\View\Components\Alert;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(
        GetTgTokenAction $getTgTokenAction,
        GetTgChatIdAction $getTgChatIdAction,
        GetTgStatusAction $getTgStatusAction,
    ): Factory|Application|View
    {
        $fromPlatforms = FromPlatform::withTrashed()
            ->orderBy('deleted_at')
            ->latest()
            ->get();

        $products = Product::withTrashed()
            ->orderBy('deleted_at')
            ->latest()
            ->get();

        $token = $getTgTokenAction->handle();
        $chatId = $getTgChatIdAction->handle();
        $tgStatus = $getTgStatusAction->handle();
        $tgStatusValues = TgStatus::labels();

        return view('pages.settings', compact('fromPlatforms', 'products', 'token', 'chatId', 'tgStatus', 'tgStatusValues'));
    }

    public function saveTgSettings(
        TgSettingsRequest $request,
        SetTgChatIdAction $setTgChatIdAction,
        SetTgTokenAction $setTgTokenAction,
        SetTgStatusAction $setTgStatusAction,
    ): RedirectResponse
    {
        $data = $request->validated();

        try {
            if($token = $data['token']) $setTgTokenAction->handle($token);
            if($chatId = $data['chat_id']) $setTgChatIdAction->handle($chatId);
            if($tgStatus = $data['tg_status']) $setTgStatusAction->handle($tgStatus);

            return redirect()->route('settings.index')
                ->with(Alert::TYPE_SUCCESS, 'Настройки для TG установлены');
        } catch (\Exception $e) {
            return back()->with(Alert::TYPE_ERROR, $e->getMessage())
                ->withInput();
        }
    }
}
