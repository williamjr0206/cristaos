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
    $nome        = $_POST['nome'] ?? '';
    $denominacao = $_POST['denominacao'] ?? '';
    $pais        = $_POST['pais'] ?? '';
    $estado      = $_POST['estado'] ?? '';
    $municipio   = $_POST['municipio'] ?? '';
    $endereco    = $_POST['endereco'] ?? '';    
    $cep         = $_POST['cep'] ?? '';
    $latitude    = $_POST['latitude'] ?? '';
    $longitude   = $_POST['longitude'] ?? '';

    if ($id) {
        $stmt = $conn->prepare("
            UPDATE igrejas
            SET nome = ?, denominacao = ?, pais = ?, estado = ?, municipio = ?,
            endereco = ?, cep = ?, latitude = ?, longitude = ? 
            WHERE id_igreja = ?
        ");
        $stmt->bind_param("ssssssissi", $nome, $denominacao, $pais, $estado,$municipio,$endereco,$cep,
        $latitude,$longitude, $id);
    } else {
        $stmt = $conn->prepare("
            INSERT INTO igrejas (nome, denominacao, pais, estado, municipio, 
            endereco, cep, latitude, longitude)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssssssiss", $nome, $denominacao, $pais, $estado, $municipio, $endereco,
        $cep, $latitude, $longitude);
    }

    $stmt->execute();
    header("Location: igrejas.php");
    exit;
}

/* =====================
   EXCLUIR
===================== */
if (isset($_GET['delete'])) {

    verificaPerfil(['ADMIN']);

    $stmt = $conn->prepare("DELETE FROM igrejas WHERE id_igreja = ?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();

    header("Location: igrejas.php");
    exit;
}

/* =====================
   EDITAR
===================== */
$editar = null;

if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM igrejas WHERE id_igreja = ?");
    $stmt->bind_param("i", $_GET['edit']);
    $stmt->execute();
    $editar = $stmt->get_result()->fetch_assoc();
}

/* =====================
   LISTAR
===================== */
$igrejas = [];

$result = $conn->query("SELECT * FROM igrejas ORDER BY nome");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $igrejas[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Igrejas</title>
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

<h2><?= $editar ? 'Editar Igreja' : 'Nova Igreja' ?></h2>

<form method="post">
    <input type="hidden" name="id" value="<?= $editar['id_igreja'] ?? '' ?>">

    <label>Nome</label>
    <input name="nome" required value="<?= htmlspecialchars($editar['nome'] ?? '') ?>">

    <label>Denominação:</label>
    <input name="denominacao" value="<?= htmlspecialchars($editar['denominacao'] ?? '') ?>">

    <label>Pais:</label>
    <input name="pais" value="<?= htmlspecialchars($editar['pais'] ?? '') ?>">

    <label>Estado:</label>
    <input  name="estado" value="<?= htmlspecialchars($editar['estado'] ?? '') ?>">

    <label>Município:</label>
    <input  name="municipio" value="<?= htmlspecialchars($editar['municipio'] ?? '') ?>">

    <label>Endereço:</label>
    <input  name="endereco" value="<?= htmlspecialchars($editar['endereco'] ?? '') ?>">


    <label>Cep:</label>
    <input name = "cep" value="<?= htmlspecialchars($editar['cep'] ?? '') ?>">

    <label>Latitude:</label>
    <input  name="latitude" value="<?= htmlspecialchars($editar['latitude'] ?? '') ?>">

    <label>Longitude:</label>
    <input  name="longitude" value="<?= htmlspecialchars($editar['longitude'] ?? '') ?>">

    <button type="submit"><?= $editar ? 'Atualizar' : 'Salvar' ?></button>

    <?php if ($editar): ?>
        <a href="igrejas.php">Cancelar</a>
    <?php endif; ?>
</form>

<h2>Lista de Igrejas Cristãs Cadastradas</h2>

<table>
    <tr>
        <th>Nome</th>
        <th>Pais</th>
        <th>Cidade</th>
        <th>Cep</th>
        <th>Endereço</th>
        <th>Lat.</th>
        </tr>Long.</th>
        <th>Ações</th>
    </tr>

    <?php foreach ($igrejas as $p): ?>
        <tr>
            <td><?= htmlspecialchars($p['nome']) ?></td>
            <td><?= htmlspecialchars($p['pais']) ?></td>
            <td><?= htmlspecialchars($p['municipio']) ?></td>
            <td><?= htmlspecialchars($p['cep']) ?></td>
            <td><?= htmlspecialchars($p['endereco']) ?></td>
            <td><?= htmlspecialchars($p['latitude']) ?></td>
            <td><?= htmlspecialchars($p['longitude']) ?></td>
            <td>
                <a href="igrejas.php?edit=<?= $p['id_igreja'] ?>">Editar</a>
                <a href="igrejas.php?delete=<?= $p['id_igreja'] ?>"
                   onclick="return confirm('Deseja excluir esta Igreja ?')">
                   Excluir
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
