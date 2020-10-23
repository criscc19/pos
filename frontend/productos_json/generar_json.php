<?php
ini_set("memory_limit",-1);
set_time_limit(-1);
require_once(DOL_DOCUMENT_ROOT.'/product/class/product.class.php');
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
require_once(DOL_DOCUMENT_ROOT.'/pos/backend/class/cash.class.php');
require_once(DOL_DOCUMENT_ROOT.'/pos/frontend/include/funciones.php');
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
include_once DOL_DOCUMENT_ROOT .'/core/lib/images.lib.php';
include_once DOL_DOCUMENT_ROOT .'/core/lib/files.lib.php';
$cashid = $_SESSION['TERMINAL_ID'];
$cash = new Cash($db);
$cash->fetch($cashid);
$warehouse = $cash->fk_warehouse;

if(isset($warehouse) && $warehouse > 0){

if($conf->global->PRODUIT_MULTIPRICES){
$limite = $conf->global->PRODUIT_MULTIPRICES_LIMIT; 
}else{
$limite = 1;  
}
$bodega = $warehouse;

    $sq ='SELECT p.rowid, p.label, p.ref, p.description, p.barcode, p.fk_product_type,barcode,p.price_ttc, p.price_base_type, p.tva_tx, p.duration, p.fk_price_expression
     FROM llx_product as p WHERE p.entity IN (1) AND p.tosell = 1 ';

//echo $sq;exit;
$sql=$db->query($sq);
$result =array();
$f = 0;

function get_level_price($fk_product,$n){
  global $db;
  $sql = $db->query('SELECT max(rowid) rowid,nivel_precio,rango1,rango2 FROM `llx_rango_precios` WHERE fk_product='.$fk_product.' AND nivel_precio='.$n.'');
  while($obj = $db->fetch_object($sql)){
    $dato = ['rango1'=>$obj->rango1,'rango2'=>$obj->rango2];
  }
  return $dato;
  }

while ($obj = $db->fetch_object($sql)) {

  $sqs = 'SELECT SUM(reel) cantidad FROM `llx_product_stock` WHERE `fk_product` = '.$obj->rowid.' AND fk_entrepot='.$warehouse.'';
  $sqls = $db->query($sqs);
  $cantidad = $db->fetch_object($sqls)->cantidad;


$pro = new Product($db);
$pro->fetch($obj->rowid);
$ext = $pro->fetch_optionals();

$result[$f]= array(
    'id'=>$pro->id,
    'text'=>''.$pro->barcode.'-'.$pro->ref.' - '.$obj->label.' - Precio:'. price($pro->multiprices[1]).' - Stock '.$pro->stock,
    'ref'=>$pro->ref,
    'label'=>$pro->label,
    'Stock'=>(float)$cantidad,
    'barcode'=>(float)$pro->barcode,
    'image'=>get_img_product(0,$pro->id),
    'icon'=>get_img_product(0,$pro->id)->realpath,
    'categorias'=>getCategories($pro->id),
    'extrafields'=>$pro->array_options
);


for ($i = 1; $i <= $limite; $i++) {   
    $rango = get_level_price($obj->rowid,$i);
if($conf->global->ACTIVAR_RAGO_CANTIDAD_PRECIO){    
    $result[$f]['rango1_'.$i]= (int)$rango['rango1'];
    $result[$f]['rango2_'.$i]= (int)$rango['rango2'];
  }
    $result[$f]['precio'.$i.'']=price($pro->multiprices[$i]);
    $result[$f]['precio'.$i.'_dolar']=price($pro->multiprices_dolar[$i]);        
  }    
$f++;

}

if(file_exists(DOL_DOCUMENT_ROOT.'/pos/frontend/productos_json/results_'.$_SESSION["TERMINAL_ID"].'.json')){
  unlink(DOL_DOCUMENT_ROOT.'/pos/frontend/productos_json/results_'.$_SESSION["TERMINAL_ID"].'.json'); 
}

$fp = fopen(DOL_DOCUMENT_ROOT.'/pos/frontend/productos_json/results_'.$_SESSION["TERMINAL_ID"].'.json', 'w');
fwrite($fp, json_encode($result));
fclose($fp); 


}




  