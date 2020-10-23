<?php
  
/** Funcion encargada de insertar en la tabla llx_banco_comision*/
function select_bancos_comision(){
    global $db;
    $sq='SELECT * FROM llx_banco_comision';
    $sql = $db->query($sq); 
    while($obj = $db->fetch_object($sql)){
        $datos = ['id'=>$obj->fk_bank,'ref'=>$obj->ref,'currency_code'=>$obj->currency_code,'comision1'=>$obj->comision1,'comision2'=>$obj->comision2,'comision3'=>$obj->comision3];
        }  
        return $datos;    
}
/** Funcion encargada de insertar en la tabla llx_banco_comision*/
function insertar_llx_banco_comision($ref,$label,$currency_code,$comision1,$comision2,$comision3,$fk_bank){
    global $db;
    $sq='SELECT * FROM llx_banco_comision where fk_bank ='.$fk_bank;
    $sql = $db->query($sq);
    if($db->num_rows($sql) > 0){
        $sq = 'UPDATE llx_banco_comision SET comision1='.$comision1.',comision2='.$comision2.',comision3='.$comision3.' WHERE fk_bank='.$fk_bank;
        $resultado = $db->query($sq);
    }else{
        $sq='INSERT INTO llx_banco_comision(ref,label,multi_currency_code,comision1,comision2,comision3,fk_bank)
        VALUES ("'.$ref.'","'.$label.'","'.$currency_code.'",'.$comision1.','.$comision2.','.$comision3.','.$fk_bank.')';
        //echo $sq;exit;
       $sql = $db->query($sq);        
    }

}

?>