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
    $professor   = $_POST['professor'] ?? '';

    if ($id) {
        $sql = "UPDATE professores SET nome_do_professor = :professor
         WHERE id_professor = :id";

        $stmt = $con->prepare($sql);
        $stmt->bindParam('id', $id);
        $stmt->bindParam(':professor',$professor);
        } else {

        $sql = "INSERT INTO cargos (nome_do_professor)
         VALUES (:professor)";

        $stmt = $con->prepare($sql);

        $stmt -> bindParam(':professor', $professor);

    }

    $stmt->execute();
    header("Location: professores.php");
    exit;
}

/* =====================
   EXCLUIR
===================== */
if (isset($_GET['delete'])) {

    $id = $_GET['delete'];
    verificaPerfil(['ADMIN']);

    $sql = "DELETE FROM professores WHERE id_professor = :id";
    $stmt = $con ->prepare($sql);
    $stmt->bindParam(':id',$id);
    $stmt->execute();

    header("Location: professores.php");
    exit;
}

/* =====================
   EDITAR
===================== */
$editar = null;

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $con->prepare("SELECT * FROM professores WHERE id_professor = :id");
    $stmt->bindparam(':id', $id);
    $stmt->execute();
    $editar = $stmt->fetch(PDO::FETCH_ASSOC);
}

/* =====================
   LISTAR
===================== */
$stmt = $con -> query("SELECT * FROM professores order by nome_do_professor");
$professores = $stmt -> fetchAll(PDO::FETCH_ASSOC);

?>


<h2>Lista dos Professores(as) nas Igrejas Evangélicas:</h2>

<table>
    <tr>
        <th>Nome do Professor(a):</th>
        <th>Ações</th>
    </tr>

    <?php foreach ($professores as $p): ?>
        <tr>
            <td><?= htmlspecialchars($p['nome_do_professor']) ?></td>
            <td>
                <a href="professores.php?edit=<?= $p['id_professor'] ?>">Editar</a>
                <a href="professores.php?delete=<?= $p['id_professor'] ?>"
                   onclick="return confirm('Deseja excluir este Professor ?')">
                   Excluir
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
