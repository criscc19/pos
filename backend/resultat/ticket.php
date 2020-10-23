<?php

$res=@include("../../../main.inc.php");                                   // For root directory
if (! $res) $res=@include("../../../../main.inc.php");                // For "custom" directory

dol_include_once('/pos/backend/class/ticket.class.php');
dol_include_once('/pos/backend/class/cash.class.php');
require_once(DOL_DOCUMENT_ROOT."/core/lib/report.lib.php");
require_once(DOL_DOCUMENT_ROOT ."/core/lib/date.lib.php");
dol_include_once('/pos/backend/class/pos.class.php');

$langs->load('pos@pos');
$langs->load('deliveries');
$langs->load('companies');

$mesg = $_SESSION['message'];
$_SESSION['message'] = '';

if(isset($_POST['date_start'])){ $fecha1 = $_POST['date_start'];}else{$fecha1 = date('Y-m-d');}
if(isset($_POST['date_end'])){ $fecha2 = $_POST['date_end'];}else{$fecha2 = date('Y-m-d');}

$page = (int)GETPOST("page",'int');
if ($page == -1) { $page = 0; }
$pageprev = $page - 1;
$pagenext = $page + 1;

if (! $sortorder) $sortorder='DESC';
if (! $sortfield) $sortfield='f.date_ticket';

/*
 * View
 */
$helpurl='EN:Module_DoliPos|FR:Module_DoliPos_FR|ES:M&oacute;dulo_DoliPos';
llxHeader("",$langs->trans("Tickets"),$helpurl);
dol_htmloutput_mesg($mesg);
print '<form method="POST" action="/pruebas/pos/backend/resultat/ticket.php">';
print '<div class="tabs" data-role="controlgroup" data-type="horizontal">';
print '<div class="inline-block tabsElem tabsElemActive">';
print '<a id="report" class="tabactive tab inline-block" href="/pruebas/pos/backend/resultat/ticket1.php">Informe</a>';
print '</div></div>';
print '<div class="tabBar tabBarWithBottom">';
print '<table width="100%" class="border">';
print '<tbody>';
print '<tr>';
print '<td width="110" style="font-size: 1.17em;font-weight: bold; color: #620909;margin-left:10px;">Nombre del informe</td>';
print '<td><h3>Reporta de ventas por terminales.</h3></td>';
print '</tr>';
print '<tr>';
print '<td style="font-size: 1.17em;font-weight: bold; color: #620909;margin-left:10px;">Periodo de análisis</td>';
print '<td><input type="date" name="date_start" value="'.$fecha1.'">';
print '- <input type="date" name="date_end" value="'.$fecha2.'"> </td>';
print '</tr>';
print '<tr>';
print '<td style="font-size: 1.17em;font-weight: bold; color: #620909;margin-left:10px;">Terminal</td>';
print '<td>';
print '<select id="terminal" name="terminal[]" multiple class="maxwidth100onsmartphone quatrevingtpercent">';
print '<option value="-1"></option>';
    $seu = $db->query('select ss.rowid,ss.name from llx_pos_cash ss');
        for ($p = 1; $p <= $db->num_rows($seu); $p++) {	
            $obs = $db->fetch_object($seu);
            if(in_array($obs->rowid,$_POST['terminal']))
                print '<option  selected value="'.$obs->rowid.'" style="margin-right:10px;">'.$obs->name.' '.'</option>';
            else
                print '<option value="'.$obs->rowid.'" style="margin-right:10px;">'.$obs->name.' '.'</option>';
        }
print '</select>';
print'</td>';
print '</tr>';
print '<tr>';
print '<td style="font-size: 1.17em;font-weight: bold; color: #620909;margin-left:10px;">Descripción</td>';
print '<td><h4>Reporte de cierre de ventas por terminal(No incluye facturas desde financiero)</h4></td>';
print '</tr><tr>';
print '<td style="font-size: 1.17em;font-weight: bold; color: #620909;margin-left:10px;">Generado el </td>';
print '<td>'.date('Y-m-d H:i:s').'</td>';
print '</tr></tbody></table>
</div>
<div class="center"><input type="submit" class="button" name="submit" value="Refrescar"></div></form>';
//Gestiones del ticker


$p = explode(":", $conf->global->MAIN_INFO_SOCIETE_PAYS);
$idpays = $p[0];


$now=dol_now();

if (!$user->rights->pos->backend)
{
	print '<a href="'.dol_buildpath('/pos/frontend/index.php',1).'"><img src='.dol_buildpath('/pos/frontend/img/bgback.jpg',1).' WIDTH="100%" HEIGHT="100%" ></a>';
}	
else {
    if ($page == -1) $page = 0 ;
    //gestiones de la facturas del pos
    $sql='SELECT ';
    $sql.=' fac.rowid idfac, fac.datef,cash.datec,fac.facnumber,fac.total,fac.ref_client, us.firstname,pos.ref cierre,poscash.name,fac.tva ';
    $sql.='FROM llx_facturas_cash cash ';
    $sql.=' inner join llx_facture fac on fac.rowid = cash.fk_facture ';
    $sql.=' inner join llx_user us on us.rowid=cash.fk_user ';
	$sql.='inner join llx_pos_control_cash pos on pos.rowid=cash.fk_cierre ';
	$sql.='inner join llx_pos_cash poscash on poscash.rowid = cash.fk_terminal ';
    $sql.=' WHERE cash.datec BETWEEN "'.date('Y-m-d 00:00:00',strtotime($_POST['date_start'])).'" and "'.date('Y-m-d 23:59:59.000000',strtotime($_POST['date_end'])).'"';
    if(count($_POST['terminal'] > 0) && isset($_POST['terminal'])){
		$sql.=' and cash.fk_terminal IN ('.implode(',',$_POST['terminal']).')';
	}
   // print $sql;
	$i = 0;
	print '<form method="get" action="'.$_SERVER["PHP_SELF"].'">'."\n";
	print '<table class="liste" width="100%">';
	print '<tr class="liste_titre">';
    print '<td align="center"><b>Fecha</b></td>';
    print '<td align="center"><b>Numero</b></td>';
    print '<td align="center"><b>Usuario</b></td>';
    print '<td align="center"><b>Cliente</b></td>';
	print '<td align="center"><b>Terminal</b></td>';
	print '<td align="center"><b>IVA</b></td>';
    print '<td align="center"><b>Monto ¢</b></td>';
    print '</tr>';
    print '<tbody>';

	$result= $db->query($sql);
	$totalfinal =0;
	$totalfinal1 =0;
    for ($p = 1; $p <= $db->num_rows($result); $p++) {
        $object = $db->fetch_object($result);
    print '<tr>';
	print '<td align="center">'.dol_print_date(strtotime($object->datec), 'dayhour', 'tzuser').'</td>';
	//<a href="../compta/facture/card.php?facid=7">
    print '<td align="center"><a href="../../../compta/facture/card.php?facid='.$object->idfac.'">'.$object->facnumber.'</a></td>';
    print '<td align="center">'.$object->firstname.'</td>';
    print '<td align="center">'.$object->ref_client.'</td>';
	print '<td align="center">'.$object->name.'</td>';
	print '<td align="center">'.price($object->tva).'</td>';
	print '<td align="center">'.price($object->total).'</td>';
	$totalfinal+=$object->total;
	$totalfinal1+=$object->tva;
    print '</tr>';
	}
	print '<tr class="liste_titre">';
	print '<td></td>';
    print '<td></td>';
    print '<td ></td>';
    print '<td ></td>';
	print '<td align="center"><b>Total:</b></td>';
	print '<td align="center"><b>'.price($totalfinal1).'</b></td>';
    print '<td align="center"><b>'.price($totalfinal).'</b></td>';
    print '</tr>';
    print '</tbody>';
	print "</table>\n";
	print "</form>\n";
		
}
llxFooter();

$db->close();
?>