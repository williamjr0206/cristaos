<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
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

    $id                     = $_POST['id'] ?? null;
    $id_igreja              = $_POST['id_igreja'] ?? '';
    $nomedomembro           = $_POST['nome_do_membro'] ?? '';
    $id_tipo                = $_POST['id_tipo'] ?? '';
    $telefone               = $_POST['telefone'] ?? '';
    $sexo                   = $_POST['sexo'] ?? '';
    $datanascimento_mysql   = $_POST['data_nascimento'] ?? '';
    $datanascimento         = !empty($datanascimento_mysql) ? date('Y-m-d', strtotime($datanascimento_mysql)) : null;
    $nacionalidade          = $_POST['nacionalidade'] ?? '';
    $naturalidade           = $_POST['naturalidade'] ?? '';
    $nomedopai              = $_POST['nome_do_pai'] ?? '';
    $nomedamae              = $_POST['nome_da_mae'] ?? '';
    $tiposanguineo          = $_POST['tipo_sanguineo'] ?? '';
    $estadocivil            = $_POST['estado_civil'] ?? '';
    $cep                    = $_POST['cep'] ?? '';
    $endereco               = $_POST['endereco'] ?? '';
    $cidade                 = $_POST['cidade'] ?? '';
    $estado                 = $_POST['estado'] ?? '';
    $email                  = $_POST['email'] ?? '';
    $ativo                  = $_POST['ativo'] ?? 1;
    $databatismo_mysql      = $_POST['data_batismo'] ?? '';
    $databatismo            = !empty($databatismo_mysql) ? date('Y-m-d', strtotime($databatismo_mysql)) : null;
    $dataprofissaodefe_mysql = $_POST['data_profissao_de_fe'] ?? '';
    $dataprofissaodefe      = !empty($dataprofissaodefe_mysql) ? date('Y-m-d', strtotime($dataprofissaodefe_mysql)) : null;
    $id_cargo               = $_POST['id_cargo'] ?? '';

    if ($id) {
        $sql = "UPDATE membros SET
                    id_igreja = :id_igreja,
                    nome_do_membro = :nome_do_membro,
                    id_tipo = :id_tipo,
                    telefone = :telefone,
                    sexo = :sexo,
                    data_nascimento = :data_nascimento,
                    nacionalidade = :nacionalidade,
                    naturalidade = :naturalidade,
                    nome_do_pai = :nome_do_pai,
                    nome_da_mae = :nome_da_mae,
                    tipo_sanguineo = :tipo_sanguineo,
                    estado_civil = :estado_civil,
                    cep = :cep,
                    endereco = :endereco,
                    cidade = :cidade,
                    estado = :estado,
                    email = :email,
                    ativo = :ativo,
                    data_batismo = :data_batismo,
                    data_profissao_de_fe = :data_profissao_de_fe,
                    id_cargo = :id_cargo
                WHERE id_membro = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
    } else {
        $sql = "INSERT INTO membros (
                    id_igreja, nome_do_membro, id_tipo, telefone, sexo, data_nascimento,
                    nacionalidade, naturalidade, nome_do_pai, nome_da_mae, tipo_sanguineo,
                    estado_civil, cep, endereco, cidade, estado, email, ativo,
                    data_batismo, data_profissao_de_fe, id_cargo
                ) VALUES (
                    :id_igreja, :nome_do_membro, :id_tipo, :telefone, :sexo, :data_nascimento,
                    :nacionalidade, :naturalidade, :nome_do_pai, :nome_da_mae, :tipo_sanguineo,
                    :estado_civil, :cep, :endereco, :cidade, :estado, :email, :ativo,
                    :data_batismo, :data_profissao_de_fe, :id_cargo
                )";

        $stmt = $pdo->prepare($sql);
    }

    $stmt->bindParam(':id_igreja', $id_igreja);
    $stmt->bindParam(':nome_do_membro', $nomedomembro);
    $stmt->bindParam(':id_tipo', $id_tipo);
    $stmt->bindParam(':telefone', $telefone);
    $stmt->bindParam(':sexo', $sexo);
    $stmt->bindParam(':data_nascimento', $datanascimento);
    $stmt->bindParam(':nacionalidade', $nacionalidade);
    $stmt->bindParam(':naturalidade', $naturalidade);
    $stmt->bindParam(':nome_do_pai', $nomedopai);
    $stmt->bindParam(':nome_da_mae', $nomedamae);
    $stmt->bindParam(':tipo_sanguineo', $tiposanguineo);
    $stmt->bindParam(':estado_civil', $estadocivil);
    $stmt->bindParam(':cep', $cep);
    $stmt->bindParam(':endereco', $endereco);
    $stmt->bindParam(':cidade', $cidade);
    $stmt->bindParam(':estado', $estado);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':ativo', $ativo);
    $stmt->bindParam(':data_batismo', $databatismo);
    $stmt->bindParam(':data_profissao_de_fe', $dataprofissaodefe);
    $stmt->bindParam(':id_cargo', $id_cargo);
    $stmt->execute();

    header("Location: " . BASE_URL . "cadastros/membros.php");
    exit;
}

/* =====================
   EXCLUIR
===================== */
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $sql = "DELETE FROM membros WHERE id_membro = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    header("Location: " . BASE_URL . "cadastros/membros.php");
    exit;
}

/* =====================
   EDITAR
===================== */
$editar = null;

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];

    $stmt = $pdo->prepare("SELECT * FROM membros WHERE id_membro = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $editar = $stmt->fetch(PDO::FETCH_ASSOC);
}

/* =====================
   SELECTS
===================== */
$stmt2 = $pdo->query("SELECT id_igreja, nome FROM igrejas ORDER BY nome");
$igrejas = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$stmt3 = $pdo->query("SELECT id_tipo, descricao FROM tipo ORDER BY descricao");
$tipos = $stmt3->fetchAll(PDO::FETCH_ASSOC);

$stmt4 = $pdo->query("SELECT id_cargo, descricao FROM cargos ORDER BY descricao");
$cargos = $stmt4->fetchAll(PDO::FETCH_ASSOC);

/* =====================
   LISTAR
===================== */
$stmt = $pdo->query("
    SELECT
        membros.id_membro,
        membros.nome_do_membro,
        membros.id_igreja,
        membros.id_cargo,
        igrejas.nome AS igreja,
        tipo.descricao AS tipo,
        membros.telefone,
        membros.data_nascimento,
        cargos.descricao AS cargo
    FROM membros
    INNER JOIN igrejas ON membros.id_igreja = igrejas.id_igreja
    INNER JOIN cargos ON membros.id_cargo = cargos.id_cargo
    INNER JOIN tipo ON membros.id_tipo = tipo.id_tipo
    WHERE membros.ativo = 1
    ORDER BY membros.nome_do_membro
");

$membros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" charset="UTF-8">
    <title>Membros da Igreja</title>

    <style>
        body { font-family: Arial; margin: 20px; }
        form { margin-bottom: 30px; }
        input, select { margin: 6px 0; padding: 6px; width: 360px; display: block; }
        table { border-collapse: collapse; width: 100%; }
        a { margin-right: 10px; }
    </style>

</head>
<body>

<h2><?= $editar ? 'Editar Membro' : 'Novo Membro' ?></h2>

<form method="post">

    <input type="hidden" name="id" value="<?= $editar['id_membro'] ?? '' ?>">


    <label>Igreja</label>
    <select name="id_igreja" required>
        <option value="">Selecione</option>
        <?php foreach ($igrejas as $igreja): ?>
            <option value="<?= $igreja['id_igreja'] ?>"
                <?= (isset($editar['id_igreja']) && $editar['id_igreja'] == $igreja['id_igreja']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($igreja['nome']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Nome</label>
    <input name="nome_do_membro" required
           value="<?= htmlspecialchars($editar['nome_do_membro'] ?? '') ?>">

    <label>Tipo do Membro</label>
    <select name="id_tipo" required>
        <option value="">Selecione</option>
        <?php foreach ($tipos as $tipo): ?>
            <option value="<?= $tipo['id_tipo'] ?>"
                <?= (isset($editar['id_tipo']) && $editar['id_tipo'] == $tipo['id_tipo']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($tipo['descricao']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Telefone (Colocar somente Números com ddd)</label>
    <input type="number" name="telefone"
           value="<?= htmlspecialchars($editar['telefone'] ?? '') ?>">

    <label>Sexo</label>
    <select name="sexo" required>
        <?php foreach (['Masculino','Feminino'] as $s): ?>
            <option value="<?= $s ?>" <?= (isset($editar['sexo']) && $editar['sexo'] == $s) ? 'selected' : '' ?>>
                <?= $s ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Data de Nascimento</label>
    <input name="data_nascimento" type="date"
           value="<?= isset($editar['data_nascimento']) && !empty($editar['data_nascimento']) ? date('Y-m-d', strtotime($editar['data_nascimento'])) : '' ?>">

    <label>Nacionalidade</label>
    <input name="nacionalidade"
           value="<?= htmlspecialchars($editar['nacionalidade'] ?? '') ?>">

    <label>Natural do Município de</label>
    <input name="naturalidade"
           value="<?= htmlspecialchars($editar['naturalidade'] ?? '') ?>">

    <label>Nome do Pai</label>
    <input name="nome_do_pai"
           value="<?= htmlspecialchars($editar['nome_do_pai'] ?? '') ?>">

    <label>Nome da Mãe</label>
    <input name="nome_da_mae"
           value="<?= htmlspecialchars($editar['nome_da_mae'] ?? '') ?>">

    <label>Tipo Sanguineo</label>
    <input name="tipo_sanguineo"
           value="<?= htmlspecialchars($editar['tipo_sanguineo'] ?? '') ?>">

    <label>Estado Civíl</label>
    <select name="estado_civil" required>
        <?php foreach (['Solteiro(a)','Casado(a)','Viuvo(a)','Separado(a)','União Estável'] as $es): ?>
            <option value="<?= $es ?>" <?= (isset($editar['estado_civil']) && $editar['estado_civil'] == $es) ? 'selected' : '' ?>>
                <?= $es ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>CEP (Colocar somente Números sem traço)</label>
    <input type="number" name="cep"
           value="<?= htmlspecialchars($editar['cep'] ?? '') ?>">

    <label>Endereço</label>
    <input name="endereco"
           value="<?= htmlspecialchars($editar['endereco'] ?? '') ?>">

    <label>Cidade</label>
    <input name="cidade"
           value="<?= htmlspecialchars($editar['cidade'] ?? '') ?>">

    <label>Estado (Por gentileza, digitar com letras maíuculas)</label>
    <input name="estado"
           value="<?= htmlspecialchars($editar['estado'] ?? '') ?>">

    <label>E-mail</label>
    <input name="email"
           value="<?= htmlspecialchars($editar['email'] ?? '') ?>">

    <label>Data de Batismo</label>
    <input name="data_batismo" type="date"
           value="<?= isset($editar['data_batismo']) && !empty($editar['data_batismo']) ? date('Y-m-d', strtotime($editar['data_batismo'])) : '' ?>">

    <label>Data de Profissão de Fé</label>
    <input name="data_profissao_de_fe" type="date"
           value="<?= isset($editar['data_profissao_de_fe']) && !empty($editar['data_profissao_de_fe']) ? date('Y-m-d', strtotime($editar['data_profissao_de_fe'])) : '' ?>">

    <label>Cargo</label>
    <select name="id_cargo" required>
        <option value="">Selecione</option>
        <?php foreach ($cargos as $c): ?>
            <option value="<?= $c['id_cargo'] ?>"
                <?= (isset($editar['id_cargo']) && $editar['id_cargo'] == $c['id_cargo']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['descricao']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Ativo</label>
    <select name="ativo" required>
        <option value="1" <?= (isset($editar['ativo']) && $editar['ativo'] == 1) ? 'selected' : '' ?>>Ativo</option>
        <option value="2" <?= (isset($editar['ativo']) && $editar['ativo'] == 2) ? 'selected' : '' ?>>Não Ativo</option>
    </select>

    <button type="submit"><?= $editar ? 'Atualizar' : 'Salvar' ?></button>

    <?php if ($editar): ?>
        <a href="membros.php">Cancelar</a>
    <?php endif; ?>
</form>

<h2>Lista</h2>

<table border="1">
    <tr>
        <th>Membro</th>
        <th>Igreja</th>
        <th>Tipo de Membro</th>
        <th>Whatsapp</th>
        <th>Nascido em</th>
        <th>Função</th>
        <th>Ações</th>
    </tr>

    <?php foreach ($membros as $m): ?>
        <tr>
            <td><?= htmlspecialchars($m['nome_do_membro']) ?></td>
            <td><?= htmlspecialchars($m['igreja']) ?></td>
            <td><?= htmlspecialchars($m['tipo']) ?></td>
            <td>
                <?php
                    $tel = preg_replace('/\D/', '', $m['telefone'] ?? '');

                    echo strlen($tel) == 11
                        ? preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $tel)
                        : (strlen($tel) == 10
                            ? preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $tel)
                            : '');
                ?>
            </td>
            <td>
                <?php
                    if (!empty($m['data_nascimento']) && $m['data_nascimento'] != '0000-00-00') {
                        echo date('d/m/Y', strtotime($m['data_nascimento']));
                    }
                ?>
            </td>
            <td><?= htmlspecialchars($m['cargo']) ?></td>
            <td>
                <a href="membros.php?edit=<?= $m['id_membro'] ?>">Editar</a>
                <a href="membros.php?delete=<?= $m['id_membro'] ?>"
                onclick="return confirm('Deseja excluir mesmo esse Membro ?')">Excluir</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>