<?php
require_once __DIR__ . '/../config/database.php';

if (!function_exists('nomeUsuarioAtual')) {
    require_once __DIR__ . '/../config/auth.php';
}
$baseUrl = BASE_URL;

$menu = [
    'Cadastros' => [
        ['chave' => 'usuarios',    'titulo' => 'Usuários',    'link' => $baseUrl . 'cadastros/usuarios.php'],
        ['chave' => 'membros',     'titulo' => 'Membros',     'link' => $baseUrl . 'cadastros/membros.php'],
        ['chave' => 'visitantes',  'titulo' => 'Visitantes',  'link' => $baseUrl . 'cadastros/visitantes.php'],
        ['chave' => 'igrejas',     'titulo' => 'Igrejas',     'link' => $baseUrl . 'cadastros/igrejas.php'],
        ['chave' => 'cargos',      'titulo' => 'Cargos',      'link' => $baseUrl . 'cadastros/cargos.php'],
        ['chave' => 'tipo',        'titulo' => 'Tipo',        'link' => $baseUrl . 'cadastros/tipo.php'],
        ['chave' => 'cursos',      'titulo' => 'Cursos',      'link' => $baseUrl . 'cadastros/cursos.php'],
        ['chave' => 'eventos',     'titulo' => 'Eventos',     'link' => $baseUrl . 'cadastros/eventos.php'],
        ['chave' => 'aulas',       'titulo' => 'Aulas',       'link' => $baseUrl . 'cadastros/aulas.php'],
        ['chave' => 'professores', 'titulo' => 'Professores', 'link' => $baseUrl . 'cadastros/professores.php'],
    ],

    'Ferramentas' => [
        ['chave' => 'presencas',    'titulo' => 'Presenças',    'link' => $baseUrl . 'cadastros/presencas_lote.php'],
        ['chave' => 'boas_vindas',  'titulo' => 'Boas-vindas',  'link' => $baseUrl . 'relatorios/boas_vindas.php'],
    ],

    'Relatórios' => [
        ['chave' => 'relatorios',         'titulo' => 'Aniversariantes',    'link' => $baseUrl . 'relatorios/aniversariantes.php'],
        ['chave' => 'relatorios',         'titulo' => 'Lista de Membros',   'link' => $baseUrl . 'relatorios/lista_membros.php'],
        ['chave' => 'lista_de_presencas', 'titulo' => 'Lista de Presenças', 'link' => $baseUrl . 'relatorios/lista_de_presencas.php'],
    ],

    'Sessão' => [
        ['chave' => 'sair', 'titulo' => 'Sair', 'link' => $baseUrl . 'logout.php'],
    ],
];
?>

<style>
    *{box-sizing:border-box}
    .topo-sistema{background:#2c3e50;color:#fff;padding:12px 16px;display:flex;align-items:center;justify-content:space-between;gap:12px;position:sticky;top:0;z-index:999}
    .topo-sistema .titulo{font-size:18px;font-weight:700}
    .topo-sistema .usuario{font-size:14px}
    .menu-botao{display:none;background:#1abc9c;border:none;color:#fff;padding:10px 12px;border-radius:8px;font-size:18px;cursor:pointer}
    .menu-sistema{background:#34495e;padding:8px 12px}
    .menu-grid{display:flex;flex-wrap:wrap;gap:18px}
    .menu-bloco{min-width:180px}
    .menu-bloco h4{margin:6px 0 8px;color:#ecf0f1;font-size:14px;text-transform:uppercase}
    .menu-bloco a{display:block;color:#fff;text-decoration:none;padding:7px 10px;border-radius:8px;margin-bottom:4px}
    .menu-bloco a:hover{background:#1abc9c}
    @media (max-width: 768px){
        .menu-botao{display:inline-block}
        .menu-sistema{display:none}
        .menu-sistema.aberto{display:block}
        .menu-grid{display:block}
        .menu-bloco{margin-bottom:14px}
    }
</style>

<div class="topo-sistema">
    <div class="titulo">Sistema das Igrejas Evangélicas</div>
    <div class="usuario">
        <?= htmlspecialchars(nomeUsuarioAtual()) ?> - Perfil: <strong><?= htmlspecialchars(perfilAtual()) ?></strong>
    </div>
    <button class="menu-botao" type="button" onclick="alternarMenu()">☰</button>
</div>

<nav class="menu-sistema" id="menuSistema">
    <div class="menu-grid">
        <?php foreach ($menu as $grupo => $itens): ?>
            <?php
            $visiveis = array_filter($itens, fn($item) => temPermissao($item['chave']));
            if (!$visiveis) continue;
            ?>
            <div class="menu-bloco">
                <h4><?= htmlspecialchars($grupo) ?></h4>
                <?php foreach ($visiveis as $item): ?>
                    <a href="<?= htmlspecialchars($item['link']) ?>"><?= htmlspecialchars($item['titulo']) ?></a>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</nav>

<script>
function alternarMenu() {
    const menu = document.getElementById('menuSistema');
    menu.classList.toggle('aberto');
}
</script>