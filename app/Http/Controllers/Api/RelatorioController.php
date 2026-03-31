<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Veiculo;
use App\Models\Revisao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RelatorioController extends Controller
{
    // ==================== VEÍCULOS ====================

    // Todos os veículos
    public function todosVeiculos()
    {
        $dados = Cache::remember('relatorio.veiculos.todos', 300, function () {
            return Veiculo::with('usuario')
                ->select('mathews.veiculos.*')
                ->get()
                ->toArray();
        });

        return response()->json($dados);
    }

    // Todos os veículos por pessoa ordenado por nome
    public function veiculosPorPessoa()
    {
        $dados = Cache::remember('relatorio.veiculos.por_pessoa', 300, function () {
            return Usuario::with('veiculos')
                ->withCount('veiculos')
                ->orderBy('nome')
                ->get()
                ->toArray();
        });

        return response()->json($dados);
    }

    // Quem tem mais veículos: homens ou mulheres
    public function veiculosPorGenero()
    {
        $dados = Cache::remember('relatorio.veiculos.genero', 300, function () {
            return DB::select("
                SELECT
                    u.sexo,
                    CASE u.sexo WHEN 'M' THEN 'Masculino' ELSE 'Feminino' END AS genero,
                    COUNT(v.id) AS total_veiculos
                FROM mathews.usuarios u
                LEFT JOIN mathews.veiculos v ON v.usuario_id = u.id AND v.deleted_at IS NULL
                WHERE u.deleted_at IS NULL
                GROUP BY u.sexo
                ORDER BY total_veiculos DESC
            ");
        });

        return response()->json($dados);
    }

    // Todas as marcas ordenadas pelo número de veículos
    public function marcasOrdenadas()
    {
        $dados = Cache::remember('relatorio.veiculos.marcas', 300, function () {
            return DB::select("
                SELECT
                    marca,
                    COUNT(*) AS total
                FROM mathews.veiculos
                WHERE deleted_at IS NULL
                GROUP BY marca
                ORDER BY total DESC
            ");
        });

        return response()->json($dados);
    }

    // Totais de marcas separados entre homens e mulheres
    public function marcasPorGenero()
    {
        $dados = Cache::remember('relatorio.veiculos.marcas_genero', 300, function () {
            return DB::select("
                SELECT
                    v.marca,
                    CASE u.sexo WHEN 'M' THEN 'Masculino' ELSE 'Feminino' END AS genero,
                    COUNT(v.id) AS total
                FROM mathews.veiculos v
                INNER JOIN mathews.usuarios u ON u.id = v.usuario_id
                WHERE v.deleted_at IS NULL AND u.deleted_at IS NULL
                GROUP BY v.marca, u.sexo
                ORDER BY total DESC
            ");
        });

        return response()->json($dados);
    }

    // ==================== PESSOAS ====================

    // Todas as pessoas
    public function todasPessoas()
    {
        $dados = Cache::remember('relatorio.pessoas.todas', 300, function () {
            return Usuario::withCount('veiculos')
                ->orderBy('nome')
                ->get()
                ->toArray();
        });

        return response()->json($dados);
    }

    // Pessoas separadas por gênero com idade média
    public function pessoasPorGenero()
    {
        $dados = Cache::remember('relatorio.pessoas.genero', 300, function () {
            return DB::select("
                SELECT
                    CASE sexo WHEN 'M' THEN 'Masculino' ELSE 'Feminino' END AS genero,
                    COUNT(*) AS total,
                    -- Arredonda e força a conversão para Inteiro para sumir com as casas decimais
                    CAST(ROUND(AVG(
                        EXTRACT(YEAR FROM AGE(CURRENT_DATE, data_nascimento))
                    )) AS INTEGER) AS idade_media
                FROM mathews.usuarios
                WHERE deleted_at IS NULL
                GROUP BY sexo
                ORDER BY genero
            ");
        });

        return response()->json($dados);
    }

    // ==================== REVISÕES ====================

    // Revisões dentro de um período
    public function revisoesPorPeriodo(Request $request)
    {
        $request->validate([
            'data_inicio' => 'required|date',
            'data_fim'    => 'required|date|after_or_equal:data_inicio',
        ]);

        $dados = Revisao::with('veiculo.usuario')
            ->whereBetween('data_revisao', [
                $request->data_inicio,
                $request->data_fim
            ])
            ->orderBy('data_revisao')
            ->get();

        return response()->json($dados);
    }

    // Marcas com maior número de revisões
    public function marcasComMaisRevisoes()
    {
        $dados = Cache::remember('relatorio.revisoes.marcas', 300, function () {
            return DB::select("
                SELECT
                    v.marca,
                    COUNT(r.id) AS total_revisoes
                FROM mathews.revisoes r
                INNER JOIN mathews.veiculos v ON v.id = r.veiculo_id
                WHERE r.deleted_at IS NULL AND v.deleted_at IS NULL
                GROUP BY v.marca
                ORDER BY total_revisoes DESC
            ");
        });

        return response()->json($dados);
    }

    // Pessoas com maior número de revisões
    public function pessoasComMaisRevisoes()
    {
        $dados = Cache::remember('relatorio.revisoes.pessoas', 300, function () {
            return DB::select("
                SELECT
                    u.nome,
                    u.sexo,
                    COUNT(r.id) AS total_revisoes
                FROM mathews.revisoes r
                INNER JOIN mathews.veiculos v ON v.id = r.veiculo_id
                INNER JOIN mathews.usuarios u ON u.id = v.usuario_id
                WHERE r.deleted_at IS NULL
                  AND v.deleted_at IS NULL
                  AND u.deleted_at IS NULL
                GROUP BY u.id, u.nome, u.sexo
                ORDER BY total_revisoes DESC
            ");
        });

        return response()->json($dados);
    }

    // Média de tempo entre revisões de uma mesma pessoa
    public function mediaTempoRevisoes()
    {
        $dados = Cache::remember('relatorio.revisoes.media_tempo', 300, function () {
            return DB::select("
                SELECT
                    u.nome,
                    ROUND(AVG(diff_dias), 0) AS media_dias_entre_revisoes
                FROM (
                    SELECT
                        v.usuario_id,
                        r.data_revisao - LAG(r.data_revisao) OVER (
                            PARTITION BY r.veiculo_id
                            ORDER BY r.data_revisao
                        ) AS diff_dias
                    FROM mathews.revisoes r
                    INNER JOIN mathews.veiculos v ON v.id = r.veiculo_id
                    WHERE r.deleted_at IS NULL AND v.deleted_at IS NULL
                ) sub
                INNER JOIN mathews.usuarios u ON u.id = sub.usuario_id
                WHERE sub.diff_dias IS NOT NULL
                GROUP BY u.id, u.nome
                ORDER BY media_dias_entre_revisoes
            ");
        });

        return response()->json($dados);
    }

    // Próximas revisões baseado na média e na última revisão
    public function proximasRevisoes()
    {
        $dados = Cache::remember('relatorio.revisoes.proximas', 300, function () {
            return DB::select("
                SELECT
                    u.nome,
                    v.marca,
                    v.modelo,
                    v.placa,
                    MAX(r.data_revisao) AS ultima_revisao,
                    ROUND(AVG(r.data_revisao - LAG(r.data_revisao) OVER (
                        PARTITION BY r.veiculo_id ORDER BY r.data_revisao
                    )), 0) AS media_dias,
                    MAX(r.data_revisao) + ROUND(AVG(r.data_revisao - LAG(r.data_revisao) OVER (
                        PARTITION BY r.veiculo_id ORDER BY r.data_revisao
                    )), 0) * INTERVAL '1 day' AS previsao_proxima_revisao
                FROM mathews.revisoes r
                INNER JOIN mathews.veiculos v ON v.id = r.veiculo_id
                INNER JOIN mathews.usuarios u ON u.id = v.usuario_id
                WHERE r.deleted_at IS NULL
                  AND v.deleted_at IS NULL
                  AND u.deleted_at IS NULL
                GROUP BY u.nome, v.id, v.marca, v.modelo, v.placa
                ORDER BY previsao_proxima_revisao
            ");
        });

        return response()->json($dados);
    }
}