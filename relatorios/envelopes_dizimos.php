<?php
require __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
verificaAcesso();

$stmt = $pdo->query("
    SELECT
        id_membro,
        nome_do_membro,
        codigo_barras
    FROM membros
    WHERE status_atual = 'Ativo'
    ORDER BY nome_do_membro
");

$membros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Envelopes de Dízimos</title>

<style>
body {
    font-family: Arial;
    margin: 20px;
}

.envelope {
    width: 360px;
    height: 220px;
    border: 2px solid #000;
    padding: 15px;
    margin-bottom: 25px;
    page-break-inside: avoid;
}
.titulo {
    text-align: center;
    font-size: 22px;
    font-weight: bold;
    margin-bottom: 20px;
}

.nome {
    font-size: 20px;
    font-weight: bold;
    margin-top: 10px;
    margin-bottom: 20px;
}

.qrcode {
    text-align: center;
}

.qrcode img {
    width: 130px;
    height: 130px;
}
.codigo {
    text-align: center;
    margin-top: 10px;
    font-size: 16px;
    font-weight: bold;
}

@media print {
    .nao-imprimir {
        display: none;
    }
}
</style>
</head>
<body>

<button class="nao-imprimir" onclick="window.print()">
Imprimir Envelopes
</button>
<?php foreach ($membros as $m): ?>

<div class="envelope">

    <div class="titulo">
        IPI de Muzambinho
    </div>

    <div class="nome">
        <?= htmlspecialchars($m['nome_do_membro']) ?>
    </div>

    <div class="qrcode">
        <img src="../qrcodes/<?= $m['codigo_barras'] ?>.png">
    </div>

    <div class="codigo">
        <?= htmlspecialchars($m['codigo_barras']) ?>
    </div>

</div>

<?php endforeach; ?>

</body>
</html>