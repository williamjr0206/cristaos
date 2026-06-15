<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
verificaAcesso();
require __DIR__ . '/../includes/menu.php';

$ano = isset($_GET['ano']) ? intval($_GET['ano']) : date('Y');

function buscarTodos($pdo, $sql, $params = [])
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function buscarUm($pdo, $sql, $params = [])
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// ======================================================
// TOTAL DE MEMBROS ATIVOS
// ======================================================
$total_ativos = buscarUm($pdo, "
    SELECT
        SUM(CASE WHEN sexo = 'Masculino' THEN 1 ELSE 0 END) AS masculino,
        SUM(CASE WHEN sexo = 'Feminino' THEN 1 ELSE 0 END) AS feminino,
        COUNT(*) AS total
    FROM membros
    WHERE status_atual = 'Ativo'
");

// ======================================================
// MEMBRESIA POR TIPO
// ======================================================
$membresia_tipo = buscarTodos($pdo, "
    SELECT
        COALESCE(t.descricao, 'Sem tipo') AS tipo,
        SUM(CASE WHEN m.sexo = 'Masculino' THEN 1 ELSE 0 END) AS masculino,
        SUM(CASE WHEN m.sexo = 'Feminino' THEN 1 ELSE 0 END) AS feminino,
        COUNT(*) AS total
    FROM membros m
    LEFT JOIN tipo t ON t.id_tipo = m.id_tipo
    WHERE m.status_atual = 'Ativo'
    GROUP BY t.descricao
    ORDER BY t.descricao
");

// ======================================================
// MEMBRESIA POR CARGO
// ======================================================
$membresia_cargo = buscarTodos($pdo, "
    SELECT
        COALESCE(c.descricao, 'Sem cargo') AS cargo,
        SUM(CASE WHEN m.sexo = 'Masculino' THEN 1 ELSE 0 END) AS masculino,
        SUM(CASE WHEN m.sexo = 'Feminino' THEN 1 ELSE 0 END) AS feminino,
        COUNT(*) AS total
    FROM membros m
    LEFT JOIN cargos c ON c.id_cargo = m.id_cargo
    WHERE m.status_atual = 'Ativo'
    GROUP BY c.descricao
    ORDER BY c.descricao
");

// ======================================================
// BATISMOS NO ANO
// ======================================================
$batismos = buscarUm($pdo, "
    SELECT
        SUM(CASE WHEN sexo = 'Masculino' THEN 1 ELSE 0 END) AS masculino,
        SUM(CASE WHEN sexo = 'Feminino' THEN 1 ELSE 0 END) AS feminino,
        COUNT(*) AS total
    FROM membros
    WHERE data_batismo IS NOT NULL
      AND YEAR(data_batismo) = :ano
", [':ano' => $ano]);

// ======================================================
// PROFISSÕES DE FÉ NO ANO
// ======================================================
$profissoes = buscarUm($pdo, "
    SELECT
        SUM(CASE WHEN sexo = 'Masculino' THEN 1 ELSE 0 END) AS masculino,
        SUM(CASE WHEN sexo = 'Feminino' THEN 1 ELSE 0 END) AS feminino,
        COUNT(*) AS total
    FROM membros
    WHERE data_profissao_de_fe IS NOT NULL
      AND YEAR(data_profissao_de_fe) = :ano
", [':ano' => $ano]);

// ======================================================
// HISTÓRICO DE MEMBROS NO ANO
// ======================================================
$historico = buscarTodos($pdo, "
    SELECT
        h.status,
        h.motivo,
        SUM(CASE WHEN m.sexo = 'Masculino' THEN 1 ELSE 0 END) AS masculino,
        SUM(CASE WHEN m.sexo = 'Feminino' THEN 1 ELSE 0 END) AS feminino,
        COUNT(*) AS total
    FROM historico_membro h
    INNER JOIN membros m ON m.id_membro = h.id_membro
    WHERE YEAR(h.data_evento) = :ano
    GROUP BY h.status, h.motivo
    ORDER BY h.status, h.motivo
", [':ano' => $ano]);

// ======================================================
// PRESENÇAS - RESUMO GERAL DO ANO
// ======================================================
$resumo_presencas = buscarUm($pdo, "
    SELECT
        COUNT(*) AS total_presencas,
        COUNT(DISTINCT p.id_membro) AS membros_distintos,
        COUNT(DISTINCT DATE(p.data_aula)) AS dias_com_presenca,
        COUNT(DISTINCT p.id_aula) AS aulas_distintas,
        COUNT(DISTINCT p.id_professor) AS professores_distintos
    FROM presencas p
    WHERE YEAR(p.data_aula) = :ano
", [':ano' => $ano]);

$total_presencas = $resumo_presencas['total_presencas'] ?? 0;
$aulas_distintas = $resumo_presencas['aulas_distintas'] ?? 0;

$media_presenca_por_aula = 0;
if ($aulas_distintas > 0) {
    $media_presenca_por_aula = $total_presencas / $aulas_distintas;
}

// ======================================================
// PRESENÇAS POR TIPO NO ANO
// Conta pessoas distintas e total de presenças
// ======================================================
$presencas_por_tipo = buscarTodos($pdo, "
    SELECT
        COALESCE(t.descricao, 'Sem tipo') AS tipo,
        COUNT(*) AS total_presencas,
        COUNT(DISTINCT p.id_membro) AS membros_distintos,
        COUNT(DISTINCT CASE WHEN m.sexo = 'Masculino' THEN p.id_membro END) AS masculino_distintos,
        COUNT(DISTINCT CASE WHEN m.sexo = 'Feminino' THEN p.id_membro END) AS feminino_distintos
    FROM presencas p
    INNER JOIN membros m ON m.id_membro = p.id_membro
    LEFT JOIN tipo t ON t.id_tipo = p.id_tipo
    WHERE YEAR(p.data_aula) = :ano
    GROUP BY t.descricao
    ORDER BY t.descricao
", [':ano' => $ano]);

// ======================================================
// PRESENÇAS POR CARGO NO ANO
// ======================================================
$presencas_por_cargo = buscarTodos($pdo, "
    SELECT
        COALESCE(c.descricao, 'Sem cargo') AS cargo,
        COUNT(*) AS total_presencas,
        COUNT(DISTINCT p.id_membro) AS membros_distintos,
        COUNT(DISTINCT CASE WHEN m.sexo = 'Masculino' THEN p.id_membro END) AS masculino_distintos,
        COUNT(DISTINCT CASE WHEN m.sexo = 'Feminino' THEN p.id_membro END) AS feminino_distintos
    FROM presencas p
    INNER JOIN membros m ON m.id_membro = p.id_membro
    LEFT JOIN cargos c ON c.id_cargo = p.id_cargo
    WHERE YEAR(p.data_aula) = :ano
    GROUP BY c.descricao
    ORDER BY c.descricao
", [':ano' => $ano]);

// ======================================================
// PRESENÇAS POR PROFESSOR NO ANO
// ======================================================
$presencas_por_professor = buscarTodos($pdo, "
    SELECT
        COALESCE(pr.nome_do_professor, 'Sem professor') AS professor,
        COUNT(*) AS total_presencas,
        COUNT(DISTINCT p.id_membro) AS membros_distintos,
        COUNT(DISTINCT DATE(p.data_aula)) AS dias_com_presenca
    FROM presencas p
    LEFT JOIN professores pr ON pr.id_professor = p.id_professor
    WHERE YEAR(p.data_aula) = :ano
    GROUP BY pr.nome_do_professor
    ORDER BY pr.nome_do_professor
", [':ano' => $ano]);

// ======================================================
// PRESENÇAS POR MÊS NO ANO
// ======================================================
$presencas_por_mes = buscarTodos($pdo, "
    SELECT
        MONTH(p.data_aula) AS mes,
        COUNT(*) AS total_presencas,
        COUNT(DISTINCT p.id_membro) AS membros_distintos,
        COUNT(DISTINCT p.id_aula) AS aulas_distintas
    FROM presencas p
    WHERE YEAR(p.data_aula) = :ano
    GROUP BY MONTH(p.data_aula)
    ORDER BY MONTH(p.data_aula)
", [':ano' => $ano]);

$nomes_meses = [
    1 => 'Janeiro',
    2 => 'Fevereiro',
    3 => 'Março',
    4 => 'Abril',
    5 => 'Maio',
    6 => 'Junho',
    7 => 'Julho',
    8 => 'Agosto',
    9 => 'Setembro',
    10 => 'Outubro',
    11 => 'Novembro',
    12 => 'Dezembro'
];

?>

<div class="container mt-4">

    <h3>Estatística Anual da Igreja</h3>

    <style>
        body {
            font-family: Arial;
            margin: 20px;
        }

        form {
            margin-bottom: 30px;
        }

        input,
        select {
            margin: 6px 0;
            padding: 6px;
            width: 180px;
            display: block;
            box-sizing: border-box;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 30px;
        }

        th,
        td {
            padding: 6px;
            border: 1px solid #ccc;
            text-align: left;
        }

        th {
            background: #f2f2f2;
        }

        h3,
        h5 {
            margin-top: 20px;
        }

        .resumo {
            background: #f8f8f8;
            border: 1px solid #ccc;
            padding: 12px;
            margin-bottom: 25px;
        }

        .total {
            font-weight: bold;
            background: #fafafa;
        }

        .observacao {
            background: #fff8dc;
            border: 1px solid #e0d28a;
            padding: 10px;
            margin-bottom: 25px;
        }

        a {
            margin-right: 10px;
        }
    </style>

    <form method="get" class="card p-3 mb-4">
        <label>Ano de referência</label>
        <input type="number" name="ano" value="<?= htmlspecialchars($ano) ?>" required>

        <button type="submit" class="btn btn-primary">
            Filtrar
        </button>
    </form>

    <div class="resumo">
        <h5>Resumo Geral - Membros Ativos</h5>
        <p><strong>Masculino:</strong> <?= $total_ativos['masculino'] ?? 0 ?></p>
        <p><strong>Feminino:</strong> <?= $total_ativos['feminino'] ?? 0 ?></p>
        <p><strong>Total:</strong> <?= $total_ativos['total'] ?? 0 ?></p>
    </div>

    <h5>1. Membresia por Tipo</h5>

    <table>
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Masculino</th>
                <th>Feminino</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($membresia_tipo as $linha): ?>
                <tr>
                    <td><?= htmlspecialchars($linha['tipo']) ?></td>
                    <td><?= $linha['masculino'] ?? 0 ?></td>
                    <td><?= $linha['feminino'] ?? 0 ?></td>
                    <td><?= $linha['total'] ?? 0 ?></td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($membresia_tipo)): ?>
                <tr>
                    <td colspan="4">Nenhum registro encontrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h5>2. Membresia por Cargo / Função</h5>

    <table>
        <thead>
            <tr>
                <th>Cargo</th>
                <th>Masculino</th>
                <th>Feminino</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($membresia_cargo as $linha): ?>
                <tr>
                    <td><?= htmlspecialchars($linha['cargo']) ?></td>
                    <td><?= $linha['masculino'] ?? 0 ?></td>
                    <td><?= $linha['feminino'] ?? 0 ?></td>
                    <td><?= $linha['total'] ?? 0 ?></td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($membresia_cargo)): ?>
                <tr>
                    <td colspan="4">Nenhum registro encontrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h5>3. Batismos em <?= htmlspecialchars($ano) ?></h5>

    <table>
        <tr>
            <th>Masculino</th>
            <th>Feminino</th>
            <th>Total</th>
        </tr>
        <tr>
            <td><?= $batismos['masculino'] ?? 0 ?></td>
            <td><?= $batismos['feminino'] ?? 0 ?></td>
            <td><?= $batismos['total'] ?? 0 ?></td>
        </tr>
    </table>

    <h5>4. Profissões de Fé em <?= htmlspecialchars($ano) ?></h5>

    <table>
        <tr>
            <th>Masculino</th>
            <th>Feminino</th>
            <th>Total</th>
        </tr>
        <tr>
            <td><?= $profissoes['masculino'] ?? 0 ?></td>
            <td><?= $profissoes['feminino'] ?? 0 ?></td>
            <td><?= $profissoes['total'] ?? 0 ?></td>
        </tr>
    </table>

    <h5>5. Movimentações no Histórico em <?= htmlspecialchars($ano) ?></h5>

    <table>
        <thead>
            <tr>
                <th>Status</th>
                <th>Motivo</th>
                <th>Masculino</th>
                <th>Feminino</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($historico)): ?>
                <?php foreach ($historico as $linha): ?>
                    <tr>
                        <td><?= htmlspecialchars($linha['status']) ?></td>
                        <td><?= htmlspecialchars($linha['motivo']) ?></td>
                        <td><?= $linha['masculino'] ?? 0 ?></td>
                        <td><?= $linha['feminino'] ?? 0 ?></td>
                        <td><?= $linha['total'] ?? 0 ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">Nenhuma movimentação encontrada para o ano informado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h5>6. Resumo Geral de Presenças em <?= htmlspecialchars($ano) ?></h5>

    <div class="observacao">
        <strong>Observação:</strong> em presenças, há duas contagens diferentes:
        <br>
        <strong>Total de presenças</strong> = soma de todos os registros de presença no ano.
        <br>
        <strong>Membros distintos</strong> = quantidade de pessoas diferentes que participaram pelo menos uma vez.
    </div>

    <table>
        <tr>
            <th>Total de presenças</th>
            <th>Membros distintos</th>
            <th>Dias com presença</th>
            <th>Aulas distintas</th>
            <th>Professores distintos</th>
            <th>Média por aula</th>
        </tr>
        <tr>
            <td><?= $resumo_presencas['total_presencas'] ?? 0 ?></td>
            <td><?= $resumo_presencas['membros_distintos'] ?? 0 ?></td>
            <td><?= $resumo_presencas['dias_com_presenca'] ?? 0 ?></td>
            <td><?= $resumo_presencas['aulas_distintas'] ?? 0 ?></td>
            <td><?= $resumo_presencas['professores_distintos'] ?? 0 ?></td>
            <td><?= number_format($media_presenca_por_aula, 2, ',', '.') ?></td>
        </tr>
    </table>

    <h5>7. Presenças por Tipo em <?= htmlspecialchars($ano) ?></h5>

    <table>
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Masculino distintos</th>
                <th>Feminino distintos</th>
                <th>Membros distintos</th>
                <th>Total de presenças</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($presencas_por_tipo)): ?>
                <?php foreach ($presencas_por_tipo as $linha): ?>
                    <tr>
                        <td><?= htmlspecialchars($linha['tipo']) ?></td>
                        <td><?= $linha['masculino_distintos'] ?? 0 ?></td>
                        <td><?= $linha['feminino_distintos'] ?? 0 ?></td>
                        <td><?= $linha['membros_distintos'] ?? 0 ?></td>
                        <td><?= $linha['total_presencas'] ?? 0 ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">Nenhuma presença encontrada para o ano informado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h5>8. Presenças por Cargo / Função em <?= htmlspecialchars($ano) ?></h5>

    <table>
        <thead>
            <tr>
                <th>Cargo</th>
                <th>Masculino distintos</th>
                <th>Feminino distintos</th>
                <th>Membros distintos</th>
                <th>Total de presenças</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($presencas_por_cargo)): ?>
                <?php foreach ($presencas_por_cargo as $linha): ?>
                    <tr>
                        <td><?= htmlspecialchars($linha['cargo']) ?></td>
                        <td><?= $linha['masculino_distintos'] ?? 0 ?></td>
                        <td><?= $linha['feminino_distintos'] ?? 0 ?></td>
                        <td><?= $linha['membros_distintos'] ?? 0 ?></td>
                        <td><?= $linha['total_presencas'] ?? 0 ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">Nenhuma presença encontrada para o ano informado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h5>9. Presenças por Professor em <?= htmlspecialchars($ano) ?></h5>

    <table>
        <thead>
            <tr>
                <th>Professor</th>
                <th>Total de presenças</th>
                <th>Membros distintos</th>
                <th>Dias com presença</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($presencas_por_professor)): ?>
                <?php foreach ($presencas_por_professor as $linha): ?>
                    <tr>
                        <td><?= htmlspecialchars($linha['professor']) ?></td>
                        <td><?= $linha['total_presencas'] ?? 0 ?></td>
                        <td><?= $linha['membros_distintos'] ?? 0 ?></td>
                        <td><?= $linha['dias_com_presenca'] ?? 0 ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">Nenhuma presença encontrada para o ano informado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h5>10. Presenças por Mês em <?= htmlspecialchars($ano) ?></h5>

    <table>
        <thead>
            <tr>
                <th>Mês</th>
                <th>Total de presenças</th>
                <th>Membros distintos</th>
                <th>Aulas distintas</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($presencas_por_mes)): ?>
                <?php foreach ($presencas_por_mes as $linha): ?>
                    <tr>
                        <td><?= htmlspecialchars($nomes_meses[intval($linha['mes'])] ?? $linha['mes']) ?></td>
                        <td><?= $linha['total_presencas'] ?? 0 ?></td>
                        <td><?= $linha['membros_distintos'] ?? 0 ?></td>
                        <td><?= $linha['aulas_distintas'] ?? 0 ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">Nenhuma presença encontrada para o ano informado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h5>11. Itens ainda não automatizados</h5>

    <table>
        <tr>
            <th>Item</th>
            <th>Situação</th>
        </tr>
        <tr>
            <td>Projetos sociais</td>
            <td>Ainda precisa de tabela própria.</td>
        </tr>
        <tr>
            <td>Horas de culto, EBD e reuniões</td>
            <td>Ainda precisa de controle específico de eventos/reuniões.</td>
        </tr>
        <tr>
            <td>Coordenadorias</td>
            <td>Podem ser calculadas por idade ou vinculadas aos cursos/aulas.</td>
        </tr>
    </table>

</div>