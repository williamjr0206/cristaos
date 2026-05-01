<?php
ob_start();

require __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
verificaAcesso();
require __DIR__ . '/../includes/menu.php';

/* =====================
   SALVAR / EDITAR
===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    date_default_timezone_set('America/Sao_Paulo');

    $id               = $_POST['id'] ?? null;
    $id_membro        = $_POST['id_membro'] ?? '';
    $status           = $_POST['status'] ?? '';
    $motivo           = $_POST['motivo'] ?? '';
    $observacao       = $_POST['observacao'] ?? '';
    $data_evento      = $_POST['data_evento'] ?? '';
    $numero_livro_ata = $_POST['numero_livro_ata'] ?? '';
    $numero_ata       = $_POST['numero_ata'] ?? '';
    $cadastrado_por   = $_SESSION['id_usuario'] ?? null;

    try {

        $pdo->beginTransaction();

        if ($id) {
            // UPDATE HISTÓRICO
            $sql = "UPDATE historico_membro 
                    SET id_membro = :id_membro,
                        status = :status,
                        motivo = :motivo,
                        observacao = :observacao,
                        data_evento = :data_evento,
                        numero_livro_ata = :numero_livro_ata,
                        numero_ata = :numero_ata,
                        cadastrado_por = :cadastrado_por
                    WHERE id = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $id);

        } else {
            // INSERT HISTÓRICO
            $sql = "INSERT INTO historico_membro (
                        id_membro, status, motivo, observacao, data_evento,
                        numero_livro_ata, numero_ata, cadastrado_por
                    )
                    VALUES (
                        :id_membro, :status, :motivo, :observacao, :data_evento,
                        :numero_livro_ata, :numero_ata, :cadastrado_por
                    )";

            $stmt = $pdo->prepare($sql);
        }

        // PARAMS
        $stmt->bindParam(':id_membro', $id_membro);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':motivo', $motivo);
        $stmt->bindParam(':observacao', $observacao);
        $stmt->bindParam(':data_evento', $data_evento);
        $stmt->bindParam(':numero_livro_ata', $numero_livro_ata);
        $stmt->bindParam(':numero_ata', $numero_ata);
        $stmt->bindParam(':cadastrado_por', $cadastrado_por);

        $stmt->execute();

        // 🔥 ATUALIZA STATUS DO MEMBRO (UMA ÚNICA VEZ)
        $stmt2 = $pdo->prepare("
            UPDATE membros 
            SET status_atual = :status 
            WHERE id_membro = :id_membro
        ");

        $stmt2->execute([
            ':status' => $status,
            ':id_membro' => $id_membro
        ]);

        $pdo->commit();

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Erro: " . $e->getMessage());
    }

    header("Location: " . BASE_URL . "cadastros/historico_membro.php");
    exit;
}
/* =====================
   EXCLUIR
===================== */
if (isset($_GET['delete'])) {

    $id = $_GET['delete'];

    $stmt = $pdo->prepare("DELETE FROM historico_membro WHERE id = :id");
    $stmt->execute([':id' => $id]);

    header("Location: " . BASE_URL . "cadastros/historico_membro.php");
    exit;
}

/* =====================
   EDITAR
===================== */
$editar = null;

if (isset($_GET['edit'])) {

    $id = $_GET['edit'];

    $stmt = $pdo->prepare("SELECT * FROM historico_membro WHERE id = ?");
    $stmt->execute([$id]);
    $editar = $stmt->fetch(PDO::FETCH_ASSOC);
}

/* =====================
   SELECTS (Membros)
===================== */
$stmt = $pdo->query("SELECT id_membro, nome_do_membro FROM membros ORDER BY nome_do_membro");
$membros = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =====================
   LISTAR
===================== */
$stmt = $pdo->query("
    SELECT 
        historico_membro.id,
        historico_membro.id_membro,
        membros.nome_do_membro,
        historico_membro.status,
        historico_membro.motivo,
        historico_membro.data_evento,
        historico_membro.numero_livro_ata,
        historico_membro.numero_ata
        
    FROM historico_membro
    INNER JOIN membros ON historico_membro.id_membro = membros.id_membro
    ORDER BY membros.nome_do_membro
");

$historico_membros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0" charset="UTF-8">
<title>Histórico de Membros</title>
<style>
    body { font-family: Arial; margin: 20px; }
    form { margin-bottom: 30px; }
    input, select { margin: 6px 0; padding: 6px; width: 360px; display: block; }
    table { border-collapse: collapse; width: 100%; }
    th, td { padding: 8px; }
    a { margin-right: 10px; }
</style>
</head>

<body>

<h2><?= $editar ? 'Editar Histórico' : 'Novo Histórico' ?></h2>

<form method="post">

<input type="hidden" name="id" value="<?= htmlspecialchars($editar['id'] ?? '') ?>">

<label>Nome do Membro</label>
<select name="id_membro" required>
    <option value="">Selecione</option>
    <?php foreach ($membros as $membro): ?>
        <option value="<?= $membro['id_membro'] ?>"
            <?= (isset($editar['id_membro']) && $editar['id_membro'] == $membro['id_membro']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($membro['nome_do_membro']) ?>
        </option>
    <?php endforeach; ?>
</select>

<label>Status do Membro</label>
<select name="status" required>
    <?php foreach (['Ativo','Inativo','Transferido','Desligado','Excluido','Falecido'] as $s): ?>
        <option value="<?= $s ?>" <?= (isset($editar['status']) && $editar['status'] == $s) ? 'selected' : '' ?>>
            <?= $s ?>
        </option>
    <?php endforeach; ?>
</select>

<label>Motivo</label>
<select name="motivo" required>
    <?php foreach (['Abandono','Transferência','Disciplina','Solicitação','Falecimento','Não Localizado'] as $m): ?>
        <option value="<?= $m ?>" <?= (isset($editar['motivo']) && $editar['motivo'] == $m) ? 'selected' : '' ?>>
            <?= $m ?>
        </option>
    <?php endforeach; ?>
</select>

<label>Observações</label>
<input type="text" name="observacao" value="<?= htmlspecialchars($editar['observacao'] ?? '') ?>">

<label>Data do Histórico</label>
<input type="date" name="data_evento" required value="<?= htmlspecialchars($editar['data_evento'] ?? '') ?>">

<label>Número do Livro da Ata</label>
<input type="number" name="numero_livro_ata" required value="<?= htmlspecialchars($editar['numero_livro_ata'] ?? '') ?>">

<label>Número da Ata</label>
<input name="numero_ata" required value="<?= htmlspecialchars($editar['numero_ata'] ?? '') ?>">

<button type="submit"><?= $editar ? 'Atualizar' : 'Salvar' ?></button>

<?php if ($editar): ?>
    <a href="historico_membro.php">Cancelar</a>
<?php endif; ?>

</form>

<h2>Lista de Históricos:</h2>

<table border="1">
<tr>
    <th>Membro</th>
    <th>Status</th>
    <th>Motivo</th>
    <th>Data</th>
    <th>Livro</th>
    <th>Ata</th>
    <th>Ações</th>
</tr>

<?php foreach ($historico_membros as $h): ?>
<tr>
    <td><?= htmlspecialchars($h['nome_do_membro']) ?></td>
    <td><?= htmlspecialchars($h['status']) ?></td>
    <td><?= htmlspecialchars($h['motivo']) ?></td>
    <td><?= htmlspecialchars($h['data_evento']) ?></td>
    <td><?= htmlspecialchars($h['numero_livro_ata']) ?></td>
    <td><?= htmlspecialchars($h['numero_ata']) ?></td>
    <td>
        <a href="historico_membro.php?edit=<?= $h['id'] ?>">Editar</a>
        <a href="historico_visual_membro.php?id_membro=<?= $h['id_membro'] ?>">
        Ver Histórico
        </a>

        <a href="historico_membro.php?delete=<?= $h['id'] ?>"
           onclick="return confirm('Deseja excluir este histórico?')">
           Excluir
        </a>
    </td>
</tr>
<?php endforeach; ?>

</table>

</body>
</html>