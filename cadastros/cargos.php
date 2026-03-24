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

    $id             = $_POST['id'] ?? null;
    $evento         = $_POST['descricao'] ?? '';


    if ($id) {


        $sql = "UPDATE cargos 
                SET descricao = :descricao
                WHERE id_curso = :id";

        $stmt = $con->prepare($sql);

        $stmt->bindParam(':descricao', $descricao);
        $stmt->bindParam(':id', $id);

    } else {

    $sql = "INSERT INTO cursos (descricao)
            VALUES (:descricao)";

    $stmt = $con->prepare($sql);

    $stmt->bindParam(':descricao', $descricao);

            }
        $stmt->execute();
}

    header("Location: cargos.php");
    exit;


/* =====================
   EXCLUIR
===================== */
if (isset($_GET['delete'])) {

    $id = $_GET['delete'];

    verificaPerfil(['ADMIN']);

    $sql = "DELETE FROM cargos WHERE id_cargo = :id";
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':id', $id);
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
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    $editar = $stmt->fetch(PDO::FETCH_ASSOC);
}

/* =====================
   LISTAR
===================== */
$stmt = $con->query("SELECT * FROM cargos ORDER BY descricao");
$cargos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Cargos</title>
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

<h2>Lista de Cargos</h2>

<table border="1">
<tr>
    <th>Descrição</th>
    <th>Ações</th>
</tr>

<?php foreach ($cargos as $c): ?>
<tr>
    <td><?= htmlspecialchars($c['descricao']) ?></td>
    <td>
        <a href="cargos.php?edit=<?= $c['id_cargo'] ?>">Editar</a>
        <a href="cargos.php?delete=<?= $c['id_cargo'] ?>"
           onclick="return confirm('Deseja excluir este cargo?')">
           Excluir
        </a>
    </td>
</tr>
<?php endforeach; ?>

</table>

</body>
</html>