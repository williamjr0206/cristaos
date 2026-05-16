<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ob_start();

require __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
verificaAcesso();

/* BUSCAR MEMBRO PELO QRCODE */
if (isset($_GET['buscar_codigo'])) {
    header('Content-Type: application/json; charset=utf-8');

    $codigo = preg_replace('/\s+/', '', trim($_GET['buscar_codigo']));

    $stmt = $pdo->prepare("
        SELECT id_membro, nome_do_membro
        FROM membros
        WHERE codigo_barras = :codigo
          AND status_atual = 'Ativo'
        LIMIT 1
    ");
    $stmt->bindParam(':codigo', $codigo);
    $stmt->execute();

    $membro = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($membro) {
        echo json_encode([
            'encontrado' => true,
            'id_membro' => $membro['id_membro'],
            'nome_do_membro' => $membro['nome_do_membro']
        ]);
    } else {
        echo json_encode([
            'encontrado' => false,
            'mensagem' => 'Membro não encontrado para este QRCode.'
        ]);
    }

    exit;
}

require __DIR__ . '/../includes/menu.php';
/* =====================
   FUNÇÕES AUXILIARES
===================== */
function buscarIdGrupoDizimos(PDO $pdo): int
{
    $stmt = $pdo->prepare("SELECT id_grupo FROM grupos WHERE descricao = :descricao LIMIT 1");
    $descricao = 'Dízimos';
    $stmt->bindParam(':descricao', $descricao);
    $stmt->execute();
    $grupo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($grupo) {
        return (int)$grupo['id_grupo'];
    }

    $stmt = $pdo->prepare("INSERT INTO grupos (descricao) VALUES (:descricao)");
    $stmt->bindParam(':descricao', $descricao);
    $stmt->execute();

    return (int)$pdo->lastInsertId();
}

function buscarNomeMembro(PDO $pdo, int $id_membro): string
{
    $stmt = $pdo->prepare("SELECT nome_do_membro FROM membros WHERE id_membro = :id_membro LIMIT 1");
    $stmt->bindParam(':id_membro', $id_membro, PDO::PARAM_INT);
    $stmt->execute();
    $membro = $stmt->fetch(PDO::FETCH_ASSOC);

    return $membro['nome_do_membro'] ?? 'Membro não identificado';
}

/* =====================
   SALVAR / EDITAR
===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    date_default_timezone_set('America/Sao_Paulo');

    $id = $_POST['id'] ?? null;
    $data_lancamento = $_POST['data_lancamento'] ?? '';
    $id_membro = (int)($_POST['id_membro'] ?? 0);
    $valor_dizimo = $_POST['valor_dizimo'] ?? '';
    $forma_de_pagamento_recebimento = $_POST['forma_de_pagamento_recebimento'] ?? 'Pix Recebido';

    try {
        $pdo->beginTransaction();

        $id_grupo_dizimos = buscarIdGrupoDizimos($pdo);
        $nome_membro = buscarNomeMembro($pdo, $id_membro);
        $descricao_lancamento = 'Dízimo - ' . $nome_membro;

        if ($id) {
            $sql = "UPDATE dizimos SET
                        data_lancamento = :data_lancamento,
                        id_membro = :id_membro,
                        valor_dizimo = :valor_dizimo
                    WHERE id_lancamento = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':data_lancamento', $data_lancamento);
            $stmt->bindParam(':id_membro', $id_membro);
            $stmt->bindParam(':valor_dizimo', $valor_dizimo);
            $stmt->execute();

            $stmtBusca = $pdo->prepare("SELECT id_lancamento_financeiro FROM dizimos WHERE id_lancamento = :id LIMIT 1");
            $stmtBusca->bindParam(':id', $id);
            $stmtBusca->execute();
            $dizimoAtual = $stmtBusca->fetch(PDO::FETCH_ASSOC);
            $id_lancamento_financeiro = $dizimoAtual['id_lancamento_financeiro'] ?? null;

            if ($id_lancamento_financeiro) {
                $sqlLanc = "UPDATE lancamentos SET
                                documento_numero = :documento_numero,
                                data_lancamento = :data_lancamento,
                                descricao = :descricao,
                                tipo = 'Receber',
                                data_vencimento = :data_vencimento,
                                valor_nominal = :valor_nominal,
                                data_pagamento = :data_pagamento,
                                valor_pago = :valor_pago,
                                status = 'Recebido',
                                forma_de_pagamento_recebimento = :forma,
                                id_grupo = :id_grupo
                            WHERE id_lancamento = :id_lancamento_financeiro";

                $documento_numero = 'DIZ-' . str_pad((string)$id, 6, '0', STR_PAD_LEFT);

                $stmtLanc = $pdo->prepare($sqlLanc);
                $stmtLanc->bindParam(':documento_numero', $documento_numero);
                $stmtLanc->bindParam(':data_lancamento', $data_lancamento);
                $stmtLanc->bindParam(':descricao', $descricao_lancamento);
                $stmtLanc->bindParam(':data_vencimento', $data_lancamento);
                $stmtLanc->bindParam(':valor_nominal', $valor_dizimo);
                $stmtLanc->bindParam(':data_pagamento', $data_lancamento);
                $stmtLanc->bindParam(':valor_pago', $valor_dizimo);
                $stmtLanc->bindParam(':forma', $forma_de_pagamento_recebimento);
                $stmtLanc->bindParam(':id_grupo', $id_grupo_dizimos);
                $stmtLanc->bindParam(':id_lancamento_financeiro', $id_lancamento_financeiro);
                $stmtLanc->execute();
            }

        } else {
            $sql = "INSERT INTO dizimos (data_lancamento, id_membro, valor_dizimo)
                    VALUES (:data_lancamento, :id_membro, :valor_dizimo)";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':data_lancamento', $data_lancamento);
            $stmt->bindParam(':id_membro', $id_membro);
            $stmt->bindParam(':valor_dizimo', $valor_dizimo);
            $stmt->execute();

            $id_dizimo = (int)$pdo->lastInsertId();
            $documento_numero = 'DIZ-' . str_pad((string)$id_dizimo, 6, '0', STR_PAD_LEFT);

            $sqlLanc = "INSERT INTO lancamentos (
                            documento_numero,
                            data_lancamento,
                            descricao,
                            tipo,
                            data_vencimento,
                            valor_nominal,
                            data_pagamento,
                            valor_pago,
                            status,
                            forma_de_pagamento_recebimento,
                            id_grupo
                        ) VALUES (
                            :documento_numero,
                            :data_lancamento,
                            :descricao,
                            'Receber',
                            :data_vencimento,
                            :valor_nominal,
                            :data_pagamento,
                            :valor_pago,
                            'Recebido',
                            :forma,
                            :id_grupo
                        )";

            $stmtLanc = $pdo->prepare($sqlLanc);
            $stmtLanc->bindParam(':documento_numero', $documento_numero);
            $stmtLanc->bindParam(':data_lancamento', $data_lancamento);
            $stmtLanc->bindParam(':descricao', $descricao_lancamento);
            $stmtLanc->bindParam(':data_vencimento', $data_lancamento);
            $stmtLanc->bindParam(':valor_nominal', $valor_dizimo);
            $stmtLanc->bindParam(':data_pagamento', $data_lancamento);
            $stmtLanc->bindParam(':valor_pago', $valor_dizimo);
            $stmtLanc->bindParam(':forma', $forma_de_pagamento_recebimento);
            $stmtLanc->bindParam(':id_grupo', $id_grupo_dizimos);
            $stmtLanc->execute();

            $id_lancamento_financeiro = (int)$pdo->lastInsertId();

            $sqlVinculo = "UPDATE dizimos
                           SET id_lancamento_financeiro = :id_lancamento_financeiro
                           WHERE id_lancamento = :id_dizimo";

            $stmtVinculo = $pdo->prepare($sqlVinculo);
            $stmtVinculo->bindParam(':id_lancamento_financeiro', $id_lancamento_financeiro);
            $stmtVinculo->bindParam(':id_dizimo', $id_dizimo);
            $stmtVinculo->execute();
        }

        $pdo->commit();

        header("Location: " . BASE_URL . "cadastros/dizimos.php");
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        die("Erro ao salvar o dízimo: " . $e->getMessage());
    }
}

/* =====================
   EXCLUIR
===================== */
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    try {
        $pdo->beginTransaction();

        $stmtBusca = $pdo->prepare("SELECT id_lancamento_financeiro FROM dizimos WHERE id_lancamento = :id LIMIT 1");
        $stmtBusca->bindParam(':id', $id);
        $stmtBusca->execute();
        $dizimo = $stmtBusca->fetch(PDO::FETCH_ASSOC);

        if (!empty($dizimo['id_lancamento_financeiro'])) {
            $sqlLanc = "DELETE FROM lancamentos WHERE id_lancamento = :id_lancamento_financeiro";
            $stmtLanc = $pdo->prepare($sqlLanc);
            $stmtLanc->bindParam(':id_lancamento_financeiro', $dizimo['id_lancamento_financeiro']);
            $stmtLanc->execute();
        }

        $sql = "DELETE FROM dizimos WHERE id_lancamento = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $pdo->commit();

        header("Location: " . BASE_URL . "cadastros/dizimos.php");
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        die("Erro ao excluir o dízimo: " . $e->getMessage());
    }
}

/* =====================
   EDITAR
===================== */
$editar = null;

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];

    $stmt = $pdo->prepare("SELECT * FROM dizimos WHERE id_lancamento = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $editar = $stmt->fetch(PDO::FETCH_ASSOC);
}

/* =====================
   SELECTS
===================== */
$stmt = $pdo->query("
    SELECT id_membro, nome_do_membro, codigo_barras
    FROM membros
    WHERE status_atual = 'Ativo'
    ORDER BY nome_do_membro
");
$membros = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =====================
   LISTAR
===================== */
$stmt = $pdo->query("SELECT 
    d.id_lancamento,
    d.data_lancamento,
    d.id_membro,
    d.valor_dizimo,
    d.id_lancamento_financeiro,
    m.nome_do_membro,
    m.codigo_barras,
    m.status_atual 
FROM dizimos d
INNER JOIN membros m
    ON d.id_membro = m.id_membro 
WHERE m.status_atual = 'Ativo' 
ORDER BY d.data_lancamento DESC, m.nome_do_membro");

$dizimos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dízimos - Lançamentos</title>

    <style>
        body { font-family: Arial; margin: 20px; }
        form { margin-bottom: 30px; }
        input, select { margin: 6px 0; padding: 6px; width: 360px; display: block; }
        table { border-collapse: collapse; width: 100%; }
        th, td { padding: 6px; }
        a { margin-right: 10px; }

        .box-qrcode {
            background: #f3f3f3;
            border: 1px solid #ccc;
            padding: 12px;
            margin-bottom: 20px;
            max-width: 430px;
        }

        .ok {
            color: green;
            font-weight: bold;
            margin-top: 8px;
        }

        .erro {
            color: red;
            font-weight: bold;
            margin-top: 8px;
        }

        #reader {
            width: 350px;
            max-width: 100%;
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .aviso-camera {
            font-size: 13px;
            color: #555;
            margin-top: 8px;
        }
    </style>
</head>
<body>

<h2><?= $editar ? 'Editar Lançamento de Dízimo' : 'Novo Lançamento de Dízimo' ?></h2>

<form method="post">

    <input type="hidden" name="id" value="<?= htmlspecialchars($editar['id_lancamento'] ?? '') ?>">

    <label>Data do Lançamento</label>
    <input type="date" name="data_lancamento" required
        value="<?= isset($editar['data_lancamento']) ? date('Y-m-d', strtotime($editar['data_lancamento'])) : date('Y-m-d') ?>">

    <div class="box-qrcode">
        <label>Ler QRCode / Código de Barras do Envelope</label>

        <input type="text"
               id="codigo_barras"
               placeholder="Clique aqui e leia o QRCode do envelope"
               autocomplete="off"
               autofocus>

        <div id="reader"></div>

        <div class="aviso-camera">
            Se o navegador pedir permissão para usar a câmera, clique em <strong>Permitir</strong>.
        </div>

        <div id="mensagem_qrcode"></div>
    </div>

    <label>Membro</label>
    <select name="id_membro" id="id_membro" required>
        <option value="">Selecione</option>
        <?php foreach ($membros as $membro): ?>
            <option value="<?= $membro['id_membro'] ?>"
                <?= (isset($editar['id_membro']) && $editar['id_membro'] == $membro['id_membro']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($membro['nome_do_membro']) ?>
                <?= !empty($membro['codigo_barras']) ? ' - ' . htmlspecialchars($membro['codigo_barras']) : '' ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Valor do Dízimo em R$</label>
    <input type="number" name="valor_dizimo" required step="0.01"
           value="<?= htmlspecialchars($editar['valor_dizimo'] ?? '') ?>">

    <label>Forma do Recebimento</label>
    <select name="forma_de_pagamento_recebimento" required>
        <?php foreach (['Pix Recebido','Pix QR Code','Cartão Débito','Crédito em Conta','Transação Bancária'] as $fpr): ?>
            <option value="<?= $fpr ?>"><?= $fpr ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit"><?= $editar ? 'Atualizar' : 'Salvar' ?></button>

    <?php if ($editar): ?>
        <a href="dizimos.php">Cancelar</a>
    <?php endif; ?>
</form>

<h2>Lista de Lançamentos de Dízimos</h2>

<table border="1">
    <tr>
        <th>Data do Lançamento</th>
        <th>Código</th>
        <th>Membro</th>
        <th>Valor do Dízimo em R$</th>
        <th>Lançamento Financeiro</th>
        <th>Ações</th>
    </tr>

    <?php foreach ($dizimos as $d): ?>
        <tr>
            <td><?= htmlspecialchars(date('d/m/Y', strtotime($d['data_lancamento']))) ?></td>
            <td><?= htmlspecialchars($d['codigo_barras'] ?? '') ?></td>
            <td><?= htmlspecialchars($d['nome_do_membro']) ?></td>
            <td><?= 'R$ ' . number_format((float)$d['valor_dizimo'], 2, ',', '.') ?></td>
            <td><?= !empty($d['id_lancamento_financeiro']) ? 'Gerado' : 'Não gerado' ?></td>
            <td>
                <a href="<?= BASE_URL ?>cadastros/dizimos.php?edit=<?= $d['id_lancamento'] ?>">Editar</a>
                <a href="<?= BASE_URL ?>cadastros/dizimos.php?delete=<?= $d['id_lancamento'] ?>"
                   onclick="return confirm('Deseja excluir este lançamento de dízimo e o lançamento financeiro correspondente?')">
                   Excluir
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<script src="https://unpkg.com/html5-qrcode"></script>

<script>
function iniciarLeitorQRCode() {
    const areaLeitor = document.getElementById('reader');
    const mensagem = document.getElementById('mensagem_qrcode');

    if (!areaLeitor) {
        return;
    }

    const html5QrCode = new Html5Qrcode("reader");

    Html5Qrcode.getCameras().then(devices => {

        if (devices && devices.length) {

            //const cameraId = devices[0].id;

            html5QrCode.start(
                { facingMode: "environment" },
            {
                    fps: 10,
                    qrbox: 250
            },
                (decodedText, decodedResult) => {

                    const campoCodigo = document.getElementById('codigo_barras');

                    if (campoCodigo.value.trim() !== decodedText.trim()) {
                        campoCodigo.value = decodedText.trim();
                        campoCodigo.dispatchEvent(new Event('change'));
                    }
                },
                (errorMessage) => {
                    // Erros de leitura contínuos são normais enquanto não identifica QRCode.
                }
            ).catch(err => {
                mensagem.innerHTML = 'Não foi possível iniciar a câmera.';
                mensagem.className = 'erro';
                console.error(err);
            });

        } else {
            mensagem.innerHTML = 'Nenhuma câmera foi encontrada neste notebook.';
            mensagem.className = 'erro';
        }

    }).catch(err => {
        mensagem.innerHTML = 'Não foi possível acessar a câmera. Verifique a permissão do navegador.';
        mensagem.className = 'erro';
        console.error(err);
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const campoCodigo = document.getElementById('codigo_barras');
    const selectMembro = document.getElementById('id_membro');
    const mensagem = document.getElementById('mensagem_qrcode');

    iniciarLeitorQRCode();

    campoCodigo.addEventListener('change', function () {
        buscarMembroPorCodigo();
    });

    campoCodigo.addEventListener('keypress', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            buscarMembroPorCodigo();
        }
    });

    function buscarMembroPorCodigo() {
        const codigo = campoCodigo.value.trim();

        if (codigo === '') {
            return;
        }

        mensagem.innerHTML = 'Buscando membro...';
        mensagem.className = '';

        fetch('dizimos.php?buscar_codigo=' + encodeURIComponent(codigo))
            .then(response => response.json())
            .then(data => {
                if (data.encontrado) {
                    selectMembro.value = data.id_membro;

                    mensagem.innerHTML = 'Membro encontrado: ' + data.nome_do_membro;
                    mensagem.className = 'ok';

                    const campoValor = document.querySelector('input[name="valor_dizimo"]');
                    campoValor.focus();
                } else {
                    selectMembro.value = '';
                    mensagem.innerHTML = data.mensagem;
                    mensagem.className = 'erro';
                    campoCodigo.focus();
                    campoCodigo.select();
                }
            })
            .catch(error => {
                mensagem.innerHTML = 'Erro ao buscar o membro pelo QRCode.';
                mensagem.className = 'erro';
                console.error(error);
            });
    }
});
</script>

</body>
</html>