<?php
require __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
verificaAcesso();
require __DIR__ . '/../includes/menu.php';

$id_membro = $_GET['id_membro'] ?? 0;

$stmt = $pdo->prepare("
    SELECT h.*, m.nome_do_membro
    FROM historico_membro h
    INNER JOIN membros m ON m.id_membro = h.id_membro
    WHERE h.id_membro = :id_membro
    ORDER BY h.data_evento DESC, h.id DESC
");
$stmt->execute([':id_membro' => $id_membro]);
$historico = $stmt->fetchAll(PDO::FETCH_ASSOC);

$nome = $historico[0]['nome_do_membro'] ?? 'Membro';
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Histórico do Membro</title>
<style>
body { font-family: Arial; margin: 20px; }
.timeline { border-left: 3px solid #3498db; padding-left: 20px; }
.item { margin-bottom: 20px; }
.data { font-weight: bold; color: #2c3e50; }
.box {
    background: #f4f6f8;
    padding: 10px;
    border-radius: 6px;
}
</style>
</head>
<body>

<h2>Histórico de: <?= htmlspecialchars($nome) ?></h2>

<div class="timeline">
<?php foreach ($historico as $h): ?>
    <div class="item">
        <div class="data"><?= date('d/m/Y', strtotime($h['data_evento'])) ?></div>
        <div class="box">
            <strong>Status:</strong> <?= $h['status'] ?><br>
            <strong>Motivo:</strong> <?= $h['motivo'] ?><br>

            <?php if (!empty($h['numero_ata'])): ?>
                <strong>Ata:</strong> Livro <?= $h['numero_livro_ata'] ?> - Nº <?= $h['numero_ata'] ?><br>
            <?php endif; ?>

            <?php if (!empty($h['observacao'])): ?>
                <strong>Obs:</strong> <?= htmlspecialchars($h['observacao']) ?>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>
</div>

<a href="historico_membro.php">Voltar</a>

</body>
</html>