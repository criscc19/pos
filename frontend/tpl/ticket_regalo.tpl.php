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

	$sql = 'SELECT  f.rowid as factureid, f.ref_client  ,p.ref, pf.amount as pAmount, f.'.$facnumber.', f.tva, f.total,f.remise_percent,  fd.qty, p.datec,fd.fk_product,f.fk_soc,f.total_ttc as facmount,f.fk_user_author FROM llx_facture f, llx_paiement_facture pf, llx_paiement p, llx_facturedet fd WHERE f.rowid = pf.fk_facture AND p.rowid = pf.fk_paiement AND fd.fk_facture = f.rowid AND  f.rowid=' . $facid;

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

		.entete {
			/* 		position: relative; */
		}
		.tva {
			font-size: 13px;
		}
		.adresse {
			/* 			float: left; */
			font-size: 13px;
			text-align:center;
		}

		.date_heure {
			/*position: absolute;*/
			top: 0;
			right: 0;
			font-size: 13px;
		}

		.infos {
			position: relative;
		}


		.liste_articles {
			width: 100%;
			border-bottom: 1px solid #000;
			text-align: center;
		}

		.liste_articles tr.titres th {
			border-bottom: 1px solid #000;
		}

		.liste_articles td.total {
			text-align: right;
		}

		.totaux {
			margin-top: 20px;
			width: 75%;
			float: right;
			text-align: right;
		}

		.lien {
			position: absolute;
			top: 0;
			left: 0;
			display: none;
		}
		.noteticket {
			font-size: 13px;
			font-weight:normal;
			text-align:center;
			width:100%
		}

		@media print {

			.lien {
				display: none;
			}

		}

	</style>

</head>

<body >
	<?php 	require_once(DOL_DOCUMENT_ROOT.'/product/class/product.class.php');  ?>
	<div class="entete">
		<div class="logo">
		</div>
		<div class="infos">
                <center><span>
				<?php
					if (version_compare(DOL_VERSION, '8.9.9', '>=')) {
						print '<img src="'.DOL_URL_ROOT.'/viewimage.php?modulepart=mycompany&amp;file='.urlencode('/logos/thumbs/'.$mysoc->logo_small).'">';
					}else{
						print '<img src="'.DOL_URL_ROOT.'/viewimage.php?modulepart=companylogo&amp;file='.urlencode('/thumbs/'.$mysoc->logo_small).'">'; 
					}
				?>
			</span></center>
			<p class="adresse"><span style="font-weight:bold;font-size:18px">CERTIFICADO DE REGALO</span><br>
						<?php

					$autor = $db->query (
		'SELECT s.firstname,s.lastname from llx_facture as f , llx_user s WHERE f.'.$facnumber.'="'.$facture.'" and f.fk_user_author =s.rowid ');
		if ($autor)
			{
				$num = $db->num_rows($autor);
				$i = 0;
				while ($i < $num)
				{
					$obj = $db->fetch_object($autor);
					//echo '<tr class="tva"><th nowrap="nowrap">'.$langs->trans("I.V.").'</th><td nowrap="nowrap">'.price2num($obj->sumTTC - $obj->sumHT,'MT')." ".$conf->currency."</td></tr>\n";
					//echo '<tr class="tva"><th nowrap="nowrap">'.$langs->trans("I.V.").'</th><td nowrap="nowrap">'.($obj->firstname .' '. $obj->lastname."</td></tr>\n";
					$i++;
				}
			}
				$res = $db->query (
					'SELECT claveNumerica FROM llx_facturaelectronica_log WHERE fk_facture='.$facid.'');
				if ( $db->num_rows($res) ) 
				{
					$ret=array(); $i=0;
					while ( $tab = $db->fetch_array($res) )
					{
						foreach ( $tab as $cle => $valeur )
						{
							$ret[$i][$cle] = $valeur;
						}
						$i++;
					}
					$tab = $ret;
					$tab_size=count($tab);
					for($i=0;$i < $tab_size;$i++) 
					{
						$claveNumerica = $tab[$i]['claveNumerica'];	
					}
				}else
				{
					//echo ('<p>Clave Numerica : </p>'."\n");
				}

					
			// Recuperation et affichage de la date et de l'heure
					$now = dol_now();
					print '<table align="center" border="0" width="100%" >
					<tr><td align="center">Factura: '.$facture.'</td></tr>
					<tr><td align="center">Cliente: '.$objSociete->nom.'</td>
					</tr>					
					</table>';
?>

				<table class="noteticket" border="0">
					<tr><td align="right">	
					</td></tr><tr><td>Valido hasta un mes de la compra</td></tr>
					<tr>
						<td>
						<?php ($claveNumerica!='')?print '<img src="https://feng.erp.cr/documento/qr/'.$claveNumerica.'">':''; ?>
						</td>
					</tr></table>
					<script type="text/javascript">
						window.print();
					</script>
					<a class="lien" href="#" onClick="javascript: window.close(); return(false);">Fermer cette fenetre</a>
				</body>


<?php
$_SESSION['serObjFacturation'] = serialize ($obj_facturation);
?>