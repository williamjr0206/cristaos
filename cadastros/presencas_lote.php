<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ob_start();

require __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
verificaAcesso();

require __DIR__ . '/../includes/menu.php';

// =====================
// PEGAR VALORES FIXOS
// =====================
$data_fixa = $_GET['data'] ?? date('Y-m-d H:i:s');
$professor_fixo = $_GET['professor'] ?? '';
$aula_fixa = $_GET['aula'] ?? '';

// =====================
// SALVAR EM LOTE
// =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data_presenca = str_replace('T', ' ', $_POST['data_aula']) . ':00';
    $id_professor = $_POST['id_professor'] ?? '';
    $id_aula = $_POST['id_aula'] ?? '';
    $presentes = $_POST['presentes'] ?? [];

    foreach ($presentes as $id_membro) {

        // Buscar id_tipo e id_cargo a partir do id_membro
        $stmtDados = $pdo->prepare("
            SELECT id_tipo, id_cargo
            FROM membros
            WHERE id_membro = :id_membro
        ");
        $stmtDados->bindParam(':id_membro', $id_membro);
        $stmtDados->execute();
        $dadosMembro = $stmtDados->fetch(PDO::FETCH_ASSOC);

        $id_tipo = $dadosMembro['id_tipo'] ?? null;
        $id_cargo = $dadosMembro['id_cargo'] ?? null;

        // Evitar duplicidade no mesmo dia/aula/membro
        $check = $pdo->prepare("
            SELECT COUNT(*)
            FROM presencas
            WHERE id_membro = :id_membro
              AND id_aula = :id_aula
              AND DATE(data_aula) = DATE(:data_aula)
        ");

        $check->execute([
            ':id_membro' => $id_membro,
            ':id_aula' => $id_aula,
            ':data_aula' => $data_presenca
        ]);

        if ($check->fetchColumn() == 0) {
            $sql = "INSERT INTO presencas
                    (id_membro, id_aula, id_professor, data_aula, id_tipo, id_cargo)
                    VALUES
                    (:id_membro, :id_aula, :id_professor, :data_aula, :id_tipo, :id_cargo)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id_membro' => $id_membro,
                ':id_aula' => $id_aula,
                ':id_professor' => $id_professor,
                ':data_aula' => $data_presenca,
                ':id_tipo' => $id_tipo,
                ':id_cargo' => $id_cargo
            ]);
        }
    }

    header("Location: presencas_lote.php?data=" . urlencode($data_presenca) . "&professor=" . urlencode($id_professor) . "&aula=" . urlencode($id_aula));
    exit;
}

// =====================
// LISTAS
// =====================
$membros = $pdo->query("SELECT id_membro, nome_do_membro, id_tipo, id_cargo FROM membros ORDER BY nome_do_membro")->fetchAll(PDO::FETCH_ASSOC);
$aulas = $pdo->query("SELECT id_aula, nome_da_aula FROM aulas ORDER BY nome_da_aula")->fetchAll(PDO::FETCH_ASSOC);
$professores = $pdo->query("SELECT id_professor, nome_do_professor FROM professores ORDER BY nome_do_professor")->fetchAll(PDO::FETCH_ASSOC);

// =====================
// BUSCAR PRESENÇAS JÁ MARCADAS
// =====================
$presentes_hoje = [];

if ($aula_fixa && $data_fixa) {
    $sql = "SELECT id_membro
            FROM presencas
            WHERE id_aula = :id_aula
              AND DATE(data_aula) = DATE(:data_aula)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_aula' => $aula_fixa,
        ':data_aula' => $data_fixa,
    ]);

    $presentes_hoje = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presenças em Lote</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        form { margin-bottom: 30px; }
        input, select { margin: 5px 0; padding: 6px; width: 300px; display: block; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 5px; }
        th { background: #eee; }
        button { margin-right: 10px; padding: 8px 12px; cursor: pointer; }
    </style>
</head>
<body>

<h2>Chamada por Lista (Checkbox)</h2>

<form method="POST">

    <label>Data e Hora:</label>
    <input type="datetime-local" name="data_aula"
    value="<?= date('Y-m-d\TH:i', strtotime($data_fixa)) ?>"
    required>

    <label>Professor:</label>
    <select name="id_professor" required>
        <option value="">Selecione</option>
        <?php foreach ($professores as $p): ?>
            <option value="<?= $p['id_professor'] ?>"
            <?= ($professor_fixo == $p['id_professor']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($p['nome_do_professor']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Aula:</label>
    <select name="id_aula" required onchange="this.form.submit()">
        <option value="">Selecione</option>
        <?php foreach ($aulas as $a): ?>
            <option value="<?= $a['id_aula'] ?>"
            <?= ($aula_fixa == $a['id_aula']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($a['nome_da_aula']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <hr>

    <h3>Lista de Membros</h3>

    <button type="button" onclick="marcarTodos()">✔ Marcar todos</button>
    <button type="button" onclick="desmarcarTodos()">❌ Desmarcar todos</button>

    <br><br>

    <table>
        <tr>
            <th>Presença</th>
            <th>Nome</th>
        </tr>

        <?php foreach ($membros as $m): ?>
            <tr>
                <td>
                    <input type="checkbox" name="presentes[]" value="<?= $m['id_membro'] ?>"
                    <?= in_array($m['id_membro'], $presentes_hoje) ? 'checked' : '' ?>>
                </td>
                <td><?= htmlspecialchars($m['nome_do_membro']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <br>
    <button type="submit">💾 Salvar Presenças</button>

</form>

<script>
function marcarTodos() {
    document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = true);
}

function desmarcarTodos() {
    document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
}
</script>

</body>
</html>