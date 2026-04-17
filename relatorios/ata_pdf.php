<?php
ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../config/database.php';
require __DIR__ . '/../config/auth.php';
require __DIR__ . '/../vendedor/fpdf/fpdf.php';

verificaPerfil(['ADMIN', 'OPERADOR', 'LIDER']);

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    die('ID da ata inválido.');
}

$stmt = $pdo->prepare("
    SELECT 
        a.id_ata,
        a.numero_livro,
        a.reuniao_numero,
        a.data_reuniao,
        a.id_igreja,
        a.ata_texto,
        i.nome AS igreja
    FROM atas a
    LEFT JOIN igrejas i ON i.id_igreja = a.id_igreja
    WHERE a.id_ata = :id
");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();

$ata = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ata) {
    die('Ata não encontrada.');
}

$stmtPres = $pdo->prepare("
    SELECT 
        m.nome_do_membro,
        c.descricao AS cargo
    FROM presencas_atas pa
    INNER JOIN membros m ON m.id_membro = pa.id_membro
    LEFT JOIN cargos c ON c.id_cargo = m.id_cargo
    WHERE pa.id_ata = :id_ata
    ORDER BY m.nome_do_membro
");
$stmtPres->bindParam(':id_ata', $id, PDO::PARAM_INT);
$stmtPres->execute();
$presencas = $stmtPres->fetchAll(PDO::FETCH_ASSOC);

class PDF extends FPDF
{
    function Header()
    {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 8, utf8_decode('Ata da Reunião'), 0, 1, 'C');
        $this->Ln(4);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Página ' . $this->PageNo(), 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 15);

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(55, 8, utf8_decode('ID da Ata:'), 0, 0);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 8, $ata['id_ata'], 0, 1);

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(55, 8, utf8_decode('Número do Livro:'), 0, 0);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 8, utf8_decode($ata['numero_livro']), 0, 1);

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(55, 8, utf8_decode('Número da Reunião:'), 0, 0);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 8, utf8_decode($ata['reuniao_numero']), 0, 1);

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(55, 8, utf8_decode('Data da Reunião:'), 0, 0);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 8, !empty($ata['data_reuniao']) ? date('d/m/Y H:i', strtotime($ata['data_reuniao'])) : '', 0, 1);

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(55, 8, utf8_decode('Igreja:'), 0, 0);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 8, utf8_decode($ata['igreja'] ?? ''), 0, 1);

$pdf->Ln(4);

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 8, utf8_decode('Texto da Ata:'), 0, 1);

$pdf->SetFont('Arial', '', 11);
$pdf->MultiCell(0, 7, utf8_decode($ata['ata_texto'] ?? ''));

$pdf->Ln(6);

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 8, utf8_decode('Presenças Registradas:'), 0, 1);

$pdf->SetFont('Arial', '', 11);

if (!empty($presencas)) {
    foreach ($presencas as $p) {
        $linha = '- ' . $p['nome_do_membro'];
        if (!empty($p['cargo'])) {
            $linha .= ' (' . $p['cargo'] . ')';
        }
        $pdf->MultiCell(0, 7, utf8_decode($linha));
    }
} else {
    $pdf->Cell(0, 7, utf8_decode('Nenhuma presença registrada para esta ata.'), 0, 1);
}

$pdf->Output('I', 'ata_' . $ata['id_ata'] . '.pdf');
exit;