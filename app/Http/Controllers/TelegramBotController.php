<?php
namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\UserTelegram;
use App\Models\Usuario;

class TelegramBotController extends Controller
{
    public function handleWebhook(Request $request): void
    {
        $data = $request->all();
        Log::info('Webhook recebido', ['data' => $data]);

        if (isset($data['message']['text'])) {
            $text = $data['message']['text'];
            $chatId = $data['message']['chat']['id'];

            // Inicia a conversa ao pressionar /start
            if (strtolower($text) === '/start') {
                $this->startConversation($chatId);

            }

            // Verifica se já temos um nome armazenado na sessão
            if ($nomeTemp = session('nome_temp')) {
                if ($this->isPhoneNumber($text)) {
                    $usuario = Usuario::where('nome', $nomeTemp)
                        ->where('telefone', $text)
                        ->first();
                    if ($usuario) {
                        $this->registerUserTelegram($chatId, $usuario->id);
                        $responseText = "Cadastro realizado com sucesso!";
                    } else {
                        $responseText = "Usuário não encontrado. Verifique o nome e telefone e tente novamente.";
                    }

                    // Limpa as sessões temporárias
                    session()->forget('nome_temp');
                } else {
                    $responseText = "Por favor, insira um número de telefone válido (ex: +5511999999999).";
                }

            } else {
                if ($this->isName($text)) {
                    session(['nome_temp' => $text]);
                    $responseText = "Obrigado, agora por favor insira seu número de telefone (ex: +5511999999999).";
                } else {
//                    log::error($text);
                    $responseText = "Por favor, informe seu nome completo.";
                }
            }

            // Envia a resposta para o Telegram
            $this->sendMessage($chatId, $responseText);
        }
    }

    private function startConversation($chatId): void
    {
        $text = "Olá! Por favor, informe seu nome completo (ex: João Silva).";
        $this->sendMessage($chatId, $text);
    }

    private function isPhoneNumber($text): bool
    {
        return preg_match('/^\+\d{11,15}$/', $text);
    }

    private function isName($text): bool
    {
        return preg_match('/^[a-zA-Z\s]+$/', $text) && str_word_count($text) >= 2;
    }

    private function registerUserTelegram($telegramId, $usuarioId): void
    {
        DB::table('usuario_telegram')->insert([
            'id_telegram' => $telegramId,
            'fk_usuario' => $usuarioId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
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
        }
    }
}




















































































//
//namespace App\Http\Controllers;
//
//use Illuminate\Http\JsonResponse;
//use Illuminate\Http\Request;
//use Illuminate\Support\Facades\DB;
//use Illuminate\Support\Facades\Log;
//use App\Models\UserTelegram;
//use App\Models\Alvo;
//use App\Models\Prisao;
//
//class TelegramBotController extends Controller
//{
//    public function handleWebhook(Request $request): void
//    {
//        Log::info('Recebendo mensagem do Telegram', ['data' => $request->all()]);
//
//        if ($request->has('message')) {
//            $chatId = $request->input('message.chat.id');
//            $receivedText = $request->input('message.text');
//            $userName = $request->input('message.from.first_name'); // Captura o nome do usuário
//
//            // Verifica se o usuário já está registrado
//            $user = UserTelegram::where('id_telegram', $chatId)->first();
//
//            if (strtolower($receivedText) === '/start') {
//                $this->startConversation($chatId);
//            } elseif (is_null($user)) {
//                // Solicita o número de telefone se o usuário não estiver registrado
//                if ($this->isPhoneNumber($receivedText)) {
////                    $this->registerUser($chatId, $userName, $receivedText); // Passa o nome para o registro
//                } else {
//                    $this->sendMessage($chatId, "Por favor, insira um número de telefone válido (ex: +5511912345678).");
//                }
//            } else {
//                // O usuário já está registrado
//                $this->sendMessage($chatId, "Seu número já está cadastrado: " . $user->Telefone);
//            }
//        } else {
//            Log::warning('Nenhuma mensagem encontrada no payload');
//        }
//    }
//
//    private function startConversation($chatId): void
//    {
//        $text = "Olá! Por favor, insira o seu número de telefone (ex: +5511912345678).";
//        $this->sendMessage($chatId, $text);
//    }
//
//    private function isPhoneNumber($text): bool
//    {
//        return preg_match('/^\+\d{1,3}\d{8,}$/', $text);
//    }
//
//    private function registerUser($chatId, $nome, $numero): void
//    {
//        // Atualiza ou cria o usuário na tabela user_telegrams
//        try {
//            $user = UserTelegram::updateOrCreate(
//                ['id_telegram' => $chatId],
//                ['Telefone' => $numero, 'nome' => $nome] // Adiciona o nome ao registro
//            );
//
//            // Verifica se o usuário foi recém-criado
//            if ($user->wasRecentlyCreated) {
//                // Mensagem de sucesso
//                $this->sendMessage($chatId, "Seu número de telefone foi registrado com sucesso!");
//
//            } else {
//                $this->sendMessage($chatId, "Seu número de telefone já está registrado.");
//            }
//
//        } catch (\Exception $e) {
//            dd($e->getMessage());
//        }
//    }
//
//
//    public function checkNome($chatId, $nome_alvo): void
//    {
//        $alvo = Alvo::where('nome_alvo', $nome_alvo)->first();
//
//        if ($alvo) {
//            $prisao = Prisao::where('fk_alvo', $alvo->id)->first();
//
//            if ($prisao) {
//                $this->sendMessage($chatId, "O alvo {$alvo->nome_alvo} está preso. Motivo: {$prisao->motivo}.");
//            } else {
//                $this->sendMessage($chatId, "O alvo {$alvo->nome_alvo} está livre.");
//            }
//        } else {
//            $this->sendMessage($chatId, "Alvo não encontrado.");
//        }
//    }
//
//    private function sendMessage($chatId, $text): void
//    {
//        $token = env('7815578518:AAH7D5woM4L-LQXfZgCN21TbPsJ_WPPT_kc'); // Coloque seu token do bot no .env
//        $url = "https://api.telegram.org/bot{$token}/sendMessage";
//
//        $data = [
//            'chat_id' => $chatId,
//            'text' => $text,
//        ];
//
//        $response = \Http::post($url, $data);
//
//        if ($response->successful()) {
//            Log::info('Mensagem enviada com sucesso', ['chat_id' => $chatId, 'text' => $text]);
//        } else {
//            Log::error('Erro ao enviar mensagem', ['chat_id' => $chatId, 'response' => $response->json()]);
//        }
//    }
//
////     Novo método para enviar mensagem para um usuário específico
//    public function sendMessageToUser($userId, $message): void
//    {
//        $user = UserTelegram::find($userId);
//
//        if ($user) {
//            $this->sendMessage($user->id_telegram, $message);
//        } else {
//            Log::warning("Usuário com ID {$userId} não encontrado.");
//        }
//    }
//
//    public function exampleSendMessage(Request $request): JsonResponse
//    {
//        $userId = $request->input('user_id'); // ID do usuário que você deseja enviar a mensagem
//        $message = $request->input('message', "Esta é uma mensagem de teste!"); // Mensagem padrão, caso não seja fornecida
//
//        // Chama o método para enviar a mensagem
//        $this->sendMessageToUser($userId, $message);
//
//        return response()->json(['success' => true, 'message' => 'Mensagem enviada com sucesso!']);
//    }
//}
