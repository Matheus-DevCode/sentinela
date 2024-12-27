<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsuarioTelegramTable extends Migration
{
    public function up(): void
    {
        Schema::create('usuario_telegram', function (Blueprint $table) {
            $table->id();
            $table->string('id_telegram')->nullable();
            $table->string('telefone');
            $table->foreignId('fk_usuario')->constrained('seguranca.usuario','id');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_telegram');
    }
}
