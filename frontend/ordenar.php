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
    <?php $fk_facture = (int)$_GET['fk_facture'];?> 
<input type="hidden" name="fk_soc" id="fk_soc" value="<?php echo $cliente->id;?>">
<input type="hidden" name="forme_juridique_code" id="forme_juridique_code" value="<?php echo $cliente->forme_juridique_code;?>">
<input type="hidden" name="idprof1" id="idprof1" value="<?php echo $cliente->idprof1;?>">
<input type="hidden" name="limite_credito" id="limite_credito" value="<?php echo $cliente->outstanding_limit;?>">
<input type="hidden" name="credito_usado" id="credito_usado" value="<?php echo (float)$pendiente['opened'];?>">
<input type="hidden" name="credito_disponible" id="credito_disponible" value="<?php echo (float)$cliente->outstanding_limit - (float)$pendiente['opened'];?>" >
<input type="hidden" name="fk_cierre" id="fk_cierre" value="<?php echo $control->id;?>">
<input type="hidden" name="fk_facture_source" id="fk_facture_source" value="">
<input type="hidden" name="fk_facture_source_num" id="fk_facture_num" value="">
<input type="hidden" name="fk_facture_source_num" id="fk_facture_source_num" value="">
<input type="hidden" name="feng_codref" id="feng_codref" value="">
<input type="hidden" name="fk_soc_default" id="fk_soc_default" value="<?php echo $cash->fk_soc;?>">
<input type="hidden" name="price_level" id="price_level" value="<?php echo $cliente->price_level;?>">
<input type="hidden" name="options_vendedor" id="options_vendedor" value="<?php echo $_SESSION['uid'];?>">
<input type="hidden" name="options_sucursal" id="options_sucursal" value="<?php echo $warehouse;?>">
<input type="hidden" name="options_facturetype" id="options_facturetype" value="<?php echo $_SESSION['TIPO_DOC'];?>">	
<input type="hidden" name="fk_facture" id="fk_facture" value="<?php echo $fk_facture;?>">	
<input type="hidden" name="multicurrency_tx" id="multicurrency_tx" value="<?php echo $multicurrency_tx;?>">
<input type="hidden" name="tipo" id="tipo" value="<?php echo $_SESSION['TIPO_DOC'];?>">	
<input type="hidden" name="moneda" id="moneda" value="<?php echo $_SESSION['MULTICURRENCY_CODE'];?>">	
<input type="hidden" name="ref_client" id="ref_client" value="">
<input type="hidden" name="default_vat_code" id="default_vat_code" value="08 Tarifa general">
<input type="hidden" name="user_author" id="user_author" value="<?php echo $user->id;?>">
<input type="hidden" name="login_vendedor" id="login_vendedor" value="<?php echo $_SESSION['login'];?>">
<input type="hidden" name="stock_negativo" id="stock_negativo" value="<?php echo $conf->global->STOCK_ALLOW_NEGATIVE_TRANSFER;?>">
<input type="hidden" name="limitar_facturacion" id="limitar_facturacion" value="<?php echo $conf->global->POS_CLIENT_LIMIT;?>">
<input type="hidden" name="limitar_login" id="limitar_login" value="<?php echo $conf->global->POS_FORCE_LOGIN;?>">
<input type="hidden" name="monto_minimo_apartado" id="monto_minimo_apartado" value="<?php echo $conf->global->POS_MIN_MONTO_APARTADO;?>">
<input type="hidden" name="actividad_economica" id="actividad_economica" value="<?php echo $conf->global->FENG_ACTIVIDAD_ECONOMICA_PRINCIPAL;?>">
<input type="hidden" name="cant_attributes" id="cant_attributes" value="0">
<input type="hidden" name="contado" id="contado" value="<?php echo $cliente->id;?>">
<input type="hidden" name="rew_points" id="rew_points" value="0">
<input type="hidden" name="fk_product" id="fk_product" value="0">
<input type="hidden" name="fk_product" id="cantidad" value="1">
<input type="hidden" name="descuento" id="cantidad" value="0">
<input type="hidden" name="servicios" id="servicios" value="<?php echo $conf->global->POS_SERVICES;?>">


    <?php include('tpl/ordenar_mesa.tpl.php') ?>
    <?php include('tpl/registrar_mesa.tpl.php') ?>
    <div class="row"> 
  <div class="grid2">
  <?php
  $mesas = get_mesas();
  foreach($mesas as $v){

  if($v->asignado){
  $mesa_grid2 =  '';
  }else{
  $mesa_grid2 =  '<div class="draggable c_mesa" data-capacidad="'.$v->capacidad.'" data-name="'.$v->name.'" data-description="'.$v->description.'" data-ubicacion="'.$v->ubicacion.'" data-estado="'.$v->estado.'" data-id="'.$v->id.'">'.$v->name.'</div>';
  }
  print '<div id="-'.$v->id.'" class="block" data-id="'.$v->id.'"><span class="mesa_titulo">'.$v->name.'</span>
  <div data-id="'.$v->id.'" class="droppable draggable-dropzone--occupied" title="'.$v->description.' - Capacidad: '.$v->capacidad.' personas">';
  print $mesa_grid2;
  print '</div>';
  print '</div> '; 
  }
  ?>
 
</div>    
  </div> <!--/row-->  

<div class="row cuadro">
<div class="grid">  
<?php

$grid = 63;
for($i=1;$i<=$grid;$i++){
$mesas2 = get_mesas('',' AND position='.$i.'');
$position = $mesas2[0]->position;
if($mesas2[0]->estado == 1){
  $clase_estado = 'ocupada';
  }else{
  $clase_estado = '';
  }
        if($position == $i){
          $mesa_grid =  '<div class="draggable c_mesa '.$clase_estado.'" data-capacidad="'.$mesas2[0]->capacidad.'" data-name="'.$mesas2[0]->name.'" data-description="'.$mesas2[0]->description.'" data-ubicacion="'.$mesas2[0]->ubicacion.'" data-estado="'.$mesas2[0]->estado.'" data-id="'.$mesas2[0]->id.'">'.$mesas2[0]->name.'</div>';
          $clase = "droppable draggable-dropzone--occupied";
        }else{
          $mesa_grid =  '';
          $clase = "droppable";        
        }

print '<div class="block" id="'.$i.'">
        <div class="'.$clase.'">';
        print $mesa_grid;
        print '</div>';
        print '</div>';

}   

?>

    </div>

</div> <!--/row-->    
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

const containers = document.querySelectorAll('.block')

const droppable = new Draggable.Droppable(containers, {
  draggable: '.draggable',
  droppable: '.droppable'
});

//droppable.on(':start', () => console.log('drag:start'));
//droppable.on('droppable:over', () => console.log('droppable:over'));
//droppable.on('droppable:out', () => console.log('droppable:out'));

droppable.on('drag:stop', (e) => {
  source = e.data.source.getAttribute('data-id');
  save_position(source,overId);
      console.log(source,overId);
  });

  droppable.on('drag:over:container', (e) => {
    overId = e.data.overContainer.getAttribute('id');

  });
//droppable.on('draggable:stop', () => console.log('draggable:stop'));
</script>