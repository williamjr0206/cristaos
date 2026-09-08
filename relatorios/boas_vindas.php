<?php
ob_start();
ini_set('display_errors',1); error_reporting(E_ALL);
require __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/auth.php';
verificaAcesso(); verificaPerfil(['ADMIN','LIDER','OPERADOR']);
require __DIR__.'/../includes/menu.php';

$nomeIgreja='IPI de Muzambinho'; $cidadeIgreja='Muzambinho'; $estadoIgreja='MG';
$id=$_GET['id']??null; $visitante=null; $linkWhatsapp=''; $linkEmail=''; $linkPdf='';

function bv64e($s){return rtrim(strtr(base64_encode($s),'+/','-_'),'=');}
function tokenBV($id,$exp){
    if(!defined('FELICITACOES_SECRET')) die('Chave FELICITACOES_SECRET não configurada.');
    $d=$id.'|'.$exp;
    return bv64e($d.'|'.bv64e(hash_hmac('sha256',$d,FELICITACOES_SECRET,true)));
}
if($id){
 $st=$pdo->prepare("SELECT * FROM visitantes WHERE id_visitante=:id"); $st->execute([':id'=>$id]); $visitante=$st->fetch(PDO::FETCH_ASSOC);
 if($visitante){
  $nome=$visitante['nome']??'Visitante'; $primeiro=explode(' ',trim($nome))[0];
  $tel=preg_replace('/\D/','',(string)($visitante['telefone']??'')); $email=trim((string)($visitante['email']??''));
  $token=tokenBV((int)$visitante['id_visitante'],time()+30*24*60*60);
  $linkPdf=rtrim(BASE_URL,'/').'/relatorios/boas_vindas_pdf.php?token='.rawurlencode($token);
  $msg="Olá, {$primeiro}! Foi uma alegria receber sua visita.\n\nPreparamos com carinho uma mensagem especial de boas-vindas para você:\n".$linkPdf;
  if(strlen($tel)>=10 && $tel!=='1234'){if(substr($tel,0,2)!=='55')$tel='55'.$tel; $linkWhatsapp='https://wa.me/'.$tel.'?text='.rawurlencode($msg);}
  if($email!=='' && filter_var($email,FILTER_VALIDATE_EMAIL)){
   $ass='Seja bem-vindo(a) - '.$nomeIgreja;
   $corpo="Olá, {$primeiro}!\n\nFoi uma alegria receber sua visita.\nPreparamos com carinho uma mensagem especial para você:\n\n{$linkPdf}\n\n{$nomeIgreja}";
   $linkEmail='mailto:'.rawurlencode($email).'?subject='.rawurlencode($ass).'&body='.rawurlencode($corpo);
  }
 }
}
$visitantes=$pdo->query("
    SELECT
        MIN(id_visitante) AS id_visitante,
        nome,
        MAX(telefone) AS telefone,
        MAX(email) AS email
    FROM visitantes
    GROUP BY nome
    ORDER BY nome
")->fetchAll(PDO::FETCH_ASSOC);
function campoBV($v){return trim((string)$v)!==''?htmlspecialchars((string)$v):'Não informado';}
?>
<!doctype html><html lang="pt-br"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Boas-vindas aos Visitantes</title><style>
*{box-sizing:border-box}body{font-family:Arial;margin:20px;background:#f5f6f7;color:#222}.pagina{max-width:1050px;margin:auto}.card{background:#fff;padding:24px;border:1px solid #ddd;border-radius:8px;margin-bottom:22px}h1,h2{margin-top:0}label{display:block;margin:10px 0 5px;font-weight:bold}select{width:100%;max-width:500px;padding:10px;border:1px solid #ccc;border-radius:5px}.btn,button{display:inline-block;padding:11px 17px;border:0;border-radius:5px;text-decoration:none;cursor:pointer;margin:10px 8px 0 0;font-size:14px}button{background:#333;color:#fff}.wa{background:#198754;color:#fff}.em{background:#245b91;color:#fff}.pdf{background:#666;color:#fff}.dados,.carta{line-height:1.7}.carta h2{text-align:center}.assinatura{margin-top:35px}.linha{margin-top:35px;width:280px;border-top:1px solid #333}.aviso{font-size:13px;color:#777;margin-top:12px}@media(max-width:700px){body{margin:10px}.card{padding:17px}.btn,button{width:100%;margin-right:0}}</style></head><body><div class="pagina">
<div class="card"><h1>Boas-vindas aos Visitantes</h1><form method="get"><label>Selecione o visitante</label><select name="id" required><option value="">Selecione</option><?php foreach($visitantes as $v): ?><option value="<?=$v['id_visitante']?>" <?=((string)$id===(string)$v['id_visitante'])?'selected':''?>><?=htmlspecialchars($v['nome'])?></option><?php endforeach;?></select><br><button>Carregar carta</button></form></div>
<?php if($visitante): ?><div class="card dados"><strong>Visitante:</strong> <?=campoBV($visitante['nome'])?><br><strong>Telefone:</strong> <?=campoBV($visitante['telefone'])?><br><strong>E-mail:</strong> <?=campoBV($visitante['email'])?><br>
<?php if($linkWhatsapp):?><a class="btn wa" href="<?=htmlspecialchars($linkWhatsapp)?>" target="_blank">Enviar por WhatsApp</a><?php endif;?>
<?php if($linkEmail):?><a class="btn em" href="<?=htmlspecialchars($linkEmail)?>">Enviar por E-mail</a><?php endif;?>
<a class="btn pdf" href="<?=htmlspecialchars($linkPdf)?>" target="_blank">Visualizar carta em PDF</a><div class="aviso">O link público é protegido por token e expira em 30 dias.</div></div>
<div class="card carta"><h2>Carta de Boas-Vindas</h2><p><?=htmlspecialchars($cidadeIgreja)?> - <?=htmlspecialchars($estadoIgreja)?></p><p>Querido(a) <strong><?=htmlspecialchars($visitante['nome'])?></strong>,</p><p>É uma alegria receber sua visita. Em nome da igreja, queremos lhe dar as boas-vindas e agradecer por ter estado conosco.</p><p>Nossa oração é que você se sinta acolhido(a), cuidado(a) e edificado(a) entre nós. Desejamos que sua caminhada com Deus seja fortalecida a cada dia, e que este contato seja o começo de uma aproximação sincera, fraterna e abençoada.</p><p>Estamos à disposição para orar com você, ouvir suas necessidades e caminhar ao seu lado no que for possível. Caso deseje, teremos alegria em recebê-lo(a) novamente em nossos cultos, aulas, reuniões e demais atividades.</p><p>Seja muito bem-vindo(a).</p><div class="assinatura"><p>Com carinho e em Cristo,</p><p><strong><?=htmlspecialchars($nomeIgreja)?></strong></p><p><?=htmlspecialchars($cidadeIgreja)?> - <?=htmlspecialchars($estadoIgreja)?></p><div class="linha"></div><p>Responsável pelo contato</p></div></div><?php endif;?></div></body></html>