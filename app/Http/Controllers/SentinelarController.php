<?php

namespace App\Http\Controllers;

use App\Models\UsuarioTelegram;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class SentinelarController extends Controller
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
                $this->sendMessage($chatId, "Por favor, informe um número de telefone válido (ex: 91 999999999).");
            }
        } else {
            $this->sendMessage($chatId, "Digite /start para iniciar o cadastro.");
        }

    }

    private function requestPhoneNumber($chatId): void
    {
        $this->sendMessage($chatId, "Olá! Por favor, informe o seu número de telefone (ex: 91 999999999) para realizar o cadastro.");
        DB::table('telegram_user_states')->updateOrInsert(
            ['chat_id' => $chatId],
            ['status' => 'awaiting_phone', 'updated_at' => now()]
        );
    }

    private function isPhoneNumber($text): bool
    {
        return preg_match('/^\d{2}\s\d{5}\d{4}$/', trim($text));
    }

    private function registerUserTelegram($chatId, $phoneNumber): void
    {
        $phoneNumber = str_replace([' '], '', $phoneNumber);

        $usuario = DB::table('usuario_telegram')->where('telefone', $phoneNumber)->first();

        if (!$usuario) {
            $this->sendMessage($chatId, "Número de telefone não cadastrado no sistema ou telefone incorreto.");
            return;
        }

        $userTelegramExists = DB::table('usuario_telegram')
            ->where('id_telegram', $chatId)
            ->exists();
        if ($userTelegramExists) {
            $responseText = "Usuário já registrado com esse número de telefone.";
        } else {
            DB::table('usuario_telegram')->update([
                'id_telegram' => $chatId,
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

    public function usuarioSenti(): Collection
    {
        return DB::table('rastreamento.usuario_telegram as rut')
            ->join('seguranca.usuario as su',
                            'rut.fk_usuario',
                            '=',
                            'su.id')
            ->where('rut.status','=','Ativo')
            ->select(DB::raw("rut.id,
                                    rut.telefone,
                                    su.nome,
                                    CASE WHEN rut.id_telegram IS NULL THEN 'Desativado' ELSE 'Ativo' END AS status"))
            ->get();
    }

    public function alterarStatusUsuario($id): JsonResponse
    {
        $usuarioSentinela = UsuarioTelegram::find($id);
        if (!$usuarioSentinela) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }

        $usuarioSentinela->status = 'Desativado';
        $usuarioSentinela->save();

        return response()->json(['message' => 'Status alterado com sucesso']);

    }
}
