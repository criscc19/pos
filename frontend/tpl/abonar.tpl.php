<?php

if(!isset($_GET['pago'])){
	
?>
<div class="facturas">
<input class="form-control my-0 py-1" type="text"  name="select_cliente" id="select_cliente" placeholder="Buscar Cliente" aria-label="Search" value=""><br>
	
<?php 
print '<form id="form_c" method="GET" action="'.$_SERVER['PHP_SELF'].'?soc='.$cash->fk_soc.'">
	Seleccione la moneda: <select id="fk_currency" name="fk_currency" style="width:200px"  class="browser-default custom-select">
	<option value="-1"></option>	
	<option value="CRC"';
	if(GETPOST('fk_currency') == 'CRC'){echo ' selected';}
	print '>COLONES</option>
	<option value="USD"';
	if(GETPOST('fk_currency') == 'USD'){echo ' selected';}	
	print '>DOLARES</option></select>
	</form>
	<script>
	$( document ).ready(function() {
	$("#fk_currency").change(function(){
	window.location.replace("'.$_SERVER['PHP_SELF'].'?soc='.GETPOST('soc').'&fk_currency="+$(this).val()+"&tipo_doc='.GETPOST('tipo_doc').'");
	
	})
    });


	</script>
	';	

if(GETPOST('fk_currency')== 'CRC' || GETPOST('fk_currency')== 'USD') {?>
<h5 class="text-center"><?php echo $soc->nom.' '.$soc->name_alias?><br><small>Limite de credito: <?php echo price($limite) ?></small></h5><br>
		<form method="post" name="frmForm"  id="frmForm"  action="<?php echo $_SERVER['PHP_SELF'].'?soc='.$cash->fk_soc.'&fk_currency='.GETPOST('fk_currency').'&tipo_doc=0,1' ?>"> 
			<input type="hidden" name="action" id="action" value="add_paiement" />
			<input type="hidden" name="fk_currency" id="fk_currency" value="<?php echo GETPOST('fk_currency') ?>" />
			<input type="hidden" name="tipo_doc" id="tipo_doc" value="<?php echo GETPOST('tipo_doc'); ?>" />			
<?php
$sq .= ' SELECT f.rowid fid,f.'.$facnumber.',f.entity,f.total_ttc,f.datef,f.date_lim_reglement,f.fk_soc,
multicurrency_code,multicurrency_total_ttc,
(SELECT IF(sr.amount_ttc IS NOT NULL, SUM(sr.amount_ttc), 0) AS amount_ttc FROM llx_societe_remise_except sr WHERE f.fk_soc=sr.fk_soc AND sr.fk_facture=f.rowid) ndc,
(SELECT IF(pf.amount IS NOT NULL, SUM(pf.amount),0) FROM llx_paiement_facture pf WHERE pf.fk_facture=f.rowid ) pagos,
(f.total_ttc - (SELECT IF(sr.amount_ttc IS NOT NULL, SUM(sr.amount_ttc), 0) AS amount_ttc FROM llx_societe_remise_except sr WHERE f.fk_soc=sr.fk_soc AND sr.fk_facture=f.rowid) - (SELECT IF(pf.amount IS NOT NULL, SUM(pf.amount),0) FROM llx_paiement_facture pf WHERE pf.fk_facture=f.rowid )) totales,
(SELECT IF(sr.multicurrency_amount_ttc IS NOT NULL, SUM(sr.multicurrency_amount_ttc), 0) AS multicurrency_amount_ttc FROM llx_societe_remise_except sr WHERE f.fk_soc=sr.fk_soc AND sr.fk_facture=f.rowid) multicurrency_ndc,
(SELECT IF(pf.multicurrency_amount IS NOT NULL, SUM(pf.multicurrency_amount),0) FROM llx_paiement_facture pf WHERE pf.fk_facture=f.rowid ) multicurrency_pagos,
(f.multicurrency_total_ttc - (SELECT IF(sr.multicurrency_amount_ttc IS NOT NULL, SUM(sr.multicurrency_amount_ttc), 0) AS multicurrency_amount_ttc FROM llx_societe_remise_except sr WHERE f.fk_soc=sr.fk_soc AND sr.fk_facture=f.rowid) - (SELECT IF(pf.multicurrency_amount IS NOT NULL, SUM(pf.multicurrency_amount),0) FROM llx_paiement_facture pf WHERE pf.fk_facture=f.rowid )) multicurrency_totales';
$sq .= ' FROM llx_facture f ';
$sq .= ' LEFT JOIN llx_facture_extrafields fex ON fex.fk_object=f.rowid';
$sq .= ' WHERE f.fk_statut = 1 AND f.paye=0 AND type=0 AND f.fk_soc = '.GETPOST('soc').' AND f.multicurrency_code= "'.GETPOST('fk_currency').'"';
$sq .= ' AND fex.facturetype IN('.GETPOST('tipo_doc').')';
$sq .= ' GROUP BY f.rowid';

$sql = $db->query($sq);

?>




Monto recibido: <input type="text" id="monto_cl" value="0"> Restante :<span data-resto="0" id="monto_cl_res"></span>
		<table style="width: 100%;"  class="table table-striped">
<thead>
			<tr class="btn-elegant">
				
				<th>Factura</th>
				<th>Fecha</th>
				<th>Vence</th>
				<th>Total</th>
				<th>NDC</th>				
				<th>Pagado</th>
				<th>Pendiente</th>								
				<th></th>

			</tr>
</thead>
<tbody>
<?php		 
for($i=1;$i<= $db->num_rows($sql);$i++){
$obj = $db->fetch_object($sql);
$moneda = $obj->multicurrency_code;	
if($moneda == 'USD' ){
$total_ttc = $obj->multicurrency_total_ttc;
$ndc = $obj->multicurrency_total_ndc;
$pagos =  $obj->multicurrency_total_pagos;
$totales = $obj->multicurrency_totales;
$total_pendiente += $obj->multicurrency_totales;
}
if($moneda == 'CRC' ){
$total_ttc = $obj->total_ttc;
$ndc = $obj->ndc;
$pagos =  $obj->pagos;
$totales = $obj->totales;
$totales_ttc += $obj->totales;
$total_pendiente += $obj->totales;	
}

if($moneda == 'USD' ){$signo = '$'; }
if($moneda == 'CRC' ){$signo = '₡';}

?>

<tr id="tr_<?php echo $obj->fid ?>">
<td><span  class="tr_fac" data-id="<?php echo $obj->fid ?>" data-estado="0" style="cursor:pointer"><?php echo $obj->$facnumber ?></span><input type="hidden" name="fid[]" value="<?php echo $obj->fid ?>"></td>
<td><?php echo $obj->datef ?></td>
<td><?php echo $obj->date_lim_reglement ?></td>
<td><?php echo $signo.price($total_ttc) ?></td>
<td><?php echo $signo.price($ndc) ?></td>
<td><?php echo $signo.price($pagos) ?></td>
<td><?php echo $signo.price($totales) ?></td>
<td>		
	<b class="monto2" style="font-size: 1.5em; display:none"><?php echo $signo ?></b><input class="monto" type="text" style="width:150px" name="monto_<?php echo $obj->fid ?>" id="monto_<?php echo $obj->fid ?>" value="0">
	
	<span data-resto="<?php echo $totales ?>" data-id="<?php echo $obj->fid ?>" class="monto2" style="cursor:pointer"><i class="fas fa-share" aria-hidden="true"></i></span>
	
	</td>

</tr>

<?php } ?>
</tbody>
<tfoot>
<tr>
<th></th>
<th></th>
<th></th>
<th></th>
<th></th>
<th>Total pendiente</th>								
<th><b><?php echo $signo.price($totales_ttc) ?></b></th>
</tr>	
</tfoot>		
</table>
<?php
$tcredito = $limite - $total_pendiente;
if($tcredito >= 0){echo '<h5><span style="color:green">Credito disponible: '.price($tcredito).'</span></h5>';}
if($tcredito < 0){echo '<h5><span style="color:red">Limite de credito sobre pasado por: '.price($tcredito).'</span></h5>';}
?>


<!--  detalle de pago -->

<?php
	$sqs = '';
	$bancos1 = select_bancos(' AND currency_code="'.$_GET['fk_currency'].'" AND courant=2');
	$bancos2 = select_bancos(' AND currency_code="'.$_GET['fk_currency'].'" AND courant !=2');
	$metodos1 = select_metodo_pago(' AND id ='.$cash->fk_modepaycash.'');  
	$metodos2 = select_metodo_pago(' AND id !='.$cash->fk_modepaycash.'');  

?>
<table class="table table-bordered" width="100%">
<tr class="btn-elegant">
<td colspan="4" align="center">Detalles del pago</td>	
</tr>
<tr>

<td>
	<?php
	//select de bancos
	print '<select data-banco="1" class="banco browser-default custom-select" name="banco1" id="banco1">';
	foreach($bancos1 as $v){
	print '<option data-pago="pago1" data-currency_code="'.$v['currency_code'].'" value="'.$v['id'].'"';
	if($cash->fk_paycash==$v['id']) {print ' selected';}
	print   '>'.$v['ref'].'</option>';
	}
	print  '</select>';
	//fin select bancos

	//select de metodo de pagos
	print '<select class="browser-default custom-select" name="metodo1" id="metodo1">';
	foreach($metodos1 as $v){
	print '<option data-metodo1_id="'.$v['id'].'" value="'.$v['code'].'"';
	if($cash->fk_modepaycash==$v['id']) {print ' selected';}
	print '>'.$langs->trans('PaymentType'.$v['code']).'</option>';
	}
	print '</select>';
	//fin select de metodo de pagos
	?></td>
	<td>
	<div class="input-group mb-3">
	<input style="width:100px;color:<?php echo $color1 ?>;background-color:<?php echo $color1_2 ?>" type="text" class="form-control"  name="pago1" id="pago1" value="0"/>
	<div class="input-group-prepend"><span id="in_pago1" class="input-group-text btn-primary"><i class="fas fa-share" aria-hidden="true"></i></span></div>
	</div>

</td>


<!-- metodo de pago 2 -->

<td>
		<?php
		//select de bancos
		print '<select data-banco="2" class="banco browser-default custom-select" name="banco2" id="banco2">';
		foreach($bancos2 as $v){
		print '<option data-pago="pago2" data-currency_code="'.$v['currency_code'].'" value="'.$v['id'].'"';
		if($cash->fk_paybank_extra==$v['id']) {print ' selected';}
		print '>'.$v['ref'].'</option>';
		}
		print '</select>';
		//fin select bancos

		//select de metodo de pagos
		print '<select class="browser-default custom-select" name="metodo2" id="metodo2">';
		foreach($metodos2 as $v){
		print '<option data-metodo2_id="'.$v['id'].'" value="'.$v['code'].'"';
		if($cash->fk_modepaybank_extra==$v['id']) {print ' selected';}
		print '>'.$langs->trans('PaymentType'.$v['code']).'</option>';
		}
		print '</select>';
		//fin select de metodo de pagos

		?></td>
		<td>
		<input type="hidden" name="signo_banco_2" id="signo_banco_2" value="<?php echo $signo2 ?>">
		<div class="input-group mb-3">
		<input style="width:100px;color:<?php echo $color1 ?>;background-color:<?php echo $color1_2 ?>" type="text" class="form-control"  name="pago2" id="pago2" value="0"/>
		<div class="input-group-prepend"><span id="in_pago2" class="input-group-text btn-primary"><i class="fas fa-share" aria-hidden="true"></i></span></div>
		</div>
</td>
<tr>
<td colspan="2"><b class="font-weight-bold">Numero de pago</b> <input type=text name="num_paiement" class="form-control"></td>
<td colspan="2"><b class="font-weight-bold">Comentario</b> <input type=text name="comment" class="form-control"></td>
<tr>
</tr>	
</table>
<div id="total_c" class="text-center totales">
<div class="alert alert-success" role="alert" id="text_covert" style="font-size:1rem;font-weight: 100;display:none"></div>
<ul class="list-group">
<li class="list-group-item">Total: <span id="g_total" data-total_ttc=""></span></li>
<li class="list-group-item">Pagado: <span id="g_pagado"></span></li>
<li class="list-group-item">Vuelto: <span id="g_vuelto" data-vuelto=""></span></li>
</ul>

</div>
<!-- fin detalle de pago -->

<center><input type="button" id="pagar" value="Ingresar pagos" class="waves-effect waves-light btn btn-primary"></center>
		</form>

<?php } ?>
	</div>

<?php 
}else{
	print '
	<a href="tpl/ticket_pagos.php?id='.$_GET['pago'].'" class="waves-effect waves-light btn btn-primary btn-sm" target="_blank"><i class="fas fa-print fa-2x"></i> Imprimir pago</a> 
	<a href="tpl/ticket_pagos_ticket.php?id='.$_GET['pago'].'" class="waves-effect waves-light btn btn-success btn-sm" target="_blank"><i class="fas fa-sticky-note fa-2x"></i> Imprimir pago ticket</a>
	';	


if(isset($_GET['estado'])){
$datos = json_decode(GETPOST('estado'));

foreach($datos as $v){
if($v->facturar==1){
 $factura = new Facture($db);
 $factura->fetch($v->id);	
if(!$conf->global->POS_REGIMEN){
 print '<a href="facturar_apartado.php?id='.$v->id.'" class="waves-effect waves-light btn btn-success btn-sm" target="_blank"><i class="fas fa-file-invoice fa-2x"></i> CREAR FACTURA ELECTRONICA ('.$factura->ref.')</a>';
}else{
 print '<a href="facturar_apartado.php?id='.$v->id.'" class="waves-effect waves-light btn btn-success btn-sm" target="_blank"><i class="fas fa-file-invoice fa-2x"></i> TERMINAR APARTADO ('.$factura->ref.')</a>';

}

}

}	

}

}


 ?>
<br>
<br>

<script type="text/javascript">
//detalle de factura
$('.tr_fac').click(get_facturedet);

function get_facturedet(){
fk_facture = $(this).attr('data-id');
elemento = $(this);

detalle = '';
 //envio por ajax
 $.ajax({
  type: "POST",
  url: "ajax/get_facture_det.php",
  data: {
    fk_facture:fk_facture,
    action:'get_facturedet'
  },
  dataType: "json",
  success: function(resp) {
 
  $.each(resp, function( index, value ) {
	detalle +=
	'<tr>'+	
    '<td>'+value.ref+'</td>'+	
    '<td>'+value.label+'</td>'+
    '<td>'+value.qty+'</td>'+ 
	'<td>'+value.remise_percent+'</td>'+
	'<td>'+value.tva_tx+'</td>'+	
	'<td>'+value.total_ht+'</td>'+
	'<td>'+value.total_ttc+'</td>'+	
	'</tr>'
  })

if($(elemento).attr('data-estado') == 1){
$('.detalle_'+fk_facture).remove();	
$(elemento).attr('data-estado',0);
}else{
$(elemento).attr('data-estado',1);
	
$("#tr_"+fk_facture).after(
'<tr class="fac_detalle detalle_'+fk_facture+'"><td colspan="8">'+
'<table style="width: 100%;" class="table bodered border">'+
	'<tr class="btn-info">'+	
    '<td>Ref</td>'+	
    '<td>Producto</td>'+
    '<td>Cantidad</td>'+ 
	'<td>Descuento</td>'+
	'<td>IVA</td>'+	
	'<td>Subtotal</td>'+
	'<td>Total</td>'+	
detalle+

'</table>'+
'</td>'+
'</tr>'
); 
}

  $('#barra_c').hide();
  $('#modalCart').modal('show');
  
}
})
//envio por ajax





}
//fin de detalle factura	




$('#empezar').click(function(){
monto = $('#monto').val();
banco = $('#accountid').val();
m_pago = $('#metodo1').val();
if(monto == "" && monto <=0 || banco == -1 || monto == 0 || m_pago == -1){ alert('Se necesita monto, banco y metodo de pago');return}
if(monto > 0 && banco > 0 && m_pago !=-1){
$('.monto').show();
$('.monto2').show();
$('#monto').attr('readonly','readonly');

sele = $('#metodo1').children("option:selected");
$('#metodo1').html('');
$('#metodo1').html(sele);

ban = $('#accountid').children("option:selected");
$('#accountid').html('');
$('#accountid').html(ban);
}

})

$( ".monto" ).keyup(calcular);
$( "#monto" ).keyup(calcular);



//metodos de pago
function calculos(){
  $('#text_covert').hide();
  total =  parseFloat($('#g_total').attr('data-total_ttc'));
  pago1 =  parseFloat($('#pago1').val());  
  pago2 =  parseFloat($('#pago2').val());
  monto_cl = parseFloat($('#monto_cl').val());
//coversion de moneda
/*   moneda1 = $('#signo_banco_1').val();
  moneda2 = $('#signo_banco_2').val();
  if(pago1 > 0 && moneda1 != $('#moneda').val()){
  
  pago1 = conversion(pago1,moneda1);
} 
 if(pago2 > 0 && moneda2 != $('#moneda').val()){
  
  pago2 = conversion(pago2,moneda2);  
}   */
//fin de conversion de moneda
   $('#g_pagado').attr('data-vuelto',pago1 + pago2);
   $('#g_pagado').text(numeral(pago1 + pago2).format('0,0.00'));  

  s_total = total - pago1 - pago2;
  if(s_total > 0){
  $('#g_vuelto').attr('style','color:red');
  oper = '-';  
  }else{
  $('#g_vuelto').attr('style','color:green');  
  oper = '';      
  }
  $('#g_vuelto').text(oper+numeral(Math.abs(s_total)).format('0,0.00'));
  $('#g_vuelto').attr('data-vuelto',s_total);
  $('#monto_cl').val(monto_cl - s_total);
  console.log(monto_cl - s_total);
}

  $('#in_pago1').click(function(){
  $('#pago2').val(0);	  
  total =  parseFloat($('#g_total').attr('data-total_ttc'));
  pago2 =  parseFloat($('#pago2').val());
  moneda1 = $('#signo_banco_1').val();
  if(pago2 == 0){
  //total = conversion_total(total,moneda1);  
  $('#pago1').val(total);
  $('#g_pagado').text(total); 
  calculos();
  }else{
if(pago2 > 0 && pago2 < total){
   s_total = total - pago2;
   //s_total = conversion_total(s_total,moneda1);   
   $('#pago1').val(s_total)
   calculos();
  }
  } 
 });


 $('#in_pago2').click(function(){
 $('#pago1').val(0);	 
  total =  parseFloat($('#g_total').attr('data-total_ttc')); 
  pago1 =  parseFloat($('#pago1').val());
  moneda2 = $('#signo_banco_2').val();
  if(pago1 == 0){
  //total = conversion_total(total,moneda2);   
  $('#pago2').val(total);
  $('#g_pagado').text(total);
  calculos();
  }else{
if(pago1 > 0 && pago1 < total){ 
   s_total =   total - pago1;
  // s_total = conversion_total(s_total,moneda2);   
   $('#pago2').val(s_total);
   calculos(); 
  }
  } 
 });

 $('#pago1').keyup(function(){
	$('#pago2').val(0);	 
calculos(); 
})


$('#pago2').keyup(function(){
	$('#pago1').val(0);	
calculos(); 
})
 
$('#pago1').blur(function(){
	$('#pago2').val(0);	
if($(this).val().length == 0){
$(this).val(0);
calculos(); 
}else{
calculos();
}
})

$('#pago2').blur(function(){
	$('#pago1').val(0);	
if($(this).val().length == 0){
$(this).val(0); 
calculos(); 
}else{
  calculos();
  }
})


function calcular(){
total_s = 0;
$( ".monto" ).each(function() {
  total_s += parseFloat($(this).val());
});

total = parseFloat($('#monto').val()) - total_s;

$('#resto').val(total);
$('#resto2').val(numeral(total).format('0,0.00'));
$('#pagado').val(total_s);
$('#g_total').text(total_s);
$('#g_total').attr('data-total_ttc',total_s);
$('#vuelto').val(total);
monto_cl = parseFloat($('#monto_cl').val());
//$('#monto_'+val_1).val(monto_1);
$('#monto_cl_res').text(monto_cl - total_s);	
$('#monto_cl_res').attr('data-resto',monto_cl - total_s);
  console.log(monto_cl - total_s);
}


$( ".monto2" ).click(function(){
	total_s = 0;
$( ".monto" ).each(function() {
  total_s += parseFloat($(this).val());
});
monto_2 = parseFloat($('#monto_cl').val());
resto = monto_2 - total_s;
val_1 = $(this).data('id');
monto_1 = parseFloat($(this).data('resto'));

if(resto > monto_1){
$('#monto_'+val_1).val(monto_1);	
}else{
$('#monto_'+val_1).val(resto);	
}	
calcular();
});


$('#pagar').click(function(){
pagado = parseFloat($('#pagado').val());
banco = $('#accountid').val();
m_pago = $('#metodo1').val();
num = $(this).attr('data-banco');
if(banco == -1 || pagado == 0 || m_pago == -1){ alert('Se necesita monto, banco y metodo de pago');return}
$('#frmForm').submit();
})

$('.banco').change(function(){
//envio por ajax
num = $(this).attr('data-banco');
$.ajax({
  type: "POST",
  url: "ajax/bancos.php",
  data: {
    id:$(this).val()
  },
  dataType: "json",
  success: function(resp) {
  $('#signo_banco_'+num).val(resp.banco.currency_code);
  $('#pago'+num).attr('style','width:100px;border-color:'+resp.banco.color+';color:'+resp.color+';background-color:'+resp.banco.color2+'');
  $('#metodo'+num).html('');
  //$('#metodo'+num).html('<option value="-1" selected></option>');
  $.each(resp.metodos, function( index, value ) {
    $('#metodo'+num).append('<option data-metodo1_id="'+value.id+'" value="'+value.code+'">'+value.label+'</option>');
  })
  //$('#barra_c').hide();
  }
  
  })
  //envio por ajax    
});

</script>