<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mathews.veiculos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')
                  ->constrained('mathews.usuarios')
                  ->onDelete('cascade');
            $table->string('marca', 50);
            $table->string('modelo', 80);
            $table->string('placa', 10)->unique();
            $table->year('ano_fabricacao');
            $table->year('ano_modelo');
            $table->foreignId('cor_id')->nullable()->constrained('cores');
            $table->string('combustivel', 20)->default('gasolina');
            $table->integer('quilometragem')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mathews.veiculos');
    }
};