<?php
function exonerar($object,$fk_soc,$fk_vendedor,$numero_documento){
global $db;
$object->fetch_lines($fk_soc,$fk_vendedor);
$object->socid = $fk_soc;
$cli = New Societe($db);
$cli->fetch($fk_soc);
$object->thirdparty = $cli;
foreach($object->lines as $line){

if($line->numero_documento ==''){
$exonerado = new Exoneracion($db);   
$exonerado->fetch($numero_documento); 
$prod = New Product($db);
$prod->fetch($line->fk_product);
$tva_tx = $line->tva_tx;
$qty = $line->qty;
$pu = $line->subprice;
$remise_percent = $line->remise_percent;
$pu_ht_devise = $line->pu_ht_devise;
$multicurrency_tx = $line->multicurrency_tx;
$remise_percent = $line->remise_percent;
$total_ht = $line->total_ht;
$total_ttc = $line->total_ttc;
$total_tva = $line->total_tva;
$pu_ht_devise = $line->multicurrency_subprice;

$exo = $exonerado->arrayObject[0];
$productos = explode(',',$exo->productList);

if(in_array($line->fk_product,$productos)){
$exoneradoPorcentaje = $exo->porcentaje;
}else{
$exoneradoPorcentaje = -1;    
} 

if($exo->productList=='all'){
$exoneradoPorcentaje = $exo->porcentaje;
}
           //veo en que estoy vendiendo el producto
           $sellingIn = (!empty($conf->global->PRODUIT_SELL_DEVICE_CODE)) ? $conf->global->PRODUIT_SELL_DEVICE_CODE : $conf->currency;

           //obtengo precio de venta
          
           $postSubprice = $line->subprice;

           $codtax = 01;

           $explodePath = explode('/',DOL_DOCUMENT_ROOT);



           $txtva = price2num($line->tva_tx,2);


            //valida si hay una exoneracion
            $exoPercent = 0;
           if($exoneradoPorcentaje > -1){
/*                $exoPercent = price2num((($txtva*$exoneradoPorcentaje)/100),2);
               $txtva=$txtva-$exoPercent ;
               $txtva=price2num($txtva,2);        */  

                $txtva=price2num($txtva - $exoneradoPorcentaje);
           }

           $multicurrency_tx = $object->multicurrency_tx;
           $remise_percent = $line->remise_percent;

            $remise_percent = $line->remise_percent; 
         
              
           $tabprice = calcul_price_total($qty, $pu, $remise_percent, $txtva, $txlocaltax1, $txlocaltax2, 0, $price_base_type, $info_bits, $product_type, $mysoc, $localtaxes_type, $situation_percent, $multicurrency_tx, $pu_ht_devise); 
          
           $pu_ht = price2num($tabprice[3],'MU');
           $pu_ht_devise = price2num($tabprice[19],'MU');

           $subtotalGlobal = ($pu_ht*$qty);
           $multicurrency_subtotalGlobal = ($pu_ht_devise*$qty);

           $subtotalGlobal = price2num($subtotalGlobal,'MU');
           $multicurrency_subtotalGlobal = price2num($multicurrency_subtotalGlobal,'MU');

               $descuentoLocal = 0;
               $descuentoLocal_Multicurrency = 0;
  

           $total_ht  = price2num(abs($tabprice[0])-abs($descuentoLocal),'MU');

           if($remise_percent >= 100){
               //$subtotalGlobal = price2num($subtotalGlobal,'MU');
               //$multicurrency_subtotalGlobal = price2num($multicurrency_subtotalGlobal,'MU');
               $total_tva = price2num(abs($tabprice[1]),'MU');
               $multicurrency_total_tva = price2num($tabprice[17],'MU');
               if($txtva > 0){
                   $total_tva = (abs($subtotalGlobal)*$txtva)/100;
                   $total_tva = price2num($total_tva,'MU');

                   $multicurrency_total_tva = (abs($multicurrency_subtotalGlobal)*$txtva)/100;
                   $multicurrency_total_tva = price2num($multicurrency_total_tva,'MU');
               }
               
           }else{
               $total_tva = price2num(abs($tabprice[1]),'MU');
               $multicurrency_total_tva = price2num($tabprice[17],'MU');
           }
           
           $total_ttc = price2num($total_ht + $total_tva,'MU');
           $total_localtax1 = price2num($tabprice[9],'MU');
           $total_localtax2 = price2num($tabprice[10],'MU'); 

           // MultiCurrency
           $multicurrency_total_ht  = price2num(abs($tabprice[16])-abs($descuentoLocal_Multicurrency),'MU');
           
           $multicurrency_total_ttc = price2num($multicurrency_total_ht + $multicurrency_total_tva,'MU');

           $exo_tva=price2num($line->tva_tx,2);
           if($remise_percent>0){
               $descuento=$remise_percent;
           }else{
               $descuento=0;
           }
           $tabprice = calcul_price_total($qty, $pu_ht, $descuento, $exo_tva, $txlocaltax1, $txlocaltax2, 0, $price_base_type, $info_bits, $product_type, $mysoc, $localtaxes_type, $situation_percent, $multicurrency_tx, $pu_ht_devise); 
           $exo_total_ht=price2num($subtotalGlobal,'MU');
           $exo_total_tva=price2num(abs($tabprice[1]),'MU');
           $exo_total_ttc=price2num($exo_total_ht + $exo_total_tva,'MU');

           $multicurrency_exo_total_ht=price2num(abs($tabprice[16])-abs($descuentoLocal_Multicurrency),'MU');
           $multicurrency_exo_total_tva=price2num($tabprice[17],'MU');
           $multicurrency_exo_total_ttc=price2num($multicurrency_exo_total_tva + $multicurrency_exo_total_ttc,'MU');

           $line->tva_tx = $txtva;

           //se saca exo
           if($exoneradoPorcentaje > -1){
               $tipo_dococumento=$exo->tipoDocumento;
               $numero_documento=$exo->numeroDocumento;
               $nombre_institucion=$exo->nombreInstitucion;
               $fecha_emision=$exo->fechaEmision;
               $porcentaje=$exo->porcentaje;
               $monto_exoneracion=price2num(($exo_total_tva-$total_tva),'MU');
               $multicurrency_monto_exoneracion=price2num(($multicurrency_exo_total_tva-$multicurrency_total_tva),'MU');
              
           }else{
               $tipo_dococumento='';
               $numero_documento='';
               $nombre_institucion='';
               $fecha_emision='null';
               $porcentaje='0';
               $monto_exoneracion='0';
               $multicurrency_monto_exoneracion='0';
               $timbre_tx=0;
               $timbre_tva=0;
               $multicurrency_timbre_tva=0;                               
           }            
    

           $result1 = $line->update($user, 1);
           
           $result2 = $object->update_price(1,'2',0,$mysoc);   

           $devimp = 0;
           if(!empty($conf->global->FENG_ENABLE_DEVIMP)){
               if(!empty($object->line->fk_product)){
                   $devImpEstado = (!empty($product->array_options['devimp']))? $product->array_options['devimp'] : 1;

               }else{
                   $devImpEstado = 1;
               }

               if($devImpEstado == 1){
                   $devimp = ($object->multicurrency_code == 'CRC') ? $object->line->total_tva : $object->line->multicurrency_total_tva;
               }

               $devimp = price2num($devimp,'MU');
           }

           $exonerado->updateCamposDet($line->id,$object->table_element_line,$tipo_dococumento,$numero_documento,$nombre_institucion,$fecha_emision,$porcentaje,$monto_exoneracion,$exo_tva,$exo_total_ht,$exo_total_tva,$exo_total_ttc,$multicurrency_exo_total_ht,$multicurrency_exo_total_tva,$multicurrency_exo_total_ttc,$multicurrency_monto_exoneracion,$codtax,$devimp);   
        }
    }
    return $txtva;
}
?>