<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../config/database.php';
require __DIR__ . '/../config/auth.php';
verificaAcesso();
require __DIR__ . '/../includes/menu.php';

$data_inicio = $_GET['inicio'] ?? '';
$data_fim    = $_GET['fim'] ?? '';

$lista = [];

if ($data_inicio && $data_fim) {

    $sql = "SELECT id_membro, nome_do_membro, data_nascimento, telefone, email
            FROM membros
            WHERE DATE_FORMAT(data_nascimento, '%m-%d') 
            BETWEEN DATE_FORMAT(:inicio, '%m-%d') 
            AND DATE_FORMAT(:fim, '%m-%d') AND status_atual = 'Ativo'
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
    <input type="date" name="inicio" value="<?= htmlspecialchars($data_inicio) ?>" required>

    <label>Data Final:</label>
    <input type="date" name="fim" value="<?= htmlspecialchars($data_fim) ?>" required>

    <button type="submit">Filtrar</button>
</form>

<?php if ($lista): ?>

<br>

<a href="aniversariantes_pdf.php?inicio=<?= urlencode($data_inicio) ?>&fim=<?= urlencode($data_fim) ?>" target="_blank">
    📄 Gerar PDF
</a>

&nbsp; | &nbsp;

<a href="aniversariantes_jpeg.php?inicio=<?= urlencode($data_inicio) ?>&fim=<?= urlencode($data_fim) ?>" target="_blank">
    🖼️ Gerar JPEG
</a>

<br><br>

<table border="1" cellpadding="5">
    <tr>
        <th>Nome</th>
        <th>Data</th>
        <th>Contato</th>
        <th>Ação</th>
    </tr>

    <?php foreach ($lista as $l): ?>
    <tr>
        <td><?= htmlspecialchars($l['nome_do_membro']) ?></td>
        <td><?= date('d/m', strtotime($l['data_nascimento'])) ?></td>
        <?php
            $tel = preg_replace('/\D/', '', (string)($l['telefone'] ?? ''));
            $temWhatsapp = strlen($tel) >= 10 && $tel !== '1234';
            $temEmail = filter_var($l['email'] ?? '', FILTER_VALIDATE_EMAIL);
        ?>
        <td>
            <?= $temWhatsapp ? 'WhatsApp ' : '' ?>
            <?= ($temWhatsapp && $temEmail) ? ' / ' : '' ?>
            <?= $temEmail ? 'E-mail' : ((!$temWhatsapp) ? 'Sem contato cadastrado' : '') ?>
        </td>
        <td>
            <?php if ($temWhatsapp): ?>
                <a target="_blank" href="felicitacoes_pdf.php?id=<?= (int)$l['id_membro'] ?>&acao=whatsapp">💬 Felicitar pelo WhatsApp</a>
            <?php endif; ?>
            <?php if ($temWhatsapp && $temEmail): ?>&nbsp; | &nbsp;<?php endif; ?>
            <?php if ($temEmail): ?>
                <a href="felicitacoes_pdf.php?id=<?= (int)$l['id_membro'] ?>&acao=email">✉️ Felicitar por e-mail</a>
            <?php endif; ?>
            <?php if ($temWhatsapp || $temEmail): ?>
                &nbsp; | &nbsp;<a target="_blank" href="felicitacoes_pdf.php?id=<?= (int)$l['id_membro'] ?>&acao=pdf">📄 Carta</a>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<?php endif; ?>