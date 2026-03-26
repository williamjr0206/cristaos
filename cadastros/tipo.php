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

    $id          = $_POST['id'] ?? null;
    $tipo   = $_POST['descricao'] ?? '';

    if ($id) {
        $sql = "UPDATE tipo SET descricao = :descricao
         WHERE id_tipo = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam('id', $id);
        $stmt->bindParam(':descricao',$tipo);
        } else {

        $sql = "INSERT INTO tipo (descricao)
         VALUES (:descricao)";

        $stmt = $pdo->prepare($sql);

        $stmt -> bindParam(':descricao', $tipo);

    }

    $stmt->execute();
    header("Location: tipo.php");
    exit;
}

/* =====================
   EXCLUIR
===================== */
if (isset($_GET['delete'])) {

    $id = $_GET['delete'];
    verificaPerfil(['ADMIN','OPERADOR']);

    $sql = "DELETE FROM tipo WHERE id_tipo = :id";
    $stmt = $pdo ->prepare($sql);
    $stmt->bindParam(':id',$id);
    $stmt->execute();

    header("Location: tipo.php");
    exit;
}

/* =====================
   EDITAR
===================== */
$editar = null;

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM tipo WHERE id_tipo = :id");
    $stmt->bindparam(':id', $id);
    $stmt->execute();
    $editar = $stmt->fetch(PDO::FETCH_ASSOC);
}

/* =====================
   LISTAR
===================== */
$stmt = $pdo -> query("SELECT * FROM tipo order by descricao");
$tipo = $stmt -> fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Tipos de Membros</title>
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

    <label>Descrição do Tipo de Membro:</label>
    <input name="descricao" required value="<?= htmlspecialchars($editar['descricao'] ?? '') ?>">


    <button type="submit"><?= $editar ? 'Atualizar' : 'Salvar' ?></button>

    <?php if ($editar): ?>
        <a href="tipo.php">Cancelar</a>
    <?php endif; ?>
</form>

<h2>Lista dos Tipos de Membros nas Igrejas Cristãs:</h2>

<table>
    <tr>
        <th>Descrição do Tipo de Membro:</th>
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
