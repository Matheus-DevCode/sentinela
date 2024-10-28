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
            $this->startConversation($chatId);
        } elseif ($userState && $userState->status === 'awaiting_phone') {
            if ($this->isPhoneNumber($text)) {
                $this->registerUserTelegram($chatId, $userState->name, $text);
                DB::table('telegram_user_states')->where('chat_id', $chatId)->delete(); // Limpar estado
            } else {
                $this->sendMessage($chatId, "Por favor, informe um número de telefone válido (ex: +5511999999999).");
            }
        } elseif ($this->isName($text)) {
            DB::table('telegram_user_states')->updateOrInsert(
                ['chat_id' => $chatId],
                ['name' => $text, 'status' => 'awaiting_phone', 'updated_at' => now()]
            );
            $this->requestPhoneNumber($chatId);
        } else {
            $this->sendMessage($chatId, "Por favor, informe seu nome completo.");
        }
    }

    private function startConversation($chatId): void
    {
        $this->sendMessage($chatId, "Olá! Por favor, informe seu nome completo (ex: João Silva) para iniciar o cadastro.");
        DB::table('telegram_user_states')->updateOrInsert(
            ['chat_id' => $chatId],
            ['status' => 'awaiting_name', 'updated_at' => now()]
        );
    }

    private function requestPhoneNumber($chatId): void
    {
        $this->sendMessage($chatId, "Agora, informe o seu número de telefone (ex: +5511999999999) para finalizar o cadastro.");
    }

    private function isPhoneNumber($text): bool
    {
        return preg_match('/^\+\d{11,15}$/', trim($text));
    }

    private function isName($text): bool
    {
        return preg_match('/^[\p{L}\s]+$/u', $text) && str_word_count($text) >= 2;
    }

    private function registerUserTelegram($chatId, $name, $phoneNumber): void
    {
        $existingUser = DB::table('usuario_telegram')
            ->where('id_telegram', $chatId)
            ->where('Telefone', $phoneNumber)
            ->exists();

        if ($existingUser) {
            $responseText = "Usuário já registrado com esse número de telefone.";
        } else {
            DB::table('usuario_telegram')->insert([
                'id_telegram' => $chatId,
                'nome' => $name,
                'Telefone' => $phoneNumber,
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






































































































//
//namespace App\Http\Controllers;
//
//use Illuminate\Http\JsonResponse;
//use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Log;
//use Illuminate\Support\Facades\Http;
//use Illuminate\Support\Facades\DB;
//use App\Models\UserTelegram;
//use App\Models\Usuario;
//
//class TelegramBotController extends Controller
//{
//    public function handleWebhook(Request $request): void
//    {
//        $responseText = $request->input("text");
//        $data = $request->all();
//        Log::info('Webhook recebido', ['data' => $data]);
//
//        if (isset($data['message']['text'])) {
//            $text = $data['message']['text'];
//            $chatId = $data['message']['chat']['id'];
//
//            Log::info('Leitura da sessão:', ['nome_temp' => session('nome_temp')]);
//            if (strtolower($text) === '/start') {
//                $this->startConversation($chatId);
//                return;
//            } elseif (session('nome_temp')) {
//                $nomeTemp = session('nome_temp');
//                Log::info('Nome na sessão:', ['nome_temp' => $nomeTemp]);
//
//                if ($this->isPhoneNumber($text)) {
//                    session(['telefone_temp' => $text]);
//                    Log::info('Telefone na sessão:', ['telefone_temp' => $text]);
//                    Log::info('Nome_temp:', ['text' => $nomeTemp]);
//                    $usuario = Usuario::where('nome', $nomeTemp)
//                        ->where('telefone', $text)
//                        ->first();
//
//                    if ($usuario) {
//                        if (!DB::table('usuario_telegram')
//                            ->where('id_telegram', $chatId)
//                            ->where('fk_usuario', $usuario->id)
//                            ->exists()) {
//                            $this->registerUserTelegram($chatId, $usuario->id);
//                            $responseText = "Usuário cadastrado com sucesso!";
//                        } else {
//                            $responseText = "Usuário já está registrado.";
//                        }
//                    } else {
//                        $responseText = "Telefone não encontrado ou não corresponde ao nome fornecido. Verifique e tente novamente.";
//                    }
//
//                    session()->forget(['nome_temp', 'telefone_temp']);
//                } else {
//                    $responseText = "Por favor, insira um número de telefone válido (ex: +5511999999999).";
//                }
//            } elseif ($this->isName($text)) {
//                Log::info('Nome válido recebido:', ['nome' => $text]);
//                $usuario = Usuario::where('nome', $text)->first();
//
//                if ($usuario) {
//                    session(['nome_temp' => $text]);
//                    Log::info('Nome armazenado na sessão:', ['nome_temp' => session('nome_temp')]);
//                    Log::info('Cadastro da sessão:', ['nome_temp' => session('nome_temp')]);
//
//                    $responseText = "Obrigado, agora insira o número de telefone (ex: +5511999999999) para continuar.";
//                } else {
//                    $responseText = "Nome não encontrado. Verifique se está escrito corretamente ou se o usuário já está cadastrado.";
//                }
//            } else {
//                log::error(response()->json(['text' => $responseText]));
//                $responseText = "Por favor, informe seu nome completo.";
//            }
//
//            if (isset($responseText)) {
//                Log::info('Enviando resposta para o chat ID:', ['chat_id' => $chatId, 'response_text' => $responseText]);
//                $this->sendMessage($chatId, $responseText);
//            } else {
//                Log::error('Nenhuma resposta definida para enviar.', ['data' => $data]);
//            }
//        } else {
//            Log::error('Texto não encontrado na mensagem recebida', ['data' => $data]);
//        }
//    }
//
//
//    private function startConversation($chatId): void
//    {
//        $text = "Olá! Por favor, informe seu nome completo (ex: João Silva).";
//        $this->sendMessage($chatId, $text);
//    }
//
//    private function isPhoneNumber($text): bool
//    {
//        return preg_match('/^\+\d{11,15}$/', trim($text));
//    }
//
//    private function isName($text): bool
//    {
//        return preg_match('/^[\p{L}\s]+$/u', $text) && str_word_count($text) >= 2;
//    }
//
//    private function registerUserTelegram($telegramId, $usuarioId): void
//    {
//        DB::table('usuario_telegram')->insert([
//            'id_telegram' => $telegramId,
//            'fk_usuario' => $usuarioId,
//            'created_at' => now(),
//            'updated_at' => now(),
//        ]);
//    }
//
//    private function sendMessage($chatId, $text): void
//    {
//        $token = '7815578518:AAH7D5woM4L-LQXfZgCN21TbPsJ_WPPT_kc';
//        $url = "https://api.telegram.org/bot{$token}/sendMessage";
//
//        $response = Http::post($url, [
//            'chat_id' => $chatId,
//            'text' => $text,
//        ]);
//
//        if (!$response->successful()) {
//            Log::error('Erro ao enviar mensagem', ['chat_id' => $chatId, 'response' => $response->json()]);
//        } else {
//            Log::info('Mensagem enviada com sucesso', ['chat_id' => $chatId, 'text' => $text]);
//        }
//    }
//}
