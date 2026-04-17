<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ob_start();

require __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
verificaPerfil(['ADMIN']);
require __DIR__ . '/../includes/menu.php';

/* =====================
   SALVAR / EDITAR
===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    date_default_timezone_set('America/Sao_Paulo');

    $id                = $_POST['id'] ?? null;
    $numero_livro      = $_POST['numero_livro'] ?? '';
    $reuniao_numero    = $_POST['reuniao_numero'] ?? '';
    $datareuniao_html  = $_POST['data_reuniao'] ?? '';
    $data_reuniao      = !empty($datareuniao_html) ? date('Y-m-d H:i:s', strtotime($datareuniao_html)) : null;
    $id_igreja         = $_POST['id_igreja'] ?? '';
    $ata_texto         = $_POST['ata_texto'] ?? '';
    $presencas         = $_POST['presencas'] ?? [];

    if ($id) {
        $sql = "UPDATE atas SET
                    numero_livro = :numero_livro,
                    reuniao_numero = :reuniao_numero,
                    data_reuniao = :data_reuniao,
                    id_igreja = :id_igreja,
                    ata_texto = :ata_texto
                WHERE id_ata = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':numero_livro', $numero_livro);
        $stmt->bindParam(':reuniao_numero', $reuniao_numero);
        $stmt->bindParam(':data_reuniao', $data_reuniao);
        $stmt->bindParam(':id_igreja', $id_igreja);
        $stmt->bindParam(':ata_texto', $ata_texto);
        $stmt->execute();

        $id_ata = (int)$id;
    } else {
        $sql = "INSERT INTO atas (
                    numero_livro,
                    reuniao_numero,
                    data_reuniao,
                    id_igreja,
                    ata_texto
                ) VALUES (
                    :numero_livro,
                    :reuniao_numero,
                    :data_reuniao,
                    :id_igreja,
                    :ata_texto
                )";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':numero_livro', $numero_livro);
        $stmt->bindParam(':reuniao_numero', $reuniao_numero);
        $stmt->bindParam(':data_reuniao', $data_reuniao);
        $stmt->bindParam(':id_igreja', $id_igreja);
        $stmt->bindParam(':ata_texto', $ata_texto);
        $stmt->execute();

        $id_ata = (int)$pdo->lastInsertId();
    }

    /* =====================
       SALVAR PRESENÇAS
    ====================== */
    $stmtDel = $pdo->prepare("DELETE FROM presencas_atas WHERE id_ata = :id_ata");
    $stmtDel->bindParam(':id_ata', $id_ata, PDO::PARAM_INT);
    $stmtDel->execute();

    if (!empty($presencas) && is_array($presencas)) {
        $stmtIns = $pdo->prepare("
            INSERT INTO presencas_atas (id_ata, id_membro)
            VALUES (:id_ata, :id_membro)
        ");

        foreach ($presencas as $id_membro) {
            if (!empty($id_membro)) {
                $id_membro = (int)$id_membro;
                $stmtIns->bindParam(':id_ata', $id_ata, PDO::PARAM_INT);
                $stmtIns->bindParam(':id_membro', $id_membro, PDO::PARAM_INT);
                $stmtIns->execute();
            }
        }
    }

    header("Location: " . BASE_URL . "cadastros/atas.php");
    exit;
}

/* =====================
   EXCLUIR
===================== */
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    $stmtDelPres = $pdo->prepare("DELETE FROM presencas_atas WHERE id_ata = :id_ata");
    $stmtDelPres->bindParam(':id_ata', $id, PDO::PARAM_INT);
    $stmtDelPres->execute();

    $stmt = $pdo->prepare("DELETE FROM atas WHERE id_ata = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    header("Location: " . BASE_URL . "cadastros/atas.php");
    exit;
}

/* =====================
   EDITAR
===================== */
$editar = null;
$presencas_marcadas = [];

if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];

    $stmt = $pdo->prepare("SELECT * FROM atas WHERE id_ata = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $editar = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmtPres = $pdo->prepare("SELECT id_membro FROM presencas_atas WHERE id_ata = :id_ata");
    $stmtPres->bindParam(':id_ata', $id, PDO::PARAM_INT);
    $stmtPres->execute();
    $presencas_marcadas = array_map('strval', $stmtPres->fetchAll(PDO::FETCH_COLUMN));
}

/* =====================
   SELECTS
===================== */
$stmt2 = $pdo->query("SELECT id_igreja, nome FROM igrejas ORDER BY nome");
$igrejas = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$stmtMembros = $pdo->query("
    SELECT
        m.id_membro,
        m.nome_do_membro,
        m.id_cargo,
        c.descricao AS cargo
    FROM membros m
    INNER JOIN cargos c ON m.id_cargo = c.id_cargo
    WHERE m.ativo = 1
      AND m.id_cargo IN (4, 5)
    ORDER BY m.nome_do_membro
");
$membros = $stmtMembros->fetchAll(PDO::FETCH_ASSOC);

/* =====================
   LISTAR
===================== */
$stmt = $pdo->query("
    SELECT
        a.id_ata,
        a.numero_livro,
        a.reuniao_numero,
        a.data_reuniao,
        a.id_igreja,
        a.ata_texto,
        i.nome AS igreja,
        (
            SELECT COUNT(*)
            FROM presencas_atas pa
            WHERE pa.id_ata = a.id_ata
        ) AS total_presencas
    FROM atas a
    INNER JOIN igrejas i ON a.id_igreja = i.id_igreja
    ORDER BY a.data_reuniao DESC, a.reuniao_numero DESC
");
$atas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" charset="UTF-8">
    <title>Cadastro de Atas</title>

    <style>
        body { font-family: Arial; margin: 20px; }
        form { margin-bottom: 30px; }
        input, select, textarea { margin: 6px 0; padding: 6px; width: 360px; display: block; }
        textarea { width: 100%; max-width: 1000px; min-height: 260px; resize: vertical; }
        table { border-collapse: collapse; width: 100%; }
        a { margin-right: 10px; }

        .presencas-box {
            margin-top: 20px;
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background: #fafafa;
            max-width: 1000px;
        }

        .presencas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 8px 20px;
            margin-top: 10px;
        }

        .presencas-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .presencas-item input[type="checkbox"] {
            width: auto;
            display: inline-block;
            margin: 0;
            padding: 0;
        }

        .presencas-item label {
            margin: 0;
            display: inline;
        }

        .acoes-presencas {
            margin-top: 10px;
        }

        .acoes-presencas button {
            margin-right: 8px;
            padding: 8px 12px;
            cursor: pointer;
        }
    </style>

    <script>
        function marcarTodos() {
            const checks = document.querySelectorAll('.check-presenca');
            checks.forEach(c => c.checked = true);
        }

        function desmarcarTodos() {
            const checks = document.querySelectorAll('.check-presenca');
            checks.forEach(c => c.checked = false);
        }
    </script>
</head>
<body>

<h2><?= $editar ? 'Editar Ata' : 'Nova Ata' ?></h2>

<form method="post">

    <input type="hidden" name="id" value="<?= $editar['id_ata'] ?? '' ?>">

    <label>Número do Livro</label>
    <input type="number" name="numero_livro" required
           value="<?= htmlspecialchars($editar['numero_livro'] ?? '18') ?>">

    <label>Número da Reunião</label>
    <input  name="reuniao_numero" required
           value="<?= htmlspecialchars($editar['reuniao_numero'] ?? '') ?>">

    <label>Data da Reunião</label>
    <input type="datetime-local" name="data_reuniao"
           value="<?= isset($editar['data_reuniao']) && !empty($editar['data_reuniao']) ? date('Y-m-d\TH:i', strtotime($editar['data_reuniao'])) : '' ?>">

    <label>Igreja</label>
    <select name="id_igreja" required>
        <option value="">Selecione</option>
        <?php foreach ($igrejas as $igreja): ?>
            <option value="<?= $igreja['id_igreja'] ?>"
                <?= (isset($editar['id_igreja']) && $editar['id_igreja'] == $igreja['id_igreja']) ? 'selected' : '' ?>
                <?= (!isset($editar['id_igreja']) && $igreja['id_igreja'] == 3) ? 'selected' : '' ?>>
                <?= htmlspecialchars($igreja['nome']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Texto da Ata</label>
    <textarea name="ata_texto" required><?= htmlspecialchars($editar['ata_texto'] ?? '') ?></textarea>

    <div class="presencas-box">
        <h3>Presenças da Ata</h3>

        <div class="acoes-presencas">
            <button type="button" onclick="marcarTodos()">Marcar Todos</button>
            <button type="button" onclick="desmarcarTodos()">Desmarcar Todos</button>
        </div>

        <div class="presencas-grid">
            <?php foreach ($membros as $m): ?>
                <div class="presencas-item">
                    <input
                        class="check-presenca"
                        type="checkbox"
                        name="presencas[]"
                        value="<?= $m['id_membro'] ?>"
                        id="membro_<?= $m['id_membro'] ?>"
                        <?= in_array((string)$m['id_membro'], $presencas_marcadas, true) ? 'checked' : '' ?>
                    >
                    <label for="membro_<?= $m['id_membro'] ?>">
                        <?= htmlspecialchars($m['nome_do_membro']) ?>
                        - <small><?= htmlspecialchars($m['cargo']) ?></small>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <button type="submit"><?= $editar ? 'Atualizar' : 'Salvar' ?></button>

    <?php if ($editar): ?>
        <a href="atas.php">Cancelar</a>
    <?php endif; ?>
</form>

<h2>Lista de Atas</h2>

<table border="1">
    <tr>
        <th>Livro</th>
        <th>Reunião</th>
        <th>Data</th>
        <th>Igreja</th>
        <th>Presenças</th>
        <th>Resumo</th>
        <th>Ações</th>
    </tr>

    <?php foreach ($atas as $a): ?>
        <tr>
            <td><?= htmlspecialchars($a['numero_livro']) ?></td>
            <td><?= htmlspecialchars($a['reuniao_numero']) ?></td>
            <td>
                <?php
                    if (!empty($a['data_reuniao']) && $a['data_reuniao'] != '0000-00-00 00:00:00') {
                        echo date('d/m/Y H:i', strtotime($a['data_reuniao']));
                    }
                ?>
            </td>
            <td><?= htmlspecialchars($a['igreja']) ?></td>
            <td><?= (int)$a['total_presencas'] ?></td>
            <td>
                <?php
                    $resumo = trim($a['ata_texto'] ?? '');
                    if (mb_strlen($resumo) > 150) {
                        $resumo = mb_substr($resumo, 0, 150) . '...';
                    }
                    echo htmlspecialchars($resumo);
                ?>
            </td>
            <td>
                <a href="atas.php?edit=<?= $a['id_ata'] ?>">Editar</a>
                <a href="atas.php?delete=<?= $a['id_ata'] ?>"
                   onclick="return confirm('Deseja excluir mesmo esta ata e suas presenças relacionadas?')">Excluir</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>