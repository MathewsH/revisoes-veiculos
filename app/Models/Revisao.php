<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Revisao extends Model
{
    use SoftDeletes;

    protected $table = 'mathews.revisoes';

    protected $fillable = [
        'usuario_id',
        'veiculo_id',
        'data_revisao',
        'quilometragem_revisao',
        'descricao',
        'valor_total',
        'oficina',
    ];

    protected $casts = [
        'data_revisao' => 'date',
        'valor_total'  => 'decimal:2',
    ];

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}