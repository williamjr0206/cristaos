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
    $nome_do_professor = $_POST['nome_do_professor'] ?? '';

    if ($id) {
        $stmt = $conn->prepare("
            UPDATE professores
            SET nome_do_professor = ? 
            WHERE id_professor = ?
        ");
        $stmt->bind_param("si", $nome_do_professor, $id);
    } else {
        $stmt = $conn->prepare("
            INSERT INTO professores (nome_do_professor)
            VALUES (?)
        ");
        $stmt->bind_param("s", $nome_do_professor);
    }

    $stmt->execute();
    header("Location: professores.php");
    exit;
}

/* =====================
   EXCLUIR
===================== */
if (isset($_GET['delete'])) {

    verificaPerfil(['ADMIN']);

    $stmt = $conn->prepare("DELETE FROM professores WHERE id_professor = ?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();

    header("Location: professores.php");
    exit;
}

/* =====================
   EDITAR
===================== */
$editar = null;

if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM professores WHERE id_professor = ?");
    $stmt->bind_param("i", $_GET['edit']);
    $stmt->execute();
    $editar = $stmt->get_result()->fetch_assoc();
}

/* =====================
   LISTAR
===================== */
$professores = [];

$result = $conn->query("SELECT * FROM professores ORDER BY nome_do_professor");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $professores[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Professores</title>
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

<h2><?= $editar ? 'Editar Professor' : 'Novo Professor' ?></h2>

<form method="post">
    <input type="hidden" name="id" value="<?= $editar['id_professor'] ?? '' ?>">

    <label>Nome do Professor(a):</label>
    <input name="nome_do_professor" required value="<?= htmlspecialchars($editar['nome_do_professor'] ?? '') ?>">


    <button type="submit"><?= $editar ? 'Atualizar' : 'Salvar' ?></button>

    <?php if ($editar): ?>
        <a href="professore.php">Cancelar</a>
    <?php endif; ?>
</form>

<h2>Lista dos Professores(as) nas Igrejas Cristãs:</h2>

<table>
    <tr>
        <th>Nome do Professor(a):</th>
        <th>Ações</th>
    </tr>

    <?php foreach ($professores as $p): ?>
        <tr>
            <td><?= htmlspecialchars($p['nome_do_professor']) ?></td>
            <td>
                <a href="professores.php?edit=<?= $p['id_professor'] ?>">Editar</a>
                <a href="professores.php?delete=<?= $p['id_professor'] ?>"
                   onclick="return confirm('Deseja excluir este Professor ?')">
                   Excluir
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
