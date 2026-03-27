<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/menu.php';
require __DIR__ . '/../config/auth.php';
verificaAcesso();

$data_inicio = $_GET['inicio'] ?? '';
$data_fim    = $_GET['fim'] ?? '';

$lista = [];

if ($data_inicio && $data_fim) {

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
}
?>

<h2>🎂 Aniversariantes</h2>

<form method="GET">
    <label>Data Inicial:</label>
    <input type="date" name="inicio" value="<?= $data_inicio ?>" required>

    <label>Data Final:</label>
    <input type="date" name="fim" value="<?= $data_fim ?>" required>

    <button type="submit">Filtrar</button>
</form>

<?php if ($lista): ?>

<br>
<a href="aniversariantes_pdf.php?inicio=<?= $data_inicio ?>&fim=<?= $data_fim ?>" target="_blank">
    📄 Gerar PDF
</a>

<table border="1" cellpadding="5">
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

<?php endif; ?>