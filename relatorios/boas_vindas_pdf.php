<?php
ini_set('display_errors',1); error_reporting(E_ALL);
require __DIR__.'/../config/database.php';

function bv64d($s){$r=strlen($s)%4;if($r)$s.=str_repeat('=',4-$r);return base64_decode(strtr($s,'-_','+/'),true);}
function validaTokenBV($t){
 if(!defined('FELICITACOES_SECRET')||!$t)return false;
 $x=bv64d($t); if($x===false)return false; $p=explode('|',$x); if(count($p)!==3)return false;
 [$id,$exp,$sig64]=$p; if(!ctype_digit($id)||!ctype_digit($exp)||time()>(int)$exp)return false;
 $sig=bv64d($sig64); if($sig===false)return false; $esperada=hash_hmac('sha256',$id.'|'.$exp,FELICITACOES_SECRET,true);
 return hash_equals($esperada,$sig)?(int)$id:false;
}
$id=validaTokenBV($_GET['token']??''); if(!$id){http_response_code(403);die('Link inválido ou expirado.');}
$st=$pdo->prepare("SELECT * FROM visitantes WHERE id_visitante=:id");$st->execute([':id'=>$id]);$v=$st->fetch(PDO::FETCH_ASSOC);
if(!$v){http_response_code(404);die('Visitante não encontrado.');}

$poss=[__DIR__.'/../fpdf/fpdf.php',__DIR__.'/../lib/fpdf/fpdf.php',__DIR__.'/../libs/fpdf/fpdf.php',__DIR__.'/../vendor/fpdf/fpdf.php',__DIR__.'/../vendor/setasign/fpdf/fpdf.php'];
$fpdf=null;foreach($poss as $a){if(file_exists($a)){$fpdf=$a;break;}}if(!$fpdf)die('Biblioteca FPDF não encontrada.');require_once $fpdf;
function pt($s){$x=@iconv('UTF-8','windows-1252//TRANSLIT',$s);return $x!==false?$x:$s;}

$igreja='IPI de Muzambinho';$cidade='Muzambinho';$uf='MG';$nome=$v['nome']??'Visitante';
$pdf=new FPDF('P','mm','A4');$pdf->SetMargins(22,20,22);$pdf->SetAutoPageBreak(true,20);$pdf->AddPage();
$pdf->SetFont('Arial','B',14);$pdf->Cell(0,8,pt($igreja),0,1,'C');$pdf->SetFont('Arial','',10);$pdf->Cell(0,6,pt($cidade.' - '.$uf),0,1,'C');
$pdf->Ln(10);$pdf->SetFont('Arial','B',18);$pdf->Cell(0,10,pt('Carta de Boas-Vindas'),0,1,'C');$pdf->Ln(8);$pdf->SetFont('Arial','',12);
$pdf->MultiCell(0,7,pt('Querido(a) '.$nome.','));$pdf->Ln(4);
$ps=['É uma alegria receber sua visita. Em nome da igreja, queremos lhe dar as boas-vindas e agradecer por ter estado conosco.','Nossa oração é que você se sinta acolhido(a), cuidado(a) e edificado(a) entre nós. Desejamos que sua caminhada com Deus seja fortalecida a cada dia, e que este contato seja o começo de uma aproximação sincera, fraterna e abençoada.','Estamos à disposição para orar com você, ouvir suas necessidades e caminhar ao seu lado no que for possível. Caso deseje, teremos alegria em recebê-lo(a) novamente em nossos cultos, aulas, reuniões e demais atividades.','Seja muito bem-vindo(a).'];
foreach($ps as $p){$pdf->MultiCell(0,7,pt($p));$pdf->Ln(4);}
$pdf->Ln(8);$pdf->MultiCell(0,7,pt('Com carinho e em Cristo,'));$pdf->Ln(5);$pdf->SetFont('Arial','B',12);$pdf->MultiCell(0,7,pt($igreja));$pdf->SetFont('Arial','',11);$pdf->MultiCell(0,7,pt($cidade.' - '.$uf));
$pdf->Ln(15);$x=$pdf->GetX();$y=$pdf->GetY();$pdf->Line($x,$y,$x+65,$y);$pdf->Ln(2);$pdf->SetFont('Arial','',10);$pdf->Cell(65,6,pt('Responsável pelo contato'),0,1,'C');
$ascii=@iconv('UTF-8','ASCII//TRANSLIT',$nome);$seg=preg_replace('/[^A-Za-z0-9_-]/','_',$ascii?:'visitante');$pdf->Output('I','boas_vindas_'.$seg.'.pdf');exit;