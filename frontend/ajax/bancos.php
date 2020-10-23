<?php
include '../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT.'/pos/backend/class/pos.class.php';
$id = $_POST['id'];
	$langs->load('bills');
	$langs->load('companies');
	$langs->load('compta');
	$langs->load('products');
	$langs->load('banks');
	$langs->load('main');
	$langs->load("other");
	$langs->loadLangs(array('bills','companies','compta','products','banks','main','withdrawals'));
	$cashid = $_SESSION['TERMINAL_ID'];
	$cash = new Cash($db);
	$cash->fetch($cashid);
	$warehouse = $cash->fk_warehouse;


$sql = "SELECT rowid, label, bank, courant, clos as status, currency_code";
$sql.= " FROM ".MAIN_DB_PREFIX."bank_account";
$sql.= " WHERE entity IN (".$conf->entity.") AND rowid=".$id."";
$sql.= " ORDER BY label";
$result = $db->query($sql);
$num = $db->num_rows($result);
while ($obj = $db->fetch_object($result)) {
$courant = 	$obj->courant;
if($obj->currency_code == 'CRC'){$signo = '₡';$color='black';$color2='#dcdcdc';}
if($obj->currency_code == 'USD'){$signo = '$';$color='green';$color2='#d7fbd7';}
$bancos['banco'] = ['id'=>$obj->rowid,'label'=>$langs->trans($obj->label),'currency_code'=>$obj->currency_code,'courant'=>$obj->courant,'signo'=>$signo,'color'=>$color,'color2'=>$color2];
}


$sql = "SELECT id, code, libelle as label, type, active";
$sql.= " FROM ".MAIN_DB_PREFIX."c_paiement";
$sql.= " WHERE active=1";
if($courant ==2){
	$sql.= ' AND id='.$cash->fk_modepaycash.'';
}else{
$sql.= ' AND id !='.$cash->fk_modepaycash.'';	
}

//if ($active >= 0) $sql.= " AND active = ".$active;
$resql = $db->query($sql);
while($obj = $db->fetch_object($resql)){
$bancos ['metodos'][]=['id'=>$obj->id,'code'=>$obj->code,'label'=>$langs->trans('PaymentType'.$obj->code)];

}

echo json_encode($bancos);
?>
