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
        $usuario = DB::table('rastreamento.usuario_telegram')->where('telefone', $phoneNumber)->first();

        if (!$usuario) {
            $this->sendMessage($chatId, "Número de telefone não cadastrado no sistema ou telefone incorreto.");
            return;
        }

        // Verifica se o usuário já está registrado na tabela usuario_telegram
        $userTelegramExists = DB::table('rastreamento.usuario_telegram')
            ->where('id_telegram', $chatId)
            ->exists();

        if ($userTelegramExists) {
            $responseText = "Usuário já registrado com esse número de telefone.";
        } else {
            DB::table('rastreamento.usuario_telegram')->insert([
                'id_telegram' => $chatId,
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

    public function notifyResponsibleForArrestedTargets(): void
    {
        // Busca todos os alvos com status 2 (alvo preso)
        $alvosPresos = DB::table('alvo')
            ->where('fk_status', 2)
            ->get();

        // Array de unidades policiais para escolher aleatoriamente
        $unidadesPoliciais = [
            'Delegacia Central',
            'Delegacia Sul',
            'Delegacia Norte',
            'Delegacia Leste',
            'Delegacia Oeste',
        ];

        foreach ($alvosPresos as $alvo) {
            // Encontra o responsável pelo alvo (usuário)
            $usuario = DB::table('seguranca.usuario')
                ->where('id', $alvo->fk_usuario)
                ->first();

            // Verifica se o usuário possui um registro de Telegram na tabela usuario_telegram
            $usuarioTelegram = DB::table('usuario_telegram')
                ->where('id_telegram', $chatId)
                ->first();

            // Gerando dados aleatórios
            $boletim = 'BO' . mt_rand(100000, 999999); // Gera um número de boletim aleatório
            $dataPrisao = now()->subDays(mt_rand(1, 30))->toDateString(); // Gera uma data de prisão aleatória nos últimos 30 dias
            $unidadePolicial = $unidadesPoliciais[array_rand($unidadesPoliciais)]; // Seleciona uma unidade policial aleatória

            // Criar os dados a serem salvos
            $dados = [
                'boletim' => $boletim,
                'data_prisao' => $dataPrisao,
                'unidade_policial' => $unidadePolicial,
            ];

            // Salvar os dados na coluna 'dados' do alvo
            DB::table('alvo')
                ->where('id', $alvo->id)
                ->update(['dados' => json_encode($dados)]);

            // Envia mensagem para o Telegram do usuário, caso ele possua um chat_id registrado
            if ($usuarioTelegram) {
                $this->sendMessage($usuarioTelegram->id_telegram, "Nome: {$alvo->nome}\nBoletim: {$dados['boletim']}\nData da Prisão: {$dados['data_prisao']}\nUnidade Policial: {$dados['unidade_policial']}");
            } else {
                Log::warning("Usuário com ID {$usuario->id} não possui um registro de Telegram.");
            }
        }
    }


}
