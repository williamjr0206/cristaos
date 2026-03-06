<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../config/database.php';
require __DIR__ . '/../config/auth.php';
require __DIR__ . '/../includes/menu.php';

verificaPerfil(['ADMIN','OPERADOR']);

/* =====================
   SALVAR / EDITAR
===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id        = $_POST['id'] ?? null;
    $descricao = $_POST['descricao'] ?? '';

    if ($id) {
        $stmt = $conn->prepare("
            UPDATE tipo
            SET descricao = ? 
            WHERE id_tipo = ?
        ");
        $stmt->bind_param("si", $descricao, $id);
    } else {
        $stmt = $conn->prepare("
            INSERT INTO tipo (descricao)
            VALUES (?)
        ");
        $stmt->bind_param("s", $descricao);
    }

    $stmt->execute();
    header("Location: tipo.php");
    exit;
}

/* =====================
   EXCLUIR
===================== */
if (isset($_GET['delete'])) {

    verificaPerfil(['ADMIN']);

    $stmt = $conn->prepare("DELETE FROM tipo WHERE id_tipo = ?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();

    header("Location: tipo.php");
    exit;
}

/* =====================
   EDITAR
===================== */
$editar = null;

if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM tipo WHERE id_tipo = ?");
    $stmt->bind_param("i", $_GET['edit']);
    $stmt->execute();
    $editar = $stmt->get_result()->fetch_assoc();
}

/* =====================
   LISTAR
===================== */
$tipo = [];

$result = $conn->query("SELECT * FROM tipo ORDER BY descricao");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $tipo[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Eventos</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        form { margin-bottom: 30px; }
        input { margin: 5px 0; padding: 6px; width: 300px; display: block; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 5px; }
        th { background: #eee; }
        a { margin-right: 10px; }
    </style>
</head>
<body>

<h2><?= $editar ? 'Editar Tipo de Membro' : 'Novo Tipo de Membro' ?></h2>

<form method="post">
    <input type="hidden" name="id" value="<?= $editar['id_tipo'] ?? '' ?>">

    <label>Descrição do Evento:</label>
    <input name="descricao" required value="<?= htmlspecialchars($editar['descricao'] ?? '') ?>">


    <button type="submit"><?= $editar ? 'Atualizar' : 'Salvar' ?></button>

    <?php if ($editar): ?>
        <a href="tipo.php">Cancelar</a>
    <?php endif; ?>
</form>

<h2>Lista dos Tipos de Membros nas Igrejas Cristãs:</h2>

<table>
    <tr>
        <th>Nome do Evento:</th>
        <th>Ações</th>
    </tr>

    <?php foreach ($tipo as $p): ?>
        <tr>
            <td><?= htmlspecialchars($p['descricao']) ?></td>
            <td>
                <a href="tipo.php?edit=<?= $p['id_tipo'] ?>">Editar</a>
                <a href="tipo.php?delete=<?= $p['id_tipo'] ?>"
                   onclick="return confirm('Deseja excluir este Tipo de Membro ?')">
                   Excluir
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
