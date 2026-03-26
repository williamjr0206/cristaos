<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=szjw_cristao", "szjw_william", "Wia685618&zenilda");
    echo "Conectou!";
} catch (PDOException $e) {
    echo $e->getMessage();
}