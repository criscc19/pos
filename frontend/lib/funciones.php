<?php

/**
 * @param $code string codigo de actividad economica
 */

function get_actividad($code){
global $db;
$sq = 'SELECT * FROM `llx_c_actividad_economica` WHERE `code` = '.$code.'';
$sql = $db->query($sq);
while($obj = $db->fetch_object($sql)){
$actividad = $obj->label;  
}
return $actividad;
}

?>