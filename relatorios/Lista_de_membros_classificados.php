<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ob_start();

require __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
verificaAcesso();
require __DIR__ . '/../includes/menu.php';

$data_inicio = $_GET['data_inicio'] ?? date('Y-01-01');
$data_fim    = $_GET['data_fim'] ?? date('Y-m-d');

$sql = "
    SELECT
        m.id_membro,
        m.nome_do_membro,
        t.id_tipo,
        t.descricao AS tipo,

        CASE
            WHEN EXISTS (
                SELECT 1
                FROM presencas p
                WHERE p.id_membro = m.id_membro
                  AND DATE(p.data_aula) BETWEEN :data_inicio AND :data_fim
            )
            THEN 'Participante'
            ELSE 'Não Participante'
        END AS participacao

    FROM membros m
    INNER JOIN tipo t ON m.id_tipo = t.id_tipo

    WHERE m.status_atual = 'Ativo'
      AND t.id_tipo IN (2, 3, 7)

    ORDER BY
        CASE
            WHEN t.id_tipo = 3 THEN 1
            WHEN t.id_tipo = 2 THEN 2
            WHEN t.id_tipo = 7 THEN 3
            ELSE 4
        END,
        participacao,
        m.nome_do_membro
";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':data_inicio', $data_inicio);
$stmt->bindParam(':data_fim', $data_fim);
$stmt->execute();

$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

$grupos = [];

foreach ($registros as $r) {
    $tipo = $r['tipo'];
    $participacao = $r['participacao'];

    if (!isset($grupos[$tipo])) {
        $grupos[$tipo] = [
            'Participante' => [],
            'Não Participante' => []
        ];
    }

    $grupos[$tipo][$participacao][] = $r;
}

$total_geral = count($registros);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" charset="UTF-8">
    <title>Lista de Membros Classificados</title>

    <style>
        body { font-family: Arial; margin: 20px; }
        form { margin-bottom: 30px; }
        input, select { margin: 6px 0; padding: 6px; width: 360px; display: block; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 25px; }
        th, td { padding: 6px; }
        a { margin-right: 10px; }

        .subtitulo {
            margin-top: 30px;
            background: #eee;
            padding: 8px;
            border: 1px solid #ccc;
        }

        .subtotal {
            font-weight: bold;
            background: #f5f5f5;
        }

        .total-geral {
            font-size: 18px;
            font-weight: bold;
            margin-top: 25px;
            padding: 10px;
            border: 1px solid #000;
            display: inline-block;
        }

        .botoes {
            margin-bottom: 20px;
        }

        @media print {
            .botoes, form, a {
                display: none;
            }

            body {
                margin: 10px;
            }
        }
    </style>
</head>
<body>

<h2>Lista de Membros Classificados</h2>

<form method="get">
    <label>Data Inicial</label>
    <input type="date" name="data_inicio" value="<?= htmlspecialchars($data_inicio) ?>" required>

    <label>Data Final</label>
    <input type="date" name="data_fim" value="<?= htmlspecialchars($data_fim) ?>" required>

    <button type="submit">Filtrar</button>
</form>

<div class="botoes">
    <button onclick="window.print()">Imprimir / Salvar em PDF</button>
</div>

<p>
    <strong>Período:</strong>
    <?= date('d/m/Y', strtotime($data_inicio)) ?>
    até
    <?= date('d/m/Y', strtotime($data_fim)) ?>
</p>

<?php if (empty($registros)): ?>

    <p>Nenhum registro encontrado para o período informado.</p>

<?php else: ?>

    <?php foreach ($grupos as $tipo => $subgrupos): ?>

        <h3 class="subtitulo"><?= htmlspecialchars($tipo) ?></h3>

        <?php
            $total_tipo = 0;
        ?>

        <?php foreach (['Participante', 'Não Participante'] as $participacao): ?>

            <?php
                $lista = $subgrupos[$participacao] ?? [];
                $subtotal = count($lista);
                $total_tipo += $subtotal;
            ?>

            <h4><?= htmlspecialchars($participacao) ?></h4>

            <table border="1">
                <tr>
                    <th style="width: 80px;">Nº</th>
                    <th>Nome</th>
                    <th>Classificação</th>
                    <th>Participação</th>
                </tr>

                <?php if ($subtotal == 0): ?>
                    <tr>
                        <td colspan="4">Nenhum registro encontrado.</td>
                    </tr>
                <?php else: ?>
                    <?php $contador = 1; ?>
                    <?php foreach ($lista as $m): ?>
                        <tr>
                            <td><?= $contador++ ?></td>
                            <td><?= htmlspecialchars($m['nome_do_membro']) ?></td>
                            <td><?= htmlspecialchars($m['tipo']) ?></td>
                            <td><?= htmlspecialchars($m['participacao']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>

                <tr class="subtotal">
                    <td colspan="3">Subtotal - <?= htmlspecialchars($participacao) ?></td>
                    <td><?= $subtotal ?></td>
                </tr>
            </table>

        <?php endforeach; ?>

        <table border="1">
            <tr class="subtotal">
                <td><strong>Total da Classificação: <?= htmlspecialchars($tipo) ?></strong></td>
                <td style="width: 120px;"><strong><?= $total_tipo ?></strong></td>
            </tr>
        </table>

    <?php endforeach; ?>

    <div class="total-geral">
        Total Geral: <?= $total_geral ?>
    </div>

<?php endif; ?>

</body>
</html>