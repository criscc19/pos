<?php
require '../../main.inc.php';
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
require_once DOL_DOCUMENT_ROOT . '/core/class/discount.class.php';


$factura = new Facture($db);
$factura->fetch(GETPOST('id'));
$factura->fetch_optionals();
if(!$conf->global->POS_REGIMEN){
$sq = 'SELECT f.act_eco  FROM llx_facture f WHERE f.rowid = '.GETPOST('id').'';
$sql = $db->query($sq);
$actividad = $db->fetch_object($sql)->act_eco;
//creando factura
$fac = new DocumentObject($db);
$fac->socid          = $factura->socid;	// Put id of third party (rowid in llx_societe table)
$fac->date           = strtotime(date('Y-m-d'));
$fac->note_public    = $factura->note_public;
$fac->note_private   = '';
$fac->cond_reglement_id   = $factura->cond_reglement_id;
//$fac->cond_reglement_id   = $conf->global->POS_COND_REGLEMENT_ID_CASH;
$fac->ref_client = $factura->ref;
$fac->mode_reglement_id    = $factura->mode_reglement_id;
$fac->date_livraison      = $factura->date_livraison;
//$fac->shipping_method_id  = 6;
$fac->multicurrency_code = $factura->multicurrency_code;
$fac->multicurrency_tx = $factura->multicurrency_tx;
$fac->public_note = $factura->public_note;
$fac->user_author = $user->id;
//extrafields
$fac->array_options[ "options_facturetype" ] = 1;	
$fac->array_options[ "options_vendedor" ] = $factura->array_options[ "options_vendedor" ];
$fac->array_options[ "options_sucursal" ] = $factura->array_options[ "options_sucursal" ];
$fac->array_options[ "tipo_doc" ] = 1;
//creando factura
$idobject=$fac->create($user);
if($idobject > 0){
$db->query('UPDATE `llx_facture` SET `act_eco` = "'.$actividad.'" WHERE `llx_facture`.`rowid` = '.$idobject.''); 
$default_vat_code = '08 Tarifa general';
foreach($factura->lines as $line){
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


$fac->fetch($idobject);
$fac->fetchFE($idobject);
$fac->fetch_optionals();
$fac->user_author = $user->id;
$res = $fac->validate($user,'', $conf->global->POS_APARTADO_ENTREPOT);

if($res){
//guardando relacion de facturas echas desde el cash
$sq = 'INSERT INTO llx_facturas_cash (
	`fk_facture`, 
    `fk_user`, 
	`fk_entrepot`, 
	`apartado`) 
	VALUES (
	"'.$idobject.'",
	"'.$user->id.'",
	"'.$_SESSION['TERMINAL_ID'].'",
	"0"
	)';
$resp = $db->query($sq);

//fin guardando relacion de gcarueas echas desde el cash 

header('Location: doc_sucess.php?facid='.$idobject.'&tipo=1&id='.$idobject.'');

}else{
    var_dump($fac->error,$fac->errors);exit;
}

}
	
}else{

   //SI SE VALIDO MOVIENDO STOCK A BODEGA DE APARTADOS  
      foreach($factura->lines as $line){
        $prod = new Product($db);
        $prod->fetch($line->fk_product);
        
        
        //rebajando stock = 1, sumando stock = 0
        $respu = $prod->correct_stock(
            $user,
            $conf->global->POS_APARTADO_ENTREPOT,
            $line->qty,//cantidad
            1,//modo
            'Salida de bodega por apartado id: '.$factura->id.'',
            '',
            'Salida de bodega de apartados por '.$factura->ref.' id: '.$factura->id.''
            );
        
      }

  //FIN SI SE VALIDO MOVIENDO STOCK A BODEGA DE APARTADOS  
  header('Location: doc_sucess.php?facid='.$factura->id.'&tipo=1&id='.$factura->id.'');
 
  
}







$db->close();

?>