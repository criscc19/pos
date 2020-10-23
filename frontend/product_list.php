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

$sq = 'SELECT e.rowid e_id,e.ref bodega,pc.name,pc.rowid pc_id FROM llx_entrepot e JOIN llx_pos_cash pc ON pc.fk_warehouse=e.rowid';
$sql = $db->query($sq);
$terminales = ' <label>Terminal :<select id="bodega">' ;
while($obj = $db->fetch_object($sq)){
$terminales .= '<option value="'.$obj->e_id.'"';
if($bodega == $obj->e_id){ $terminales .=' " Selected"';}
$terminales .= '>'.$obj->name.'</option>' ;
}
$terminales .= '</select></label>' ;

$limite = $conf->global->PRODUIT_MULTIPRICES_LIMIT;

$niveles = ' <label>Nivel de precio:</span><select name="nivel" id="nivel">';
for ($i = 1; $i <= $limite; $i++) {
    $niveles .= '<option value="'.$i.'"';
    $niveles .=  '>'.$i.'</option>';
}
$niveles .= '</select></label>';

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
        <link rel='stylesheet'   href='datatables/css/datatables.css' type='text/css' media='all' />

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

<table id="dtabla" class="cell-border stripe order-column" style="width:100%"></span>
<thead>
<tr>
<th>Codigo</th>
<th>Producto</th>
<th>Precio</th>
<th>Inventario</th>
</tr>
</thead>
<tbody>
<?php
$sq = '
SELECT p.rowid,p.ref,p.label,p.description,';
$sq .=' (SELECT pp.price_ttc FROM llx_product_price pp WHERE pp.fk_product=p.rowid AND pp.price_level=1 ORDER BY pp.date_price DESC LIMIT 1) precio,';
$sq .= 'if (ps.reel is not null, ps.reel, 0) reel
FROM llx_product p
LEFT JOIN llx_product_stock ps ON ps.fk_product=p.rowid AND ps.fk_entrepot='.$bodega.' LIMIT 150';
$sql = $db->query($sq);
for ($g = 1; $g <= $db->num_rows($sql); $g++) {
$obj = $db->fetch_object($sql);
$precio +=$obj->precio;
print '
<tr>
<td><a href="../product/card.php?id='.$obj->rowid.'">'.$obj->ref.'</a></td>
<td>'.$obj->label.'</td>
<td>₡ '.price($obj->precio).'</td>';
if($obj->reel < 0){
print '<td style="color:#820000"><b>'.$obj->reel.'</b></td>';
}else{print '<td>'.$obj->reel.'</td>';}
print '</tr>';
}
?>
</tbody>
<tfoot>
<tr>
<th></th>
<th></th>
<th>
<th></th>
</tr>
</thead>
</tfoot></table>
    </div>
  <!--Main Layout-->

 </body>
</html> 
<script type='text/javascript' src='js/jquery.easy-autocomplete.js'></script>
<script type='text/javascript' src='js/bootstrap.min.js'></script>
<script type='text/javascript' src='js/mdb.min.js'></script>
<script src="sweetalert2/dist/sweetalert2.min.js"></script>
<script src="datatables/datatables.js"></script>
<script src="datatables/buttons.html5.js"></script>
<script src="datatables/dataTables.buttons.js"></script>
<script src="datatables/dataTables.fixedHeader.js"></script>
<script src="datatables/dataTables.responsive.js"></script>

<script src="js/numeral/numeral.js"></script>

<script>

$(document).ready(function () {

               tabla = $("#dtabla").on("preInit.dt", function(){

                 $("#dtabla_wrapper input[type='search']").after("<button class='btn btn-primary btn-sm' type='button' id='btn_search'>Buscar</button>");
                 $('#dtabla_filter').append('<?php echo $terminales ?>');
                 $('#dtabla_filter').append('<?php echo $niveles ?>');
                });

$('#dtabla').DataTable({
    "processing": true,
     "serverSide": true,
     "initComplete":function(){onint();},
     //dom: 'lfrtip',
     dom: 'lBfrtip',//definimos los elementos del control de la tabla 
     "ajax":{
                    url: "ajax/product_list.php",
                    type:"GET",
                  data:function(dtp){
                    bodega = $('#bodega').val();
                    nivel = $('#nivel').val();
                    // change the return value to what your server is expecting
                    // here is the path to the search value in the textbox
                    dtp.search.bodega = bodega;
                    dtp.search.nivel = nivel;
                    var searchValue = dtp.search.value;
                    
                    return dtp;}
                },
    fixedHeader: true,
    lengthMenu: [10, 25, 50, 100, 500, 1000, 10000, 100000],
    "columns": [
   
    { "data": "codigo" },
    { "data": "producto" },
    { "data": "precio" },
    { "data": "inventario" },
    
    ],
//importacion
buttons: [ 
            { extend: 'copyHtml5', footer: true,orientation: 'landscape',pageSize: 'A4', exportOptions:{columns: [ 0, ':visible' ]}},
            { extend: 'excelHtml5', footer: true, exportOptions:{columns: [ 0, ':visible' ]} },
            { extend: 'csvHtml5', footer: true, exportOptions:{columns: [ 0, ':visible' ]} },
            { extend: 'pdfHtml5', footer: true,pageSize: 'A4',exportOptions:{columns: [ 0, ':visible' ]},
                customize: function(doc) {
                    doc.content[1].table.widths = 
        Array(doc.content[1].table.body[0].length + 1).join('*').split('');
                    }
            
            },
            'colvis'
        ],
            
//importacion

    'language':{
	'sProcessing':     'Procesando...',
	'sLengthMenu':     'Mostrar _MENU_ registros',
	'sZeroRecords':    'No se encontraron resultados',
	'sEmptyTable':     'Ningún dato disponible en esta tabla',
	'sInfo':           'Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros',
	'sInfoEmpty':      'Mostrando registros del 0 al 0 de un total de 0 registros',
	'sInfoFiltered':   '(filtrado de un total de _MAX_ registros)',
	'sInfoPostFix':    '',
	'sSearch':         'Buscar:',
	'sUrl':            '',
	'sInfoThousands':  ',',
	'sLoadingRecords': 'Cargando...',
	'oPaginate': {
		'sFirst':    'Primero',
		'sLast':     'Último',
		'sNext':     'Siguiente',
		'sPrevious': 'Anterior'
	},
	'oAria': {
		'sSortAscending':  ': Activar para ordenar la columna de manera ascendente',
		'sSortDescending': ': Activar para ordenar la columna de manera descendente'
	}
},    
    
});

   // this function is used to intialize the event handlers
   function onint(){
     // take off all events from the searchfield
     $("#dtabla_wrapper input[type='search']").off();
     // Use return key to trigger search
     $("#dtabla_wrapper input[type='search']").on("keydown", function(evt){
          if(evt.keyCode == 13){
            $("#dtabla").DataTable().search($("input[type='search']").val()).draw();
          }
     });
     $("#btn_search").button().on("click", function(){
           $("#dtabla").DataTable().search($("input[type='search']").val()).draw();

     });
   }

});




    

$(".button-collapse").sideNav();
$("#selectaccountid").addClass('browser-default custom-select');
$("#selectpaiementcode").addClass('browser-default custom-select');


</script>