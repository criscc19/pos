<?php
$res=@include("../../main.inc.php");                                   // For root directory
if (! $res) $res=@include("../../../main.inc.php");                // For "custom" directory

dol_include_once('/pos/backend/class/pos.class.php');
//dol_include_once('/pos/frontend/class/mobile_detect.php');
require_once(DOL_DOCUMENT_ROOT."/product/stock/class/entrepot.class.php");
require_once(DOL_DOCUMENT_ROOT."/pos/frontend/include/funciones.php");
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';
dol_include_once('/pos/backend/class/ticket.class.php');
require_once(DOL_DOCUMENT_ROOT."/core/class/html.formother.class.php");
require_once(DOL_DOCUMENT_ROOT ."/core/lib/date.lib.php");

$langs->load('pos@pos');
$langs->load('deliveries');
$langs->load('companies');

$ticketyear=GETPOST('ticketyear','int');
$ticketmonth=GETPOST('ticketmonth','int');
$deliveryyear=GETPOST('deliveryyear','int');
$deliverymonth=GETPOST('deliverymonth','int');
$sref=GETPOST('sref','alfa');
$sref_client=GETPOST('sref_client','alfa');
$snom=GETPOST('snom');
$sall=GETPOST('sall');
$socid=GETPOST('socid','int');
$viewstatut=GETPOST('viewstatut','int');

// Security check
$ticketid = GETPOST('ticketid','int');//isset($_GET["ticketid"])?$_GET["ticketid"]:'';
if ($user->societe_id) $socid=$user->societe_id;

$sortfield = GETPOST('sortfield','alpha');
$sortorder = GETPOST('sortorder','alpha');
$page = (int)GETPOST('page','int');

if ($page == -1) { $page = 0; }
$offset = $conf->liste_limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;

$month	=GETPOST('month','int');
$year	=GETPOST('year','int');

$limit = $conf->liste_limit;
if (! $sortorder) $sortorder='DESC';
if (! $sortfield) $sortfield='f.date_c';
?>
<!DOCTYPE html>
<html lang="es">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" type="image/png" href="img/favicon.png"/>
		<title>Venta</title>

		<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
        <script src="https://kit.fontawesome.com/62c4e05a29.js"></script>	
        <link rel='stylesheet'   href='css/easy-autocomplete.css' type='text/css' media='all' />	
        <link rel='stylesheet'   href='css/bootstrap.min.css' type='text/css' media='all' />       
		<link rel='stylesheet'   href='css/mdb.min.css' type='text/css' media='all' />		
        <link rel="stylesheet" href="sweetalert2/dist/sweetalert2.min.css">
		<link rel='stylesheet'   href='css/main.css' type='text/css' media='all' />
	</head>
    <body class="fixed-sn mdb-skin">  
    <?php include 'tpl/menu2.tpl.php'; ?>
    
  <div id="barra_c" class="progress md-progress bg-success" style="position: sty;position: sticky;top: 0;z-index: 99999;display:none">
  <div class="indeterminate"></div></div> 
  <!--/.Double navigation-->
<div id="product_content" class="product_content">
<div id="product_arrow"><i class="fas fa-caret-left fa-4x"></i></div>
</div>
       <!--Main Layout-->
    <div class="container-fluid mt-2">


<?php 
$html = new FormOther($db);
$now=dol_now();
$ticketstatic=new Ticket($db);

if ($page == -1) $page = 0 ;

$sql = 'SELECT ';
$sql.= ' f.rowid, f.ref, f.fk_cash, f.fk_user, f.amount_teor, f.amount_real,  f.amount_diff,';
$sql.= ' f.date_c, f.type_control';
$sql.= ' from '.MAIN_DB_PREFIX.'pos_control_cash as f';
$sql.= " WHERE f.entity = ".$conf->entity;
if ($viewstatut <> '') $sql.= ' AND f.type_control = '.$viewstatut;

if ($_GET['filtre'])
{
	$filtrearr = explode(',', $_GET['filtre']);
	foreach ($filtrearr as $fil)
	{
		$filt = explode(':', $fil);
		$sql .= ' AND ' . trim($filt[0]) . ' = ' . trim($filt[1]);
		}
}
if ($_GET['search_ref'])
{
	 $sql.= ' AND f.rowid LIKE \'%'.$db->escape(trim($_GET['search_ref'])).'%\'';
}
if ($_GET['search_user'])
{
	$sql.= ' AND s.nom LIKE \'%'.$db->escape(trim($_GET['search_user'])).'%\'';
}

if ($month > 0)
{
	if ($year > 0)
		$sql.= " AND f.date_c BETWEEN '".$db->idate(dol_get_first_day($year,$month,false))."' AND '".$db->idate(dol_get_last_day($year,$month,false))."'";
	else
	$sql.= " AND date_format(f.date_c, '%m') = '".$month."'";
}
else if ($year > 0)
{
	$sql.= " AND f.date_c BETWEEN '".$db->idate(dol_get_first_day($year,1,false))."' AND '".$db->idate(dol_get_last_day($year,12,false))."'";
}
if ($_POST['sf_ref'])
{
	$sql.= ' AND rowid LIKE \'%'.$db->escape(trim($_POST['sf_ref'])) . '%\'';
	}
if ($sall)
{
	$sql.= ' AND (s.nom LIKE \'%'.$db->escape($sall).'%\' OR f.ticketnumber LIKE \'%'.$db->escape($sall).'%\' OR f.note LIKE \'%'.$db->escape($sall).'%\' OR fd.description LIKE \'%'.$db->escape($sall).'%\')';
}
$sql.= ' AND f.fk_user='.$_SESSION['uid'].'';
if (! $sall)
{
	/*$sql.= ' GROUP BY f.rowid, f.type, f.increment, f.total_ht, f.total_ttc,';
	$sql.= ' f.date_c, ';
	$sql.= ' f.paye, f.fk_statut,';*/
}
$sql.= ' ORDER BY ';
$listfield=explode(',',$sortfield);
foreach ($listfield as $key => $value) $sql.= $listfield[$key].' '.$sortorder.',';
$sql.= ' f.rowid DESC ';
$sql.= $db->plimit($limit+1,$offset);
        //print $sql;

$resql = $db->query($sql);
if ($resql)
{
	$num = $db->num_rows($resql);

	if ($socid)
	{
		$soc = new Societe($db);
		$soc->fetch($socid);
	}

	$param='&amp;socid='.$socid;
	if ($month) $param.='&amp;month='.$month;
	if ($year)  $param.='&amp;year=' .$year;

	if($viewstatut)
		$label=$langs->trans('CloseList');
	else
		$label=$langs->trans('ArchingList');
	
print '<h1 class="text-center">Cierres de caja</h1>';
	$i = 0;
	print '<form method="get" action="'.$_SERVER["PHP_SELF"].'">'."\n";
	print '<table class="table table-bordered table-striped" width="100%">';
print '<tr class="btn-dark">
<th scope="col">Ref</th>
<th scope="col">Tipo</th>
<th scope="col">Fecha</th>
<th scope="col">Caja</th> 
<th scope="col">Usuario</th>      
<th scope="col">Importe teorico</th>
<th scope="col">Importe real</th>
<th scope="col">Diferencia</th>                   
</tr>';
	if ($num > 0)
	{
		$var=True;
		$total=0;
		$totalrecu=0;
		
		$controlstatic = new ControlCash($db, $objp->fk_cash);
		$controlstatic->type_control = $objp->type_control;
		
		while ($i < min($num,$limit))
		{
			$objp = $db->fetch_object($resql);
			$var=!$var;

			//$datelimit=$db->jdate($objp->datelimite);

			print '<tr '.$bc[$var].'>';
			print '<td nowrap="nowrap">';

			print '<table class="nobordernopadding"><tr class="nocellnopadd">';

			//Id cash control
			
			print '<td class="nobordernopadding" nowrap="nowrap">';
			if($objp->type_control){
				if($conf->global->POS_FACTURE == 1){
					print '<a href="cierre.php?closeid='.$objp->rowid.'&terminalid='.$objp->fk_cash.'&viewstatut=2">'.$objp->ref.'</a>';
				}
				else{	
					print '<a href="cierre.php?closeid='.$objp->rowid.'&terminalid='.$objp->fk_cash.'&viewstatut=2">'.$objp->ref.'</a>';
				}
			}
			else {
				print $objp->ref;
			}
			print '</td>';

			print '</tr></table>';

			print "</td>\n";
			
			//Type
	        print '<td align="left" nowrap="nowrap">';
			print $controlstatic->LibStatut($objp->type_control,2);
			print "</td>";;

			// Date
			print '<td align="center" nowrap>';
			print dol_print_date($db->jdate($objp->date_c),'day');
			print '</td>';
			
			//Cash
			print '<td>';
			$cash=new Cash($db);
			$cash->fetch($objp->fk_cash);
			print $cash->getNomUrl(1);
			print '</td>';
	
			//User
			$userstatic=new User($db);
	        $userstatic->fetch($objp->fk_user); 
	        print "<td>".$userstatic->getNomUrl(1)."</td>\n";
	        
	        //Teoric
	        print '<td align="right">'.price($objp->amount_teor).'</td>';
	        
	        //Real
	        print '<td align="right">'.price($objp->amount_real).'</td>';
	        
	        //Diff
	        print '<td align="right">'.price($objp->amount_diff).'</td>';
	        /*
	        //Out
	        print '<td align="right"></td>';
	        
	        //In
	        print '<td align="right"></td>';
	        
	        //Next day
	        print '<td align="right"></td>';*/
	        
            		
           $i++;
		}

		
	}

	print "</table>\n";
	print "</form>\n";
	$db->free($resql);
	
	/*print '<div class="tabsAction">';
	print '<a class="butAction" href="'.DOL_URL_ROOT.'/pos/backend/closes/fiche.php">'.$langs->trans('NewClose').'</a>';
	print '</div>';*/
}
else
{
	dol_print_error($db);
}
?>
    </div>
  <!--Main Layout-->
</body>
</html> 
<script type='text/javascript' src='js/jquery.easy-autocomplete.js'></script>
<script type='text/javascript' src='js/bootstrap.min.js'></script>
<script type='text/javascript' src='js/mdb.min.js'></script>
<script src="sweetalert2/dist/sweetalert2.min.js"></script>
<script src="js/numeral/numeral.js"></script>
<script>
$(".button-collapse").sideNav();
</script>
<?php
$db->close();
?>