<?php 
require '../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';
require_once DOL_DOCUMENT_ROOT."/societe/class/societe.class.php";
require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT . '/feng/class/feng.document.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/price.lib.php';
require_once DOL_DOCUMENT_ROOT . '/feng/class/feng.jarvis.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/paiement/class/paiement.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';
require_once(DOL_DOCUMENT_ROOT.'/pos/frontend/class/cashdespro_facture.class.php');
require_once(DOL_DOCUMENT_ROOT.'/pos/backend/class/cash.class.php');
require_once DOL_DOCUMENT_ROOT . '/comm/propal/class/propal.class.php';
if(DOL_VERSION < 11){
	$facnumber = 'facnumber';
	}else{
	$facnumber = 'ref';  
	}	


if(GETPOST('action')=='get_ndc'){
$sq = 'SELECT f.rowid f_id,fd.rowid fd_id,f.'.$facnumber.' facnumber,f.datef,f.total,f.total_ttc,f.multicurrency_total_ht,f.multicurrency_total_ttc,
 f.multicurrency_code,f.ref_client,fd.fk_facture,p.rowid p_id,p.ref,p.label,fd.description,fd.total_ht fd_total_ht,fd.total_ttc fd_total_ttc,fd.qty,
 fd.multicurrency_total_ht fd_multicurrency_total_ht,fd.multicurrency_total_ttc fd_multicurrency_total_ttc,s.nom
FROM llx_facture f 
JOIN llx_facturedet fd ON fd.fk_facture=f.rowid
JOIN llx_societe s ON f.fk_soc = s.rowid 
LEFT JOIN llx_product p ON fd.fk_product = p.rowid
WHERE  type=0 AND f.multicurrency_code="'.GETPOST('moneda').'" AND fk_soc='.GETPOST('fk_soc').' AND f.fk_statut > 0';
if(GETPOST('fecha') !=''){
$sq .= ' AND f.datef BETWEEN "'.GETPOST('fecha').'" AND "'.date('Y-m-d').'"';        
}
if(GETPOST('nombre') !=''){
$sq .= ' AND s.'.GETPOST('col_busc_n').' LIKE "%'.GETPOST('nombre').'%"';        
}
if(GETPOST('producto') !=''){
$sq .= ' AND p.'.GETPOST('col_busc').' LIKE "%'.GETPOST('producto').'%"';        
}

if(GETPOST('ref_fac') !=''){
  $sq .= ' AND f.'.$facnumber.' LIKE "%'.GETPOST('ref_fac').'%"';        
  }

$sql = $db->query($sq);
while($obj = $db->fetch_object($sql)){
if($obj->ref_client != ''){$cliente = $obj->ref_client;}else{$cliente = $obj->nom;}  
    $datos[$obj->f_id] = ['id'=>$obj->f_id,'facnumber'=>$obj->facnumber,'datef'=>$obj->datef,'total'=>price($obj->total_ht),'total_ttc'=>price($obj->total_ttc),
             'multicurrency_total_ht'=>price($obj->total_ttc),'multicurrency_total_ttc'=>price($obj->multicurrency_total_ttc),'multicurrency_code'=>$obj->multicurrency_code,
             'ref_client'=>$obj->ref_client,'nom'=>$obj->nom];
             $sq2 = 'SELECT f.rowid f_id,fd.rowid fd_id,f.'.$facnumber.',f.datef,f.total,f.total_ttc,f.multicurrency_total_ht,f.multicurrency_total_ttc,
             f.multicurrency_code,f.ref_client,fd.fk_facture,p.rowid p_id,p.ref,p.label,fd.description,fd.total_ht fd_total_ht,fd.total_ttc fd_total_ttc,fd.qty,
             fd.multicurrency_total_ht fd_multicurrency_total_ht,fd.multicurrency_total_ttc fd_multicurrency_total_ttc
              FROM llx_facture f 
            JOIN llx_facturedet fd ON fd.fk_facture=f.rowid 
            LEFT JOIN llx_product p ON fd.fk_product = p.rowid
            WHERE  fd.fk_facture='.$obj->f_id.''; 
            $sql2 = $db->query($sq2);
while($obj2 = $db->fetch_object($sql2)){
    $datos[$obj->f_id]['detalle'][] = [
    'id'=>$obj2->fd_id,
    'fk_facture'=>$obj2->fk_facture,
    'fk_product'=>$obj2->p_id,
    'ref'=>$obj2->ref,
    'label'=>$obj2->label,   
    'fd_total_ht'=>price($obj2->fd_total_ht),
    'fd_total_ttc'=>price($obj2->fd_total_ttc),
    'qty'=>$obj2->qty,   
    'fd_multicurrency_total_ht'=>price($obj2->fd_multicurrency_total_ht),
    'fd_multicurrency_total_ttc'=>price($obj2->fd_multicurrency_total_ttc)];   
}                  
      
}


echo json_encode($datos);
}

if(GETPOST('action')=='aplicar_ndc'){
  $mascara = 'NDC-{000000+000000}';
  $element = 'facture_cashdespro';
  $referencia = 'facnumber';
  $numero = get_next_value($db,$mascara,$element,$referencia ,$where,$soc,$obj->date,'next');
  
  $cur = new Multicurrency($db);
  $cur->fetch($conf->global->ID_MULTIMONEDA);
  $cambio = $cur->rates[0]->rate;
  $multicurrency_tx = $cambio; 
  $cambio_dolar = 1/$cambio;

//obtebniendo datos de la factura
$facture = New Facture($db);
$facture->fetch(GETPOST('fk_facture'));




  $fac = new Facture_cashdespro($db);
  $fac->facnumber = $numero;
  $fac->date = date('Y-m-d H:i:s');
  $fac->date_creation = date('now');
  $fac->socid = $facture->socid;
  $fac->ref_client = $facture->ref_client;
  $fac->entity = $conf->entity;
  $fac->cond_reglement_id= 0;
  $fac->mode_reglement_id= 0;
  $fac->multicurrency_tx = $facture->multicurrency_tx;
  $fac->multicurrency_code=$facture->multicurrency_code;
  $fac->fk_facture_source=GETPOST('fk_facture');  
  $fac->type = 2;
  if(GETPOST('fk_facture') > 0 ) {$res = $fac->create($user);};
if($res > 0){
//obteniendo lineas de factura
$sq = 'SELECT * FROM llx_facturedet WHERE fk_facture='.GETPOST('fk_facture').' AND rowid IN('.implode(',',$_POST['lineas']).')';
$sql = $db->query($sq);

while($obj = $db->fetch_object($sql)){
  if($obj->fk_product > 0){
    $type = 0;
  }else{
  $type = 1;  
  }
  //llenado lineas factura cash  
  $desc = $obj->descripcion;
  $pu_ht = $obj->subprice; 
  $qty = $obj->qty; 
  $txtva = $obj->tva_tx; 
  $txlocaltax1=0; 
  $txlocaltax2=0; 
  $fk_product=$obj->fk_product;
  $remise_percent=$obj->remise_percent; 
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
  $pu_ht_devise = $obj->multicurrency_subprice;
  $fk_soc = $facture->socid;
  $fk_user = $user->id;
  $fk_vendedor = $user->id;
 
  $resp = $fac->addline($desc, $pu_ht, $qty, $txtva, $txlocaltax1, $txlocaltax2, $fk_product, $remise_percent, $date_start, $date_end, $ventil, $info_bits, $fk_remise_except, $price_base_type, $pu_ttc, $type, $rang, $special_code0, $origin, $origin_id, $fk_parent_line, $fk_fournprice, $pa_ht, $label, $array_options, $situation_percent, $fk_prev_id, $fk_unit, $pu_ht_devise,$fk_soc,$fk_user,$fk_vendedor,$multicurrency_tx);

}

$datos = get_lineas($facture->socid,$user->id,$res,GETPOST('fk_facture'));
echo json_encode($datos);
}else{

  $data = ['id'=>$res,'tipo'=>GETPOST('tipo'),'error'=>1,'msg'=>$fac->error];
	echo json_encode($data); exit;
}


};









function get_lineas($fk_soc,$fk_vendedor,$fk_facture,$fk_facture_source){
  global $db;
  $sq = 'SELECT fd.rowid,fd.fk_facture,fd.fk_product,fd.label,fd.description,fd.tva_tx,fd.total_tva,fd.subprice,fd.total_ht,
  fd.total_localtax1,fd.total_ttc,fd.remise_percent,fd.multicurrency_code,fd.multicurrency_subprice,
  fd.multicurrency_total_ht,fd.multicurrency_total_ttc,fd.multicurrency_total_tva,p.ref p_ref,p.label,fd.qty 
  FROM llx_facturedet_cashdespro fd
  LEFT JOIN llx_product p ON fd.fk_product=p.rowid'; 
  
  $sq .= ' WHERE fk_soc = '.$fk_soc.' AND fk_vendedor = '.$fk_vendedor.' AND fk_facture = '.$fk_facture.'';
  $sql = $db->query($sq);
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
  
  $product = New Product($db);
  $product->fetch($obj->fk_product);
  $product->fetch_optionals();  
  
  if($product->id > 0){
  $extrafields = $product->array_options;  
  }else{
  $extrafields = array();   
  }

  $lineas['productos'][] =[
      'id'=>$obj->rowid,
      'fk_product'=>$obj->fk_product,
      'ref'=>$obj->p_ref,    
      'label'=>$obj->label,
      'description'=>$obj->description,   
      'qty'=>$obj->qty, 
      'extrafields'=>$extrafields,      
      'tva_tx'=>$obj->tva_tx,
      's_tva_tx'=>$obj->tva_tx,
      'total_tva'=>price($obj->total_tva),       
      'subprice'=>price($obj->subprice),
      'total_ht'=>price($obj->total_ht),
      'total_ttc'=>price($obj->total_ttc),
      's_total_tva'=>price($obj->total_tva),       
      's_subprice'=>$obj->subprice,
      's_total_ht'=>$obj->total_ht,
      's_total_ttc'=>$obj->total_ttc,     
      'remise_percent'=>$obj->remise_percent,
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
  }
  if(count($lineas)==0){
  $lineas = $lineas['productos'] = array();
  $lineas['totales'] = array();
  }
  
  $fac = new Facture_cashdespro($db);
  $fac->fetch($fk_facture);

  $fac_source = new Facture($db);
  $fac_source->fetch($fac->fk_facture_source);
  $lineas['fk_soc'] = $fk_soc;
  $lineas['fk_facture'] = $fk_facture;
  $lineas['type'] = $fac->type;
  $lineas['fk_facture_source'] = $fk_facture_source;
  $lineas['fk_facture_num'] = $fac->ref;
  $lineas['fk_facture_source_num'] = $fac_source->ref;
  return $lineas;    
  }
  
?>