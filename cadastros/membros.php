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

    $id                      = $_POST['id'] ?? null;
    $codigo_barras           = $_POST['codigo_barras'] ?? '';
    $id_igreja               = $_POST['id_igreja'] ?? '';
    $nomedomembro            = $_POST['nome_do_membro'] ?? '';
    $id_tipo                 = $_POST['id_tipo'] ?? '';
    $telefone                = $_POST['telefone'] ?? '';
    $sexo                    = $_POST['sexo'] ?? '';
    $datanascimento_mysql    = $_POST['data_nascimento'] ?? '';
    $datanascimento          = !empty($datanascimento_mysql) ? date('Y-m-d', strtotime($datanascimento_mysql)) : null;
    $nacionalidade           = $_POST['nacionalidade'] ?? '';
    $naturalidade            = $_POST['naturalidade'] ?? '';
    $nomedopai               = $_POST['nome_do_pai'] ?? '';
    $nomedamae               = $_POST['nome_da_mae'] ?? '';
    $tiposanguineo           = $_POST['tipo_sanguineo'] ?? '';
    $estadocivil             = $_POST['estado_civil'] ?? '';
    $cep                     = $_POST['cep'] ?? '';
    $endereco                = $_POST['endereco'] ?? '';
    $cidade                  = $_POST['cidade'] ?? '';
    $estado                  = $_POST['estado'] ?? '';
    $email                   = $_POST['email'] ?? '';
    $status_atual            = $_POST['status_atual'] ?? 'Ativo';
    $databatismo_mysql       = $_POST['data_batismo'] ?? '';
    $databatismo             = !empty($databatismo_mysql) ? date('Y-m-d', strtotime($databatismo_mysql)) : null;
    $dataprofissaodefe_mysql = $_POST['data_profissao_de_fe'] ?? '';
    $dataprofissaodefe       = !empty($dataprofissaodefe_mysql) ? date('Y-m-d', strtotime($dataprofissaodefe_mysql)) : null;
    $id_cargo                = $_POST['id_cargo'] ?? '';

    if ($codigo_barras === '') {
        $codigo_barras = null;
    }

    if ($id) {
        $sql = "UPDATE membros SET
                    codigo_barras = :codigo_barras,
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
                    status_atual = :status_atual,
                    data_batismo = :data_batismo,
                    data_profissao_de_fe = :data_profissao_de_fe,
                    id_cargo = :id_cargo
                WHERE id_membro = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
    } else {
        $sql = "INSERT INTO membros (
                    codigo_barras, id_igreja, nome_do_membro, id_tipo, telefone, sexo, data_nascimento,
                    nacionalidade, naturalidade, nome_do_pai, nome_da_mae, tipo_sanguineo,
                    estado_civil, cep, endereco, cidade, estado, email, status_atual,
                    data_batismo, data_profissao_de_fe, id_cargo
                ) VALUES (
                    :codigo_barras, :id_igreja, :nome_do_membro, :id_tipo, :telefone, :sexo, :data_nascimento,
                    :nacionalidade, :naturalidade, :nome_do_pai, :nome_da_mae, :tipo_sanguineo,
                    :estado_civil, :cep, :endereco, :cidade, :estado, :email, :status_atual,
                    :data_batismo, :data_profissao_de_fe, :id_cargo
                )";

        $stmt = $pdo->prepare($sql);
    }

    $stmt->bindParam(':codigo_barras', $codigo_barras);
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
    $stmt->bindParam(':status_atual', $status_atual);
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
        membros.codigo_barras,
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
    WHERE membros.status_atual = 'Ativo'
    ORDER BY membros.nome_do_membro
");

$membros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membros da Igreja</title>

    <style>
        * { box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f5f6f7;
            color: #222;
        }

        .pagina-membros {
            max-width: 1200px;
            margin: 0 auto;
        }

        .form-container,
        .lista-container {
            background: #fff;
            padding: 25px;
            border: 1px solid #ddd;
            border-radius: 7px;
            margin-bottom: 25px;
        }

        .form-container h1,
        .lista-container h2 {
            margin-top: 0;
        }

        .titulo-secao {
            margin-top: 25px;
            margin-bottom: 15px;
            padding-bottom: 7px;
            border-bottom: 1px solid #eee;
            font-size: 18px;
            font-weight: bold;
        }

        .campo { margin-bottom: 15px; }

        .linha {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .linha-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            font-size: 14px;
        }

        input,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 15px;
            background: #fff;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #777;
            box-shadow: 0 0 0 2px rgba(0,0,0,.06);
        }

        .ajuda {
            display: block;
            margin-top: 5px;
            color: #777;
            font-size: 12px;
        }

        .acoes {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .btn {
            display: inline-block;
            padding: 11px 18px;
            border: 0;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            font-size: 15px;
        }

        .btn-salvar {
            background: #333;
            color: #fff;
        }

        .btn-voltar {
            background: #eee;
            color: #333;
            border: 1px solid #ccc;
        }


        .filtros-lista {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 12px;
            margin: 15px 0 18px;
            padding: 15px;
            background: #f8f8f8;
            border: 1px solid #e5e5e5;
            border-radius: 6px;
        }

        .filtros-lista input,
        .filtros-lista select {
            margin: 0;
        }

        .resultado-filtro {
            margin-bottom: 10px;
            color: #666;
            font-size: 13px;
        }

        .tabela-wrap {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 950px;
        }

        th, td {
            padding: 10px 8px;
            border-bottom: 1px solid #e5e5e5;
            text-align: left;
            vertical-align: middle;
            font-size: 14px;
        }

        th {
            background: #f3f3f3;
            font-weight: bold;
        }

        tbody tr:hover { background: #fafafa; }

        .acao-link {
            display: inline-block;
            margin: 2px 8px 2px 0;
            text-decoration: none;
        }

        .editar { color: #245b91; }
        .excluir { color: #a52828; }

        @media (max-width: 800px) {
            body { margin: 10px; }

            .linha,
            .linha-3 {
                grid-template-columns: 1fr;
                gap: 0;
                margin-bottom: 0;
            }

            .linha > div,
            .linha-3 > div {
                margin-bottom: 15px;
            }

            .form-container,
            .lista-container {
                padding: 18px;
            }

            .filtros-lista {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="pagina-membros">

    <div class="form-container">

        <h1><?= $editar ? 'Editar membro' : 'Novo membro' ?></h1>

        <form method="post">
            <input type="hidden" name="id" value="<?= $editar['id_membro'] ?? '' ?>">

            <div class="titulo-secao">Identificação</div>

            <div class="campo">
                <label for="nome_do_membro">Nome completo *</label>
                <input type="text" id="nome_do_membro" name="nome_do_membro" required
                       value="<?= htmlspecialchars($editar['nome_do_membro'] ?? '') ?>">
            </div>

            <div class="linha-3">
                <div>
                    <label for="codigo_barras">Código de barras</label>
                    <input type="text" id="codigo_barras" name="codigo_barras"
                           value="<?= htmlspecialchars($editar['codigo_barras'] ?? '') ?>">
                </div>

                <div>
                    <label for="id_igreja">Igreja *</label>
                    <select name="id_igreja" id="id_igreja" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($igrejas as $igreja): ?>
                            <option value="<?= $igreja['id_igreja'] ?>"
                                <?= (isset($editar['id_igreja']) && $editar['id_igreja'] == $igreja['id_igreja']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($igreja['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="id_tipo">Tipo do membro *</label>
                    <select name="id_tipo" id="id_tipo" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($tipos as $tipo): ?>
                            <option value="<?= $tipo['id_tipo'] ?>"
                                <?= (isset($editar['id_tipo']) && $editar['id_tipo'] == $tipo['id_tipo']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tipo['descricao']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="linha-3">
                <div>
                    <label for="sexo">Sexo *</label>
                    <select name="sexo" id="sexo" required>
                        <option value="">Selecione...</option>
                        <?php foreach (['Masculino','Feminino'] as $s): ?>
                            <option value="<?= $s ?>" <?= (isset($editar['sexo']) && $editar['sexo'] == $s) ? 'selected' : '' ?>>
                                <?= $s ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="data_nascimento">Data de nascimento</label>
                    <input id="data_nascimento" name="data_nascimento" type="date"
                           value="<?= isset($editar['data_nascimento']) && !empty($editar['data_nascimento']) ? date('Y-m-d', strtotime($editar['data_nascimento'])) : '' ?>">
                </div>

                <div>
                    <label for="estado_civil">Estado civil *</label>
                    <select name="estado_civil" id="estado_civil" required>
                        <option value="">Selecione...</option>
                        <?php foreach (['Solteiro(a)','Casado(a)','Viuvo(a)','Separado(a)','União Estável'] as $es): ?>
                            <option value="<?= $es ?>" <?= (isset($editar['estado_civil']) && $editar['estado_civil'] == $es) ? 'selected' : '' ?>>
                                <?= $es ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="titulo-secao">Contato e endereço</div>

            <div class="linha">
                <div>
                    <label for="telefone">Telefone / WhatsApp</label>
                    <input type="text" id="telefone" name="telefone"
                           value="<?= htmlspecialchars($editar['telefone'] ?? '') ?>">
                    <span class="ajuda">Informe o DDD e o número.</span>
                </div>

                <div>
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($editar['email'] ?? '') ?>">
                </div>
            </div>

            <div class="linha-3">
                <div>
                    <label for="cep">CEP</label>
                    <input type="text" id="cep" name="cep"
                           value="<?= htmlspecialchars($editar['cep'] ?? '') ?>">
                </div>

                <div>
                    <label for="cidade">Cidade</label>
                    <input type="text" id="cidade" name="cidade"
                           value="<?= htmlspecialchars($editar['cidade'] ?? '') ?>">
                </div>

                <div>
                    <label for="estado">Estado</label>
                    <input type="text" id="estado" name="estado" maxlength="2"
                           style="text-transform:uppercase"
                           value="<?= htmlspecialchars($editar['estado'] ?? '') ?>">
                    <span class="ajuda">Use a sigla do estado, por exemplo: MG.</span>
                </div>
            </div>

            <div class="campo">
                <label for="endereco">Endereço</label>
                <input type="text" id="endereco" name="endereco"
                       value="<?= htmlspecialchars($editar['endereco'] ?? '') ?>">
            </div>

            <div class="titulo-secao">Dados pessoais e familiares</div>

            <div class="linha">
                <div>
                    <label for="nacionalidade">Nacionalidade</label>
                    <input type="text" id="nacionalidade" name="nacionalidade"
                           value="<?= htmlspecialchars($editar['nacionalidade'] ?? '') ?>">
                </div>

                <div>
                    <label for="naturalidade">Naturalidade</label>
                    <input type="text" id="naturalidade" name="naturalidade"
                           value="<?= htmlspecialchars($editar['naturalidade'] ?? '') ?>">
                </div>
            </div>

            <div class="linha">
                <div>
                    <label for="nome_do_pai">Nome do pai</label>
                    <input type="text" id="nome_do_pai" name="nome_do_pai"
                           value="<?= htmlspecialchars($editar['nome_do_pai'] ?? '') ?>">
                </div>

                <div>
                    <label for="nome_da_mae">Nome da mãe</label>
                    <input type="text" id="nome_da_mae" name="nome_da_mae"
                           value="<?= htmlspecialchars($editar['nome_da_mae'] ?? '') ?>">
                </div>
            </div>

            <div class="campo">
                <label for="tipo_sanguineo">Tipo sanguíneo</label>
                <input type="text" id="tipo_sanguineo" name="tipo_sanguineo"
                       value="<?= htmlspecialchars($editar['tipo_sanguineo'] ?? '') ?>">
            </div>

            <div class="titulo-secao">Vida eclesiástica</div>

            <div class="linha">
                <div>
                    <label for="data_batismo">Data de batismo</label>
                    <input id="data_batismo" name="data_batismo" type="date"
                           value="<?= isset($editar['data_batismo']) && !empty($editar['data_batismo']) ? date('Y-m-d', strtotime($editar['data_batismo'])) : '' ?>">
                </div>

                <div>
                    <label for="data_profissao_de_fe">Data de profissão de fé</label>
                    <input id="data_profissao_de_fe" name="data_profissao_de_fe" type="date"
                           value="<?= isset($editar['data_profissao_de_fe']) && !empty($editar['data_profissao_de_fe']) ? date('Y-m-d', strtotime($editar['data_profissao_de_fe'])) : '' ?>">
                </div>
            </div>

            <div class="linha">
                <div>
                    <label for="id_cargo">Cargo *</label>
                    <select name="id_cargo" id="id_cargo" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($cargos as $c): ?>
                            <option value="<?= $c['id_cargo'] ?>"
                                <?= (isset($editar['id_cargo']) && $editar['id_cargo'] == $c['id_cargo']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['descricao']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="status_atual">Status do membro *</label>
                    <select name="status_atual" id="status_atual" required>
                        <?php foreach (['Ativo','Inativo','Transferido','Desligado','Excluido','Falecido'] as $s): ?>
                            <option value="<?= $s ?>"
                                <?= (($editar['status_atual'] ?? 'Ativo') == $s) ? 'selected' : '' ?>>
                                <?= $s ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="acoes">
                <button type="submit" class="btn btn-salvar">
                    <?= $editar ? 'Atualizar membro' : 'Salvar membro' ?>
                </button>

                <?php if ($editar): ?>
                    <a href="membros.php" class="btn btn-voltar">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="lista-container">
        <h2>Membros ativos</h2>

        <div class="filtros-lista">
            <input type="text" id="filtroNome" placeholder="Pesquisar por nome...">

            <select id="filtroIgreja">
                <option value="">Todas as igrejas</option>
                <?php foreach ($igrejas as $igreja): ?>
                    <option value="<?= htmlspecialchars($igreja['nome']) ?>">
                        <?= htmlspecialchars($igreja['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select id="filtroTipo">
                <option value="">Todos os tipos</option>
                <?php foreach ($tipos as $tipo): ?>
                    <option value="<?= htmlspecialchars($tipo['descricao']) ?>">
                        <?= htmlspecialchars($tipo['descricao']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="resultado-filtro">
            Exibindo <strong id="qtdFiltrada"><?= count($membros) ?></strong> membro(s).
        </div>

        <div class="tabela-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Membro</th>
                        <th>Igreja</th>
                        <th>Tipo</th>
                        <th>WhatsApp</th>
                        <th>Nascimento</th>
                        <th>Função</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($membros as $m): ?>
                    <tr class="linha-membro"
                        data-nome="<?= htmlspecialchars(mb_strtolower($m['nome_do_membro'], 'UTF-8')) ?>"
                        data-igreja="<?= htmlspecialchars($m['igreja']) ?>"
                        data-tipo="<?= htmlspecialchars($m['tipo']) ?>">
                        <td><?= htmlspecialchars($m['codigo_barras'] ?? '') ?></td>
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
                            <a class="acao-link editar" href="membros.php?edit=<?= $m['id_membro'] ?>">Editar</a>
                            <a class="acao-link excluir"
                               href="membros.php?delete=<?= $m['id_membro'] ?>"
                               onclick="return confirm('Deseja excluir mesmo esse Membro ?')">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const filtroNome   = document.getElementById('filtroNome');
    const filtroIgreja = document.getElementById('filtroIgreja');
    const filtroTipo   = document.getElementById('filtroTipo');
    const linhas       = document.querySelectorAll('.linha-membro');
    const qtdFiltrada  = document.getElementById('qtdFiltrada');

    function normalizar(texto) {
        return (texto || '')
            .toLocaleLowerCase('pt-BR')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
    }

    function aplicarFiltros() {
        const nome   = normalizar(filtroNome.value.trim());
        const igreja = filtroIgreja.value;
        const tipo   = filtroTipo.value;
        let total = 0;

        linhas.forEach(function (linha) {
            const nomeLinha   = normalizar(linha.dataset.nome);
            const igrejaLinha = linha.dataset.igreja;
            const tipoLinha   = linha.dataset.tipo;

            const mostrar =
                (!nome || nomeLinha.includes(nome)) &&
                (!igreja || igrejaLinha === igreja) &&
                (!tipo || tipoLinha === tipo);

            linha.style.display = mostrar ? '' : 'none';

            if (mostrar) total++;
        });

        qtdFiltrada.textContent = total;
    }

    filtroNome.addEventListener('input', aplicarFiltros);
    filtroIgreja.addEventListener('change', aplicarFiltros);
    filtroTipo.addEventListener('change', aplicarFiltros);
});
</script>

</body>
</html>
