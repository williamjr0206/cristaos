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
    $descricao   = $_POST['descricao'] ?? '';

    if ($id) {
        $sql = "UPDATE eventos SET descricao = :descricao
         WHERE id_evento = :id";

        $stmt = $con->prepare($sql);
        $stmt->bindParam('id', $id);
        } else {

        $sql = "INSERT INTO eventos (descricao)
         VALUES (:descricao)";

        $stmt = $con->prepare($sql);

        $stmt -> bindParam(':descricao', $descricao);

    }

    $stmt->execute();
    header("Location: eventos.php");
    exit;
}

/* =====================
   EXCLUIR
===================== */
if (isset($_GET['delete'])) {

    $id = $_GET['delete'];
    verificaPerfil(['ADMIN']);

    $sql = "DELETE FROM eventos WHERE id_evento = :id";
    $stmt = $con ->prepare($sql);
    $stmt->bindParam(':id',$id);
    $stmt->execute();

    header("Location: eventos.php");
    exit;
}

/* =====================
   EDITAR
===================== */
$editar = null;

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $con->prepare("SELECT * FROM eventos WHERE id_evento = :id");
    $stmt->bindparam(':id', $id);
    $stmt->execute();
    $editar = $stmt->fetch(PDO::FETCH_ASSOC);
}

/* =====================
   LISTAR
===================== */
$stmt = $con -> query("SELECT * FROM eventos order by descricao");
$eventos = $stmt -> fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Igrejas Evangélicas</title>
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

<h2><?= $editar ? 'Editar Evento' : 'Novo Evento' ?></h2>

<form method="post">
    <input type="hidden" name="id" value="<?= $editar['id_evento'] ?? '' ?>">

    <label>Descrição do Cargo</label>
    <input name="descricao" required value="<?= htmlspecialchars($editar['descricao'] ?? '') ?>">


    <button type="submit"><?= $editar ? 'Atualizar' : 'Salvar' ?></button>

    <?php if ($editar): ?>
        <a href="eventos.php">Cancelar</a>
    <?php endif; ?>
</form>

<h2>Lista de Eventos na Igreja</h2>

<table>
    <tr>
        <th>Descrição:</th>
    </tr>

    <?php foreach ($eventos as $e): ?>
        <tr>
            <td><?= htmlspecialchars($e['descricao']) ?></td>
        <td>
                <a href="eventos.php?edit=<?= $e['id_evento'] ?>">Editar</a>
                <a href="eventos.php?delete=<?= $e['id_evento'] ?>"
                   onclick="return confirm('Deseja excluir este Evento ?')">
                   Excluir
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
