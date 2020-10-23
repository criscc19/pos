<?php
require('../../../main.inc.php');
if (!empty($conf->rewards->enabled)) require_once DOL_DOCUMENT_ROOT.'/rewards/class/rewards.class.php';
if (empty($conf->rewards->enabled)) require_once DOL_DOCUMENT_ROOT.'/pos/backend/class/rewards.class.php';
global $db;

if(DOL_VERSION < 11){
    $facnumber = 'facnumber';
    }else{
    $facnumber = 'ref';  
    }

if(isset($_POST) &&  $_POST['action'] != 'get_ndc_desc'){
if(isset($_POST['q'])){    
$sq = 'SELECT * FROM llx_societe
WHERE (nom LIKE "%'.$_POST['q'].'%" OR name_alias LIKE "%'.$_POST['q'].'%" OR siren LIKE "%'.$_POST['q'].'%") AND client=1 LIMIT 10'
;
}

if(isset($_POST['action']) && $_POST['action'] == 'get_client_info'){
    $sq = 'SELECT * FROM llx_societe
    WHERE rowid='.$_POST['fk_soc'].' LIMIT 1';
    }

$sql = $db->query($sq);
$data = [];
for($i=1;$i<=$db->num_rows($sql);$i++){
$soc = $db->fetch_object($sql);	

$cliente = new Societe($db);
$cliente->fetch($soc->rowid);
$pendiente = $cliente->getOutstandingBills();
$av_discounts = $cliente->getAvailableDiscounts();
$comercials = $cliente->getSalesRepresentatives($user);
$comercial = [];
foreach($comercials as $c){
$comercial[] = $c['login'];  
};


$correos = $cliente->thirdparty_and_contact_email_array();
$contactos = $cliente->contact_array_objects();
$pendiente_propal = $cliente->getOutstandingProposals();
$pendiente_commande = $cliente->getOutstandingOrders();

if($soc->price_level == ''){
$price_level = 1;
}else{
$price_level = $soc->price_level;   
}

//REWARDS
$rewards = New Rewards($db);
$puntos = $rewards->getCustomerPoints($soc->rowid);
if($rewards->getCustomerReward($soc->rowid)==1){$inscrito='SI';}else{$inscrito='NO';}

//exoneracion
$sq2 = 'SELECT * FROM `llx_facturaelectronica_societe_exonerado` WHERE fk_soc='.$soc->rowid.'';
$sql2 = $db->query($sq2);
$exone = [];
while($obj2 = $db->fetch_object($sql2)){
$exone[] = [
   'numero_documento'=>$obj2->numero_documento,
   'tipo_dococumento'=>$obj2->tipo_dococumento,
   'tipo_dococumento'=>$obj2->descripcion_documento,
   'porcentaje'=>$obj2->porcentaje,  
   'product_list'=>$obj2->product_list, 
   'nombre_institucion'=>$obj2->nombre_institucion,  
];    
}
//fin exoneracion


$data []= [
    'id'=>$soc->rowid,
    'text'=>$soc->nom.' '.$soc->name_alias.' ced: '.$soc->siren.'',
    'nom'=>$soc->nom,
    'puntos'=>$puntos,
    'inscrito'=>$inscrito,
    'name_alias'=>$soc->name_alias, 
    'code_client'=>$soc->code_client,        
    'price_level'=>$price_level,
    'address'=>$soc->address,
    'phone'=>$soc->phone,
    'cond_reglement'=>$soc->cond_reglement,
    'limite_credito'=>(float)$cliente->outstanding_limit,
    'credito_usado'=>(float)$pendiente['opened'],
    'av_discounts'=>$av_discounts,
    'remise_percent'=>$cliente->remise_percent,    
    'comercial' =>implode(',',$comercial),
    'correo' =>$soc->email,  
    'pendiente_propal'=>(float)$pendiente_propal['opened'], 
    'pendiente_commande'=>(float)$pendiente_commande['opened'], 
    'credito_disponible'=>(float)$soc->outstanding_limit - (float)$pendiente['opened'],
    'siren'=>$soc->siren,
    'idprof1'=>$soc->idprof1,
    'forme_juridique'=>$cliente->forme_juridique,    
    'forme_juridique_code'=>$cliente->forme_juridique_code,
    'exoneracion'=>$exone
    ];       
}
echo json_encode($data);	
}

if($_POST['action']=='get_ndc_desc'){
global $facnumber;
    global $db;    
    $sq = 'SELECT fx.rowid fx_id,fx.amount_ht,fx.amount_tva,fx.amount_ttc,f2.rowid ndc_id,f3.'.$facnumber.' ndc_facnumber,f2.rowid ndc_source_id,f2.'.$facnumber.' ndc_source_facnumber,f.rowid desc_aplicado_id,f.'.$facnumber.' desc_aplicado_facnumber,f2.ref_client
    FROM llx_societe_remise_except  fx
    JOIN llx_facture f3 ON fx.fk_facture_source=f3.rowid
    JOIN llx_facture f2 ON f3.fk_facture_source = f2.rowid
    LEFT JOIN llx_facture f ON fx.fk_facture = f.rowid
    WHERE fk_facture IS NULL AND fx.fk_soc = '.$_POST['fk_soc'].'';
    $sql = $db->query($sq);
    while($obj = $db->fetch_object($sql)){
    $datos[] = [
        'decuento_id'=>$obj->fx_id,
        'amount_ht'=>$obj->amount_ht,
        'amount_tva'=>$obj->amount_tva,
        'amount_ttc'=>$obj->amount_ttc,        
        'amount_ht2'=>price($obj->amount_ht),
        'amount_tva2'=>price($obj->amount_tva),
        'amount_ttc2'=>price($obj->amount_ttc),
        'ndc_id'=>$obj->ndc_id,
        'ndc_facnumber'=>$obj->ndc_facnumber,
        'ndc_source_id'=>$obj->ndc_source_id,    
        'ndc_source_facnumber'=>$obj->ndc_source_facnumber,
        'desc_aplicado_id'=>$obj->desc_aplicado_id,
        'desc_aplicado_facnumber'=>$obj->desc_aplicado_facnumber,
        'ref_client'=>$obj->ref_client    
    ];
    
    }
    if(count($datos) > 0){
    echo json_encode($datos);
    }else{
    echo json_encode(array());
    }
    
 
}

?>