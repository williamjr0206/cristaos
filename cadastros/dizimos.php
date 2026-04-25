<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ob_start();

require __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
verificaAcesso();
require __DIR__ . '/../includes/menu.php';

/* =====================
   SALVAR / EDITAR
===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    date_default_timezone_set('America/Sao_Paulo');

    $id                     = $_POST['id'] ?? null;
    $data_lancamento        = $_POST['data_lancamento'] ?? '';
    $id_membro              = $_POST['id_membro'] ?? '';
    $valor_dizimo                = $_POST['valor_dizimo'] ?? '';
    
    if ($id) {
        $sql = "UPDATE dizimos SET
                    data_lancamento = :data_lancamento,
                    id_membro = :id_membro,
                    valor_dizimo = :valor_dizimo
                WHERE id_lancamento = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
    } else {
        $sql = "INSERT INTO dizimos (data_lancamento, id_membro,valor_dizimo) VALUES (:data_lancamento,
                :id_membro, :valor_dizimo)";

        $stmt = $pdo->prepare($sql);
    }

    $stmt->bindParam(':data_lancamento', $data_lancamento);
    $stmt->bindParam(':id_membro', $id_membro);
    $stmt->bindParam(':valor_dizimo', $valor_dizimo);
    $stmt->execute();

    header("Location: " . BASE_URL . "cadastros/dizimos.php");
    exit;
}

/* =====================
   EXCLUIR
===================== */
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $sql = "DELETE FROM dizimos WHERE id_lancamento = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    header("Location: " . BASE_URL . "cadastros/dizimos.php");
    exit;
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
$stmt = $pdo->query("SELECT id_membro, nome_do_membro FROM membros ORDER BY nome_do_membro");
$membros = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =====================
   LISTAR
===================== */
$stmt = $pdo->query("SELECT 
    id_lancamento,
    data_lancamento, 
    dizimos.id_membro, 
    valor_dizimo, 
    membros.nome_do_membro,
    membros.ativo 
FROM dizimos 
INNER JOIN membros 
    ON dizimos.id_membro = membros.id_membro 
WHERE membros.ativo = 1 
ORDER BY membros.nome_do_membro"
);


$dizimos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"charset="UTF-8">
    <title>Dízimos - Lançamentos</title>

    <style>
        body { font-family: Arial; margin: 20px; }
        form { margin-bottom: 30px; }
        input, select { margin: 6px 0; padding: 6px; width: 360px; display: block; }
        table { border-collapse: collapse; width: 100%; }
        a { margin-right: 10px; }

    </style>

</head>
<body>

<h2><?= $editar ? 'Editar Lançamento de Dízimo' : 'Novo Lançamento de Dízimo' ?></h2>

<form method="post">

    <input type="hidden" name="id" value="<?= $editar['id_lancamento'] ?? '' ?>">

    <label>Data do Lançamento</label>
    <input type="date" name="data_lancamento" required
        value="<?= isset($editar['data_lancamento']) ? date('Y-m-d', strtotime($editar['data_lancamento'])) : '' ?>">


    <label>Membros</label>
    <select name="id_membro" required>
        <option value="">Selecione</option>
        <?php foreach ($membros as $membro): ?>
            <option value="<?= $membro['id_membro'] ?>"
                <?= (isset($editar['id_membro']) && $editar['id_membro'] == $membro['id_membro']) ? 'selected' : '' ?>>
                <?= $membro['nome_do_membro'] ?>
            </option>
        <?php endforeach; ?>
    </select>

<label>Valor do Dízimo em R$</label>
<input type="number" name="valor_dizimo" required step="0.01" value="<?= $editar['valor_dizimo'] ?? '' ?>">


    <button type="submit"><?= $editar ? 'Atualizar' : 'Salvar' ?></button>

    <?php if ($editar): ?>
        <a href="dizimos.php">Cancelar</a>
    <?php endif; ?>
</form>

<h2>Lista de Lançamentos</h2>

<table border="1">
    <tr>
        <th>Data do Lançamento</th>
        <th>Membro</th>
        <th>Valor do Dízimo em R$</th>
        <th>Ações</th>
    </tr>

    <?php foreach ($dizimos as $d): ?>
        <tr>
            <td><?= htmlspecialchars($d['data_lancamento']) ?></td>
            <td><?= htmlspecialchars($d['nome_do_membro']) ?></td>
            <td><?= htmlspecialchars($d['valor_dizimo']) ?></td>
            <td>
                <a href="<?= BASE_URL ?>cadastros/dizimos.php?edit=<?= $d['id_lancamento'] ?>">Editar</a>
                <a href="<?= BASE_URL ?>cadastros/dizimos.php?delete=<?= $d['id_lancamento'] ?>"
                onclick="return confirm('Deseja excluir este Lançamento ?')">
                Excluir
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>