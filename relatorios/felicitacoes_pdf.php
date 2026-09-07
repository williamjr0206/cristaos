<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../config/database.php';

/*
|--------------------------------------------------------------------------
| CARTA DE ANIVERSÁRIO - ACESSO SEGURO
|--------------------------------------------------------------------------
| - id + acao: somente usuário autenticado do sistema.
| - token: acesso público somente à carta, sem login.
| - O token é assinado e tem validade de 30 dias.
|
| IMPORTANTE:
| Defina FELICITACOES_SECRET em config/database.php (ou outro config
| carregado aqui) com uma chave longa e aleatória, por exemplo:
| define('FELICITACOES_SECRET', 'COLOQUE-AQUI-UMA-CHAVE-GRANDE-E-SECRETA');
|--------------------------------------------------------------------------
*/

if (!defined('FELICITACOES_SECRET')) {
    exit('Configuração de segurança das felicitações não definida.');
}

function base64url_encode_felicitacoes($dados) {
    return rtrim(strtr(base64_encode($dados), '+/', '-_'), '=');
}

function base64url_decode_felicitacoes($dados) {
    $resto = strlen($dados) % 4;
    if ($resto) {
        $dados .= str_repeat('=', 4 - $resto);
    }
    return base64_decode(strtr($dados, '-_', '+/'), true);
}

function criarTokenFelicitacoes($idMembro, $validadeAte) {
    $payload = $idMembro . '|' . $validadeAte;
    $assinatura = hash_hmac('sha256', $payload, FELICITACOES_SECRET);
    return base64url_encode_felicitacoes($payload . '|' . $assinatura);
}

function validarTokenFelicitacoes($token) {
    $decodificado = base64url_decode_felicitacoes($token);
    if ($decodificado === false) return false;

    $partes = explode('|', $decodificado);
    if (count($partes) !== 3) return false;

    [$idMembro, $validadeAte, $assinaturaRecebida] = $partes;

    if (!ctype_digit($idMembro) || !ctype_digit($validadeAte)) return false;
    if ((int)$validadeAte < time()) return false;

    $payload = $idMembro . '|' . $validadeAte;
    $assinaturaCorreta = hash_hmac('sha256', $payload, FELICITACOES_SECRET);

    if (!hash_equals($assinaturaCorreta, $assinaturaRecebida)) return false;

    return (int)$idMembro;
}

$tokenPublico = $_GET['token'] ?? '';
$acessoPublico = ($tokenPublico !== '');

if ($acessoPublico) {
    $id = validarTokenFelicitacoes($tokenPublico);

    if (!$id) {
        http_response_code(403);
        exit('Este link de felicitações é inválido ou expirou.');
    }

    $acao = 'pdf';
} else {
    require __DIR__ . '/../config/auth.php';
    verificaAcesso();

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    $acao = $_GET['acao'] ?? 'pdf';

    if (!$id) exit('Membro inválido.');
}

$stmt = $pdo->prepare("SELECT id_membro,nome_do_membro,telefone,email FROM membros WHERE id_membro=:id AND status_atual='Ativo' LIMIT 1");
$stmt->execute([':id'=>$id]);
$m = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$m) {
    http_response_code(404);
    exit('Membro não encontrado ou inativo.');
}

$nome = trim($m['nome_do_membro']);
$primeiroNome = preg_split('/\s+/', $nome)[0];
$telefone = preg_replace('/\D/', '', (string)($m['telefone'] ?? ''));
$email = trim((string)($m['email'] ?? ''));

$candidatos = [
    __DIR__ . '/../fpdf/fpdf.php',
    __DIR__ . '/../lib/fpdf/fpdf.php',
    __DIR__ . '/../vendor/fpdf/fpdf.php'
];
$fpdf = null;
foreach ($candidatos as $c) if (file_exists($c)) { $fpdf=$c; break; }
if (!$fpdf) exit('FPDF não encontrado. Ajuste o caminho em felicitacoes_pdf.php.');
require_once $fpdf;

function pdfTxt($s){ return iconv('UTF-8','windows-1252//TRANSLIT',$s); }

$pdf = new FPDF('P','mm','A4');
$pdf->SetMargins(25,20,25);
$pdf->SetAutoPageBreak(true,25);
$pdf->AddPage();
$pdf->SetFont('Arial','B',15);
$pdf->MultiCell(0,8,pdfTxt('Igreja Presbiteriana Independente de Muzambinho'),0,'C');
$pdf->Ln(8);
$pdf->SetFont('Arial','B',18);
$pdf->Cell(0,10,pdfTxt('Feliz Aniversário!'),0,1,'C');
$pdf->Ln(8);
$pdf->SetFont('Arial','',12);

$texto = "Querido(a) {$nome},\n\n".
"Neste dia especial, agradecemos a Deus por sua vida e desejamos que o Senhor continue conduzindo seus passos, renovando suas forças e derramando sobre você graça, paz, saúde e muitas bênçãos.\n\n".
"Que este novo ano de vida seja marcado pela presença de Deus, pelo crescimento na fé e por muitos momentos de alegria junto à sua família e aos irmãos em Cristo.\n\n".
"Receba o carinho e as felicitações de toda a Igreja Presbiteriana Independente de Muzambinho.\n\n".
"“Este é o dia que o Senhor fez; regozijemo-nos e alegremo-nos nele.”\nSalmo 118:24\n\n".
"Com carinho,\nIgreja Presbiteriana Independente de Muzambinho";

$pdf->MultiCell(0,7,pdfTxt($texto),0,'J');

$dir=__DIR__.'/felicitacoes_geradas';
if(!is_dir($dir)) @mkdir($dir,0775,true);
$arquivo='felicitacoes_'.$id.'.pdf';
$caminho=$dir.'/'.$arquivo;
$pdf->Output('F',$caminho);

$validadeAte = time() + (30 * 24 * 60 * 60);
$tokenCarta = criarTokenFelicitacoes($id, $validadeAte);
$urlPdf = rtrim(BASE_URL,'/') . '/relatorios/felicitacoes_pdf.php?token=' . rawurlencode($tokenCarta);

if($acao==='whatsapp'){
    if(strlen($telefone)<10 || $telefone==='1234') exit('Este membro não possui WhatsApp válido cadastrado.');
    if(strlen($telefone)<=11 && substr($telefone,0,2)!=='55') $telefone='55'.$telefone;
    $msg="Olá, {$primeiroNome}! A IPI de Muzambinho deseja a você um feliz aniversário!\n\n".
         "Preparamos com carinho uma mensagem especial para você:\n".$urlPdf;
    header('Location: https://wa.me/'.$telefone.'?text='.rawurlencode($msg));
    exit;
}

if($acao==='email'){
    if(!filter_var($email,FILTER_VALIDATE_EMAIL)) exit('Este membro não possui e-mail válido cadastrado.');
    $assunto='Feliz Aniversário - IPI de Muzambinho';
    $corpo="Olá, {$primeiroNome}!\n\nA IPI de Muzambinho deseja a você um feliz aniversário!\n\nSua carta de felicitações:\n{$urlPdf}\n\nQue Deus abençoe sua vida!";
    $headers="Content-Type: text/plain; charset=UTF-8\r\n";
    $ok=@mail($email,$assunto,$corpo,$headers);
    echo $ok ? '<h2>E-mail enviado com sucesso.</h2>' : '<h2>O servidor não confirmou o envio do e-mail.</h2><p>O PDF foi gerado. Para envio automático confiável, configure SMTP/PHPMailer.</p>';
    echo '<p><a target="_blank" href="'.htmlspecialchars($urlPdf).'">Abrir carta em PDF</a></p>';
    exit;
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="'.$arquivo.'"');
header('Content-Length: '.filesize($caminho));
readfile($caminho);
exit;
