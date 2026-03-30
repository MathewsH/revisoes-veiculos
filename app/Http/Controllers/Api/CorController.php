<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Cor;
use Illuminate\Http\Request;

class CorController extends Controller {
    public function index() {
        return response()->json(Cor::orderBy('nome')->get());
    }

    public function store(Request $request) {
        $request->validate(['nome' => 'required|string|max:50']);
        $nome = ucfirst(strtolower(trim($request->nome)));
        $cor  = Cor::firstOrCreate(['nome' => $nome]);
        return response()->json($cor, 201);
    }
}