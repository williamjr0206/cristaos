<?php
$hostAtual = $_SERVER['HTTP_HOST'] ?? 'localhost';

$protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

/*
|--------------------------------------------------------------------------
| BASE_URL
|--------------------------------------------------------------------------
| Local:  http://localhost/cristaos/
| Milbr:  https://seusite.com/cristaos/
|--------------------------------------------------------------------------
*/
$pastaProjeto = '/cristaos/';

if (!defined('BASE_URL')) {
    define('BASE_URL', $protocolo . $host . $pastaProjeto);
}


/*
|--------------------------------------------------------------------------
| CHAVE DAS CARTAS DE FELICITAÇÕES
|--------------------------------------------------------------------------
| Usada para assinar os links públicos das cartas de aniversário.
|--------------------------------------------------------------------------
*/
if (!defined('FELICITACOES_SECRET')) {
    define('FELICITACOES_SECRET', 'IPI-Muzambinho-2026-7f4a9c2e-91d6-4b73-a85c-f2e8d6173c40');
}

$ambiente = ($hostAtual === 'localhost') ? 'local' : 'prod';

$config = require __DIR__ . "/database.$ambiente.php";

$servername = $config['host'];
$username   = $config['user'];
$password   = $config['pass'];
$database   = $config['db'];

try {
    $pdo = new PDO(
        "mysql:host=$servername;dbname=$database;charset=utf8",
        $username,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}