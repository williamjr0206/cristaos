<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ob_start();

require __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
verificaAcesso();

require __DIR__ . '/../includes/menu.php';

/* =====================
   EDITAR (carrega antes do form)
===================== */
$editar = null;

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];

    $stmt = $pdo->prepare("SELECT * FROM visitantes WHERE id_visitante = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $editar = $stmt->fetch(PDO::FETCH_ASSOC);
}

/* =====================
   DATA FIXA PARA O FORM
===================== */
$data_fixa = isset($editar['data_cadastro']) && !empty($editar['data_cadastro'])
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
    $id_evento     = $_POST['id_evento'] ?? null;
    $telefone      = $_POST['telefone'] ?? '';
    $email         = $_POST['email'] ?? '';
    $cidade        = $_POST['cidade'] ?? '';
    $endereco      = $_POST['endereco'] ?? '';
    $oracao        = $_POST['oracao'] ?? '';
    $cadastrante   = $_POST['cadastrante'] ?? '';

    if ($id_evento === '') {
        $id_evento = null;
    }

    if ($id) {
        $sql = "UPDATE visitantes SET 
                    nome = :nome,
                    data_cadastro = :data_cadastro,
                    sexo = :sexo,
                    id_membro = :tipomembro,
                    id_evento = :id_evento,
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
                    (nome, sexo, id_membro, id_evento, telefone, email, cidade, endereco, oracao, data_cadastro, cadastrante)
                VALUES 
                    (:nome, :sexo, :tipomembro, :id_evento, :telefone, :email, :cidade, :endereco, :oracao, :data_cadastro, :cadastrante)";

        $stmt = $pdo->prepare($sql);
    }

    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':sexo', $sexo);
    $stmt->bindParam(':tipomembro', $tipomembro);
    $stmt->bindParam(':id_evento', $id_evento, $id_evento === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
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
   SELECTS
===================== */
$stmtTipo = $pdo->query("SELECT id_tipo, descricao FROM tipo ORDER BY descricao");
$tipos = $stmtTipo->fetchAll(PDO::FETCH_ASSOC);

$stmtMembros = $pdo->query("SELECT id_membro, nome_do_membro FROM membros ORDER BY nome_do_membro");
$membros = $stmtMembros->fetchAll(PDO::FETCH_ASSOC);

$stmtEventos = $pdo->query("SELECT id_evento, descricao FROM eventos ORDER BY descricao");
$eventos = $stmtEventos->fetchAll(PDO::FETCH_ASSOC);

/* =====================
   LISTAR
===================== */
$stmt = $pdo->query("
    SELECT 
        visitantes.id_visitante,
        visitantes.nome,
        visitantes.sexo,
        visitantes.id_evento,
        tipo.descricao AS tipo_descricao,
        eventos.descricao AS evento_descricao,
        visitantes.telefone,
        visitantes.email,
        visitantes.cidade,
        visitantes.endereco,
        visitantes.oracao,
        visitantes.cadastrante,
        visitantes.data_cadastro
    FROM visitantes 
    INNER JOIN tipo ON visitantes.id_membro = tipo.id_tipo
    LEFT JOIN eventos ON visitantes.id_evento = eventos.id_evento
    ORDER BY visitantes.data_cadastro desc
");

$visitantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Visitantes</title>
<style>
* { box-sizing: border-box; }
body { font-family: Arial, sans-serif; margin:20px; background:#f5f6f7; color:#222; }
.pagina { max-width:1200px; margin:0 auto; }
.form-container,.lista-container { background:#fff; padding:25px; border:1px solid #ddd; border-radius:7px; margin-bottom:25px; }
h1,h2 { margin-top:0; }
.titulo-secao { margin-top:25px; margin-bottom:15px; padding-bottom:7px; border-bottom:1px solid #eee; font-size:18px; font-weight:bold; }
.campo { margin-bottom:15px; }
.linha { display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:15px; }
.linha-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:15px; margin-bottom:15px; }
label { display:block; margin-bottom:5px; font-weight:bold; font-size:14px; }
input,select,textarea { width:100%; padding:10px; border:1px solid #ccc; border-radius:5px; font-size:15px; background:#fff; font-family:Arial,sans-serif; }
textarea { min-height:90px; resize:vertical; }
input:focus,select:focus,textarea:focus { outline:none; border-color:#777; box-shadow:0 0 0 2px rgba(0,0,0,.06); }
.ajuda { display:block; margin-top:5px; color:#777; font-size:12px; }
.acoes { display:flex; gap:10px; flex-wrap:wrap; margin-top:25px; padding-top:20px; border-top:1px solid #eee; }
.btn { display:inline-block; padding:11px 18px; border:0; border-radius:5px; text-decoration:none; cursor:pointer; font-size:15px; }
.btn-salvar { background:#333; color:#fff; }
.btn-voltar { background:#eee; color:#333; border:1px solid #ccc; }
.tabela-wrap { width:100%; overflow-x:auto; }
table { width:100%; border-collapse:collapse; min-width:1100px; }
th,td { padding:10px 8px; border-bottom:1px solid #e5e5e5; text-align:left; vertical-align:top; font-size:14px; }
th { background:#f3f3f3; }
tbody tr:hover { background:#fafafa; }
.acao-link { display:inline-block; margin:2px 8px 2px 0; text-decoration:none; }
.editar { color:#245b91; } .excluir { color:#a52828; }
@media(max-width:800px) {
 body { margin:10px; }
 .linha,.linha-3 { grid-template-columns:1fr; gap:0; margin-bottom:0; }
 .linha>div,.linha-3>div { margin-bottom:15px; }
 .form-container,.lista-container { padding:18px; }
}
</style>
</head>
<body>
<div class="pagina">
<div class="form-container">
<h1><?= $editar ? 'Editar visitante' : 'Novo visitante' ?></h1>
<form method="post">
<input type="hidden" name="id" value="<?= $editar['id_visitante'] ?? '' ?>">

<div class="titulo-secao">Identificação da visita</div>
<div class="linha">
 <div>
  <label for="data_cadastro">Data e hora da visita *</label>
  <input type="datetime-local" id="data_cadastro" name="data_cadastro"
         value="<?= date('Y-m-d\TH:i', strtotime($data_fixa)) ?>" required>
 </div>
 <div>
  <label for="id_evento">Evento *</label>
  <select name="id_evento" id="id_evento" required>
   <option value="">Selecione...</option>
   <?php foreach ($eventos as $e): ?>
    <option value="<?= $e['id_evento'] ?>" <?= (isset($editar['id_evento']) && $editar['id_evento'] == $e['id_evento']) ? 'selected' : '' ?>>
     <?= htmlspecialchars($e['descricao']) ?>
    </option>
   <?php endforeach; ?>
  </select>
 </div>
</div>

<div class="campo">
 <label for="nome">Nome completo *</label>
 <input type="text" id="nome" name="nome" required value="<?= htmlspecialchars($editar['nome'] ?? '') ?>">
</div>

<div class="linha">
 <div>
  <label for="sexo">Sexo *</label>
  <select name="sexo" id="sexo" required>
   <option value="">Selecione...</option>
   <?php foreach (['Masculino','Feminino'] as $s): ?>
    <option value="<?= $s ?>" <?= (isset($editar['sexo']) && $editar['sexo'] == $s) ? 'selected' : '' ?>><?= $s ?></option>
   <?php endforeach; ?>
  </select>
 </div>
 <div>
  <label for="id_tipomembro">Tipo *</label>
  <select name="id_tipomembro" id="id_tipomembro" required>
   <option value="">Selecione...</option>
   <?php foreach ($tipos as $t): ?>
    <option value="<?= $t['id_tipo'] ?>" <?= (isset($editar['id_membro']) && $editar['id_membro'] == $t['id_tipo']) ? 'selected' : '' ?>>
     <?= htmlspecialchars($t['descricao']) ?>
    </option>
   <?php endforeach; ?>
  </select>
 </div>
</div>

<div class="titulo-secao">Contato e endereço</div>
<div class="linha">
 <div>
  <label for="telefone">Telefone / WhatsApp</label>
  <input type="text" id="telefone" name="telefone" value="<?= htmlspecialchars($editar['telefone'] ?? '') ?>">
  <span class="ajuda">Informe o DDD e o número.</span>
 </div>
 <div>
  <label for="email">E-mail</label>
  <input type="email" id="email" name="email" value="<?= htmlspecialchars($editar['email'] ?? '') ?>">
 </div>
</div>
<div class="linha">
 <div>
  <label for="cidade">Cidade</label>
  <input type="text" id="cidade" name="cidade" value="<?= htmlspecialchars($editar['cidade'] ?? '') ?>">
 </div>
 <div>
  <label for="endereco">Endereço</label>
  <input type="text" id="endereco" name="endereco" value="<?= htmlspecialchars($editar['endereco'] ?? '') ?>">
 </div>
</div>

<div class="titulo-secao">Acompanhamento</div>
<div class="campo">
 <label for="oracao">Pedido de oração</label>
 <textarea id="oracao" name="oracao"><?= htmlspecialchars($editar['oracao'] ?? '') ?></textarea>
</div>
<div class="campo">
 <label for="cadastrante">Cadastrado por</label>
 <select name="cadastrante" id="cadastrante">
  <option value="">Selecione...</option>
  <?php foreach ($membros as $m): ?>
   <option value="<?= $m['id_membro'] ?>" <?= (isset($editar['cadastrante']) && $editar['cadastrante'] == $m['id_membro']) ? 'selected' : '' ?>>
    <?= htmlspecialchars($m['nome_do_membro']) ?>
   </option>
  <?php endforeach; ?>
 </select>
</div>

<div class="acoes">
 <button type="submit" class="btn btn-salvar"><?= $editar ? 'Atualizar visitante' : 'Salvar visitante' ?></button>
 <?php if ($editar): ?><a href="visitantes.php" class="btn btn-voltar">Cancelar</a><?php endif; ?>
</div>
</form>
</div>

<div class="lista-container">
<h2>Lista de visitantes</h2>
<div class="tabela-wrap">
<table>
<thead><tr>
<th>Nome</th><th>Data</th><th>Sexo</th><th>Tipo</th><th>Evento</th>
<th>Telefone</th><th>E-mail</th><th>Cidade</th><th>Endereço</th><th>Oração</th><th>Ações</th>
</tr></thead>
<tbody>
<?php foreach ($visitantes as $v): ?>
<tr>
<td><?= htmlspecialchars($v['nome']) ?></td>
<td><?= !empty($v['data_cadastro']) ? date('d/m/Y H:i', strtotime($v['data_cadastro'])) : '' ?></td>
<td><?= htmlspecialchars($v['sexo']) ?></td>
<td><?= htmlspecialchars($v['tipo_descricao']) ?></td>
<td><?= htmlspecialchars($v['evento_descricao'] ?? '') ?></td>
<td><?= htmlspecialchars($v['telefone']) ?></td>
<td><?= htmlspecialchars($v['email']) ?></td>
<td><?= htmlspecialchars($v['cidade']) ?></td>
<td><?= htmlspecialchars($v['endereco']) ?></td>
<td><?= htmlspecialchars($v['oracao']) ?></td>
<td>
 <a class="acao-link editar" href="visitantes.php?edit=<?= $v['id_visitante'] ?>">Editar</a>
 <a class="acao-link excluir" href="visitantes.php?delete=<?= $v['id_visitante'] ?>" onclick="return confirm('Deseja excluir este visitante?')">Excluir</a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>
</div>
</body>
</html>