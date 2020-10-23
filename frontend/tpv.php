<?php
$res=@include("../../main.inc.php");                                   // For root directory
if (! $res) $res=@include("../../../main.inc.php");                // For "custom" directory

//dol_include_once('/pos/frontend/class/mobile_detect.php');
require_once(DOL_DOCUMENT_ROOT."/product/stock/class/entrepot.class.php");
require_once(DOL_DOCUMENT_ROOT."/pos/frontend/include/funciones.php");
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';
require_once(DOL_DOCUMENT_ROOT.'/pos/frontend/class/get_img.class.php');
dol_include_once('/pos/backend/class/pos.class.php');
if ($conf->rewards->enabled) {require_once DOL_DOCUMENT_ROOT.'/rewards/class/rewards.class.php';}
if (!$conf->rewards->enabled) {require_once DOL_DOCUMENT_ROOT.'/pos/backend/class/rewards.class.php';}

global $db, $langs,$conf;
$langs->load("pos@pos");
$langs->load("rewards@rewards");
$langs->load("bills");
$langs->load("companies");

if(empty($_SESSION['login']) || empty($_SESSION['TERMINAL_ID']))
{
	header('location: index.php');
}

$logo = DOL_MAIN_URL_ROOT.'/viewimage.php?modulepart=mycompany&file=logos/thumbs/'.$mysoc->logo_small; 



if(GETPOST('action') == 'abrir_caja'){
//PERTUTA DE CAJA
$control = new ControlCash($db,$_SESSION['TERMINAL_ID']);
$open = $control->get_cash_id_open($_SESSION['uid']);  
  $data = [
    'userid'=>$_SESSION['uid'],
    'type_control'=>1,
    'amount_teoric'=>0,
    'multicurrency_amount_teoric'=>0,    
    'amount_diff'=>0,    
    'amount_reel'=>GETPOST('amount_reel'),
    'multicurrency_amount_reel'=>GETPOST('multicurrency_amount_reel'),  
    'fk_responsable'=>"", 
    'fk_cierre'=>""     
  ];
  $res = $control->create($data);

  if($res){
    header('location: tpv.php');
  }
  }



//APERTURA DE CAJA
$cashid = $_SESSION['TERMINAL_ID'];
$cash = new Cash($db);
$cash->fetch($cashid);
$warehouse = $cash->fk_warehouse;

$bank = New Account($db);


$bank->fetch($cash->fk_paycash);
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
$av_discounts = $cliente->getAvailableDiscounts();
$comercial = [];
$comercials = $cliente->getSalesRepresentatives($user);
foreach($comercials as $c){
$comercial[] = $c['login'];  
};

$correos = $cliente->contact_property_array();
$contactos = $cliente->contact_array_objects();
$pendiente_propal = $cliente->getOutstandingProposals();
$pendiente_commande = $cliente->getOutstandingOrders();

//obteniedo tipo de cambio
$currencyRate = new MultiCurrency($db);
$scambio = $currencyRate->getIdAndTxFromCode($db, 'USD');
$cambio_dolar = 1/$scambio[1];
//fin de obteniedo tipo de cambio
$multicurrency_tx = $scambio[1]; 


$control = new ControlCash($db,$_SESSION['TERMINAL_ID']);
(int)$open = $control->get_cash_id_open($_SESSION['uid']);
if($open > 0){
$control->fetch((int)$open);  
} 

//REWARDS
$rewards = New Rewards($db);
$puntos = $rewards->getCustomerPoints($cliente->id);
if($rewards->getCustomerReward($cliente->id)==1){$inscrito='SI';}else{$inscrito='NO';}
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
        <link rel="stylesheet" href="css/select2.css">
		<link rel='stylesheet'   href='css/main.css' type='text/css' media='all' />
    <link href="teclado_virtual/css/keyboard-dark.min.css" rel="stylesheet">
	</head>
 <body class="fixed-sn mdb-skin">
<?php include 'tpl/menu.tpl.php'; ?>

  <div id="barra_c" class="progress md-progress bg-success" style="position: sty;position: sticky;top: 0;z-index: 99999;display:none">
  <div class="indeterminate"></div></div> 
  <!--/.Double navigation-->
<div id="product_content" class="product_content">
</div>


<?php include('tpl/ndc.tpl.php') ?>
<?php include('tpl/dividir_fac.tpl.php') ?>
 <!--modal cliente-->
 <div class="modal fade" id="modalContactForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header text-center">
        <h4 class="modal-title w-100 font-weight-bold">Nuevo cliente</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body mx-3">
      <div class="md-form col-12 ml-auto">
		<select  class="browser-default custom-select" id="tipo_cedula"  name="tipo_cedula" required>
		<option value="" disabled selected>Seleccione tipo de cédula</option>
		<option value="5062">Nacional</option>
		<option value="5061">Jurídico</option>
		<option value="5063">Extranjero</option>
		</select>
		</div>	

        
    <!-- Grid column -->
    <div class="col-auto">
      <!-- Default input -->
      <label class="sr-only">Cedula</label>
      <div class="input-group mb-2">
        <div class="input-group-prepend">
          <div class="input-group-text"><i class="fas fa-address-card"></i></div>
        </div>
        <input type="text" name="cedula" id="cedula" class="form-control py-0" placeholder="Cedula" required>
      </div>
    </div>
    <!-- Grid column -->

    <!-- Grid column -->
    <div class="col-auto">
      <!-- Default input -->
      <label class="sr-only">nombre</label>
      <div class="input-group mb-2">
        <div class="input-group-prepend">
          <div class="input-group-text"><i class="fas fa-user"></i></div>
        </div>
        <input type="text" name="firstname" id="firstname"  class="form-control py-0"  placeholder="Nombre / empresa" required>
      </div>
    </div>
    <!-- Grid column -->

    <!-- Grid column -->
    <div class="col-auto">
      <!-- Default input -->
      <label class="sr-only">Apellidos</label>
      <div class="input-group mb-2">
        <div class="input-group-prepend">
          <div class="input-group-text"><i class="fas fa-user"></i></div>
        </div>
        <input type="text" name="lastname" id="lastname"  class="form-control py-0"   placeholder="Apellidos / Nombre fantasia" requiered>
      </div>
    </div>
    <!-- Grid column -->
  
       <!-- Grid column -->
       <div class="col-auto">
      <!-- Default input -->
      <label class="sr-only">Correo</label>
      <div class="input-group mb-2">
        <div class="input-group-prepend">
          <div class="input-group-text"><i class="fas fa-envelope"></i></div>
        </div>
        <input type="text" name="email" id="email" class="form-control py-0"   placeholder="Correo" requiered>
      </div>
    </div>
    <!-- Grid column --> 


    <!-- Grid column -->
    <div class="col-auto">
      <!-- Default input -->
      <label class="sr-only">Direccion</label>
      <div class="input-group mb-2">
        <div class="input-group-prepend">
          <div class="input-group-text"><i class="fas fa-map-marked-alt"></i></div>
        </div>
        <input type="text" name="direccion" id="direccion" class="form-control py-0"   placeholder="Direccion" requiered>
      </div>
    </div>
    <!-- Grid column -->



    
    <!-- Grid column -->
    <div class="col-auto">
      <!-- Default input -->
      <label class="sr-only">Telefono</label>
      <div class="input-group mb-2">
        <div class="input-group-prepend">
          <div class="input-group-text"><i class="fas fa-phone-alt"></i></div>
        </div>
        <input type="text" name="phone" id="phone" class="form-control py-0"  placeholder="Telefono" requiered>
      </div>
    </div>
    <!-- Grid column -->


    <div class="md-form col-12 ml-auto">
		<select  class="lugares browser-default custom-select" data-nom="provincia" name="provincia" id="provincia" required>
        <option value="-1" disabled selected>Seleccione provinvia</option>
        <?php 
        		$resql7=$db->query("select * from provincias");

                while ($obj7 = $db->fetch_object($resql7))
                {
                echo '<option value="'.$obj7->id.'">'.$obj7->provincia.'</option>';

                }

        ?>
        </select>
		</div>	


        <div class="md-form col-12 ml-auto">
		<select  class="lugares browser-default custom-select" data-nom="canton" name="canton" id="canton" >
        <option value="-1" disabled selected>Seleccione canton</option>
        </select>       
        </div>
        
         <div class="md-form col-12 ml-auto">
		<select  class="lugares browser-default custom-select" data-nom="distrito" name="distrito" id="distrito" >
        <option value="-1" disabled selected>Seleccione distrito</option>
        </select>       
        </div> 
        
        
        <div class="md-form col-12 ml-auto">
		<select  class="lugares browser-default custom-select" data-nom="barrio" name="barrio" id="barrio" >
        <option value="-1" disabled selected>Seleccione barrio</option>
        </select>       
        </div>



      </div>
      <div class="modal-footer d-flex justify-content-center">
        <input class="btn btn-unique" id="nuevocliente"type="submit" value="Send">
         <!--<i class="fas fa-paper-plane-o ml-1"></i></input>-->
      </div>
    </div>
  </div>
</div>
 <!--/.modal cliente-->
  <!--Main Layout-->

    <div class="container-fluid mt-5">
    <?php 

    if((int)$open > 0){
    include('tpl/venta.tpl.php');  
    }else{
    include('tpl/caja_error.tpl.php');   
    }
     

    

    ?>
    </div>

<?php include('tpl/product.tpl.php') ?>
  <!--Main Layout-->
  <?php include('tpl/cierre_caja.php'); ?>
  
  <?php include('tpl/login.tpl.php'); ?> 

</body>
</html> 
<script>
permisos = <?php echo json_encode($user->rights) ?>;
<?php $form->load_cache_vatrates('"'.$mysoc->country_code.'"'); ?>
vatrates = <?php echo json_encode($form->cache_vatrates) ?>;
</script>
<script type='text/javascript' src='js/jquery.easy-autocomplete.js'></script>
<script type='text/javascript' src='js/bootstrap.js'></script>
<script type='text/javascript' src='js/mdb.min.js'></script>
<script src="js/select2.js"></script>
<script src="sweetalert2/dist/sweetalert2.min.js"></script>
<script src="js/numeral/numeral.js"></script>
<script src="js/numeral/en-au.js"></script>
<script src="js/numeral/es.js"></script>
<script src="js/autocompletado.js"></script>
<script src="js/jquery.inputmask.js"></script>
<script src="ajax/lugares.js"></script>
<script src="js/cedulas.js"></script>
<script src="js/teclado.js"></script>
<script src="js/venta.js"></script>
<script src="js/restaurant.js"></script>
<script src="js/calculadora.js"></script>
<script src="js/nuevocliente.js"></script>
<!-- keyboard widget css & script (required) -->
<script src="teclado_virtual/js/jquery.keyboard.js"></script>
<script src="js/teclado_virtual.js"></script>
<script src="js/login.js"></script>

<script>
// SideNav Button Initialization
$(".button-collapse").sideNav();
// SideNav Scrollbar Initialization
//var sideNavScrollbar = document.querySelector('.custom-scrollbar');
//var ps = new PerfectScrollbar(sideNavScrollbar);

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
                  $("#fk_soc").val($("#select_cliente").getSelectedItemData().id);
                  $("#cliente").html($("#select_cliente").getSelectedItemData().nom);
                  $("#forme_juridique_code").val($("#select_cliente").getSelectedItemData().forme_juridique_code);  
                  $("#idprof1").val($("#select_cliente").getSelectedItemData().siren); 
                  $("#limite_credito").val($("#select_cliente").getSelectedItemData().limite_credito); 
                  $("#credito_usado").val($("#select_cliente").getSelectedItemData().credito_usado);
                  $("#credito_disponible").val($("#select_cliente").getSelectedItemData().credito_disponible);
                  $("#fk_facture").val(0);
                  $("#total_points").val($("#select_cliente").getSelectedItemData().puntos);
                  $("#text_total_points").text(numeral($("#select_cliente").getSelectedItemData().puntos).format('0,0.00'));
                  get_lineas();
                  get_ndc_desc($("#select_cliente").getSelectedItemData().id);
                  $("#eac-container-select_cliente ul").hide();
                  $("#exampleModalLongTitle").html($("#select_cliente").getSelectedItemData().text);
                  $("#price_level").val($("#select_cliente").getSelectedItemData().price_level);

                  get_client_info($("#select_cliente").getSelectedItemData().id);
                  $("#remise_client").val($("#select_cliente").getSelectedItemData().remise_percent);
                  <?php if($conf->global->POS_CLIENT_DESC == 1){ ?>
                  $("#descuento").val($("#select_cliente").getSelectedItemData().remise_percent);                    
                  <?php
                  } ?>

                 

                  $('#options_exoneracion').html('');
                  //$('#options_exoneracion').append('<option></option>');
                  $.each($("#select_cliente").getSelectedItemData().exoneracion, function( index, value ) {
                  $('#options_exoneracion').append( '<option value="'+value.numero_documento+'">'+value.tipo_dococumento+' - '+value.numero_documento+'</option>');
                  })                
          }
      },
    
  };
  
  $("#select_cliente").easyAutocomplete(options);
  //FIN*******AUTOCOMPLETADO

function get_ndc_desc(fk_soc){
//envio por ajax
$.ajax({
  type: "POST",
  url: "ajax/clientes.php",
  data: {
    fk_soc:fk_soc,
    action:'get_ndc_desc'
  },
  dataType: "json",
  success: function(resp) {
  $('#ndc_desc').html('')
  $.each(resp, function( index, value ) {
    if(value.ref_client !=''){ref_cliete = ' - ('+value.ref_client+')';}else{$ref_cliete ='';}
    $('#ndc_desc').append( '<option class="sel_'+value.decuento_id+'" data-amount="'+value.amount_ttc+'" value="'+value.decuento_id+'">'+value.amount_ttc2+ref_cliete+' - ('+value.ndc_facnumber+') -> ('+value.ndc_source_facnumber+')</option>');
  })

  $('#ndc_desc').select2({placeholder: "Aplicar descuentos disponibles"});
  $('#ndc_desc').on('select2:select', function (e) {
  calculos(); 
  });

  $('#ndc_desc').on('select2:unselect', function (e) {
  calculos(); 
});

  }
  
  })
  //envio por ajax
}




$('#ndc_desc').select2({placeholder: "Aplicar descuentos disponibles"});

$('#ndc_desc').on('select2:select', function (e) {
  calculos(); 
});

$('#ndc_desc').on('select2:unselect', function (e) {
  calculos(); 
});

$(document).ready(function() {
  // Show sideNav
  $('.menuiz').sideNav('show');
 
});



</script>