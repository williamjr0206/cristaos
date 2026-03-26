<?php
$host = "szjw.com.br";
$db   = "szjw_cristaos";
$user = "szjw_william";
$pass = "Wia685618&zenilda";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
