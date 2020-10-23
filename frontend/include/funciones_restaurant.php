<?php
if(DOL_VERSION < 11){
    $facnumber = 'facnumber';
    }else{
    $facnumber = 'ref';  
    }

function get_mesas($mesa='',$sqs=''){
global $db;
$sq = 'SELECT * FROM llx_pos_restaurant_mesas';
$sq .=' WHERE 1';
if($mesa !=''){$sq .=' AND rowid='.$mesa.'';}
$sq .= $sqs;
$sql = $db->query($sq);
$mesas = [];

while($obj = $db->fetch_object($sql)){
$mesa = new Stdclass();
$mesa->id = $obj->rowid;
$mesa->name = $obj->name;
$mesa->description = $obj->description;
$mesa->capacidad = $obj->capacidad;
$mesa->ubicacion = $obj->ubicacion;
$mesa->position = $obj->position;
$mesa->estado = $obj->estado;
$mesa->asignado = $obj->asignado;
$mesa->sql = $sq;
$mesas[] = $mesa;


}
if(count($mesas) > 0){return $mesas;}
else{
$mesa = new Stdclass();
$mesa->sql = $sq;  
$mesas[] = $mesa;  
return $mesas;
}
}

function get_departamentos($fk_dep='',$sqs=''){
global $db;
$sq = 'SELECT * FROM llx_pos_restaurant_departamentos';
$sq .=' WHERE 1';
if($fk_dep !=''){$sq .=' AND rowid='.$fk_dep.'';}
$sq .= $sqs;
$sql = $db->query($sq);
$departamentos = [];

while($obj = $db->fetch_object($sql)){
$depa = new Stdclass();
$depa->id = $obj->rowid;
$depa->name = $obj->name;
$depa->categorias = $obj->categorias;
$depa->sql = $sq;
$departamentos[] = $depa;
}
if(count($departamentos) > 0){return $departamentos;}
else{
$depa = new Stdclass();
$depa->sql = $sq;  
$departamentos[] = $depa;  
return $departamentos;
}
}

function getToken($length){
    $token = "";
    $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
    $codeAlphabet.= "0123456789";
    $max = strlen($codeAlphabet); // edited
  
   for ($i=0; $i < $length; $i++) {
       $token .= $codeAlphabet[random_int(0, $max-1)];
   }
  
   return $token;
  }
  
?>