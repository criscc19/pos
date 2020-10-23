<?php
ini_set("memory_limit",-1);
set_time_limit(-1);
require '../../../master.inc.php';
require_once(DOL_DOCUMENT_ROOT.'/product/class/product.class.php');
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
require_once(DOL_DOCUMENT_ROOT.'/pos/backend/class/cash.class.php');
require_once(DOL_DOCUMENT_ROOT.'/pos/frontend/include/funciones.php');
require_once(DOL_DOCUMENT_ROOT.'/pos/frontend/class/get_img.class.php');
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
include_once DOL_DOCUMENT_ROOT .'/core/lib/images.lib.php';
include_once DOL_DOCUMENT_ROOT .'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/variants/class/ProductCombination.class.php';

$warehouse = 1;
$q = $_POST['q']; //select_product
$l = $_POST['l']; //price_level
$m = $_POST['m']; //moneda
$cantidad = 1; //cantidad

if(isset($warehouse) && $warehouse > 0){

if($conf->global->PRODUIT_MULTIPRICES){
$limite = $conf->global->PRODUIT_MULTIPRICES_LIMIT; 
}else{
$limite = 1;  
}
$bodega = $warehouse;
if($_POST['action']=='get_categorias'){
$sq = 'SELECT p.rowid, p.label, p.ref, p.description, p.barcode, p.fk_product_type,barcode,p.price_ttc, p.price_base_type, p.tva_tx, p.duration, p.fk_price_expression
FROM llx_product as p 
JOIN llx_categorie_product cp ON cp.fk_product=p.rowid
WHERE p.entity IN (1) AND p.tosell = 1 AND cp.fk_categorie='.$_POST['cat_id'].' LIMIT 50';  
}else{
    $sq ='SELECT p.rowid, p.label, p.ref, p.description, p.barcode, p.fk_product_type,barcode,p.price_ttc, p.price_base_type, p.tva_tx, p.duration, p.fk_price_expression
     FROM llx_product as p 
     WHERE p.entity IN (1) AND p.tosell = 1 AND 
     ((p.ref LIKE "%'.$q.'%" OR p.label LIKE "%'.$q.'%" OR p.barcode LIKE "%'.$q.'%")) AND tosell=1 limit 25
     ';  
}
if($_POST['action']=='get_atributes'){
    $sq ='SELECT p.rowid, p.label, p.ref, p.description, p.barcode, p.fk_product_type,barcode,p.price_ttc, p.price_base_type, p.tva_tx, p.duration, p.fk_price_expression
     FROM llx_product as p 
     WHERE p.rowid='.$_POST['fk_parent'].' AND p.entity IN (1) AND p.tosell = 1';    
}

if($_POST['action']=='get_variant'){
  $prodcomb = new ProductCombination($db);
  if ($res = $prodcomb->fetchByProductCombination2ValuePairs($_POST['fk_parent'], $_POST['attributes'])) {
    $idprod = $res->fk_product_child;
  }
  else
  {
echo -1;exit;
  }

    $sq ='SELECT p.rowid, p.label, p.ref, p.description, p.barcode, p.fk_product_type,barcode,p.price_ttc, p.price_base_type, p.tva_tx, p.duration, p.fk_price_expression
     FROM llx_product as p 
     WHERE p.rowid='.$idprod.' AND p.entity IN (1) AND p.tosell = 1';    
}

//echo $sq;exit;
$sql=$db->query($sq);
$result =array();
$f = 0;

function get_level_price($fk_product,$cantidad){
    global $db;
    $sql = $db->query('SELECT max(rowid) rowid,nivel_precio,rango1,rango2 FROM `llx_rango_precios` WHERE fk_product='.$fk_product.' AND ('.$cantidad.' BETWEEN rango1 AND rango2) LIMIT 1');
    while($obj = $db->fetch_object($sql)){
     $nivel_precio = $obj->nivel_precio;
    }
    
    return $nivel_precio;
    }

function get_variant($fk_prouct){
global $db;
$sq = 'SELECT pv.rowid,pv.ref,pv.label,pav.rowid pav_id,pav.value,pa.rowid pa_id,pa.label 
FROM llx_product_attribute_combination2val pc2v
JOIN llx_product_attribute_combination pac ON pc2v.fk_prod_combination=pac.rowid
JOIN llx_product p ON pac.fk_product_parent=p.rowid
JOIN llx_product pv ON pac.fk_product_child=pv.rowid
JOIN llx_product_attribute_value pav ON pav.rowid=pc2v.fk_prod_attr_val
JOIN llx_product_attribute pa ON pa.rowid=pav.fk_product_attribute
WHERE p.rowid='.$fk_prouct.'';
$sql = $db->query($sq);

$variant =[]; 
while($obj = $db->fetch_object($sql)){
 $variant[$obj->label][$obj->value]= ['id_attr'=>$obj->pa_id,'id_value'=>$obj->pav_id];
}

return $variant;
}





while ($obj = $db->fetch_object($sql)) {

  $sqs = 'SELECT SUM(reel) cantidad FROM `llx_product_stock` WHERE `fk_product` = '.$obj->rowid.' AND fk_entrepot='.$warehouse.'';
  $sqls = $db->query($sqs);
  $cantidad = $db->fetch_object($sqls)->cantidad;


$pro = new Product($db);
$pro->fetch($obj->rowid);
$ext = $pro->fetch_optionals();
if($pro->barcode !=''){$barcode = $pro->barcode.' - ';}else{$barcode = '';};

$nivel = $l;
    
if($conf->global->ACTIVAR_RAGO_CANTIDAD_PRECIO){    
 $nivel = get_level_price($obj->rowid,$nivel);  
  }
  
if($nivel <= 0 ){
$nivel = $l;
}

if($m == 'CRC'){
  $precio=$pro->multiprices_ttc[$nivel];
  $multiprices_base_type=$pro->multiprices_base_type[$nivel];
  $multiprices_default_vat_code=$pro->multiprices_default_vat_code[$nivel];
  $multiprices_tva_tx=$pro->multiprices_tva_tx[$nivel];
}
  else{
  $precio=$pro->multiprices_dolar_ttc[$nivel];
  $multiprices_base_type=$pro->multiprices_base_type[$nivel];
  $multiprices_default_vat_code=$pro->multiprices_default_vat_code[$nivel];
  $multiprices_tva_tx=$pro->multiprices_tva_tx[$nivel]; 
 }   
    
 $images = new imagenes($db);
 $imgs = $images->productImage(0,$pro->id);
  $variant = get_variant($pro->id);
  $result[$f]= array(
    'id'=>$pro->id,
    'text'=>$barcode.$pro->ref.' - '.$obj->label.' - Precio:'. price($precio).' - Stock '.(float)$cantidad,
    'ref'=>$pro->ref,
    'label'=>$pro->label,
    'description'=>$pro->description,    
    'type'=>$pro->type,    
    'Stock'=>(float)$cantidad,
    'barcode'=>(float)$pro->barcode,
    'multiprices_base_type'=>$multiprices_base_type,
    'multiprices_default_vat_code'=>$multiprices_default_vat_code,
    'multiprices_tva_tx'=>$multiprices_tva_tx,   
    'image'=>$imgs->share_phath,
    'icon'=>$imgs->share_phath,
    'categorias'=>getCategories($pro->id),
    'extrafields'=>$pro->array_options,
    'attributes'=>$variant,
    'cant_attributes'=>count($variant)   
);

  
  if($m == 'CRC'){$result[$f]['precio']=price($pro->multiprices[$nivel]);}else{
   $result[$f]['precio']=price($pro->multiprices_dolar[$nivel]); 
  }   
    $f++;


if($pro->isVariant() && $_POST['action']=='get_variant'){
  $images = new imagenes($db);
 $imgs = $images->productImage(0,$pro->id);
  $variant = get_variant($pro->id);
$result[$f]= array(
    'id'=>$pro->id,
    'text'=>$barcode.$pro->ref.' - '.$obj->label.' - Precio:'. price($precio).' - Stock '.(float)$cantidad,
    'ref'=>$pro->ref,
    'label'=>$pro->label,
    'type'=>$pro->type,     
    'Stock'=>(float)$cantidad,
    'barcode'=>(float)$pro->barcode,
    'multiprices_base_type'=>$multiprices_base_type,
    'multiprices_default_vat_code'=>$multiprices_default_vat_code,
    'multiprices_tva_tx'=>$multiprices_tva_tx,   
    'image'=>$imgs->share_phath,
    'icon'=>$imgs->share_phath,
    'categorias'=>getCategories($pro->id),
    'extrafields'=>$pro->array_options,
    'attributes'=>$variant,
    'cant_attributes'=>count($variant)   
);

  
  if($m == 'CRC'){$result[$f]['precio']=price($pro->multiprices[$nivel]);}else{
   $result[$f]['precio']=price($pro->multiprices_dolar[$nivel]); 
  }   
    $f++;
}



} 
echo json_encode($result);

}




  