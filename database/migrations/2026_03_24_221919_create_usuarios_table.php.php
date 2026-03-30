<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mathews.usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100);
            $table->enum('sexo', ['M', 'F']);
            $table->date('data_nascimento');
            $table->string('cpf', 14)->unique();
            $table->string('email', 100)->unique();
            $table->string('senha', 255); // para login
            $table->enum('tipo', ['admin', 'comum'])->default('comum');
            $table->string('telefone', 20)->nullable();
            $table->string('cep', 10)->nullable();
            $table->string('rua', 150)->nullable();
            $table->string('numero', 10)->nullable();
            $table->string('bairro', 80)->nullable();
            $table->string('cidade', 80)->nullable();
            $table->string('estado', 2)->nullable();
            $table->rememberToken(); // para manter login ativo
            $table->timestamps();
            $table->softDeletes();
        });

        \Illuminate\Support\Facades\DB::table('mathews.usuarios')->insert([
            [
                'nome'            => 'Mathews Admin',
                'sexo'            => 'M',
                'data_nascimento' => '2003-01-01',
                'cpf'             => '000.000.000-00',
                'email'           => 'admin@admin.com',
                'senha'           => Hash::make('123456'),
                'tipo'            => 'admin',
                'telefone'        => '(62) 99999-9999',
                'cidade'          => 'Goiânia',
                'estado'          => 'GO',
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'nome'            => 'José Gabriel',
                'sexo'            => 'M',
                'data_nascimento' => '2001-05-15',
                'cpf'             => '111.111.111-11',
                'email'           => 'jose@email.com',
                'senha'           => Hash::make('123456'),
                'tipo'            => 'comum',
                'telefone'        => '(67) 99999-1111',
                'cidade'          => 'Dourados',
                'estado'          => 'MS',
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'nome'            => 'Hera Delboni',
                'sexo'            => 'F',
                'data_nascimento' => '1999-08-22',
                'cpf'             => '222.222.222-22',
                'email'           => 'hera@email.com',
                'senha'           => Hash::make('123456'),
                'tipo'            => 'comum',
                'telefone'        => '(67) 99999-2222',
                'cidade'          => 'Dourados',
                'estado'          => 'MS',
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'nome'            => 'Alisson Canato',
                'sexo'            => 'M',
                'data_nascimento' => '2000-11-10',
                'cpf'             => '333.333.333-33',
                'email'           => 'alisson@email.com',
                'senha'           => Hash::make('123456'),
                'tipo'            => 'comum',
                'telefone'        => '(67) 99999-3333',
                'cidade'          => 'Dourados',
                'estado'          => 'MS',
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'nome'            => 'André Chastel Lima',
                'sexo'            => 'M',
                'data_nascimento' => '1985-03-30',
                'cpf'             => '444.444.444-44',
                'email'           => 'andre@email.com',
                'senha'           => Hash::make('123456'),
                'tipo'            => 'comum',
                'telefone'        => '(67) 99999-4444',
                'cidade'          => 'Dourados',
                'estado'          => 'MS',
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'nome'            => 'Mariana Costa',
                'sexo'            => 'F',
                'data_nascimento' => '1995-07-12',
                'cpf'             => '555.555.555-55',
                'email'           => 'mariana@email.com',
                'senha'           => Hash::make('123456'),
                'tipo'            => 'comum',
                'telefone'        => '(67) 99999-5555',
                'cidade'          => 'Campo Grande',
                'estado'          => 'MS',
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'nome'            => 'Lucas Ferreira',
                'sexo'            => 'M',
                'data_nascimento' => '1998-02-25',
                'cpf'             => '666.666.666-66',
                'email'           => 'lucas@email.com',
                'senha'           => Hash::make('123456'),
                'tipo'            => 'comum',
                'telefone'        => '(67) 99999-6666',
                'cidade'          => 'Três Lagoas',
                'estado'          => 'MS',
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'nome'            => 'Camila Rocha',
                'sexo'            => 'F',
                'data_nascimento' => '2002-09-05',
                'cpf'             => '777.777.777-77',
                'email'           => 'camila@email.com',
                'senha'           => Hash::make('123456'),
                'tipo'            => 'comum',
                'telefone'        => '(67) 99999-7777',
                'cidade'          => 'Dourados',
                'estado'          => 'MS',
                'created_at'      => now(),
                'updated_at'      => now(),
            ]
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('mathews.usuarios');
    }
};