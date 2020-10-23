<?php
include '../../../main.inc.php';
$fk_propal = $_POST['fk_propal'];
 $sq = 'SELECT fd.rowid fd_id,fd.qty,fd.tva_tx,fd.description fd_description,fd.remise_percent,fd.total_ht,fd.total_tva,
 fd.total_ttc,p.ref,p.label,p.description  FROM llx_propaldet fd
 LEFT JOIN llx_product p ON fd.fk_product=p.rowid
 WHERE fk_propal = '.$fk_propal.''; 
 $sql = $db->query($sq);
 $datos = [];
 while($obj = $db->fetch_object($sql)){
 $datos[] = [
   'fd_id'=>$obj->fd_id,
   'qty'=>$obj->qty,
   'tva_tx'=>$obj->tva_tx,
   'fd_description'=>$obj->fd_description,
   'remise_percent'=>$obj->remise_percent,
   'total_ht'=>price($obj->total_ht),
   'total_tva'=>price($obj->total_tva),
   'total_ttc'=>price($obj->total_ttc),
   'ref'=>$obj->ref,   
   'label'=>$obj->label,   
   'description'=>$obj->description   
  ];
 }
echo json_encode($datos);
?>