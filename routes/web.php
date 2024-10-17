<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelegramBotController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Rota para o webhook do Telegram
Route::post('/telegram/webhook', [TelegramBotController::class, 'handleWebhook'])->name('telegram.webhook');


// Rota para verificar o nome
Route::post('/check-nome', [TelegramBotController::class, 'checkNome'])->name('check.nome');

