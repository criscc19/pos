<?php
/** Funcion encargada de insertar en la tabla llx_banco_comision*/
function insertar_llx_banco_comision($ref,$label,$currency_code,$comision1,$comision2,$comision3,$fk_bank){
    global $db;
    if(existe_llx_banco_comision($fk_bank)){
        actualiza_llx_banco_comision($comision1,$comision2,$comision3,$fk_bank);
    }else{
        $sq='INSERT INTO llx_banco_comision(ref,label,multi_currency_code,comision1,comision2,comision3,fk_bank)
        VALUES ("'.$ref.'","'.$label.'","'.$currency_code.'",'.$comision1.','.$comision2.','.$comision3.','.$fk_bank.')';
       $sql = $db->query($sq);
    }
}
/**Funcion encargada de eliminar de la tabla llx_banco_comision */ 
function eliminar_llx_banco_comision($rowid){
    global $db;
    $sq = 'DELETE FROM llx_banco_comision cs  WHERE cs.rowid ='.$rowid;
    $consulta = $db->query($sq);
    return $consulta;
}
/**Funcion actualiza la tabla llx_banco_comision */
function actualiza_llx_banco_comision($comision1,$comision2,$comision3,$fk_bank){
    global $db;
    $sq = 'UPDATE llx_banco_comision SET comision1='.$comision1.',comision2='.$comision2.',comision3='.$comision3.' WHERE fk_bank='.$fk_bank;
    $resultado = $db->query($sq);
}
/**Funcion muestra la informacion de la tabla llx_banco_comision*/
function ver_llx_banco_comision(){
    global $db;    
    $sq = "SELECT * FROM llx_banco_comision";
    $consulta = $db->query($sq);
    while($obj=$db->fetch_object($consulta)){
    $bancos_comision[]=['id'=>$obj->rowid,'ref'=>$obj->ref,'label'=>$obj->label,'currency_code'=>$obj->multi_currency_code,'comision1'=>$obj->comision1,'comision2'=>$obj->comision2,'comision3'=>$obj->comision3,'fk_bank'=>$obj->fk_bank];
    }
    return $bancos_comision;
    }
/**Funcion verificacion si exite en la tabla llx_banco_comision */
function existe_llx_banco_comision($fk_bank){
    global $db;
    $bandera= false;
    $sq='SELECT * FROM llx_banco_comision where fk_bank ='.$fk_bank;
    $resultado = $db->query($sq);
    if(count($resultado) > 0){
        $bandera=true;
    } 
    return $bandera;
}

?>