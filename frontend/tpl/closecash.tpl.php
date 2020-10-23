<?php
$res=@include("../../../main.inc.php");                                   // For root directory
if (! $res) $res=@include("../../../../main.inc.php");                // For "custom" directory

dol_include_once('/pos/backend/class/ticket.class.php');
dol_include_once('/pos/backend/class/cash.class.php');
require_once(DOL_DOCUMENT_ROOT."/core/lib/company.lib.php");
require_once(DOL_DOCUMENT_ROOT ."/pos/frontend/include/funciones.php");
dol_include_once('/pos/backend/class/pos.class.php');
if(DOL_VERSION < 11){
	$facnumber = 'facnumber';
	}else{
	$facnumber = 'ref';  
	}	
global $langs, $db, $mysoc;

$langs->load("main");
$langs->load("pos@pos");
header("Content-type: text/html; charset=".$conf->file->character_set_client);

$closeid=GETPOST('id','int');
$terminalid=GETPOST('terminal','int');
?>
<html>
<head>
<title>Print ticket</title>

<style type="text/css">

	body {
		font-size: 1.5em;
		position: relative;
	}

	.entete {
/* 		position: relative; */
	}

		.adresse {
/* 			float: left; */
			font-size: 12px;
		}

		.date_heure {
			position: absolute;
			top: 0;
			right: 0;
			font-size: 16px;
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
		width: 30%;
		float: right;
		text-align: right;
	}

	.lien {
		position: absolute;
		top: 0;
		left: 0;
		display: none;
	}

	@media print {

		.lien {
			display: none;
		}

	}

</style>

</head>

<body>

<?php

$html = '';
$html .= '<h3> Facturas en colones</h3>';
$html .= '<table class="tagtable liste listwithfilterbefore">';
$html .= '<thead>';
$html .= '<tr class="liste_titre">';
$html .= '<th class="liste_titre" align="left">Factura</th>';
$html .= '<th class="liste_titre" align="left">Cliente</th>';
$html .= '<th class="liste_titre" align="left">Fecha</th>';
$html .= '<th class="liste_titre" align="left">Subtotal</th>';
$html .= '<th class="liste_titre" align="left">IVA</th>';
$html .= '<th class="liste_titre" align="left">Total</th>';
$html .= '</tr>';
$html .= '<tbody>';

$sq = 'SELECT f.rowid,f.tms,f.datef,f.tva,f.total,
f.'.$facnumber.',s.rowid s_id,s.nom,s.name_alias,
f.total_ttc,pc.rowid pc_id FROM llx_facturas_cash fc
JOIN llx_facture f ON fc.fk_facture=f.rowid
JOIN llx_pos_control_cash pc ON fc.fk_cierre=pc.rowid
JOIN llx_societe s ON f.fk_soc=s.rowid
WHERE pc.rowid='.$closeid.' AND f.multicurrency_code = "CRC"';

$sql = $db->query($sq);
while ($obj = $db->fetch_object($sql)) {
$total += $obj->total;
$tva += $obj->tva;
$total_ttc += $obj->total_ttc;
$html .= '<tr>';
$html .= '<td><a href="../../compta/facture/card.php?facid='.$obj->rowid.'">'.$obj->$facnumber.'</a></td>';
$html .= '<td>'.$obj->nom.'</td>';
$html .= '<td>'.$obj->tms.'</td>';
$html .= '<td>'.price($obj->total).'</td>';
$html .= '<td>'.price($obj->tva).'</td>';
$html .= '<td>'.price($obj->total_ttc).'</td>';
$html .= '</tr>';
}

$html .= '<tr>';
$html .= '<td></td>';
$html .= '<td></td>';
$html .= '<td></td>';
$html .= '<td><strong>'.price($total).'</strong></td>';
$html .= '<td><strong>'.price($tva).'</strong></td>';
$html .= '<td><strong>'.price($total_ttc).'</strong></td>';
$html .= '</tr>';

$html .= '</tbody>';
$html .= '</thead>';
$html .= '</table>';


$html .= '<h3> Facturas en dolares</h3>';
$html .= '<table class="tagtable liste listwithfilterbefore">';
$html .= '<thead>';
$html .= '<tr class="liste_titre">';
$html .= '<th class="liste_titre" align="left">Factura</th>';
$html .= '<th class="liste_titre" align="left">Cliente</th>';
$html .= '<th class="liste_titre" align="left">Fecha</th>';
$html .= '<th class="liste_titre" align="left">Subtotal</th>';
$html .= '<th class="liste_titre" align="left">IVA</th>';
$html .= '<th class="liste_titre" align="left">Total</th>';
$html .= '</tr>';
$html .= '<tbody>';

$sq = 'SELECT f.rowid,f.tms,f.datef,f.tva,f.total,
f.'.$facnumber.',s.rowid s_id,s.nom,s.name_alias,
f.total_ttc,pc.rowid pc_id FROM llx_facturas_cash fc
JOIN llx_facture f ON fc.fk_facture=f.rowid
JOIN llx_pos_control_cash pc ON fc.fk_cierre=pc.rowid
JOIN llx_societe s ON f.fk_soc=s.rowid
WHERE pc.rowid='.$closeid.' AND f.multicurrency_code = "USD"';

$sql = $db->query($sq);
while ($obj = $db->fetch_object($sql)) {
$total_dolar += $obj->total;
$tva_dolar += $obj->tva;
$total_ttc_dolar += $obj->total_ttc;
	
$html .= '<tr>';
$html .= '<td><a href="../../compta/facture/card.php?facid='.$obj->rowid.'">'.$obj->$facnumber.'</a></td>';
$html .= '<td>'.$obj->nom.'</td>';
$html .= '<td>'.$obj->tms.'</td>';
$html .= '<td>'.$obj->total.'</td>';
$html .= '<td>'.$obj->tva.'</td>';
$html .= '<td>'.$obj->total_ttc.'</td>';
$html .= '</tr>';
}

$html .= '<tr>';
$html .= '<td></td>';
$html .= '<td></td>';
$html .= '<td></td>';
$html .= '<td><strong>'.price($total_dolar).'</strong></td>';
$html .= '<td><strong>'.price($tva_dolar).'</strong></td>';
$html .= '<td><strong>'.price($total_ttc_dolar).'</strong></td>';
$html .= '</tr>';
$html .= '</tbody>';
$html .= '</thead>';
$html .= '</table>';


$html .= '<h3> Pagos en colones</h3>';

$html .= '<table class="tagtable liste listwithfilterbefore">';
$html .= '<thead>';
$html .= '<tr class="liste_titre">';
$html .= '<th class="liste_titre" align="left">Pago</th>';
$html .= '<th class="liste_titre" align="left">Factura</th>';
$html .= '<th class="liste_titre" align="left">Fecha</th>';
$html .= '<th class="liste_titre" align="left">Monto</th>';
$html .= '<th class="liste_titre" align="left">Forma de pago</th>';
$html .= '<th class="liste_titre" align="left">Banco</th>';
$html .= '<th class="liste_titre" align="left">Comision1</th>';
$html .= '<th class="liste_titre" align="left">Comision2</th>';
$html .= '<th class="liste_titre" align="left">Comision3</th>';
$html .= '</tr>';
$html .= '<tbody>';

$sq = 'SELECT p.rowid,p.ref,p.tms,pf.amount,f.rowid f_id,f.'.$facnumber.',pa.metodo_pago,pa.multicurrency_code,
bca.rowid bca_id,bca.ref bca_ref FROM llx_pagos_cash pa 
JOIN llx_paiement_facture pf ON pa.fk_paiement_facture = pf.rowid
JOIN llx_paiement p ON pf.fk_paiement=p.rowid
JOIN llx_bank b ON pa.fk_bank = b.rowid
JOIN llx_bank_account bca ON b.fk_account = bca.rowid
LEFT JOIN llx_banco_comision bc ON bc.fk_bank=bca.rowid
JOIN llx_facture f ON pa.fk_facture = f.rowid
WHERE pa.multicurrency_code = "CRC" AND pa.fk_cierre='.$closeid.'';
$sql = $db->query($sq);
while ($obj = $db->fetch_object($sql)) {
if($obj->metodo_pago == 'CB'){	
$comision =	get_comision($obj->bca_id);
$comision1 = $obj->amount * $comision['comision1']/100;
$comision2 = $obj->amount * $comision['comision2']/100;
$comision3 = $obj->amount * $comision['comision3']/100;
$total_comision1 += $comision1;
$total_comision2 += $comision2;
$total_comision3 += $comision3;
}else{
$comision =	get_comision($obj->bca_id);
$comision1 = 0;
$comision2 = 0;
$comision3 = 0;
$total_comision1 += $comision1;
$total_comision2 += $comision2;
$total_comision3 += $comision3;	
}
$total_amount += $obj->amount;
$html .= '<tr>';
$html .= '<td><a href="../../compta/paiement/card.php?id='.$obj->rowid.'">'.$obj->ref.'</a></td>';
$html .= '<td><a href="../../compta/facture/card.php?facid='.$obj->rowid.'">'.$obj->$facnumber.'</a></td>';
$html .= '<td>'.$obj->tms.'</td>';
$html .= '<td>'.price($obj->amount).'</td>';
$html .= '<td>'.$langs->trans('PaymentType'.$obj->metodo_pago).'</td>';
$html .= '<td><a href="../../compta/bank/card.php?id='.$obj->bca_id.'">'.$obj->bca_ref.'</a></td>';
$html .= '<td>'.price($comision1).'</td>';
$html .= '<td>'.price($comision2) .'</td>';
$html .= '<td>'.price($comision3).'</td>';
$html .= '</tr>';
}
$html .= '<tr>';
$html .= '<td></td>';
$html .= '<td></td>';
$html .= '<td></td>';
$html .= '<td><b>'.price($total_amount).'</b></td>';
$html .= '<td></td>';
$html .= '<td></td>';
$html .= '<td><b>'.price($total_comision1).'</b></td>';
$html .= '<td><b>'.price($total_comision2) .'</b></td>';
$html .= '<td><b>'.price($total_comision3).'</b></td>';
$html .= '</tr>';

$html .= '</tbody>';
$html .= '</thead>';
$html .= '</table>';



$html .= '<h3> Pagos en Dolares</h3>';

$html .= '<table class="tagtable liste listwithfilterbefore">';
$html .= '<thead>';
$html .= '<tr class="liste_titre">';
$html .= '<th class="liste_titre" align="left">Pago</th>';
$html .= '<th class="liste_titre" align="left">Factura</th>';
$html .= '<th class="liste_titre" align="left">Fecha</th>';
$html .= '<th class="liste_titre" align="left">Monto</th>';
$html .= '<th class="liste_titre" align="left">Forma de pago</th>';
$html .= '<th class="liste_titre" align="left">Banco</th>';
$html .= '<th class="liste_titre" align="left">Comision1</th>';
$html .= '<th class="liste_titre" align="left">Comision2</th>';
$html .= '<th class="liste_titre" align="left">Comision3</th>';
$html .= '</tr>';
$html .= '<tbody>';

$sq = 'SELECT p.rowid,p.ref,p.tms,pf.multicurrency_amount,f.rowid f_id,f.'.$facnumber.',pa.metodo_pago,pa.multicurrency_code,bca.rowid bca_id,bca.ref bca_ref FROM llx_pagos_cash pa 
JOIN llx_paiement_facture pf ON pa.fk_paiement_facture = pf.rowid
JOIN llx_paiement p ON pf.fk_paiement=p.rowid
JOIN llx_bank b ON pa.fk_bank = b.rowid
JOIN llx_bank_account bca ON b.fk_account = bca.rowid
LEFT JOIN llx_banco_comision bc ON bc.fk_bank=bca.rowid
JOIN llx_facture f ON pa.fk_facture = f.rowid
WHERE pa.multicurrency_code = "USD" AND pa.fk_cierre='.$closeid.'';

$sql = $db->query($sq);
while ($obj = $db->fetch_object($sql)) {
if($obj->metodo_pago == 'CB'){	
$comision_dolar =	get_comision($obj->bca_id);
$comision1_dolar = $obj->multicurrency_amount * $comision['comision1']/100;
$comision2_dolar = $obj->multicurrency_amount * $comision['comision2']/100;
$comision3_dolar = $obj->multicurrency_amount * $comision['comision3']/100;
$total_comision1_dolar += $comision1_dolar;
$total_comision2_dolar += $comision2_dolar;
$total_comision3_dolar += $comision3_dolar;
}else{
$comision_dolar =	get_comision($obj->bca_id);
$comision1_dolar = 0;
$comision2_dolar = 0;
$comision3_dolar = 0;
$total_comision1_dolar += $comision1_dolar;
$total_comision2_dolar += $comision2_dolar;
$total_comision3_dolar += $comision3_dolar;	
}
$total_amount_dolar  += $obj->multicurrency_amount;
$html .= '<tr>';
$html .= '<td><a href="../../compta/paiement/card.php?id='.$obj->rowid.'">'.$obj->ref.'</a></td>';
$html .= '<td><a href="../../compta/facture/card.php?facid='.$obj->rowid.'">'.$obj->$facnumber.'</a></td>';
$html .= '<td>'.$obj->tms.'</td>';
$html .= '<td>'.price($obj->multicurrency_amount).'</td>';
$html .= '<td>'.$langs->trans('PaymentType'.$obj->metodo_pago).'</td>';
$html .= '<td><a href="../../compta/bank/card.php?id='.$obj->bca_id.'">'.$obj->bca_ref.'</a></td>';
$html .= '<td>'.price($comision1_dolar).'</td>';
$html .= '<td>'.price($comision2_dolar) .'</td>';
$html .= '<td>'.price($comision3_dolar).'</td>';
$html .= '</tr>';
}
$html .= '<tr>';
$html .= '<td></td>';
$html .= '<td></td>';
$html .= '<td></td>';
$html .= '<td><b>'.price($total_amount_dolar).'</b></td>';
$html .= '<td></td>';
$html .= '<td></td>';
$html .= '<td><b>'.price($total_comision1_dolar).'</b></td>';
$html .= '<td><b>'.price($total_comision2_dolar) .'</b></td>';
$html .= '<td><b>'.price($total_comision3_dolar).'</b></td>';
$html .= '</tr>';

$html .= '</tbody>';
$html .= '</thead>';
$html .= '</table>';

$html .= '<h3>Comision bancaria colones</h3>';

$html .= '<table class="tagtable liste listwithfilterbefore">';
$html .= '<thead>';
$html .= '<tr class="liste_titre">';
$html .= '<th class="liste_titre" align="left">Ref</th>';
$html .= '<th class="liste_titre" align="left">Descripcion</th>';
$html .= '<th class="liste_titre" align="left">Comision1</th>';
$html .= '<th class="liste_titre" align="left">Comision2</th>';
$html .= '<th class="liste_titre" align="left">Comision3</th>';
$html .= '<th class="liste_titre" align="left">Total</th>';
$html .= '</tr>';
$html .= '<tbody>';
$bancos = select_bancos(' AND currency_code="CRC"');

$sql = $db->query($sq);
foreach ($bancos as $b) {
$comisiones_banco = get_pagos_banco($closeid,'CRC', ' AND bca.rowid='.$b['id'].' AND pa.metodo_pago ="CB"');
if(count($comisiones_banco) > 0){
foreach ($comisiones_banco as $comisiones) {
$comision1 = $comisiones['amount'] * $comisiones['comision1']/100;
$comision2 = $comisiones['amount'] * $comisiones['comision2']/100;
$comision3 = $comisiones['amount'] * $comisiones['comision3']/100;
$total_comision += $comision1 + $comision2 + $comision3;

}
}else{
	$comision1 = 0;
	$comision2 = 0;
	$comision3 = 0;
	$total_comision += $comision1 + $comision2 + $comision3;
		
}
$html .= '<tr>';
$html .= '<td><a href="../../compta/bank/card.php?id='.$b['id'].'">'.$b['ref'].'</a></td>';
$html .= '<td><a href="../../compta/bank/card.php?id='.$b['id'].'">'.$b['label'].'</a></td>';
$html .= '<td>'.price($comision1).'</td>';
$html .= '<td>'.price($comision2).'</td>';
$html .= '<td>'.price($comision3).'</td>';
$html .= '<td>'.price($comision1 + $comision2 + $comision3).'</td>';
$html .= '</tr>';
unset($comision1,$comision2,$comision3);
}
$html .= '<tr>';
$html .= '<td></td>';
$html .= '<td></td>';
$html .= '<td></td>';
$html .= '<td></td>';
$html .= '<td></td>';
$html .= '<td><b>'.price($total_comision).'</b></td>';
$html .= '</tr>';
$html .= '</tbody>';
$html .= '</thead>';
$html .= '</table>';



$html .= '<h3>Arqueos</h3>';

$html .= '<table class="tagtable liste listwithfilterbefore">';
$html .= '<thead>';
$html .= '<tr class="liste_titre">';
$html .= '<th class="liste_titre" align="left">Ref</th>';
$html .= '<th class="liste_titre" align="left">Monto</th>';
$html .= '<th class="liste_titre" align="left">Responsable</th>';
$html .= '<th class="liste_titre" align="left">Fecha</th>';
$html .= '</tr>';
$html .= '<tbody>';
$sq = 'SELECT pc.rowid pc_id,pc.ref,pc.amount_real,u.rowid u_id,u.firstname u_firstname,u.lastname u_lastname,u.login u_login,
u2.rowid u2_id,u2.firstname u2_firstname,u2.lastname u2_lastname,u2.login u2_login,pc.date_open
FROM llx_pos_control_cash pc
LEFT JOIN llx_user u ON pc.fk_user=u.rowid
LEFT JOIN llx_user u2 ON pc.fk_responsable = u2.rowid
WHERE fk_cierre='.$closeid.' AND type_control=0';

$sql = $db->query($sq);
while($obj = $db->fetch_object($sql)){
$amount_real += $obj->amount_real;
$html .= '<tr>';
$html .= '<td>'.$obj->ref.'</td>';
$html .= '<td>'.price($obj->amount_real).'</td>';
$html .= '<td>'.$obj->u2_firstname.' '.$obj->u2_lastname.'</td>';
$html .= '<td>'.$obj->date_open.'</td>';
$html .= '</tr>';

}

$html .= '<tr>';
$html .= '<td></td>';
$html .= '<td><b>'.price($amount_real).'<b></td>';
$html .= '<td></td>';
$html .= '<td></td>';
$html .= '</tr>';
$html .= '</tbody>';
$html .= '</thead>';
$html .= '</table>';



$html .= '<h3>Comision bancaria dolares</h3>';

$html .= '<table class="tagtable liste listwithfilterbefore">';
$html .= '<thead>';
$html .= '<tr class="liste_titre">';
$html .= '<th class="liste_titre" align="left">Ref</th>';
$html .= '<th class="liste_titre" align="left">Descripcion</th>';
$html .= '<th class="liste_titre" align="left">Comision1</th>';
$html .= '<th class="liste_titre" align="left">Comision2</th>';
$html .= '<th class="liste_titre" align="left">Comision3</th>';
$html .= '<th class="liste_titre" align="left">Total</th>';
$html .= '</tr>';
$html .= '<tbody>';
$bancos_dolar = select_bancos(' AND currency_code="USD"');

$sql = $db->query($sq);
foreach ($bancos_dolar as $b) {
$comisiones_banco = get_pagos_banco($closeid,'USD', ' AND bca.rowid='.$b['id'].' AND pa.metodo_pago ="CB"');
if(count($comisiones_banco) > 0){
foreach ($comisiones_banco as $comisiones) {
	$comision1_dolar = $comisiones['multicurrency_amount'] * $comisiones['comision1']/100;
	$comision2_dolar = $comisiones['multicurrency_amount'] * $comisiones['comision2']/100;
	$comision3_dolar = $comisiones['multicurrency_amount'] * $comisiones['comision3']/100;
	$total_comision_dolar += $comision1_dolar + $comision2_dolar + $comision3_dolar;

}
}else{
	$comision1_dolar = 0;
	$comision2_dolar = 0;
	$comision3_dolar = 0;
	$total_comision_dolar += $comision1_dolar + $comision2_dolar + $comision3_dolar;
		
}
$html .= '<tr>';
$html .= '<td><a href="../../compta/bank/card.php?id='.$b['id'].'">'.$b['ref'].'</a></td>';
$html .= '<td><a href="../../compta/bank/card.php?id='.$b['id'].'">'.$b['label'].'</a></td>';
$html .= '<td>'.price($comision1_dolar).'</td>';
$html .= '<td>'.price($comision2_dolar).'</td>';
$html .= '<td>'.price($comision3_dolar).'</td>';
$html .= '<td>'.price($comision1_dolar + $comision2_dolar + $comision3_dolar).'</td>';
$html .= '</tr>';
unset($comision1_dolar,$comision2_dolar,$comision3_dolar);
}
$html .= '<tr>';
$html .= '<td></td>';
$html .= '<td></td>';
$html .= '<td></td>';
$html .= '<td></td>';
$html .= '<td></td>';
$html .= '<td><b>'.price($total_comision_dolar).'</b></td>';
$html .= '</tr>';
$html .= '</tbody>';
$html .= '</thead>';
$html .= '</table>';
$html .= '</tbody>';
$html .= '</thead>';
$html .= '</table>';

$html .='<h3 style="text-align:center">Detalle de efectivo en colones</h3>';

$html .= '<table class="tagtable liste listwithfilterbefore">';
$denominaciones = select_denominaciones(' AND muticurrency_code="CRC"');

$cont = 2;
foreach ($denominaciones as $denominacion) {

	if($denominacion['code'] <= 500){
		$icono = $denominacion['code'];
		$size = 25;
		}
		
		if($denominacion['code'] > 500){
			$size = 50;
		}
	  $sq = 'SELECT d.rowid,d.code,d.label,d.muticurrency_code,c.cantidad  FROM llx_cierre_caja_denominacion c
	  JOIN llx_c_denominaciones d ON c.fk_denominacion=d.rowid
	  WHERE fk_cierre_caja = '.$closeid.' AND c.fk_denominacion = '.$denominacion['id'].'';	

	  $sql = $db->query($sq);
	  $valor = $db->fetch_object($sql)->cantidad;
	  $valor = $valor * (int)$denominacion['code'];
	  $total_valor += $valor;
if ($cont%2 == 0) {
$html .='<tr>';
$html .= '<td>';
$html .= $denominacion['label'];
$html .= ' <img src="../frontend/img/money/CRC/'.$denominacion['code'].'.png" width="'.$size.'px"> <b>'.$valor.'</b>';
$html .= '</td>';
}else{

$html .= '<td>';
$html .= $denominacion['label'];
$html .= ' <img src="../frontend/img/money/CRC/'.$denominacion['code'].'.png" width="'.$size.'px"> <b>'.$valor.'</b>';
$html .= '</td>';
$html .='</tr>';
}


$cont++;
}
$html .= '<tr><td><strong>Total: '.price($total_valor).'</strong><td><tr>';
$html .= '</table>';



$html .='<h3 style="text-align:center">Detalle de efectivo en dolares</h3>';

$html .= '<table class="tagtable liste listwithfilterbefore">';
$denominaciones = select_denominaciones(' AND muticurrency_code="USD"');

$cont = 2;
foreach ($denominaciones as $denominacion) {

	if($denominacion['code'] <= 500){
		$icono = $denominacion['code'];
		$size = 50;
		}
		
		if($denominacion['code'] > 500){
			$size = 50;
		}
	  $sq = 'SELECT d.rowid,d.code,d.label,d.muticurrency_code,c.cantidad  FROM llx_cierre_caja_denominacion c
	  JOIN llx_c_denominaciones d ON c.fk_denominacion=d.rowid
	  WHERE fk_cierre_caja = '.$closeid.' AND c.fk_denominacion = '.$denominacion['id'].'';	

	  $sql = $db->query($sq);
	  $valor = $db->fetch_object($sql)->cantidad;
	  $valor = $valor * (int)$denominacion['code'];
	  $total_valor_dolar += $valor;
if ($cont%2 == 0) {
$html .='<tr>';
$html .= '<td>';
$html .= $denominacion['label'];
$html .= ' <img src="../frontend/img/money/USD/'.$denominacion['code'].'.png" width="'.$size.'px"> <b>'.$valor.'</b>';
$html .= '</td>';
}else{

$html .= '<td>';
$html .= $denominacion['label'];
$html .= ' <img src="../frontend/img/money/USD/'.$denominacion['code'].'.png" width="'.$size.'px"> <b>'.$valor.'</b>';
$html .= '</td>';
$html .='</tr>';
}


$cont++;
}
$html .= '<tr><td><strong>Total: '.price($total_valor_dolar).'</strong><td><tr>';
$html .= '</table>';

$html .= '<h3>Resumen efectivo</h3>';
$pagos_efectivo = get_pagos_banco($closeid,'CRC',$sqls=' AND pa.metodo_pago="LIQ"');
$arqueos = get_arqueos($terminalid,$closeid);
foreach($pagos_efectivo as $p){
$pagos_liq +=(float)$p['amount'];
}
$dif_efectivo = ($pagos_liq - (float)$arqueos['amount_real']) - $total_valor;
$html .= '<table class="tagtable liste listwithfilterbefore">';
$html .= '<thead>';
$html .= '<tr class="liste_titre">';
$html .= '<th>Total en efectivo teorico</th>';
$html .= '<th>Total en efectivo real</th>';
$html .= '<th>Total en arqueos</th>';
$html .= '<th>Diferencia</th>';
$html .= '</tr>';
$html .= '</thead>';
$html .= '<tr>';
$html .= '<td>'.price($pagos_liq).'</td>';
$html .= '<td>'.price($total_valor).'</td>';
$html .= '<td>'.price($arqueos['amount_real']).'</td>';
$html .= '<td>'.price($dif_efectivo).'</td>';
$html .= '<tr>';
$html .= '</table>';

$html .= '<h3>Resumen tarjeta</h3>';
$pagos_tarjeta = get_pagos_banco($closeid,'CRC',$sqls=' AND pa.metodo_pago="CB"');
$arqueos = get_arqueos($terminalid,$closeid);
foreach($pagos_tarjeta as $p){
$pagos_cb +=(float)$p['amount'];
}
$html .= '<table class="tagtable liste listwithfilterbefore">';
$html .= '<thead>';
$html .= '<tr class="liste_titre">';
$html .= '<th>Total en tarjeta</th>';
$html .= '</tr>';
$html .= '</thead>';
$html .= '<tr>';
$html .= '<td>'.price($pagos_cb).'</td>';
$html .= '<tr>';
$html .= '</table>';


echo $html;
	?>


<script type="text/javascript">

	window.print();

</script>

<a class="lien" href="#" onclick="javascript: window.close(); return(false);">Fermer cette fenetre</a>

</body>