<?php
$hostAtual = $_SERVER['HTTP_HOST'] ?? '';

$protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$pastaProjeto = '/cristaos/'; // só mude isso se mudar o nome da pasta

define('BASE_URL', $protocolo . $host . $pastaProjeto);
$ambiente = (
    $hostAtual === 'localhost' ||
    $hostAtual === 'localhost'
) ? 'local' : 'prod';

$config = require __DIR__ . "/database.$ambiente.php";

//$conn = new mysqli(
$servername   =    $config['host'];
$username     =    $config['user'];
$password     =    $config['pass'];
$database     =    $config['db'];
//);

$con=new PDO("mysql:host=$servername;dbname=$database;charset=utf8",$username,$password);

$con->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

try {
    $con = new PDO("mysql:host=$servername;dbname=$database;charset=utf8", $username, $password);
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}


//if ($conn->connect_error) {
//    die("Erro de conexão com o banco");
//}
