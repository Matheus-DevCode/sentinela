<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTelegram extends Model
{
    use HasFactory;

    protected $table = 'usuario_telegram'; // Nome da tabela

    protected $fillable = [
        'id_telegram', // ID do chat
        'Telefone',      // Número de telefone
        'fk_usuario',        // Nome do usuário
    ];
}
