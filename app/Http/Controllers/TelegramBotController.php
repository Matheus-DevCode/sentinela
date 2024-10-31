<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class TelegramBotController extends Controller
{
    public function handleWebhook(Request $request): void
    {
        $data = $request->all();
        Log::info('Recebendo webhook:', ['data' => $data]);

        $text = $data['message']['text'] ?? null;
        $chatId = $data['message']['chat']['id'] ?? null;

        if (!$text || !$chatId) {
            Log::error('Erro: Texto ou chat ID não encontrados na mensagem recebida', ['data' => $data]);
            return;
        }

        $userState = DB::table('telegram_user_states')->where('chat_id', $chatId)->first();

        if (strtolower($text) === '/start') {
            $this->requestPhoneNumber($chatId);
        } elseif ($userState && $userState->status === 'awaiting_phone') {
            if ($this->isPhoneNumber($text)) {
                $this->registerUserTelegram($chatId, $text);
                DB::table('telegram_user_states')->where('chat_id', $chatId)->delete(); // Limpar estado
            } else {
                $this->sendMessage($chatId, "Por favor, informe um número de telefone válido (ex: +5511999999999).");
            }
        } else {
            $this->sendMessage($chatId, "Digite /start para iniciar o cadastro.");
        }
    }

    private function requestPhoneNumber($chatId): void
    {
        $this->sendMessage($chatId, "Olá! Por favor, informe o seu número de telefone (ex: +5511999999999) para realizar o cadastro.");
        DB::table('telegram_user_states')->updateOrInsert(
            ['chat_id' => $chatId],
            ['status' => 'awaiting_phone', 'updated_at' => now()]
        );
    }

    private function isPhoneNumber($text): bool
    {
        return preg_match('/^\+\d{11,15}$/', trim($text));
    }

    private function registerUserTelegram($chatId, $phoneNumber): void
    {
        // Verifica se o telefone existe na tabela usuario
        $usuario = DB::table('usuario')->where('telefone', $phoneNumber)->first();

        if (!$usuario) {
            $this->sendMessage($chatId, "Número de telefone não cadastrado no sistema ou telefone incorreto.");
            return;
        }

        // Verifica se o usuário já está registrado na tabela usuario_telegram
        $userTelegramExists = DB::table('usuario_telegram')
            ->where('id_telegram', $chatId)
            ->where('Telefone', $phoneNumber)
            ->exists();

        if ($userTelegramExists) {
            $responseText = "Usuário já registrado com esse número de telefone.";
        } else {
            DB::table('usuario_telegram')->insert([
                'id_telegram' => $chatId,
                'Telefone' => $phoneNumber,
                'fk_usuario' => $usuario->id, // A variável $usuario agora está definida
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $responseText = "Usuário registrado com sucesso!";
        }

        $this->sendMessage($chatId, $responseText);
    }

    private function sendMessage($chatId, $text): void
    {
        $token = '7815578518:AAH7D5woM4L-LQXfZgCN21TbPsJ_WPPT_kc';
        $url = "https://api.telegram.org/bot{$token}/sendMessage";

        $response = Http::post($url, [
            'chat_id' => $chatId,
            'text' => $text,
        ]);

        if (!$response->successful()) {
            Log::error('Erro ao enviar mensagem', ['chat_id' => $chatId, 'response' => $response->json()]);
        } else {
            Log::info('Mensagem enviada com sucesso', ['chat_id' => $chatId, 'text' => $text]);
        }
    }
}
