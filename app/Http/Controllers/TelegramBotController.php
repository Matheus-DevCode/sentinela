<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramBotController extends Controller
{
    public function handleWebhook(Request $request)
    {
        // Log para verificar se a mensagem está sendo recebida
        Log::info('Recebendo mensagem do Telegram', ['data' => $request->all()]);

        if ($request->has('message')) {
            $chatId = $request->input('message.chat.id');
            $text = "Olá! melhiante e ladrao.";

            // Enviar resposta
            $this->sendMessage($chatId, $text);
        } else {
            Log::warning('Nenhuma mensagem encontrada no payload');
        }
    }

    private function sendMessage($chatId, $text)
    {
        $token = '7815578518:AAH7D5woM4L-LQXfZgCN21TbPsJ_WPPT_kc'; // Coloque seu token do bot
        $url = "https://api.telegram.org/bot{$token}/sendMessage";

        $data = [
            'chat_id' => $chatId,
            'text' => $text,
        ];

        // Envia a requisição para o Telegram
        $response = \Http::post($url, $data);

        // Log da resposta para depuração
        Log::info('Resposta do Telegram', ['response' => $response->json()]);
    }
}
