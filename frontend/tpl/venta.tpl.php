
  <div class="container-fluid contenido  table-responsive">
<br>
 <?php $fk_facture = (int)$_GET['fk_facture'];?> 
<input type="hidden" name="fk_soc" id="fk_soc" value="<?php echo $cliente->id;?>">
<input type="hidden" name="forme_juridique_code" id="forme_juridique_code" value="<?php echo $cliente->forme_juridique_code;?>">
<input type="hidden" name="idprof1" id="idprof1" value="<?php echo $cliente->idprof1;?>">
<input type="hidden" name="limite_credito" id="limite_credito" value="<?php echo $cliente->outstanding_limit;?>">
<input type="hidden" name="credito_usado" id="credito_usado" value="<?php echo (float)$pendiente['opened'];?>">
<input type="hidden" name="credito_disponible" id="credito_disponible" value="<?php echo (float)$cliente->outstanding_limit - (float)$pendiente['opened'];?>" >
<input type="hidden" name="fk_cierre" id="fk_cierre" value="<?php echo $control->id;?>">
<input type="hidden" name="fk_facture_source" id="fk_facture_source" value="">
<input type="hidden" name="fk_facture_source_num" id="fk_facture_num" value="">
<input type="hidden" name="fk_facture_source_num" id="fk_facture_source_num" value="">
<input type="hidden" name="feng_codref" id="feng_codref" value="">
<input type="hidden" name="fk_soc_default" id="fk_soc_default" value="<?php echo $cash->fk_soc;?>">
<input type="hidden" name="price_level" id="price_level" value="<?php echo $cliente->price_level;?>">
<input type="hidden" name="options_vendedor" id="options_vendedor" value="<?php echo $_SESSION['uid'];?>">
<input type="hidden" name="options_sucursal" id="options_sucursal" value="<?php echo $warehouse;?>">
<input type="hidden" name="options_facturetype" id="options_facturetype" value="<?php echo $_SESSION['TIPO_DOC'];?>">	
<input type="hidden" name="fk_facture" id="fk_facture" value="<?php echo $fk_facture;?>">	
<input type="hidden" name="multicurrency_tx" id="multicurrency_tx" value="<?php echo $multicurrency_tx;?>">
<input type="hidden" name="tipo" id="tipo" value="<?php echo $_SESSION['TIPO_DOC'];?>">	
<input type="hidden" name="moneda" id="moneda" value="<?php echo $_SESSION['MULTICURRENCY_CODE'];?>">	
<input type="hidden" name="mode_limit" id="mode_limit" value="<?php echo $conf->global->MODE_LIMIT;?>">	
<input type="hidden" name="ref_client" id="ref_client" value="">
<input type="hidden" name="remise_client" id="remise_client" value="0">
<input type="hidden" name="default_vat_code" id="default_vat_code" value="08 Tarifa general">
<input type="hidden" name="user_author" id="user_author" value="<?php echo $user->id;?>">
<input type="hidden" name="login_vendedor" id="login_vendedor" value="<?php echo $_SESSION['login'];?>">
<input type="hidden" name="stock_negativo" id="stock_negativo" value="<?php echo (int)$conf->global->STOCK_ALLOW_NEGATIVE_TRANSFER;?>">
<input type="hidden" name="client_desc" id="client_desc" value="<?php echo (int)$conf->global->POS_CLIENT_DESC;?>">
<input type="hidden" name="limit_descuento" id="limit_descuento" value="<?php echo (int)$conf->global->LIMIT_DESCUENTO;?>">
<input type="hidden" name="limitar_facturacion" id="limitar_facturacion" value="<?php echo $conf->global->POS_CLIENT_LIMIT;?>">
<input type="hidden" name="limitar_login" id="limitar_login" value="<?php echo (int)$conf->global->POS_FORCE_LOGIN;?>">
<input type="hidden" name="monto_minimo_apartado" id="monto_minimo_apartado" value="<?php echo $conf->global->POS_MIN_MONTO_APARTADO;?>">
<input type="hidden" name="actividad_economica" id="actividad_economica" value="<?php echo $conf->global->FENG_ACTIVIDAD_ECONOMICA_PRINCIPAL;?>">
<input type="hidden" name="cant_attributes" id="cant_attributes" value="0">
<input type="hidden" name="contado" id="contado" value="<?php echo $cliente->id;?>">
<input type="hidden" name="rew_points" id="rew_points" value="0">
<input type="hidden" name="servicios" id="servicios" value="<?php echo $conf->global->POS_SERVICES;?>">
<?php 
include('tpl/variant.tpl.php');
include('tpl/change_client.tpl.php');
include('tpl/rewards.tpl.php');
?>
<div id="div_conten_user" style="display:none">
<?php
print $form->select_dolusers($user->id, 'log_user', 1, '', 0, '', $array, 0, 0, 0, '', 0, '', 'log_user browser-default custom-select');
?>
</div>
<div class="row" >
<!-- tabla detalle -->
    <div class="col-md-12">
     <div class="">
     <table id="tabla_venta" class="table table-bordered table-striped" width="100%">
  <thead>
       <tr>
       <th scope="col" colspan="<?php if($_SESSION['MULTICURRENCY_CODE'] == 'USD') { echo 6;}else{ echo 3;}?>" id="tr_select_cliente">
       <input class="form-control my-0 py-1" type="text"  name="select_cliente" id="select_cliente" placeholder="Buscar Cliente" aria-label="Search" value="">      
       </th>
       <th>
       
       </i><span class="teclado_virtual" data-id="select_cliente"><i class="fas fa-keyboard fa-2x"></i></span>
       </th>
       <th scope="col" colspan="4" id="tr_actividad_economica">
       <?php
//obteniedoi actividad economica
$actividad_eco = get_actividad_principal($conf->global->FENG_ACTIVIDAD_ECONOMICA_PRINCIPAL);
//creando array de actividad economica principal y secundaria
$arracti = json_decode($conf->global->FENG_ACTIVIDAD_ECONOMICA,true);
$arracti [] = $conf->global->FENG_ACTIVIDAD_ECONOMICA_PRINCIPAL;
//obteniedo actividades economicas
$select_actividad_eco = get_actividad_principal($arracti);

print '
<select id="actividad" name="actividad" class="browser-default custom-select">
<option></option>';
foreach($select_actividad_eco as $v){
print '<option value="'.$v['code'].'"';
if($conf->global->FENG_ACTIVIDAD_ECONOMICA_PRINCIPAL==$v['code']) echo 'selected';
print '>'.$v['label'].'</option>';    
}
print '
</select>';
?>
       </th>
            
       </tr> 
<!--------- SELECCIONADOR DE PRODUCTOS ---------------->
 <tr> 
 <th class="th_titulo" colspan="<?php if($_SESSION['MULTICURRENCY_CODE'] == 'USD') { echo 11;}else{ echo 8;}?>">
 <span class="badge badge-info text-uppercase"><span class="font-weight-bold">Cajero:</span> <span id="current_cajero"><?php echo $_SESSION['firstname'].' '.$_SESSION['lastname'] ?></span></span> 
 <span class="badge badge-success text-uppercase"><span class="font-weight-bold">vendedor:</span> <span id="current_vendedor"><?php echo $_SESSION['firstname'].' '.$_SESSION['lastname'] ?></span></span> 
 <span class="badge badge-warning text-uppercase"><span class="font-weight-bold">Factura:</span> <span id="fac_num">SIN GUARDAR</span></span>
 <span id="change_cliente" class="btn-primary btn-custom"><i class="fas fa-user"></i> <i class="fas fa-exchange-alt"></i></span>
 Ex: <select name="options_exoneracion" id="options_exoneracion">
 <?php
 $sq = 'SELECT * FROM `llx_facturaelectronica_societe_exonerado` WHERE fk_soc='.$cliente->id.'';
 $sql = $db->query($sq);
while($obj = $db->fetch_object($sql)){
print '<option value="'.$obj->numero_documento.'">'.$tipo_dococumento.' - '.$obj->numero_documento.'</option>';
}
 ?>
 </select>
 </th>
 </tr> 
 </table>
<!---------FIN SELECCIONADOR DE PRODUCTOS ----------------> 
   <tr>
   <th colspan="<?php if($_SESSION['MULTICURRENCY_CODE'] == 'USD') { echo 11;}else{ echo 8;}?>">
  </tr>  
   <table boreder="1" width="100%">
   <tr>
     
<!--------- SELECCIONADOR DE PRODUCTOS ---------------->
<td width="46%" class="producto">
   <input style="width: 100%;" type="text" tabindex="0" class="form-control my-0 py-1" name="select_product" id="select_product" placeholder="Buscar Producto" value="" autocomplete="off">
      <input type="hidden" neme="fk_product" id="fk_product" value="0">
      <input type="hidden" neme="precio_min" id="precio_min" value="0">
      <input type="hidden" neme="max_discount" id="max_discount" value="0">
      <input type="hidden" neme="entrepot_stock" id="entrepot_stock" value="0">
      <input type="hidden" neme="product_type" id="product_type" value="0">         
  
   </div>
</div>     
   </td>

   <td width="20%" class="cantidad">
   <div class="input-group">
   <input type="text" name="cantidad" id="cantidad" class="form-control" placeholder="Cantidad">
  <div class="input-group-prepend">
  <span id="in_pago_2" class="input-group-text btn-primary"><span class="teclado_virtual" data-id="cantidad" ><i class="fas fa-keyboard"></i></span> </span>
 </div>
</div>
   </td>

<td width="2%"><span class="btn btn-primary btn-sm teclado_virtual" data-id="select_product" ><i class="fas fa-keyboard"></i></span></td>
<td width="2%"><div id="insertar" class="btn btn-primary btn-sm"><i class="fas fa-share"></i></div></td>  
<!--------- /SELECCIONADOR DE PRODUCTOS ---------------->
   <td width="20%" class="descuento">
   <?php
        if($user->rights->pos->discount){//gestion de permiso de descuento
      ?>

      
   <div class="input-group">
   <input type="text" name="descuento" id="descuento" class="form-control" placeholder="Descuento">
   <select name="tipo_desc" id="tipo_desc"><option value="PC">%</option><option value="CAN">#</option></select>
  <div class="input-group-prepend">
  <span id="in_pago_2" class="input-group-text btn-primary teclado_virtual" data-id="descuento"><i class="fas fa-keyboard"></i></span>
 </div>
</div>  
<?php } ?>   
   </td>
   </tr>
   </table>
   <table id="tabla_venta" class="table table-bordered table-striped" width="100%">
    <tr class="btn-dark">
      <th scope="col" width="50px">Codigo</th>
      <th scope="col" width="20%">Codigo</th>
      <th scope="col" width="40%">Descripcion</th>
      <th scope="col" width="1%">Cantidad</th>
      <?php if($_SESSION['MULTICURRENCY_CODE'] == 'USD') { ?><th scope="col" class="dolar">P.U($)</th> <?php } ?>        
      <th scope="col">P.U</th>
      <th scope="col" width="1%">DESC</th>
      <th scope="col" width="1%">I.V.A</th>
      <?php if($_SESSION['MULTICURRENCY_CODE'] == 'USD') { ?><th scope="col" class="dolar">Subtotal($)</th> <?php } ?>  
      <?php if($_SESSION['MULTICURRENCY_CODE'] == 'USD') { ?><th scope="col" class="dolar">Total($)</th> <?php } ?>            
      <th scope="col">Subtotal</th> 
      <th scope="col">Total</th>               
    </tr>
  </thead>
  <tbody id="detalle">
  <?php

$sq = 'SELECT fd.rowid,fd.fk_soc,fd.fk_facture,fd.fk_product,fd.description,fd.tva_tx,fd.subprice,fd.total_ht,fd.total_tva,
fd.total_localtax1,fd.total_ttc,fd.remise_percent,fd.multicurrency_code,fd.multicurrency_subprice,
fd.multicurrency_total_ht,fd.multicurrency_total_ttc,fd.multicurrency_total_tva,p.rowid p_id,p.ref p_ref,p.label,fd.qty 
FROM llx_facturedet_cashdespro fd
LEFT JOIN llx_product p ON fd.fk_product=p.rowid'; 
$sq .= ' WHERE fd.fk_soc = '.$cliente->id.' AND fd.fk_vendedor = '.$_SESSION['uid'].' AND fk_facture=0';

$fila = 1;
$sql = $db->query($sq);
while($obj = $db->fetch_object($sql)){
$product = New Product($db);
$product->fetch($obj->fk_product);
if($product->id > 0){
$res = $product->fetch_optionals();
}
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
  if($obj->fk_product == ''){
    $label = $obj->description;
    $ref = 'Servicio';   
   }else{
    $label = $obj->label;
    $ref = $obj->p_ref; 
  }
$images = new imagenes($db);
$imgs = $images->productImage(0,$obj->fk_product);
print '<tr class="fila" 
id="fila_'.$fila.'" 
data-indice="'.$fila.'" 
data-comodin="'.$product->array_options['options_comodin'].'" 
data-id="'.$obj->rowid.'" 
data-p_ref="'.$ref.'" 
data-label="'.$label.'" 
data-qty="'.$obj->qty.'" 
data-subprice="'.$obj->subprice.'" 
data-multicurrency_subprice="'.$obj->multicurrency_subprice.'" 
data-remise_percent="'.$obj->remise_percent.'" 
data-tva_tx="'.$obj->tva_tx.'" 
data-multicurrency_total_ht="'.$obj->multicurrency_total_ht.'" 
data-multicurrency_total_ttc="'.$obj->multicurrency_total_ttc.'"
data-total_ht="'.$obj->total_ht.'" 
data-total_ttc="'.$obj->total_ttc.'"
data-fk_product="'.$obj->fk_product.'">';
print '<td><a class="group1" href="'.$imgs->share_phath.'" title="'.$ref.' - '.$label.'"><img src="'.$imgs->share_phath.'" width="50px"></a></td>';
print '<td>';
print '<div class="form-check"><input data-id="'.$obj->rowid.'" data-fk_facture="'.$obj->fk_facture.'" data-p_ref="'.$ref.'" data-label="'.$label.'" 
data-qty="'.$obj->qty.'" 
data-subprice="'.$obj->subprice.'"  
data-multicurrency_subprice="'.$obj->multicurrency_subprice.'" 
data-remise_percent="'.$obj->remise_percent.'" data-tva_tx="'.$obj->tva_tx.'" 
data-total_ht="'.$obj->total_ht.'" data-total_ttc="'.$obj->total_ttc.'" data-indice="'.$fila.'" 
data-id="'.$obj->rowid.'" data-fk_product="'.$obj->fk_product.'" type="checkbox" class="check_product_'.$obj->rowid.' 
form-check-input check_product" id="check_product_'.$obj->rowid.'"><label class="form-check-label" for="check_product_'.$obj->rowid.'">
<a class="product_info text-secondary" data-rowid="'.$obj->rowid.'" data-fk_facture="'.$obj->fk_facture.'">'.$ref.'</a>
</label></div>';
print '</td>';
print '<td>'.$label.'</td>';
print '<td>'.$obj->qty.'</td>'; 
if($_SESSION['MULTICURRENCY_CODE'] == 'USD') print '<td class="dolar">'.$moneda_d.price($obj->multicurrency_subprice).'</td>'; 
print '<td>'.$moneda.price($obj->subprice).'</td>';        
print '<td>%'.price($obj->remise_percent).'</td>';
print '<td>%'.price($obj->tva_tx).'</td>';
if($_SESSION['MULTICURRENCY_CODE'] == 'USD') print '<td class="dolar">'.$moneda_d.price($obj->multicurrency_total_ht).'</td>';      
if($_SESSION['MULTICURRENCY_CODE'] == 'USD') print '<td class="dolar">'.$moneda_d.price($obj->multicurrency_total_ttc).'</td>'; 
print '<td>'.$moneda.price($obj->total_ht).'</td>';      
print '<td>'.$moneda.price($obj->total_ttc).'</td>';  
print '</tr>';
$fila++;
}
 
?>
 
  </tbody>
</table>
</div>   
</div> 
<!--  /tabla detalle --> 

<div class="col-md-8">
<?php

$bank_active = json_decode($cash->bank_active);
$bank_actives = implode(',',$bank_active);
$sqs = '';
$bancos1 = select_bancos(' AND rowid IN('.$bank_actives.') AND currency_code="'.$_SESSION['MULTICURRENCY_CODE'].'" AND courant=2');
$bancos2 = select_bancos(' AND rowid IN('.$bank_actives.') AND currency_code="'.$_SESSION['MULTICURRENCY_CODE'].'" AND courant !=2');

  $metodos1 = select_metodo_pago(' AND id ='.$cash->fk_modepaycash.'');  

  $metodos2 = select_metodo_pago(' AND id !='.$cash->fk_modepaycash.'');  

?>

<div class="table-responsive">
<table class="table table-bordered" width="100%">
<!-- Informacion de los metodos de pago -->
<?php 
if($user->rights->pos->payment_methods){//gestion de permiso de metodo de pago
?>
    <thead>
    <tr class="btn-elegant" id='tr_dolar_cambio'>
      <th class="text-center">
      
      <div class="input-group">
      <input class="form-control" data-dolar="0" type="text" style="width:40px" name="pago1_dolar" id="pago1_dolar" value="0">
      <div class="input-group-prepend">
      <span class="input-group-text btn-success" id="btn_pago1"><i class="fas fa-share" aria-hidden="true"></i></span>
      </div>         
      </div> 
      
      
      </th>  
      <th class="text-center" colspan="2" style="font-size: 1.5rem;">METODO DE PAGO</th> 
      <th class="text-center">
      <div class="input-group">
      <input class="form-control" data-dolar="0" type="text" style="width:40px" name="pago2_dolar" id="pago2_dolar" value="0">
      <div class="input-group-prepend">
      <span class="input-group-text btn-success" id="btn_pago2"><i class="fas fa-share" aria-hidden="true"></i></span>
      </div>         
      </div>
      </th>           
    </tr>

    <tr class="btn-elegant" id="tr_dolar_apartado" style="display:none">
      <th class="text-center">
      
      <div class="input-group">
      <input class="form-control" data-dolar="0" type="text" style="width:40px" name="pago1_ap" id="pago1_ap" value="<?php echo $conf->global->POS_MIN_MONTO_APARTADO;?>%">
      <div class="input-group-prepend">
      <span class="input-group-text btn-secondary" id="btn_pago_ap1"><i class="fas fa-share" aria-hidden="true"></i></span>
      </div>         
      </div> 
      
      
      </th>  
      <th class="text-center" colspan="2" style="font-size: 1.5rem;">Aplicar <?php echo $conf->global->POS_MIN_MONTO_APARTADO;?>%</th> 
      <th class="text-center">
      <div class="input-group">
      <input class="form-control" data-dolar="0" type="text" style="width:40px" name="pago2_ap" id="pago2_ap" value="<?php echo $conf->global->POS_MIN_MONTO_APARTADO;?>%">
      <div class="input-group-prepend">
      <span class="input-group-text btn-secondary" id="btn_pago_ap2"><i class="fas fa-share" aria-hidden="true"></i></span>
      </div>         
      </div>
      </th>           
    </tr> 

  </thead> 
<?php } ?>
  <!-- fin de la linea de metodo de pago-->       
  <tbody>  
<tr> 
<td colspan="3">
<select name="ndc_desc[]" id="ndc_desc" class="browser-default custom-select" multiple>
<?php
$ndc_desc = get_descuento($cash->fk_soc);
foreach($ndc_desc as $v){
if($v['ref_client'] !=''){$ref_cliete = ' - ('.$v['ref_client'].')';}else{$ref_cliete ='';}
print '<option class="sel_'.$v['decuento_id'].'" value="'.$v['decuento_id'].'" data-amount="'.$v['amount_ttc'].'">'.price($v['amount_ttc']).$ref_cliete.' - ('.$v['ndc_facnumber'].') -> ('.$v['ndc_source_facnumber'].')</option>';
}
?>
</td>
<td>
<span id="split_ndc_desc" class="split_ndc_desc input-group-text btn-primary">DIVIDIR</span>
</td>
</tr> 
   
      <tr>
          <td><?php
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
					print '>'.$v['label'].'</option>';
					}
					print '</select>';
					//fin select de metodo de pagos
?></td>
 <td>

 <input type="hidden" name="signo_banco_1" id="signo_banco_1" value="<?php echo $signo1 ?>">
 <div class="input-group mb-3">
 <input style="width:100px;color:<?php echo $color1 ?>;background-color:<?php echo $color1_2 ?>" data-valor="0" data-pago="1" type="text" class="pago form-control"  name="pago1" id="pago1" value="0"/>
 <div class="input-group-prepend"><span id="in_pago1" data-pago="1" class="in_pago input-group-text btn-primary"><i class="fas fa-share" aria-hidden="true"></i></span></div>
</div>

 </td>         
          <td><?php
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
print '>'.$v['label'].'</option>';
}
print '</select>';
//fin select de metodo de pagos

?></td>
<td>
<input type="hidden" name="signo_banco_2" id="signo_banco_2" value="<?php echo $signo2 ?>">
 <div class="input-group mb-3">
 <input style="width:100px;color:<?php echo $color1 ?>;background-color:<?php echo $color1_2 ?>" type="text" data-valor="0" data-pago="2" class="pago form-control"  name="pago2" id="pago2" value="0"/>
 <div class="input-group-prepend"><span id="in_pago2" data-pago="2" class="in_pago input-group-text btn-primary"><i class="fas fa-share" aria-hidden="true"></i></span></div>
</div>
</td>
</tr> 

<tr>
<td></td>
<td></td>
<td>
<?php
//select de bancos
print '<select data-banco="2" class="banco browser-default custom-select" name="banco3" id="banco3">';
foreach($bancos2 as $v){
            print '<option data-pago="pago2" data-currency_code="'.$v['currency_code'].'" value="'.$v['id'].'"';
if($cash->fk_paybank_extra==$v['id']) {print ' selected';}
print '>'.$v['ref'].'</option>';
}
print '</select>';
        //fin select bancos
        
//select de metodo de pagos
print '<select class="browser-default custom-select" name="metodo3" id="metodo3">';
foreach($metodos2 as $v){

print '<option data-metodo2_id="'.$v['id'].'" value="'.$v['code'].'"';
if($cash->fk_modepaybank_extra==$v['id']) {print ' selected';}
print '>'.$v['label'].'</option>';
}
print '</select>';
//fin select de metodo de pagos
?>
</td>
<td>
<input type="hidden" name="signo_banco_3" id="signo_banco_3" value="<?php echo $signo2 ?>">
 <div class="input-group mb-3">
 <input style="width:100px;color:<?php echo $color1 ?>;background-color:<?php echo $color1_2 ?>" type="text" data-valor="0" data-pago="3" class="pago form-control"  name="pago3" id="pago3" value="0"/>
 <div class="input-group-prepend"><span id="in_pago3" data-pago="3" class="in_pago input-group-text btn-primary"><i class="fas fa-share" aria-hidden="true"></i></span></div>
</div>
</td>
</tr>

<tr>
<td align="right" class="font-weight-bold" >
#vaucher 
</select>
</td>
<td colspan="3"><input type="text" class="form-control" name="vaucher_num" id="vaucher_num"></td>
</tr>  

  </tbody>   
    </table> 
</div>
<div id="total_c" class="text-center totales">
<div class="alert alert-success" role="alert" id="text_covert" style="font-size:1rem;font-weight: 100;display:none"></div>
<ul class="list-group">
<li class="list-group-item">Total: <span id="g_total" data-total_ttc="<?php if($_SESSION['MULTICURRENCY_CODE'] == 'USD'){echo $multicurrency_total_ttc;}else{echo $total_ttc;} ?>"><?php if($_SESSION['MULTICURRENCY_CODE'] == 'USD'){echo $moneda.price($multicurrency_total_ttc);}else{echo $moneda.price($total_ttc);} ?></span></li>
<li class="list-group-item">Pagado: <span id="g_pagado">0</span></li>
<li class="list-group-item">Vuelto: <span id="g_vuelto" data-vuelto="0">0</span></li>
</ul>

</div>

</div>


<div class="col-md-4">
<div class="table-responsive">   
    <table class="table table-bordered table-striped" width="100%">
    <thead>
    <tr class="btn-elegant">
      <th scope="col" class="text-center">Totales</th>           
    </tr>
  </thead>        
  <tbody>   
<?php if($_SESSION['MULTICURRENCY_CODE'] == 'USD') { ?><tr class="dolar"><td class="font-weight-bold dolar">Subtotal($): <span id="t_subtotal_d"><?php echo $moneda_d.price($multicurrency_total_ttc) ?></span></td></tr> <?php } ?> 
      <tr><td class="font-weight-bold">Subtotal: <span id="t_subtotal"><?php echo $moneda.price($total_ht) ?></span></td></tr>   
      <?php if($_SESSION['MULTICURRENCY_CODE'] == 'USD') { ?><tr><td class="font-weight-bold dolar">Descuento($): <span id="t_descuento_d"><?php echo $moneda_d.price($multicurrency_descuento) ?></span></td></tr><?php } ?> 
      <tr><td class="font-weight-bold">Descuento: <span id="t_descuento"><?php echo $moneda.price($descuento) ?></span></td></tr>      
      <?php if($_SESSION['MULTICURRENCY_CODE'] == 'USD') { ?><tr><td class="font-weight-bold dolar">IVA($): <span id="t_iva_d"><?php echo $moneda_d.price($multicurrency_total_tva) ?></span></td></tr><?php } ?> 
      <tr><td class="font-weight-bold">IVA: <span id="t_iva"><?php echo $moneda.price($total_tva) ?></span></td></tr>
      <?php if($_SESSION['MULTICURRENCY_CODE'] == 'USD') { ?><tr><td class="font-weight-bold dolar">Total($): <span data-multicurrency_total_ttc="<?php echo $total_ttc ?>" id="t_total_d"><?php echo $moneda_d.price($multicurrency_total_ttc) ?></span></td> </tr><?php } ?> 
      <tr><td class="font-weight-bold">Total: <span data-total_ttc="<?php echo $total_ttc ?>" id="t_total"><?php echo $moneda.price($total_ttc) ?></span></td> </tr>           
  </tbody>   
    </table> 
    </div>
  <center><span class="font-weight-bold">Nota:</span></center>
  <textarea name="public_note" id="public_note"  style="width:100%"></textarea><br>
  Orden de Salida N°: <input type="text" name="options_orden_salida" id="options_orden_salida" value="">     
    </div>



		
<div class="col-md-12" id="btn_validar">
<center><div id="boton_validar"></div></center>      
</div>

</div>

</div>



<!-- Modal: modalCart -->
<div class="modal fade" id="modal_product_info" tabindex="-1" role="dialog" aria-labelledby="modal_product_info"
  aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <!--Header-->
      <div class="modal-header">
        <h4 class="modal-title" id="myModalLabel">Detalle de linea</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <!--Body-->
      <div class="modal-body" id="body_modal_product_info">
      </div>
      <!--Footer-->
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-primary" data-dismiss="modal">Close</button>
        <!--<button class="btn btn-primary">Checkout</button>-->
      </div>
    </div>
  </div>
</div>
<!-- Modal: modalCart -->



<!-- Modal: modalCart -->
<div class="modal fade" id="modalCart" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-fluid" role="document">
    <div class="modal-content">
      <!--Header-->
      <div class="modal-header">
        <h4 class="modal-title" id="myModalLabel">Listado de Facturas</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <!--Body-->
      <div class="modal-body">

        <table class="table table-hover">
          <thead>
            <tr>
              <th colspan="2"><button id="fusionar" class="btn btn-primary btn-sm">Unificar Seleccionadas</button></th>              
              <th>Cliente</th>
              <th>Alias</th>
              <th>vendedor</th>
              <th>Mesa</th>              
              <th>Monto</th>
              <th>Hora</th>
              <th>Tipo</th>
              <th></th>
            </tr>
          </thead>
          <tbody id="save_fac_list">

          </tbody>
        </table>

      </div>
      <!--Footer-->
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-primary" data-dismiss="modal">Close</button>
        <!--<button class="btn btn-primary">Checkout</button>-->
      </div>
    </div>
  </div>
</div>
<!-- Modal: modalCart -->



<!-- Modal -->
<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
  aria-hidden="true">

  <!-- Add .modal-dialog-centered to .modal-dialog to vertically center the modal -->
  <div class="modal-dialog modal-dialog-centered" role="document">


    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
<!-- CALCULADORA -->

      <div class="calc-card">
        <button id="clear" value="" class="button3">C</button><div id="screen" class="screen">0</div>
    <div class="buttons2">

      <button class="digit button3" value="7">7</button>
      <button class="digit button3" value="8">8</button>
      <button class="digit button3" value="9">9</button>
      <button class="operator button3" id="divide" value="/">&#247;</button>
      <button class="digit button3" value="4">4</button>
      <button class="digit button3" value="5">5</button>
      <button class="digit button3" value="6">6</button>
      <button class="operator button3" id="minus" value="-">-</button>
      <button class="digit button3" value="1">1</button>
      <button class="digit button3" value="2">2</button>
      <button class="digit button3" value="3">3</button>
      <button class="operator button3" id="plus" value="+">+</button>
      <button class="digit button3" value="0">0</button>
      <button class="digit button3" value=".">.</button>
      <button id="equal" class="button3">=</button>
        <button class="operator button3" id="multiply" value="*">x</button>
    </div>

      </div>

<!-- /FIN CALCULADORA -->

    </div>
  </div>
</div>

