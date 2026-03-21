<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../config/database.php';
require __DIR__ . '/../config/auth.php';
require __DIR__ . '/../includes/menu.php';

verificaPerfil(['ADMIN','OPERADOR','LIDER']);

/* =====================
   SALVAR / EDITAR
===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id          = $_POST['id'] ?? null;
    $nome        = $_POST['nome'] ?? '';
    $sexo        = $_POST['sexo'] ?? '';
    $tipomembro  = $_POST['id_tipomembro'] ?? '';
    $telefone    = $_POST['telefone'] ?? '';
    $email       = $_POST['email'] ?? '';
    $cidade      = $_POST['cidade'] ?? '';
    $oracao      = $_POST['oracao'] ?? '';
    $data        = $data['data'] ?? '';
    $cadastrante = $_POST['cadastrante'] ?? '';    

    if ($id) {
        $sql = "UPDATE visitantes SET nome = :nome
        , sexo = :sexo, id_membro = :id_tipomembro, telefone = :telefone, email = :email, cidade = :cidade,
         oracao =:oracao, data_cadastro = :data_cadastro, cadastrante = :cadastrante 
         WHERE id_visitante = :id_visitante";

        $stmt = $con->prepare($sql);
        $stmt->  bindParam('id_visitante', $id); 
        $stmt -> bindParam(':nome', $nome);
        $stmt -> bindParam(':sexo', $sexo);
        $stmt -> bindParam(':id_tipomembro', $tipomembro);
        $stmt -> bindParam(':telefone', $telefone);
        $stmt -> bindParam(':email', $email);
        $stmt -> bindParam(':cidade', $cidade);
        $stmt -> bindParam(':oracao', $oracao);
        $stmt -> bindParam(':data_cadastro', $data);
        $stmt -> bindParam(':cadastrante', $cadastrante);

        } else {

        $sql = "INSERT INTO visitantes (nome, sexo, id_membro, telefone, email,
         cidade, oracao, data_cadastro, cadastrante)
         VALUES (:nome, :sexo, :id_tipomembro,
          :telefone, :email , :cidade, :oracao,
          :data_cadastro, :cadastrante)";

        $stmt = $con->prepare($sql);

        $stmt -> bindParam(':nome', $nome);
        $stmt -> bindParam(':sexo', $sexo);
        $stmt -> bindParam(':id_tipomembro', $tipomembro);
        $stmt -> bindParam(':telefone', $telefone);
        $stmt -> bindParam(':email', $email);
        $stmt -> bindParam(':cidade', $cidade);
        $stmt -> bindParam(':oracao', $oracao);
        $stmt -> bindParam(':data_cadastro', $data);
        $stmt -> bindParam(':cadastrante', $cadastrante);


    }

    $stmt->execute();
    header("Location: visitantes.php");
    exit;
}

/* =====================
   EXCLUIR
===================== */
if (isset($_GET['delete'])) {

    $id = $_GET['delete'];
    verificaPerfil(['ADMIN','OPERADOR','LIDER']);

    $sql = "DELETE FROM visitantes WHERE id_visitante = :id_visitante";
    $stmt = $con ->prepare($sql);
    $stmt->bindParam(':id_visitante',$id);
    $stmt->execute();

    header("Location: visitantes.php");
    exit;
}

/* =====================
   EDITAR
===================== */
$editar = null;

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $con->prepare("SELECT * FROM visitantes WHERE id_visitante = :id_visitante");
    $stmt->bindparam(':id_visitante', $id);
    $stmt->execute();
    $editar = $stmt->fetch(PDO::FETCH_ASSOC);
}

/* =====================
   LISTAR
===================== */
$stmt = $con -> query("SELECT id_visitante, nome, sexo, tipo.descricao, telefone, cidade, oracao, data_cadastro,
cadastrante  FROM visitantes inner join tipo on id_membro = id_tipo order by nome");
$visitantes = $stmt -> fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Visitantes</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        form { margin-bottom: 30px; }
        input { margin: 5px 0; padding: 6px; width: 300px; display: block; }
        select { margin: 5px 0; padding: 6px; width: 300px; display: block; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 5px; }
        th { background: #eee; }
        a { margin-right: 10px; }
    </style>
</head>
<body>

<h2><?= $editar ? 'Editar Visitante' : 'Novo Visitante' ?></h2>

<form method="post">
    <input type="hidden" name="id" value="<?= $editar['id_visitante'] ?? '' ?>">

    <label>Nome</label>
    <input name="nome" required value="<?= htmlspecialchars($editar['nome'] ?? '') ?>">

    <label>Sexo</label>
    <select name="sexo" required>
        <?php foreach (['Masculino','Feminino'] as $s): ?>
            <option value="<?= $s ?>"
                <?= ($editar && $editar['sexo'] === $s) ? 'selected' : '' ?>>
                <?= $s ?>
            </option>
        <?php endforeach; ?>
    </select>

	<label>Tipo de Membro</label> 
				<select name="id_tipomembro" required>
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

    <label>Data do Cadastro</label>
    <input  type="date" name="data" value="<?= htmlspecialchars($editar['data_cadastro'] ?? '') ?>">


	<label>Cadastro feito Por</label>
				<select name="cadastrante">
					<option>Selecione</option>
					<?php
						$result_niveis_acessos =$con->prepare("SELECT * FROM membros order by nome_do_membro");
						$result_niveis_acessos->execute();
						$resultado_niveis_acesso = $result_niveis_acessos->fetchAll(PDO::FETCH_ASSOC);
						foreach($resultado_niveis_acesso as $row_niveis_acessos){?>
							<option value="<?php echo $row_niveis_acessos['nome_do_membro']; ?>"><?php echo $row_niveis_acessos['nome_do_membro']; ?></option> <?php
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

    <?php foreach ($visitantes as $v): ?>
        <tr>
            <td><?= htmlspecialchars($v['nome']) ?></td>
            <td><?= htmlspecialchars($v['sexo']) ?></td>
            <td><?= htmlspecialchars($v['descricao']) ?></td>
            <td><?= htmlspecialchars($v['telefone']) ?></td>
            <td><?= htmlspecialchars($v['cidade']) ?></td>
            <td><?= htmlspecialchars($v['oracao']) ?></td>
        <td>
                <a href="visitantes.php?edit=<?= $v['id_visitante'] ?>">Editar</a>
                <a href="visitante.php?delete=<?= $v['id_visitante'] ?>"
                   onclick="return confirm('Deseja excluir esta Visitante ?')">
                   Excluir
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
