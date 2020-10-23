<?php
$res=@include("../../master.inc.php");                                   // For root directory
if (! $res) $res=@include("../../../master.inc.php");                // For "custom" directory

dol_include_once('/pos/backend/class/pos.class.php');
//dol_include_once('/pos/frontend/class/mobile_detect.php');
require_once(DOL_DOCUMENT_ROOT.'/pos/frontend/class/cashdespro_facture.class.php');
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
require_once(DOL_DOCUMENT_ROOT.'/pos/frontend/class/get_img.class.php');
if(DOL_VERSION < 11){
	$facnumber = 'facnumber';
	}else{
	$facnumber = 'ref';  
	}	
$id = GETPOST('id');

if(GETPOST('action')=='validar'){
  $fac = new Facture_cashdespro($db);
  $fac->fetch(GETPOST('fk_facture'));
  $res = $fac->validate();
  
}


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
<?php 
if($mesa[0]->estado == 0){
print '<div class="alert alert-danger" role="alert">
Lo sentimos su  no ha sido reservada. porfavor solicite la reservación
</div>';    
}else{ ?>

<ul class="nav nav-tabs menu_menu">
  <li class="nav-item">
    <a class="nav-link" href="orden_client.php?id=<?php echo $id ?>">Ordenar</a>
  </li>
  <li class="nav-item">
    <a class="nav-link active" href="orden_resume.php?id=<?php echo $id ?>">Validar</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="orden_estado.php?id=<?php echo $id ?>">Estado de la orden</a>
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
        
<?php
 print '<input type="hidden" name="fk_soc" id="fk_soc" value="'.$conf->global->POS_VENDEDOR_GLOBAL.'">
 <input type="hidden" name="price_level" id="price_level" value="'.$conf->global->POS_NIVEL_PRECIO_GLOBAL.'">
 <input type="hidden" name="fk_mesa" id="fk_mesa" value="'.$id.'">
 <input type="hidden" name="fk_facture" id="fk_facture" value="0">	
 <input type="hidden" name="moneda" id="moneda" value="'.$conf->global->POS_MONEDA_GLOBAL.'">	
 <input type="hidden" name="cantidad" id="cantidad" value="1">
 <input type="hidden" name="tipo" id="tipo" value="'.$conf->global->POS_TIPO_DOC_GLOBAL.'">  
 <input type="hidden" name="fk_vendedor" id="fk_vendedor" value="'.$conf->global->POS_VENDEDOR_GLOBAL.'">  
 <input type="hidden" name="descuento" id="descuento" value="0">';
 if($orden->estado > 0){   
print '<div class="alert alert-warning" role="alert" style="">
Su orden a sido cerrada, si desea perdir adicinales solicite una nueva orden:<br>
Escriba su nombre o un alias (opcional)<input type="text" id="ref_client" name="ref_client">
<button class="btn btn-primary" id="iniciar">Iniciar Orden</button>
</div>';
} ?>
  <!-- Section cart -->
  <section class="section my-5 pb-5">

<div class="card card-ecommerce">

  <div class="card-body">

    <!-- Shopping Cart table -->
    <div class="table-responsive">

      <table class="table product-table table-cart-v-2">

        <!-- Table head -->
        <thead class="mdb-color lighten-5">

          <tr>

            <th></th>

            <th class="font-weight-bold">

              <strong>Platillo</strong>

            </th>


            <th class="font-weight-bold">

              <strong>Precio</strong>

            </th>

            <th class="font-weight-bold">

              <strong>Cantidad</strong>

            </th>


            <th></th>
            <th></th>
          </tr>

        </thead>
        <!-- Table head -->

        <!-- Table body -->
        <tbody>
<?php
$sq = 'SELECT fd.rowid,fd.fk_facture,fd.fk_product,fd.label,fd.description,fd.tva_tx,fd.total_tva,fd.subprice,fd.total_ht,
fd.total_localtax1,fd.total_ttc,fd.remise_percent,fd.multicurrency_code,fd.multicurrency_subprice,p.ref p_ref,p.label p_label,
fd.multicurrency_total_ht,fd.multicurrency_total_ttc,fd.multicurrency_total_tva,p.ref p_ref,p.label,fd.qty 
FROM llx_facturedet_cashdespro fd
LEFT JOIN llx_product p ON fd.fk_product=p.rowid'; 
$sq .= ' WHERE fk_soc = '.$orden->s_id.' AND fk_vendedor = '.$orden->u_id.' AND fk_facture = '.$orden->f_id.'';
$sql = $db->query($sq);
while($obj = $db->fetch_object($sql)){
  $product = New Product($db);
  $product->fetch($obj->fk_product);
  $images = new imagenes($db);
  $imgs = $images->productImage(0,$product->id);
  
?>
          <!-- First row -->
          <tr>

            <th scope="row">

              <img src="<?php echo $imgs->share_phath ?>" alt=""
                class="img-fluid z-depth-0">

            </th>

            <td>

              <h5 class="mt-3">

                <strong><?php echo $obj->p_label ?></strong>

              </h5>

              <p class="text-muted"><?php echo $obj->p_ref ?></p>

            </td>


            <td><?php echo price($obj->total_ht); ?></td>

            <td class="text-center text-md-left">

              <span class="qty" id="qty_<?php echo $obj->rowid; ?>"><?php echo $obj->qty; ?></span>
              <?php if($orden->estado == 0){ ?>
              <div class="btn-group radio-group ml-2" data-toggle="buttons">

                <label  
                data-id="<?php echo $obj->rowid; ?>" 
              data-descuento="<?php echo $obj->remise_percent; ?>" 
              data-fk_product="<?php echo $obj->fk_product; ?>""
              data-fk_soc="<?php echo $orden->s_id; ?>" 
              data-fk_facture="<?php echo $orden->f_id; ?>"
              data-tipo_desc="PC"         
              data-price_level="1" 
              data-price_base="<?php echo $product->multiprices_base_type[1]; ?>"           
              data-tva_tx="<?php echo $obj->tva_tx; ?>" 
              data-precio="<?php echo $obj->subprice; ?>"        
                
                class="menos btn btn-sm btn-primary btn-rounded">

                  <input type="radio" class="" name="options" id="option1_<?php echo $obj->rowid; ?>">&mdash;

                </label>

                <label
                data-id="<?php echo $obj->rowid; ?>" 
              data-descuento="<?php echo $obj->remise_percent; ?>" 
              data-fk_product='<?php echo $obj->fk_product; ?>' 
              data-fk_soc="<?php echo $orden->s_id; ?>" 
              data-fk_facture="<?php echo $orden->f_id; ?>"
              data-tipo_desc="PC"         
              data-price_level="1" 
              data-price_base="<?php echo $product->multiprices_base_type[1]; ?>"           
              data-tva_tx="<?php echo $obj->tva_tx; ?>" 
              data-precio="<?php echo $obj->subprice; ?>"        
                  
                  class="mas btn btn-sm btn-primary btn-rounded">

                  <input type="radio" name="options" id="option2_<?php echo $obj->rowid; ?>">+

                </label>

              </div>
              <?php } ?>
            </td>

            <td class="font-weight-bold">
            <?php if($orden->estado == 0){ ?>
              <button 
              data-id="<?php echo $obj->rowid; ?>" 
              data-descuento="<?php echo $obj->remise_percent; ?>" 
              data-fk_product='<?php echo $obj->fk_product; ?>' 
              data-fk_soc="<?php echo $orden->s_id; ?>" 
              data-fk_facture="<?php echo $orden->f_id; ?>"
              data-tipo_desc="PC"         
              data-price_level="1" 
              data-price_base="<?php echo $product->multiprices_base_type[1]; ?>"           
              data-tva_tx="<?php echo $obj->tva_tx; ?>" 
              data-precio="<?php echo $obj->subprice; ?>"                   
              type="button" class="delete btn btn-sm btn-danger" data-toggle="tooltip" data-placement="top"
              title="Remove item">X

              </button>
            <?php } ?>
            </td>
            <td><?php echo $estado ?></td>
          </tr>
          <!-- First row -->

<?php
}
?>

 <!-- ORDENES ANTERIORES -->


<?php
foreach($ordenes as $v){
if($v->f_id !=$orden->f_id){
$sq = 'SELECT fd.rowid,fd.fk_facture,fd.fk_product,fd.label,fd.description,fd.tva_tx,fd.total_tva,fd.subprice,fd.total_ht,
fd.total_localtax1,fd.total_ttc,fd.remise_percent,fd.multicurrency_code,fd.multicurrency_subprice,p.ref p_ref,p.label p_label,
fd.multicurrency_total_ht,fd.multicurrency_total_ttc,fd.multicurrency_total_tva,p.ref p_ref,p.label,fd.qty,fd.estado 
FROM llx_facturedet_cashdespro fd
LEFT JOIN llx_product p ON fd.fk_product=p.rowid'; 
$sq .= ' WHERE fk_soc = '.$v->s_id.' AND fk_vendedor = '.$v->u_id.' AND fk_facture = '.$v->f_id.'';
$sql = $db->query($sq);
while($obj = $db->fetch_object($sql)){
  $product = New Product($db);
  $product->fetch($obj->fk_product);
  $images = new imagenes($db);
  $imgs = $images->productImage(0,$product->id);
if($obj->estado > 0){
 $estado = '<span class="badge badge-success text-uppercase"><span class="font-weight-bold">Orden:</span> <span id="estado_num">Listo</span></span>';
}

if($obj->estado <= 0){
 $estado = '<span class="badge badge-warning text-uppercase"><span class="font-weight-bold">Orden:</span> <span id="estado_num">En proceso</span></span>';
}
?>
          <!-- First row -->
          <tr>

            <th scope="row">

              <img src="<?php echo $imgs->share_phath ?>" alt=""
                class="img-fluid z-depth-0">

            </th>

            <td>

              <h5 class="mt-3">

                <strong><?php echo $obj->p_label ?></strong>

              </h5>

              <p class="text-muted"><?php echo $obj->p_ref ?></p>

            </td>


            <td><?php echo price($obj->total_ht); ?></td>

            <td class="text-center text-md-left">

              <span class="qty" id="qty_<?php echo $obj->rowid; ?>"><?php echo $obj->qty; ?></span>
              <?php if($v->estado == 0){ ?>
              <div class="btn-group radio-group ml-2" data-toggle="buttons">

                <label  
                data-id="<?php echo $obj->rowid; ?>" 
              data-descuento="<?php echo $obj->remise_percent; ?>" 
              data-fk_product="<?php echo $obj->fk_product; ?>""
              data-fk_soc="<?php echo $v->s_id; ?>" 
              data-fk_facture="<?php echo $v->f_id; ?>"
              data-tipo_desc="PC"         
              data-price_level="1" 
              data-price_base="<?php echo $product->multiprices_base_type[1]; ?>"           
              data-tva_tx="<?php echo $obj->tva_tx; ?>" 
              data-precio="<?php echo $obj->subprice; ?>"        
                
                class="menos btn btn-sm btn-primary btn-rounded">

                  <input type="radio" class="" name="options" id="option1_<?php echo $obj->rowid; ?>">&mdash;

                </label>

                <label
                data-id="<?php echo $obj->rowid; ?>" 
              data-descuento="<?php echo $obj->remise_percent; ?>" 
              data-fk_product='<?php echo $obj->fk_product; ?>' 
              data-fk_soc="<?php echo $v->s_id; ?>" 
              data-fk_facture="<?php echo $v->f_id; ?>"
              data-tipo_desc="PC"         
              data-price_level="1" 
              data-price_base="<?php echo $product->multiprices_base_type[1]; ?>"           
              data-tva_tx="<?php echo $obj->tva_tx; ?>" 
              data-precio="<?php echo $obj->subprice; ?>"        
                  
                  class="mas btn btn-sm btn-primary btn-rounded">

                  <input type="radio" name="options" id="option2_<?php echo $obj->rowid; ?>">+

                </label>

              </div>
              <?php } ?>
            </td>

            <td class="font-weight-bold">
            <?php if($v->estado == 0){ ?>
              <strong><?php echo price($obj->total_ttc); ?></strong>

            </td>

            <td>

              <button 
              data-id="<?php echo $obj->rowid; ?>" 
              data-descuento="<?php echo $obj->remise_percent; ?>" 
              data-fk_product='<?php echo $obj->fk_product; ?>' 
              data-fk_soc="<?php echo $v->s_id; ?>" 
              data-fk_facture="<?php echo $v->f_id; ?>"
              data-tipo_desc="PC"         
              data-price_level="1" 
              data-price_base="<?php echo $product->multiprices_base_type[1]; ?>"           
              data-tva_tx="<?php echo $obj->tva_tx; ?>" 
              data-precio="<?php echo $obj->subprice; ?>"                   
              type="button" class="delete btn btn-sm btn-danger" data-toggle="tooltip" data-placement="top"
              title="Remove item">X

              </button>
            <?php } ?>
            </td>
          <td><?php echo $estado ?></td>
          </tr>
          <!-- First row -->

<?php
}
}
}

?>



          <!-- Fourth row -->
          <tr>

            <td></td>

            <td>

              <h4 class="mt-2">

                <strong>Total</strong>

              </h4>

            </td>

            <td class="text-right">

              <h4 class="mt-2">
<?php
foreach($ordenes as $t){
$total_ttc += $t->total_ttc;
}
?>
                <strong><?php echo price($total_ttc); ?></strong>

              </h4>

            </td>

            <td  class="text-right">
            <?php if($orden->estado == 0){ ?>
              <a href="orden_resume.php?id=<?php echo $id; ?>&action=validar&fk_facture=<?php echo $orden->f_id; ?>"><button type="button" class="btn btn-primary btn-rounded">Confirmar orden

                <i class="fas fa-angle-right right"></i>

              </button></a>
            <?php } ?>
            </td>
          <td></td>
          <td></td>
          </tr>
          <!-- Fourth row -->

        </tbody>
        <!-- Table body -->

      </table>

    </div>
    <!-- Shopping Cart table -->

  </div>

</div>

</section>
<!-- Section cart -->


</div><!--/container fluid-->      
</div><!--/container fluid-->    

  <!--Main Layout-->
<?php } ?>
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


$('.mas').click(function(){
id = $(this).data('id');
cantidad = parseInt($('#qty_'+id).text());
cantidad_mas = cantidad + 1;
$('#qty_'+id).text(cantidad_mas);
update_line_restaurant($(this))
})

$('.menos').click(function(){
id = $(this).data('id');
cantidad = parseInt($('#qty_'+id).text());
cantidad_menos = cantidad - 1;
if(cantidad_menos < 1){
  cantidad_menos = 1; 
}
$('#qty_'+id).text(cantidad_menos);
update_line_restaurant($(this))
})


function update_line_restaurant(element){
  $('#barra_c').show(); 
  fk_facture = $('#fk_facture').val();
  id = $(element).data('id'); 
  fk_soc = $('#fk_soc').val();
  moneda = $('#moneda').val();
  price_level = $('#price_level').val(); 
cantidad = parseInt($('#qty_'+id).text());
descuento = $(element).data('descuento'); 
fk_product = element.data('fk_product'); 
fk_soc = $(element).data('fk_soc'); 
fk_facture = $(element).data('fk_facture'); 
tipo_desc =  $(element).data('tipo_desc');      
price_level = $(element).data('price_level'); 
price_base = $(element).data('price_base');            
tva_tx = $(element).data('tva_tx'); 
precio = $(element).data('precio'); 

 //envio por ajax
$.ajax({
  type: "POST",
  url: "ajax/add_line_restaurant.php",
  data: {
    id:id,
    descuento:descuento,
    tipo_desc:tipo_desc,
    cantidad:cantidad,
    precio:precio,
    fk_soc:fk_soc,
    tva_tx:tva_tx,
    fk_facture:fk_facture,
    fk_product:fk_product,
    moneda:moneda,
    price_level:price_level,
    price_base:price_base,
    action:'update_line'
  },
  dataType: "json",
  success: function(resp) {
  generar(resp)
  $('#barra_c').hide();
  $("#cantidad").val('');
  $("#select_product").focus(); 
  }
  
  })
  //envio por ajax
}

$('.delete').click(delete_line_restaurant);

function delete_line_restaurant(){
  $('#barra_c').show();  
fk_product = $(this).attr('data-fk_product');
fk_soc = $(this).attr('data-fk_soc');
id = $(this).attr('data-id');
fk_facture = $(this).attr('data-fk_facture');

 //envio por ajax
$.ajax({
  type: "POST",
  url: "ajax/add_line_restaurant.php",
  data: {
    fk_product:fk_product,
    fk_soc:fk_soc,
    id:id,
    fk_facture:fk_facture,
    action:'delete_line'
  },
  dataType: "json",
  success: function(resp) {
  location.reload();
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
</script>
