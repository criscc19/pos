<?php
$res=@include("../../main.inc.php");                                   // For root directory
if (! $res) $res=@include("../../../main.inc.php");                // For "custom" directory

dol_include_once('/pos/backend/class/pos.class.php');
//dol_include_once('/pos/frontend/class/mobile_detect.php');
require_once(DOL_DOCUMENT_ROOT."/product/stock/class/entrepot.class.php");
require_once(DOL_DOCUMENT_ROOT."/pos/frontend/include/funciones.php");
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';

if(DOL_VERSION < 11){
	$facnumber = 'facnumber';
	}else{
	$facnumber = 'ref';  
	}	
global $db, $langs,$conf;
$cashid = $_SESSION['TERMINAL_ID'];
$cash = new Cash($db);
$cash->fetch($cashid);
$warehouse = $cash->fk_warehouse;

$bank = New Account($db);

$bank->fetch($cash->fk_paybank);
$banco1_moneda = $bank->currency_code;
if($banco1_moneda=='CRC'){$signo1 = 'CRC';$color1='black';$color1_2='#dcdcdc';}
if($banco1_moneda=='USD'){$signo1 = 'USD';$color1='green';$color1_2='#d7fbd7';}

$bank->fetch($cash->fk_paybank_extra);
$banco2_moneda = $bank->currency_code;
if($banco2_moneda=='CRC'){$signo2 = 'CRC';$color2='black';$color2_2='#dcdcdc';}
if($banco2_moneda=='USD'){$signo2 = 'USD';$color2='green';$color2_2='#d7fbd7';}

$cliente = new Societe($db);
$cliente->fetch($cash->fk_soc);
$pendiente = $cliente->getOutstandingBills();


$cur = new Multicurrency($db);
$cur->fetch($conf->global->ID_MULTIMONEDA);
$cambio = $cur->rates[0]->rate;
$multicurrency_tx = $cambio; 
$cambio_dolar = 1/$cambio;

$control = new ControlCash($db,$_SESSION['TERMINAL_ID']);
(int)$open = $control->get_cash_id_open($user->id); 

$control->fetch((int)$open);

$form = New Form($db);


if(!isset($_GET['soc'])){
header('Location: '.$_SERVER['PHP_SELF'].'?soc='.$cash->fk_soc.'&fk_currency='.$_SESSION['MULTICURRENCY_CODE'].'&tipo_doc='.$_GET['tipo_doc'].'');
}
$soc = new Societe($db);
$soc->fetch($_GET['soc']);
$limite = $soc->outstanding_limit;
$langs->loadLangs(array('companies', 'bills', 'banks', 'multicurrency'));

$currencyRate = new MultiCurrency($db);
$scambio = $currencyRate->getIdAndTxFromCode($db, 'USD');
$cambio = 1/$scambio[1];
//datos del cash
$bodega = $warehouse ;
$societe = $cash->fk_soc;
$usuario = $user->id;
$entity = $conf->entity;



if(GETPOST('action')=='add_paiement'){
require_once DOL_DOCUMENT_ROOT.'/compta/paiement/class/paiement.class.php';	
require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';
if(GETPOST('pago1') > 0 ){
$banco_id = GETPOST('banco1');
$metodo = GETPOST('metodo1');
}

if(GETPOST('pago2') > 0 ){
$banco_id = GETPOST('banco2');
$metodo = GETPOST('metodo2');
}
	foreach($_POST['fid'] as $v){
	
		
	    if(GETPOST('fk_currency') == 'CRC'){
		if($_POST['monto_'.$v] > 0){
		$pagos[$v] = $_POST['monto_'.$v ];
		$multicurrency_pagos = array();
		}
		}
	    if(GETPOST('fk_currency') == 'USD'){
		if($_POST['monto_'.$v] > 0){	
		$pagos = array();		
		$multicurrency_pagos[$v] = $_POST['monto_'.$v ];
		}
		}		
		


		}
//var_dump($pagos,$multicurrency_pagos);exit;	
$paiement = new Paiement($db);
$paiement->datepaye     = strtotime(GETPOST('re'));
$paiement->amounts      = $pagos;   // Array with all payments dispatching with invoice id
$paiement->multicurrency_amounts = $multicurrency_pagos;   // Array with all payments dispatching
$paiement->paiementid   = dol_getIdFromCode($db,$metodo,'c_paiement','code','id',1);
$paiement->num_paiement = GETPOST('num_paiement');
$paiement->multicurrency_code = GETPOST('num_paiement');
$paiement->note         = GETPOST('comment');
$result = $paiement->create($user, 1);		
if ($a['type'] == Facture::TYPE_CREDIT_NOTE) $label='(CustomerInvoicePaymentBack)';  // Refund of a credit note
if($result >0){

$resp = $paiement->addPaymentToBank($user,'payment',GETPOST('comment'), $banco_id,GETPOST('num_paiement'),GETPOST('num_paiement'));
if($resp > 0){

//comprobando si es apartado para creacion de factura electronica
foreach($_POST['fid'] as $v){
	$sq = 'SELECT f.rowid,fe.tipo_doc,f.total_ttc,
	(SELECT SUM(pf.amount) FROM llx_paiement_facture pf WHERE pf.fk_facture=f.rowid) pagado
	FROM llx_facture f
	JOIN llx_facture_extrafields fe  ON fe.fk_object=f.rowid 
	WHERE f.rowid='.$v.'';
	$sql = $db->query($sq);
	while($obj = $db->fetch_object($sql)){
	$tipo = $obj->tipo_doc;
	$total = $obj->total_ttc;
	$pagado = (float)$obj->pagado;
	
	if($tipo == 3 && $pagado >= $total){
		$facturar[] = ['id'=>$v,'facturar'=>1];
		}else{
		$facturar[]	= ['id'=>$v,'facturar'=>0];
		}

	}

		
	}

setEventMessages('Pagos ingresados correctamene','');
//info del pago
$info_pago = new Paiement($db);
$info_pago->fetch($result);
/**
 * registro de pagos desde el cash
 */
$sq="SELECT f.rowid as facid, f.".$facnumber.", f.total_ttc, f.fk_statut,pf.rowid pf_id, pf.amount,pf.multicurrency_amount, s.nom as name, p.note
FROM llx_facture as f
JOIN llx_paiement_facture pf ON pf.fk_facture=f.rowid
JOIN llx_societe s ON f.fk_soc = s.rowid
JOIN llx_paiement p ON pf.fk_paiement=p.rowid
WHERE pf.fk_paiement =".$info_pago->id." GROUP by f.rowid";
$sql = $db->query($sq);
while($obj = $db->fetch_object($sql)){
	if(GETPOST('fk_currency') == 'CRC'){
$fk_paiement_facture = $obj->pf_id;
$user = $user->id;
$terminal = $_SESSION['TERMINAL_ID'];
$idwarehouse = $warehouse;
$bank_line = $info_pago->bank_line;
$fk_facture = $obj->facid;
$fk_paiement = $info_pago->id;
$fk_cierre = $control->id;
$metodo_pago = $info_pago->type_code;
$monto = $obj->amount;
$multicurrency_code = GETPOST('fk_currency');

set_paiement_log($fk_paiement_facture,$user,$terminal,$idwarehouse,$bank_line,$fk_facture,$fk_paiement,$fk_cierre,$metodo_pago,$monto,$multicurrency_code);

        set_money_control($obj->facid,$info_pago->id,$info_pago->id,GETPOST('tipo'),
		$info_pago->bank_line,$info_pago->bank_line,$info_pago->amount,0,$obj->total_ttc,
		0,$scambio,GETPOST('fk_currency'),GETPOST('fk_currency'),0,
		0,$usuario->id,$_SESSION['TERMINAL_ID'],$control->id,$info_pago->type_code,$info_pago->type_code);


		}
	    if(GETPOST('fk_currency') == 'USD'){			
$fk_paiement_facture = $obj->pf_id;
$user = $obj->user->id;
$terminal = $_SESSION['TERMINAL_ID'];
$idwarehouse = $idwarehouse;
$bank_line = $info_pago->bank_line;
$fk_facture = $obj->facid;
$fk_paiement = $info_pago->id;
$fk_cierre = $control->id;
$metodo_pago = $info_pago->type_code;
$monto = $obj->multicurrency_amount;
$multicurrency_code = GETPOST('fk_currency');
set_paiement_log($fk_paiement_facture,$user,$terminal,$idwarehouse,$bank_line,$fk_facture,$fk_paiement,$fk_cierre,$metodo_pago,$monto,$multicurrency_code);


        set_money_control($obj->facid,$info_pago->id,$info_pago->id,GETPOST('tipo'),
		$info_pago->bank_line,$info_pago->bank_line,0,$info_pago->multicurrency_amount,$obj->total_ttc,
		0,$scambio,GETPOST('fk_currency'),GETPOST('fk_currency'),0,
		0,$usuario->id,$_SESSION['TERMINAL_ID'],$control->id,$info_pago->type_code,$info_pago->type_code);
					
		}
		
		
}
/**
 * fin registro de pagos desde el cash
 */
//print '<script type="text/javascript">window.location = "'.$_SERVER['PHP_SELF'].'?soc='.$cash->fk_soc.'&fk_currency='.GETPOST('fk_currency').'&pago='.$result.'&tipo_doc='.GETPOST('tipo_doc').'&estado='.json_encode($facturar).'"</script>';
header('Location: '.$_SERVER['PHP_SELF'].'?soc='.$cash->fk_soc.'&fk_currency='.GETPOST('fk_currency').'&pago='.$result.'&tipo_doc='.GETPOST('tipo_doc').'&estado='.json_encode($facturar).'');exit;
}
else{setEventMessages($paiement->error, $paiement->errors, 'errors');
header('Location: '.$_SERVER['PHP_SELF'].'?soc='.$cash->fk_soc.'&fk_currency='.GETPOST('fk_currency').'&tipo_doc='.GETPOST('tipo_doc').'&estado='.json_encode($facturar).'');exit;
	}
}
else{
setEventMessages($paiement->error, $paiement->errors, 'errors');
header('Location: '.$_SERVER['PHP_SELF'].'?soc='.$cash->fk_soc.'&fk_currency='.GETPOST('fk_currency').'&tipo_doc='.GETPOST('tipo_doc').'&estado='.json_encode($facturar).'');exit;
}
}


?>
<!DOCTYPE html>
<html lang="es">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" type="image/png" href="img/favicon.png"/>
		<title>Venta</title>

		<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
        <script src="https://kit.fontawesome.com/62c4e05a29.js"></script>	
        <link rel='stylesheet'   href='css/easy-autocomplete.css' type='text/css' media='all' />	
        <link rel='stylesheet'   href='css/bootstrap.min.css' type='text/css' media='all' />       
		<link rel='stylesheet'   href='css/mdb.min.css' type='text/css' media='all' />		
        <link rel="stylesheet" href="sweetalert2/dist/sweetalert2.min.css">
		<link rel='stylesheet'   href='css/main.css' type='text/css' media='all' />
	</head>
 <body class="fixed-sn mdb-skin">
<?php include 'tpl/menu2.tpl.php'; ?>

  <div id="barra_c" class="progress md-progress bg-success" style="position: sty;position: sticky;top: 0;z-index: 99999;display:none">
  <div class="indeterminate"></div></div> 
  <!--/.Double navigation-->
<div id="product_content" class="product_content">
<div id="product_arrow"><i class="fas fa-caret-left fa-4x"></i></div>
</div>

  <!--Main Layout-->

    <div class="container-fluid mt-2">
      <?php include 'tpl/abonar.tpl.php'; ?>
    </div>
  <!--Main Layout-->
 </body>
</html> 
<script type='text/javascript' src='js/jquery.easy-autocomplete.js'></script>
<script type='text/javascript' src='js/bootstrap.min.js'></script>
<script type='text/javascript' src='js/mdb.min.js'></script>
<script src="sweetalert2/dist/sweetalert2.min.js"></script>
<script src="js/numeral/numeral.js"></script>

<script>

$(".button-collapse").sideNav();
$("#selectaccountid").addClass('browser-default custom-select');
$("#selectpaiementcode").addClass('browser-default custom-select');
//*******AUTOCOMPLETADO
var options = {
	
  url: function(q) {
    return "ajax/clientes.php";
  },

  getValue: function(element) {
    return element.text;
  },
  
/*    template: {
        type: "iconLeft",
        fields: {
            iconSrc: function(element) {
    return element.icon;
  }
        }
    },  */

  ajaxSettings: {
    dataType: "json",
    method: "POST",
    data: {
      dataType: "json"
    }
  },

  preparePostData: function(data) {
    data.q = $("#select_cliente").val();
    data.e = 1;
    return data;
  },

    requestDelay: 500,  
 list: {

    maxNumberOfElements: 5000,	 

        
        onChooseEvent: function() {
          window.location.replace("<?php echo $_SERVER['PHP_SELF'] ?>?soc="+$("#select_cliente").getSelectedItemData().id+"&<?php echo 'fk_currency='.GETPOST('fk_currency') ?>&<?php echo 'tipo_doc='.GETPOST('tipo_doc') ?>");
        }
    },
  
};

$("#select_cliente").easyAutocomplete(options);
//FIN*******AUTOCOMPLETADO

</script>