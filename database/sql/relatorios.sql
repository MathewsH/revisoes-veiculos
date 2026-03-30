-- ================================================
-- RELATÓRIOS DO SISTEMA DE REVISÕES DE VEÍCULOS
-- Schema: mathews
-- ================================================


-- ================================================
-- RELATÓRIOS DE VEÍCULOS
-- ================================================

-- R01: Todos os veículos
SELECT
    v.id,
    v.marca,
    v.modelo,
    v.placa,
    v.ano_fabricacao,
    v.ano_modelo,
    v.cor,
    v.combustivel,
    v.quilometragem,
    u.nome AS proprietario
FROM mathews.veiculos v
INNER JOIN mathews.usuarios u ON u.id = v.usuario_id
WHERE v.deleted_at IS NULL
ORDER BY v.marca, v.modelo;


-- R02: Todos os veículos por pessoa ordenado por nome
SELECT
    u.nome AS proprietario,
    u.sexo,
    v.marca,
    v.modelo,
    v.placa,
    v.ano_fabricacao
FROM mathews.veiculos v
INNER JOIN mathews.usuarios u ON u.id = v.usuario_id
WHERE v.deleted_at IS NULL
  AND u.deleted_at IS NULL
ORDER BY u.nome, v.marca;


-- R03: Quem tem mais veículos (homens ou mulheres)
SELECT
    CASE u.sexo WHEN 'M' THEN 'Masculino' ELSE 'Feminino' END AS genero,
    COUNT(v.id) AS total_veiculos
FROM mathews.usuarios u
LEFT JOIN mathews.veiculos v ON v.usuario_id = u.id AND v.deleted_at IS NULL
WHERE u.deleted_at IS NULL
GROUP BY u.sexo
ORDER BY total_veiculos DESC;


-- R04: Todas as marcas ordenadas pelo número de veículos
SELECT
    marca,
    COUNT(*) AS total
FROM mathews.veiculos
WHERE deleted_at IS NULL
GROUP BY marca
ORDER BY total DESC;


-- R05: Totais de marcas separados entre homens e mulheres
SELECT
    v.marca,
    CASE u.sexo WHEN 'M' THEN 'Masculino' ELSE 'Feminino' END AS genero,
    COUNT(v.id) AS total
FROM mathews.veiculos v
INNER JOIN mathews.usuarios u ON u.id = v.usuario_id
WHERE v.deleted_at IS NULL
  AND u.deleted_at IS NULL
GROUP BY v.marca, u.sexo
ORDER BY v.marca, total DESC;


-- ================================================
-- RELATÓRIOS DE PESSOAS
-- ================================================

-- R06: Todas as pessoas
SELECT
    u.id,
    u.nome,
    u.sexo,
    u.data_nascimento,
    EXTRACT(YEAR FROM AGE(CURRENT_DATE, u.data_nascimento)) AS idade,
    u.cpf,
    u.email,
    u.telefone,
    u.cidade,
    u.estado,
    COUNT(v.id) AS total_veiculos
FROM mathews.usuarios u
LEFT JOIN mathews.veiculos v ON v.usuario_id = u.id AND v.deleted_at IS NULL
WHERE u.deleted_at IS NULL
GROUP BY u.id
ORDER BY u.nome;


-- R07: Pessoas separadas por gênero com idade média
SELECT
    CASE sexo WHEN 'M' THEN 'Masculino' ELSE 'Feminino' END AS genero,
    COUNT(*) AS total,
    ROUND(AVG(
        EXTRACT(YEAR FROM AGE(CURRENT_DATE, data_nascimento))
    ), 1) AS idade_media
FROM mathews.usuarios
WHERE deleted_at IS NULL
GROUP BY sexo
ORDER BY genero;


-- ================================================
-- RELATÓRIOS DE REVISÕES
-- ================================================

-- R08: Todas as revisões dentro de um período (substituir as datas)
SELECT
    r.id,
    r.data_revisao,
    r.quilometragem_revisao,
    r.descricao,
    r.valor_total,
    r.oficina,
    r.proxima_revisao_prevista,
    v.marca,
    v.modelo,
    v.placa,
    u.nome AS proprietario
FROM mathews.revisoes r
INNER JOIN mathews.veiculos v ON v.id = r.veiculo_id
INNER JOIN mathews.usuarios u ON u.id = v.usuario_id
WHERE r.deleted_at IS NULL
  AND r.data_revisao BETWEEN '2024-01-01' AND '2024-12-31'
ORDER BY r.data_revisao;


-- R09: Marcas com maior número de revisões
SELECT
    v.marca,
    COUNT(r.id) AS total_revisoes
FROM mathews.revisoes r
INNER JOIN mathews.veiculos v ON v.id = r.veiculo_id
WHERE r.deleted_at IS NULL
  AND v.deleted_at IS NULL
GROUP BY v.marca
ORDER BY total_revisoes DESC;


-- R10: Pessoas com maior número de revisões
SELECT
    u.nome,
    CASE u.sexo WHEN 'M' THEN 'Masculino' ELSE 'Feminino' END AS genero,
    COUNT(r.id) AS total_revisoes
FROM mathews.revisoes r
INNER JOIN mathews.veiculos v ON v.id = r.veiculo_id
INNER JOIN mathews.usuarios u ON u.id = v.usuario_id
WHERE r.deleted_at IS NULL
  AND v.deleted_at IS NULL
  AND u.deleted_at IS NULL
GROUP BY u.id, u.nome, u.sexo
ORDER BY total_revisoes DESC;


-- R11: Média de tempo entre revisões de uma mesma pessoa
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
    WHERE r.deleted_at IS NULL
      AND v.deleted_at IS NULL
) sub
INNER JOIN mathews.usuarios u ON u.id = sub.usuario_id
WHERE sub.diff_dias IS NOT NULL
GROUP BY u.id, u.nome
ORDER BY media_dias_entre_revisoes;


-- R12: Próximas revisões baseado na média e na última revisão
SELECT
    u.nome,
    v.marca,
    v.modelo,
    v.placa,
    MAX(r.data_revisao)                              AS ultima_revisao,
    ROUND(AVG(r.data_revisao - LAG(r.data_revisao) OVER (
        PARTITION BY r.veiculo_id ORDER BY r.data_revisao
    )), 0)                                           AS media_dias,
    MAX(r.data_revisao) + ROUND(AVG(r.data_revisao - LAG(r.data_revisao) OVER (
        PARTITION BY r.veiculo_id ORDER BY r.data_revisao
    )), 0) * INTERVAL '1 day'                        AS previsao_proxima_revisao
FROM mathews.revisoes r
INNER JOIN mathews.veiculos v ON v.id = r.veiculo_id
INNER JOIN mathews.usuarios u ON u.id = v.usuario_id
WHERE r.deleted_at IS NULL
  AND v.deleted_at IS NULL
  AND u.deleted_at IS NULL
GROUP BY u.nome, v.id, v.marca, v.modelo, v.placa
ORDER BY previsao_proxima_revisao;