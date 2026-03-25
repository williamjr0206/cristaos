<?php
session_start();

require __DIR__ . '/config/database.php';
require __DIR__ . '/config/auth.php';

// garante que está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Sistema - Igrejas Cristãs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
        }
        h2 {
            margin-bottom: 10px;
        }
        .menu a {
            display: inline-block;
            margin: 6px 10px 6px 0;
            padding: 10px 15px;
            background: #f0f0f0;
            text-decoration: none;
            border-radius: 4px;
            color: #000;
        }
        .menu a:hover {
            background: #ddd;
        }
        .logout {
            margin-top: 30px;
        }
    </style>
</head>
<body>
<h1>Sistema da Igrejas Evangélicas no Brasil</h1>
<h2>Bem-vindo, <?= htmlspecialchars($_SESSION['nome']) ?></h2>
<p>Perfil: <strong><?= $_SESSION['perfil'] ?></strong></p>

<div class="menu">
    <?php if ($_SESSION['perfil'] === 'ADMIN'): ?>
        <a href="<?= BASE_URL ?>cadastros/usuarios.php">👤 Usuários |</a>
        <a href="<?= BASE_URL ?>cadastros/cargos.php">👨‍🔬 Cargos |</a>
        <a href="<?= BASE_URL ?>cadastros/cursos.php">📕 Cursos |</a>
        <a href="<?= BASE_URL ?>cadastros/igrejas.php">⛪ Igrejas |</a>
        <a href="<?= BASE_URL ?>cadastros/eventos.php">👯‍♂️ Eventos |</a>
        <a href="<?= BASE_URL ?>cadastros/professores.php">👨‍🏫 Professores |</a>
        <a href="<?= BASE_URL ?>cadastros/tipo.php">👩‍🤝‍👩 Tipos de Membros |</a>
        <a href="<?= BASE_URL ?>cadastros/membros.php">👩‍🤝‍👩 Membros |</a>
        <a href="<?= BASE_URL ?>relatorios/lista_membros.php">📝 Listagem de Membros |</a>        
    <?php endif; ?>
    <?php if ($_SESSION['perfil'] === 'LIDER' or $_SESSION['perfil'] === 'ADMIN'): ?>
        <a href="<?= BASE_URL ?>cadastros/visitantes.php">👤 Visitantes |</a>
        <a href="<?= BASE_URL ?>cadastros/aulas.php">📓 Aulas |</a>
    <?php endif; ?>

    
    <a href="login.php">🚪 Sair</a>
</div>

</body>
</html>
