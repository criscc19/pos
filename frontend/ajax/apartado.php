<?php 
/**
 *  SI ES APARTADO
 */
if(DOL_VERSION < 11){
	$facnumber = 'facnumber';
	}else{
	$facnumber = 'ref';  
	}	

if(GETPOST('tipo')==3){

    $mascara = 'APARTADO-{0000+0000}';
    $element = 'facture';
    $referencia = $facnumber;
    $numero = get_next_value($db,$mascara,$element,$referencia ,$where,$soc,$obj->date,'next');
    
    $cash_fac = new Facture_cashdespro($db);
    if($fk_facture == 0){
    $cash_fac->id=0;
    $vendedor = $user->id;
    }else{
    $cash_fac->fetch($fk_facture);
    $vendedor = $vendedor;	
    }

 $date_lim_reglement = strtotime(date('Y-m-d'). ' + 30 days');


    $cash_fac->fetch_lines($fk_soc,$usuario->id);

    $cant_lineas = count($cash_fac->lines);    
    //creando factura
    $fac = new DocumentObject($db);
    $fac->socid          = $fk_soc;	// Put id of third party (rowid in llx_societe table)
    $fac->date           = strtotime(date('Y-m-d'));
    $fac->note_public    = $public_note;
    $fac->note_private   = '';
    $fac->cond_reglement_id   = $conf->global->POS_COND_REGLEMENT_ID_CREDIT;
    $fac->date_lim_reglement   = $date_lim_reglement;
    $fac->mode_reglement_id    = GETPOST('metodo1_id');
    $fac->date_livraison      = strtotime(date('Y-m-d'));
    //$fac->shipping_method_id  = 6;
    $fac->multicurrency_code = $moneda;
    $fac->ref_client = GETPOST('ref_client');
    if($moneda=='CRC'){$fac->multicurrency_tx = 1;}
    if($moneda=='USD'){$fac->multicurrency_tx = $multicurrency_tx;}
    $fac->public_note = $public_note;
    $fac->source         = 7;
    $fac->user_author = $user->id;
    //extrafields
    $fac->array_options[ "options_facturetype" ] = $type;	
    $fac->array_options[ "options_vendedor" ] = GETPOST('fk_vendedor');
    $fac->array_options[ "options_sucursal" ] = $options_sucursal;
    $fac->array_options[ "options_tipo_doc" ] =GETPOST('tipo');
    //creando factura
    if($cant_lineas > 0){
      $idobject=$fac->create($usuario,1);
      }else{
      $idobject= -5;
      $data = ['id'=>$idobject,'tipo'=>GETPOST('tipo'),'error'=>1,'msg'=>'No se puede crear una factura sin lineas'];
      echo json_encode($data); exit;
      }
if($idobject > 0){

    $db->query('UPDATE `llx_facture` SET `act_eco` = "'.$actividad.'" WHERE `llx_facture`.`rowid` = '.$idobject.'');	
    //llenando lineas
    foreach($cash_fac->lines as $line){
    $cash_total_ttc += $line->total_ttc;

    $pu_ht = $line->subprice; 
    $qty = $line->qty; 
    $txtva = $line->tva_tx; 
    $txlocaltax1=0; 
    $txlocaltax2=0; 
    $fk_product=$line->fk_product;
    $remise_percent=$line->remise_percent; 
    $date_start=''; 
    $date_end=''; 
    $ventil=0; 
    $info_bits=0;
    $fk_remise_except='';
    $price_base_type='HT'; 
    $pu_ttc=0; 
    $type=$line->product_type; 
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
    $pu_ht_devise = $line->multicurrency_subprice;
    
    $resp = $fac->addline($desc, $pu_ht, $qty, $txtva, $txlocaltax1, $txlocaltax2, $fk_product, $remise_percent, $date_start, $date_end, $ventil, $info_bits, $fk_remise_except, $price_base_type, $pu_ttc, $type, $rang, $special_code0, $origin, $origin_id, $fk_parent_line, $fk_fournprice, $pa_ht, $label, $array_options, $situation_percent, $fk_prev_id, $fk_unit, $pu_ht_devise);
    $db->query('UPDATE `llx_facturedet` SET `codtax` = "01",vat_src_code="'.$default_vat_code.'" WHERE `llx_facturedet`.`rowid` = '.$resp.'');
 

    }
  }

    $cashid = $_SESSION['TERMINAL_ID'];
    $cash = new Cash($db);
    $cash->fetch($cashid);
    $idwarehouse = $cash->fk_warehouse;
    
    if($idobject > 0){
  

  
    $db->query('UPDATE `llx_facture` SET `act_eco` = "'.$conf->global->FENG_ACTIVIDAD_ECONOMICA_PRINCIPAL.'" WHERE `llx_facture`.`rowid` = '.$idobject.'');	
       $fac->fetch($idobject);
       $fac->fetchFE($idobject);
       $fac->fetch_optionals();
       $fac->user_author = $usuario->id;
       $res = $fac->validate($usuario,$numero, $idwarehouse,1);
       
       $num_lineas = count($fac->lines);
       $f_total = (float)$fac->total_ttc;
       $c_total = $cash_total_ttc;
   //SI SE VALIDO MOVIENDO STOCK A BODEGA DE APARTADOS  
    if($res > 0 && $fac->statut > 0 && $num_lineas == $cant_lineas && $c_total == $f_total){ 

      if($fk_facture == 0){ 
        $sq = 'DELETE FROM llx_facturedet_cashdespro WHERE fk_facture = 0 AND fk_soc='.$fk_soc.' AND fk_vendedor='.$vendedor.'';
       $resl = $db->query($sq);   
       }else{
         $sq = 'DELETE FROM llx_facture_cashdespro WHERE rowid = '.$_POST['fk_facture'].'';
         $resl = $db->query($sq);
         if($resl){
         $sq = 'DELETE FROM llx_facturedet_cashdespro WHERE fk_facture = '.$_POST['fk_facture'].'';
         $resl = $db->query($sq);    
         } 
       }      

      $db->query('UPDATE `llx_facture` SET '.$facnumber.' = "'.$numero.'" WHERE `llx_facture`.`rowid` = '.$idobject.'');	
      $fac->fetch_lines();
      foreach($fac->lines as $line){
        $prod = new Product($db);
        $prod->fetch($line->fk_product);
        
        
        //rebajando stock = 1, sumando stock = 0
        $respu = $prod->correct_stock(
            $usuario,
            $conf->global->POS_APARTADO_ENTREPOT,
            $line->qty,//cantidad
            0,//modo
            'Ingreso ala bodega de apartados por factura id: '.$fac->id.'',
            '',
            'Ingreso ala bodega de apartados por '.$numero.' id: '.$fac->id.''
            );
        
      }
  
  
    }
  //FIN SI SE VALIDO MOVIENDO STOCK A BODEGA DE APARTADOS
  $idobject = $fac->id;
  if($res > 0 && $fac->statut > 0 && $num_lineas == $cant_lineas && $c_total == $f_total){
  
    //guardando relacion de facturas echas desde el cash
  
    set_facture_log($cash->id,$fk_cierre,$idobject,$idwarehouse,GETPOST('tipo'),$usuario->id);
    //fin guardando relacion de gcarueas echas desde el cash
    
/*
BLOQUE DE PAGOS 1
*/
$num_pagos = 3;
for($i=1;$i<=$num_pagos;$i++){
//array pago1
if(GETPOST('pago'.$i) > 0){
	if((float)GETPOST('pago'.$i) > (float)GETPOST('total')){
		$bankaccountid=GETPOST('banco'.$i);
		$res = abs((float)GETPOST('total') - (float)GETPOST('pago'.$i));
		$pago = (float)GETPOST('pago'.$i) - $res;   
    if($moneda=='USD' ){
    $pagos = array(); 
    $multicurrency_pagos =  [$idobject=>$pago];  
    }else{
    $pagos = [$idobject=>$pago]; 
    $multicurrency_pagos = array(); 
    }
		
		//var_dump($pagos);
		}
	if((float)GETPOST('pago'.$i) <= (float)GETPOST('total')){
		
		$bankaccountid=GETPOST('banco'.$i);
    if($moneda=='USD'){
		$pagos = array();
    $multicurrency_pagos = [$idobject=>(float)GETPOST('pago').$i];
    }else{
		$pagos = [$idobject=>(float)GETPOST('pago'.$i)];
    $multicurrency_pagos = array();      
    }
		//var_dump($pagos,'<br><br><br>');
	}

$paiement = new Paiement($db);
$paiement->datepaye     = $now;
$paiement->amounts      = $pagos;   // Array with all payments dispatching with invoice id
$paiement->multicurrency_amounts = $multicurrency_pagos;   // Array with all payments dispatching
$paiement->paiementid   = dol_getIdFromCode($db,GETPOST('metodo'.$i),'c_paiement','code','id',1);
$paiement->num_paiement = GETPOST('vaucher_num');
$paiement->multicurrency_code = $moneda;
$paiement->note         = GETPOST('vaucher_num');
$result1 = $paiement->create($user, 1);


if($result1 >0){
	$resp1 = $paiement->addPaymentToBank($user,'payment',GETPOST('vaucher_num'), GETPOST('banco'.$i),GETPOST('vaucher_num'),GETPOST('vaucher_num'));
	if($resp1 > 0){
	setEventMessages('Pagos ingresados correctamene','');
	//info del pago
	$info_pago1 = new Paiement($db);
	$info_pago1->fetch($result1);

  

/**
 * registro de pagos desde el cash
 */

$sq="SELECT f.rowid as facid, f.".$facnumber.", f.total_ttc, f.fk_statut,pf.rowid pf_id, pf.amount,pf.multicurrency_amount, s.nom as name, p.note
FROM llx_facture as f
JOIN llx_paiement_facture pf ON pf.fk_facture=f.rowid
JOIN llx_societe s ON f.fk_soc = s.rowid
JOIN llx_paiement p ON pf.fk_paiement=p.rowid
WHERE pf.fk_paiement =".$info_pago1->id." GROUP by f.rowid";
$sql = $db->query($sq);
while($obj = $db->fetch_object($sql)){
	if($moneda == 'CRC'){
$fk_paiement_facture = $obj->pf_id;
$fk_user = $user->id;
$terminal = $_SESSION['TERMINAL_ID'];
$idwarehouse = $idwarehouse;
$bank_line = $info_pago1->bank_line;
$fk_facture = $obj->facid;
$fk_paiement = $info_pago1->id;
$fk_cierre = $fk_cierre;
$metodo_pago = $info_pago1->type_code;
$monto = $obj->amount;
$multicurrency_code = $moneda;

set_paiement_log($fk_paiement_facture,$fk_user,$terminal,$idwarehouse,$bank_line,$fk_facture,$fk_paiement,$fk_cierre,$metodo_pago,$monto,$multicurrency_code);

		}
	    if($moneda == 'USD'){			
$fk_paiement_facture = $obj->pf_id;
$fk_user = $obj->user->id;
$terminal = $_SESSION['TERMINAL_ID'];
$idwarehouse = $idwarehouse;
$bank_line = $info_pago1->bank_line;
$fk_facture = $obj->facid;
$fk_paiement = $info_pago1->id;
$fk_cierre = $fk_cierre;
$metodo_pago = $info_pago1->type_code;
$monto = $obj->multicurrency_amount;
$multicurrency_code = $moneda;
set_paiement_log($fk_paiement_facture,$fk_user,$terminal,$idwarehouse,$bank_line,$fk_facture,$fk_paiement,$fk_cierre,$metodo_pago,$monto,$multicurrency_code);			
		}	

	}	
/**
 * fin registro de pagos desde el cash
 */
	 

}
	else{setEventMessages($paiement->error, $paiement->errors, 'errors');
		//dol_print_error($db,$paiement->error);
		}
		
	}
	else{
	setEventMessages($paiement->error, $paiement->errors, 'errors');
	//dol_print_error($db,$paiement->error);
	}
}
}
	
    /*
    FIN BLOQUE DE PAGOS 2
    */
            
    
    //registrado registro de pagos
    set_money_control($idobject,$info_pago1->id,$info_pago2->id,GETPOST('tipo'),
    $info_pago1->bank_line,$info_pago2->bank_line,$info_pago1->amount,$info_pago2->amount,GETPOST('total'),
    GETPOST('vuelto'),GETPOST('multicurrency_tx'),GETPOST('b_moneda1'),GETPOST('b_moneda2'),GETPOST('pago1_dolar'),
    GETPOST('pago2_dolar'),$usuario->id,$_SESSION['TERMINAL_ID'],$fk_cierre,$info_pago1->type_code,$info_pago2->type_code);
    
    //devolviendo datos 
  /*       $facture = New Facture($db);
        $facture->fetch($idobject);
        $resp = $facture->fetch_lines();
        $datos['factura'] = $facture;
        $datos['error'] = ['code'=>$res,'descripcion'=>$fac->error];
        echo json_encode($datos);  */
    //fin devolviendo datos 
        
       }
  
  
       $data = ['id'=>$idobject,'tipo'=>GETPOST('tipo'),'error'=>0,'msg'=>''];
       echo json_encode($data); 
  }else{
    $data = ['id'=>$idobject,'tipo'=>GETPOST('tipo'),'error'=>1,'msg'=>$fac->error];
    echo json_encode($data); 
       }
  
  }
  
  
?>