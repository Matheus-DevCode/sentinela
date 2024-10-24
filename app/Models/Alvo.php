<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alvo extends Model
{
    use HasFactory;

    protected $fillable = [
        'fk_usuario',
        'nome_alvo',
        'status',
    ];
}
