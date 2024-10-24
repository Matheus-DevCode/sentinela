<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlvoTable extends Migration
{
    public function up(): void
    {
        Schema::create('alvo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fk_usuario')->references('id')->on('usuario');
            $table->string('nome_alvo');
            $table->string('status');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alvo');
    }
}
