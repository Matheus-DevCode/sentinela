<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class NotifyUsers extends Command
{
    // Nome do comando que será executado pelo cron
    protected $signature = 'notify:users';

    // Descrição do comando
    protected $description = 'Envia mensagens automáticas para usuários';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {
        // Pegando todos os alvos da tabela 'alvo'
        $alvosPresos = DB::table('alvo')->select('nome')->get();

        // Array de unidades policiais para escolher aleatoriamente
        $unidadesPoliciais = [
            'Delegacia Central',
            'Delegacia Sul',
            'Delegacia Norte',
            'Delegacia Leste',
            'Delegacia Oeste',
        ];

        // Percorrendo todos os alvos
        foreach ($alvosPresos as $alvo) {
            // Busca o usuário correspondente ao fk_usuario do alvo
            $usuario = DB::table('seguranca.usuario')->where('id', $alvo->fk_usuario)->first();

            // Se o usuário não for encontrado, continua com o próximo alvo
            if (!$usuario) {
                Log::warning('Usuário não encontrado para o alvo', ['alvo_id' => $alvo->id, 'fk_usuario' => $alvo->fk_usuario]);
                continue;
            }

            // Agora buscamos o id_telegram na tabela usuario_telegram usando o id do usuário encontrado
            $usuarioTelegram = DB::table('rastreamento.usuario_telegram')
                ->where('fk_usuario', $usuario->id)
                ->first();

            // Se o id_telegram não for encontrado, continua com o próximo alvo
            if (!$usuarioTelegram || !isset($usuarioTelegram->id_telegram)) {
                Log::warning('Usuário Telegram não encontrado ou id_telegram não disponível', ['alvo_id' => $alvo->id, 'fk_usuario' => $alvo->fk_usuario]);
                continue;
            }

            // Obtemos o chatId diretamente da tabela usuario_telegram
            $chatId = $usuarioTelegram->id_telegram;

            // Gerando dados aleatórios
            $boletim = 'BO' . mt_rand(100000, 999999); // Número de boletim aleatório
            $dataPrisao = now()->subDays(mt_rand(1, 30))->toDateString(); // Data aleatória
            $unidadePolicial = $unidadesPoliciais[array_rand($unidadesPoliciais)]; // Unidade aleatória

            // Criar os dados a serem enviados
            $dados = [
                'boletim' => $boletim,
                'data_prisao' => $dataPrisao,
                'unidade_policial' => $unidadePolicial,
            ];

            // Formatar a mensagem
            $mensagem = $this->formatarMensagem($dados);

            // Enviar a mensagem
            $this->sendMessage($chatId, $mensagem);
        }
    }

    private function formatarMensagem(array $dados): string
    {
        // Formata a mensagem a ser enviada
        return "Informações sobre o alvo:\n"
            . "Boletim: {$dados['boletim']}\n"
            . "Data da prisão: {$dados['data_prisao']}\n"
            . "Unidade policial: {$dados['unidade_policial']}";
    }

    private function sendMessage($chatId, $text): void
    {
        // Usando variável de ambiente para o token
        $token = '7815578518:AAH7D5woM4L-LQXfZgCN21TbPsJ_WPPT_kc';
        $url = "https://api.telegram.org/bot{$token}/sendMessage"; // URL da API do Telegram para enviar mensagens

        // Fazendo a requisição POST
        $response = Http::post($url, [
            'chat_id' => $chatId,
            'text' => $text,
        ]);

        // Verifica se a resposta da requisição foi bem-sucedida
        if (!$response->successful()) {
            Log::error('Erro ao enviar mensagem', [
                'chat_id' => $chatId,
                'response' => $response->json(), // Exibe o erro retornado pela API
            ]);
        } else {
            Log::info('Mensagem enviada com sucesso', [
                'chat_id' => $chatId,
                'text' => $text, // Log da mensagem enviada
            ]);
        }
    }
}
