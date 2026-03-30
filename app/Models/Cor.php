<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Cor extends Model {
    protected $table    = 'mathews.cores';
    protected $fillable = ['nome'];

    public function veiculos() {
        return $this->hasMany(Veiculo::class, 'cor_id');
    }
}