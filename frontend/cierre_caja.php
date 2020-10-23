<?php
include_once('../../master.inc.php');
require_once DOL_DOCUMENT_ROOT.'/pos/frontend/include/funciones.php';
dol_include_once('/pos/backend/class/pos.class.php');

$bancos = select_bancos(' AND currency_code="CRC"');
$bancos_dolar = select_bancos(' AND currency_code="USD"');
$denominaciones = select_denominaciones(' AND muticurrency_code="CRC"');
$denominaciones_dolar = select_denominaciones(' AND muticurrency_code="USD"');

$tipo = $_POST['tipo_cierre'];
$moneda = $_POST['arqueo_currency_code'];
$user = New User($db);
$user->fetch($_SESSION['uid']);
$cash = new Cash($db);
$cash->fetch($_POST['cierre_fk_cash']);

$control = new ControlCash($db,$_POST['cierre_fk_cash']);

if($tipo  == 1){
$res = $control->closeControlCash($_POST['cierre_fk_cierre'],$user->id,date('Y-m-d H:i:s'));

if($res){
//guardando detalle de dancos colones
foreach($bancos as $b){
 $res = $db->query('INSERT INTO `llx_cierre_caja_denominacion_bank`
 (
      `fk_cierre_caja`,
       `fk_bank`,
        `monto`
     )
  VALUES 
  (
      "'.$_POST['cierre_fk_cierre'].'",
      "'.$b['id'].'",      
      "'.$_POST['bank_'.$b['id']].'"     
      )'
      
    );   
}

//guardando detalle de dancos dolares
foreach($bancos_dolar as $b){
 $res = $db->query('INSERT INTO `llx_cierre_caja_denominacion_bank`
 (
      `fk_cierre_caja`,
       `fk_bank`,
        `monto`
     )
  VALUES 
  (
      "'.$_POST['cierre_fk_cierre'].'",
      "'.$b['id'].'",      
      "'.$_POST['bank_dolar_'.$b['id']].'"     
      )'
      
    );   
}

//guardando detalle de nominaciones colones
foreach($denominaciones as $d){

 $res = $db->query('INSERT INTO `llx_cierre_caja_denominacion`(
     `fk_cierre_caja`, 
     `fk_denominacion`, 
     `cantidad`) 
     VALUES (
        "'.$_POST['cierre_fk_cierre'].'", 
        "'.$d['id'].'",                
        "'.$_POST['nom_'.$d['id']].'"

         )'
      
    );   
}

//guardando detalle de nominaciones colones
foreach($denominaciones_dolar as $d){
 $res = $db->query('INSERT INTO `llx_cierre_caja_denominacion`(
     `fk_cierre_caja`, 
     `fk_denominacion`, 
     `cantidad`) 
     VALUES (
        "'.$_POST['cierre_fk_cierre'].'", 
        "'.$d['id'].'",                
        "'.$_POST['dolar_nom_'.$d['id']].'"

         )'
      
    );   
}


$res = $cash->set_closed($user);
if($res){
header('location: index.php');   
}
}

}


if($tipo  == 0){
if(GETPOST('arq_moneda') == 'USD'){$real_usd = GETPOST('real');$real_crc = 0;}
if(GETPOST('arq_moneda') == 'CRC'){$real_usd = 0;$real_crc = GETPOST('real');}
$control = new ControlCash($db,$_POST['cierre_fk_cash']);
$control->comment = $_POST['comentario'];
  $data = [
    'userid'=>$user->id,
    'type_control'=>0,
    'amount_reel'=>$real_crc,
    'amount_teoric'=>$_POST['teorico'],
    'multicurrency_amount_teoric'=>$_POST['teorico_usd'],    
    'amount_diff'=>0,    
    'amount_reel'=>GETPOST('real'),
    'multicurrency_amount_reel'=>$real_usd,
    'fk_responsable'=>$_POST['fk_responsable'], 
    'fk_cierre'=>$_POST['cierre_fk_cierre']       
  ];
   $res = $control->create($data);
if($res > 0){
header('location: tpv.php');   
}else{
  var_dump($control->error);
}
}
?>