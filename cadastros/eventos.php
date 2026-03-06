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
            UPDATE eventos
            SET nome_do_curso = ? 
            WHERE id_evento = ?
        ");
        $stmt->bind_param("si", $nome_do_curso, $id);
    } else {
        $stmt = $conn->prepare("
            INSERT INTO cursos (descricao)
            VALUES (?)
        ");
        $stmt->bind_param("s", $descricao);
    }

    $stmt->execute();
    header("Location: eventos.php");
    exit;
}

/* =====================
   EXCLUIR
===================== */
if (isset($_GET['delete'])) {

    verificaPerfil(['ADMIN']);

    $stmt = $conn->prepare("DELETE FROM eventos WHERE id_evento = ?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();

    header("Location: eventos.php");
    exit;
}

/* =====================
   EDITAR
===================== */
$editar = null;

if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM eventos WHERE id_evento = ?");
    $stmt->bind_param("i", $_GET['edit']);
    $stmt->execute();
    $editar = $stmt->get_result()->fetch_assoc();
}

/* =====================
   LISTAR
===================== */
$eventos = [];

$result = $conn->query("SELECT * FROM eventos ORDER BY descricao");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $eventos[] = $row;
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

<h2><?= $editar ? 'Editar Evento' : 'Novo Evento' ?></h2>

<form method="post">
    <input type="hidden" name="id" value="<?= $editar['id_evento'] ?? '' ?>">

    <label>Descrição do Evento:</label>
    <input name="descricao" required value="<?= htmlspecialchars($editar['descricao'] ?? '') ?>">


    <button type="submit"><?= $editar ? 'Atualizar' : 'Salvar' ?></button>

    <?php if ($editar): ?>
        <a href="eventos.php">Cancelar</a>
    <?php endif; ?>
</form>

<h2>Lista dos Eventos nas Igrejas Cristãs:</h2>

<table>
    <tr>
        <th>Nome do Evento:</th>
        <th>Ações</th>
    </tr>

    <?php foreach ($cursos as $p): ?>
        <tr>
            <td><?= htmlspecialchars($p['descricao']) ?></td>
            <td>
                <a href="eventos.php?edit=<?= $p['id_evento'] ?>">Editar</a>
                <a href="eventos.php?delete=<?= $p['id_evento'] ?>"
                   onclick="return confirm('Deseja excluir este Evento ?')">
                   Excluir
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
