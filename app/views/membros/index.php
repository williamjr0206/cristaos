<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"charset="UTF-8">
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
            
            <?= $igreja['nome'] ?>
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
                <?= (isset($editar['id_tipo']) && $editar['id_membro'] == $tipo['id_tipo']) ? 'selected' : '' ?>>
                <?= $tipo['descricao'] ?>
            </option>
        <?php endforeach; ?>
    </select>

<label>Telefone (Colocar somente Números com ddd)</label>
<input type='number' name="telefone"
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
        value="<?= isset($editar['data_nascimento']) ? date('Y-m-d', strtotime($editar['data_nascimento'])) : '' ?>">

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
<input name="nome_sanguineo" 
        value="<?= htmlspecialchars($editar['tipo_sanguineo'] ?? '') ?>">

<label>Estado Civíl</label>
    <select name="estado_civil" required>
    <?php foreach (['Solteiro(a)','Casado(a)', 'Viuvo(a)','Separado(a)','União Estável'] as $es): ?>
        <option value="<?= $es ?>" <?= (isset($editar['estado_civil']) && $editar['estado_civil'] == $es) ? 'selected' : '' ?>>
            <?= $es ?>
        </option>
    <?php endforeach; ?>
</select>

<label>CEP (Colocar somente Números sem traço)</label>
<input type='number' name="cep"
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
        value="<?= htmlspecialchars($editar['e-mail'] ?? '') ?>">

<label>Data de Batismo</label>
<input name="data_batismo" type="date" 
        value="<?= isset($editar['data_batismo']) ? date('Y-m-d', strtotime($editar['data_batismo'])) : '' ?>">

<label>Data de Profissão de Fé</label>
<input name="data_profissao_de_fe" type="date" 
        value="<?= isset($editar['data_profissao_de_fe']) ? date('Y-m-d', strtotime($editar['data_profissao_de_fe'])) : '' ?>">

<label>Cargo</label>
    <select name="id_cargo" required>
        <option value="">Selecione</option>
        <?php foreach ($cargos as $c): ?>
            <option value="<?= $c['id_cargo'] ?>"
                <?= (isset($editar['id_cargo']) && $editar['id_membro'] == $c['id_cargo']) ? 'selected' : '' ?>>
                <?= $c['descricao'] ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Ativo
        <input type="checkbox" name="ativo"
            <?= (!isset($editar) || ($editar['ativo'] ?? 1)) ? 'checked' : '' ?>>
    </label>

    <button type="submit"><?= $editar ? 'Atualizar' : 'Salvar' ?></button>

    <?php if ($editar): ?>
        <a href="membros.php">Cancelar</a>
    <?php endif; ?>
</form>

<h2>Lista de Aulas</h2>

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
</td>            <td>
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
                   onclick="return confirm('Deseja excluir mesmo esse Membro ?')">
                   Excluir
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>