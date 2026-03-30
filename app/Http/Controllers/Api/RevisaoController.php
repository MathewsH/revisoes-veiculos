<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Revisao;
use Illuminate\Http\Request;
use App\Jobs\EnviarLembreteRevisao;

class RevisaoController extends Controller
{
    public function index()
    {
        $revisoes = Revisao::with(['veiculo', 'usuario'])->latest()->paginate(20);
        return response()->json($revisoes);
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'usuario_id'            => 'required|integer',
            'veiculo_id'            => 'required|integer', 
            'data_revisao'          => 'required|date',
            'quilometragem_revisao' => 'required|integer|min:0',
            'descricao'             => 'nullable|string',
            'valor_total'           => 'nullable|numeric|min:0|max:2000000',
            'oficina'               => 'nullable|string|max:100',
        ]);

        $revisao = Revisao::create($dados);

        return response()->json($revisao->load(['veiculo', 'usuario']), 201);
    }

    public function show(string $id)
    {
        $revisao = Revisao::with(['veiculo', 'usuario'])->findOrFail($id);
        return response()->json($revisao);
    }

    public function update(Request $request, string $id)
    {
        $revisao = Revisao::findOrFail($id);

        $dados = $request->validate([
            'usuario_id'            => 'sometimes|integer',
            'veiculo_id'            => 'sometimes|integer', 
            'data_revisao'          => 'sometimes|date',
            'quilometragem_revisao' => 'sometimes|integer|min:0',
            'descricao'             => 'nullable|string',
            'valor_total'           => 'nullable|numeric|min:0|max:2000000',
            'oficina'               => 'nullable|string|max:100',
        ]);

        $revisao->update($dados);

        return response()->json($revisao->load(['veiculo', 'usuario']));
    }

    public function destroy(string $id)
    {
        $revisao = Revisao::findOrFail($id);
        $revisao->delete();
        return response()->json(['message' => 'Revisão removida com sucesso.']);
    }

    public function dispararLembretes()
    {
        $revisoes = Revisao::with(['veiculo', 'usuario'])->get();
        $contador = 0;

        foreach ($revisoes as $revisao) {
            EnviarLembreteRevisao::dispatch($revisao);
            $contador++;
        }

        return response()->json([
            'message' => "{$contador} lembretes foram colocados na fila de envio com sucesso!"
        ]);
    }
}