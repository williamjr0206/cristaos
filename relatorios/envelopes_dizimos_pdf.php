<?php
ob_start();

require __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
verificaAcesso();

require __DIR__ . '/../vendedor/fpdf/fpdf.php';


/* ==========================
   BUSCAR MEMBROS ATIVOS
========================== */

$stmt = $pdo->query("
    SELECT
        id_membro,
        nome_do_membro,
        codigo_barras
    FROM membros
    WHERE status_atual = 'Ativo'
      AND codigo_barras IS NOT NULL
      AND codigo_barras <> ''
    ORDER BY nome_do_membro
");

$membros = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ==========================
   CONFIGURAÇÃO DO PDF
========================== */

$pdf = new FPDF('P', 'mm', 'A4');
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();

$pdf->SetTitle(utf8_decode('Etiquetas QRCode - Dízimos'));

/*
A4 = 210 x 297 mm

Modelo simples:
3 colunas x 8 linhas = 24 etiquetas por página

Tamanho aproximado por etiqueta:
largura: 63 mm
altura: 32 mm
*/

$margemEsquerda = 10;
$margemTopo = 12;

$larguraEtiqueta = 63;
$alturaEtiqueta = 32;

$espacoColuna = 4;
$espacoLinha = 3;

$colunas = 3;
$linhas = 8;

$contador = 0;

/* ==========================
   GERAR ETIQUETAS
========================== */

foreach ($membros as $m) {

    if ($contador > 0 && $contador % ($colunas * $linhas) == 0) {
        $pdf->AddPage();
    }

    $posicaoNaPagina = $contador % ($colunas * $linhas);

    $coluna = $posicaoNaPagina % $colunas;
    $linha = floor($posicaoNaPagina / $colunas);

    $x = $margemEsquerda + ($coluna * ($larguraEtiqueta + $espacoColuna));
    $y = $margemTopo + ($linha * ($alturaEtiqueta + $espacoLinha));

    $codigo = $m['codigo_barras'];
    $nome = $m['nome_do_membro'];

    $arquivoQrCode = __DIR__ . '/../qrcodes/' . $codigo . '.png';

    // Moldura da etiqueta
    $pdf->Rect($x, $y, $larguraEtiqueta, $alturaEtiqueta);

    // Título
    $pdf->SetXY($x + 2, $y + 2);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell($larguraEtiqueta - 4, 4, utf8_decode('IPI de Muzambinho'), 0, 1, 'C');

    // Nome do membro
    $pdf->SetXY($x + 2, $y + 8);
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->MultiCell($larguraEtiqueta - 22, 3.5, utf8_decode($nome), 0, 'L');

    // Código
    $pdf->SetXY($x + 2, $y + 24);
    $pdf->SetFont('Arial', '', 7);
    $pdf->Cell($larguraEtiqueta - 22, 4, utf8_decode($codigo), 0, 0, 'L');

    // QRCode
    if (file_exists($arquivoQrCode)) {
        $pdf->Image($arquivoQrCode, $x + 43, $y + 8, 16, 16);
    } else {
        $pdf->SetXY($x + 42, $y + 13);
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Cell(18, 4, 'SEM QR', 0, 0, 'C');
    }

    $contador++;
}

/* ==========================
   SAÍDA
========================== */

ob_end_clean();

$pdf->Output('I', 'etiquetas_qrcode_dizimos.pdf');
exit;