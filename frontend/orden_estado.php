<?php
$res=@include("../../master.inc.php");                                   // For root directory
if (! $res) $res=@include("../../../master.inc.php");                // For "custom" directory

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
require_once(DOL_DOCUMENT_ROOT.'/product/class/product.class.php');
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
require_once(DOL_DOCUMENT_ROOT.'/pos/backend/class/cash.class.php');
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
include_once DOL_DOCUMENT_ROOT .'/core/lib/images.lib.php';
include_once DOL_DOCUMENT_ROOT .'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/variants/class/ProductCombination.class.php';
if(DOL_VERSION < 11){
	$facnumber = 'facnumber';
	}else{
	$facnumber = 'ref';  
	}	
$id = GETPOST('id');
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
        <link rel='stylesheet'   href='css/pro/mdb-carousel-3d.min.css' type='text/css' media='all' />    		
        <link rel="stylesheet" href="sweetalert2/dist/sweetalert2.min.css">
    <link rel='stylesheet'   href='css/main.css' type='text/css' media='all' />
    <link rel='stylesheet'   href='css/restaurant.css' type='text/css' media='all' />


	</head>
 <body class="fixed-sn mdb-skin">
<?php //include 'tpl/menu_restaurant.tpl.php'; ?>
<ul class="nav nav-tabs menu_menu">
  <li class="nav-item">
    <a class="nav-link" href="orden_client.php?id=<?php echo $id ?>">Ordenar</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="orden_resume.php?id=<?php echo $id ?>">Validar</a>
  </li>
  <li class="nav-item">
    <a class="nav-link active" href="orden_estado.php?id=<?php echo $id ?>">Estado</a>
  </li>

</ul>
  <div id="barra_c" class="progress md-progress bg-success" style="position: sty;position: sticky;top: 0;z-index: 99999;display:none">
  <div class="indeterminate"></div></div> 
  <!--/.Double navigation-->
</div>

  <!--Main Layout-->
  <!--<br><br>-->
<div class="container-fluid">
<div class="container-fluid table-responsive">        
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


<div class="row"> 
<!-- menu -->
<div class="menu app-section">
		<div class="container">
			<div class="app-title">
				<h1 class="app-title2">Detalle de la orden</h1>
				<ul class="line">
					<li><i class="fa fa-snowflake-o"></i></li>
					<li class="line-center"><i class="fa fa-snowflake-o"></i></li>
					<li><i class="fa fa-snowflake-o"></i></li>
				</ul>
			</div>
			<div class="content">
            <div class="container">

 <!--/ carrucel cat-->
<div id="t1">
<div class="row" id="menu_product">

<!-- <div class="col-md3">
    <div class="entry">
        <img src="img/pizza1.png" alt="">
        <h6><a href="">Pizza Title</a></h6>
        <div class="rating">
            <span class="active"><i class="fa fa-star" aria-hidden="true"></i></span>
            <span class="active"><i class="fa fa-star" aria-hidden="true"></i></span>
            <span class="active"><i class="fa fa-star" aria-hidden="true"></i></span>
            <span class="active"><i class="fa fa-star" aria-hidden="true"></i></span>
            <span class=""><i class="fa fa-star" aria-hidden="true"></i></span>
        </div>
        <div class="price">
            <h5>$28</h5>
        </div>
        <button class="button">ADD TO CART</button>
    </div>
</div>
 -->



</div>
</div>
	<!-- end menu -->

</div> <!--/row--> 
</div><!--/container fluid-->      
</div><!--/container fluid-->    

  <!--Main Layout-->

</body>
</html> 
<script type='text/javascript' src='js/jquery.easy-autocomplete.js'></script>
<script type='text/javascript' src='js/bootstrap.min.js'></script>
<script type='text/javascript' src='js/mdb.min.js'></script>
<script type='text/javascript' src='js/mdb-carousel-3d.min.js'></script>
<script src="sweetalert2/dist/sweetalert2.min.js"></script>
<script src="js/numeral/numeral.js"></script>
<script src="js/venta.js"></script>
<script src="js/restaurant.js"></script>
<script>
$(".button-collapse").sideNav();

</script>
<script>
    $(function() {
      $('.carousel-3d-basic').mdbCarousel3d();
      $('.carousel-3d-controls').mdbCarousel3d();
      $('.carousel-3d-vertical').mdbCarousel3d({
        vertical: true
      });
      $('.carousel-3d-autoplay-off').mdbCarousel3d({
        autoplay: false
      });
    });
  </script>
<script>
$('.cat').click(get_product_cate);
$('#categorias').click(get_restaurant_cat);



function get_product_cate(){
fk_categorie = $(this).data('id');
fk_parent = $(this).data('fk_parent');
levels = $(this).data('levels');
      //envio por ajax
      $.ajax({
        type: "POST",
        url: "ajax/productos2.php",
        data: {
       
          action:'get_categorias',
          m : 'CRC',
          l : '1',
          cat_id : fk_categorie
  
        },
        dataType: "json",
        success: function(resp) { 

        render_product('#menu_product',resp)

        get_restaurant_subcat(fk_categorie)
  
        
         }
              })
        //envio por ajax  
}




function render_product(element_id,data){
html = '';
$.each(data, function( index, value ) {
html +='<div class="col-md-2 p_lista"  data-fk_product="'+value.id+'" data-entrepot_stock="'+value.Stock+'" data-max_discount="'+value.options_descuento+'">'+
    '<div class="entry">'+
        '<img src="'+value.image+'" width="100%">'+
        '<h6><a href="">'+value.label+'</a></h6>'+
        '<div class="rating">'+
            '<span class="active"><i class="fa fa-star" aria-hidden="true"></i></span>'+
            '<span class="active"><i class="fa fa-star" aria-hidden="true"></i></span>'+
            '<span class="active"><i class="fa fa-star" aria-hidden="true"></i></span>'+
            '<span class="active"><i class="fa fa-star" aria-hidden="true"></i></span>'+
            '<span class=""><i class="fa fa-star" aria-hidden="true"></i></span>'+
        '</div>'+
        '<div class="price">'+
            '<h5>'+value.precio+'</h5>'+
        '</div>'+
        '<button class="button">Agregar a la orden</button>'+
    '</div>'+
'</div>';    
})
                
$(element_id).html(html);

}


function get_restaurant_subcat(fk_parent){
 //envio por ajax
 $.ajax({
  type: "POST",
  url: "ajax/categorias2.php",
  data: {   action:'getCategories',parentcategory:fk_parent},
  dataType: "json",
  success: function(resp) {
render_categorie('#carousel1',resp)
  }
  })
  //envio por ajax   
   }

function get_restaurant_cat(){
 //envio por ajax
 $.ajax({
  type: "POST",
  url: "ajax/categorias2.php",
  data: {   action:'getCategories',parentcategory:0},
  dataType: "json",
  success: function(resp) {
render_categorie('#carousel1',resp)
  }
  })
  //envio por ajax   
   }
   


function render_categorie(element_id,data){
    $(element_id).html('');
control = ''
c = 0;
 $.each(data, function( index, value ) {
    if(c ==0){
        active = 'active';
        }else{
        active = '';    
        }    
        control +='<li data-target="#multi-item-example" data-slide-to="'+c+'" class="'+active+'"></li>';
c++;
 })
$('#indicators').html(control);

html = '';
a = 1;
 $.each(data, function( index, value ) {
if(a ==1){
active = 'active';
}else{
active = '';    
}

html += '<div class="carousel-item '+active+'">';

$.each(value, function( index2, value2 ) {

    html += '<div class="col-md-4">';
      html += '<div data-id="'+value2.id+'"  data-levels="'+value2.levels+'" data-fk_parent="'+value2.fk_parent+'" class="cat card mb-2 carroucel_item" style="background-image: url('+value2.thumb+');">';
        html += '<!-- <img class="card-img-top" src="<?php echo $cat2['thumb'] ?>"> -->';
        html += '<div class="card-body" style="display:flex">';
          html += '<h4 class="card-title">'+value2.label+'</h4>';
          html += '<p class="card-text"></p>';
         html += '</div>';
      html += '</div>';
    html += '</div>';



  
  })  
  html += '</div>';   
a++;
      }) 
$(element_id).html(html);
$('.cat').click(get_product_cate);
$('#categorias').click(get_restaurant_cat);
}
</script>
