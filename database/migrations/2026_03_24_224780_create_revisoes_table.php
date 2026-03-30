<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mathews.revisoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')
                  ->constrained('mathews.usuarios')
                  ->onDelete('cascade');
            $table->foreignId('veiculo_id')
                  ->constrained('mathews.veiculos')
                  ->onDelete('cascade');
            $table->date('data_revisao');
            $table->integer('quilometragem_revisao');
            $table->text('descricao')->nullable();
            $table->decimal('valor_total', 10, 2)->default(0);
            $table->string('oficina', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mathews.revisoes');
    }
};