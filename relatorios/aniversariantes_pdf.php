<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/menu.php';
require __DIR__ . '/../config/auth.php';
verificaAcesso();

// Recebe datas
$data_inicio = $_GET['inicio'] ?? '';
$data_fim    = $_GET['fim'] ?? '';

$sql = "SELECT nome_do_membro, data_nascimento
        FROM membros
        WHERE DATE_FORMAT(data_nascimento, '%m-%d') 
        BETWEEN DATE_FORMAT(:inicio, '%m-%d') 
        AND DATE_FORMAT(:fim, '%m-%d')
        ORDER BY DATE_FORMAT(data_nascimento, '%m-%d')";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':inicio' => $data_inicio,
    ':fim' => $data_fim
]);

$lista = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Aniversariantes</title>

<style>
body {
    font-family: Arial;
}

h2 {
    text-align: center;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    border: 1px solid #000;
    padding: 8px;
    text-align: left;
}
</style>

</head>
<body>

<h2>🎂 Lista de Aniversariantes</h2>

<table>
<tr>
    <th>Nome</th>
    <th>Data</th>
</tr>

<?php foreach ($lista as $l): ?>
<tr>
    <td><?= $l['nome_do_membro'] ?></td>
    <td><?= date('d/m', strtotime($l['data_nascimento'])) ?></td>
</tr>
<?php endforeach; ?>

</table>

<script>
window.print();
</script>

</body>
</html>