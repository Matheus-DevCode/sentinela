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
            $table->string('id_telegram')->unique(); // ID do Telegram
            $table->string('nome');
            $table->string('Telefone');
//            $table->unsignedBigInteger('fk_usuario');

//            $table->foreign('fk_usuario')->references('id')->on('usuario')->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_telegram');
    }
}
