<?php 
require('../../../main.inc.php');
require_once(DOL_DOCUMENT_ROOT.'/CashdeskPro/include/environnement.php');
require_once(DOL_DOCUMENT_ROOT.'/CashdeskPro/class/Facturation.class.php');

$obj_facturation = unserialize ($_SESSION['serObjFacturation']);
unset ($_SESSION['serObjFacturation']);

$facture="";
$product="";
$societe="";
$paiement_array=array();
$pAmounts=array();
$pAmount=0;
$fAmount=0;
$fAuthor=0;
$cantidad=0;
$facid = GETPOST('facid');
$factureid=$facid;
if(DOL_VERSION < 11){
	$facnumber = 'facnumber';
	}else{
	$facnumber = 'ref';  
	}	
	$sql = 'SELECT  f.rowid as factureid,f.datef, f.ref_client  ,p.ref, pf.amount as pAmount, f.'.$facnumber.', f.tva, f.total,f.remise_percent,  fd.qty, p.datec,fd.fk_product,f.fk_soc,f.total_ttc as facmount,f.fk_user_author FROM llx_facture f, llx_paiement_facture pf, llx_paiement p, llx_facturedet fd WHERE f.rowid = pf.fk_facture AND p.rowid = pf.fk_paiement AND fd.fk_facture = f.rowid AND  f.rowid=' . $facid;

//echo $sql;


	$resql = $db->query($sql);
	if ($resql && $db->num_rows($resql) > 0)
	{ 
		while($objp = $db->fetch_object($resql)){
			$facture=$objp->$facnumber;
			$product=$objp->fk_product;
			$societe=$objp->fk_soc;
			$fAuthor=$objp->fk_user_author;
			$cantidad=$objp->qty;
			$factureid=$objp->factureid;
            $fecha=$objp->datef;
			$paiement_array["monto"][]=$objp->pAmount;
			$paiement_array["ref"][]=$objp->ref;
			$paiement_array["fecha"][]=$objp->datec;



			$tva=$objp->tva;
			$subtotal=$objp->total;
			$descuento=$objp->remise_percent;

			$pAmount=$pAmount+$objp->pAmount;
			$pAmounts[$objp->payid]=$objp->pAmount;
			$fAmount=$objp->facmount;

			$facture_ref_clien=$objp->ref_client;


		}
	}else{

		$facid = GETPOST('facid');
		$sql = 'SELECT * FROM `llx_facture` WHERE rowid=' . $facid;
	//	echo  $sql;
		$resql = $db->query($sql);
		if ($resql && $db->num_rows($resql)>0)
		{ 
			$objp = $db->fetch_object($resql);
			$societe=$objp->fk_soc;
			$factureid=$objp->rowid;

			$tva=$objp->tva;
			$subtotal=$objp->total;
			$subtotal_cabecera=$objp->total;
			$descuento=$objp->remise_percent;



			$factureDateLimit = $objp->date_lim_reglement;
			//$facture=(isset($objp->ref_client) && !empty($objp->ref_client) )?$objp->ref_client:$objp->facnumber;

			$facture=$objp->$facnumber;
			$facture_ref_clien=$objp->ref_client;


			$note_public=$objp->note_public;
		}


	}

	$pAmount=array_sum($pAmounts);

	$sql = 'SELECT nom FROM `llx_societe` WHERE rowid=' . $societe;

	$resqlSoc = $db->query($sql);
	
	if ($resqlSoc)
	{ 

		$objSociete = $db->fetch_object($resqlSoc);
		
	}


	$sql = 'SELECT ref, label FROM `llx_product` WHERE rowid=' . $product;

	$resqlProd = $db->query($sql);
	
	if ($resqlProd)
	{ 
		
		$objProd = $db->fetch_object($resqlProd);
		
	}


?>

<html>
<head>
<meta charset="UTF-8">
	<style type="text/css">

		body {
			font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; 
			font-size: 13px;
			position: relative;
			width:70mm;
		}

		.totpays{
			font-size: 13px;
		}
		.ticket {
    transform: rotate(90deg);
    margin-top: 180;
    margin-left: -150;
    width: 450;
}
	</style>

</head>

<body>
<div class="ticket">
<h1><?php echo $objSociete->nom.' '.$objSociete->name_alias ?></h1>
<h1>Apartado #<?php echo $facture ?><br> Fecha <?php echo date('d/m/Y',strtotime($fecha));?> </h1>
</div>
</body>

<?php
$_SESSION['serObjFacturation'] = serialize ($obj_facturation);
?>