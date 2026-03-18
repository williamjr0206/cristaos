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
        $sql = "UPDATE cargos SET descricao = :descricao
         WHERE id_cargo = :id";

        $stmt = $con->prepare($sql);
        $stmt->bindParam('id', $id);
        } else {

        $sql = "INSERT INTO cargos (descricao)
         VALUES (:descricao)";

        $stmt = $con->prepare($sql);

        $stmt -> bindParam(':descricao', $descricao);

    }

    $stmt->execute();
    header("Location: cargos.php");
    exit;
}

/* =====================
   EXCLUIR
===================== */
if (isset($_GET['delete'])) {

    $id = $_GET['delete'];
    verificaPerfil(['ADMIN']);

    $sql = "DELETE FROM cargos WHERE id_cargo = :id";
    $stmt = $con ->prepare($sql);
    $stmt->bindParam(':id',$id);
    $stmt->execute();

    header("Location: cargos.php");
    exit;
}

/* =====================
   EDITAR
===================== */
$editar = null;

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $con->prepare("SELECT * FROM cargos WHERE id_cargo = :id");
    $stmt->bindparam(':id', $id);
    $stmt->execute();
    $editar = $stmt->fetch(PDO::FETCH_ASSOC);
}

/* =====================
   LISTAR
===================== */
$stmt = $con -> query("SELECT * FROM cargos order by descricao");
$cargo = $stmt -> fetchAll(PDO::FETCH_ASSOC);

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

<h2><?= $editar ? 'Editar Cargo' : 'Novo Cargo' ?></h2>

<form method="post">
    <input type="hidden" name="id" value="<?= $editar['id_cargo'] ?? '' ?>">

    <label>Descrição do Cargo</label>
    <input name="descricao" required value="<?= htmlspecialchars($editar['descricao'] ?? '') ?>">


    <button type="submit"><?= $editar ? 'Atualizar' : 'Salvar' ?></button>

    <?php if ($editar): ?>
        <a href="cargos.php">Cancelar</a>
    <?php endif; ?>
</form>

<h2>Lista de Cargos na Igreja</h2>

<table>
    <tr>
        <th>Descrição:</th>
    </tr>

    <?php foreach ($cargo as $c): ?>
        <tr>
            <td><?= htmlspecialchars($c['descricao']) ?></td>
        <td>
                <a href="cargos.php?edit=<?= $c['id_cargo'] ?>">Editar</a>
                <a href="cargos.php?delete=<?= $c['id_cargo'] ?>"
                   onclick="return confirm('Deseja excluir este Cargo ?')">
                   Excluir
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
