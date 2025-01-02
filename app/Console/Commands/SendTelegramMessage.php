<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SendTelegramMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-telegram-message {chatId} {message}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia uma mensagem para um usuário do Telegram';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // Obtém os argumentos do comando
        $chatId = $this->argument('chatId');
        $message = $this->argument('message');
        $token = '7582105005:AAFYllvqbuCGnPjJKSzxGhIhR9lYXa-YlFM'; // Substitua pelo seu token

        // URL da API do Telegram para enviar mensagem
        $url = "https://api.telegram.org/bot{$token}/sendMessage";

        // Faz a requisição para enviar a mensagem
        $response = Http::post($url, [
            'chat_id' => $chatId,
            'text' => $message,
        ]);

        // Verifica se a mensagem foi enviada com sucesso
        if ($response->successful()) {
            $this->info('Mensagem enviada com sucesso!');
        } else {
            $this->error('Erro ao enviar mensagem: ' . $response->body());
            // Adicionando informações adicionais para depuração
            $this->error('Status Code: ' . $response->status());
            $this->error('Response Body: ' . $response->body());
        }
    }
//        comando pra enviar a mensagem
//        php artisan app:send-telegram-message 123456789 'Olá, isso é um teste!'
}
