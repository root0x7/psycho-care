<?php

namespace App\Http\Controllers\Api;

use App\Actions\Auth\RegisterViaTelegramAction;
use App\Http\Controllers\Controller;
use App\Services\Telegram\TelegramAuthService;
use App\Telegram\Handlers\TelegramBotHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TelegramController extends Controller
{
    public function __construct(
        private TelegramAuthService $authService,
        private RegisterViaTelegramAction $registerAction,
        private TelegramBotHandler $botHandler
    ) {}

    /**
     * Handle Telegram Login Widget callback.
     * POST /api/auth/telegram
     */
    public function login(Request $request): JsonResponse
    {
        $data = $request->all();

        if (!$this->authService->verify($data)) {
            return response()->json(['error' => 'Invalid Telegram authentication data'], 403);
        }

        $role = $request->input('role', 'patient');
        if (!in_array($role, ['doctor', 'patient'])) {
            $role = 'patient';
        }

        $user = $this->registerAction->execute($data, $role);

        $token = $user->createToken('telegram-auth')->plainTextToken;

        return response()->json([
            'token'               => $token,
            'user'                => $user->only(['id', 'first_name', 'registration_status']),
            'registration_status' => $user->registration_status->value,
        ]);
    }

    /**
     * Handle Telegram bot webhook.
     * POST /api/telegram/webhook
     */
    public function webhook(Request $request): JsonResponse
    {
        $update = $request->all();

        // Process asynchronously to return 200 immediately
        dispatch(function () use ($update) {
            app(TelegramBotHandler::class)->handle($update);
        })->afterResponse();

        return response()->json(['ok' => true]);
    }
}
