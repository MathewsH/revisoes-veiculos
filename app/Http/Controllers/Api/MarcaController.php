<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Marca;
use Illuminate\Http\Request;

class MarcaController extends Controller {
    public function index() {
        return response()->json(Marca::orderBy('nome')->get());
    }

    public function store(Request $request) {
        $request->validate(['nome' => 'required|string|max:80']);
        $nome  = ucfirst(strtolower(trim($request->nome)));
        $marca = Marca::firstOrCreate(['nome' => $nome]);
        return response()->json($marca, 201);
    }
}