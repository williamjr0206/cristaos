<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../config/database.php';
require __DIR__ . '/../config/auth.php';
require __DIR__ . '/../includes/menu.php';

verificaPerfil(['ADMIN','OPERADOR']);

/* =====================
   SALVAR / EDITAR
===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id          = $_POST['id'] ?? null;
    $nome        = $_POST['nome'] ?? '';
    $sexo        = $_POST['sexo'] ?? '';
    $tipomembro  = $_POST['id_tipo'] ?? '';
    $telefone    = $_POST['telefone'] ?? '';
    $email       = $_POST['email'] ?? '';
    $cidade      = $_POST['cidade'] ?? '';
    $oracao      = $_POST['oracao'] ?? '';
    $data        = $data['data'] ?? '';
    $cadastrante = $_POST['cadastrante'] ?? '';    

    if ($id) {
        $stmt = $conn->prepare("
            UPDATE visitantes
            SET nome = ?, sexo = ?, id_tipo = ?, telefone = ?, email = ?, cidade = ?, oracao = ?, data_cadastro = ?,
            cadastrante = ? 
            WHERE id_visitante = ?
        ");
        $stmt->bind_param("ssisssi", $nome, $sexo, $tipomembro, $telefone,$email, $cidade, $oracao, $data, $cadastrante, $id);
        } else {
        $stmt = $conn->prepare("
            INSERT INTO visitantes (nome, sexo, id_tipo, telefone, email, cidade, oracao, 
            data_cadastro, cadastrante)
            VALUES (?, ?, ?, ?, ? , ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssissss", $nome, $sexo, $tipomembro, $telefone, $email, $cidade, $oracao, $data,
        $cadastrante);
    }

    $stmt->execute();
    header("Location: visitantes.php");
    exit;
}

/* =====================
   EXCLUIR
===================== */
if (isset($_GET['delete'])) {

    verificaPerfil(['ADMIN']);

    $stmt = $conn->prepare("DELETE FROM visitantes WHERE id_visitante = ?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();

    header("Location: visitantes.php");
    exit;
}

/* =====================
   EDITAR
===================== */
$editar = null;

if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM visitantes WHERE id_visitante = ?");
    $stmt->bind_param("i", $_GET['edit']);
    $stmt->execute();
    $editar = $stmt->get_result()->fetch_assoc();
}

/* =====================
   LISTAR
===================== */
$igrejas = [];

$result = $conn->query("SELECT * FROM visitantes ORDER BY nome");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $visitantes[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Igrejas</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        form { margin-bottom: 30px; }
        input { margin: 5px 0; padding: 6px; width: 300px; display: block; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 5px; }
        th { background: #eee; }
        a { margin-right: 10px; }
    </style>
</head>
<body>

<h2><?= $editar ? 'Editar Visitante' : 'Nova Visitante' ?></h2>

<form method="post">
    <input type="hidden" name="id" value="<?= $editar['id_visitante'] ?? '' ?>">

    <label>Nome</label>
    <input name="nome" required value="<?= htmlspecialchars($editar['nome'] ?? '') ?>">

    <label>Sexo</label>
    <select name="sexo" required>
        <?php foreach (['Masculino','Feminino'] as $s): ?>
            <option value="<?= $p ?>"
                <?= ($editar && $editar['sexo'] === $p) ? 'selected' : '' ?>>
                <?= $p ?>
            </option>
        <?php endforeach; ?>
    </select>

	<label>Tipo de Membro</label> 
				<select name="id_tipo">
					<option>Selecione</option>
					<?php
						$result_niveis_acessos =$con->prepare("SELECT * FROM tipo order by descricao ");
						$result_niveis_acessos->execute();
						$resultado_niveis_acesso = $result_niveis_acessos->fetchAll(PDO::FETCH_ASSOC);
						foreach($resultado_niveis_acesso as $row_niveis_acessos){?>
							<option value="<?php echo $row_niveis_acessos['id_tipo']; ?>"><?php echo $row_niveis_acessos['descricao']; ?></option> <?php
						}
						
					?>
				</select>

    <label>Telefone</label>
    <input name="telefone" value="<?= htmlspecialchars($editar['telefone'] ?? '') ?>">

    <label>Cidade</label>
    <input  name="cidade" value="<?= htmlspecialchars($editar['cidade'] ?? '') ?>">

    <label>Pedido de Oração</label>
    <input  type='text' name="oracao" value="<?= htmlspecialchars($editar['oracao'] ?? '') ?>">

    <label>Endereço:</label>
    <input  type='date' name="data" value="<?= htmlspecialchars($editar['data'] ?? '') ?>">


	<label>Cadastro feito Por</label>
				<select name="cadastrante">
					<option>Selecione</option>
					<?php
						$result_niveis_acessos =$con->prepare("SELECT * FROM membros order by nome_do_membro");
						$result_niveis_acessos->execute();
						$resultado_niveis_acesso = $result_niveis_acessos->fetchAll(PDO::FETCH_ASSOC);
						foreach($resultado_niveis_acesso as $row_niveis_acessos){?>
							<option value="<?php echo $row_niveis_acessos['id_membro']; ?>"><?php echo $row_niveis_acessos['nome_do_membro']; ?></option> <?php
						}
						
					?>
				</select><br><br>

    <button type="submit"><?= $editar ? 'Atualizar' : 'Salvar' ?></button>

    <?php if ($editar): ?>
        <a href="visitantes.php">Cancelar</a>
    <?php endif; ?>
</form>

<h2>Lista de Visitantes na Igreja</h2>

<table>
    <tr>
        <th>Nome</th>
        <th>Sexo</th>
        <th>Membro</th>
        <th>Telefone</th>
        <th>Cidade</th>
        <th>Pedido de Oraçao</th>
    </tr>

    <?php foreach ($visitantes as $p): ?>
        <tr>
            <td><?= htmlspecialchars($p['nome']) ?></td>
            <td><?= htmlspecialchars($p['sexo']) ?></td>
            <td><?= htmlspecialchars($p['id_tipo']) ?></td>
            <td><?= htmlspecialchars($p['telefone']) ?></td>
            <td><?= htmlspecialchars($p['cidade']) ?></td>
            <td><?= htmlspecialchars($p['oracao']) ?></td>
        <td>
                <a href="visitantes.php?edit=<?= $p['id_visitante'] ?>">Editar</a>
                <a href="visitante.php?delete=<?= $p['id_visitante'] ?>"
                   onclick="return confirm('Deseja excluir esta Visitante ?')">
                   Excluir
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
