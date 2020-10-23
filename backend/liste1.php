<?php
/** 
 * *
**/

$res=@include("../../main.inc.php");                                   // For root directory
if (! $res) $res=@include("../../../main.inc.php");                // For "custom" directory

dol_include_once('/pos/backend/class/ticket.class.php');
require_once(DOL_DOCUMENT_ROOT."/core/class/html.formother.class.php");
dol_include_once('/pos/backend/class/cash.class.php');
require_once(DOL_DOCUMENT_ROOT ."/core/lib/date.lib.php");
dol_include_once('/pos/backend/class/pos.class.php');

$langs->load('pos@pos');
$langs->load('deliveries');
$langs->load('companies');

$ticketyear=GETPOST("ticketyear","int");
$ticketmonth=GETPOST("ticketmonth","int");
$deliveryyear=GETPOST("deliveryyear","int");
$deliverymonth=GETPOST("deliverymonth","int");
$socid=GETPOST('socid','int');
$userid=GETPOST('userid','int');
$viewstatut=GETPOST('viewstatut');
$viewtype=GETPOST('viewtype');
$closeid=GETPOST('closeid','int');
$placeid=GETPOST('placeid','int');
$cashid=GETPOST('cashid','int');
$terminalid=GETPOST('terminalid','int');
$action=GETPOST('action','string');

$mesg = $_SESSION['message'];
$_SESSION['message'] = '';

$sortfield = GETPOST("sortfield",'alpha');
$sortorder = GETPOST("sortorder",'alpha');
$page = (int)GETPOST("page",'int');
if ($page == -1) { $page = 0; }
$offset = $conf->liste_limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;

$month    =GETPOST('month','int');
$year     =GETPOST('year','int');

$limit = $conf->liste_limit;

$titulo='Reportes de Caja';
llxHeader('',$titulo,'','',0,0,$arrayofjs,$arrayofcss);
	print '<form method="get" action="'.$_SERVER["PHP_SELF"].'" method="POST">';
    print '<table class="liste" width="100%">';
    //para la gestion del ciere de caja
    print '<tr class="liste_titre" style="text-align: center;">';
    print '<td></td>';
    print '<td></td>';
    print '<td></td>';
    print '<td></td>';
    print '<td></td>';
    print '<td></td>';
    print '<td></td>';
    print '</tr>';
    print '<tr class="liste_titre" style="text-align: center;">';
    print '<td>Fecha</td>';
  
    print '<td>facnumber #</td>';
    print '<td></td>';
    print '<td>₡</td>';
    print '<td></td>';
    print '<td>Dolares</td>';
    print '</tr>';
    print '<tbody>';
    if(GETPOST('closeid')!=NULL){
        global $db;
        $idcierre =GETPOST('closeid');
        $sq='SELECT f.rowid,
        f.datec,
        f.datef,
        f.tva,
        f.total,
        f.facnumber,
        f.ref_client,
        f.total_ttc,
        pc.rowid pc_id FROM llx_facturas_cash fc
        JOIN llx_facture f ON fc.fk_facture=f.rowid
        JOIN llx_pos_control_cash pc ON fc.fk_cierre=pc.rowid
        WHERE pc.rowid='.$idcierre.' AND f.multicurrency_code = "USD"';
        $sql = $db->query($sq);
        if(count($sql)>0){
            while($obj = $db->fetch_object($sql)){
                print '<tr style="text-align: center;">';
                print '<th>'.$obj->facnumber.'</th>';
                print '<th>'.$obj->datec.'</th>';
                print '<th>'.$obj->ref_client.'</th>';
                print '<th>'.price($obj->tva).'</th>';
                print '<th>'.price($obj->total).'</th>';
                print '<td></td>';
                print '<th>'.price($obj->total_ttc).'</th>';//iva
                
                print '</tr>';
            }
        }
        //funcion par poder ver los los bancos y sus posibles descuentos en dolares
        print '<tr class="liste_titre" style="text-align: center;">';
        print '<td></td>';
        print '<td></td>';
        print '<td></td>';
        print '<td>Bancos Dolares</td>';
        print '<td></td>';
        print '<td></td>';
        print '<td></td>';
        print '</tr>';
        print '<tr class="liste_titre" style="text-align: center;">';
        print '<td>Banco</td>';
        print '<td>Codigo</td>';
        print '<td>Comision1</td>';
        print '<td>Comision2</td>';
        print '<td>Comision3</td>';
        print '<td>Subtotal</td>';
        print '<td>Bruto</td>';
        print '</tr>';
        print '<tbody>';

        $sq='SELECT com.multi_currency_code,
        com.ref,
        com.label,
        com.comision1,
        com.comision2,
        com.comision3,
        bank.monto,
        cie.tarjeta
        from llx_banco_comision com 
        inner join llx_cierre_caja_denominacion_bank bank on com.rowid =bank.fk_bank 
        inner join llx_cierres_caja cie on bank.fk_cierre_caja =cie.rowid 
        inner join llx_pos_control_cash pos on pos.rowid=bank.fk_cierre_caja 
        where bank.fk_cierre_caja='.$idcierre;
         $sql = $db->query($sq); 
        // var_dump($sql);exit;
        $bruto=0;
         while($obj = $db->fetch_object($sql)){
             $monto=price($obj->monto);
             $com1=price($obj->comision1);
             $com2=price($obj->comision2);
             $com3=price($obj->comision3);
            
             if($obj->multi_currency_code =="USD"){
                print '<tr style="text-align: center;">';
                print '<th>'.$obj->label.'</th>';
                print '<th>'.$obj->ref.'</th>';
                print '<th>'.$com1.'</th>';
                print '<th>'.$com2.'</th>';
                print '<th>'.$com3.'</th>';
                print '<th>'.price($obj->monto).'</th>';
                //validacion para hacer la deduccion de las comisiones
                $bruto =$monto-($monto*(($com1+$com2+$com3)/100));
                print '<th>'.$bruto.'</th>';
                print '</tr>';
                $bruto=0;
             }
           
         }

          //funcion par poder ver los los bancos y sus posibles descuentos en Colones
        print '<tr class="liste_titre" style="text-align: center;">';
        print '<td></td>';
        print '<td></td>';
        print '<td></td>';
        print '<td>Bancos Colones</td>';
        print '<td></td>';
        print '<td></td>';
        print '<td></td>';
        print '</tr>';
        print '<tr class="liste_titre" style="text-align: center;">';
        print '<td>Banco</td>';
        print '<td>Codigo</td>';
        print '<td>Comision1</td>';
        print '<td>Comision2</td>';
        print '<td>Comision3</td>';
        print '<td>Subtotal</td>';
        print '<td>Bruto</td>';
        print '</tr>';
        print '<tbody>';

        $sq='SELECT com.multi_currency_code,
        com.ref,
        com.label,
        com.comision1,
        com.comision2,
        com.comision3,
        bank.monto,
        cie.tarjeta
        from llx_banco_comision com 
        inner join llx_cierre_caja_denominacion_bank bank on com.rowid =bank.fk_bank 
        inner join llx_cierres_caja cie on bank.fk_cierre_caja =cie.rowid 
        inner join llx_pos_control_cash pos on pos.rowid=bank.fk_cierre_caja 
        where bank.fk_cierre_caja='.$idcierre;
         $sql = $db->query($sq); 
        // var_dump($sql);exit;
        $bruto=0;
         while($obj = $db->fetch_object($sql)){
             $monto=price($obj->monto);
             $com1=price($obj->comision1);
             $com2=price($obj->comision2);
             $com3=price($obj->comision3);
             if($obj->multi_currency_code =="CRC"){
                print '<tr style="text-align: center;">';
                print '<th>'.$obj->label.'</th>';
                print '<th>'.$obj->ref.'</th>';
                print '<th>'.$com1.'</th>';
                print '<th>'.$com2.'</th>';
                print '<th>'.$com3.'</th>';
                print '<th>'.price($obj->monto).'</th>';
                //validacion para hacer la deduccion de las comisiones
                $bruto =$monto-($monto*(($com1+$com2+$com3)/100));
                print '<th>'.$bruto.'</th>';
                print '</tr>';
                $bruto=0;
             }
           
         }
    }
    print '</tbody>';
	print '</table>';
    print '</form>';
    
include_once('../frontend/include/nuevas_funciones.php');
//var_dump(select_bancos_comision());
llxFooter();

$db->close();
?>