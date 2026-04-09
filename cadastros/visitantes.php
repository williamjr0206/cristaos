<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ob_start();

require __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
verificaAcesso();

require __DIR__ . '/../includes/menu.php';

/* =====================
   DATA FIXA PARA O FORM
===================== */
$data_fixa = isset($editar['data_cadastro'])
    ? $editar['data_cadastro']
    : date('Y-m-d H:i:s');

/* =====================
   SALVAR / EDITAR
===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id            = $_POST['id'] ?? null;
    $data_fixa     = !empty($_POST['data_cadastro'])
        ? str_replace('T', ' ', $_POST['data_cadastro']) . ':00'
        : date('Y-m-d H:i:s');

    $nome          = $_POST['nome'] ?? '';
    $sexo          = $_POST['sexo'] ?? '';
    $tipomembro    = $_POST['id_tipomembro'] ?? '';
    $telefone      = $_POST['telefone'] ?? '';
    $email         = $_POST['email'] ?? '';
    $cidade        = $_POST['cidade'] ?? '';
    $endereco      = $_POST['endereco'] ?? '';
    $oracao        = $_POST['oracao'] ?? '';
    $cadastrante   = $_POST['cadastrante'] ?? '';

    if ($id) {
        $sql = "UPDATE visitantes SET 
                    nome = :nome,
                    data_cadastro = :data_cadastro,
                    sexo = :sexo,
                    id_membro = :tipomembro,
                    telefone = :telefone,
                    email = :email,
                    cidade = :cidade,
                    endereco = :endereco,
                    oracao = :oracao,
                    cadastrante = :cadastrante
                WHERE id_visitante = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
    } else {
        $sql = "INSERT INTO visitantes 
                    (nome, sexo, id_membro, telefone, email, cidade, endereco, oracao, data_cadastro, cadastrante)
                VALUES 
                    (:nome, :sexo, :tipomembro, :telefone, :email, :cidade, :endereco, :oracao, :data_cadastro, :cadastrante)";

        $stmt = $pdo->prepare($sql);
    }

    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':sexo', $sexo);
    $stmt->bindParam(':tipomembro', $tipomembro);
    $stmt->bindParam(':telefone', $telefone);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':cidade', $cidade);
    $stmt->bindParam(':endereco', $endereco);
    $stmt->bindParam(':oracao', $oracao);
    $stmt->bindParam(':data_cadastro', $data_fixa);
    $stmt->bindParam(':cadastrante', $cadastrante);

    $stmt->execute();

    header("Location: " . BASE_URL . "cadastros/visitantes.php");
    exit;
}

/* =====================
   EXCLUIR
===================== */
if (isset($_GET['delete'])) {

    $id = $_GET['delete'];

    $sql = "DELETE FROM visitantes WHERE id_visitante = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    header("Location: " . BASE_URL . "cadastros/visitantes.php");
    exit;
}

/* =====================
   EDITAR
===================== */
$editar = null;

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];

    $stmt = $pdo->prepare("SELECT * FROM visitantes WHERE id_visitante = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $editar = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($editar && !empty($editar['data_cadastro'])) {
        $data_fixa = $editar['data_cadastro'];
    }
}

/* =====================
   SELECTS
===================== */
$stmtTipo = $pdo->query("SELECT id_tipo, descricao FROM tipo ORDER BY descricao");
$tipos = $stmtTipo->fetchAll(PDO::FETCH_ASSOC);

$stmtMembros = $pdo->query("SELECT id_membro, nome_do_membro FROM membros ORDER BY nome_do_membro");
$membros = $stmtMembros->fetchAll(PDO::FETCH_ASSOC);

/* =====================
   LISTAR
===================== */
$stmt = $pdo->query("
    SELECT 
        visitantes.id_visitante,
        visitantes.nome,
        visitantes.sexo,
        tipo.descricao,
        visitantes.telefone,
        visitantes.email,
        visitantes.cidade,
        visitantes.endereco,
        visitantes.oracao,
        visitantes.cadastrante,
        visitantes.data_cadastro
    FROM visitantes 
    INNER JOIN tipo ON visitantes.id_membro = tipo.id_tipo
    ORDER BY visitantes.nome
");

$visitantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0" charset="UTF-8">
<title>Visitantes</title>

    <style>
        body { font-family: Arial; margin: 20px; }
        form { margin-bottom: 30px; }
        input, select { margin: 6px 0; padding: 6px; width: 360px; display: block; }
        table { border-collapse: collapse; width: 100%; }
        a { margin-right: 10px; }

    </style>

</head>
<body>

<h2><?= $editar ? 'Editar Visitante' : 'Novo Visitante' ?></h2>

<form method="post">

<input type="hidden" name="id" value="<?= $editar['id_visitante'] ?? '' ?>">

<label>Data Cadastro</label>
<input type="datetime-local" name="data_cadastro"
    value="<?= date('Y-m-d\TH:i', strtotime($data_fixa)) ?>"
    required>

<label>Nome</label>
<input name="nome" required value="<?= htmlspecialchars($editar['nome'] ?? '') ?>">

<label>Sexo</label>
<select name="sexo" required>
    <?php foreach (['Masculino','Feminino'] as $s): ?>
        <option value="<?= $s ?>" <?= (isset($editar['sexo']) && $editar['sexo'] == $s) ? 'selected' : '' ?>>
            <?= $s ?>
        </option>
    <?php endforeach; ?>
</select>

<label>Tipo de Membro</label>
<select name="id_tipomembro" required>
    <option value="">Selecione</option>
    <?php foreach ($tipos as $t): ?>
        <option value="<?= $t['id_tipo'] ?>"
            <?= (isset($editar['id_membro']) && $editar['id_membro'] == $t['id_tipo']) ? 'selected' : '' ?>>
            <?= $t['descricao'] ?>
        </option>
    <?php endforeach; ?>
</select>

<label>Telefone</label>
<input name="telefone" value="<?= htmlspecialchars($editar['telefone'] ?? '') ?>">

<label>Email</label>
<input name="email" value="<?= htmlspecialchars($editar['email'] ?? '') ?>">

<label>Cidade</label>
<input name="cidade" value="<?= htmlspecialchars($editar['cidade'] ?? '') ?>">

<label>Endereço</label>
<input name="endereco" value="<?= htmlspecialchars($editar['endereco'] ?? '') ?>">

<label>Pedido de Oração</label>
<input name="oracao" value="<?= htmlspecialchars($editar['oracao'] ?? '') ?>">

<label>Cadastrado por</label>
<select name="cadastrante">
    <option value="">Selecione</option>
    <?php foreach ($membros as $m): ?>
        <option value="<?= $m['id_membro'] ?>"
            <?= (isset($editar['cadastrante']) && $editar['cadastrante'] == $m['id_membro']) ? 'selected' : '' ?>>
            <?= $m['nome_do_membro'] ?>
        </option>
    <?php endforeach; ?>
</select>

<button type="submit"><?= $editar ? 'Atualizar' : 'Salvar' ?></button>

<?php if ($editar): ?>
    <a href="visitantes.php">Cancelar</a>
<?php endif; ?>

</form>

<h2>Lista de Visitantes</h2>

<table border="1">
<tr>
    <th>Nome</th>
    <th>Data Cadastro</th>
    <th>Sexo</th>
    <th>Tipo</th>
    <th>Telefone</th>
    <th>Email</th>
    <th>Cidade</th>
    <th>Endereço</th>
    <th>Oração</th>
    <th>Ações</th>
</tr>

<?php foreach ($visitantes as $v): ?>
<tr>
    <td><?= htmlspecialchars($v['nome']) ?></td>
    <td><?= htmlspecialchars($v['data_cadastro']) ?></td>
    <td><?= htmlspecialchars($v['sexo']) ?></td>
    <td><?= htmlspecialchars($v['descricao']) ?></td>
    <td><?= htmlspecialchars($v['telefone']) ?></td>
    <td><?= htmlspecialchars($v['email']) ?></td>
    <td><?= htmlspecialchars($v['cidade']) ?></td>
    <td><?= htmlspecialchars($v['endereco']) ?></td>
    <td><?= htmlspecialchars($v['oracao']) ?></td>
    <td>
        <a href="visitantes.php?edit=<?= $v['id_visitante'] ?>">Editar</a>
        <a href="visitantes.php?delete=<?= $v['id_visitante'] ?>"
           onclick="return confirm('Deseja excluir este visitante?')">
           Excluir
        </a>
    </td>
</tr>
<?php endforeach; ?>

</table>

</body>
</html>