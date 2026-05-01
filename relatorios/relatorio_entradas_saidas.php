<?php
require __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
verificaAcesso();
require __DIR__ . '/../includes/menu.php';

$inicio = $_GET['inicio'] ?? '';
$fim    = $_GET['fim'] ?? '';

$dados = [];

if ($inicio && $fim) {

    $stmt = $pdo->prepare("
        SELECT 
            m.nome_do_membro,
            h.status,
            h.motivo,
            h.data_evento,
            h.numero_livro_ata,
            h.numero_ata

        FROM historico_membro h
        INNER JOIN membros m ON m.id_membro = h.id_membro

        WHERE DATE(h.data_evento) BETWEEN :inicio AND :fim

        ORDER BY h.data_evento DESC
    ");

    $stmt->execute([
        ':inicio' => $inicio,
        ':fim' => $fim
    ]);

    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Relatório Entradas e Saídas</title>
<style>
body { font-family: Arial; margin: 20px; }
input { padding: 6px; margin: 5px; }
table { border-collapse: collapse; width: 100%; }
th, td { padding: 8px; border: 1px solid #ccc; }
th { background: #2c3e50; color: white; }
</style>
</head>
<body>

<h2>Relatório de Entradas e Saídas</h2>

<form method="get">
    <label>Data Inicial</label>
    <input type="date" name="inicio" required>

    <label>Data Final</label>
    <input type="date" name="fim" required>

    <button type="submit">Filtrar</button>
</form>

<?php if ($dados): ?>

<table>
<tr>
    <th>Nome</th>
    <th>Status</th>
    <th>Motivo</th>
    <th>Data</th>
    <th>Ata</th>
</tr>

<?php foreach ($dados as $d): ?>
<tr>
    <td><?= htmlspecialchars($d['nome_do_membro']) ?></td>
    <td><?= $d['status'] ?></td>
    <td><?= $d['motivo'] ?></td>
    <td><?= date('d/m/Y', strtotime($d['data_evento'])) ?></td>
    <td>
        <?php if ($d['numero_ata']): ?>
            Livro <?= $d['numero_livro_ata'] ?> - Nº <?= $d['numero_ata'] ?>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>

</table>

<?php endif; ?>

</body>
</html>