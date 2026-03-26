<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../config/database.php';
require __DIR__ . '/../config/auth.php';
require __DIR__ . '/../includes/menu.php';

verificaPerfil(['ADMIN','OPERADOR','LIDER']);

/* =====================
   SALVAR / EDITAR
===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id          = $_POST['id'] ?? null;
    $nomedocurso   = $_POST['nome_do_curso'] ?? '';

    if ($id) {
        $sql = "UPDATE cursos SET nome_do_curso = :curso
         WHERE id_curso = :id";

        $stmt = $con->prepare($sql);
        $stmt->bindParam('id', $id);
        $stmt->bindParam(':curso',$nomedocurso);
        } else {

        $sql = "INSERT INTO cursos (nome_do_curso)
         VALUES (:curso)";

        $stmt = $pdo->prepare($sql);

        $stmt -> bindParam(':curso', $nomedocurso);

    }

    $stmt->execute();
    header("Location: cursos.php");
    exit;
}

/* =====================
   EXCLUIR
===================== */
if (isset($_GET['delete'])) {

    $id = $_GET['delete'];
    verificaPerfil(['ADMIN','LIDER']);

    $sql = "DELETE FROM cursos WHERE id_curso = :id";
    $stmt = $pdo ->prepare($sql);
    $stmt->bindParam(':id',$id);
    $stmt->execute();

    header("Location: cursos.php");
    exit;
}

/* =====================
   EDITAR
===================== */
$editar = null;

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM cursos WHERE id_curso = :id");
    $stmt->bindparam(':id', $id);
    $stmt->execute();
    $editar = $stmt->fetch(PDO::FETCH_ASSOC);
}

/* =====================
   LISTAR
===================== */

$stmt = $pdo -> query("SELECT * FROM cursos order by nome_do_curso");
$cursos = $stmt -> fetchAll(PDO::FETCH_ASSOC);

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
        select { margin: 5px 0; padding: 6px; width: 300px; display: block; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 5px; }
        th { background: #eee; }
        a { margin-right: 10px; }
    </style>
</head>
<body>

<h2><?= $editar ? 'Editar Curso' : 'Novo Curso' ?></h2>

<form method="post">
    <input type="hidden" name="id" value="<?= $editar['id_curso'] ?? '' ?>">

    <label>Descrição do Curso</label>
    <input name="nome_do_curso" required value="<?= htmlspecialchars($editar['nome_do_curso'] ?? '') ?>">


    <button type="submit"><?= $editar ? 'Atualizar' : 'Salvar' ?></button>

    <?php if ($editar): ?>
        <a href="cursos.php">Cancelar</a>
    <?php endif; ?>
</form>

<h2>Lista de Cursos na Igreja</h2>

<table>
    <tr>
        <th>Curso:</th>
    </tr>

    <?php foreach ($cursos as $c): ?>
        <tr>
            <td><?= htmlspecialchars($c['nome_do_curso']) ?></td>
        <td>
                <a href="cursos.php?edit=<?= $c['id_curso'] ?>">Editar</a>
                <a href="cursos.php?delete=<?= $c['id_curso'] ?>"
                   onclick="return confirm('Deseja excluir este Curso ?')">
                   Excluir
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
