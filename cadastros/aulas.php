<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../config/database.php';
require __DIR__ . '/../config/auth.php';
require __DIR__ . '/../includes/menu.php';

verificaPerfil(['ADMIN']);

/* =====================
   SALVAR / EDITAR
===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    date_default_timezone_set('America/Sao_Paulo');
    $id             = $_POST['id_aula'] ?? null;
    $dataaula_mysql = $_POST['data_aula'] ?? '';
    $dataaula       = date('Y-m-d H:i:s',strtotime($dataaula_mysql));
    $nomeaula       = $_POST['nome_da_aula'] ?? '';
    $evento         = $_POST['id_evento'] ?? '';
    $curso          = $_POST['id_curso'] ?? '';

    if ($id) {
        $sql = "UPDATE aulas SET  data_aula = :da
        , nome_da_aula = :nome_da_aula, id_evento = :id_evento, id_curso = :id_curso WHERE id_aula = :id";

        $stmt = $con->prepare($sql);

        $stmt -> bindParam(':id', $id);
        $stmt -> bindParam(':da', $dataaula);
        $stmt -> bindParam(':nome_da_aula', $nomeaula);
        $stmt -> bindParam(':id_evento', $evento);
        $stmt -> bindParam(':id_curso', $curso);

        } else {

        $sql = "INSERT INTO aulas (data_aula, nome_da_aula, id_evento, id_curso)
         VALUES (:da, :nome_da_aula, :id_evento, :id_curso)";

        $stmt = $con->prepare($sql);

        $stmt -> bindParam(':da', $dataaula);
        $stmt -> bindParam(':nome_da_aula', $nomeaula);
        $stmt -> bindParam(':id_evento', $evento);
        $stmt -> bindParam(':id_curso', $curso);

       }

        $stmt->execute();
    header("Location: aulas.php");
    exit;
}

/* =====================
   EXCLUIR
===================== */
if (isset($_GET['delete'])) {

    $id = $_GET['delete'];
    verificaPerfil(['ADMIN']);

    $sql = "DELETE FROM aulas WHERE id_aula = :id";
    $stmt = $con ->prepare($sql);
    $stmt->bindParam(':id',$id);
    $stmt->execute();

    header("Location: aulas.php");
    exit;
}

/* =====================
   EDITAR
===================== */
$editar = null;

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $con->prepare("SELECT * FROM aulas where id_aula = :id");
    $stmt->bindparam(':id', $id);
    $stmt->execute();
    $editar = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* =====================
   LISTAR
===================== */
    $stmt = $con->query("SELECT id_aula, data_aula, nome_da_aula, eventos.id_evento,
            eventos.descricao as evento, cursos.id_curso, cursos.nome_do_curso as curso FROM aulas inner join eventos on 
            aulas.id_evento = eventos.id_evento inner join cursos on 
            aulas.id_curso = cursos.id_curso order by data_aula");

$aulas = $stmt -> fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Aulas</title>
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

<h2><?= $editar ? 'Editar Aula' : 'Nova Aula' ?></h2>

<form method="post">

    <input type="hidden" name="id" value="<?= $editar['id_aula'] ?? '' ?>"

    <label>Data e horário da Aula</label>
    <input type="datetime-local" name="data_aula" required value="<?= htmlspecialchars($editar['data_aula'] ?? '') ?>">

    <label>Assunto da Aula</label>
    <input name="nome_da_aula" required value="<?= htmlspecialchars($editar['nome_da_aula'] ?? '') ?>">

    <label>Escolha o Evento para a Aula</label>
				<select name="id_evento" required>
					<?php
						$result_niveis_acessos =$con->prepare("SELECT * FROM eventos order by descricao ");
						$result_niveis_acessos->execute();
						$resultado_niveis_acesso = $result_niveis_acessos->fetchAll(PDO::FETCH_ASSOC);
						foreach($resultado_niveis_acesso as $row_niveis_acessos){?>
							<option value="<?php echo $row_niveis_acessos['id_evento']; ?>"><?php echo $row_niveis_acessos['descricao']; ?></option> <?php
						}
						
					?>
				</select>
    
    <label>Escolha o Curso da Aula</label>
				<select name="id_curso" required>
					<?php
						$result_niveis_acessos =$con->prepare("SELECT * FROM cursos order by nome_do_curso ");
						$result_niveis_acessos->execute();
						$resultado_niveis_acesso = $result_niveis_acessos->fetchAll(PDO::FETCH_ASSOC);
						foreach($resultado_niveis_acesso as $row_niveis_acessos){?>
							<option value="<?php echo $row_niveis_acessos['id_curso']; ?>"><?php echo $row_niveis_acessos['nome_do_curso']; ?></option> <?php
						}
						
					?>
				</select>

    <button type="submit"><?= $editar ? 'Atualizar' : 'Salvar' ?></button>

    <?php if ($editar): ?>
        <a href="aulas.php">Cancelar</a>
    <?php endif; ?>
</form>

<h2>Lista de Aulas</h2>

<table>
    <tr>
        <th>Data da Aula</th>
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
                <a href="aulas.php?edit=<?= $a['id_aula'] ?>">Editar</a>
                <a href="aulas.php?delete=<?= $a['id_aula'] ?>"
                   onclick="return confirm('Deseja excluir esta Aula ?')">
                   Excluir
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
