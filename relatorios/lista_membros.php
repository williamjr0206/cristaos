<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../config/database.php';
require __DIR__ . '/../config/auth.php';
verificaAcesso();
require __DIR__ . '/../includes/menu.php';

/* =====================
   LISTAR
===================== */
$stmt = $pdo->query("SELECT * FROM membros ORDER BY nome_do_membro");
$membros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lista de Membros</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        form { margin-bottom: 30px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 5px; }
        th { background: #eee; }
    </style>
</head>
<body>

<h1>Lista dos Membros da Igreja:</h1>

<table>
    <tr>
        <th>Nome do Membro</th>
        <th>Id do Membro</th>
    </tr>

    <?php foreach ($membros as $m): ?>
        <tr>
            <td><?= htmlspecialchars($m['nome_do_membro']) ?></td>
            <td><?= htmlspecialchars($m['id_membro']) ?></td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>