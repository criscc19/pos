<?php
/**funtion encargada de poder insertar en  */
function insertar_llx_pos_control_cash($fk_cierre_caja,$fk_banco,$monto){
    global $db, $langs,$conf;
    $sql='INSERT INTO llx_cierre_caja_denominacion_bank(fk_cierre_caja,fk_bank,monto)
     VALUES ('.$fk_cierre_caja.','.$fk_banco.','.$monto.')';
     $consulta = $db->query($sql);
     return $consulta;
}
/**Funcion encargada de eliminacion en la tabla llx_pos_control_cash*/
function Eliminar_llx_pos_control_cash($rowid){
    global $db, $langs,$conf;
    $sql='DELETE FROM  llx_cierre_caja_denominacion_bank  bank WHERE bank.rowid='.$rowid;
    $consulta = $db->query($sql);
    return $consulta;
}

/**Funcion encargada de actualizacion en la tabla llx_pos_control_cash*/
function actualizacion_llx_pos_control_cash($rowid,$fk_cierre_caja,$fk_banco,$monto){
    global $db, $langs,$conf;
    $sql='UPDATE llx_cierre_caja_denominacion_bank SET 
    fk_cierre_caja='.$fk_cierre_caja.',fk_bank='.$fk_banco.',monto='.$monto.' WHERE rowid='.$rowid;
    $consulta = $db->query($sql);
    return $consulta;
}
/**Funcion encargada de mostrar todo de la tabla llx_pos_control_cash*/
function ver__llx_pos_control_cash(){
    global $db,$langs,$conf;
    $sql ='SELECT * FROM llx_cierre_caja_denominacion_bank';
    $consulta = $db->query($sql);
    while($obj=$db->fetch_object($consulta)){
        $bancos_cierre_caja[]=['id'=>$obj->rowid,'fk_cierre_caja'=>$obj->fk_cierre_caja,'$fk_banco'=>$obj->fk_banco,'$monto'=>$obj->monto];
        }
    return $bancos_cierre_caja;
}

?>