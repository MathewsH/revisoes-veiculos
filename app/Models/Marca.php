<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Marca extends Model {
    protected $table    = 'mathews.marcas';
    protected $fillable = ['nome'];

    public function veiculos() {
        return $this->hasMany(Veiculo::class, 'marca_id');
    }
}