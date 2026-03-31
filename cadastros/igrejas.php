<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ob_start();

require __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
verificaAcesso();

require __DIR__ . '/../includes/menu.php';

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
        $sql = "UPDATE igrejas SET
                    nome = :nome,
                    denominacao = :denominacao,
                    pais = :pais,
                    estado = :estado,
                    municipio = :municipio,
                    endereco = :endereco,
                    cep = :cep,
                    latitude = :latitude,
                    longitude = :longitude
                WHERE id_igreja = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
    } else {

        $sql = "INSERT INTO igrejas
                    (nome, denominacao, pais, estado, municipio, endereco, cep, latitude, longitude)
                VALUES
                    (:nome, :denominacao, :pais, :estado, :municipio, :endereco, :cep, :latitude, :longitude)";

        $stmt = $pdo->prepare($sql);
    }

    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':denominacao', $denominacao);
    $stmt->bindParam(':pais', $pais);
    $stmt->bindParam(':estado', $estado);
    $stmt->bindParam(':municipio', $municipio);
    $stmt->bindParam(':endereco', $endereco);
    $stmt->bindParam(':cep', $cep);
    $stmt->bindParam(':latitude', $latitude);
    $stmt->bindParam(':longitude', $longitude);

    $stmt->execute();
    header("Location: " . BASE_URL . "cadastros/igrejas.php");
    exit;
}

/* =====================
   EXCLUIR
===================== */
if (isset($_GET['delete'])) {

    $id = $_GET['delete'];

    $sql = "DELETE FROM igrejas WHERE id_igreja = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    header("Location: " . BASE_URL . "cadastros/igrejas.php");
    exit;
}

/* =====================
   EDITAR
===================== */
$editar = null;

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM igrejas WHERE id_igreja = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $editar = $stmt->fetch(PDO::FETCH_ASSOC);
}

/* =====================
   LISTAR
===================== */
$stmt = $pdo->query("SELECT * FROM igrejas ORDER BY nome");
$igrejas = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" charset="UTF-8">
    <title>Igrejas</title>
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

<h2><?= $editar ? 'Editar Igreja' : 'Nova Igreja' ?></h2>

<form method="post">
    <input type="hidden" name="id" value="<?= $editar['id_igreja'] ?? '' ?>">

    <label>Nome</label>
    <input name="nome" required value="<?= htmlspecialchars($editar['nome'] ?? '') ?>">

    <label>Denominação</label>
    <input name="denominacao" required value="<?= htmlspecialchars($editar['denominacao'] ?? '') ?>">

    <label>País</label>
    <input name="pais" required value="<?= htmlspecialchars($editar['pais'] ?? '') ?>">

    <label>Estado</label>
    <input name="estado" required value="<?= htmlspecialchars($editar['estado'] ?? '') ?>">

    <label>Município</label>
    <input name="municipio" required value="<?= htmlspecialchars($editar['municipio'] ?? '') ?>">

    <label>Endereço</label>
    <input name="endereco" required value="<?= htmlspecialchars($editar['endereco'] ?? '') ?>">

    <label>Cep</label>
    <input name="cep" value="<?= htmlspecialchars($editar['cep'] ?? '') ?>">

    <label>Latitude</label>
    <input name="latitude" value="<?= htmlspecialchars($editar['latitude'] ?? '') ?>">

    <label>Longitude</label>
    <input name="longitude" value="<?= htmlspecialchars($editar['longitude'] ?? '') ?>">

    <button type="submit"><?= $editar ? 'Atualizar' : 'Salvar' ?></button>

    <?php if ($editar): ?>
        <a href="igrejas.php">Cancelar</a>
    <?php endif; ?>
</form>

<h2>Lista de Igreja Evangélicas</h2>

<table>
    <tr>
        <th>Nome</th>
        <th>Denominação</th>
        <th>Endereço</th>
    </tr>

    <?php foreach ($igrejas as $i): ?>
        <tr>
            <td><?= htmlspecialchars($i['nome']) ?></td>
            <td><?= htmlspecialchars($i['denominacao']) ?></td>
            <td><?= htmlspecialchars($i['endereco']) ?></td>
            <td>
                <a href="<?= BASE_URL ?>cadastros/igrejas.php?edit=<?= $i['id_igreja'] ?>">Editar</a>
                <a href="<?= BASE_URL ?>cadastros/igrejas.php?delete=<?= $i['id_igreja'] ?>"
                   onclick="return confirm('Deseja excluir esta Igreja ?')">
                   Excluir
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>