<?php

ini_set('session.gc_maxlifetime', 7200); // 2 horas
session_set_cookie_params(7200);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function usuarioLogado(): bool
{
    return !empty($_SESSION['id_usuario']);
}

function perfilAtual(): string
{
    return strtoupper($_SESSION['perfil'] ?? '');
}

function nomeUsuarioAtual(): string
{
    return $_SESSION['nome'] ?? ($_SESSION['nome_usuario'] ?? 'Usuário');
}

function verificaAcesso(): void
{
    if (!usuarioLogado()) {
        header('Location: ' . (defined('BASE_URL') ? BASE_URL : '/') . 'login.php');
        exit;
    }
}

function verificaPerfil(array $perfisPermitidos): void
{
    verificaAcesso();

    $perfil = perfilAtual();
    $perfisPermitidos = array_map('strtoupper', $perfisPermitidos);

    if (!in_array($perfil, $perfisPermitidos, true)) {
        http_response_code(403);
        echo '<!DOCTYPE html>
        <html lang="pt-br">
        <head>
            <meta charset="UTF-8">
            <title>Acesso negado</title>
            <style>
                body{font-family:Arial,sans-serif;background:#f4f6f8;margin:0;padding:40px}
                .box{max-width:700px;margin:0 auto;background:#fff;padding:30px;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.08)}
                h2{margin-top:0;color:#c0392b}
                a{display:inline-block;margin-top:15px;text-decoration:none;background:#2c3e50;color:#fff;padding:10px 16px;border-radius:8px}
            </style>
        </head>
        <body>
            <div class="box">
                <h2>Acesso negado</h2>
                <p>Seu perfil não possui permissão para acessar esta página.</p>
                <p><strong>Perfil atual:</strong> ' . htmlspecialchars($perfil) . '</p>
                <a href="' . (defined('BASE_URL') ? BASE_URL : '/') . 'index.php">Voltar ao início</a>
            </div>
        </body>
        </html>';
        exit;
    }
}

function temPermissao(string $chave): bool
{
    $perfil = perfilAtual();

    $permissoes = [
        'ADMIN' => [
            'dashboard', 'usuarios', 'membros', 'visitantes', 'igrejas', 'cargos',
            'tipo', 'cursos', 'eventos', 'aulas', 'professores', 'presencas',
            'relatorios', 'consultas', 'lista_de_presencas', 'boas_vindas', 'sair'
        ],
        'OPERADOR' => [
            'dashboard', 'visitantes', 'presencas', 'boas_vindas', 'sair'
        ],
        'LIDER' => [
            'dashboard', 'aulas', 'cursos', 'presencas', 'relatorios',
            'consultas', 'lista_de_presencas', 'boas_vindas', 'sair'
        ],
    ];

    return in_array($chave, $permissoes[$perfil] ?? [], true);
}