<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\TelegramBotController;

class NotifyResponsibleForArrestedTargets extends Command
{
    // Nome e descrição do comando
    protected $signature = 'notify:arrested-targets';
    protected $description = 'Notifica os responsáveis por alvos presos via Telegram';

    public function __construct()
    {
        parent::__construct();
    }

//    comando pra ativar: php artisan notify:arrested-targets


    public function handle(): void
    {
        // Instancia o controlador e chama o método de notificação
        $telegramBotController = new TelegramBotController();
        $telegramBotController->notifyResponsibleForArrestedTargets();

        $this->info('Notificação enviada para os responsáveis por alvos presos.');
    }
}
