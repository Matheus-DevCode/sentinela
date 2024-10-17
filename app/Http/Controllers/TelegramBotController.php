<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\BotDB;

class TelegramBotController extends Controller
{
    public function handleWebhook(Request $request): void
    {
        Log::info('Recebendo mensagem do Telegram', ['data' => $request->all()]);

        if ($request->has('message')) {
            $chatId = $request->input('message.chat.id');
            $receivedText = $request->input('message.text');

            if (strtolower($receivedText) === '/start') {
                $text = "Olá! Por favor, insira o nome da pessoa para verificação.";
                $this->sendMessage($chatId, $text); // Mover para enviar mensagem aqui
            } else {
                // Chama o método checkNome com o chatId e o nome recebido
                $this->checkNome($chatId, $receivedText);
            }
        } else {
            Log::warning('Nenhuma mensagem encontrada no payload');
        }
    }

    public function checkNome($chatId, $name): void
    {
        $pessoa = BotDB::where('name', $name)->first();

        if ($pessoa) {
            $this->sendMessage($chatId, "O cidadão " . $pessoa->name . " está cadastrado.");
        } else {
            $this->sendMessage($chatId, "Pessoa não cadastrada.");
        }
    }

    private function sendMessage($chatId, $text): void
    {
        $token = '7815578518:AAH7D5woM4L-LQXfZgCN21TbPsJ_WPPT_kc'; // Coloque seu token do bot
        $url = "https://api.telegram.org/bot{$token}/sendMessage";

        $data = [
            'chat_id' => $chatId,
            'text' => $text,
        ];

        $response = \Http::post($url, $data);

        // Verifica se a resposta foi bem-sucedida
        if ($response->successful()) {
            Log::info('Mensagem enviada com sucesso', ['chat_id' => $chatId, 'text' => $text]);
        } else {
            Log::error('Erro ao enviar mensagem', ['chat_id' => $chatId, 'response' => $response->json()]);
        }
    }
}
