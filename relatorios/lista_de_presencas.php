<?php
ob_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
verificaAcesso();
verificaPerfil(['ADMIN', 'LIDER']);
require __DIR__ . '/../includes/menu.php';

/*
=========================================================
FILTROS
=========================================================
*/
$id_evento = $_GET['id_evento'] ?? '';
$data_inicial = $_GET['data_inicial'] ?? '';
$data_final = $_GET['data_final'] ?? '';

/*
=========================================================
LISTA DE EVENTOS
=========================================================
*/
$stmtEventos = $pdo->query("SELECT id_evento, descricao FROM eventos ORDER BY descricao");
$eventos = $stmtEventos->fetchAll(PDO::FETCH_ASSOC);

/*
=========================================================
VARIÁVEIS
=========================================================
*/
$datas_aulas = [];
$participantes = [];
$presencas_por_participante = [];
$evento_descricao = '';
$totais_por_aula = [];
$total_membros = 0;
$total_visitantes = 0;

if (!empty($id_evento)) {

    /*
    =========================================================
    EVENTO
    =========================================================
    */
    $stmtEvento = $pdo->prepare("SELECT descricao FROM eventos WHERE id_evento = :id_evento");
    $stmtEvento->bindParam(':id_evento', $id_evento);
    $stmtEvento->execute();
    $evento_descricao = $stmtEvento->fetchColumn();

    /*
    =========================================================
    BUSCAR AULAS / DATAS DO EVENTO
    =========================================================
    */
    $sqlAulas = "
        SELECT id_aula, data_aula, nome_da_aula
        FROM aulas
        WHERE id_evento = :id_evento
    ";

    if (!empty($data_inicial) && !empty($data_final)) {
        $sqlAulas .= " AND DATE(data_aula) BETWEEN :data_inicial AND :data_final ";
    }

    $sqlAulas .= " ORDER BY data_aula";

    $stmtAulas = $pdo->prepare($sqlAulas);
    $stmtAulas->bindParam(':id_evento', $id_evento);

    if (!empty($data_inicial) && !empty($data_final)) {
        $stmtAulas->bindParam(':data_inicial', $data_inicial);
        $stmtAulas->bindParam(':data_final', $data_final);
    }

    $stmtAulas->execute();
    $aulas = $stmtAulas->fetchAll(PDO::FETCH_ASSOC);

    foreach ($aulas as $a) {
        $datas_aulas[$a['id_aula']] = [
            'data_aula' => $a['data_aula'],
            'nome_da_aula' => $a['nome_da_aula']
        ];
        $totais_por_aula[$a['id_aula']] = 0;
    }

    /*
    =========================================================
    MEMBROS DO EVENTO
    =========================================================
    */
    $sqlMembros = "
        SELECT DISTINCT
            m.id_membro,
            m.nome_do_membro
        FROM membros m
        INNER JOIN presencas p ON p.id_membro = m.id_membro
        INNER JOIN aulas a ON a.id_aula = p.id_aula
        WHERE a.id_evento = :id_evento
    ";

    if (!empty($data_inicial) && !empty($data_final)) {
        $sqlMembros .= " AND DATE(a.data_aula) BETWEEN :data_inicial AND :data_final ";
    }

    $sqlMembros .= " ORDER BY m.nome_do_membro";

    $stmtMembros = $pdo->prepare($sqlMembros);
    $stmtMembros->bindParam(':id_evento', $id_evento);

    if (!empty($data_inicial) && !empty($data_final)) {
        $stmtMembros->bindParam(':data_inicial', $data_inicial);
        $stmtMembros->bindParam(':data_final', $data_final);
    }

    $stmtMembros->execute();
    $membros = $stmtMembros->fetchAll(PDO::FETCH_ASSOC);

    foreach ($membros as $m) {
        $chave = 'M_' . $m['id_membro'];
        $participantes[$chave] = [
            'tipo' => 'MEMBRO',
            'id'   => $m['id_membro'],
            'nome' => $m['nome_do_membro']
        ];
    }

    $total_membros = count($membros);

    /*
    =========================================================
    PRESENÇAS DOS MEMBROS
    =========================================================
    */
    $sqlPresencas = "
        SELECT
            p.id_membro,
            p.id_aula
        FROM presencas p
        INNER JOIN aulas a ON a.id_aula = p.id_aula
        WHERE a.id_evento = :id_evento
    ";

    if (!empty($data_inicial) && !empty($data_final)) {
        $sqlPresencas .= " AND DATE(a.data_aula) BETWEEN :data_inicial AND :data_final ";
    }

    $stmtPresencas = $pdo->prepare($sqlPresencas);
    $stmtPresencas->bindParam(':id_evento', $id_evento);

    if (!empty($data_inicial) && !empty($data_final)) {
        $stmtPresencas->bindParam(':data_inicial', $data_inicial);
        $stmtPresencas->bindParam(':data_final', $data_final);
    }

    $stmtPresencas->execute();
    $presencas = $stmtPresencas->fetchAll(PDO::FETCH_ASSOC);

    foreach ($presencas as $p) {
        $chave = 'M_' . $p['id_membro'];
        $presencas_por_participante[$chave][$p['id_aula']] = true;

        if (isset($totais_por_aula[$p['id_aula']])) {
            $totais_por_aula[$p['id_aula']]++;
        }
    }

    /*
    =========================================================
    VISITANTES DO EVENTO
    =========================================================
    */
    $sqlVisitantes = "
        SELECT
            v.id_visitante,
            v.nome,
            v.data_cadastro
        FROM visitantes v
        WHERE v.id_evento = :id_evento
    ";

    if (!empty($data_inicial) && !empty($data_final)) {
        $sqlVisitantes .= " AND DATE(v.data_cadastro) BETWEEN :data_inicial AND :data_final ";
    }

    $sqlVisitantes .= " ORDER BY v.nome";

    $stmtVisitantes = $pdo->prepare($sqlVisitantes);
    $stmtVisitantes->bindParam(':id_evento', $id_evento);

    if (!empty($data_inicial) && !empty($data_final)) {
        $stmtVisitantes->bindParam(':data_inicial', $data_inicial);
        $stmtVisitantes->bindParam(':data_final', $data_final);
    }

    $stmtVisitantes->execute();
    $visitantes = $stmtVisitantes->fetchAll(PDO::FETCH_ASSOC);

    foreach ($visitantes as $v) {
        $chave = 'V_' . $v['id_visitante'];
        $participantes[$chave] = [
            'tipo' => 'VISITANTE',
            'id'   => $v['id_visitante'],
            'nome' => $v['nome']
        ];

        $dataVisitante = date('Y-m-d', strtotime($v['data_cadastro']));

        foreach ($datas_aulas as $id_aula => $dadosAula) {
            $dataAula = date('Y-m-d', strtotime($dadosAula['data_aula']));

            if ($dataVisitante === $dataAula) {
                $presencas_por_participante[$chave][$id_aula] = true;

                if (isset($totais_por_aula[$id_aula])) {
                    $totais_por_aula[$id_aula]++;
                }
            }
        }
    }

    $total_visitantes = count($visitantes);

    /*
    =========================================================
    ORDENAR TODOS PELO NOME
    =========================================================
    */
    uasort($participantes, function ($a, $b) {
        return strcasecmp($a['nome'], $b['nome']);
    });
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Presenças</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #222;
        }

        h1, h2, h3 {
            margin-bottom: 8px;
        }

        .filtros {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .filtros label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }

        .filtros input,
        .filtros select {
            width: 320px;
            max-width: 100%;
            padding: 8px;
            margin-top: 4px;
        }

        .filtros button,
        .filtros a {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 14px;
            text-decoration: none;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            background: #2c3e50;
            color: #fff;
        }

        .filtros a {
            background: #7f8c8d;
        }

        .cabecalho-relatorio {
            margin: 20px 0;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background: #fafafa;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 15px;
            font-size: 14px;
        }

        th, td {
            border: 1px solid #bbb;
            padding: 8px;
            text-align: center;
        }

        th {
            background: #eaeaea;
        }

        td.nome {
            text-align: left;
            font-weight: bold;
            min-width: 240px;
        }

        .presente {
            background: #d4edda;
            font-weight: bold;
        }

        .falta {
            background: #f8d7da;
            font-weight: bold;
        }

        .neutro {
            background: #f4f4f4;
            color: #777;
            font-weight: bold;
        }

        .resumo {
            margin-top: 25px;
        }

        .print-btn {
            margin-top: 15px;
            padding: 10px 14px;
            border: none;
            border-radius: 8px;
            background: #1abc9c;
            color: #fff;
            cursor: pointer;
        }

        .tipo-participante {
            font-size: 11px;
            display: block;
            margin-top: 4px;
            color: #555;
            font-weight: normal;
        }

        .linha-total {
            background: #eef5ff;
            font-weight: bold;
        }

        @media print {
            .filtros,
            .print-btn,
            .topo-sistema,
            .menu-sistema {
                display: none !important;
            }

            body {
                margin: 0;
            }
        }
    </style>
</head>
<body>

<h1>Relatório de Lista de Presenças</h1>

<div class="filtros">
    <form method="get">
        <label>Evento</label>
        <select name="id_evento" required>
            <option value="">Selecione</option>
            <?php foreach ($eventos as $e): ?>
                <option value="<?= $e['id_evento'] ?>" <?= ($id_evento == $e['id_evento']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($e['descricao']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Data Inicial</label>
        <input type="date" name="data_inicial" value="<?= htmlspecialchars($data_inicial) ?>">

        <label>Data Final</label>
        <input type="date" name="data_final" value="<?= htmlspecialchars($data_final) ?>">

        <br>
        <button type="submit">Gerar Relatório</button>
        <a href="lista_de_presencas.php">Limpar</a>
    </form>

    <button class="print-btn" onclick="window.print()">Imprimir / Salvar em PDF</button>
</div>

<?php if (!empty($id_evento) && !empty($datas_aulas)): ?>
    <div class="cabecalho-relatorio">
        <h3>Evento: <?= htmlspecialchars($evento_descricao) ?></h3>
        <?php if (!empty($data_inicial) && !empty($data_final)): ?>
            <p>Período: <?= date('d/m/Y', strtotime($data_inicial)) ?> até <?= date('d/m/Y', strtotime($data_final)) ?></p>
        <?php endif; ?>
        <p>Total de encontros/aulas: <?= count($datas_aulas) ?></p>
        <p>Total de membros: <?= $total_membros ?></p>
        <p>Total de visitantes: <?= $total_visitantes ?></p>
        <p>Total geral de participantes: <?= count($participantes) ?></p>
    </div>

    <table>
        <tr>
            <th>Participante</th>
            <?php foreach ($datas_aulas as $id_aula => $dadosAula): ?>
                <th>
                    <?= date('d/m', strtotime($dadosAula['data_aula'])) ?><br>
                    <small><?= htmlspecialchars($dadosAula['nome_da_aula']) ?></small>
                </th>
            <?php endforeach; ?>
            <th>Presenças</th>
            <th>Faltas</th>
        </tr>

        <?php foreach ($participantes as $chave => $p): ?>
            <?php
                $total_presencas = 0;
                $total_faltas = 0;
            ?>
            <tr>
                <td class="nome">
                    <?= htmlspecialchars($p['nome']) ?>
                    <span class="tipo-participante">
                        <?= $p['tipo'] === 'MEMBRO' ? 'Membro' : 'Visitante' ?>
                    </span>
                </td>

                <?php foreach ($datas_aulas as $id_aula => $dadosAula): ?>
                    <?php
                        $presente = !empty($presencas_por_participante[$chave][$id_aula]);

                        if ($p['tipo'] === 'MEMBRO') {
                            if ($presente) {
                                $total_presencas++;
                            } else {
                                $total_faltas++;
                            }

                            $classe = $presente ? 'presente' : 'falta';
                            $texto = $presente ? 'P' : 'F';
                        } else {
                            if ($presente) {
                                $total_presencas++;
                                $classe = 'presente';
                                $texto = 'P';
                            } else {
                                $classe = 'neutro';
                                $texto = '-';
                            }
                        }
                    ?>
                    <td class="<?= $classe ?>">
                        <?= $texto ?>
                    </td>
                <?php endforeach; ?>

                <td><?= $total_presencas ?></td>
                <td><?= $total_faltas ?></td>
            </tr>
        <?php endforeach; ?>

        <tr class="linha-total">
            <td>Total por aula</td>
            <?php foreach ($datas_aulas as $id_aula => $dadosAula): ?>
                <td><?= $totais_por_aula[$id_aula] ?? 0 ?></td>
            <?php endforeach; ?>
            <td colspan="2">Membros + Visitantes</td>
        </tr>
    </table>

    <div class="resumo">
        <p><strong>Legenda:</strong> P = Presença | F = Falta | - = não se aplica</p>
        <p><strong>Observação:</strong> os visitantes são relacionados ao evento pelo campo <code>id_evento</code> e marcados na aula cuja data coincide com a data de cadastro.</p>
    </div>

<?php elseif (!empty($id_evento)): ?>
    <p>Nenhum registro encontrado para os filtros informados.</p>
<?php endif; ?>

</body>
</html>