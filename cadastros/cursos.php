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

    $id            = $_POST['id'] ?? null;
    $nome_do_curso = $_POST['nome_do_curso'] ?? '';

    if ($id) {
        $stmt = $conn->prepare("
            UPDATE cursos
            SET nome_do_curso = ? 
            WHERE id_curso = ?
        ");
        $stmt->bind_param("si", $nome_do_curso, $id);
    } else {
        $stmt = $conn->prepare("
            INSERT INTO cursos (nome_do_curso)
            VALUES (?)
        ");
        $stmt->bind_param("s", $descricao);
    }

    $stmt->execute();
    header("Location: cursos.php");
    exit;
}

/* =====================
   EXCLUIR
===================== */
if (isset($_GET['delete'])) {

    verificaPerfil(['ADMIN']);

    $stmt = $conn->prepare("DELETE FROM cursoos WHERE id_curso = ?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();

    header("Location: cursos.php");
    exit;
}

/* =====================
   EDITAR
===================== */
$editar = null;

if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM cursos WHERE id_curso = ?");
    $stmt->bind_param("i", $_GET['edit']);
    $stmt->execute();
    $editar = $stmt->get_result()->fetch_assoc();
}

/* =====================
   LISTAR
===================== */
$cursos = [];

$result = $conn->query("SELECT * FROM cursos ORDER BY nome_do_curso");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $cursos[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cursos</title>
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

<h2><?= $editar ? 'Editar Cargo' : 'Novo Cargo' ?></h2>

<form method="post">
    <input type="hidden" name="id" value="<?= $editar['id_cargo'] ?? '' ?>">

    <label>Descrição do Cargo:</label>
    <input name="nome_do_curso" required value="<?= htmlspecialchars($editar['nome_do_curso'] ?? '') ?>">


    <button type="submit"><?= $editar ? 'Atualizar' : 'Salvar' ?></button>

    <?php if ($editar): ?>
        <a href="cursos.php">Cancelar</a>
    <?php endif; ?>
</form>

<h2>Lista dos Cursos nas Igrejas Cristãs:</h2>

<table>
    <tr>
        <th>Nome do Curso:</th>
        <th>Ações</th>
    </tr>

    <?php foreach ($cursos as $p): ?>
        <tr>
            <td><?= htmlspecialchars($p['nome_do_curso']) ?></td>
            <td>
                <a href="cursos.php?edit=<?= $p['id_curso'] ?>">Editar</a>
                <a href="cursos.php?delete=<?= $p['id_curso'] ?>"
                   onclick="return confirm('Deseja excluir este curso ?')">
                   Excluir
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
