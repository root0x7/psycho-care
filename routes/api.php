<?php

use App\Http\Controllers\Api\TelegramController;
use Illuminate\Support\Facades\Route;

// Telegram Authentication (Login Widget callback)
Route::post('/auth/telegram', [TelegramController::class, 'login'])
    ->middleware(['throttle:10,1']);

// Telegram Bot Webhook
Route::post('/telegram/webhook', [TelegramController::class, 'webhook'])
    ->name('telegram.webhook');
