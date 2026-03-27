<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function verificaAcesso()
{
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: ../login.php");
        exit;
    }

    $perfil = $_SESSION['perfil'];
    $pagina = basename($_SERVER['PHP_SELF']);

    // ADMIN pode tudo
    if ($perfil === 'ADMIN') {
        return;
    }

    // Permissões por perfil
    $permissoes = [

        'OPERADOR' => [
            'visitantes.php',
            'presencas.php',
            'presencas_lote.php'
        ],

        'LIDER' => [
            'aulas.php',
            'cursos.php',
            'presencas.php',
            'presencas_lote.php',
            'aniversariantes.php',
            'lista_membros.php'
        ]

    ];

    if (
        !isset($permissoes[$perfil]) ||
        !in_array($pagina, $permissoes[$perfil])
    ) {
        echo "⛔ Acesso não autorizado.";
        exit;
    }
}