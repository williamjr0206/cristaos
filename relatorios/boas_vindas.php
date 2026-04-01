<?php
ob_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
verificaAcesso();
verificaPerfil(['ADMIN', 'LIDER', 'OPERADOR']);
require __DIR__ . '/../includes/menu.php';

/*
=========================================================
CONFIGURAÇÕES DA CARTA
=========================================================
*/
$nomeIgreja = 'Sua Igreja';
$cidadeIgreja = 'Sua Cidade';
$estadoIgreja = 'UF';

$id = $_GET['id'] ?? null;
$visitante = null;
$mensagemWhatsapp = '';
$linkWhatsapp = '';
$linkEmail = '';

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM visitantes WHERE id_visitante = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $visitante = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($visitante) {
        $nomeVisitante = $visitante['nome'] ?? 'Visitante';
        $telefone = preg_replace('/\D/', '', $visitante['telefone'] ?? '');
        $email = trim($visitante['email'] ?? '');

        $mensagemWhatsapp = "Olá, " . $nomeVisitante . "! Seja muito bem-vindo(a). "
            . "Foi uma alegria receber sua visita. "
            . "Nossa oração é que Deus abençoe sua vida e sua família. "
            . "Estamos à disposição e teremos alegria em recebê-lo(a) novamente.";

        if (!empty($telefone)) {
            // Para o Brasil, WhatsApp exige DDI 55. Evita duplicar se já vier com 55.
            if (substr($telefone, 0, 2) !== '55') {
                $telefone = '55' . $telefone;
            }
            $linkWhatsapp = "https://wa.me/" . $telefone . "?text=" . urlencode($mensagemWhatsapp);
        }

        if (!empty($email)) {
            $assunto = "Seja bem-vindo(a) - " . $nomeIgreja;
            $corpo = "Olá, " . $nomeVisitante . ",\n\n"
                . "Foi uma alegria receber sua visita.\n"
                . "Seja muito bem-vindo(a).\n\n"
                . "Estamos à disposição e teremos alegria em recebê-lo(a) novamente.\n\n"
                . $nomeIgreja . "\n"
                . $cidadeIgreja . " - " . $estadoIgreja;
            $linkEmail = "mailto:" . rawurlencode($email)
                . "?subject=" . rawurlencode($assunto)
                . "&body=" . rawurlencode($corpo);
        }
    }
}

/*
=========================================================
LISTA DE VISITANTES
=========================================================
*/
$stmtLista = $pdo->query("SELECT id_visitante, nome, telefone, email FROM visitantes ORDER BY nome");
$visitantes = $stmtLista->fetchAll(PDO::FETCH_ASSOC);

function valorCampo($valor, $padrao = 'Não informado') {
    return !empty(trim((string)$valor)) ? htmlspecialchars($valor) : $padrao;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boas-vindas aos Visitantes</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; color: #222; }
        .bloco { background: #f8f9fb; border: 1px solid #dfe5ec; border-radius: 12px; padding: 16px; margin-bottom: 20px; }
        label { display: block; margin: 10px 0 4px; font-weight: bold; }
        select, input, textarea, a.botao, button {
            width: 100%; max-width: 420px; padding: 10px; border-radius: 8px; border: 1px solid #ccd3db; box-sizing: border-box;
        }
        a.botao, button { display: inline-block; text-decoration: none; background: #2c3e50; color: white; border: none; cursor: pointer; text-align: center; margin-top: 10px; }
        a.botao.sec { background: #7f8c8d; }
        a.botao.ok { background: #1abc9c; }
        .acoes a { max-width: 260px; margin-right: 10px; }
        .carta {
            background: white; border: 1px solid #d8dde3; border-radius: 14px; padding: 28px; max-width: 850px;
            box-shadow: 0 2px 10px rgba(0,0,0,.05);
        }
        .carta h2 { text-align: center; color: #2c3e50; margin-top: 0; }
        .meta { color: #555; margin-bottom: 18px; }
        .assinatura { margin-top: 40px; }
        .linha { margin-top: 40px; width: 280px; border-top: 1px solid #333; }
        table { border-collapse: collapse; width: 100%; max-width: 850px; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #eee; }
        @media print {
            .topo-sistema, .menu-sistema, .bloco, .acoes, table h2 { display: none !important; }
            body { margin: 0; }
            .carta { border: none; box-shadow: none; max-width: 100%; }
        }
    </style>
</head>
<body>

<div class="bloco">
    <h2>Boas-vindas aos Visitantes</h2>
    <form method="get">
        <label>Selecione o visitante</label>
        <select name="id" required>
            <option value="">Selecione</option>
            <?php foreach ($visitantes as $v): ?>
                <option value="<?= $v['id_visitante'] ?>" <?= ($id == $v['id_visitante']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($v['nome']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Carregar Carta</button>
    </form>
</div>

<?php if ($visitante): ?>
    <div class="bloco">
        <strong>Visitante:</strong> <?= valorCampo($visitante['nome']) ?><br>
        <strong>Telefone:</strong> <?= valorCampo($visitante['telefone']) ?><br>
        <strong>E-mail:</strong> <?= valorCampo($visitante['email']) ?>
        <div class="acoes">
            <?php if ($linkWhatsapp): ?>
                <a class="botao ok" href="<?= htmlspecialchars($linkWhatsapp) ?>" target="_blank">Enviar por WhatsApp</a>
            <?php endif; ?>

            <?php if ($linkEmail): ?>
                <a class="botao" href="<?= htmlspecialchars($linkEmail) ?>">Enviar por E-mail</a>
            <?php endif; ?>

            <a class="botao sec" href="#" onclick="window.print(); return false;">Imprimir / Salvar em PDF</a>
        </div>
    </div>

    <div class="carta">
        <h2>Carta de Boas-Vindas</h2>
        <p class="meta"><?= htmlspecialchars($cidadeIgreja) ?> - <?= htmlspecialchars($estadoIgreja) ?></p>

        <p>Querido(a) <strong><?= htmlspecialchars($visitante['nome']) ?></strong>,</p>

        <p>
            É uma alegria receber sua visita. Em nome da igreja, queremos lhe dar as boas-vindas
            e agradecer por ter estado conosco.
        </p>

        <p>
            Nossa oração é que você se sinta acolhido(a), cuidado(a) e edificado(a) entre nós.
            Desejamos que sua caminhada com Deus seja fortalecida a cada dia, e que este contato
            seja o começo de uma aproximação sincera, fraterna e abençoada.
        </p>

        <p>
            Estamos à disposição para orar com você, ouvir suas necessidades e caminhar ao seu lado
            no que for possível. Caso deseje, teremos alegria em recebê-lo(a) novamente em nossos
            cultos, aulas, reuniões e demais atividades.
        </p>

        <p>Seja muito bem-vindo(a).</p>

        <div class="assinatura">
            <p>Com carinho e em Cristo,</p>
            <p><strong><?= htmlspecialchars($nomeIgreja) ?></strong></p>
            <p><?= htmlspecialchars($cidadeIgreja) ?> - <?= htmlspecialchars($estadoIgreja) ?></p>
            <div class="linha"></div>
            <p>Responsável pelo contato</p>
        </div>
    </div>
<?php endif; ?>

</body>
</html>
