<?php


use App\Http\Controllers\TelegramBotController;
use Illuminate\Support\Facades\Route;



// Rota para lidar com o webhook do Telegram
Route::post('/telegram-webhook', [TelegramBotController::class, 'handleWebhook']);

// Rota para enviar mensagem de exemplo
Route::post('/telegram/send-message', [TelegramBotController::class, 'exampleSendMessage']);


Route::post('/telegram-webhook', [SentinelarController::class, 'handleWebhook']);
Route::post('/telegram/send-message', [SentinelarController::class, 'exampleSendMessage']);
