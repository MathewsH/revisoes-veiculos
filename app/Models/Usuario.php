<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, SoftDeletes, Notifiable;

    protected $table = 'mathews.usuarios';

    protected $fillable = [
        'nome',
        'sexo',
        'data_nascimento',
        'cpf',
        'email',
        'senha',
        'tipo',
        'telefone',
        'cep',
        'rua',
        'numero',
        'bairro',
        'cidade',
        'estado',
    ];

    protected $hidden = [
        'senha',
        'remember_token',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
    ];

    // Diz ao Laravel que a senha está na coluna "senha" ao invés de "password"
    public function getAuthPassword()
    {
        return $this->senha;
    }

    // Um usuário tem muitos veículos
    public function veiculos()
    {
        return $this->hasMany(Veiculo::class, 'usuario_id');
    }

    // Verifica se o usuário é admin
    public function isAdmin(): bool
    {
        return $this->tipo === 'admin';
    }

    // Calcula a idade automaticamente
    public function getIdadeAttribute(): int
    {
        return $this->data_nascimento->age;
    }
}