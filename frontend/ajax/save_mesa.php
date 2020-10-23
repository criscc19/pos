<?php
require '../../../main.inc.php';
if($_POST['action']== 'save_position'){
if((int)$_POST['position'] > 0){
$res = $db->query('UPDATE llx_pos_restaurant_mesas SET position = '.$_POST['position'].',asignado="1" WHERE llx_pos_restaurant_mesas.rowid = '.$_POST['id'].'');
echo $res;
}
if((int)$_POST['position'] < 0){
$res = $db->query('UPDATE llx_pos_restaurant_mesas SET position = 0,asignado="0", estado = "0" WHERE llx_pos_restaurant_mesas.rowid = '.$_POST['id'].'');
echo $res;
}
}
?>