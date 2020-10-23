<?php
$res=@include("../../main.inc.php");                                   // For root directory
if (! $res) $res=@include("../../../main.inc.php");                // For "custom" directory

dol_include_once('/pos/backend/class/pos.class.php');
//dol_include_once('/pos/frontend/class/mobile_detect.php');
require_once(DOL_DOCUMENT_ROOT."/product/stock/class/entrepot.class.php");
require_once(DOL_DOCUMENT_ROOT."/pos/frontend/include/funciones.php");
require_once(DOL_DOCUMENT_ROOT."/pos/frontend/include/funciones_restaurant.php");
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
        <script src="https://cdn.jsdelivr.net/npm/@shopify/draggable@1.0.0-beta.5/lib/draggable.bundle.js"></script>  
           
        <link rel='stylesheet'   href='css/easy-autocomplete.css' type='text/css' media='all' />	
        <link rel='stylesheet'   href='css/bootstrap.min.css' type='text/css' media='all' />       
		<link rel='stylesheet'   href='css/mdb.min.css' type='text/css' media='all' />		
        <link rel="stylesheet" href="sweetalert2/dist/sweetalert2.min.css">
    <link rel='stylesheet'   href='css/main.css' type='text/css' media='all' />
    <link rel='stylesheet'   href='css/restaurant.css' type='text/css' media='all' />

	</head>
 <body class="fixed-sn mdb-skin">
<?php include 'tpl/menu_restaurant.tpl.php'; ?>

  <div id="barra_c" class="progress md-progress bg-success" style="position: sty;position: sticky;top: 0;z-index: 99999;display:none">
  <div class="indeterminate"></div></div> 
  <!--/.Double navigation-->
</div>

  <!--Main Layout-->
  <br><br>
    <div class="container-fluid mt-5">
    <ul class="nav nav-tabs" id="myTab" role="tablist">
<?php
$dep = get_departamentos($fk_dep='',$sqs='');
foreach($dep as $v){?>

  <li class="nav-item">
    <a class="nav-link" id="tab_<?php print $v->name ?>" data-toggle="tab" href="#tab_cont_<?php print $v->id ?>" role="tab" aria-controls="tab_cont_<?php print $v->id ?>"
      aria-selected="true"><?php print $v->name ?></a>
  </li>

<?php } ?>
</ul>
<div class="tab-content" id="myTabContent">
<?php foreach($dep as $v){?>
  <div class="tab-pane fade show" id="tab_cont_<?php print $v->id ?>" role="tabpanel" aria-labelledby="tab_cont_<?php print $v->id ?>">

  <table class="table table-bordered" width="100%">
  <tr class="btn-dark">
      <th scope="col" width="">Mesa</th>
      <th scope="col" width="">Factura</th>      
      <th scope="col" width="">Cliente</th>
      <th scope="col" width="">Ref cliente</th>      
      <th scope="col" width="">Mesero</th>    
       <th scope="col" width=""></th>                  
    </tr>
  <tbody>
<?php
$sq = 'SELECT f.rowid f_id,f.fk_mesa,s.rowid s_id,u.rowid u_id, u.firstname,u.lastname,u.login,s.nom,s.name_alias,
f.ref_client,f.type,f.facnumber,(SELECT SUM(fd.total_ttc) FROM llx_facturedet_cashdespro fd WHERE fd.fk_facture=f.rowid) total_ttc,
(SELECT SUM(fd.multicurrency_total_ttc) FROM llx_facturedet_cashdespro fd WHERE fd.fk_facture=f.rowid) multicurrency_total_ttc,
f.multicurrency_code,f.tms,rm.name mesa,p.rowid p_id,p.ref,p.label,p.description,c.rowid c_id,c.label
FROM llx_facture_cashdespro f
JOIN llx_societe s ON f.fk_soc=s.rowid
LEFT JOIN llx_pos_restaurant_mesas rm ON f.fk_mesa=rm.rowid
LEFT JOIN llx_user u ON f.fk_user_author=u.rowid
LEFT JOIN llx_facturedet_cashdespro fd ON fd.fk_facture=f.rowid
LEFT JOIN llx_product p ON fd.fk_product=p.rowid
LEFT JOIN llx_categorie_product cp ON cp.fk_product=p.rowid 
LEFT JOIN llx_categorie c ON cp.fk_categorie=c.rowid 
WHERE c.rowid IN('.$v->categorias.')
GROUP BY f.rowid
ORDER BY f.rowid ASC';
$sql = $db->query($sq);
while($obj = $db->fetch_object($sql)){
print '<tr class="unique-color" style="color:#FFF">';
print '<td>'.$obj->mesa.'</td>';
print '<td>'.$obj->facnumber.'</td>';
print '<td>'.$obj->nom.'</td>';
print '<td>'.$obj->ref_client.'</td>';
print '<td>'.$obj->firstname.' '.$obj->lastname.'</td>';
print '<td><div  class="print_orden" data-fk_facture="'.$obj->f_id.'" data-fk_categorie="'.$obj->c_id.'" data-fk_mesa="'.$obj->fk_mesa.'"><i class="fas fa-file-invoice fa-2x"></i></td></div>';
print '</tr>';
print '<tr>';
print '<td>ID</td>';
print '<td>Ref</td>';
print '<td>Producto</td>';
print '<td>Cantidad</td>';
print '<td>Categoria</td>';
print '<td></td>';
print '</tr>';
$sq2 = 'SELECT f.rowid f_id,s.rowid s_id,u.rowid u_id, u.firstname,u.lastname,u.login,s.nom,s.name_alias,
f.ref_client,f.type,f.facnumber,(SELECT SUM(fd.total_ttc) FROM llx_facturedet_cashdespro fd WHERE fd.fk_facture=f.rowid) total_ttc,
(SELECT SUM(fd.multicurrency_total_ttc) FROM llx_facturedet_cashdespro fd WHERE fd.fk_facture=f.rowid) multicurrency_total_ttc,
f.multicurrency_code,f.tms,rm.name mesa,p.rowid p_id,p.ref,p.label,p.description,c.rowid c_id,c.label categoria,fd.qty,fd.fk_product,
fd.rowid fd_id,fd.estado
FROM llx_facture_cashdespro f
JOIN llx_societe s ON f.fk_soc=s.rowid
LEFT JOIN llx_pos_restaurant_mesas rm ON f.fk_mesa=rm.rowid
LEFT JOIN llx_user u ON f.fk_user_author=u.rowid
LEFT JOIN llx_facturedet_cashdespro fd ON fd.fk_facture=f.rowid
LEFT JOIN llx_product p ON fd.fk_product=p.rowid
LEFT JOIN llx_categorie_product cp ON cp.fk_product=p.rowid 
LEFT JOIN llx_categorie c ON cp.fk_categorie=c.rowid 
WHERE f.rowid = '.$obj->f_id.' AND c.rowid IN('.$v->categorias.')
GROUP BY fd.fk_product
ORDER BY f.rowid ASC';
$sql2 = $db->query($sq2);
while($obj2 = $db->fetch_object($sql2)){
if($obj2->estado == 1){$estado = ' checked ';}else{$estado = '';}
print '<tr>';
print '<td>'.$obj2->fd_id.'</td>';
print '<td>'.$obj2->ref.'</td>';
print '<td>'.$obj2->label.'</td>';
print '<td>'.$obj2->qty.'</td>';
print '<td>'.$obj2->categoria.'</td>';
print '<td>
<div class="form-check">
<input'.$estado.' data-id="'.$obj2->fd_id.'" data-fk_categoria="'.$obj2->c_id.'"  data-fk_mesa="'.$obj->fk_mesa.'" type="checkbox" class="check_'.$obj2->fd_id.' form-check-input" id="check_'.$obj2->fd_id.'">
<label class="form-check-label" for="check_'.$obj2->fd_id.'">
</label></div>
</td>';
print '</tr>';
}

}
?>
  </tbody>
  </table>

    </div>

<?php } ?>
    </div>
  <!--Main Layout-->

</body>
</html> 
<script type='text/javascript' src='js/jquery.easy-autocomplete.js'></script>
<script type='text/javascript' src='js/bootstrap.min.js'></script>
<script type='text/javascript' src='js/mdb.min.js'></script>
<script src="sweetalert2/dist/sweetalert2.min.js"></script>
<script src="js/numeral/numeral.js"></script>
<script src="js/venta.js"></script>
<script src="js/restaurant.js"></script>
<script>
$(".button-collapse").sideNav();

</script>

<script>
$('.form-check-input').change(cambiar_estado);
function cambiar_estado(){
if($(this).prop('checked')){
 estado = 1;
}else{
estado = 0;  
} 
id = $(this).data('id');
fk_categoria = $(this).data('fk_categoria');
 //envio por ajax
 $.ajax({
  type: "POST",
  url: "ajax/add_line_restaurant.php",
  data: {
    id:id,
    estado:estado,
    action:'update_estado'
  },
  dataType: "json",
  success: function(resp) {

  $('#barra_c').hide();
  }
  
  })
  //envio por ajax

}

$('.print_orden').click(function(){
  url = 'tpl/comanda_ticket.php?fk_facture='+$(this).data('fk_facture')+'&fk_mesa='+$(this).data('fk_mesa')+'&fk_categorie='+$(this).data('fk_categorie')+''
  window.open(url, "_blank");
})
</script>