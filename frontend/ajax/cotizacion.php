<?php
/**
 * SI ES COTIZACION
 */
if(GETPOST('tipo')==4){
$cash_fac = new Facture_cashdespro($db);

  if($fk_facture == 0){
    $cash_fac->id=0;
    $vendedor = $user->id;
    }else{
    $cash_fac->fetch($fk_facture);
    $vendedor = $vendedor;	
    }
    $cash_fac->fetch_lines($fk_soc,$usuario->id);
    $cant_lineas = count($cash_fac->lines);

    $cant_lineas = count($cash_fac->lines);

//creando permisos necesarios
$usuario->rights->facture = new stdClass();
$usuario->rights->facture->invoice_advance = new stdClass();
$usuario->rights->facture->invoice_advance->validate=1;
$usuario->rights->facture->creer = 1;

$usuario->rights->propal = new stdClass();
$usuario->rights->propal->propal_advance = new stdClass();
$usuario->rights->propal->propal_advance->validate=1;
$usuario->rights->propal->creer = 1;


$mascara = $conf->global->PROPALE_SAPHIR_MASK;
$element = 'propal';
$referencia = 'ref';
$numero = get_next_value($db,$mascara,$element,$referencia ,$where,$soc,$obj->date,'next');


$object = new Propal($db);
$object->ref = $numero;
$object->entity = (GETPOSTISSET('entity')?GETPOST('entity', 'int'):$conf->entity);
$object->ref_client = GETPOST('ref_client');
$object->datep = strtotime(date('Y-m-d'));
$object->socid = GETPOST('fk_soc','int');
$object->author = $usuario->id; // deprecated
$object->note_public = GETPOST('public_note','none');
$object->multicurrency_code = $moneda;
if($moneda=='CRC'){$object->multicurrency_tx = 1;}
if($moneda=='USD'){$object->multicurrency_tx = $multicurrency_tx;}
$object->array_options[ "options_tipo_doc" ] =GETPOST('tipo');
$object->array_options[ "options_fk_cierre" ] =GETPOST('fk_cierre');  


if($cant_lineas > 0){
	$id=$object->create($usuario,1);
	}else{
	$id= -5;
	$data = ['id'=>$id,'tipo'=>GETPOST('tipo'),'error'=>1,'msg'=>'No se puede crear una factura sin lineas'];
	echo json_encode($data); exit;
	}


if($id > 0){



//llenando lineas
foreach($cash_fac->lines as $line){
  $cash_total_ttc += $line->total_ttc;  
if($moneda == 'CRC'){
 $pu_ht = $line->subprice;
 $pu_ht_devise= '';
}else{
  $pu_ht = '';
  $pu_ht_devise=$line->multicurrency_subprice;  
}
  $desc=$line->desc; 
  $pu_ht=$pu_ht; 
  $qty=$line->qty; 
  $txtva=$line->tva_tx; 
  $txlocaltax1=0.0; 
  $txlocaltax2=0.0; 
  $fk_product=$line->fk_product; 
  $remise_percent=$line->remise_percent; 
  $price_base_type='HT'; 
  $pu_ttc=0.0; 
  $info_bits=0; 
  $type=$line->product_type; 
  $rang=-1; 
  $special_code=0; 
  $fk_parent_line=0; 
  $fk_fournprice=0; 
  $pa_ht=0; 
  $label='';
  $date_start=''; 
  $date_end='';
  $array_options=0; 
  $fk_unit=null; 
  $origin=''; 
  $origin_id=0; 
  $pu_ht_devise=$pu_ht_devise; 
  $fk_remise_except=0;
  $resp = $object->addline($desc, $pu_ht, $qty, $txtva, $txlocaltax1, $txlocaltax2, $fk_product, $remise_percent, $price_base_type='HT', $pu_ttc, $info_bits, $type, $rang=-1, $special_code, $fk_parent_line, $fk_fournprice, $pa_ht, $label,$date_start, $date_end,$array_options, $fk_unit, $origin, $origin_id, $pu_ht_devise, $fk_remise_except);
  if($resp > 0){
  $db->query('UPDATE `llx_propaldet` SET `codtax` = "01",vat_src_code="'.$default_vat_code.'" WHERE `llx_propaldet`.`rowid` = '.$resp.'');
  
  }else{
 var_dump($object->error);
  }
  
}
$object->fetch($id);
$res = $object->valid($usuario);
$num_lineas = count($object->lines);
$f_total = (float)$object->total_ttc;
$c_total = $cash_total_ttc;

if($res > 0 && $object->statut > 0 && $num_lineas == $cant_lineas && $c_total == $f_total){
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
  $data = ['id'=>$id,'tipo'=>GETPOST('tipo'),'error'=>0,'msg'=>''];

  echo json_encode($data); 

}else{

   $data = ['id'=>$id,'tipo'=>GETPOST('tipo'),'error'=>1,'msg'=>$object->error];
   echo json_encode($data);  
}


  }else{

   $data = ['id'=>$id,'tipo'=>GETPOST('tipo'),'error'=>1,'msg'=>$object->error];
   echo json_encode($data);   
  }



}


/**
 * FIN SI ES COTIZACION
 */

?>