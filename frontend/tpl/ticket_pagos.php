<?php
require '../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/expedition/class/expedition.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/html.formproduct.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/order.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/sendings.lib.php';

require_once DOL_DOCUMENT_ROOT.'/compta/paiement/class/paiement.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
require_once DOL_DOCUMENT_ROOT .'/core/modules/facture/modules_facture.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/payments.lib.php';
if(DOL_VERSION < 11){
	$facnumber = 'facnumber';
	}else{
	$facnumber = 'ref';  
	}	
if (! empty($conf->projet->enabled)) {
	require_once DOL_DOCUMENT_ROOT . '/projet/class/project.class.php';
	require_once DOL_DOCUMENT_ROOT . '/core/class/html.formprojet.class.php';
}
if (! empty($conf->stock->enabled))  require_once DOL_DOCUMENT_ROOT.'/product/stock/class/entrepot.class.php';
if (! empty($conf->propal->enabled)) require_once DOL_DOCUMENT_ROOT.'/comm/propal/class/propal.class.php';
if (! empty($conf->product->enabled) || ! empty($conf->service->enabled)) 	require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';

$langs->loadLangs(array('orders',"companies","bills",'propal','deliveries','stocks',"productbatch",'incoterm'));

$id=GETPOST('id','int');			// id of order


/* ************************************************************ACTUALIZAR ESTADO DE IMPRESION
******************************************************/	

	$sqlU = "UPDATE ".MAIN_DB_PREFIX."paiement";
	$sqlU.= " SET impresion = 1";
	$sqlU.= " WHERE rowid = " . $id;

	$db->query($sqlU);

/* ************************************************************TABLA PARA GENERAR PDF DE PAGOS ******************************************************/		
$logodir = $conf->mycompany->dir_output;
$logo = $logodir.'/logos/'.$mysoc->logo;
	//CONSTRUCCION DEL TITULO DEL DOCUMENTO

	$html .= '<table width="100%" border="0">';
	$html .='<tr align="center" style="font-size: 20px;">';
	$html .='<td>&nbsp;</td>';
	$html .='<td style="font-weight: bold; text-align:center; color:#800000">Pagos</td>';
	$html.= '<td><img src="'.$logo.'" width="120"></td>';
	//$html.= '<td><img src="https://erp.cr/impafesademo/viewimage.php?cache=1&modulepart=companylogo&file='.urlencode($mysoc->logo).'" width="120"></td>';
	$html .='</tr>';
	$html .= "</table>";

	//TERCERA CONSULTA PARA OBTENER EL DIFERENCIAL DEL PAGO
	$sql3="SELECT b.amount monto FROM llx_paiement p JOIN llx_bank b ON p.fk_bank=b.rowid WHERE p.rowid=".$id;

	$difB=0;

	$resql3 = $db->query($sql3);
	if ($resql3){
		$num3 = $db->num_rows($resql3);
		$i3 = 0;
		while ($i3 < $num3){

			$objp3 = $db->fetch_object($resql3);

			$difB=$objp3->monto;

			$i3++;
		}		
		$db->free($resql3);
	}else{
		dol_print_error($db);
	}

	 //SEGUNDA CONSULTA PARA OBTENER LA INFORMACION GENERAL DEL PAGO
	$sql2="SELECT date(p.datep) as fecha,p.num_paiement,p.amount,p.ref,ba.label,ba.number FROM llx_paiement p
	join llx_bank b on p.fk_bank=b.rowid
	join llx_bank_account ba on ba.rowid=b.fk_account
	where p.rowid=".$id;

	$html .= '<table width="100%" border="0">';
	$resql2 = $db->query($sql2);

	$deposito=0;

	if ($resql2){
		$num2 = $db->num_rows($resql2);
		$i2 = 0;
		while ($i2 < $num2){

			$objp2 = $db->fetch_object($resql2);

			$deposito=$objp2->amount;

			$html .='<tr align="left">';
    		$html .='<td><strong>Referencia:</strong> '.$objp2->ref.'</td>';
    		$html .='</tr>';

			$html .='<tr align="left">';
			$date = new DateTime($objp2->fecha);
    		$html .='<td><strong>Fecha:</strong> '.$date->format('d-m-Y').'</td>';
    		$html .='</tr>';

    		$html .='<tr align="left">';
    		$html .='<td><strong>N&uacute;mero:</strong> '.$objp2->num_paiement.'</td>';
    		$html .='</tr>';

    		$html .='<tr align="left">';
    		$html .='<td><strong>Monto del dep&oacute;sito:</strong> '.price($objp2->amount).'</td>';
    		$html .='</tr>';

    		$html .='<tr align="left">';
    		$html .='<td><strong>Cuenta Bancaria:</strong> '.$objp2->label.'</td>';
    		$html .='</tr>';

    		$html .='<tr align="left">';
    		$html .='<td><strong>N&uacute;mero de cuenta:</strong> '.$objp2->number.'</td>';
  			$html .='</tr>';

			$i2++;
		}		
		$db->free($resql2);
	}else{
		dol_print_error($db);
	}

	$html .= "</table><br><br>";

	//PRIMERA CONSULTA PARA OBTENER EL DESGLOSE DE LOS MONTOS DE LOS PAGOS
	$sql="SELECT f.rowid as facid, f.".$facnumber.", f.total_ttc, f.fk_statut, pf.amount, s.nom as name, p.note
	 FROM llx_facture as f
	 JOIN llx_paiement_facture pf ON pf.fk_facture=f.rowid
	 JOIN llx_societe s ON f.fk_soc = s.rowid
	 JOIN llx_paiement p ON pf.fk_paiement=p.rowid
	 WHERE pf.fk_paiement =".$id." GROUP by f.rowid";

 	$html .= '<table width="100%" border="0" cellpadding="1"style="font-size:8px"> 
	    <tr class="" style="text-align:center; background-color: #000000; color:#ffffff">
	    <td>Factura</td>
	    <td>Empresa</td>
	    <td>Recibo/Nota</td>
		<td>Esperando el pago</td>
	    <td>Pagada por este pago</td>
	    <td>Resta por pagar</td>
	    <td>Estado</td>

	  	</tr>';

	$resql = $db->query($sql);
	$total_total_ttc=0;
	//$total_amount=0;
	$total_remaintopay=0;
	//$factura="vacio";

	if ($resql){
		$num = $db->num_rows($resql);
		$i = 0;
		$total = array();
		while ($i < $num){
			$objp = $db->fetch_object($resql);

			$invoice=new Facture($db);
			$invoice->fetch($objp->facid);

			$paiement = $invoice->getSommePaiement();
			$creditnotes=$invoice->getSumCreditNotesUsed();
			$deposits=$invoice->getSumDepositsUsed();
			$alreadypayed=price2num($paiement + $creditnotes + $deposits,'MT');
			$remaintopay=price2num($invoice->total_ttc - $paiement - $creditnotes - $deposits,'MT');

			$total_total_ttc+=$objp->total_ttc;
			$total[]=$objp->amount;
			$total_remaintopay+=$remaintopay;

			$estadoF=($objp->fk_statut==2)?"Pagada":"Pago parcial";

			/*if($factura==$objp->facnumber){
				$html .= '<tr style="text-align:center;color:#ff0000;">';
			}else{
				$html .= '<tr style="text-align:center;">';
			}*/
			$html .= '<tr style="text-align:center;">';

			$html .= '<td>'.$objp->$facnumber.'</td>';
			$html .= '<td>'.$objp->name.'</td>';
			if($objp->recibo==null){
				$html .= '<td>'.$objp->note.'</td>';
			}else{
				$html .= '<td>'.$objp->recibo.'</td>';
			}
			$html .= '<td>'.price($objp->total_ttc).'</td>';
			$html .= '<td>'.price($objp->amount).'</td>';
			$html .= '<td>'.price($remaintopay).'</td>';
			$html .= '<td>'.$estadoF.'</td>';
			$html .= '</tr>';

			//$factura=$objp->facnumber;

			$i++;
		}
		
		$db->free($resql);

		$html .= '<tr style="text-align:center;">';
		$html .= '<td>'.'&nbsp;'.'</td>';
		$html .= '<td>'.'&nbsp;'.'</td>';
		$html .= '<td>'.'&nbsp;'.'</td>';
		$html .= '<td>'.'&nbsp;'.'</td>';
		$html .= '<td colspan="3" style="text-align:center; background-color: #800000; color:#ffffff">'.'Totales'.'</td>';
		$html .= '</tr>';

		$html .= '<br>';

		$html .= '<tr>';
		$html .= '<td>'.'&nbsp;'.'</td>';
		$html .= '<td>'.'&nbsp;'.'</td>';
		$html .= '<td>'.'&nbsp;'.'</td>';
		$html .= '<td>'.'&nbsp;'.'</td>';
		$html .= '<td colspan="2" style="border-bottom: 1px solid #ddd; text-align:left;">'.'Pagada por este pago'.'</td>';
		$html .= '<td style="border-bottom: 1px solid #ddd; text-align:right;">'.price(array_sum($total)).'</td>';
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= '<td>'.'&nbsp;'.'</td>';
		$html .= '<td>'.'&nbsp;'.'</td>';
		$html .= '<td>'.'&nbsp;'.'</td>';
		$html .= '<td>'.'&nbsp;'.'</td>';
		$html .= '<td colspan="2" style="border-bottom: 1px solid #ddd; text-align:left;">'.'Diferencial pago dep&oacute;sito'.'</td>';
		$html .= '<td style="border-bottom: 1px solid #ddd; text-align:right;">'.price($deposito-array_sum($total)).'</td>';
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= '<td>'.'&nbsp;'.'</td>';
		$html .= '<td>'.'&nbsp;'.'</td>';
		$html .= '<td>'.'&nbsp;'.'</td>';
		$html .= '<td>'.'&nbsp;'.'</td>';
		$html .= '<td colspan="2" style="border-bottom: 1px solid #ddd; text-align:left;">'.'Diferencial banco dep&oacute;sito'.'</td>';
		$html .= '<td style="border-bottom: 1px solid #ddd; text-align:right;">'.price($difB).'</td>';
		$html .= '</tr>';
	}else{
		dol_print_error($db);
	}
	$html .= "</table>";

/****************************************************** GENERAR PDF ************************************************/
$htm='<style type="text/css">
.cabecera {
	border-bottom: 1px solid black;
}
.productos {
	border-bottom: 1px solid gray;
}
.texto {
	color: #FFF;
}
</style>'.$html;

require_once '../../../includes/tecnickcom/tcpdf/tcpdf.php';

class mipdf extends TCPDF{  
  //Header personalizado
  public function Header() {

  	//$urllogo = 'https://erp.cr/impafesademo/viewimage.php?cache=1&modulepart=companylogo&file=' . urlencode($mysoc->logo);
    //$this->Image($urllogo, 165,7, 15, '', 'PNG', 'http://ng.cr/', '', false, 300, '', false, false, 0, false, false, false);

    $this->SetFont('helvetica', 'B', 10);
	//mueve a la derecha el titulo 90mm, por que el ancho es 100 y el documento tiene 279.4: 179 - 100= 180/2= 90;

	$this->Cell(90);
	$this->SetY(1);
	$this->SetX(250);
	//despues de false, 'C', false, sigue '' es para un tooltip
	//$this->SetTextColor(255,255,255);
	$this->SetFillColor(0,76,170);
	//los dos primeros parametros ancho,alto //despues de la C que es centrar texto sigue el relleno
	//despues de 'C' poner true si va color de fondo
    //$this->Cell(100, 20, 'Hoja de alisto de mercaderia', 0, false, 'C', false, '', 0, false, 'T', 'M');
  }
  
  //footer personalizado
  public function Footer() {
    // posicion
    $this->SetY(-15);
    // fuente
    $this->SetFont('helvetica', 'I', 8);
    // numero de pagina
    $this->Cell(0, 10, 'Página '.$this->getAliasNumPage().' de '.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
  }
}


//crear carpetas
$ruta = 'docs/';

$pageLayout = array(200,250); //  or array($height, $width) 
//iniciando un nuevo pdf
//orientacion L hotizontal, P vertical
$pdf = new mipdf(P, 'mm', $pageLayout, true, 'UTF-8', false);
 ob_end_clean(); 
//echo $pdf->logo(urlencode($mysoc->logo));
//margenes para html(tabla): izquierda,arriba,derecha, arriba 35 porque la imagen tiene 15
$pdf->SetMargins(5, 10, 5);

//margen del titulo
$pdf->SetHeaderMargin(1);
 
//informacion del pdf
$pdf->SetCreator('Sicla');
$pdf->SetAuthor('Sicla');
$pdf->SetTitle('PDF Generado Para Clientes de Sicla');
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
 
//tipo de fuente y tamanio
$pdf->SetFont('helvetica', '', 10);

//agregar pag 1
$pdf->AddPage();
$htm = $htm ;
//escribe el texto en la hoja
$pdf->writeHTMLCell(0, 0, '', '', $htm, 0, 1, 0, true, '', true);
 
 
//agregar pag 2
//$pdf->AddPage();
////escrite el texto en la hoja
//$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);


//terminar el pdf
//$pdf->Output(__DIR__ .'/docs/reporte.pdf', 'F');
$pdf->Output('reporte.pdf', 'I');

/****************************************************** GENERAR PDF ************************************************/

llxFooter();

$db->close();
