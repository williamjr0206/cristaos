<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../config/database.php';
require __DIR__ . '/../config/auth.php';
verificaAcesso();

// Não incluir menu aqui, para a imagem sair limpa
// require __DIR__ . '/../includes/menu.php';

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
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Aniversariantes - JPEG</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f6f8;
    margin: 0;
    padding: 30px;
}

.acoes {
    margin-bottom: 20px;
    text-align: center;
}

button {
    background: #2c3e50;
    color: #fff;
    border: none;
    padding: 10px 18px;
    cursor: pointer;
    border-radius: 6px;
    font-size: 14px;
}

button:hover {
    background: #1f2d3a;
}

#relatorio {
    max-width: 900px;
    margin: 0 auto;
    background: #fff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

h2 {
    text-align: center;
    margin-top: 0;
    margin-bottom: 10px;
}

.periodo {
    text-align: center;
    margin-bottom: 20px;
    color: #555;
    font-size: 14px;
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

th {
    background: #eaeaea;
}

.sem-dados {
    text-align: center;
    padding: 20px;
    color: #666;
}
</style>
</head>
<body>

<div class="acoes">
    <button onclick="gerarJPEG()">Baixar JPEG</button>
</div>

<div id="relatorio">
    <h2>🎂 Lista de Aniversariantes</h2>

    <div class="periodo">
        Período:
        <strong><?= $data_inicio ? date('d/m/Y', strtotime($data_inicio)) : '--/--/----' ?></strong>
        até
        <strong><?= $data_fim ? date('d/m/Y', strtotime($data_fim)) : '--/--/----' ?></strong>
    </div>

    <?php if ($lista): ?>
        <table>
            <tr>
                <th>Nome</th>
                <th>Data</th>
            </tr>

            <?php foreach ($lista as $l): ?>
            <tr>
                <td><?= htmlspecialchars($l['nome_do_membro']) ?></td>
                <td><?= date('d/m', strtotime($l['data_nascimento'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <div class="sem-dados">Nenhum aniversariante encontrado no período informado.</div>
    <?php endif; ?>
</div>

<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
<script>
function gerarJPEG() {
    const relatorio = document.getElementById('relatorio');

    html2canvas(relatorio, {
        scale: 2,
        backgroundColor: '#ffffff'
    }).then(canvas => {
        const link = document.createElement('a');
        link.download = 'aniversariantes.jpg';
        link.href = canvas.toDataURL('image/jpeg', 0.95);
        link.click();
    });
}
</script>

</body>
</html>