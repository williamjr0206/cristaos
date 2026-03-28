<?php

require_once __DIR__ . '/../../config/database.php';

class MembrosController {

    public function index() {
        global $pdo;


                // Membros
        $stmt = $pdo->query("SELECT * FROM membros");
        $membros = $stmt->fetchAll();

        // Igrejas
        $stmt = $pdo->query("SELECT * FROM igrejas");
        $igrejas = $stmt->fetchAll();

        // Tipos
        $stmt = $pdo->query("SELECT * FROM tipo");
        $tipos = $stmt->fetchAll();

        // Cargos
        $stmt = $pdo->query("SELECT * FROM cargos");
        $cargos = $stmt->fetchAll();


         // Membro igreja tipo membro e cargo
        $stmt = $pdo->query("
    SELECT 
        membros.id_membro,
        membros.nome_do_membro , membros.id_igreja, membros.id_cargo,
        igrejas.id_igreja,igrejas.nome  AS igreja,
        tipo.id_tipo, tipo.descricao AS tipo,
        membros.telefone as telefone, membros.data_nascimento as data_nascimento,
        cargos.descricao as cargo
    FROM membros
    INNER JOIN igrejas ON membros.id_igreja = igrejas.id_igreja
    INNER JOIN cargos ON membros.id_cargo = cargos.id_cargo
    INNER JOIN tipo ON membros.id_tipo = tipo.id_tipo
    ORDER BY membros.nome_do_membro
");
        $membros = $stmt->fetchAll();

        // Controle de edição
        $editar = false;
 

        require __DIR__ . '/../views/membros/index.php';

    }

    public function editar() {
    global $pdo;

    $id = $_GET['id'] ?? null;

    if (!$id) {
        die("ID não informado");
    }

    $stmt = $pdo->prepare("SELECT * FROM membros WHERE id_membro = ?");
    $stmt->execute([$id]);
    $membro = $stmt->fetch();

    // carregar listas também
    $igrejas = $pdo->query("SELECT * FROM igrejas")->fetchAll();
    $tipos = $pdo->query("SELECT * FROM tipo")->fetchAll();
    $cargos = $pdo->query("SELECT * FROM cargos")->fetchAll();

    $editar = true;

    require __DIR__ . '/../views/membros/index.php';

    }

    public function excluir() {
    global $pdo;

    $id = $_GET['id'] ?? null;

    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM membros WHERE id_membro = ?");
        $stmt->execute([$id]);
    }

    header("Location: membros.php");
    exit;

    }
}