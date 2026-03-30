<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Veiculo extends Model {
    use SoftDeletes;

    protected $table = 'mathews.veiculos';

    protected $fillable = [
        'usuario_id', 'marca', 'modelo', 'placa',
        'ano_fabricacao', 'ano_modelo', 'cor_id',
        'combustivel', 'quilometragem',
    ];

    public function usuario() {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
    public function cor() {
        return $this->belongsTo(Cor::class, 'cor_id');
    }
    public function revisoes() {
        return $this->hasMany(Revisao::class, 'veiculo_id');
    }
    public function ultimaRevisao() {
        return $this->hasOne(Revisao::class, 'veiculo_id')->latest('data_revisao');
    }
}