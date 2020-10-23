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

$sq ='SELECT f.rowid f_id,s.rowid s_id,rm.rowid m_id,u.rowid u_id, u.firstname,u.lastname,u.login,s.nom,s.name_alias,
 f.ref_client,f.type,f.facnumber,(SELECT SUM(fd.total_ttc) FROM llx_facturedet_cashdespro fd WHERE fd.fk_facture=f.rowid) total_ttc,
  (SELECT SUM(fd.multicurrency_total_ttc) FROM llx_facturedet_cashdespro fd WHERE fd.fk_facture=f.rowid) multicurrency_total_ttc, 
  f.multicurrency_code,f.tms,rm.name mesa,f.multicurrency_code,f.fk_statut
  FROM llx_facture_cashdespro f 
  JOIN llx_societe s ON f.fk_soc=s.rowid 
  LEFT JOIN llx_pos_restaurant_mesas rm ON f.fk_mesa=rm.rowid 
  JOIN llx_user u ON f.fk_user_author=u.rowid 
  WHERE fk_mesa='.$id.'
  ORDER BY f.rowid DESC';
$sql = $db->query($sq);
$ordenes = [];

while($obj = $db->fetch_object($sql)){
$orden = new stdClass();  
  if($obj->type==0){$tipo='FACTURA';$color = 'alert-primary';}
  if($obj->type==1){$tipo='TICKET';$color = 'alert-success';}
  if($obj->type==2){$tipo='NDC';$color = 'alert-danger';}
  if($obj->type==3){$tipo='APARTADO';$color = 'alert-secondary';}
  if($obj->type==4){$tipo='CONTIZACION';$color = 'alert-warning';}
  $orden->f_id = $obj->f_id;
  $orden->s_id = $obj->s_id;
  $orden->u_id = $obj->u_id;
  $orden->m_id = $obj->m_id;  
  $orden->login = $obj->login;
  $orden->firstname = $obj->firstname;
  $orden->lastname = $obj->lastname;
  $orden->nom = $obj->nom;
  $orden->name_alias = $obj->name_alias;
  $orden->ref_client = $obj->ref_client;
  $orden->mesa = $obj->mesa;
  $orden->facnumber = $obj->facnumber;
  $orden->total_ttc = (float)$obj->total_ttc;
  $orden->multicurrency_total_ttc = (float)$obj->multicurrency_total_ttc;
  $orden->multicurrency_code = $obj->multicurrency_code;
  $orden->fecha = date('H:i:s',strtotime($obj->tms));
  $orden->type = $obj->type;
  $orden->color = $color;
  $orden->estado = $obj->fk_statut;
  $ordenes[] = $orden;
  }
$orden = $ordenes[0];

$mesa = get_mesas('',' AND rowid='.$id.'');

if($conf->global->POS_MONEDA_GLOBAL=='CRC'){$signo = '₡';$color1='black';$color1_2='#dcdcdc';}
if($conf->global->POS_MONEDA_GLOBAL=='USD'){$signo = '$';$color1='green';$color1_2='#d7fbd7';}

?>

<!DOCTYPE html>
<html lang="es">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" type="image/png" href="img/favicon.png"/>
		<title>Restaurante - Bienvenido</title>

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
<?php
if((int)$orden->f_id <= 0){
  print '<input type="hidden" name="fk_soc" id="fk_soc" value="'.$conf->global->POS_CLIENTE_GLOBAL.'">
  <input type="hidden" name="price_level" id="price_level" value="'.$conf->global->POS_NIVEL_PRECIO_GLOBAL.'">
  <input type="hidden" name="fk_mesa" id="fk_mesa" value="'.$id.'">
  <input type="hidden" name="fk_facture" id="fk_facture" value="0">	
  <input type="hidden" name="moneda" id="moneda" value="'.$conf->global->POS_MONEDA_GLOBAL.'">	
  <input type="hidden" name="cantidad" id="cantidad" value="1">
  <input type="hidden" name="tipo" id="tipo" value="'.$conf->global->POS_TIPO_DOC_GLOBAL.'">  
  <input type="hidden" name="fk_vendedor" id="fk_vendedor" value="'.$conf->global->POS_VENDEDOR_GLOBAL.'">  
  <input type="hidden" name="descuento" id="descuento" value="0">';
  
print '<div class="alert alert-danger" role="alert" style="">
Lo sentimos su mesa no ha sido reservada. porfavor incie su pedido Aqui:<br>
Escriba su nombre o un alias (opcional)<input type="text" id="ref_client" name="ref_client">
<button class="btn btn-primary btn_restaurant" id="iniciar">Iniciar Orden</button>
</div>';
  
}else{
 
 ?>
<ul class="nav nav-tabs menu_menu">
  <li class="nav-item">
    <a class="nav-link active" href="orden_client.php?id=<?php echo $id ?>">Ordenar</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="orden_resume.php?id=<?php echo $id ?>">Validar</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="orden_estado.php?id=<?php echo $id ?>">Estado</a>
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
<input type="hidden" name="fk_soc" id="fk_soc" value="<?php echo $orden->s_id;?>">
<input type="hidden" name="price_level" id="price_level" value="1">
<input type="hidden" name="fk_product" id="fk_product" value="">
<input type="hidden" name="fk_mesa" id="fk_mesa" value="<?php echo $id;?>">
<input type="hidden" name="fk_facture" id="fk_facture" value="<?php echo $orden->f_id;?>">	
<input type="hidden" name="moneda" id="moneda" value="<?php echo $orden->multicurrency_code;?>">	
<input type="hidden" name="cantidad" id="cantidad" value="1">
<input type="hidden" name="descuento" id="descuento" value="0">
<input type="hidden" name="tipo_menu" id="tipo_menu" value="list">
<div class="row"> 
<!-- menu -->
<div class="menu app-section">
		<div class="container">
			<div class="app-title">
				<h1 class="app-title2">Menu Especial</h1>
				<ul class="line">
					<li><i class="fa fa-snowflake-o"></i></li>
					<li class="line-center"><i class="fa fa-snowflake-o"></i></li>
					<li><i class="fa fa-snowflake-o"></i></li>
				</ul>
			</div>
			<div class="content">
    
  <!--<div class="container">
 <!-- carrucel              
  <div class="carousel-3d carousel-3d-controls">
    <div class="carousel-3d-inner">
      <div class="carousel-3d-item"><img src="https://mdbootstrap.com/img/Photos/Slides/img%20(46).jpg" alt="Slide"></div>
      <div class="carousel-3d-item"><img src="https://mdbootstrap.com/img/Photos/Slides/img%20(45).jpg" alt="Slide"></div>
      <div class="carousel-3d-item"><img src="https://mdbootstrap.com/img/Photos/Slides/img%20(47).jpg" alt="Slide"></div>
      <div class="carousel-3d-item"><img src="https://mdbootstrap.com/img/Photos/Slides/img%20(48).jpg" alt="Slide"></div>
      <div class="carousel-3d-item"><img src="https://mdbootstrap.com/img/Photos/Slides/img%20(49).jpg" alt="Slide"></div>
      <div class="carousel-3d-item"><img src="https://mdbootstrap.com/img/Photos/Slides/img%20(50).jpg" alt="Slide"></div>
      <div class="carousel-3d-item"><img src="https://mdbootstrap.com/img/Photos/Slides/img%20(51).jpg" alt="Slide"></div>
      <div class="carousel-3d-item"><img src="https://mdbootstrap.com/img/Photos/Slides/img%20(52).jpg" alt="Slide"></div>
      <div class="carousel-3d-item"><img src="https://mdbootstrap.com/img/Photos/Slides/img%20(53).jpg" alt="Slide"></div>
    </div>
    <div class="carousel-3d-controls">
      <a class="prev-btn waves-effect waves-light"><i class="fas fa-chevron-left"></i></a>
      <a class="next-btn waves-effect waves-light"><i class="fas fa-chevron-right"></i></a>
    </div>
  </div>
</div> --> 
 <!--/ carrucel -->

 <!-- carrucel cat-->
 <!--Carousel Wrapper-->
<div id="multi-item-example" class="carousel slide carousel-multi-item" data-ride="carousel" data-interval="0">

<!--Controls-->
<div class="controls-top"></i><span class="btn-info btn_restaurant" style="padding:10px" id="categorias">Categorias</span>
  <a class="btn-floating de btn_restaurant" href="#multi-item-example" data-slide="prev"><i class="fas fa-chevron-left"></i></a>
  <a class="btn-floating iz btn_restaurant" href="#multi-item-example" data-slide="next"><i
      class="fas fa-chevron-right"></i></a>
</div>
<!--/.Controls-->

<!--Indicators-->
<!--<ol class="carousel-indicators" id="indicators">
<?php
    $cates = get_categories(); 
    $cate_div = array_chunk($cates,3);
    $c = 0;
    $a = 1;
foreach($cate_div as $cat){
    if($c ==0){
        $active = 'active';
        }else{
        $active = '';    
        }
?>    
  <li class="btn_restaurant" data-target="#multi-item-example" data-slide-to="<?php echo $c ?>" class="<?php echo $active ?>"></li>

<?php
$c++;
}
?>
</ol>-->
<!--/.Indicators-->

<!--Slides-->
<div class="carousel-inner" role="listbox" id="carousel1">
<?php

foreach($cate_div as $cat){
if($a ==1){
$active = 'active';
}else{
$active = '';    
}
?>

  <!--First slide-->
  <div class="carousel-item <?php echo $active ?>">
<?php

foreach($cat as $cat2){
?>
    <div class="col-md-4 item_cat">
      <div data-id="<?php echo $cat2['id'] ?>"  data-levels="<?php echo $cat2['levels'] ?>" data-fk_parent="<?php echo $cat2['fk_parent'] ?>" class="cat card mb-2 carroucel_item" style="background-image: url(<?php echo $cat2['thumb'] ?>);">
        <!-- <img class="card-img-top" src="<?php echo $cat2['thumb'] ?>"> -->
        <div class="card-body" style="display:flex">
          <h4 class="card-title"><?php echo $cat2['label'] ?></h4>
          <p class="card-text"><?php echo $cat2->decription ?></p>
         </div>
      </div>
    </div>


  <?php 
  }   
  ?>
  </div>
  <!--/.First slide-->
  <?php 
  $a++;
  }   
  ?>

</div>
<!--/.Slides-->

</div>
<!--/.Carousel Wrapper-->
 <!--/ carrucel cat-->
<div id="t1">
<div>
<div id="btn_rest_grid" class="float-right menu_tipo" data-tipo="grid"><i class="fas fa-th-large fa-3x"></i> </div>
<div id="btn_rest_list"  class="float-left menu_tipo" data-tipo="list"><i class="fas fa-th-list fa-3x"></i></div>
<div style="clear:both">
</div>
<br>
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
<?php
}
?>
<a class="nav-link" href="orden_resume.php?id=<?php echo $id ?>">
<div class="carrito">
<div class="cart">
<button type="button" class="btn btn-outline-secondary waves-effect px-3"><i class="fas fa-utensils"></i></button>
<span class="counter" id="cart_cant">0</span>
</div></div></a>

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
        if($('#tipo_menu').val()=='list'){
          render_product_list('#menu_product',resp)
        }
        if($('#tipo_menu').val()=='grid'){
          render_product('#menu_product',resp)
        }        

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
        '<div class="titulo4">'+value.description+'</div>'+        
        '<div class="price">'+
            '<h5  style="color:<?php echo $color1 ?>"> <?php echo $signo ?> '+value.precio+'</h5>'+
        '</div>'+
<?php if($orden->estado == 0){ ?> '<button class="btn btn-sm waves-effect waves-light add_product btn_restaurant" data-fk_product="'+value.id+'">Agregar a la orden</button>'+<?php } ?>
    '</div>'+
'</div>';    
})
                
$(element_id).html(html);
$('.add_product').click(function(){
id = $(this).data('fk_product');
$('#fk_product').val(id);
insert_line_restaurant();
})
}


function render_product_list(element_id,data){
html = '';
html +='<ul class="list-group list-group-flush" style=" width: 100%;text-align: left;">';
$.each(data, function( index, value ) {
html +='<li class="p_lista list-group-item"  data-fk_product="'+value.id+'" data-entrepot_stock="'+value.Stock+'" data-max_discount="'+value.options_descuento+'">'+
        '<span class="titulo3">'+value.label+'</span>'+' <span class="titulo4">'+value.description+'<span><br>'+
        '<span class="titulo5" style="color:<?php echo $color1 ?>"> <?php echo $signo ?> '+numeral(value.precio).format('0,0.00')+'<span>'+
<?php if($orden->estado == 0){ ?> '<button class="btn btn-sm waves-effect waves-light add_product btn_restaurant" data-fk_product="'+value.id+'">Agregar a la orden</button>'+<?php } ?>
    ''+
'</li>';    
})
html +='</ul>';            
$(element_id).html(html);
$('.add_product').click(function(){
id = $(this).data('fk_product');
$('#fk_product').val(id);
insert_line_restaurant();
})
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

    html += '<div class="col-md-4 item_cat">';
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


function insert_line_restaurant(){
  fk_product = $('#fk_product').val();
  cantidad = parseFloat($('#cantidad').val());
  descuento = parseFloat($('#descuento').val());
  tipo_desc = $('#tipo_desc').val();
  price_level = $('#price_level').val();
  moneda = $('#moneda').val();
  fk_soc = $('#fk_soc').val();
  tipo = $('#tipo').val();
  fk_facture = $('#fk_facture').val();
  select_product = $('#select_product').val();
  max_discount = parseFloat($("#max_discount").val());
  entrepot_stock = parseFloat($("#entrepot_stock").val());
  stock_negativo = parseInt($("#stock_negativo").val());
  limitar_login = parseInt($("#limitar_login").val());  
//envio por ajax
$.ajax({
    type: "POST",
    url: "ajax/add_line_restaurant.php",
    data: {
      fk_product:fk_product,
      descuento:descuento,
      tipo_desc:tipo_desc,
      cantidad:cantidad,
      price_level:price_level,
      moneda:moneda,
      fk_soc:fk_soc,
      fk_facture:fk_facture,
      tipo:tipo,
      select_product:select_product,
      action:'add_line'
    },
    dataType: "json",
    success: function(resp) {
    toastr.success('Se a incluido en su orden.');
    counter();
    }
    
    })
    //envio por ajax  
}

$('#iniciar').click(save_fac_restaurant);




function save_fac_restaurant(){
  $('#barra_c').show(); 
  fk_facture = $('#fk_facture').val();
  fk_soc = $('#fk_soc').val();
  moneda = $('#moneda').val();
  tipo = $('#tipo').val();
  fk_mesa = $('#fk_mesa').val();
  ref_client = $('#ref_client').val();
 //envio por ajax
 $.ajax({
  type: "POST",
  url: "ajax/add_line_restaurant.php",
  data: {
    fk_soc:fk_soc,
    fk_facture:fk_facture,
    moneda:moneda,
    ref_client:ref_client,
    fk_mesa:fk_mesa,
    tipo:tipo,
    action:'save_fac'
  },
  dataType: "json",
  success: function(resp) {
  location.reload();
  $('#barra_c').hide();
  }
  
  })
  //envio por ajax

}
counter();

function counter(){
//envio por ajax
$.ajax({
    type: "POST",
    url: "ajax/add_line_restaurant.php",
    data: {
      fk_mesa:<?php echo $id ?>,
      action:'counter'
    },
    dataType: "json",
    success: function(resp) {
    $('#cart_cant').html(resp);
    console.log(resp)
    }
    
    })
    //envio por ajax    
}


$('.menu_tipo').click(switch_menu);

function switch_menu(){
tipo = $(this).data('tipo');
$('.tipo_activo').removeClass('tipo_activo');
if(tipo == 'grid'){
$('#tipo_menu').val('grid');
}
if(tipo == 'list'){
$('#tipo_menu').val('list');

}
$(this).addClass('tipo_activo');
}
</script>
