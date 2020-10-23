<?php
require('../../../master.inc.php');
require_once(DOL_DOCUMENT_ROOT.'/pos/frontend/class/cashdespro_facture.class.php');
require_once(DOL_DOCUMENT_ROOT.'/product/class/product.class.php');
require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';
require_once DOL_DOCUMENT_ROOT . '/feng/class/feng.exonerado.class.php';
require_once DOL_DOCUMENT_ROOT . '/pos/frontend/include/exonerar.php';
$user = new User($db);
$user->fetch($conf->global->POS_VENDEDOR_GLOBAL);
//creando permisos necesarios
$user->rights->facture = new stdClass();
$user->rights->facture->invoice_advance = new stdClass();
$user->rights->facture->invoice_advance->validate=1;
$user->rights->facture->creer = 1;

if($_POST['action'] == 'counter'){
$sq = 'SELECT COUNT(fd.rowid) cantidad FROM llx_facturedet_cashdespro fd
JOIN llx_facture_cashdespro f ON fd.fk_facture=f.rowid AND f.fk_mesa='.$_POST['fk_mesa'].'';
$sql = $db->query($sq);
while($obj = $db->fetch_object($sql)){
 echo  $obj->cantidad;
}

}

if($_POST['action'] == 'dividir_fac'){
$num = GETPOST('num_div'); 
$tipo = GETPOST('tipo_div'); 
$fac = new Facture_cashdespro($db);
$fac->fetch(GETPOST('fk_facture')); 
$num_lines  = count($fac->lines);
//obteniedo tipo de cambio
$currencyRate = new MultiCurrency($db);
$scambio = $currencyRate->getIdAndTxFromCode($db, 'USD');
$cambio_dolar = 1/$scambio[1];
$multicurrency_tx = $scambio[1]; 
//fin de obteniedo tipo de cambio
$c=0;
for($i=1;$i<=$num;$i++){
//creando referencia factura
$mascara = 'SEPA-{000000+000000}';
$element = 'facture_cashdespro';
$referencia = 'facnumber';
$numero = get_next_value($db,$mascara,$element,$referencia ,$where,$soc,$obj->date,'next');
//fin creando referencia    
 //nueva factura
$fac_1 = new Facture_cashdespro($db);
$fac_1->facnumber = $numero;
$fac_1->date = date('Y-m-d H:i:s');
$fac_1->date_creation = date('now');
$fac_1->socid = $conf->global->POS_CLIENTE_GLOBAL;
$fac_1->ref_client = $ref_client;
$fac_1->entity = $conf->entity;
$fac_1->cond_reglement_id= 0;
$fac_1->mode_reglement_id= 0;
$fac_1->multicurrency_tx = $multicurrency_tx;
$fac_1->multicurrency_code= $conf->global->POS_MONEDA_GLOBAL;
$fac_1->type = $conf->global->POS_TIPO_DOC_GLOBAL;
$fac_1->fk_mesa = $fk_mesa;
$fac_id = $fac_1->create($user);  

 foreach($fac->lines as $l){
        $desc = $l->desc;
        $pu_ht = ($tipo == 'todo'?$l->subprice/$num:$_POST['num_div_part'][$c]/$num_lines); 
        $qty = $l->qty;
        $txtva = $l->tva_tx;
        $txlocaltax1=0; 
        $txlocaltax2=0; 
        $fk_product=$l->fk_product;
        $remise_percent=$l->remise_percent;
        $date_start=''; 
        $date_end=''; 
        $ventil=0; 
        $info_bits=0;
        $fk_remise_except='';
        $price_base_type=($tipo == 'todo'?'HT':'TTC'); 
        $pu_ttc=0; 
        $type=$type; 
        $rang=-1; 
        $special_code=0;
        $origin=''; 
        $origin_id=0; 
        $fk_parent_line=0; 
        $fk_fournprice=null; 
        $pa_ht=0;
        $label=''; 
        $array_options=0; 
        $situation_percent=100; 
        $fk_prev_id=0; 
        $fk_unit = null;
        $pu_ht_devise = $l->multicurrency_subprice;
        $fk_soc = $fac->socid;
        $fk_user = $user->id;
        $fk_vendedor = $fac->user_author;
        
        $resp = $fac_1->addline($desc, $pu_ht, $qty, $txtva, $txlocaltax1, $txlocaltax2, $fk_product, $remise_percent, $date_start, $date_end, $ventil, $info_bits, $fk_remise_except, $price_base_type, $pu_ttc, $type, $rang, $special_code0, $origin, $origin_id, $fk_parent_line, $fk_fournprice, $pa_ht, $label, $array_options, $situation_percent, $fk_prev_id, $fk_unit, $pu_ht_devise,$fk_soc,$fk_user,$fk_vendedor,$multicurrency_tx);

    }
 //fin agregando lineas
$c++;
        }

//eliminando factura
/* $id = $_POST['fk_facture'];
$sq = 'DELETE FROM llx_facture_cashdespro WHERE rowid = '.GETPOST('fk_facture').'';
$res = $db->query($sq);
if($res){
$sq = 'DELETE FROM llx_facturedet_cashdespro WHERE fk_facture = '.GETPOST('fk_facture').'';
$res = $db->query($sq);    
} */
//fin eliminando factura
echo 1;
}

if($_POST['action'] == 'fusionar'){
$lineas = [];    
foreach($_POST['facturas'] as $f){
    $fac = new Facture_cashdespro($db);
    $fac->fetch($f['fk_facture']);
    $fk_mesa = $fac->fk_mesa;
    foreach($fac->lines as $l){
    $line = New stdClass();   
    $line->subprice = $l->subprice;
    $line->fk_product = $l->fk_product;
    $line->desc = $l->desc;   
    $line->qty = $l->qty;   
    $line->tva_tx = $l->tva_tx;  
    $line->remise_percent = $l->remise_percent; 
    $line->multicurrency_subprice = $l->multicurrency_subprice; 
    $lineas[] = $line;          
    }
    
   }
//creando referencia factura
$mascara = 'COMB-{000000+000000}';
$element = 'facture_cashdespro';
$referencia = 'facnumber';
$numero = get_next_value($db,$mascara,$element,$referencia ,$where,$soc,$obj->date,'next');
//fin creando referencia
//obteniedo tipo de cambio
$currencyRate = new MultiCurrency($db);
$scambio = $currencyRate->getIdAndTxFromCode($db, 'USD');
$cambio_dolar = 1/$scambio[1];
$multicurrency_tx = $scambio[1]; 
//fin de obteniedo tipo de cambio

$fac = new Facture_cashdespro($db);
$fac->facnumber = $numero;
$fac->date = date('Y-m-d H:i:s');
$fac->date_creation = date('now');
$fac->socid = $conf->global->POS_CLIENTE_GLOBAL;
$fac->ref_client = $ref_client;
$fac->entity = $conf->entity;
$fac->cond_reglement_id= 0;
$fac->mode_reglement_id= 0;
$fac->multicurrency_tx = $multicurrency_tx;
$fac->multicurrency_code= $conf->global->POS_MONEDA_GLOBAL;
$fac->type = $conf->global->POS_TIPO_DOC_GLOBAL;
$fac->fk_mesa = $fk_mesa;
$fac_id = $fac->create($user);  

if($fac_id > 0){
    $fac = new Facture_cashdespro($db);
    $fac->fetch($fac_id);   
    foreach($lineas as $l){
    $desc = $l->desc;
    $pu_ht = $l->subprice; 
    $qty = $l->qty;
    $txtva = $l->tva_tx;
    $txlocaltax1=0; 
    $txlocaltax2=0; 
    $fk_product=$l->fk_product;
    $remise_percent=$l->remise_percent;
    $date_start=''; 
    $date_end=''; 
    $ventil=0; 
    $info_bits=0;
    $fk_remise_except='';
    $price_base_type='HT'; 
    $pu_ttc=0; 
    $type=$type; 
    $rang=-1; 
    $special_code=0;
    $origin=''; 
    $origin_id=0; 
    $fk_parent_line=0; 
    $fk_fournprice=null; 
    $pa_ht=0;
    $label=''; 
    $array_options=0; 
    $situation_percent=100; 
    $fk_prev_id=0; 
    $fk_unit = null;
    $pu_ht_devise = $l->multicurrency_subprice;
    $fk_soc = $fac->socid;
    $fk_user = $fac->socid;
    $fk_vendedor = $fac->user_author;
    
    $resp = $fac->addline($desc, $pu_ht, $qty, $txtva, $txlocaltax1, $txlocaltax2, $fk_product, $remise_percent, $date_start, $date_end, $ventil, $info_bits, $fk_remise_except, $price_base_type, $pu_ttc, $type, $rang, $special_code0, $origin, $origin_id, $fk_parent_line, $fk_fournprice, $pa_ht, $label, $array_options, $situation_percent, $fk_prev_id, $fk_unit, $pu_ht_devise,$fk_soc,$fk_user,$fk_vendedor,$multicurrency_tx);
    }
    }
foreach($_POST['facturas'] as $f){
 //eliminando factura
 $id = $f['fk_facture'];
$sq = 'DELETE FROM llx_facture_cashdespro WHERE rowid = '.$f['fk_facture'].'';
$res = $db->query($sq);
if($res){
$sq = 'DELETE FROM llx_facturedet_cashdespro WHERE fk_facture = '.$f['fk_facture'].'';
$res = $db->query($sq);    
} 
//fin eliminando factura   
}
  echo 1; 
}





if($_POST['action'] == 'separar'){

    $mascara = 'SEPA-{000000+000000}';
    $element = 'facture_cashdespro';
    $referencia = 'facnumber';
    $numero = get_next_value($db,$mascara,$element,$referencia ,$where,$soc,$obj->date,'next');

//obteniedo tipo de cambio
$currencyRate = new MultiCurrency($db);
$scambio = $currencyRate->getIdAndTxFromCode($db, 'USD');
$cambio_dolar = 1/$scambio[1];
$multicurrency_tx = $scambio[1]; 
//fin de obteniedo tipo de cambio

         
    $fac = new Facture_cashdespro($db);
    $fac->facnumber = $numero;
    $fac->date = date('Y-m-d H:i:s');
    $fac->date_creation = date('now');
    $fac->socid = $conf->global->POS_CLIENTE_GLOBAL;
    $fac->ref_client = $ref_client;
    $fac->entity = $conf->entity;
    $fac->cond_reglement_id= 0;
    $fac->mode_reglement_id= 0;
    $fac->multicurrency_tx = $multicurrency_tx;
    $fac->multicurrency_code= $conf->global->POS_MONEDA_GLOBAL;
    $fac->type = $conf->global->POS_TIPO_DOC_GLOBAL;
    $fac->fk_mesa = $fk_mesa;
    $fac_id = $fac->create($user);  
if($fac_id > 0){
$fac = new Facture_cashdespro($db);
$fac->fetch($fac_id);   
foreach($_POST['lineas'] as $l){
$linea = new FactureLigne_cashdespro($db);
$linea->fetch($l['id']);
$desc = $linea->desc;
$pu_ht = $linea->subprice; 
$qty = $linea->qty;
$txtva = $linea->tva_tx;
$txlocaltax1=0; 
$txlocaltax2=0; 
$fk_product=$linea->fk_product;
$remise_percent=$linea->remise_percent;
$date_start=''; 
$date_end=''; 
$ventil=0; 
$info_bits=0;
$fk_remise_except='';
$price_base_type='HT'; 
$pu_ttc=0; 
$type=$type; 
$rang=-1; 
$special_code=0;
$origin=''; 
$origin_id=0; 
$fk_parent_line=0; 
$fk_fournprice=null; 
$pa_ht=0;
$label=''; 
$array_options=0; 
$situation_percent=100; 
$fk_prev_id=0; 
$fk_unit = null;
$pu_ht_devise = $linea->multicurrency_subprice;
$fk_soc = $fac->socid;
$fk_user = $user->id;
$fk_vendedor = $fac->user_author;

$resp = $fac->addline($desc, $pu_ht, $qty, $txtva, $txlocaltax1, $txlocaltax2, $fk_product, $remise_percent, $date_start, $date_end, $ventil, $info_bits, $fk_remise_except, $price_base_type, $pu_ttc, $type, $rang, $special_code0, $origin, $origin_id, $fk_parent_line, $fk_fournprice, $pa_ht, $label, $array_options, $situation_percent, $fk_prev_id, $fk_unit, $pu_ht_devise,$fk_soc,$fk_user,$fk_vendedor,$multicurrency_tx);

if($resp > 0){
    $sq = 'DELETE FROM `llx_facturedet_cashdespro` WHERE `llx_facturedet_cashdespro`.`rowid` = '.$l['id'].'';
    $res = $db->query($sq);    
}
}
}
$datos = get_lineas($fk_soc,$fk_vendedor,GETPOST('fk_facture'));
echo json_encode($datos);

}


if($_POST['action'] == 'add_line'){
    
$fk_product = (int)$_POST['fk_product'];
$descuento = (float)$_POST['descuento'];
$tipo_desc = $_POST['tipo_desc'];
$level = (int)$_POST['price_level'];
$cantidad = $_POST['cantidad'];
$fk_facture = (int)$_POST['fk_facture'];
$moneda = $_POST['moneda'];
$fk_soc = (int)$_POST['fk_soc'];
$fk_user = $user->id;
$numero_documento = 'Vilco cena';



if($fk_product > 0){
$type=0; 
$prod = new Product($db);
$prod->fetch($fk_product); 


}else{
$type=1;    
}

//LOGICA DE PRECIOS EN DOLARES Y RANGO POR CANTIDAD
$fk_product = $fk_product;

            function get_level_price($fk_product,$cantidad){
           
            global $db;
            $sql = $db->query('SELECT max(rowid) rowid,nivel_precio,rango1,rango2 FROM `llx_rango_precios` WHERE fk_product='.$fk_product.' AND ('.$cantidad.' BETWEEN rango1 AND rango2)');
            while($obj = $db->fetch_object($sql)){
             $nivel_precio = $obj->nivel_precio;
            }
            return $nivel_precio;
            }

                $nivel = $level;
                if($nivel==0){$nivel=1;}

                if($conf->global->ACTIVAR_RAGO_CANTIDAD_PRECIO){
                 $nivel= get_level_price($fk_product,$cantidad);
                }

                if($nivel==0){$nivel=$level;}

                $multiprices_base_type=$prod->multiprices_base_type[$nivel];
                $multiprices_default_vat_code=$prod->multiprices_default_vat_code[$nivel];
                $multiprices_tva_tx=$prod->multiprices_tva_tx[$nivel]; 

                if($multiprices_base_type==''){$multiprices_base_type='HT';}
                

                //cunado la factura es en dolares
                if($moneda == 'USD'){
                    //obteniedo tipo de cambio
                    $currencyRate = new MultiCurrency($db);
                    $scambio = $currencyRate->getIdAndTxFromCode($db, 'USD');
                    $cambio_dolar = 1/$scambio[1];
                    //fin de obteniedo tipo de cambio
                    $multicurrency_tx = $scambio[1];                  
                    $prod = new Product($db);
                    $prod->fetch($fk_product); 
                    
                    if($prod->multiprices_base_type[$nivel] == 'HT' || $prod->multiprices_base_type[$nivel] == ''){
                    if((float)$prod->multiprices_dolar[$nivel] > 0 ){
                    $pu='';                        
                    $pu_ht_devise = $prod->multiprices_dolar[$nivel];
                    //combirtiendo descuento en porcentaje
                    if($tipo_desc == 'CAN' && $descuento > 0){
                    $precio_c = $prod->multiprices_dolar[$nivel];
                    $descuento_c = $descuento;
                    $dif = $precio_c - $descuento_c;
                    $dif2 = $dif / $precio_c;
                    $descuento = $dif2 * 100;
                    }
                    //fin combirtiendo descuento en porcentaje                 
                    }else{
                    $pu = $prod->multiprices[$nivel];
                    $pu_ht_devise = '';
                    $multicurrency_tx = $multicurrency_tx;
                    //combirtiendo descuento en porcentaje
                    if($tipo_desc == 'CAN' && $descuento > 0){
                        $precio_c = $prod->multiprices[$nivel];
                        $descuento_c = $descuento;
                        $dif = $precio_c - $descuento_c;
                        $dif2 = $dif / $precio_c;
                        $descuento = $dif2 * 100;
                        $descuento = 100 - $descuento;
                        }
                    //fin combirtiendo descuento en porcentaje                      
                    
                    }


                    }
                   
                    if($prod->multiprices_base_type[$nivel] == 'TTC'){
                    if((float)$prod->multiprices_dolar_ttc[$nivel] > 0 ){
                    $pu='';
                    $pu_ht_devise = $prod->multiprices_dolar_ttc[$nivel]; 
                    //combirtiendo descuento en porcentaje
                    if($tipo_desc == 'CAN' && $descuento > 0){
                        $precio_c = $prod->multiprices_dolar_ttc[$nivel];
                        $descuento_c = $descuento;
                        $dif = $precio_c - $descuento_c;
                        $dif2 = $dif / $precio_c;
                        $descuento = $dif2 * 100;
                        $descuento = 100 - $descuento;
                        }
                    //fin combirtiendo descuento en porcentaje                                          
                    }else{
                    $pu = $prod->multiprices_ttc[$nivel];
                    $pu_ht_devise = '';
                    $multicurrency_tx = $multicurrency_tx; 
                     //combirtiendo descuento en porcentaje
                     if($tipo_desc == 'CAN' && $descuento > 0){
                        $precio_c = $prod->multiprices_ttc[$nivel];
                        $descuento_c = $descuento;
                        $dif = $precio_c - $descuento_c;
                        $dif2 = $dif / $precio_c;
                        $descuento = $dif2 * 100;
                        $descuento = 100 - $descuento;
                        }
                    //fin combirtiendo descuento en porcentaje                    
                    
                    }
     
                    }                
              
                }

                //cuando la factura es en colones
                if($moneda == 'CRC'){
                    //obteniedo tipo de cambio
                    $currencyRate = new MultiCurrency($db);
                    $scambio = $currencyRate->getIdAndTxFromCode($db, 'USD');
                    $cambio_dolar = 1/$scambio[1];
                    //fin de obteniedo tipo de cambio
                    $multicurrency_tx = $scambio[1];                     
                    $prod = new Product($db);
                    $prod->fetch($fk_product);
                    if($prod->multiprices_base_type[$nivel] == 'HT' || $prod->multiprices_base_type[$nivel] == ''){

                    if((float)$prod->multiprices[$nivel] > 0){
                    $pu= $prod->multiprices[$nivel];                        
                    $pu_ht_devise = ''; 
                      //combirtiendo descuento en porcentaje
                      if($tipo_desc == 'CAN' && $descuento > 0){
                        $precio_c = $prod->multiprices[$nivel];
                        $descuento_c = $descuento;
                        $dif = $precio_c - $descuento_c;
                        $dif2 = $dif / $precio_c;
                        $descuento = $dif2 * 100;
                        $descuento = 100 - $descuento;
                        }
                    //fin combirtiendo descuento en porcentaje                                                              
                    }else{
                    $cambio = 1/$multicurrency_tx;
                    
                    $pu = $prod->multiprices_dolar[$nivel]*$cambio;
                    $pu_ht_devise = '';
                    $multicurrency_tx = $multicurrency_tx;  
                       //combirtiendo descuento en porcentaje
                       if($tipo_desc == 'CAN' && $descuento > 0){
                        $precio_c = $pu;
                        $descuento_c = $descuento;
                        $dif = $precio_c - $descuento_c;
                        $dif2 = $dif / $precio_c;
                        $descuento = $dif2 * 100;
                        $descuento = 100 - $descuento;
                        }
                    //fin combirtiendo descuento en porcentaje                    
                    

                    }                   
                    }
                
                    if($prod->multiprices_base_type[$nivel] == 'TTC'){
                        if((float)$prod->multiprices[$nivel] > 0){
                            $pu= $prod->multiprices[$nivel];                        
                            $pu_ht_devise = '';
                       //combirtiendo descuento en porcentaje
                       if($tipo_desc == 'CAN' && $descuento > 0){
                        $precio_c = $prod->multiprices[$nivel];
                        $descuento_c = $descuento;
                        $dif = $precio_c - $descuento_c;
                        $dif2 = $dif / $precio_c;
                        $descuento = $dif2 * 100;
                        $descuento = 100 - $descuento;
                        }
                    //fin combirtiendo descuento en porcentaje                             
                            
                            }else{
                            $cambio = 1/$multicurrency_tx;
                            $pu = $prod->multiprices_dolar[$nivel]*$cambio;
                            $pu_ht_devise = '';
                            $multicurrency_tx = $multicurrency_tx; 
                        //combirtiendo descuento en porcentaje
                        if($tipo_desc == 'CAN' && $descuento > 0){
                            $precio_c = $pu;
                            $descuento_c = $descuento;
                            $dif = $precio_c - $descuento_c;
                            $dif2 = $dif / $precio_c;
                            $descuento = $dif2 * 100;
                            $descuento = 100 - $descuento;
                            }
                        //fin combirtiendo descuento en porcentaje                            
                            
                            } 
                    }                

                }
                
//fin de factura en colones

//LOGICA DE PRECIOS EN DOLARES Y RANGO POR CANTIDAD


 //el objeto factura debe ser creado
$fac = new Facture_cashdespro($db);

//obteniendo datos de factura existente
if($fk_facture > 0 ){
    //$fac->id= $_POST['fk_facture'];
    $fac->fetch($fk_facture);
    $fk_soc = $fac->socid;
    $fk_vendedor = $fac->user_author;
}  

//si no hay factura guardadda el id es 0
if($fk_facture <= 0 ){
    $fac->id=0;
    $fk_vendedor = $user->id;
}

$fac->multicurrency_code=$moneda;
$fac->multicurrency_tx= $multicurrency_tx;

//si ya existe el producto sumar la cantidad
$existe = is_product_exist($fk_product,$fk_soc,$fk_vendedor,$fk_facture);
if((int)$existe['id'] > 0){
    $cantidad = $cantidad + $existe['cantidad'];
    if(!isset( $_POST['tva_tx'])){
        $tva_tx = $prod->tva_tx ;  
        }else{
        $tva_tx = $_POST['tva_tx'];        
        }

    $rowid= $existe['id']; 
    $desc=''; 
    $pu= $pu; 
    $qty=$cantidad; 
    $remise_percent=$descuento; 
    $date_start=''; 
    $date_end=''; 
    $txtva=$tva_tx; 
    $txlocaltax1=0; 
    $txlocaltax2=0; 
    $price_base_type=$prod->multiprices_base_type[$nivel]; 
    $info_bits=0; 
    $type= $type; 
    $fk_parent_line=0; 
    $skip_update_total=0; 
    $fk_fournprice=null; 
    $pa_ht=0; 
    $label=''; 
    $special_code=0; 
    $array_options=0; 
    $situation_percent=100; 
    $fk_unit = null; 
    $pu_ht_devise = $pu_ht_devise;
    $fk_soc=$fk_soc; 
    $fk_facture=$fk_facture;
    $notrigger=0;
    
    $resp = $fac->updateline($rowid, $desc, $pu, $qty, $remise_percent, $date_start, $date_end, $txtva, $txlocaltax1, $txlocaltax2, $price_base_type, $info_bits, $type, $fk_parent_line, $skip_update_total, $fk_fournprice, $pa_ht, $label, $special_code, $array_options, $situation_percent, $fk_unit, $pu_ht_devise,$fk_soc,$fk_facture,$fk_user,$fk_vendedor,$moneda,$multicurrency_tx, $notrigger);       
 
  
    if($resp > 0){
    $db->query('UPDATE `llx_facturedet_cashdespro` SET `numero_documento` = "" WHERE `llx_facturedet_cashdespro`.`rowid` ='.$rowid.'');
        //exonerar($fac,$fk_soc,$fk_vendedor,$numero_documento);    
        $datos = get_lineas($fk_soc,$fk_vendedor,$fk_facture);
        echo json_encode($datos);
    }
    else{
    echo -1;
    }  
exit;
}
//fin si ya existe el producto sumar la cantidad;
       

   if((int)$fk_product > 0 ){
    $type =  0;

   }else{
   $type =  1;
   $descripcion = $_POST['select_product'];
   }

    $desc = $descripcion;
    $pu_ht = $pu; 
    $qty = $cantidad; 
    $txtva = $prod->tva_tx; 
    $txlocaltax1=0; 
    $txlocaltax2=0; 
    $fk_product=$fk_product;
    $remise_percent=$descuento; 
    $date_start=''; 
    $date_end=''; 
    $ventil=0; 
    $info_bits=0;
    $fk_remise_except='';
    $price_base_type='HT'; 
    $pu_ttc=0; 
    $type=$type; 
    $rang=-1; 
    $special_code=0;
    $origin=''; 
    $origin_id=0; 
    $fk_parent_line=0; 
    $fk_fournprice=null; 
    $pa_ht=0;
    $label=''; 
    $array_options=0; 
    $situation_percent=100; 
    $fk_prev_id=0; 
    $fk_unit = null;
    $pu_ht_devise = $pu_ht_devise;
    $fk_soc = $fk_soc;
    $fk_user = $fk_user;
    $fk_vendedor = $fk_vendedor;

    $resp = $fac->addline($desc, $pu_ht, $qty, $txtva, $txlocaltax1, $txlocaltax2, $fk_product, $remise_percent, $date_start, $date_end, $ventil, $info_bits, $fk_remise_except, $price_base_type, $pu_ttc, $type, $rang, $special_code0, $origin, $origin_id, $fk_parent_line, $fk_fournprice, $pa_ht, $label, $array_options, $situation_percent, $fk_prev_id, $fk_unit, $pu_ht_devise,$fk_soc,$fk_user,$fk_vendedor,$multicurrency_tx);
    
    if($resp){
    //exonerar($fac,$fk_soc,$fk_vendedor,$numero_documento); 
   $datos = get_lineas($fk_soc,$fk_vendedor,$fk_facture);
   echo json_encode($datos);
    }else{
        echo -1;
    }
}

if($_POST['action'] == 'update_line'){
    $fk_product = (int)$_POST['fk_product'];
    $descuento = (float)$_POST['descuento'];
    $level = (int)$_POST['price_level'];
    $cantidad = $_POST['cantidad'];
    $price_base = $_POST['price_base'];
    $fk_facture = (int)$_POST['fk_facture'];
    $tipo_desc = $_POST['tipo_desc'];    
    $moneda = $_POST['moneda'];
    $fk_soc = (int)$_POST['fk_soc'];
    $id = $_POST['id'];
    $precio = $_POST['precio'];
    $tva_tx = $_POST['tva_tx'];
if($fk_facture > 0){
    $fac = New Facture_cashdespro($db);
    $fac->fetch($fk_facture);
    $fk_user = $user->id;
    $fk_vendedor = $fac->user_author;    
}else{
    $fk_user = $user->id;
    $fk_vendedor = $user->id;       
}

    
    if($fk_product > 0){
    $type=0;   
    }else{
    $type=1;    
    }
    
    
    
    if($level <= 0 ){
    $level=0;
    }
    
    if($level > 0 ){
    $level= $level;
    }    
    
    
    $prod = new Product($db);
    $prod->fetch($fk_product);
    
              //cunado la factura es en dolares
                if($moneda == 'USD'){
                    //obteniedo tipo de cambio
                    $currencyRate = new MultiCurrency($db);
                    $scambio = $currencyRate->getIdAndTxFromCode($db, 'USD');
                    $cambio_dolar = 1/$scambio[1];
                    //fin de obteniedo tipo de cambio
                    $multicurrency_tx = $scambio[1];                  
                    $prod = new Product($db);
                    $prod->fetch($fk_product); 
                    $pu='';                        
                    $pu_ht_devise = $precio;            

                        //combirtiendo descuento en porcentaje
                        if($tipo_desc == 'CAN' && $descuento > 0){
                            $precio_c =$pu_ht_devise;
                            $descuento_c = $descuento;
                            $dif = $precio_c - $descuento_c;
                            $dif2 = $dif / $precio_c;
                            $descuento = $dif2 * 100;
                            $descuento = 100 - $descuento;
                            }
                        //fin combirtiendo descuento en porcentaje               
                }

                //cuanro la factura es en colones
                if($moneda == 'CRC'){
                
                    //obteniedo tipo de cambio
                    $currencyRate = new MultiCurrency($db);
                    $scambio = $currencyRate->getIdAndTxFromCode($db, 'USD');
                    $cambio_dolar = 1/$scambio[1];
                    //fin de obteniedo tipo de cambio
                    $multicurrency_tx = $scambio[1];  
                                   
                    $prod = new Product($db);
                    $prod->fetch($fk_product);
                    $cambio = 1/$multicurrency_tx;
   if((int)$precio == 0 ){                 
                    $pu = $cambio*$prod->multiprices_dolar[$level];
                    $pu_ht_devise = '';
                    $multicurrency_tx = $multicurrency_tx;                        
                        //combirtiendo descuento en porcentaje
                        if($tipo_desc == 'CAN' && $descuento > 0){
                            $precio_c =$pu;
                            $descuento_c = $descuento;
                            $dif = $precio_c - $descuento_c;
                            $dif2 = $dif / $precio_c;
                            $descuento = $dif2 * 100;
                            $descuento = 100 - $descuento;
                            }
                        //fin combirtiendo descuento en porcentaje    
       }else{
                    $pu = $precio;
                    $pu_ht_devise = '';
                    $multicurrency_tx =$multicurrency_tx; 
                         //combirtiendo descuento en porcentaje
                         if($tipo_desc == 'CAN' && $descuento > 0){
                            $precio_c =$pu;
                            $descuento_c = $descuento;
                            $dif = $precio_c - $descuento_c;
                            $dif2 = $dif / $precio_c;
                            $descuento = $dif2 * 100;
                            $descuento = 100 - $descuento;
                            }
                        //fin combirtiendo descuento en porcentaje                                  
       }

                }
//fin de factura en colones
     //el objeto factura debe ser creado
    $fac = new Facture_cashdespro($db);
    
    //obteniendo datos de factura existente
    if($fk_facture > 0 ){
        //$fac->id= $_POST['fk_facture'];
        $fac->fetch($fk_facture);
        $fk_soc = $fac->socid;
    }  
    
    //si no hay factura guardadda el id es 0
    if($fk_facture <= 0 ){
        $fac->id=0;
    }
   
    $fac->multicurrency_code=$moneda;
    $fac->multicurrency_tx= $multicurrency_tx;

        $rowid= $id; 
        $desc=''; 
        $pu= $pu; 
        $qty=$cantidad; 
        $remise_percent=$descuento; 
        $date_start=''; 
        $date_end=''; 
        $txtva=$tva_tx; 
        $txlocaltax1=0; 
        $txlocaltax2=0; 
        $price_base_type=$price_base; 
        $info_bits=0; 
        $type= 0; 
        $fk_parent_line=0; 
        $skip_update_total=0; 
        $fk_fournprice=null; 
        $pa_ht=0; 
        $label=''; 
        $special_code=0; 
        $array_options=0; 
        $situation_percent=100; 
        $fk_unit = null; 
        $pu_ht_devise = $pu_ht_devise;
        $fk_soc=$fk_soc; 
        $fk_facture=$fk_facture;
        $notrigger=0;
        $resp = $fac->updateline($rowid, $desc, $pu, $qty, $remise_percent, $date_start, $date_end, $txtva, $txlocaltax1, $txlocaltax2, $price_base_type, $info_bits, $type, $fk_parent_line, $skip_update_total, $fk_fournprice, $pa_ht, $label, $special_code, $array_options, $situation_percent, $fk_unit, $pu_ht_devise,$fk_soc,$fk_facture,$fk_user,$fk_vendedor,$moneda,$multicurrency_tx);       
     
    
        if($resp > 0){
            $datos = get_lineas($fk_soc,$fk_vendedor,$fk_facture);
            echo json_encode($datos);
        }
        else{
        echo -1;
        dol_print_error($db,$fac->error);
        }     
}
//dol_print_error($db,$fac->error);
//var_dump($fac->error);


function is_product_exist($fk_product,$fk_soc,$fk_vendedor,$fk_facture){
global $db;
$sq = 'SELECT * FROM `llx_facturedet_cashdespro` WHERE `fk_product` = '.$fk_product.' AND fk_soc = '.$fk_soc.' AND fk_vendedor = '.$fk_vendedor.' AND fk_facture = '.$fk_facture.'';
$sql = $db->query($sq);
while($obj = $db->fetch_object($sql)){
$dato =['id'=>$obj->rowid,'cantidad'=>$obj->qty];
}
return $dato;
}


if($_POST['action']=='delete_line'){
    $fk_facture = (int)$_POST['fk_facture'];
    $fk_soc = (int)$_POST['fk_soc'];
if($fk_facture > 0){
 $fac = New Facture_cashdespro($db);
 $fac->fetch($fk_facture); 
 $fk_user = $user->id;
 $fk_vendedor = $fac->user_author;     
}else{
    $fk_user = $user->id;
    $fk_vendedor = $user->id;     
}
  
$id = $_POST['id'];
$sq = 'DELETE FROM `llx_facturedet_cashdespro` WHERE `llx_facturedet_cashdespro`.`rowid` = '.$id.'';
$res = $db->query($sq);
        if($res){
            $datos = get_lineas($fk_soc,$fk_vendedor,$fk_facture);
            echo json_encode($datos);
            }else{
            echo -1;
        }
}

if($_POST['action']=='update_estado'){
$sq = 'UPDATE `llx_facturedet_cashdespro` SET `estado` = "'.GETPOST('estado').'" WHERE `llx_facturedet_cashdespro`.`rowid` = '.GETPOST('id').'';
$res = $db->query($sq);
echo $res;
}


function get_lineas($fk_soc,$fk_vendedor,$fk_facture,$action='default',$rowid=0){
global $db;
$sq = 'SELECT fd.rowid,fd.fk_facture,fd.fk_product,fd.label,fd.description,fd.tva_tx,fd.total_tva,fd.subprice,fd.total_ht,
fd.total_localtax1,fd.total_ttc,fd.remise_percent,fd.multicurrency_code,fd.multicurrency_subprice,
fd.multicurrency_total_ht,fd.multicurrency_total_ttc,fd.multicurrency_total_tva,p.ref p_ref,p.label,fd.qty 
FROM llx_facturedet_cashdespro fd
LEFT JOIN llx_product p ON fd.fk_product=p.rowid'; 
$sq .= ' WHERE fk_soc = '.$fk_soc.' AND fk_vendedor = '.$fk_vendedor.' AND fk_facture = '.$fk_facture.'';
if($action=='modal_product_info'){
$sq .= ' AND fd.rowid = '.$rowid.'';
}
$sq .= ' ORDER BY fd.rowid DESC';

$sql = $db->query($sq);
$descuento = 0;
while($obj = $db->fetch_object($sql)){
$total_tva += $obj->total_tva;
$total_ht += $obj->total_ht;
$total_ttc += $obj->total_ttc;
$multicurrency_total_tva += $obj->multicurrency_total_tva;
$multicurrency_total_ht += $obj->multicurrency_total_ht;
$multicurrency_total_ttc += $obj->multicurrency_total_ttc;

if($obj->remise_percent > 0){
$descuento += ($obj->subprice * $obj->qty) * $obj->remise_percent/100;
$multicurrency_descuento += ($obj->multicurrency_subprice * $obj->qty) * $obj->remise_percent/100;

}else{
$descuento += 0;
$multicurrency_descuento =0;
}

if($obj->multicurrency_code == 'USD'){
  $moneda = '$';
}

if($obj->multicurrency_code == 'CRC'){
  $moneda = '₡';
}
if($obj->fk_product == ''){
 $label = $obj->description;
 $ref = 'Servicio';   
 //llenado estrafield de comodin
 $extrafields['options_comodin'] = 1;

}else{
 $label = $obj->label;
 $ref = $obj->p_ref;       
$product = New Product($db);
$product->fetch($obj->fk_product);
$product->fetch_optionals();  

if($product->id > 0){
$extrafields = $product->array_options;  
}else{
$extrafields = array();   
}

}
$t_iva = $obj->subprice * $obj->tva_tx/100;
$subprice_ttc = $obj->subprice + $t_iva;
$des = $obj->subprice * $obj->remise_percent/100;
$subprice_descuento = $obj->subprice - $des;
$descuento_ht = ($obj->subprice * $obj->qty) * $obj->remise_percent/100;

$lineas['productos'][] =[
    'id'=>$obj->rowid,
    'fk_product'=>$obj->fk_product,
    'ref'=>$ref,    
    'label'=>$label,
    'description'=>$obj->description,   
    'qty'=>$obj->qty, 
    'extrafields'=>$extrafields, 
    'tva_tx'=>$obj->tva_tx,
    's_tva_tx'=>$obj->tva_tx,
    'total_tva'=>price($obj->total_tva),       
    'subprice'=>price($obj->subprice),
    'subprice_ttc'=>price($subprice_ttc), 
    'descuento_ht'=>price($des),
    'descuento'=>price($descuento_ht),        
    'total_ht'=>price($obj->total_ht),
    'total_ttc'=>price($obj->total_ttc),
    's_total_tva'=>price($obj->total_tva),       
    's_subprice'=>$obj->subprice,
    's_subprice_ttc'=>$subprice_ttc,    
    's_total_ht'=>$obj->total_ht,
    's_total_ttc'=>$obj->total_ttc,     
    'remise_percent'=>price($obj->remise_percent),
    's_remise_percent'=>$obj->remise_percent,
    'multicurrency_code'=>$obj->multicurrency_code,
    'multicurrency_subprice'=>price($obj->multicurrency_subprice),
    'multicurrency_total_ht'=>price($obj->multicurrency_total_ht),
    'multicurrency_total_ttc'=>price($obj->multicurrency_total_ttc),
    'multicurrency_total_tva'=>price($obj->multicurrency_total_tva),
    's_tmulticurrency_subprice'=>$obj->multicurrency_subprice,
    's_tmulticurrency_total_ht'=>$obj->multicurrency_total_ht,
    's_tmulticurrency_total_ttc'=>$obj->multicurrency_total_ttc,
    's_tmulticurrency_total_tva'=>$obj->total_tva,    
    'moneda'=>$moneda
];
}
$lineas['totales'] = [
't_total_tva'=>price($total_tva),
't_total_ht' =>price($total_ht),
't_total_ttc'=>price($total_ttc),
's_t_total_tva'=>$total_tva,
's_t_total_ht' =>$total_ht,
's_t_total_ttc'=>$total_ttc,
't_multicurrency_total_tva'=>price($multicurrency_total_tva),
't_multicurrency_total_ht'=>price($multicurrency_total_ht),
't_multicurrency_total_ttc'=>price($multicurrency_total_ttc),
's_t_multicurrency_total_tva'=>$multicurrency_total_tva,
's_t_multicurrency_total_ht'=>$multicurrency_total_ht,
's_t_multicurrency_total_ttc'=>$multicurrency_total_ttc,
'total_descuento'=>price($descuento),
'multicurrency_total_descuento'=>price($multicurrency_descuento),
'moneda'=>$moneda
];


if(count($lineas)==0){
$lineas = $lineas['productos'] = array();
$lineas['totales'] = array();
}

$fac = new Facture_cashdespro($db);
$fac->fetch($fk_facture);
$fac->fetch_thirdparty();
if($fk_facture <= 0){$type = $_POST['tipo'];}else{
$type = $fac->type;    
}

$fac_source = new Facture($db);
$fac_source->fetch($fac->fk_facture_source);


$lineas['fk_facture'] = $fk_facture;
$lineas['type'] = $type;
$lineas['fk_facture_source'] = $fac->fk_facture_source;
$lineas['fk_facture_num'] = $fac->ref;
$lineas['fk_facture_source_num'] = $fac_source->ref;

$vendedor = new User($db);
$vendedor->fetch($fac->user_author);
$lineas['fac_info'] = [
'ref'=>$fac->ref,
'vendedor'=>$vendedor->firtsname.' '.$vendedor->lastname,
'ref_client'=>$fac->ref_client,
'nom'=>$fac->thirdparty->nom,
'fk_soc'=>$fac->thirdparty->id
];


return $lineas;    
}

if($_POST['action'] == 'get_lineas'){
    $fk_facture = (int)$_POST['fk_facture'];
    $fk_soc = (int)$_POST['fk_soc'];
    if($fk_facture > 0){
        $fac = new Facture_cashdespro($db);
        $fac->fetch($fk_facture);
        $fk_user = $user->id;
        $fk_vendedor = $user->user_author;    
    }else{
    $fk_user = $user->id;
    $fk_vendedor = $user->id;           
    }   
$lineas = get_lineas($fk_soc,$fk_vendedor,$fk_facture);
echo json_encode($lineas);
}


if($_POST['action']=='modal_product_info'){
    $fk_facture = (int)$_POST['fk_facture'];
    $rowid = (int)$_POST['rowid'];
    if($fk_facture > 0){
        $fac = new Facture_cashdespro($db);
        $fac->fetch($fk_facture);
        $fk_user = $user->id;
        $fk_vendedor = $fac->user_author; 
        $fk_soc = $fac->socid;    
    }else{
    $fk_user = $user->id;
    $fk_vendedor = $user->id; 
    $fk_soc = (int)$_POST['fk_soc'];       
    }
        
    $lineas = get_lineas($fk_soc,$fk_vendedor,$fk_facture,'modal_product_info',$rowid);
    echo json_encode($lineas);
}


if($_POST['action'] == 'save_fac'){

    $fk_facture = (int)$_POST['fk_facture'];
    $fk_soc = (int)$_POST['fk_soc'];
    $fk_mesa = (int)$_POST['fk_mesa'];
    $ref_client = $_POST['ref_client'];
    $tipo = $_POST['tipo'];
$mascara = 'PROV-{000000+000000}';
$element = 'facture_cashdespro';
$referencia = 'facnumber';
$numero = get_next_value($db,$mascara,$element,$referencia ,$where,$soc,$obj->date,'next');


//obteniedo tipo de cambio
$currencyRate = new MultiCurrency($db);
$scambio = $currencyRate->getIdAndTxFromCode($db, 'USD');
$cambio_dolar = 1/$scambio[1];
//fin de obteniedo tipo de cambio
$multicurrency_tx = $scambio[1]; 


    
if($fk_facture == 0){
$fac = new Facture_cashdespro($db);
$fac->facnumber = $numero;
$fac->date = date('Y-m-d H:i:s');
$fac->date_creation = date('now');
$fac->socid = $_POST['fk_soc'];
$fac->ref_client = $ref_client;
$fac->entity = $conf->entity;
$fac->cond_reglement_id= 0;
$fac->mode_reglement_id= 0;
$fac->multicurrency_tx = $multicurrency_tx;
$fac->multicurrency_code=$_POST['moneda'];
$fac->type = $tipo;
$fac->fk_mesa = $fk_mesa;
$res = $fac->create($user); 
$fk_user = $conf->global->POS_VENDEDOR_GLOBAL;
$fk_vendedor = $conf->global->POS_VENDEDOR_GLOBAL;
if($res > 0){
$sq = 'UPDATE `llx_pos_restaurant_mesas` SET `estado` = "1" WHERE `llx_pos_restaurant_mesas`.`rowid` = '.$fk_mesa.'';
$sql = $db->query($sq);
}

}else{
$fac = new Facture_cashdespro($db);
$fac->fetch($fk_facture);
$fk_user = $user->id;
$fk_vendedor = $fac->user_author;
$fac->type = $tipo;
$fac->ref_client = $ref_client;
$res = $fac->update($user);
}

if($res > 0){
$sq = 'UPDATE llx_facturedet_cashdespro SET fk_facture = '.$res.' WHERE fk_soc='.$_POST['fk_soc'].' AND fk_vendedor='.$user->id.' AND fk_facture=0';
$res = $db->query($sq);
if($res){
    $datos = get_lineas($fk_soc,$fk_vendedor,$fk_facture);
    echo 1;
}else{
$sq;
echo -1;    
}

}



}


