<?php
include("../../../main.inc.php");
$columna[0] = 'ref';
$columna[1] = 'label';
$columna[2] = 'precio';
$columna[3] = 'reel';

$draw = $_GET['draw'];
$start = $_GET['start'];
$length = $_GET['length'];
$search = $_GET['search']['value'];
$order_index = $_GET['order'][0]['column'];
$order = $columna[$order_index];
$dir = $_GET['order'][0]['dir'];
$bodega = $_GET['search']['bodega'];
$entidad = 1;
$nivel = $_GET['search']['nivel'];

$sq = '
SELECT p.rowid,p.ref,p.label,p.description,';
$sq .=' (SELECT pp.price_ttc FROM llx_product_price pp WHERE pp.fk_product=p.rowid AND pp.price_level='.$nivel.' ORDER BY pp.date_price DESC LIMIT 1) precio,';
$sq .= 'if (ps.reel is not null, ps.reel, 0) reel
FROM llx_product p
LEFT JOIN llx_product_stock ps ON ps.fk_product=p.rowid AND ps.fk_entrepot='.$bodega.'';
if($search !=''){
    $sq .= '  WHERE p.entity='.$entidad.' AND p.ref LIKE "%'.$search.'%" OR p.label LIKE "%'.$search.'%"';	
    }
$sq .= ' ORDER BY '.$order.' '.$dir.' LIMIT '.$start.','.$length.';';

$sql = $db->query($sq);
$datos = [];
$datos['draw'] = $draw;
$datos['recordsTotal'] = $db->num_rows($sql);
$datos['recordsFiltered'] = $db->num_rows($sql);
while($obj = $db->fetch_object($sql)){
$datos['data'][]=['codigo'=>$obj->ref,'producto'=>$obj->label,'precio'=>price($obj->precio),'inventario'=>$obj->reel];
}

echo json_encode($datos);exit;
