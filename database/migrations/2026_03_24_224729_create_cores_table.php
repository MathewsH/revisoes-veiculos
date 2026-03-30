<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('mathews.cores', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 50)->unique();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\DB::table('mathews.cores')->insert([
            ['nome' => 'Branco',    'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Preto',     'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Prata',     'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Cinza',     'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Vermelho',  'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Azul',      'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Verde',     'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Amarelo',   'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Laranja',   'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Bege',      'created_at' => now(), 'updated_at' => now()],
        ]);
    }
    public function down(): void {
        Schema::dropIfExists('mathews.cores');
    }
};