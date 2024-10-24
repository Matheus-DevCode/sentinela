<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prisao extends Model
{
    use HasFactory;

    protected $fillable = [
        'fk_alvo',      // Chave estrangeira para a tabela de alvos
        'data_prisao',  // Data da prisão
        'motivo',       // Motivo da prisão
    ];

    // Você pode adicionar uma relação com Alvo se precisar
    public function alvo(): BelongsTo
    {
        return $this->belongsTo(Alvo::class, 'fk_alvo');
    }
}
