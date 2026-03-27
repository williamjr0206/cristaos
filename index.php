
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/config/database.php';
require __DIR__ . '/config/auth.php';

verificaAcesso();

//require __DIR__ . '/includes/menu.php';
?>

<h2>Bem-vindo ao sistema das Igrejas Evangélicas</h2>
<style>
body {
    margin: 0;
    font-family: Arial;
}

.topo {
    background: #2c3e50;
    color: white;
    padding: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.menu-btn {
    font-size: 22px;
    cursor: pointer;
    display: none;
}

.menu {
    background: #34495e;
}

.menu a {
    display: inline-block;
    color: white;
    padding: 10px;
    text-decoration: none;
}

.menu a:hover {
    background: #1abc9c;
}

/* MOBILE */
@media (max-width: 768px) {
    .menu {
        display: none;
        flex-direction: column;
    }

    .menu a {
        display: block;
        border-top: 1px solid #2c3e50;
    }

    .menu-btn {
        display: block;
    }

    .menu.ativo {
        display: flex;
    }
}
</style>

<div class="topo">
    <span>📖 Sistema Igreja</span>
    <span class="menu-btn" onclick="toggleMenu()">☰</span>
</div>

<nav class="menu" id="menu">
    <a href="<?= BASE_URL ?>index.php">🏠 Início</a>
    <a href="<?= BASE_URL ?>cadastros/usuarios.php">👤 Usuários</a>
    <a href="<?= BASE_URL ?>cadastros/visitantes.php">👥 Visitantes</a>
    <a href="<?= BASE_URL ?>cadastros/cargos.php">👩‍🏫 Cargos</a>
    <a href="<?= BASE_URL ?>cadastros/igrejas.php">⛪ Igrejas</a>
    <a href="<?= BASE_URL ?>cadastros/cursos.php">📒 Cursos</a>
    <a href="<?= BASE_URL ?>cadastros/eventos.php">👯‍♂️ Eventos</a>
    <a href="<?= BASE_URL ?>cadastros/professores.php">👩‍🏫 Professores</a>
    <a href="<?= BASE_URL ?>cadastros/tipo.php">👥 Tipos</a>
    <a href="<?= BASE_URL ?>cadastros/membros.php">👨‍👩‍👧 Membros</a>
    <a href="<?= BASE_URL ?>cadastros/aulas.php">📓 Aulas</a>
    <a href="<?= BASE_URL ?>cadastros/presencas_lote.php">📖 Presenças</a>
    <a href="<?= BASE_URL ?>relatorios/aniversariantes.php">🎂 Aniversariantes</a>
    <a href="../login.php">🚪 Sair</a>
</nav>

<script>
function toggleMenu() {
    document.getElementById("menu").classList.toggle("ativo");
}
</script>

<hr>