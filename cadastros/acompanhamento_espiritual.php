<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
verificaAcesso();
require __DIR__ . '/../includes/menu.php';


$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_membro = $_POST['id_membro'] ?? '';
    $id_tipo = $_POST['id_tipo'] ?? '';
    $id_status = $_POST['id_status'] ?? 1;
    $data_acompanhamento = $_POST['data_acompanhamento'] ?? date('Y-m-d');
    $responsavel = trim($_POST['responsavel'] ?? '');
    $situacao_espiritual = trim($_POST['situacao_espiritual'] ?? '');
    $assunto = trim($_POST['assunto'] ?? '');
    $observacao = trim($_POST['observacao'] ?? '');
    $proxima_acao = trim($_POST['proxima_acao'] ?? '');
    $data_retorno = $_POST['data_retorno'] ?: null;
    $prioridade = $_POST['prioridade'] ?? 'MEDIA';
    $sigiloso = isset($_POST['sigiloso']) ? 1 : 0;

    if ($id_membro && $id_tipo) {
        $sql = "INSERT INTO acompanhamento_espiritual
                (
                    id_membro, id_tipo, id_status, data_acompanhamento,
                    responsavel, situacao_espiritual, assunto,
                    observacao, proxima_acao, data_retorno,
                    prioridade, sigiloso
                )
                VALUES
                (
                    :id_membro, :id_tipo, :id_status, :data_acompanhamento,
                    :responsavel, :situacao_espiritual, :assunto,
                    :observacao, :proxima_acao, :data_retorno,
                    :prioridade, :sigiloso
                )";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_membro' => $id_membro,
            ':id_tipo' => $id_tipo,
            ':id_status' => $id_status,
            ':data_acompanhamento' => $data_acompanhamento,
            ':responsavel' => $responsavel,
            ':situacao_espiritual' => $situacao_espiritual,
            ':assunto' => $assunto,
            ':observacao' => $observacao,
            ':proxima_acao' => $proxima_acao,
            ':data_retorno' => $data_retorno,
            ':prioridade' => $prioridade,
            ':sigiloso' => $sigiloso
        ]);

        $mensagem = 'Acompanhamento espiritual registrado com sucesso.';
    } else {
        $mensagem = 'Informe o membro e o tipo de acompanhamento.';
    }
}

$membros = $pdo->query("
    SELECT id_membro, nome_do_membro 
    FROM membros 
    ORDER BY nome_do_membro
")->fetchAll(PDO::FETCH_ASSOC);

$tipos = $pdo->query("
    SELECT id, descricao 
    FROM acompanhamento_tipos 
    WHERE ativo = 1 
    ORDER BY descricao
")->fetchAll(PDO::FETCH_ASSOC);

$status = $pdo->query("
    SELECT id, descricao 
    FROM acompanhamento_status 
    WHERE ativo = 1 
    ORDER BY id
")->fetchAll(PDO::FETCH_ASSOC);

$registros = $pdo->query("
    SELECT 
        ae.id,
        ae.data_acompanhamento,
        ae.responsavel,
        ae.assunto,
        ae.prioridade,
        ae.data_retorno,
        ae.sigiloso,
        m.nome_do_membro AS membro,
        t.descricao AS tipo,
        s.descricao AS status
    FROM acompanhamento_espiritual ae
    INNER JOIN membros m ON m.id_membro = ae.id_membro
    INNER JOIN acompanhamento_tipos t ON t.id = ae.id_tipo
    INNER JOIN acompanhamento_status s ON s.id = ae.id_status
    ORDER BY ae.data_acompanhamento DESC, ae.id DESC
    LIMIT 100
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">

    <h3>Acompanhamento Espiritual</h3>

    <?php if ($mensagem): ?>
        <div class="alert alert-info">
            <?= htmlspecialchars($mensagem) ?>
        </div>
    <?php endif; ?>

    <form method="post" class="card p-3 mb-4">


    <style>
    body {
        font-family: Arial;
        margin: 20px;
    }

    form {
        margin-bottom: 30px;
    }

    input,
    select,
    textarea {
        margin: 6px 0;
        padding: 6px;
        width: 360px;
        display: block;
        box-sizing: border-box;
    }

    textarea {
        resize: vertical;
    }

    table {
        border-collapse: collapse;
        width: 100%;
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

    a {
        margin-right: 10px;
    }

    .mensagem {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
        padding: 10px;
        margin-bottom: 20px;
    }

    .erro {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
        padding: 10px;
        margin-bottom: 20px;
    }

    .linha {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }

    .campo {
        display: flex;
        flex-direction: column;
    }

    .checkbox {
        width: auto;
        display: inline-block;
    }

    h3,
    h5 {
        margin-top: 20px;
    }
</style>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Membro</label>
                <select name="id_membro" class="form-control" required>
                    <option value="">Selecione...</option>
                    <?php foreach ($membros as $m): ?>
                        <option value="<?= $m['id_membro'] ?>">
                            <?= htmlspecialchars($m['nome_do_membro']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label>Tipo</label>
                <select name="id_tipo" class="form-control" required>
                    <option value="">Selecione...</option>
                    <?php foreach ($tipos as $t): ?>
                        <option value="<?= $t['id'] ?>">
                            <?= htmlspecialchars($t['descricao']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label>Status</label>
                <select name="id_status" class="form-control">
                    <?php foreach ($status as $s): ?>
                        <option value="<?= $s['id'] ?>">
                            <?= htmlspecialchars($s['descricao']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <label>Data</label>
                <input type="date" name="data_acompanhamento" class="form-control"
                       value="<?= date('Y-m-d') ?>" required>
            </div>

            <div class="col-md-3">
                <label>Responsável</label>
                <input type="text" name="responsavel" class="form-control">
            </div>

            <div class="col-md-3">
                <label>Situação espiritual</label>
                <input type="text" name="situacao_espiritual" class="form-control">
            </div>

            <div class="col-md-3">
                <label>Prioridade</label>
                <select name="prioridade" class="form-control">
                    <option value="BAIXA">Baixa</option>
                    <option value="MEDIA" selected>Média</option>
                    <option value="ALTA">Alta</option>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label>Assunto</label>
            <input type="text" name="assunto" class="form-control">
        </div>

        <div class="mb-3">
            <label>Observação</label>
            <textarea name="observacao" class="form-control" rows="4"></textarea>
        </div>

        <div class="mb-3">
            <label>Próxima ação</label>
            <textarea name="proxima_acao" class="form-control" rows="3"></textarea>
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <label>Data de retorno</label>
                <input type="date" name="data_retorno" class="form-control">
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <div class="form-check">
                    <input type="checkbox" name="sigiloso" class="form-check-input" id="sigiloso">
                    <label class="form-check-label" for="sigiloso">
                        Registro sigiloso
                    </label>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">
            Salvar acompanhamento
        </button>

    </form>

    <h5>Últimos acompanhamentos</h5>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Membro</th>
                    <th>Tipo</th>
                    <th>Status</th>
                    <th>Responsável</th>
                    <th>Assunto</th>
                    <th>Prioridade</th>
                    <th>Retorno</th>
                    <th>Sigiloso</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registros as $r): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($r['data_acompanhamento'])) ?></td>
                        <td><?= htmlspecialchars($r['membro']) ?></td>
                        <td><?= htmlspecialchars($r['tipo']) ?></td>
                        <td><?= htmlspecialchars($r['status']) ?></td>
                        <td><?= htmlspecialchars($r['responsavel']) ?></td>
                        <td><?= htmlspecialchars($r['assunto']) ?></td>
                        <td><?= htmlspecialchars($r['prioridade']) ?></td>
                        <td>
                            <?= $r['data_retorno'] ? date('d/m/Y', strtotime($r['data_retorno'])) : '-' ?>
                        </td>
                        <td><?= $r['sigiloso'] ? 'Sim' : 'Não' ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($registros)): ?>
                    <tr>
                        <td colspan="9" class="text-center">
                            Nenhum acompanhamento registrado.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>