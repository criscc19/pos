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

</div>

  <!--Main Layout-->
<br><br>
    <div class="container-fluid mt-5">
    <div class="row">
    <table id="tabla_venta" class="table table-bordered table-striped" width="100%">
  <thead>
 
    <tr class="btn-dark">
      <th scope="col">Ref</th>
      <th scope="col">Cliente</th>
      <th scope="col">Ref cliente</th>
      <th scope="col">Fecha</th>       
      <th scope="col">Vendedor</th>                 
      <th scope="col">Subtotal</th>
      <th scope="col">I.V.A</th>  
      <th scope="col">Total</th>               
    </tr>
  </thead>
  <tbody id="detalle">
   <?php
   $sq = 'SELECT pr.rowid,pr.ref,pr.total_ht,pr.ref_client,pr.total,pr.tva,pr.tms,pex.tipo_doc,pex.fk_cierre,u.rowid u_id,u.login,u.firstname,u.lastname,s.rowid si_is,s.nom,s.name_alias FROM llx_propal pr
   JOIN llx_propal_extrafields pex ON pr.rowid=pex.fk_object
   JOIN llx_societe s ON pr.fk_soc=s.rowid
   JOIN llx_user u ON pr.fk_user_author = u.rowid
   WHERE pex.fk_cierre = '.GETPOST('fk_cierre').'';

   $sql = $db->query($sq);
   while($obj = $db->fetch_object($sql)){
   print '<tr id="tr_'.$obj->rowid.'">
   <td><span  class="tr_fac" data-id="'.$obj->rowid.'" data-estado="0" style="cursor:pointer">'.$obj->ref.'</span>';
   print '<td>'.$obj->nom.' '.$obj->name_alias.'</td>';   
   print '<td>'.$obj->ref_client.'</td>'; 
   print '<td>'.date('d/m/Y H:i:s',strtotime($obj->tms)).'</td>'; 
   print '<td>'.$obj->login.'</td>';
   print '<td>'.price($obj->total_ht).'</td>';
   print '<td>'.price($obj->tva).'</td>';        
   print '<td>'.price($obj->total).'</td>';
           
   print '</tr>';
   }
   ?>
  </tbody>
</table>
</div>
    
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

//detalle de factura
$('.tr_fac').click(get_facturedet);

function get_facturedet(){
fk_propal = $(this).attr('data-id');
elemento = $(this);

detalle = '';
 //envio por ajax
 $.ajax({
  type: "POST",
  url: "ajax/get_propal_det.php",
  data: {
    fk_propal:fk_propal,
    action:'get_propaldet'
  },
  dataType: "json",
  success: function(resp) {
 
  $.each(resp, function( index, value ) {
	detalle +=
	'<tr>'+	
    '<td>'+value.ref+'</td>'+	
    '<td>'+value.label+'</td>'+
    '<td>'+value.qty+'</td>'+ 
	'<td>'+value.remise_percent+'</td>'+
	'<td>'+value.tva_tx+'</td>'+	
	'<td>'+value.total_ht+'</td>'+
	'<td>'+value.total_ttc+'</td>'+	
	'</tr>'
  })

if($(elemento).attr('data-estado') == 1){
$('.detalle_'+fk_propal).remove();	
$(elemento).attr('data-estado',0);
}else{
$(elemento).attr('data-estado',1);
	
$("#tr_"+fk_propal).after(
'<tr class="fac_detalle detalle_'+fk_propal+'"><td colspan="8">'+
'<table style="width: 100%;" class="table bodered border">'+
	'<tr class="btn-info">'+	
    '<td>Ref</td>'+	
    '<td>Producto</td>'+
    '<td>Cantidad</td>'+ 
	'<td>Descuento</td>'+
	'<td>IVA</td>'+	
	'<td>Subtotal</td>'+
	'<td>Total</td>'+	
detalle+

'</table>'+
'</td>'+
'</tr>'
); 
}

  $('#barra_c').hide();
  $('#modalCart').modal('show');
  
}
})
//envio por ajax





}
//fin de detalle factura	

</script>