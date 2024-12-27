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
            $table->string('nome');
            $table->foreignId('fk_usuario')->constrained('seguranca.usuario');
            $table->foreignId('fk_status')->constrained('rastreamento.status');
            $table->json('dados')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alvo');
    }
}
