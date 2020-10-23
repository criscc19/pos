<?php 
require('../../../main.inc.php');
$action = GETPOST('action');
if(DOL_VERSION < 11){
	$facnumber = 'facnumber';
	}else{
	$facnumber = 'ref';  
    }			
$fk_mesa = GETPOST('fk_mesa');
$fk_facture = GETPOST('fk_facture');
$fk_categorie = GETPOST('fk_categorie');

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
			<p class="adresse"><?php 
			if (version_compare(DOL_VERSION, '8.9.9', '>=')) {
				print '<img src="'.DOL_URL_ROOT.'/viewimage.php?modulepart=mycompany&amp;file='.urlencode('/logos/thumbs/'.$mysoc->logo_small).'">';
			}else{
				print '<img src="'.DOL_URL_ROOT.'/viewimage.php?modulepart=companylogo&amp;file='.urlencode('/thumbs/'.$mysoc->logo_small).'">'; 
			}

			?></span>
			<p style="font-weight:bold;font-size:18px;text-align:center"><span>Comanda</span></p>
			   
			  <table align="center" border="0" width="100%" >
<?php
$sq2 = 'SELECT f.rowid f_id,s.rowid s_id,u.rowid u_id, u.firstname,u.lastname,u.login,s.nom,s.name_alias,
f.ref_client,f.type,f.facnumber,(SELECT SUM(fd.total_ttc) FROM llx_facturedet_cashdespro fd WHERE fd.fk_facture=f.rowid) total_ttc,
(SELECT SUM(fd.multicurrency_total_ttc) FROM llx_facturedet_cashdespro fd WHERE fd.fk_facture=f.rowid) multicurrency_total_ttc,
f.multicurrency_code,f.tms,rm.name mesa,p.rowid p_id,p.ref,p.label,p.description,c.rowid c_id,c.label categoria,fd.qty,fd.fk_product,
fd.rowid fd_id,fd.estado
FROM llx_facture_cashdespro f
JOIN llx_societe s ON f.fk_soc=s.rowid
LEFT JOIN llx_pos_restaurant_mesas rm ON f.fk_mesa=rm.rowid
LEFT JOIN llx_user u ON f.fk_user_author=u.rowid
LEFT JOIN llx_facturedet_cashdespro fd ON fd.fk_facture=f.rowid
LEFT JOIN llx_product p ON fd.fk_product=p.rowid
LEFT JOIN llx_categorie_product cp ON cp.fk_product=p.rowid 
LEFT JOIN llx_categorie c ON cp.fk_categorie=c.rowid 
WHERE f.rowid = '.$fk_facture.' AND c.rowid IN('.$fk_categorie.') AND f.fk_mesa = '.$fk_mesa.'
GROUP BY fd.fk_product
ORDER BY f.rowid ASC';

$sql2 = $db->query($sq2);
while($obj2 = $db->fetch_object($sql2)){
if($obj2->estado == 1){$estado = ' checked ';}else{$estado = '';}
print '<tr>';
print '<td>
<b>Orden:</b> '.$obj2->facnumber.' <br>
<b>Mesa: </b>'.$obj2->mesa.'<br>
<b>Producto:</b> '.$obj2->ref.' '.$obj2->label.'<br>  
<b>Descricion:</b> '.$obj2->description.' <br> 
<b>cantidad:</b>' .$obj2->qty.' <br>
<hr>
</td>';
print '</tr>';

}
?>				
				</table>
					<script type="text/javascript">
						window.print();
					</script>
					<a class="lien" href="#" onClick="javascript: window.close(); return(false);">Fermer cette fenetre</a>
				</body>
