<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../config/database.php';
require __DIR__ . '/../config/auth.php';
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

    $data_presenca = str_replace('T', ' ', $_POST['data_presenca']) . ':00';

    $id_professor = $_POST['id_professor'];
    $id_aula = $_POST['id_aula'];
    $presentes = $_POST['presentes'] ?? [];

    foreach ($presentes as $id_membro) {

        // EVITA DUPLICIDADE
        $check = $pdo->prepare("SELECT COUNT(*) FROM presencas 
            WHERE id_membro = :id_membro 
            AND id_aula = :id_aula 
            AND DATE(data_presenca) = DATE(:data)");

        $check->execute([
            ':id_membro' => $id_membro,
            ':id_aula' => $id_aula,
            ':data' => $data_presenca
        ]);

        if ($check->fetchColumn() == 0) {

            $sql = "INSERT INTO presencas 
                    (id_membro, id_aula, id_professor, data_presenca)
                    VALUES (:id_membro, :id_aula, :id_professor, :data)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id_membro' => $id_membro,
                ':id_aula' => $id_aula,
                ':id_professor' => $id_professor,
                ':data' => $data_presenca
            ]);
        }
    }

    header("Location: presencas_lote.php?data=$data_presenca&professor=$id_professor&aula=$id_aula");
    exit;
}

// =====================
// LISTAS
// =====================
$membros = $pdo->query("SELECT id_membro, nome_do_membro FROM membros ORDER BY nome_do_membro")->fetchAll();
$aulas = $pdo->query("SELECT id_aula, nome_da_aula FROM aulas ORDER BY nome_da_aula")->fetchAll();
$professores = $pdo->query("SELECT id_professor, nome_do_professor FROM professores ORDER BY nome_do_professor")->fetchAll();

// =====================
// BUSCAR PRESENÇAS JÁ MARCADAS
// =====================
$presentes_hoje = [];

if ($aula_fixa && $data_fixa) {

    $sql = "SELECT id_membro FROM presencas 
            WHERE id_aula = :id_aula 
            AND DATE(data_presenca) = DATE(:data)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_aula' => $aula_fixa,
        ':data' => $data_fixa
    ]);

    $presentes_hoje = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>

<h2>Chamada por Lista (Checkbox)</h2>

<form method="POST">

<label>Data e Hora:</label><br>
<input type="datetime-local" name="data_presenca"
value="<?= date('Y-m-d\TH:i', strtotime($data_fixa)) ?>"
required><br><br>

<label>Professor:</label><br>
<select name="id_professor" required>
    <option value="">Selecione</option>
    <?php foreach ($professores as $p): ?>
        <option value="<?= $p['id_professor'] ?>"
        <?= ($professor_fixo == $p['id_professor']) ? 'selected' : '' ?>>
            <?= $p['nome_do_professor'] ?>
        </option>
    <?php endforeach; ?>
</select><br><br>

<label>Aula:</label><br>
<select name="id_aula" required onchange="this.form.submit()">
    <option value="">Selecione</option>
    <?php foreach ($aulas as $a): ?>
        <option value="<?= $a['id_aula'] ?>"
        <?= ($aula_fixa == $a['id_aula']) ? 'selected' : '' ?>>
            <?= $a['nome_da_aula'] ?>
        </option>
    <?php endforeach; ?>
</select><br><br>

<hr>

<h3>Lista de Membros</h3>

<button type="button" onclick="marcarTodos()">✔ Marcar todos</button>
<button type="button" onclick="desmarcarTodos()">❌ Desmarcar todos</button>

<br><br>

<table border="1" cellpadding="5">
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
    <td><?= $m['nome_do_membro'] ?></td>
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