<?php
if($_GET['tipo']==0 || $_GET['tipo']==1 || $_GET['tipo']==2 || $_GET['tipo']==3){
if(!($_GET['facid'])){
$id = $_GET['id']; 
}else{
$id = $_GET['facid'];   
}

$object = new Facture($db);
$object->fetch($id);
$rest = $object->fetch_thirdparty(); 
$paiement = $object->getSommePaiement();
$creditnotes=$object->getSumCreditNotesUsed();
$deposits=$object->getSumDepositsUsed();
$alreadypayed=price2num($paiement + $creditnotes + $deposits,'MT');
$remaintopay=price2num($object->total_ttc - $paiement - $creditnotes - $deposits,'MT');
}



?>
 
<!--/.Navbar -->
<div class="container-fluid ">
<br>


</div>
<br></br>
<div class="row" >

<!-- totales -->

<div class="col-md-8">
<div class="table-responsive">   
    <table class="table table-bordered table-striped" width="100%">
    <thead>
    <tr class="btn-elegant">
      <th scope="col" class="text-center">Informacion del documento</th>           
    </tr>
  </thead>        
  <tbody>   
  <tr>
  <td class="font-weight-bold" style="font-size:1rem">Numero: <?php echo $object->ref ?></td>
  </tr>
  <tr>
  <td class="font-weight-bold" style="font-size:1rem">Cliente: <?php echo $object->thirdparty->name.' '.$object->thirdparty->name_alias ?></td>
  </tr>  
  </tbody>   
    </table> 
    </div>
          
    </div>


<!-- totales -->  
<!-- totales -->
<div class="col-md-4">
<div class="table-responsive">   
    <table class="table table-bordered table-striped" width="100%">
    <thead>
    <tr class="btn-elegant">
      <th scope="col" class="text-center">Totales</th>           
    </tr>
  </thead>        
  <tbody>   
<?php if($object->multicurrency_code == 'USD') { ?><tr class="dolar"><td class="font-weight-bold dolar">Subtotal($): <span id="t_subtotal_d"><?php echo $moneda_d.price($object->multicurrency_total_ttc) ?></span></td></tr> <?php } ?> 
      <tr><td class="font-weight-bold">Subtotal: <span id="t_subtotal"><?php echo $moneda.price($object->total_ht) ?></span></td></tr>   
      <?php if($object->multicurrency_code == 'USD') { ?><tr><td class="font-weight-bold dolar">Descuento($): <span id="t_descuento_d"><?php echo $moneda_d.price($object->multicurrency_descuento) ?></span></td></tr><?php } ?> 
      <tr><td class="font-weight-bold">Descuento: <span id="t_descuento"><?php echo $moneda.price($descuento) ?></span></td></tr>      
      <?php if($object->multicurrency_code == 'USD') { ?><tr><td class="font-weight-bold dolar">IVA($): <span id="t_iva_d"><?php echo $moneda_d.price($object->multicurrency_total_tva) ?></span></td></tr><?php } ?> 
      <tr><td class="font-weight-bold">IVA: <span id="t_iva"><?php echo $moneda.price($object->total_tva) ?></span></td></tr>
      <?php if($object->multicurrency_code == 'USD') { ?><tr><td class="font-weight-bold dolar">Total($): <span data-multicurrency_total_ttc="<?php echo $object->multicurrency_total_ttc ?>" id="t_total_d"><?php echo $moneda_d.price($object->multicurrency_total_ttc) ?></span></td> </tr><?php } ?> 
      
      <?php
      $descuentos = get_descuento($object->socid,$id);
      $des_ndc = 0;
      if(count($descuentos) > 0){
      foreach($descuentos as $v){
       $des_ndc += $v['amount_ttc'];
       print '<tr><td class="font-weight-bold" style="color:red">'.$v['ndc_facnumber'].' : -'.price($v['amount_ttc']).'</td></tr>';
      }
    }
      ?>
        
    <tr><td class="font-weight-bold">Total: <span data-total_ttc="<?php echo $total_ttc ?>" id="t_total"><?php echo $moneda.price($object->total_ttc - $des_ndc) ?></span></td> </tr>
    <tr><td class="font-weight-bold">Pagos: <span data-pagos="<?php echo $paiement ?>" id="t_pagado"><?php echo $moneda.price($paiement) ?></span></td> </tr>  
    <tr><td class="font-weight-bold">Motas de credito: <span data-ndc="<?php echo $creditnotes ?>" id="t_ndc"><?php echo $moneda.price($creditnotes) ?></span></td> </tr>  
    <tr><td class="font-weight-bold">Resta por pagar: <span data-resto="<?php echo $remaintopay ?>" id="t_resto"><?php echo $moneda.price($remaintopay) ?></span></td> </tr>                 
  </tbody>   
    </table> 
    </div>
          
    </div>

<!-- totales -->


<!-- tabla detalle -->
    <div class="col-md-12">
     <div class="table-responsive">
     <table id="tabla_venta" class="table table-bordered table-striped" width="100%">
  <thead>

    <tr class="btn-dark">
      <th scope="col" width="20%">Codigo</th>
      <th scope="col" width="40%">Descripcion</th>
      <th scope="col" width="1%">Cantidad</th>
      <?php if($object->multicurrency_code == 'USD') { ?><th scope="col" class="dolar">P.U($)</th> <?php } ?>        
      <th scope="col">P.U</th>
      <th scope="col" width="1%">DESC</th>
      <th scope="col" width="21%">I.V.A</th>
      <?php if($object->multicurrency_code == 'USD') { ?><th scope="col" class="dolar">Subtotal($)</th> <?php } ?>  
      <?php if($object->multicurrency_code == 'USD') { ?><th scope="col" class="dolar">Total($)</th> <?php } ?>            
      <th scope="col">Subtotal</th> 
      <th scope="col">Total</th>               
    </tr>
  </thead>
  <tbody id="detalle">
  <?php
$fk_facture = (int)$id;
$sq = 'SELECT fd.rowid,fd.fk_facture,fd.fk_product,fd.description,fd.tva_tx,fd.subprice,fd.total_ht,fd.total_tva,
fd.total_localtax1,fd.total_ttc,fd.remise_percent,fd.multicurrency_code,fd.multicurrency_subprice,
fd.multicurrency_total_ht,fd.multicurrency_total_ttc,fd.multicurrency_total_tva,p.ref p_ref,p.label,fd.qty 
FROM llx_facturedet fd
LEFT JOIN llx_product p ON fd.fk_product=p.rowid'; 
$sq .= ' WHERE  fk_facture = '.$fk_facture.'';

$fila = 1;
$sql = $db->query($sq);
while($obj = $db->fetch_object($sql)){
$total_tva += $obj->total_tva;
$total_ht += $obj->total_ht;
$total_ttc += $obj->total_ttc;
$multicurrency_total_tva += $obj->multicurrency_total_tva;
$multicurrency_total_ht += $obj->multicurrency_total_ht;
$multicurrency_total_ttc += $obj->multicurrency_total_ttc;

if($obj->remise_percent > 0){
$descuento += ($obj->subprice * $obj->qty) * $obj->remise_percent/100;
$multicurrency_descuento += ($obj->multicurrency_subprice * $obj->qty) * $obj->remise_percent/100;

}else{
$descuento += 0;
$multicurrency_descuento =0;
}
  $moneda_d = '$';
  $moneda = '₡';


print '<tr>';
print '<td>'.$obj->p_ref.'</td>';
print '<td>'.$obj->label.'</td>';
print '<td>'.$obj->qty.'</td>'; 
if($object->multicurrency_code == 'USD') print '<td class="dolar">'.$moneda_d.price($obj->multicurrency_subprice).'</td>'; 
print '<td>'.$moneda.price($obj->subprice).'</td>';        
print '<td>%'.price($obj->remise_percent).'</td>';
print '<td>%'.price($obj->tva_tx).'</td>';
if($object->multicurrency_code == 'USD') print '<td class="dolar">'.$moneda_d.price($obj->multicurrency_total_ht).'</td>';      
if($object->multicurrency_code == 'USD') print '<td class="dolar">'.$moneda_d.price($obj->multicurrency_total_ttc).'</td>'; 
print '<td>'.$moneda.price($obj->total_ht).'</td>';      
print '<td>'.$moneda.price($obj->total_ttc).'</td>';  
print '</tr>';
$fila++;
}
 
?>
 
  </tbody>
</table>
</div> 

<div class="inline-block divButAction">
<a  class="button btn btn-default btn-sm waves-effect waves-light" href="doc_sucess.php?facid=<?php echo $id ?>&action=presend&tipo=<?php echo $_GET['tipo'] ?>&mode=init&id=<?php echo $id ?>">Enviar e-mail</a>
<a  class="button btn btn-primary btn-sm waves-effect waves-light" target="_blank" href="<?php echo '../../feng/ticket_default.php?facid='.$id.'&action=ticket';?>">Imprimir ticket</a>
<a  class="button btn btn-default btn-sm waves-effect waves-light" target="_blank" href="<?php echo 'tpl/ticket_regalo.tpl.php?facid='.$id.'&action=ticket';?>">Ticket de regalo</a>
<?php if($_GET['tipo'] == 3){ ?>
<a  class="button btn btn-info btn-sm waves-effect waves-light" target="_blank" href="<?php echo 'tpl/ticket_apartado.tpl.php?facid='.$id.'&action=ticket';?>">Imprimir etiqueta apartado</a> 
<?php } ?>
</div>
</div> 
<!--  /tabla detalle --> 
	
<div class="col-md-8">
<?php

$facid = $id;
$id = $id;
$action = GETPOST('action');
$usercansend = (empty($conf->global->MAIN_USE_ADVANCED_PERMS) || $user->rights->facture->invoice_advance->send);

	// Actions to send emails
	if (empty($id)) $id=$facid;
	$trigger_name='BILL_SENTBYMAIL';
	$paramname='id';
	$autocopy='MAIN_MAIL_AUTOCOPY_INVOICE_TO';
	$trackid='inv'.$object->id;

  
	// Select mail models is same action as presend
  // Select mail models is same action as presend
  $form = new Form($db);
  $formother = new FormOther($db);
  $formfile = new FormFile($db);
  $formmargin = new FormMargin($db);
  $paymentstatic=new Paiement($db);
  $bankaccountstatic = new Account($db);

	// Actions to send emails


	if (GETPOST('modelselected','alpha')) {
		$action = 'presend';
	}



	// Actions to build doc
	$upload_dir = $conf->facture->dir_output;
	$permissioncreate=$user->rights->facture->creer;
 
  $object = new Facture($db);
  $object->fetch($id);
  $rest = $object->fetch_thirdparty(); 
		// Documents generes
		$filename = dol_sanitizeFileName($object->ref);
		$filedir = $conf->facture->dir_output . '/' . dol_sanitizeFileName($object->ref);
		$urlsource = $_SERVER['PHP_SELF'] . '?facid=' . $object->id;
		$genallowed = $user->rights->facture->lire;
		$delallowed = $user->rights->facture->creer;

		print $formfile->showdocuments('facture', $filename, $filedir, $urlsource, $genallowed, $delallowed, $object->modelpdf, 1, 0, 0, 28, 0, '', '', '', $soc->default_lang);
		$somethingshown = $formfile->numoffiles;
  
  // Presend form
  include DOL_DOCUMENT_ROOT.'/core/actions_sendmails.inc.php'; 
 
	$modelmail='facture_send';
	$defaulttopic='SendBillRef';
  $diroutput = $conf->facture->dir_output;
  $trackid='inv'.$object->id;
  include DOL_DOCUMENT_ROOT.'/core/tpl/card_presend.tpl.php';
  
?>  

</div>
  </div>

</div><br><br>



