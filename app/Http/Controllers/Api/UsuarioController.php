<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    // Lista todos os usuários
    public function index()
    {
        $usuarios = Usuario::withCount('veiculos')
            ->whereNull('deleted_at')
            ->latest()
            ->paginate(20);
        return response()->json($usuarios);
    }

    // Cria um novo usuário
    public function store(Request $request)
    {
        $dados = $request->validate($this->regras());
        $dados['senha'] = Hash::make('123456');
        // $dados['senha'] = Hash::make($dados['senha']);
        $usuario = Usuario::create($dados);
        return response()->json($usuario, 201);
    }

    public function update(Request $request, string $id)
    {
        $usuario = Usuario::findOrFail($id);
        $dados = $request->validate($this->regras($id));
        if (isset($dados['senha'])) {
            $dados['senha'] = Hash::make($dados['senha']);
        }
        $usuario->update($dados);
        return response()->json($usuario);
    }

    // Exibe um usuário específico com seus veículos
    public function show(string $id)
    {
        $usuario = Usuario::with('veiculos')->findOrFail($id);
        return response()->json($usuario);
    }

    public function verificarCpf(string $cpf, Request $request)
    {
        $cpfLimpo = preg_replace('/\D/', '', $cpf);
        
        $query = Usuario::whereRaw("regexp_replace(cpf, '[^0-9]', '', 'g') = ?", [$cpfLimpo]);

        if ($request->query('ignorar_id')) {
            $query->where('id', '!=', $request->query('ignorar_id'));
        }

        return response()->json(['em_uso' => $query->exists()]);
    }

    private function regras(string $id = null): array
    {
        return [
            'nome'            => 'required|string|max:100',
            'sexo'            => 'required|in:M,F',
            'data_nascimento' => 'required|date|before:today|after:1800-01-01',
            'cpf' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($id) {
                    $cpf = preg_replace('/\D/', '', $value);
                    // Verifica duplicata
                    $query = Usuario::whereRaw("regexp_replace(cpf, '[^0-9]', '', 'g') = ?", [$cpf]);
                    if ($id) $query->where('id', '!=', $id);
                    if ($query->exists()) { $fail('Este CPF já está em uso.'); return; }
                    // Valida formato
                    if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
                        $fail('CPF inválido.'); return;
                    }
                    for ($t = 9; $t < 11; $t++) {
                        $sum = 0;
                        for ($c = 0; $c < $t; $c++) $sum += intval($cpf[$c]) * ($t + 1 - $c);
                        $d = ((10 * $sum) % 11) % 10;
                        if (intval($cpf[$t]) !== $d) { $fail('CPF inválido.'); return; }
                    }
                },
            ],
            'email' => [
                'required',
                'email',
                function ($attribute, $value, $fail) use ($id) {
                    $query = Usuario::where('email', $value);
                    if ($id) $query->where('id', '!=', $id);
                    if ($query->exists()) $fail('Este e-mail já está em uso.');
                },
            ],
            // 'senha'           => $id ? 'sometimes|string|min:6' : 'required|string|min:6',
            'senha'           => 'nullable|string|min:6',
            'tipo'            => 'sometimes|in:admin,comum',
            'telefone'        => 'nullable|string|max:20',
            'cep'             => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    $cep = preg_replace('/\D/', '', $value ?? '');
                    if ($value && strlen($cep) !== 8) {
                        $fail('CEP inválido.');
                    }
                },
            ],
            'rua'             => 'nullable|string|max:150',
            'numero'          => 'nullable|string|max:10',
            'bairro'          => 'nullable|string|max:80',
            'cidade'          => 'nullable|string|max:80',
            'estado'          => 'nullable|string|max:2',
        ];
    }

    // Remove um usuário (soft delete)
    public function destroy(string $id)
    {
        $usuario = Usuario::findOrFail($id);
        $usuario->delete();
        return response()->json(['message' => 'Usuário removido com sucesso.']);
    }
}