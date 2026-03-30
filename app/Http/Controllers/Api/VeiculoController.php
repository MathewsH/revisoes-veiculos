<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Veiculo;
use Illuminate\Http\Request;

class VeiculoController extends Controller
{
    public function index()
    {
        $veiculos = Veiculo::with(['usuario', 'cor'])->latest()->paginate(20);
        return response()->json($veiculos);
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'usuario_id'     => 'required|integer',
            'marca'          => 'required|string|max:80',
            'modelo'         => 'required|string|max:80',
            'placa'          => ['required', 'string', 'max:10',
                function ($a, $v, $f) {
                    if (Veiculo::where('placa', $v)->exists()) $f('Placa já cadastrada.');
                }
            ],
            'ano_fabricacao' => 'required|integer|min:1900|max:2100',
            'ano_modelo'     => 'required|integer|min:1900|max:2100',
            'cor_id'         => 'nullable|integer',
            'combustivel'    => 'nullable|string|max:20',
            'quilometragem'  => 'nullable|integer|min:0',
        ]);

        $veiculo = Veiculo::create($dados);
        return response()->json($veiculo->load(['cor', 'usuario']), 201);
    }

    public function show(string $id)
    {
        $veiculo = Veiculo::with(['usuario', 'cor', 'revisoes'])->findOrFail($id);
        return response()->json($veiculo);
    }

    public function update(Request $request, string $id)
    {
        $veiculo = Veiculo::findOrFail($id);

        $dados = $request->validate([
            'marca'          => 'sometimes|string|max:80',
            'modelo'         => 'sometimes|string|max:80',
            'placa'          => ['sometimes', 'string', 'max:10',
                function ($a, $v, $f) use ($id) {
                    if (Veiculo::where('placa', $v)->where('id', '!=', $id)->exists())
                        $f('Placa já cadastrada.');
                }
            ],
            'ano_fabricacao' => 'sometimes|integer|min:1900|max:2100',
            'ano_modelo'     => 'sometimes|integer|min:1900|max:2100',
            'cor_id'         => 'nullable|integer',
            'combustivel'    => 'nullable|string|max:20',
            'quilometragem'  => 'nullable|integer|min:0',
        ]);

        $veiculo->update($dados);
        return response()->json($veiculo->load(['cor', 'usuario']));
    }

    public function destroy(string $id)
    {
        $veiculo = Veiculo::findOrFail($id);
        $veiculo->delete();
        return response()->json(['message' => 'Veículo removido com sucesso.']);
    }
}