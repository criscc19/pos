<?php
/**funtion encargada de insertar en la tabla llx_cierre_caja_denominacion */
function insertar_llx_cierre_caja_denominacion($fk_cierre_caja,$fk_banco,$cantidad){
    global $db, $langs,$conf;
    $sql='INSERT INTO llx_cierre_caja_denominacion(fk_cierre_caja,fk_denominacion,cantidad)
     VALUES ('.$fk_cierre_caja.','.$fk_denominacion.','.$cantidad.')';
     $consulta = $db->query($sql);
     return $consulta;
}
/**Funcion encargada de eliminar en la tabla llx_cierre_caja_denominacion */
function Eliminar_llx_cierre_caja_denominacion($rowid){
    global $db, $langs,$conf;
    $sql='DELETE FROM  llx_cierre_caja_denominacion den WHERE den.rowid='.$rowid;
    $consulta = $db->query($sql);
    return $consulta;
}

/**Funcion encargada de actualizar en la tabla llx_cierre_caja_denominacion */
function actualizacion_llx_cierre_caja_denominacion($rowid,$fk_cierre_caja,$fk_denominacion,$cantidad){
    global $db, $langs,$conf;
    $sql='UPDATE llx_cierre_caja_denominacion SET 
    fk_cierre_caja='.$fk_cierre_caja.',fk_denominacion='.$fk_denominacion.',cantidad='.$cantidad.' WHERE rowid='.$rowid;
    $consulta = $db->query($sql);
    return $consulta;
}
/**Funcion encargada de mostrar la informacion de la tabla llx_cierre_caja_denominacion */
function ver__llx_cierre_caja_denominacion(){
    global $db,$langs,$conf;
    $sql ='SELECT * FROM llx_cierre_caja_denominacion';
    $consulta = $db->query($sql);
    while($obj=$db->fetch_object($consulta)){
        $demoninacion_cierre_caja[]=['id'=>$obj->rowid,'fk_cierre_caja'=>$obj->fk_cierre_caja,'$fk_demoninacion'=>$obj->fk_denominacion,'$cantidad'=>$obj->xcantidad];
        }
    return $denominacion_cierre_caja;
}

?>