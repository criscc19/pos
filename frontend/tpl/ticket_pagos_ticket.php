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


/* ************************************************************TABLA PARA GENERAR PDF DE PAGOS ******************************************************/		
		// Logo

$logodir = $conf->mycompany->dir_output;
$logo = $logodir.'/logos/'.$mysoc->logo;

	//CONSTRUCCION DEL TITULO DEL DOCUMENTO

	$html .= '<table width="100%" border="0" style="font-size:8px">';
	$html .='<tr align="center" style="font-size: 10px;">';
	$html .='<td>&nbsp;</td>';
	$html .='<td style="font-weight: bold; text-align:center; color:#800000"><img src="'.$logo.'" width="120"></td>';
	$html.= '<td></td>';
	//$html.= '<td><img src="https://erp.cr/impafesademo/viewimage.php?cache=1&modulepart=companylogo&file='.urlencode($mysoc->logo).'" width="120"></td>';
	$html .='</tr>';
	$html .= "</table>";

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

 	$html .= '<table width="100%" border="0" cellpadding="0" cellspacing="0"  cellspacing="0" style="font-size:8px">';

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

			$html .= '<tr>';
			$html .= '<td colspan="7"  align="left">';
			$html .= '<strong>Factura:</strong> '.$objp->$facnumber.'<br>';
			$html .= '<strong>Empresa:</strong>'.$objp->name.'<br>';
			if($objp->recibo==null){
				$html .= '<strong>Recibo/Nota:</strong>'.$objp->note.'<br>';
			}else{
				$html .= '<strong>Recibo/Nota:</strong>'.$objp->recibo.'<br>';
			}
			$html .= '<strong>Esperando el pago:</strong>'.price($objp->total_ttc).'<br>';
			$html .= '<strong>Pagada por este pago:</strong>'.price($objp->amount).'<br>';
			$html .= '<strong>Resta por pagar:</strong>'.price($remaintopay).'<br>';
			$html .= '<strong>Estado:</strong>'.$estadoF.'</td>';
			$html .= '</tr>';
			$html .= '<tr><td colspan="7"><div style="border-style:dotted"></div></td></tr>';
			//$factura=$objp->facnumber;

			$i++;
		}
		
		$db->free($resql);

		$html .= '<br>';					

		$html .= '<tr style="text-align:center;">';
		$html .= '<td colspan="7" style="text-align:center; background-color: #800000; color:#ffffff">'.'Totales'.'</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td colspan="7" style="border-bottom: 1px solid #ddd; text-align:left;">
		<strong>Pagada por este pago:</strong> '.price(array_sum($total)).'<br>';

		$html .= '</td>';
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

//medidas para calcular el largo del ticket
$header = 50;
$lineas = 30 * $num;
$totales = 40;

$alto = $header + $lineas + $totales;
//crear carpetas
$ruta = 'docs/';

$pageLayout = array($alto,80); //  or array($height, $width) 
//iniciando un nuevo pdf
//orientacion L hotizontal, P vertical
$pdf = new mipdf(P, 'mm', $pageLayout, true, 'UTF-8', false);
$pdf->setPrintFooter(false);
//echo $pdf->logo(urlencode($mysoc->logo));
//margenes para html(tabla): izquierda,arriba,derecha, arriba 35 porque la imagen tiene 15
$pdf->SetMargins(2, 5, 2);

//margen del titulo
$pdf->SetHeaderMargin(1);
 
//informacion del pdf
$pdf->SetCreator('Sicla');
$pdf->SetAuthor('Sicla');
$pdf->SetTitle('PDF Generado Para Clientes de Sicla');
$pdf->SetAutoPageBreak(TRUE, 0);
 
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
