<?php
if(DOL_VERSION < 11){
    $facnumber = 'facnumber';
    }else{
    $facnumber = 'ref';  
    }

function get_actividad_principal($code){
if(is_array($code)){$code = implode(',',$code);}else{$code=$code;}
global $db;
$actividad = array();
$sq = 'SELECT * FROM `llx_c_actividad_economica` WHERE `code` IN ('.$code.') AND active=1';

$sql = $db->query($sq ); 
while($obj = $db->fetch_object($sql)){
$actividad []= ['id'=>$obj->rowid,'code'=>$obj->code,'label'=>$obj->label]; 
} 
return $actividad;
};

function select_actividad_principal($code){
    global $db;
    $actividad = array();
    $sq = 'SELECT * FROM `llx_c_actividad_economica`';
    $sql = $db->query($sq ); 
    while($obj = $db->fetch_object($sql)){
    $actividad []= ['id'=>$obj->rowid,'code'=>$obj->code,'label'=>$obj->label]; 
    } 
    return $actividad;
    };
    
function select_metodo_pago($sqs=''){
    global $db,$langs;
    $sql = "SELECT id, code, libelle as label, type, active";
    $sql.= " FROM ".MAIN_DB_PREFIX."c_paiement";
    $sql.= " WHERE active=1";
    $sql.= $sqs;    
    //if ($active >= 0) $sql.= " AND active = ".$active;
    $metodos = [];
    $resql = $db->query($sql);
    while($obj = $db->fetch_object($resql)){
        $label = ($langs->transnoentitiesnoconv("PaymentTypeShort".$obj->code) != ("PaymentTypeShort".$obj->code) ? $langs->transnoentitiesnoconv("PaymentTypeShort".$obj->code) : ($obj->label != '-' ? $obj->label : ''));   
    $metodos []= ['id'=>$obj->id,'code'=>$obj->code,'label'=>$label];
    
    }
    return $metodos;
}

function select_bancos($sqs=''){
global $db,$facnumber;    
$sq = "SELECT * FROM llx_bank_account WHERE 1";
$sq .=$sqs;
$sql = $db->query($sq);
$bancos = [];
while($obj=$db->fetch_object($sql)){
$bancos[]=['id'=>$obj->rowid,'ref'=>$obj->ref,'label'=>$obj->label,'currency_code'=>$obj->currency_code,'courant'=>$obj->courant];
}
return $bancos;
}




function get_numero_facturas(){
    require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';   
    global $db;
    $soc = new Societe($db);
    $soc->fetch($_SESSION["CASHDESKPRO_ID_THIRDPARTY"]);
    $limite = $soc->outstanding_limit;
    $sq .= ' SELECT f.rowid fid,f.'.$facnumber.',f.entity,f.total_ttc,f.datef,f.date_lim_reglement,f.fk_soc,
    multicurrency_code,multicurrency_total_ttc,
    (SELECT IF(sr.amount_ttc IS NOT NULL, SUM(sr.amount_ttc), 0) AS amount_ttc FROM llx_societe_remise_except sr WHERE f.fk_soc=sr.fk_soc AND sr.fk_facture=f.rowid) ndc,
    (SELECT IF(pf.amount IS NOT NULL, SUM(pf.amount),0) FROM llx_paiement_facture pf WHERE pf.fk_facture=f.rowid ) pagos,
    (f.total_ttc - (SELECT IF(sr.amount_ttc IS NOT NULL, SUM(sr.amount_ttc), 0) AS amount_ttc FROM llx_societe_remise_except sr WHERE f.fk_soc=sr.fk_soc AND sr.fk_facture=f.rowid) - (SELECT IF(pf.amount IS NOT NULL, SUM(pf.amount),0) FROM llx_paiement_facture pf WHERE pf.fk_facture=f.rowid )) totales,
    (SELECT IF(sr.multicurrency_amount_ttc IS NOT NULL, SUM(sr.multicurrency_amount_ttc), 0) AS multicurrency_amount_ttc FROM llx_societe_remise_except sr WHERE f.fk_soc=sr.fk_soc AND sr.fk_facture=f.rowid) multicurrency_ndc,
    (SELECT IF(pf.multicurrency_amount IS NOT NULL, SUM(pf.multicurrency_amount),0) FROM llx_paiement_facture pf WHERE pf.fk_facture=f.rowid ) multicurrency_pagos,
    (f.multicurrency_total_ttc - (SELECT IF(sr.multicurrency_amount_ttc IS NOT NULL, SUM(sr.multicurrency_amount_ttc), 0) AS multicurrency_amount_ttc FROM llx_societe_remise_except sr WHERE f.fk_soc=sr.fk_soc AND sr.fk_facture=f.rowid) - (SELECT IF(pf.multicurrency_amount IS NOT NULL, SUM(pf.multicurrency_amount),0) FROM llx_paiement_facture pf WHERE pf.fk_facture=f.rowid )) multicurrency_totales,';
    $sq .= 'fex.apartado';
    $sq .= ' FROM llx_facture f ';
    $sq .= ' LEFT JOIN llx_facture_extrafields fex ON fex.fk_object=f.rowid';
    $sq .= ' WHERE f.fk_statut = 1 AND f.paye=0 AND type=0 AND f.fk_soc = '.$_SESSION["CASHDESKPRO_ID_THIRDPARTY"] .' GROUP BY f.rowid';
    $sql = $db->query($sq);
    while($obj = $db->fetch_object($sql)){
    $total_pendiente += $obj->totales;	
    $limite = $soc->outstanding_limit;
    $tcredito = $limite - $total_pendiente;
    if($tcredito < 0){$mensaje =  '<h5><span style="color:red">Limite de credito sobre pasado por: '.price($tcredito).'</span></h5>';} 
    $numero = $db->num_rows($sql);
     $datos = ['num_factura'=>$numero,'credito'=>$tcredito,'mensaje'=>$mensaje];
     return $datos;
    }
}


function get_img_product($index=0,$fk_product)
{	
    global $db,$conf, $langs, $user;	
    $prod = new Product($db);
    $prod->fetch($fk_product);
    //obteniendo imagen
    $sdir = $conf->product->multidir_output[$prod->entity];
    $dir = $sdir . '/';
    $pdir = '/';
    $dir .= get_exdir(0,0,0,0,$prod,'product').$prod->ref.'/';
    $pdir .= get_exdir(0,0,0,0,$prod,'product').$prod->ref.'/';
    
    
    // Defined relative dir to DOL_DATA_ROOT
    $relativedir = '';
    
    if ($dir)
    {
    $relativedir = preg_replace('/^'.preg_quote(DOL_DATA_ROOT,'/').'/', '', $dir);
    $relativedir = preg_replace('/^[\\/]/','',$relativedir);
    $relativedir = preg_replace('/[\\/]$/','',$relativedir);
    }
    
    $dirthumb = $dir.'thumbs/';
    $pdirthumb = $pdir.'thumbs/';
    
    $return ='<!-- Photo -->'."\n";
    $nbphoto=0;
    
    $filearray=dol_dir_list($dir,"files",0,'','(\.meta|_preview.*\.png)$',$sortfield,(strtolower($sortorder)=='desc'?SORT_DESC:SORT_ASC),1);
    $img = $url = str_replace(" ", "%20",$filearray[$index]['name']);
    $ext = explode('.',$filearray[$index]['name']);
    $img_nom = $ext[0];
    $img_ext = $ext[1];
    if(count($filearray) > 0){
    $protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';	
    $url = $protocol.$_SERVER['HTTP_HOST'].DOL_URL_ROOT.'/documents/produit/'.urlencode($prod->ref).'/'.urlencode($img).'';
    $realpath = DOL_MAIN_URL_ROOT.'/viewimage.php?modulepart=product&entity='.$prod->entity.'&file='.urlencode($prod->ref).'/'.urlencode($img);
    $realpath_thumb = DOL_MAIN_URL_ROOT.'/viewimage.php?modulepart=product&entity='.$prod->entity.'&file='.urlencode($prod->ref).'/thumbs/'.urlencode($img_nom.'_mini.'.$img_ext);	
    }
    if(count($filearray) <= 0){
    $protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';	
    $url = $protocol.$_SERVER['HTTP_HOST'].DOL_URL_ROOT.'/public/theme/common/nophoto.png';	
    $realpath = DOL_MAIN_URL_ROOT.'/viewimage.php?modulepart=product&file='.urlencode('noimage.jpg');
    $realpath_thumb = DOL_MAIN_URL_ROOT.'/viewimage.php?modulepart=product&file='.urlencode('noimage.jpg');
    }
    $imagen = new stdClass();
    $imagen->url = (string)$url;
    $imagen->nombre = $filearray[$index]['name'];
    $imagen->index = $index;
    $imagen->realpath = $realpath;
    $imagen->imagenes = $filearray;
    $imagen->protocol = $protocol;
    $imagen->realpath_thumb = $realpath_thumb;
    return $imagen;
}

function select_denominaciones($sqls=''){
    global $db;
    $sql = "SELECT rowid, code, label, active";
    $sql.= " FROM ".MAIN_DB_PREFIX."c_denominaciones";
    $sql.= " WHERE active=1";
    $sql.= $sqls;
    $resql = $db->query($sql);
    while($obj = $db->fetch_object($resql)){
        $denominaciones []= ['id'=>$obj->rowid,'code'=>$obj->code,'label'=>$obj->label]; 
    }
    return $denominaciones;
}

function get_facture_info($fk_facture){
  $invoice = new Facture($db);
  $invoice->fetch($fk_facture);

  $paiement = $invoice->getSommePaiement();
  $creditnotes = $invoice->getSumCreditNotesUsed();
  $deposits = $invoice->getSumDepositsUsed();
  $alreadypayed = price2num($paiement + $creditnotes + $deposits, 'MT');
  $remaintopay = price2num($invoice->total_ttc - $paiement - $creditnotes - $deposits, 'MT'); 
  $datos = [];
  $dato = ['pagos'=>$paiement,'ndc'=>$creditnotes,'depositos'=>$deposits,'pagado'=>$alreadypayed,'pendiente'=>$remaintopay];

}

function getCategories($id){
    global $db;
    $sq = 'SELECT c.rowid,c.label,c.fk_parent FROM llx_categorie_product cp 
    JOIN llx_categorie c ON cp.fk_categorie=c.rowid
    WHERE cp.fk_product='.$id.'';
    $sql = $db->query($sq);
    while($obj = $db->fetch_object($sql)){
    $cate[] = ['id'=>$obj->rowid,'label'=>$obj->label,'fk_parent'=>$obj->fk_parent];
    }
    return $cate;
    }


function get_descuento($fk_soc,$aplicado=0){

global $db,$facnumber;    
$sq = 'SELECT fx.rowid fx_id,fx.amount_ht,fx.amount_tva,fx.amount_ttc,f2.rowid ndc_id,f3.'.$facnumber.' ndc_facnumber,f2.rowid ndc_source_id,f2.'.$facnumber.' ndc_source_facnumber,f.rowid desc_aplicado_id,f.'.$facnumber.' desc_aplicado_facnumber,f2.ref_client
FROM llx_societe_remise_except  fx
JOIN llx_facture f3 ON fx.fk_facture_source=f3.rowid
JOIN llx_facture f2 ON f3.fk_facture_source = f2.rowid
LEFT JOIN llx_facture f ON fx.fk_facture = f.rowid';
if($aplicado == 0){
$sq .= ' WHERE fk_facture IS NULL AND fx.fk_soc = '.$fk_soc.'';    
}else{
$sq .= ' WHERE fk_facture='.$aplicado.' AND fx.fk_soc = '.$fk_soc.'';
}
$datos = [];
$sql = $db->query($sq);
while($obj = $db->fetch_object($sql)){
$datos[] = [
    'decuento_id'=>$obj->fx_id,
    'amount_ht'=>$obj->amount_ht,
    'amount_tva'=>$obj->amount_tva,
    'amount_ttc'=>$obj->amount_ttc,
    'ndc_id'=>$obj->ndc_id,
    'ndc_facnumber'=>$obj->ndc_facnumber,
    'ndc_source_id'=>$obj->ndc_source_id,    
    'ndc_source_facnumber'=>$obj->ndc_source_facnumber,
    'desc_aplicado_id'=>$obj->desc_aplicado_id,
    'desc_aplicado_facnumber'=>$obj->desc_aplicado_facnumber,
    'ref_client'=>$obj->ref_client    
];

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

/**Funcion encargada de eliminar de la tabla llx_banco_comision */ 
function eliminar_llx_banco_comision($rowid){
    
    global $db;
    $sq = 'DELETE FROM llx_banco_comision cs  WHERE cs.rowid ='.$rowid;
    $consulta = $db->query($sq);
    return $consulta;
}


function get_comision($fk_bank){
global $db;
$sq = 'SELECT * FROM `llx_banco_comision` WHERE `fk_bank` = '.$fk_bank.'';
$sql = $db->query($sq);
$datos = [];
while($obj = $db->fetch_object($sql)){
$datos = ['id'=>$obj->fk_bank,'comision1'=>$obj->comision1,'comision2'=>$obj->comision2,'comision3'=>$obj->comision3];
}  
return $datos;
}


function get_arqueos($fk_cash,$fk_cierre,$sqls=''){
global $db;
$sq = 'SELECT SUM(amount_real) amount_real,SUM(multicurrency_amount_real) multicurrency_amount_real 
FROM llx_pos_control_cash WHERE type_control=0 AND fk_cash='.$fk_cash.' AND fk_cierre='.$fk_cierre.'';
$sq .= $sqls;
$sql = $db->query($sq);
$datos = ['amount_real'=>0,'multicurrency_amount_real'=>0];
while($obj = $db->fetch_object($sql)){
$datos = ['amount_real'=>$obj->amount_real,'multicurrency_amount_real'=>$obj->multicurrency_amount_real];
}  
return $datos;
}



function set_facture_log($terminal,$fk_cierre,$fk_facture,$fk_entrepot,$type,$user){
    global $db;
$sq = 'INSERT INTO llx_facturas_cash (
        `fk_facture`, 
        `fk_user`, 
        `fk_entrepot`, 
        `type`,        
        `fk_terminal`,
        `fk_cierre`        
        ) 
        VALUES (
        "'.$fk_facture.'",
        "'.$user.'",
        "'.$fk_entrepot.'",
        "'.$type.'",   
        "'.$terminal.'",               
        "'.$fk_cierre.'"  
 )'; 
$res = $db->query($sq); 
return $res;
}

function set_paiement_log($fk_paiement_facture,$user,$terminal,$idwarehouse,$bank_line,$fk_facture,$fk_paiement,$fk_cierre,$metodo_pago,$monto,$multicurrency_code){
  global $db; global $conf;
  $sq = 'INSERT INTO `llx_pagos_cash`(
    `fk_paiement_facture`,
    `fk_user_author`,
    `fk_caja`,
    `entity`,
    `fk_entrepot`,
    `fk_bank`,
    `fk_facture`,
    `fk_paiement`,
    `fk_cierre`,
    `metodo_pago`,
    `monto`,
    `multicurrency_code`             	
    ) VALUES (
    "'.$fk_paiement_facture.'",
    "'.$user.'",
    "'.$terminal.'",
    "'.$conf->entity.'",
    "'.$idwarehouse.'",
    "'.$bank_line.'",
    "'.$fk_facture.'",
    "'.$fk_paiement.'",
    "'.$fk_cierre.'",
    "'.$metodo_pago.'",
    "'.$monto.'",
    "'.$multicurrency_code.'"       
    )';

        $res = $db->query($sq); 
        return $res;        
        
}


function set_money_control($fk_object,$fk_pago1,$fk_pago2,$type,
$fk_bank,$fk_bank_2,$monto1,$monto2,$total_ttc,$cambio,$multicurrency_tx,$moneda1,$moneda2,$dolar_monto1,$dolar_monto2,
$fk_user,$fk_cash,$fk_cierre,$metodo_pago1,$metodo_pago2){
global $db; global $conf;   
//registrado registro de pagos
$sq = 'INSERT INTO llx_cashdespro_dinero_control(
    `fk_object`, 
    `fk_pago1`, 
    `fk_pago2`, 
    `type`, 
    `fk_bank`, 
    `fk_bank_2`,     
    `monto1`, 
    `monto2`, 
    `total_ttc`, 
    `cambio`, 
    `multicurrency_tx`, 
    `moneda2`, 
    `moneda1`,
    `dolar_monto1`,
    `dolar_monto2`,     
    `fk_user`, 
    `fk_cash`,
    `fk_cierre`,
    `metodo_pago1`,
    `metodo_pago2`        
    ) VALUES (
    "'.$fk_object.'",
    "'.$fk_pago1.'",
    "'.$fk_pago2.'",
    "'.$type.'",
    "'.$fk_bank.'",
    "'.$fk_bank_2.'",    
    "'.$monto1.'",
    "'.$monto2.'",    
    "'.$total_ttc.'",
    "'.$cambio.'",
    "'.$multicurrency_tx.'",    
    "'.$moneda1.'",
    "'.$moneda2.'", 
    "'.$dolar_monto1.'",
    "'.$dolar_monto2.'",   
    "'.$fk_user.'",   
    "'.$fk_cash.'",
    "'.$fk_cierre.'",
    "'.$metodo_pago1.'",    
    "'.$metodo_pago2.'"   
     )';
     $res = $db->query($sq); 
     return $res;  
}

function get_pagos_banco($fk_cierre,$multicurrency_code,$sqls=''){
    global $db; global $conf,$facnumber;  
   
$sq = 'SELECT p.rowid,p.ref,p.tms,pf.amount,pf.multicurrency_amount,f.rowid f_id,f.'.$facnumber.',pa.metodo_pago,pa.multicurrency_code,bca.rowid bca_id,
bca.ref bca_ref,bc.comision1,bc.comision2,bc.comision3 
FROM llx_pagos_cash pa 
JOIN llx_paiement_facture pf ON pa.fk_paiement_facture = pf.rowid
JOIN llx_paiement p ON pf.fk_paiement=p.rowid
JOIN llx_bank b ON pa.fk_bank = b.rowid
JOIN llx_bank_account bca ON b.fk_account = bca.rowid
LEFT JOIN llx_banco_comision bc ON bc.fk_bank=bca.rowid
JOIN llx_facture f ON pa.fk_facture = f.rowid
WHERE pa.multicurrency_code = "'.$multicurrency_code.'" AND pa.fk_cierre='.$fk_cierre.'';
$sq .= $sqls;
$datos = [];
$sql = $db->query($sq);
while($obj = $db->fetch_object($sql)){
$datos[] = ['amount'=>$obj->amount,'multicurrency_amount'=>$obj->multicurrency_amount,'comision1'=>$obj->comision1,'comision2'=>$obj->comision1,'comision3'=>$obj->comision3,'metodo'=>$obj->code,'libelle'=>$obj->libelle];
}
return $datos;
}


function get_banco_resumen($fk_banco,$fk_cierre){
  global $db; global $conf;
  $sq = 'SELECT SUM(pc.monto) monto FROM llx_pagos_cash pc
  JOIN llx_bank bk ON pc.fk_bank=bk.rowid
  JOIN llx_bank_account ba ON bk.fk_account=ba.rowid
  WHERE ba.rowid='.$fk_banco.' AND pc.fk_cierre='.$fk_cierre.'';
  $sql = $db->query($sq);
  while($obj = $db->fetch_object($sql)){
  $monto = $obj->monto;
  }
  return $monto;
}

function get_pago_resumen($fk_cierre,$fk_banco){
  global $db; global $conf;
  $sq = 'SELECT SUM(monto) monto FROM llx_pagos_cash pc WHERE pc.fk_cierre = '.$fk_cierre.' AND pc.metodo_pago="'.$fk_banco.'"';
  $sql = $db->query($sq);
  while($obj = $db->fetch_object($sql)){
  $monto = $obj->monto;
  }
  return $monto;
}



/**
 * INSERT INTO `llx_pos_control_cash`(`rowid`, `ref`, `entity`, 
 * `fk_cash`, `fk_user`, `date_c`, `type_control`, `amount_teor`,
 *  `amount_real`, `amount_diff`, `multicurrency_amount_teor`, 
 * `multicurrency_amount_real`, `multicurrency_amount_diff`, 
 * `amount_mov_out`, `amount_mov_int`, `amount_next_day`, `comment`,
 *  `date_open`, `date_close`, `user_close`) 
 * VALUES ([value-1],[value-2],[value-3],[value-4],
 * [value-5],[value-6],[value-7],[value-8],[value-9],[value-10],
 * [value-11],[value-12],[value-13],[value-14],[value-15],
 * [value-16],[value-17],[value-18],[value-19],[value-20])
 */
/**Funtion encargada de poder insertar en llx_pos_control_cash*/
function insertar_llx_pos_control_cash($rowid,$ref,$entity,$fk_cash,
        $fk_user,$date_c,$type_control,$amount_teor,$amount_real,$amount_diff
        ,$multicurrency_amount_teor,$multicurrency_amount_real,$multicurrency_amount_diff,
        $amount_mov_out,$amount_mov_int,$amount_next_day,$comment,$date_open,$date_close,$user_close){
    global $db, $langs,$conf;
    $sql='INSERT INTO llx_cierre_caja_denominacion_bank(fk_cierre_caja,fk_bank,monto)
     VALUES ('.$fk_cierre_caja.','.$fk_banco.','.$monto.')';
     $consulta = $db->query($sql);
     return $consulta;
}




function get_mage_ategory($idCat)
{	
  global $conf, $langs, $user,$db;	
  $object = new Categorie($db);
  $object->fetch($idCat);
  $nbphoto = 0;
  $nbbyrow = 5;

  $maxWidth = 160;
  $maxHeight = 120;
  $upload_dir = $conf->categorie->multidir_output[$object->entity];
  $pdir = get_exdir($object->id, 2, 0, 0, $object, 'category').$object->id."/photos/";
  $dir = $upload_dir.'/'.$pdir;

  $listofphoto = $object->liste_photos($dir);

//obteniendo url publica
include_once DOL_DOCUMENT_ROOT.'/ecm/class/ecmfiles.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/security2.lib.php';
$ecmfile=new EcmFiles($db);
$result = $ecmfile->fetch('','', 'category/'.$pdir.'/'.$listofphoto[0]['photo'].'', '', '', $hashp);
if($ecmfile->share == ''){
  $ecmfile->filename = $listofphoto[0]['photo'];
  $ecmfile->filepath = 'category/'.$pdir;
  $ecmfile->fullpath_orig = $dir.$listofphoto[0]['photo'];
  $ecmfile->share = getRandomPassword(true);
  $ecmfile->label = $object->label;
  $res = $ecmfile->create($user);	   			   
}
$ecmfile=new EcmFiles($db);  
$result = $ecmfile->fetch('','', 'category/'.$pdir.'/'.$listofphoto[0]['photo'].'', '', '', $hashp);

//fin obteniendo url publica
if($_SERVER['HTTPS'] == 'on'){$protocol = 'https://';}else{$protocol = 'http://';}	
$share_phath = $protocol.$_SERVER['HTTP_HOST'].DOL_URL_ROOT.'/document.php?hashp='.$ecmfile->share;	
$realpath = DOL_URL_ROOT.'/viewimage.php?modulepart=product&file='.urlencode('noimage.jpg');
if($ecmfile->share==''){
return $realpath;  
}
return $share_phath;
}




function get_categories($idCat=0)
{
  global $db;
  switch($idCat)
  {
    case 0: //devolver las categorias con nivel 1
      $objCat = new Categorie($db);
      //$cats=$objCat->get_full_arbo(0);
      $cats = $objCat->get_full_arbo($idCat);
              
      if (count($cats) > 0)
      {
        $retarray=array();
        foreach($cats as $key => $val)
        {
            $objCate = new Categorie($db);   
            $rese=$objCate->fetch($val['id']);  
            $cats_count = $objCate->get_filles($val['id']);         
          if ($val['level'] < 2)
          { $val['fk_parent']=$val['fk_parent'];
            $val['image']=get_mage_ategory($val['id']);
            $val['thumb']=get_mage_ategory($val['id']);
            $val['levels']=count($cats_count);
            $retarray[]=$val;
          }	
        }
        return $retarray;
      }
      break;

    case ($idCat>0):
      $objCat = new Categorie($db);
    
      $result=$objCat->fetch($idCat);
      if($result > 0)
      {
        $cats = $objCat->get_filles($idCat);
        //$cats = self::get_filles($idCat);
        if (sizeof ($cats) > 0)
        {
          $retarray=array();
          foreach($cats as $val)
          {
            $cat['fk_parent']=$val->fk_parent;  
            $cat['id']=$val->id;
            $cat['label']=$val->label;
            $cat['fulllabel']=$val->label;
            $cat['fullpath']='_'.$val->id;
            $cat['levels']=count($cats);
            $cat['image']=get_mage_ategory($val->id);
            $cat['thumb']=get_mage_ategory($val->id);
            $retarray[]=$cat;
          }
          return $retarray;
        }
      }
      
      break;
      
    default:
      return -1;
      break;
  }
}





?>