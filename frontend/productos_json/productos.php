<?php
include_once '../../main.inc.php';
//autocompletado
if(isset($_POST['q'])){
$q = $_POST['q'];
$s = $_POST['s'];
//obteniedo nivel de precio de societe
$sqls = $db->query('SELECT price_level FROM llx_societe WHERE rowid = '.$s.'');
if($db->num_rows($sqls)>0){
for ($e = 1; $e <= $db->num_rows($sqls); $e++) {
$objs = $db->fetch_object($sqls);
$nivel = $objs->price_level;
}}else{$nivel = 1;};

$sql=$db->query('SELECT p.rowid, p.label, p.ref, p.description, p.barcode, p.fk_product_type,barcode,
(SELECT price FROM `llx_product_price` WHERE fk_product = p.rowid AND price_level = '.$nivel.' ORDER BY rowid DESC LIMIT 1) price,
(SELECT multicurrency_price FROM `llx_product_price` WHERE fk_product = p.rowid AND price_level = '.$nivel.' ORDER BY rowid DESC LIMIT 1) multicurrency_price, 
p.price_ttc, p.price_base_type, p.tva_tx, p.duration, p.fk_price_expression, 
(SELECT e.reel FROM llx_product_stock e WHERE e.fk_product =p.rowid AND e.fk_entrepot=1) stock
 FROM llx_product as p WHERE p.entity IN (1) AND p.tosell = 1 AND 
((p.ref LIKE "%'.$q.'%" OR p.label LIKE "%'.$q.'%" OR p.barcode LIKE "%'.$q.'%")) AND tosell=1 ORDER BY  p.label, p.ref limit 50');
$result =array();
for ($i = 1; $i <= $db->num_rows($sql); $i++) {
$obj = $db->fetch_object($sql);
$sqp = $db->query('SELECT entity, ref, filename FROM llx_ecm_files WHERE filepath = "produit/'.$obj->ref.'" AND position =1');
for ($x = 1; $x <= $db->num_rows($sqp); $x++) {
$obp = $db->fetch_object($sqp);
$fo = explode('.',$obp->filename);}
// $result[]= array('id'=>$obj->rowid,'text'=>'Ref:'.$obj->ref.' - Nom:'.$obj->label.' - Precio:'. price($obj->price).' - Stock'.$obj->stock,'icon'=>''.DOL_URL_ROOT .'/viewimage.php?modulepart=product&entity='.$obp->entity.'&file='.$obj->ref.'/thumbs/'.$fo[0].'_mini.'.$fo[1].''); 
$result[]= array(
    'id'=>$obj->rowid,
    'text'=>''.$obj->ref.' - '.$obj->label.' - Precio:'. price($obj->price).' - Stock '.$obj->stock,
    'ref'=>$obj->ref,
    'label'=>$obj->label,
    'Stock'=>$obj->stock,
    'precio'=>$obj->price,
    'precio_dolar'=>$obj->precio_dolar,    
    'barcode'=>$obj->barcode,    
    'icon'=>''
);
}
echo json_encode($result);
}
