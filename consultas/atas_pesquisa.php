<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../config/database.php';
require __DIR__ . '/../config/auth.php';
require __DIR__ . '/../includes/menu.php';

verificaPerfil(['ADMIN']);

$numero_livro   = trim($_GET['numero_livro'] ?? '');
$reuniao_numero = trim($_GET['reuniao_numero'] ?? '');
$data_reuniao   = trim($_GET['data_reuniao'] ?? '');
$palavra_chave  = trim($_GET['palavra_chave'] ?? '');

$atas = [];

try {
    $sql = "
        SELECT 
            a.id_ata,
            a.numero_livro,
            a.reuniao_numero,
            a.data_reuniao,
            a.id_igreja,
            a.ata_texto,
            i.nome AS igreja,
            COUNT(pa.id_presenca) AS total_presencas
        FROM atas a
        LEFT JOIN presencas_atas pa ON pa.id_ata = a.id_ata
        LEFT JOIN igrejas i ON i.id_igreja = a.id_igreja
        WHERE 1=1
    ";

    $params = [];

    if ($numero_livro !== '') {
        $sql .= " AND a.numero_livro = :numero_livro ";
        $params[':numero_livro'] = $numero_livro;
    }

    if ($reuniao_numero !== '') {
        $sql .= " AND a.reuniao_numero LIKE :reuniao_numero ";
        $params[':reuniao_numero'] = "%{$reuniao_numero}%";
    }

    if ($data_reuniao !== '') {
        $sql .= " AND DATE(a.data_reuniao) = :data_reuniao ";
        $params[':data_reuniao'] = $data_reuniao;
    }

    if ($palavra_chave !== '') {
        $sql .= " AND a.ata_texto LIKE :palavra_chave ";
        $params[':palavra_chave'] = "%{$palavra_chave}%";
    }

    $sql .= "
        GROUP BY 
            a.id_ata,
            a.numero_livro,
            a.reuniao_numero,
            a.data_reuniao,
            a.id_igreja,
            a.ata_texto,
            i.nome
        ORDER BY a.data_reuniao DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $atas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro ao pesquisar atas: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Pesquisa de Atas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f4f6f8;
        }

        h2 {
            margin-bottom: 20px;
        }

        .filtro-box {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 8px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }

        .linha {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
        }

        .campo {
            flex: 1;
            min-width: 220px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 6px;
        }

        input[type="text"],
        input[type="date"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #bbb;
            border-radius: 6px;
            box-sizing: border-box;
        }

        .botoes {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        button, .btn-limpar, .btn-acao {
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
        }

        button {
            background: #2c3e50;
            color: #fff;
        }

        .btn-limpar {
            background: #95a5a6;
            color: #fff;
        }

        .btn-acao {
            background: #1abc9c;
            color: #fff;
            margin-right: 8px;
            margin-top: 8px;
        }

        .btn-pdf {
            background: #c0392b;
        }

        .btn-imprimir {
            background: #2980b9;
        }

        .resultado {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 8px rgba(0,0,0,0.08);
            padding: 20px;
            margin-bottom: 20px;
        }

        .resultado h3 {
            margin-top: 0;
            color: #2c3e50;
        }

        .campo-resultado {
            margin-bottom: 8px;
        }

        .campo-resultado strong {
            color: #333;
        }

        .texto-ata {
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 12px;
            border-radius: 6px;
            white-space: pre-wrap;
            margin-top: 10px;
        }

        .sem-resultados {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #ffeeba;
        }

        @media print {
            .filtro-box,
            .acoes-tela,
            .acoes-ata {
                display: none !important;
            }

            body {
                background: #fff;
                margin: 0;
            }

            .resultado {
                box-shadow: none;
                border: 1px solid #ccc;
                break-inside: avoid;
            }
        }
    </style>

    <script>
        function imprimirResultados() {
            window.print();
        }
    </script>
</head>
<body>

    <h2>Pesquisa de Atas</h2>

    <div class="filtro-box">
        <form method="get">
            <div class="linha">
                <div class="campo">
                    <label for="numero_livro">Número do Livro</label>
                    <input type="text" name="numero_livro" id="numero_livro"
                           value="<?= htmlspecialchars($numero_livro) ?>">
                </div>

                <div class="campo">
                    <label for="reuniao_numero">Número da Reunião</label>
                    <input type="text" name="reuniao_numero" id="reuniao_numero"
                           value="<?= htmlspecialchars($reuniao_numero) ?>">
                </div>

                <div class="campo">
                    <label for="data_reuniao">Data da Ata</label>
                    <input type="date" name="data_reuniao" id="data_reuniao"
                           value="<?= htmlspecialchars($data_reuniao) ?>">
                </div>

                <div class="campo">
                    <label for="palavra_chave">Palavra ou Frase-Chave</label>
                    <input type="text" name="palavra_chave" id="palavra_chave"
                           value="<?= htmlspecialchars($palavra_chave) ?>">
                </div>
            </div>

            <div class="botoes">
                <button type="submit">Pesquisar</button>
                <a href="atas_pesquisa.php" class="btn-limpar">Limpar</a>
            </div>
        </form>
    </div>

    <?php if ($_GET && count($atas) > 0): ?>
        <div class="acoes-tela" style="margin-bottom:20px;">
            <button type="button" onclick="imprimirResultados()" class="btn-acao btn-imprimir">
                Imprimir resultados
            </button>
        </div>
    <?php endif; ?>

    <?php if ($_GET): ?>
        <?php if (count($atas) > 0): ?>
            <?php foreach ($atas as $ata): ?>
                <div class="resultado">
                    <h3>Ata ID <?= htmlspecialchars($ata['id_ata']) ?></h3>

                    <div class="campo-resultado"><strong>ID da Ata:</strong> <?= htmlspecialchars($ata['id_ata']) ?></div>
                    <div class="campo-resultado"><strong>Número do Livro:</strong> <?= htmlspecialchars($ata['numero_livro']) ?></div>
                    <div class="campo-resultado"><strong>Número da Reunião:</strong> <?= htmlspecialchars($ata['reuniao_numero']) ?></div>
                    <div class="campo-resultado"><strong>Data da Reunião:</strong> 
                        <?= !empty($ata['data_reuniao']) ? date('d/m/Y H:i', strtotime($ata['data_reuniao'])) : '' ?>
                    </div>
                    <div class="campo-resultado"><strong>Igreja:</strong> <?= htmlspecialchars($ata['igreja'] ?? '') ?></div>
                    <div class="campo-resultado"><strong>ID da Igreja:</strong> <?= htmlspecialchars($ata['id_igreja']) ?></div>
                    <div class="campo-resultado"><strong>Total de Presenças Vinculadas:</strong> <?= htmlspecialchars($ata['total_presencas']) ?></div>

                    <div class="campo-resultado"><strong>Texto da Ata:</strong></div>
                    <div class="texto-ata"><?= nl2br(htmlspecialchars($ata['ata_texto'])) ?></div>

                    <div class="acoes-ata">
                        <a href="<?= BASE_URL ?>relatorios/ata_pdf.php?id=<?= $ata['id_ata'] ?>" 
                           target="_blank" 
                           class="btn-acao btn-pdf">
                           Gerar PDF
                        </a>

                        <button type="button" class="btn-acao btn-imprimir" onclick="window.print()">
                            Imprimir
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="sem-resultados">
                Nenhuma ata encontrada com os filtros informados.
            </div>
        <?php endif; ?>
    <?php endif; ?>

</body>
</html>