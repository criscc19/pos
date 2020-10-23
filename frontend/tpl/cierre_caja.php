  <!-- Central Modal Small -->
<div class="modal fade" id="cierre_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <form method="POST" action="cierre_caja.php" id="form_cierre">
  <!-- Change class .modal-sm to change the size of the modal -->
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title w-100" id="myModalLabel">Cierre de Caja</h4>
        <select name="tipo_cierre" id="tipo_cierre" class="browser-default custom-select">
        <option value="-1">Elija el tipo de control</option>   
        <option value="0">ARQUEO</option>
        <option value="1">CIERRE</option>
        </select>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      <input type="hidden" name="arqueo_currency_code" value="<?php echo $_SESSION['MULTICURRENCY_CODE']?>">
      <input type="hidden" name="cierre_fk_cash" value="<?php echo $cash->id ?>">
      <input type="hidden" name="cierre_fk_cierre" value="<?php echo $control->id ?>">      
            <!----------------------------------------------------------->
      <!-- Contenido  -->
      <?php
      
      $bancos_cierre = select_bancos(' AND currency_code="CRC"');
      $dolar_bancos_cierre = select_bancos(' AND currency_code="USD"');
      $arqueos = get_arqueos($cash->id,$control->id);
      ?>
      <div id="modo_cierre" style="display:none">

      <table width="100%">
      <tr><td colspan="5" align="center" class="text-white elegant-color-dark">DETALLE EN COLONES</td></tr>        
    <?php
    foreach($bancos_cierre as $v){
      $montos = get_pagos_banco($control->id,'CRC',' AND bca.rowid = '.$v['id'].'');
      $monto = 0;
      if(count($montos) > 0){
      foreach($montos as $mo){
      $monto += $mo['amount'];  
      }
     }

    $montos_cash = get_pagos_banco($control->id,'CRC',' AND pa.metodo_pago ="LIQ"');
    $monto_cash = 0;
    if(count($montos_cash) > 0){
        foreach($montos_cash as $mo_cash){
        $monto_cash += $mo_cash['amount'];  
        }
      }

    $montos_card = get_pagos_banco($control->id,'CRC',' AND pa.metodo_pago ="CB"');
    $monto_card = 0;
    if(count($montos_card) > 0){
        foreach($montos_card as $mo_card){
        $monto_card += $mo_card['amount'];  
        }
      }
      
    $monto_card = 0;
    if(count($montos_card) > 0){
        foreach($montos_card as $mo_card){
        $monto_card += $mo_card['amount'];  
        }
      }

      
      if(!$conf->global->POS_CIERRE_BANCOS > 0){$visible_b='style="display:none"';}else{$visible_b='';}

      print '<tr '.$visible_b.'>';  
      print '<td colspan="3">Monto: '.$v['label'].'</td>';
      print '<td colspan="2"><input type="float" name="bank_'.$v['id'].'" id="bank_'.$v['id'].'" value="'.$monto.'" placeholder="'.$v['id'].' 100" minlength="1" onkeypress="return soloNumeros(event);" onchange="verifyOnChange(this.id);"/> </td>';
      print '</tr>';
      
     }
?>
        <tr>
            <td colspan="3">Monto en tarjeta:</td>
            <td colspan="2">
            <input type="text" readonly name="tarjeta" id="tarjeta" value="<?php echo $monto_card; ?>" placeholder="10.000" minlength="1" onkeypress="return soloNumeros(event);" onchange="verifyOnChange(this.id);"/> 
            
            </td>
        </tr>

        <tr>
            <td colspan="3">Monto en Efectivo:</td>
            <td colspan="2">
            <input readonly type="float" name="efectivo" id="efectivo" value="<?php echo $monto_cash; ?>"  placeholder="10.000" minlength="1" onkeypress="return soloNumeros(event);" onchange="verifyOnChange(this.id);"/>
            
            </td>
        </tr>

        <tr>
            <td colspan="3">Monto en arqueos:</td>
            <td colspan="2">
            <input readonly type="text" id="arq_amount_real" name="arq_amount_real" value="<?php echo (float)$arqueos['amount_real'] ?>">
          
            </td>
        </tr>           
               

        <tr>
            <td>Desgloce:</td>
            <td colspan="4"></td>
        </tr>
        <tr><td colspan="5"><hr></td></tr>

<?php $denominaciones = select_denominaciones(' AND muticurrency_code="CRC"');

$cont = 2;
foreach ($denominaciones as $denominacion) {
if($denominacion['code'] <= 500){
$icono = '<img src="img/money/CRC/'.$denominacion['code'].'.png" width="25px">';
}

if($denominacion['code'] > 500){
$icono = '<img src="img/money/CRC/'.$denominacion['code'].'.png" width="50px">';
}

if ($cont%2 == 0) { ?>
<tr>
    <td align="right">
      <label>
        <?php echo $denominacion['label']; ?>
      </label> 
        <?php echo $icono; ?></td>
    <td align="left">
        <input style="width:50px" class="valor" data-valor="<?php echo $denominacion['code']; ?>" type="number" name="nom_<?php echo $denominacion['id']; ?>" id="val_<?php echo $denominacion['code']; ?>" placeholder="10" minlength="1"/>
            <span class="text_valor" id="text_<?php echo $denominacion['code']; ?>">0</span>
    </td>
    <td width="20%"></td>

    <?php } else { ?>
    <td align="right">
      <label>
          <?php echo $denominacion['label']; ?>
      </label>
          <?php echo $icono; ?></td>
    <td align="left">
        <input style="width:50px" class="valor" data-valor="<?php echo $denominacion['code']; ?>" type="number" name="nom_<?php echo $denominacion['id']; ?>" id="val_<?php echo $denominacion['code']; ?>" placeholder="10"  minlength="1" /> 
            <span class="text_valor" id="text_<?php echo $denominacion['code']; ?>">0</span>
    </td>
    </tr>
    <?php
     }
    
  $cont++;
}
?>
<tr>
<td colspan="5" align="center">
<b>Total efectivo: <span id="tocierre"></span></b><br> <b>Diferencia: <span id="diferencia"></span></b> 
</td>
</tr>	

<?php if(!$conf->global->POS_CIERRE_DOLARES){$visible_d='style="display:none"';}else{$visible_d='';} ?>


<tr <?php echo $visible_d; ?>><td colspan="5" align="center" class="text-white green darken-4">DETALLE EN DOLARES</td></tr> 














<!---- dolares -->
       
<?php
    foreach($dolar_bancos_cierre as $v){
      $dolar_montos = get_pagos_banco($control->id,'USD',' AND bca.rowid = '.$v['id'].'');
      $dolar_monto = 0;
      if(count($dolar_montos) > 0){
      foreach($dolar_montos as $dolar_mo){
      $dolar_monto += $dolar_mo['multicurrency_amount'];  
      }
     }

    $dolar_montos_cash = get_pagos_banco($control->id,'USD',' AND pa.metodo_pago ="LIQ"');
    $dolar_monto_cash = 0;
    if(count($dolar_montos_cash) > 0){
        foreach($dolar_montos_cash as $dolar_mo_cash){
        $dolar_monto_cash += $dolar_mo_cash['multicurrency_amount'];  
        }
      }

    $dolar_montos_card = get_pagos_banco($control->id,'USD',' AND pa.metodo_pago ="CB"');
    $dolar_monto_card = 0;
    if(count($dolar_montos_card) > 0){
        foreach($dolar_montos_card as $dolar_mo_card){
        $dolar_monto_card += $dolar_mo_card['multicurrency_amount'];  
        }
      }
      


      print '<tr '.$visible_d.'>';    
      print '<td colspan="3">Monto: '.$v['label'].'</td>';
      print '<td colspan="2">';
      print '<input type="float" value="'.$dolar_monto.'" name="bank_dolar_'.$v['id'].'" id="bank_dolar_'.$v['id'].'" placeholder="'.$v['id'].' 100" minlength="1" onkeypress="return soloNumeros(event);" onchange="verifyOnChange(this.id);llenarhidden(this.id);"/> ';
      print '</td>';
      print '</tr>';
      
    }

    ?>
        <tr <?php echo $visible_d; ?>>
            <td colspan="3">Monto en tarjeta:</td>
            <td colspan="2">
            <input type="text" readonly name="tarjeta_dolar" value="<?php echo $dolar_monto_card ?>" id="tarjeta_dolar" placeholder="10.000" minlength="1" /> 
            </td>
        </tr>

        <tr <?php echo $visible_d; ?>>
            <td colspan="3">Monto en Efectivo:</td>
            <td colspan="2">
            <input type="float" readonly name="efectivo_dolar"  value="<?php echo $dolar_monto_cash ?>" id="efectivo_dolar" placeholder="10.000" minlength="1""/> 
            </td>
        </tr> 

        <tr <?php echo $visible_d; ?>>
            <td colspan="3">Monto en dolares arqueos:</td>
            <td colspan="2">
            <input type="text" readonly id="arq_multicurrency_amount_real" name="arq_multicurrency_amount_real" value="<?php echo (float)$arqueos['multicurrency_amount_real'] ?>">
            </td>
        </tr>         
                     
        <tr <?php echo $visible_d; ?>>
            <td>Desgloce:</td>
            <td colspan="4"></td>
        </tr>
        <tr><td colspan="5"><hr></td></tr>

<?php $denominaciones_dolar = select_denominaciones(' AND muticurrency_code="USD"');

$cont = 2;
foreach ($denominaciones_dolar as $denominacion_dolar) {
$icono2 = '<img src="img/money/USD/'.$denominacion_dolar['code'].'.png" width="50px">';

if ($cont%2 == 0) { ?>
<tr <?php echo $visible_d; ?>>
    <td align="right"><label>
    <?php echo $denominacion_dolar['label']; ?>
    </label> 
    <?php echo $icono2; ?></td>
    <td align="left">
    <input style="width:50px" class="valor_dolar" data-valor_dolar="<?php echo $denominacion_dolar['code']; ?>" type="number" name="dolar_nom_<?php echo $denominacion_dolar['id']; ?>" id="val_<?php echo $denominacion_dolar['code']; ?>" placeholder="10" minlength="1" onkeypress="return soloNumeros(event);" onchange="verifyOnChange(this.id);llenarhidden(this.id);"/>
     <span class="text_valor_dolar" id="text_<?php echo $denominacion_dolar['code']; ?>_dolar">0</span>
     </td>
    <td width="20%"></td>

    <?php } else { ?>
    <td align="right">
      <label>
          <?php echo $denominacion_dolar['label']; ?>
      </label>
       <?php echo $icono2; ?>
    </td>
    <td align="left">
    <input type="hidden" name="1val_<?php echo $denominacion_dolar['code'] ?>" id="1val_<?php echo $denominacion_dolar['code'] ?>"/>
      <input style="width:50px" class="valor_dolar" data-valor_dolar="<?php echo $denominacion_dolar['code']; ?>" type="number" name="dolar_nom_<?php echo $denominacion_dolar['id']; ?>" id="val_<?php echo $denominacion_dolar['code']; ?>" placeholder="10" minlength="1" onkeypress="return soloNumeros(event);" onchange="verifyOnChange(this.id);llenarhidden(this.id);"/>
        <span class="text_valor_dolar" id="text_<?php echo $denominacion_dolar['code']; ?>_dolar">0</span>
    </td>
    </tr>
    <?php
     }
    
  $cont++;
}
?>
<tr <?php echo $visible_d; ?>>
<td colspan="5" align="center">
<b>Total efectivo: <span id="tocierre_dolar"></span></b><br> <b>Diferencia: <span id="diferencia_dolar"></span></b> 
</td>
</tr>	
<!---- fin dolares -->
</table>
</div>

<div id="modo_arqueo" style="display:none">
<!---- div login -->
<div id="login_div_r">
<div class="input-group mb-3" style="width:300px" >
      <div class="input-group-prepend">
      <span class="input-group-text"><i class="fas fa-user"></i></span>
      </div>  
      <input class="form-control" type="text" placeholder="Usuario" name="arqueo_user" id="arqueo_user">  
      </div>

      
<div class="input-group mb-3" style="width:300px" >
      <div class="input-group-prepend">
      <span class="input-group-text"><i class="fas fa-key"></i></span>
      </div>  
      <input class="form-control" type="password" placeholder="Contraseña" id="arqueo_pass" name="arqueo_pass">   
      </div>
<div id="login_div_msg_red" style="color:red;display:none">Usuario no valido</div>
<div id="login_div_msg_green" style="color:green;display:none"></div>
<div class="btn btn-success btn-sm" id="btn_login_arqueo">Comprobar usuario</div>
<div id="login_arqueo_loader" style="display:none" class="spinner-border text-primary" role="status">
  <span class="sr-only">Autenticando...</span>
</div>
</div>
<!---- /div login -->

<!---- arqueo -->
<div id="login_div" style="display:none">
<input type="hidden" name="fk_responsable" id="fk_responsable" class="form-control" value="">
Teorico CRC: <input readonly type="text" name="teorico" id="teorico" class="form-control" value="<?php echo (float)$monto_cash; ?>" style="width:200px"><br>
Teorico USD: <input readonly type="text" name="teorico_usd" id="teorico" class="form-control" value="<?php echo (float)$dolar_monto_cash; ?>" style="width:200px"><br>
<div class="form-row">
<div class="form-group">
 Real:<input type="text" name="real" id="real" class="form-control" style="width:200px">
</div>
<div class="form-group">
Moneda: <select name="arq_moneda" class="form-control"><option value="CRC" <?php if($_SESSION['MULTICURRENCY_CODE'] == 'CRC') echo 'selected'; ?>>CRC</option>
<option value="USD" <?php if($_SESSION['MULTICURRENCY_CODE'] == 'USD') echo 'selected'; ?>>USD</option></select>
</div>
</div>
<br>
Comentario: <textarea name="comentario" class="form-control"></textarea><br>

</div>
<!---- /arqueo -->
</div>


       <!-- /Contenido  -->
      </div>
      <div class="modal-footer">
      <!--<form method="POST" action="tpv3.php?cierre=1">-->

        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-success btn-sm" name="btncerrar" id="btncerrar">Aplicar Cierre/Arqueo</button>

      </div>
    </div>
  </div>
      </form>  
</div>
<!-- Central Modal Small -->
  

<script>
   $("#btncerrar").click(function(){
    $("#form_cierre").submit();
    //mensaje('provando','solo la informacion necesaria','success');
   });

  /**Funcion para poder mostrar un mensaje de confirmacion */
  function mensaje(title,mensaje,icono){//warning and success
  Swal.fire({
  title: title,
  text: mensaje,
  icon: 'success',
  showCancelButton: true,
  confirmButtonColor: '#3085d6',
  cancelButtonColor: '#d33',
  confirmButtonText: 'Confirmar'
}).then((result) => {
  if (result.value==true) {
    Swal.fire(
      'Informacion',
      'Procesada exitozamente',
      'success'
    )
  }
})
}
</script>

<script>
    $( document ).ready(function() {
        $(".valor").keyup(function(){
            valor = parseFloat($(this).data("valor"));
            cant = $(this).val();
            total = valor * cant;
            $("#text_"+valor).text(total);
            calcular()
            })

        $(".valor").change(function(){
            valor = parseFloat($(this).data("valor"));
            cant = $(this).val();
            total = valor * cant;
            $("#text_"+valor).text(total);
            calcular()
            })					

            function calcular(){
            tot = 0;
            valor_cierre = parseFloat($("#efectivo").val());
            valor_cierre_arq = parseFloat($("#arq_amount_real").val());
            $(".text_valor").each(function(){
            valor = parseFloat($(this).text());
            tot += valor;
            });
            $("#tocierre").text(tot);
            total_cierre = tot - valor_cierre + valor_cierre_arq;
            if(total_cierre >= 0){
                $("#diferencia").attr("style","color:green");	
                $("#diferencia").text(total_cierre);
                }else{
                $("#diferencia").attr("style","color:red");	
                $("#diferencia").text(total_cierre);
                                        
                }
            }
    });
</script>

<script>
    $( document ).ready(function() {
        $(".valor_dolar").keyup(function(){
            valor = parseFloat($(this).data("valor_dolar"));
            cant = $(this).val();
            total = valor * cant;
            $("#text_"+valor+'_dolar').text(total);
            calcular()
            })

        $(".valor_dolar").change(function(){
            valor = parseFloat($(this).data("valor_dolar"));
            cant = $(this).val();
            total = valor * cant;
            $("#text_"+valor+'_dolar').text(total);
            calcular()
            })					

            function calcular(){
            tot = 0;
            valor_cierre = parseFloat($("#efectivo_dolar").val());
            multicurrency_amount_real_arq = parseFloat($("#arq_multicurrency_amount_real").val());            
            $(".text_valor_dolar").each(function(){
            valor = parseFloat($(this).text());
            tot += valor;
            });
            $("#tocierre_dolar").text(tot);
            total_cierre = tot - valor_cierre + multicurrency_amount_real_arq;
            if(total_cierre >= 0){
                $("#diferencia_dolar").attr("style","color:green");	
                $("#diferencia_dolar").text(total_cierre);
                }else{
                $("#diferencia_dolar").attr("style","color:red");	
                $("#diferencia_dolar").text(total_cierre);
                                        
                }
            }
    });
</script>