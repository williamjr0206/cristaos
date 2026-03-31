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

    $id             = $_POST['id'] ?? null;
    $dataaula_mysql = $_POST['data_aula'] ?? '';
    $dataaula       = date('Y-m-d H:i:s', strtotime($dataaula_mysql));
    $nomeaula       = $_POST['nome_da_aula'] ?? '';
    $evento         = $_POST['id_evento'] ?? '';
    $curso          = $_POST['id_curso'] ?? '';

    if ($id) {
        $sql = "UPDATE aulas 
                SET data_aula = :da,
                    nome_da_aula = :nome,
                    id_evento = :evento,
                    id_curso = :curso
                WHERE id_aula = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
    } else {
        $sql = "INSERT INTO aulas (data_aula, nome_da_aula, id_evento, id_curso)
                VALUES (:da, :nome, :evento, :curso)";

        $stmt = $pdo->prepare($sql);
    }

    $stmt->bindParam(':da', $dataaula);
    $stmt->bindParam(':nome', $nomeaula);
    $stmt->bindParam(':evento', $evento);
    $stmt->bindParam(':curso', $curso);

    $stmt->execute();

    header("Location: " . BASE_URL . "cadastros/aulas.php");
    exit;
}

/* =====================
   EXCLUIR
===================== */
if (isset($_GET['delete'])) {

    $id = $_GET['delete'];

    $sql = "DELETE FROM aulas WHERE id_aula = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    header("Location: " . BASE_URL . "cadastros/aulas.php");
    exit;
}

/* =====================
   EDITAR
===================== */
$editar = null;

if (isset($_GET['edit'])) {

    $id = $_GET['edit'];

    $stmt = $pdo->prepare("SELECT * FROM aulas WHERE id_aula = ?");
    $stmt->execute([$id]);
    $editar = $stmt->fetch(PDO::FETCH_ASSOC);
}

/* =====================
   SELECTS (EVENTOS E CURSOS)
===================== */
$stmt2 = $pdo->query("SELECT id_evento, descricao FROM eventos");
$eventos = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$stmt3 = $pdo->query("SELECT id_curso, nome_do_curso FROM cursos");
$cursos = $stmt3->fetchAll(PDO::FETCH_ASSOC);

/* =====================
   LISTAR
===================== */
$stmt = $pdo->query("
    SELECT 
        aulas.id_aula,
        aulas.data_aula,
        aulas.nome_da_aula,
        eventos.descricao AS evento,
        cursos.nome_do_curso AS curso
    FROM aulas
    INNER JOIN eventos ON aulas.id_evento = eventos.id_evento
    INNER JOIN cursos ON aulas.id_curso = cursos.id_curso
    ORDER BY data_aula
");

$aulas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"charset="UTF-8">
    <title>Aulas</title>

    <style>
        body { font-family: Arial; margin: 20px; }
        form { margin-bottom: 30px; }
        input, select { margin: 6px 0; padding: 6px; width: 360px; display: block; }
        table { border-collapse: collapse; width: 100%; }
        a { margin-right: 10px; }

    </style>

</head>
<body>

<h2><?= $editar ? 'Editar Aula' : 'Nova Aula' ?></h2>

<form method="post">

    <input type="hidden" name="id" value="<?= $editar['id_aula'] ?? '' ?>">

    <label>Data e horário da Aula</label>
    <input type="datetime-local" name="data_aula" required
        value="<?= isset($editar['data_aula']) ? date('Y-m-d\TH:i', strtotime($editar['data_aula'])) : '' ?>">

    <label>Assunto da Aula</label>
    <input name="nome_da_aula" required
        value="<?= htmlspecialchars($editar['nome_da_aula'] ?? '') ?>">

    <label>Evento</label>
    <select name="id_evento" required>
        <option value="">Selecione</option>
        <?php foreach ($eventos as $evento): ?>
            <option value="<?= $evento['id_evento'] ?>"
                <?= (isset($editar['id_evento']) && $editar['id_evento'] == $evento['id_evento']) ? 'selected' : '' ?>>
                <?= $evento['descricao'] ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Curso</label>
    <select name="id_curso" required>
        <option value="">Selecione</option>
        <?php foreach ($cursos as $c): ?>
            <option value="<?= $c['id_curso'] ?>"
                <?= (isset($editar['id_curso']) && $editar['id_curso'] == $c['id_curso']) ? 'selected' : '' ?>>
                <?= $c['nome_do_curso'] ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit"><?= $editar ? 'Atualizar' : 'Salvar' ?></button>

    <?php if ($editar): ?>
        <a href="aulas.php">Cancelar</a>
    <?php endif; ?>
</form>

<h2>Lista de Aulas</h2>

<table border="1">
    <tr>
        <th>Data</th>
        <th>Aula</th>
        <th>Evento</th>
        <th>Curso</th>
        <th>Ações</th>
    </tr>

    <?php foreach ($aulas as $a): ?>
        <tr>
            <td><?= htmlspecialchars($a['data_aula']) ?></td>
            <td><?= htmlspecialchars($a['nome_da_aula']) ?></td>
            <td><?= htmlspecialchars($a['evento']) ?></td>
            <td><?= htmlspecialchars($a['curso']) ?></td>
            <td>
                <a href="<?= BASE_URL ?>cadastros/aulas.php?edit=<?= $a['id_aula'] ?>">Editar</a>
                <a href="<?= BASE_URL ?>cadastros/aulas.php?delete=<?= $a['id_aula'] ?>"
                onclick="return confirm('Deseja excluir esta Aula?')">
                Excluir
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>