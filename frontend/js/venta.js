if($('#tipo').val() == 3){
$('#tr_dolar_apartado').show();  
}



function add_line(){
if($('#cant_attributes').val() > 0 ){
  get_atributes($('#fk_product').val());
$('#variantModalCenter').modal('show');
return;  
}
$('#barra_c').show();
fk_product = $('#fk_product').val();
cantidad = parseFloat($('#cantidad').val());
descuento = parseFloat($('#descuento').val());
tipo_desc = $('#tipo_desc').val();
price_level = $('#price_level').val();
moneda = $('#moneda').val();
fk_soc = $('#fk_soc').val();
tipo = $('#tipo').val();
fk_facture = $('#fk_facture').val();
select_product = $('#select_product').val();
max_discount = parseFloat($("#max_discount").val());
entrepot_stock = parseFloat($("#entrepot_stock").val());
stock_negativo = parseInt($("#stock_negativo").val());
limitar_login = parseInt($("#limitar_login").val());
servicios = parseInt($("#servicios").val());
product_type = parseInt($("#product_type").val());
///logica de limitaciones de stock y login
if(product_type == 0){
if(fk_product == 0){
  Swal.fire({
      type: 'error',
      title: 'No se a seleccionado un producto',
      showConfirmButton: false,
      timer: 1000
      
    })
return;
} 
} 


if(cantidad <= 0 || cantidad==''){
Swal.fire({
    type: 'error',
    title: 'La cantidad minima es 1',
    showConfirmButton: false,
    timer: 1000
    
  })
return;
}

if(cantidad <= 0 || cantidad==''){
Swal.fire({
    type: 'error',
    title: 'La cantidad minima es 1',
    showConfirmButton: false,
    timer: 1000
    
  })
return;
}

check_desc = limitar_descuento(descuento,'#descuento');
if(check_desc == 0){
    Swal.fire({
        type: 'error',
        title: 'El descuento maximo esta limitado',
        showConfirmButton: false,
        timer: 3000
      })  
return
}

  if(stock_negativo==0 && product_type==0 && entrepot_stock <=0){
    Swal.fire({
        type: 'error',
        title: 'La configuración del sistema no admite stock en negativo',
        showConfirmButton: false,
        timer: 3000
      })  
    $('#select_product').val('').trigger('change');   
    return;
    }
    
  if(stock_negativo==0 && product_type==0 && cantidad >= entrepot_stock){
    Swal.fire({
        type: 'error',
        title: 'La configuración del sistema no admite stock en negativo',
        showConfirmButton: false,
        timer: 3000
      })  
    $('#select_product').val('').trigger('change');   
    return;
    }

    
    if(stock_negativo==1 && product_type==0){
    if(entrepot_stock <=0 || cantidad > entrepot_stock){
    if(limitar_login==1 && product_type==0){
    $('#l_action').val('line');
    show_login('No hay existencias en la bodega');
    $('#select_product').val('').trigger('change');
    }else{
    Swal.fire({
        type: 'error',
        title: 'No hay existencias en la bodega',
        showConfirmButton: false,
        timer: 3000
      })
      $('#select_product').val('').trigger('change');  
      return;        
        }

        }
        if(entrepot_stock > 0 && cantidad < entrepot_stock){
          insert();
        }


  }


    if(stock_negativo==0 && product_type==0){
    if(entrepot_stock <=0 || cantidad > entrepot_stock){
      Swal.fire({
              type: 'error',
              title: 'No hay existencias en la bodega',
              showConfirmButton: false,
              timer: 1000
            })
            $('#select_product').val('').trigger('change');  
            return;        
        }
        if(entrepot_stock > 0 && cantidad < entrepot_stock){
          insert();
        }
      }

      if(stock_negativo==0 && product_type==0 && cantidad == entrepot_stock){
        insert();
      }

      if(stock_negativo==1 && product_type==0 && cantidad == entrepot_stock){
        insert();
      }

if(product_type==1 && servicios==1){
 insert(); 
}


}


///logica de liomitaciuones de stock y login
function insert(){
  fk_product = $('#fk_product').val();
  cantidad = parseFloat($('#cantidad').val());
  descuento = parseFloat($('#descuento').val());
  tipo_desc = $('#tipo_desc').val();
  price_level = $('#price_level').val();
  moneda = $('#moneda').val();
  fk_soc = $('#fk_soc').val();
  tipo = $('#tipo').val();
  fk_facture = $('#fk_facture').val();
  select_product = $('#select_product').val();
  max_discount = parseFloat($("#max_discount").val());
  entrepot_stock = parseFloat($("#entrepot_stock").val());
  stock_negativo = parseInt($("#stock_negativo").val());
  limitar_login = parseInt($("#limitar_login").val());  
  options_exoneracion = $("#options_exoneracion").val();    
//envio por ajax
$.ajax({
    type: "POST",
    url: "ajax/cash_detalle.php",
    data: {
      fk_product:fk_product,
      descuento:descuento,
      tipo_desc:tipo_desc,
      cantidad:cantidad,
      price_level:price_level,
      moneda:moneda,
      fk_soc:fk_soc,
      fk_facture:fk_facture,
      options_exoneracion:options_exoneracion,
      tipo:tipo,
      select_product:select_product,
      action:'add_line'
    },
    dataType: "json",
    success: function(resp) {
     $('#barra_c').hide();
      if(parseInt(resp.error) < 0 ){
        Swal.fire(
          'Error!',
          resp.messaje,
          'error'
        )  
        return
      }      
    generar(resp)

    $("#select_product").val("").trigger("change");
    $("#max_discount").val(0);
    $("#cantidad").val('');
    $("#fk_product").val(0);
    $("#select_product").focus(); 
    }
    
    })
    //envio por ajax  
}




 $('#btn_login').click(function(){
 login_user($('#l_usuario').val(),$('#l_pass').val());
 });


function show_login(message){
  $('#mess_login').html('<span style="color:red">'+message+'</span>')
  $('#modal_login').modal('show');
}


function login_user(usuario,pass){
 //envio por ajax
 $.ajax({
  type: "POST",
  url: "ajax/login.php",
  data: {
    usuario:usuario,
    pass:pass,
    action:'login'
  },
  dataType: "json",
  success: function(resp) {
    loginr = parseInt(resp.id);
    if(loginr > 0){
    if($("#l_action").val()=='line'){
    if(resp.rights.pos.stock){
    insert();  
    $('#modal_login').modal('hide');
    }else{$('#mess_error').html('<span style="color:red">Este usuario no tiene permisos para facturar stock negativo</span>');}
    
    $("#select_product").val("").trigger("change");
    }
      
    if($("#l_action").val()=='client'){
      if(resp.rights.pos.limitar){
        confirm_valida_tipo();
        $('#modal_login').modal('hide');
      }else{$('#mess_error').html('<span style="color:red">Este usuario no tiene permisos para facturar con limite de credito sobrepasado</span>');}
      
    }  

    
    $('#l_pass').val('');
    
    return; 
    }else{
      
      $('#mess_error').html('<span style="color:red">Usuario o contraseña invalido</span>');
      return;  
    }

  }
  
  })  
}


function limitar_factura(){
  credito_disponible = parseFloat($('#credito_disponible').val()); 
  t_total =   parseFloat($('#t_total').attr('data-total_ttc'));
  if(t_total > credito_disponible || credito_disponible <= 0){
    return true;
  }

  if(t_total < credito_disponible || credito_disponible >= 0){
    return false;
  }
  
}



function delete_line(){
  $('#barra_c').show();  
fk_product = $('#fk_product').val();
fk_soc = $('#fk_soc').val();
id = $('.tr_selected').attr('data-id');
fk_facture = $('#fk_facture').val();
 //envio por ajax
$.ajax({
  type: "POST",
  url: "ajax/cash_detalle.php",
  data: {
    fk_product:fk_product,
    fk_soc:fk_soc,
    id:id,
    fk_facture:fk_facture,
    action:'delete_line'
  },
  dataType: "json",
  success: function(resp) {
  $('#barra_c').hide();
  $("#select_product").val("").trigger("change");
  $("#max_discount").val(0);
  $("#cantidad").val('');
  $("#fk_product").val(0);
  $("#select_product").focus(); 
  generar(resp);
  }
  
  })
  //envio por ajax

}

function update_line(id,descuento,cantidad,precio,tva_tx,price_base){
  $('#barra_c').show(); 
  fk_facture = $('#fk_facture').val();
  fk_soc = $('#fk_soc').val();
  fk_product = $('.tr_selected').attr('data-fk_product');
  moneda = $('#moneda').val();
  tipo_desc = $('#tipo_desc').val();
  price_level = $('#price_level').val(); 
  precio_min = $('#precio_min').val();   
  options_exoneracion = $('#options_exoneracion').val();

 //envio por ajax
$.ajax({
  type: "POST",
  url: "ajax/cash_detalle.php",
  data: {
    id:id,
    descuento:descuento,
    tipo_desc:tipo_desc,
    cantidad:cantidad,
    precio:precio,
    fk_soc:fk_soc,
    tva_tx:tva_tx,
    fk_facture:fk_facture,
    fk_product:fk_product,
    precio_min:precio_min,
    moneda:moneda,
    options_exoneracion:options_exoneracion,
    price_level:price_level,
    price_base:price_base,
    action:'update_line'
  },
  dataType: "json",
  success: function(resp) {
  $('#barra_c').hide();    
  if(parseInt(resp.error) < 0 ){
    Swal.fire(
      'Error!',
      resp.messaje,
      'error'
    )  
    return
  }
  generar(resp)
  $("#cantidad").val('');
  $("#select_product").focus(); 
  }
  
  })
  //envio por ajax
}


function save_fac_confirm(){
  fk_soc_default = $('#fk_soc_default').val();
  fk_soc = $('#fk_soc').val();  
  Swal.fire({
    title: 'Enviar factura a caja?',
    html: 'Nombre del cliente: <input type="text" name="ref_cliente" id="ref_cliente">',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Enviar'
  }).then((result) => {
    if (result.value) {
    if(fk_soc == fk_soc_default){
    if($('#ref_cliente').val().length == 0){
      
      Swal.fire(
        'Obligatorio!',
        'El nombre del cliente es obligatorio.',
        'error'
      ) 
    }else{
    ref_client = $('#ref_cliente').val();
    save_fac(ref_client);
    }

    }else{
    ref_client = $('#ref_cliente').val();
    save_fac(ref_client);
    }


    }
  })  
}


function save_fac(ref_client){
  $('#barra_c').show(); 
  fk_facture = $('#fk_facture').val();
  fk_soc = $('#fk_soc').val();
  moneda = $('#moneda').val();
  tipo = $('#tipo').val();
  fk_mesa = $('#fk_mesa').val();

  ref_client = ref_client;
 //envio por ajax
 $.ajax({
  type: "POST",
  url: "ajax/cash_detalle.php",
  data: {
    fk_soc:fk_soc,
    fk_facture:fk_facture,
    moneda:moneda,
    ref_client:ref_client,
    fk_mesa:fk_mesa,
    tipo:tipo,
    action:'save_fac'
  },
  dataType: "json",
  success: function(resp) {
  generar(resp)
  $('#barra_c').hide();
  }
  
  })
  //envio por ajax

}




function save_fac_lits(){
  $('#barra_c').show(); 
  fk_facture = $('#fk_facture').val();
  fk_soc = $('#fk_soc').val();
  moneda = $('#moneda').val(); 
 //envio por ajax
 $.ajax({
  type: "POST",
  url: "ajax/cash_detalle.php",
  data: {
    fk_soc:fk_soc,
    fk_facture:fk_facture,
    moneda:moneda,
    action:'get_save_fac_list'
  },
  dataType: "json",
  success: function(resp) {
  $('#save_fac_list').html('') ; 
  $.each(resp, function( index, value ) {

    $('#save_fac_list').append(
    '<tr id="tr_fac_list_'+value.f_id+'">'+
    '<td><div class="form-check"><input data-fk_facture="'+value.f_id+'" type="checkbox" class="check_fac_'+value.f_id+' form-check-input check_fac" id="check_fac_'+value.f_id+'"><label class="form-check-label" for="check_fac_'+value.f_id+'"></label></div></td>'+    
    '<td data-fk_facture="'+value.f_id+'" data-login_vendedor="'+value.login+'" data-cliente="'+cliente+'" data-fk_soc="'+value.s_id+'" data-fk_vendedor="'+value.u_id+'" class="get_fac" style="cursor:pointer;color:blue">'+value.facnumber+'</td>'+
    '<td>'+value.nom+'</td>'+
    '<td>'+value.ref_client+'</td>'+
    '<td>'+value.login+'</td>'+
    '<td>'+value.mesa+'</td>'+
    '<td>'+value.total_ttc+'</td>'+ 
    '<td>'+value.fecha+'</td>'+
    '<td class="'+value.color+'">'+value.type+'</td>'+  
    '<td data-fk_facture="'+value.f_id+'" data-cliente="'+cliente+'" data-fk_soc="'+value.s_id+'" data-fk_vendedor="'+value.u_id+'" class="del_fac" style="color:red;cursor:pointer"><i class="fas fa-trash-alt"></i></td>'+              
    '</tr>'
    );
  })
  $('#barra_c').hide();
  $('#modalCart').modal('show');

//OBTENIDO FACTURA GUARDADA

$('.get_fac').click(function(){
  $('#barra_c').show(); 
  fk_facture = $(this).attr('data-fk_facture');
  fk_soc = $(this).attr('data-fk_soc');
  fk_vendedor = $(this).attr('data-fk_vendedor');
  cliente = $(this).attr('data-cliente');
  login_vendedor = $(this).attr('data-login_vendedor');
  $('#log_user').val(fk_vendedor);
//envio por ajax
$.ajax({
    type: "POST",
    url: "ajax/cash_detalle.php",
    data: {
      fk_soc:fk_soc,
      fk_facture:fk_facture,
      fk_vendedor:fk_vendedor,      
      action:'get_fac_lineas'
    },
    dataType: "json",
    success: function(resp) {
    generar(resp)
    if(resp.fac_info.ref_client !=''){
     ref_client = ' ('+resp.fac_info.ref_client+')';
    }else{
     ref_client = '';
    }
    $('#fac_num').text(resp.fac_info.ref);
    $('#current_vendedor').text(resp.fac_info.vendedor);
    $('#barra_c').hide();
    $('#modalCart').modal('hide');
    $("#fk_soc").val(resp.fac_info.fk_soc);
    $("#fk_facture").val(fk_facture);    
    $("#cliente").html(resp.fac_info.nom+ref_client);
    $("#ref_client").val(resp.fac_info.ref_client);
    $("#options_vendedor").val(fk_vendedor);
    $("#login_vendedor").val(fk_vendedor);    
    $.each($('#log_user option'), function( index, value ) {
      $(this).removeAttr('selected');
        })
    $("#log_user option[value="+fk_vendedor+"]").attr('selected', 'selected');   
    get_client_info(resp.fac_info.fk_soc);
    }
    
    })
    //envio por ajax  
});

//OBTENIDO FACTURA GUARDADA


//ELIMINADO FACTURA
$('.del_fac').click(function(){
  $('#barra_c').show(); 
  fk_facture = $(this).attr('data-fk_facture');

//envio por ajax
$.ajax({
    type: "POST",
    url: "ajax/cash_detalle.php",
    data: {
      fk_facture:fk_facture,   
      action:'del_fac'
    },
    dataType: "json",
    success: function(resp) {
    if(resp==1){
      $('#barra_c').hide();
      //$('#modalCart').modal('hide');
      location.reload(); 
    }

    }
    
    })
    //envio por ajax  
})
//FIN ELIMINADO FACTURA



  }
  
  })
  //envio por ajax
}


function set_descuento_global(){
  fk_facture = $('#fk_facture').val();
  fk_soc = $('#fk_soc').val();
  moneda = $('#moneda').val(); 
  tipo_desc = $('#tipo_desc').val();
  Swal.fire({
    title: 'Seguro de aplicar descuento global?',
    html: 'Descuento: <input type="number" name="desc_global" id="desc_global">',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Aplicar!'
  }).then((result) => {
    if (result.value) {
descuento =  $('#desc_global').val();

check_desc = limitar_descuento(descuento,'#desc_global');
if(check_desc == 0){
    Swal.fire({
        type: 'error',
        title: 'El descuento maximo esta limitado',
        showConfirmButton: false,
        timer: 3000
      })  
return
}

 //envio por ajax
 $.ajax({
  type: "POST",
  url: "ajax/cash_detalle.php",
  data: {
    fk_facture:fk_facture,
    fk_soc:fk_soc,
    moneda:moneda,
    descuento:descuento,
    tipo_desc:tipo_desc,
    action:'set_descuento'
  },
  dataType: "json",
  success: function(resp) {
     generar(resp) 
    $('#barra_c').hide();
  
  }
  
  })
  //envio por ajax 

    }
  })  


};

function limitar_descuento(descuento,element){
descu = 0;
tipo_descuento = $('#mode_limit').val();
descuento_cliente = $('#remise_client').val();
descuento_producto = $('#max_discount').val();
descuento_global = $('#limit_descuento').val();
if(tipo_descuento == 'global'){descu = parseFloat(descuento_global);}
if(tipo_descuento == 'producto'){descu = parseFloat(descuento_producto);}
if(tipo_descuento == 'cliente'){descu = parseFloat(descuento_cliente);}

if(descuento > descu ){
$(element).val(descu);   
return 0
}

if(descuento <= descu ){
return 1
}

}



//metodos de pago
function calculos(){
  $('#text_covert').hide();
  total =  parseFloat($('#g_total').attr('data-total_ttc'));
  pago = 0;
  $( ".pago" ).each(function( index ) {
    pago += parseFloat($(this).attr('data-valor'));
  });


  ndc_desc = 0;
  if($('#ndc_desc').val().length > 0){
  $.each($('#ndc_desc').val(), function( index, value ) {
  monto = $('.sel_'+value).attr('data-amount');
  ndc_desc +=parseFloat(monto);
  }) 
  }
  puntos = parseFloat($('#conv_points').val());


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
   $('#g_pagado').attr('data-vuelto',pago + ndc_desc + puntos );
   $('#g_pagado').text(numeral(pago + ndc_desc + puntos).format('0,0.00'));  

  s_total = total - pago - ndc_desc - puntos;




  if(s_total > 0){
  $('#g_vuelto').attr('style','color:red');
  oper = '-';  
  }else{
  $('#g_vuelto').attr('style','color:green');  
  oper = '';      
  }
  $('#g_vuelto').text(oper+numeral(Math.abs(s_total)).format('0,0.00'));
  $('#g_vuelto').attr('data-vuelto',s_total);
data  = {"total":total,"s_total":s_total,"pago":pago,"ndc":ndc_desc,"puntos":puntos};
  return data;
}



$('#btn_pago_ap1').click(function(){
  total =  parseFloat($('#g_total').attr('data-total_ttc')); 
  porc = parseFloat($('#monto_minimo_apartado').val());
  s_total = total * porc / 100;
  $('#pago1').attr('data-valor',s_total);
  $('#pago1').val(s_total);
  calculos('pago1');
 });


$('#btn_pago_ap2').click(function(){
  total =  parseFloat($('#g_total').attr('data-total_ttc')); 
  porc = parseFloat($('#monto_minimo_apartado').val());
  s_total = total * porc / 100;
  $('#pago2').attr('data-valor',s_total);
  $('#pago2').val(s_total);
  calculos('pago2');
 });




$('.pago').keyup(function(){
$(this).attr('data-valor',$(this).val());
id = $(this).data('pago');

});

$('.pago').blur(function(){
$(this).attr('data-valor',$(this).val());
id = $(this).data('pago');
data = calculos(id);
console.log(data)
});
 


$('.in_pago').click(function(){
  total =  parseFloat($('#g_total').attr('data-total_ttc'));
  id = $(this).data('pago');
  data = calculos();
  if(data.pago <= 0 && data.ndc && data.puntos){ 
    $('#pago'+id).val(data.total);
    $('#pago'+id).attr('data-valor',total);    
    }else{
    if(data.s_total > 0){
    $('#pago'+id).val(data.s_total);
    $('#pago'+id).attr('data-valor',data.s_total);       
    }

    }
   calculos();
})
//metodos de pago

function conversion(monto,moneda){
 cambio = parseFloat($('#multicurrency_tx').val());
  if(moneda == 'USD' && $('#moneda').val()=='CRC'){
  m_cambio = parseFloat(1/cambio);
  total_p = monto * m_cambio;
  $('#text_covert').html('Se ha hecho la conversion de moneda: '+numeral(total_p).format('0,0.00')+' '+$('#moneda').val()+' / '+numeral(monto).format('0,0.00')+' '+moneda)
  $('#text_covert').show();
  return total_p;
  
  }
  
  else if(moneda == 'CRC' && $('#moneda').val()=='USD'){
  m_cambio = parseFloat(1/cambio);
  total_p = monto / m_cambio;
  $('#text_covert').html('Se ha hecho la conversion de moneda: '+numeral(total_p).format('0,0.00')+' '+$('#moneda').val()+' / '+numeral(monto).format('0,0.00')+' '+moneda)
  $('#text_covert').show(); 
   return total_p;
  }
else{return monto;}

  }


function conversion_total(monto,moneda){
 cambio = parseFloat($('#multicurrency_tx').val());
  if(moneda == 'USD' && $('#moneda').val()=='CRC'){
  m_cambio = parseFloat(1/cambio);
  total_p = monto / m_cambio;
  $('#text_covert').html('Se ha hecho la conversion de moneda: '+numeral(monto).format('0,0.00')+' '+moneda+' / '+numeral(total_p).format('0,0.00')+' '+$('#moneda').val())
  $('#text_covert').show();
  return total_p;
  
  }
  
  else if(moneda == 'CRC' && $('#moneda').val()=='USD'){
  m_cambio = parseFloat(1/cambio);
  total_p = monto * m_cambio;
  $('#text_covert').html('Se ha hecho la conversion de moneda: '+numeral(monto).format('0,0.00')+' '+moneda+' / '+numeral(total_p).format('0,0.00')+' '+$('#moneda').val())
  $('#text_covert').show(); 
   return total_p;
  }
else{return monto;}

  }
  
function convertir_moneda(moneda1,moneda2,monto){
  cambio = parseFloat($('#multicurrency_tx').val()); 
if(moneda1 == 'USD' && moneda2 == 'CRC'){
  m_cambio = parseFloat(1/cambio);
  total_p = monto * m_cambio;
  $('#text_covert').html('<button id="cerrar_d" type="button" class="close" aria-label="Close"><span aria-hidden="true">×</span></button>Se ha hecho la conversion de moneda: '+numeral(monto).format('0,0.00')+' '+moneda1+' / '+numeral(total_p).format('0,0.00')+' '+moneda2)
  $('#text_covert').show('slow');  
  $('#cerrar_d').click(function(){
    $('#text_covert').hide('slow');
  })
 return total_p 
}

if(moneda1 == 'CRC' && moneda2 == 'USD'){
  m_cambio = parseFloat(1/cambio);
  total_p = monto / m_cambio;
  $('#text_covert').html('Se ha hecho la conversion de moneda: '+numeral(monto).format('0,0.00')+' '+moneda1+' / '+numeral(total_p).format('0,0.00')+' '+moneda2)
  $('#text_covert').show('slow');  
  $('#cerrar_d').click(function(){
    $('#text_covert').hide('slow');
  })
 return total_p 
}

}



$('#pago1_dolar').keyup(function(){
  c_moneda = $('#moneda').val()
  if(c_moneda=='CRC'){
   c_moneda2 = 'USD'; 
  }
  if(c_moneda=='USD'){
   c_moneda2 = 'CRC'; 
  }  
  dolar = convertir_moneda(c_moneda2,c_moneda,$(this).val());
  $(this).attr('data-dolar',dolar);
})

$('#pago2_dolar').keyup(function(){  
  dolar = convertir_moneda(c_moneda2,c_moneda,$(this).val());
  $(this).attr('data-dolar',dolar);
})

$('#btn_pago1').click(function(){  
colones = $('#pago1_dolar').attr('data-dolar');
$('#pago1').val(colones);
calculos();
})


$('#btn_pago2').click(function(){  
colones = $('#pago2_dolar').attr('data-dolar');
$('#pago2').val(colones);
calculos();
})

$( document ).ready(function() {
    $(document).shortkeys({     
        'Shift+Q': function(){$('#pago1').focus()},
        'Shift+W': function(){$('#pago2').focus()},
        'Shift+Enter': function(){confirm_valida_tipo()}
      });      
});





function generar(obj){
$('#detalle').html('') ;
f = 1; 
$.each(obj.productos, function( index, value ) {
  if($('#moneda').val()=='USD'){
    d_suprice = '<td class="dolar">'+value.moneda+value.multicurrency_subprice+'</td>';
    d_total_ht = '<td class="dolar">'+value.moneda+value.multicurrency_total_ht+'</td>';
    d_total_ttc = '<td class="dolar">'+value.moneda+value.multicurrency_total_ttc+'</td>';
  }else{
    d_suprice = '';
    d_total_ht = '';
    d_total_ttc = '';
}
    $('#detalle').append(     
        '<tr class="fila" id="fila_'+f+'" data-comodin="'+value.extrafields.options_comodin+'" data-p_ref="'+value.ref+'" data-label="'+value.label+'" data-qty="'+value.qty+'" data-subprice="'+value.s_subprice+'"  data-multicurrency_subprice="'+value.s_tmulticurrency_subprice+'" data-remise_percent="'+value.s_remise_percent+'" data-tva_tx="'+value.s_tva_tx+'" data-total_ht="'+value.s_total_ht+'" data-total_ttc="'+value.s_total_ttc+'" data-indice="'+f+'" data-id="'+value.id+'" data-fk_product="'+value.fk_product+'">'+
        '<td><img src="'+value.image+'" width="50px"></td>'+        
        '<td>'+
        '<div class="form-check"><input data-id="'+value.id+'" data-fk_facture="'+obj.fk_facture+'" data-p_ref="'+value.ref+'" data-label="'+value.label+'" data-qty="'+value.qty+'" data-subprice="'+value.s_subprice+'"  data-multicurrency_subprice="'+value.s_tmulticurrency_subprice+'" data-remise_percent="'+value.s_remise_percent+'" data-tva_tx="'+value.s_tva_tx+'" data-total_ht="'+value.s_total_ht+'" data-total_ttc="'+value.s_total_ttc+'" data-indice="'+f+'" data-id="'+value.id+'" data-fk_product="'+value.fk_product+'" type="checkbox" class="check_product_'+value.id+' form-check-input check_product" id="check_product_'+value.id+'"><label class="form-check-label" for="check_product_'+value.id+'">'+
        '<a class="product_info text-secondary" data-rowid="'+value.id+'" data-fk_facture="'+obj.fk_facture+'">'+value.ref+'</a>'+
        '</label></div></td>'+       
        '<td>'+value.label+'</td>'+ 
        '<td>'+value.qty+'</td>'+
        d_suprice+          
        '<td>'+value.moneda+value.subprice+'</td>'+          
        '<td>%'+value.remise_percent+'</td>'+ 
        '<td>%'+value.tva_tx+'</td>'+
        d_total_ht+        
        d_total_ttc+           
        '<td>'+value.moneda+value.total_ht+'</td>'+        
        '<td>'+value.moneda+value.total_ttc+'</td>'+                       
        '</tr>'
  );
f++; 
})

$('#t_subtotal_d').text(obj.totales.s_t_multicurrency_total_ht);
$('#t_subtotal').text(obj.totales.t_total_ht);
$('#t_descuento_d').text(obj.totales.multicurrency_total_descuento);
$('#t_descuento').text(obj.totales.total_descuento);
$('#t_iva').text(obj.totales.t_total_tva);
$('#t_total_d').text(obj.totales.s_t_multicurrency_total_ttc);
$('#t_total').text(obj.totales.t_total_ttc);


$('#t_total_d').attr('data-multicurrency_total_ttc',obj.totales.s_t_multicurrency_total_ttc);
if($('#moneda').val()=='CRC'){
$('#t_total').attr('data-total_ttc',obj.totales.s_t_total_ttc);
$('#g_total').attr('data-total_ttc',obj.totales.s_t_total_ttc);
$('#t_subtotal').attr('data-total_ht',obj.totales.s_t_total_ht);
$('#g_total').text(obj.totales.t_total_ttc);
}else{
$('#t_total').attr('data-total_ttc',obj.totales.s_t_multicurrency_total_ttc);
$('#g_total').attr('data-total_ttc',obj.totales.s_t_multicurrency_total_ttc);
$('#t_subtotal').attr('data-total_ht',obj.totales.s_t_multicurrency_total_ht);
$('#g_total').text(obj.totales.t_multicurrency_total_ttc);  
}
$(".fila").click(function(){
$('.tr_selected').removeClass('tr_selected stylish-color text-white') ;
$(this).addClass('tr_selected stylish-color text-white') ;
});

$('#tipo_doc').val(obj.type).trigger('change');
$('#fk_facture_source').val(obj.fk_facture_source); 
$('#fk_facture_source_num').val(obj.fk_facture_source_num); 
$('#fk_facture_num').val(obj.fk_facture_num); 
$('.product_info').click(modal_product_info);

}





  $(".fila").click(function(){
      $('.tr_selected').removeClass('tr_selected stylish-color text-white') ;
      $(this).addClass('tr_selected stylish-color text-white') ;
  });
 

$('#separar').click(confirm_separar);
$('#fusionar').click(confirm_fusionar);
  
function confirm_separar(){
  //CONFIRMACION DE EDITAR PTODUCTO
Swal.fire({
  html: '',
  title: 'Confirma la separacion de lineas en un factura nueva?',
  icon: 'warning',
  showCancelButton: true,
  confirmButtonColor: '#3085d6',
  cancelButtonColor: '#d33',
  confirmButtonText: 'Aplicar'
  }).then((result) => {
  if (result.value) {
    separar()
  }
  })
  //FIN DE CONFIRMACION DE EDITAR PRODUCTO
}

function confirm_fusionar(){
  //CONFIRMACION DE EDITAR PTODUCTO
Swal.fire({
  html: '',
  title: 'Confirma la combinacion de facturas?',
  icon: 'warning',
  showCancelButton: true,
  confirmButtonColor: '#3085d6',
  cancelButtonColor: '#d33',
  confirmButtonText: 'Aplicar'
  }).then((result) => {
  if (result.value) {
    fusionar()
  }
  })
  //FIN DE CONFIRMACION DE EDITAR PRODUCTO
}


function separar(){
lineas = [];
$( ".check_product" ).each(function( index ) {
  if($(this).prop('checked')){
    lineas.push({"id":$( this ).data('id'),"fk_facture":$( this ).data('fk_facture')});  
   }
});

if(lineas.length == 0){
  Swal.fire({
  type: 'error',
  title: 'No hay lineas seleccionadas',
  showConfirmButton: false,
  timer: 3000
})  
return ;
};
fk_facture = $('#fk_facture').val();
 //envio por ajax
 $.ajax({
  type: "POST",
  url: "ajax/add_line_restaurant.php",
  data: {
    lineas:lineas,
    fk_facture:fk_facture,
    action:'separar'
  },
  dataType: "json",
  success: function(resp) {
    generar(resp)
  $('#barra_c').hide();
  }
  
  })
  //envio por ajax

}


function fusionar(){
  facturas = [];
  $( ".check_fac" ).each(function( index ) {
    if($(this).prop('checked')){
      facturas.push({"fk_facture":$( this ).data('fk_facture')});  
     }
  });
  
  if(facturas.length == 0){
    Swal.fire({
    type: 'error',
    title: 'No hay facturas seleccionadas',
    showConfirmButton: false,
    timer: 3000
  })  
  return ;
  };
   //envio por ajax
   $.ajax({
    type: "POST",
    url: "ajax/add_line_restaurant.php",
    data: {
      facturas:facturas,
      action:'fusionar'
    },
    dataType: "json",
    success: function(resp) {
    save_fac_lits()
    $('#barra_c').hide();
    }
    
    })
    //envio por ajax
  
  }


function update_confirm(){
  tr_s = $('.tr_selected').length;
  fk_product = $('.tr_selected').attr('data-fk_product');
  id = $('.tr_selected').attr('data-id');
  descuento = $('.tr_selected').attr('data-remise_percent');
  comodin = $('.tr_selected').attr('data-comodin');
  cantidad = $('.tr_selected').attr('data-qty');
  tva_tx = parseInt($('.tr_selected').attr('data-tva_tx'));
  titulo = $('.tr_selected').attr('data-p_ref')+' - '+$('.tr_selected').attr('data-label');
  moneda= $('#moneda').val();
  if(moneda=='USD'){
  precio = $('.tr_selected').attr('data-multicurrency_subprice');  
  }

  if(moneda=='CRC'){
    precio = $('.tr_selected').attr('data-subprice'); 
  } 
  rates = '';
  rates += '<select class="form-control" name="edit_iva" id="edit_iva">';
  $.each(vatrates, function( index, value ) {
  if(value.txtva == tva_tx){sele = 'selected';}else{sele = '';}
  
  rates += '<option value="'+value.txtva+'" '+sele+'>'+value.label+'</option>';
  })
  rates += '</select>';



  if(tr_s > 0){ 
    console.log(comodin,permisos.pos.edit_product);
    if(permisos.pos.edit_product==1){
     editar = ' style="display:grid"';
     discount = ' style="display:grid"';
     iva = ' style="display:grid"';
     if(permisos.pos.discount==1){
       discount = ' style="display:grid"';
     }else{discount = ' style="display:none"';}

     if(permisos.pos.iva==1){
       iva = ' style="display:grid"';
     }else{iva = ' style="display:none"';}     

    }else{
      editar = ' style="display:none"';

      if(permisos.pos.discount==1){
      discount = ' style="display:grid"';
      }else{discount = ' style="display:none"';}
      
      if(permisos.pos.iva==1){
        iva = ' style="display:grid"';
      }else{iva = ' style="display:none"';}

    }

    if(comodin==1){
     editar = ' style="display:grid"';
     discount = ' style="display:grid"';
     iva = ' style="display:grid"';

     if(permisos.pos.discount==1){
       discount = ' style="display:grid"';
     }else{discount = ' style="display:none"';}

     if(permisos.pos.iva==1){
       iva = ' style="display:grid"';
     }else{iva = ' style="display:none"';}     

     
    }
    

 
 html_d = '<table class="table table-bordered table-striped" width="100%">';   
 html_d += '<tbody>' ;
 html_d +=  '<tr'+editar+' class="btn-elegant"><td scope="col" class="text-center">Precio</td></tr>';    
 html_d +=  '<tr'+editar+'><td scope="col" class="text-center"><input style="width:200px;float:left" class="form-control" type="text" name="edit_precio" id="edit_precio" value="'+precio+'">';
 html_d +=  ' <select id="edit_price_base" class="form-control" style="width:200px;float:left"><option value="HT">SIN IVA</option><option value="TTC">CON IVA</option></select>'; 
 html_d +=  ' </td></tr>';
 html_d +=  ' </td></tr>';
 html_d +=  '<tr'+discount+' class="btn-elegant"><td scope="col" class="text-center">Descuento</td></tr>';
 html_d +=  '<tr'+discount+'><td scope="col" class="text-center"><input class="form-control" type="text" name="edit_descuento" id="edit_descuento" value="'+descuento+'"></td></tr>';
 html_d +=  '<tr class="btn-elegant"><td scope="col" class="text-center">Cantidad</td></tr>';       
 html_d +=  '<tr><td scope="col" class="text-center"><input class="form-control" type="text" name="edit_cantidad" id="edit_cantidad" value="'+cantidad+'"></td></tr>';
 html_d +=  '<tr'+iva+' class="btn-elegant"><td scope="col" class="text-center">I.V.A</td></tr>';
 html_d +=  '<tr'+iva+'><td scope="col" class="text-center">'+rates+'</td></tr>';        
 html_d +=  '</tbody>';      
 html_d +=  '</table>';
//CONFIRMACION DE EDITAR PTODUCTO
Swal.fire({
html: html_d,
title: titulo,
icon: 'warning',
showCancelButton: true,
confirmButtonColor: '#3085d6',
cancelButtonColor: '#d33',
confirmButtonText: 'Aplicar'
}).then((result) => {
if (result.value) {
id = $('.tr_selected').attr('data-id');
descuento = $('#edit_descuento').val();
precio = $('#edit_precio').val();
cantidad = $('#edit_cantidad').val();
tva_tx = $('#edit_iva').val();  
price_base = $('#edit_price_base').val();  
check_desc = limitar_descuento(descuento,'#edit_descuento');
if(check_desc == 0){
    Swal.fire({
        type: 'error',
        title: 'El descuento maximo esta limitado',
        showConfirmButton: false,
        timer: 3000
      })  
return
}
update_line(id,descuento,cantidad,precio,tva_tx,price_base);
}
})
//FIN DE CONFIRMACION DE EDITAR PRODUCTO

  }
  
}

//abriendo modal editar
 $(document).keydown(function(e) { 
   if(e.keyCode==45 || e.keyCode==96){ 
      update_confirm()
    } 
 
  });

//fin abriendo modal editar


$(document).keydown(function(e) {
var hasFocus = $('#cantidad').is(':focus');
if(hasFocus){
 if(e.keyCode==13){

  add_line();
}

 }
  });

$(document).keydown(function(e) {
 if(e.keyCode==39){
$('#cantidad').focus();
 }
  });


$(document).keydown(function(e) {
 if(e.keyCode==39){
$('#cantidad').focus();
 }
  });

$(document).keydown(function(e) {
 if(e.keyCode==37){
$('#select_product').focus();
 }
  });



//navegnado en la tabla
/*   $(document).keydown(function(e) {
    
    if(e.keyCode==38){
    tr_t = $('#detalle tr').length;
    tr_s = $('.tr_selected').length;
         id1 = parseInt($('.tr_selected').attr('data-indice'));         
         id2 = parseInt(id1 - 1);
         if(id2==0){id2=1}
         if(tr_s==0){id2=tr_t}
         $('.tr_selected').removeClass('tr_selected bg-primary text-white') ;
         $('#fila_'+id2).addClass('tr_selected bg-primary text-white') ;
      console.log(id2);   
    }
});

$(document).keydown(function(e) {
  if(e.keyCode==40){
  tr_t = $('#detalle tr').length;
  tr_s = $('.tr_selected').length;
       id1 = parseInt($('.tr_selected').attr('data-indice'));         
       id2 = parseInt(id1 + 1);
       if(id2 >= tr_t){id2=tr_t}
       if(tr_s==0){id2=1}
       $('.tr_selected').removeClass('tr_selected bg-primary text-white') ;
       $('#fila_'+id2).addClass('tr_selected bg-primary text-white') ;
       console.log(tr_t,id2); 
  }
}); */
// fin navegnado en la tabla


function delete_confirm(){
  tr_s = $('.tr_selected').length;
  if(tr_s > 0){  
titulo = $('.tr_selected').attr('data-p_ref')+' - '+$('.tr_selected').attr('data-label');       
//confirmacion
Swal.fire({
title: 'Esta seguro?',
text: "Eliminar "+titulo,
icon: 'warning',
showCancelButton: true,
confirmButtonColor: '#3085d6',
cancelButtonColor: '#d33',
confirmButtonText: 'Eliminar'
}).then((result) => {
if (result.value) {
delete_line();
}
})
//fin de confirmacion
}  
};


$(document).keydown(function(e) {
  if(e.keyCode==46){
  delete_confirm();
  }

});

//acciones de botones
$('#recargar').click(function(){
  location.reload(); 
});

$('#insertar').click(function(){
/*   fk_product = $('#fk_product').val();
  cantidad = parseFloat($('#cantidad').val());
  descuento = parseFloat($('#descuento').val());
  tipo_desc = $('#tipo_desc').val();
  price_level = $('#price_level').val();
  moneda = $('#moneda').val();
  fk_soc = $('#fk_soc').val();
  tipo = $('#tipo').val();
  fk_facture = $('#fk_facture').val();
  select_product = $('#select_product').val();
  max_discount = parseFloat($("#max_discount").val());
  entrepot_stock = parseFloat($("#entrepot_stock").val());
  stock_negativo = parseInt($("#stock_negativo").val());
  limitar_login = parseInt($("#limitar_login").val());
 if(fk_product=='' || fk_product == 0){
    Swal.fire({
        type: 'error',
        title: 'No se a seleccionado un producto',
        showConfirmButton: false,
        timer: 1000
        
      })
return;
} 
if(cantidad <= 0 || cantidad==''){
  Swal.fire({
      type: 'error',
      title: 'La cantidad minima es 1',
      showConfirmButton: false,
      timer: 1000
      
    })
return;
}

if(cantidad <= 0 || cantidad==''){
  Swal.fire({
      type: 'error',
      title: 'La cantidad minima es 1',
      showConfirmButton: false,
      timer: 1000
      
    })
return;
}


if(stock_negativo==0){

if(entrepot_stock <= 0 || cantidad > entrepot_stock){
if(limitar_login==1){
  $('#l_action').val('line');
 show_login('No hay existencias en la bodega'); 
 }else{
  Swal.fire({
      type: 'error',
      title: 'No hay existencias en la bodega',
      showConfirmButton: false,
      timer: 1000
      
    })
    return;
 }
     
}else{
add_line();  
}

}


if(stock_negativo==1){
add_line();  
} */

add_line();

  
});



$('#eliminar').click(function(){
tr_s = $('.tr_selected').length;
if(tr_s > 0){   
  delete_confirm();
}  
});

$('#editar').click(function(){
  tr_s = $('.tr_selected').length;
  if(tr_s > 0){    
    update_confirm(); 
  } 
});

$('#recuperar').click(function(){
  tr_s = $('.tr_selected').length;
  save_fac_lits();
});

$('#dividir').click(function(){
if($('#fk_facture').val() > 0){
  $('#modal_dividir').modal('show');  
}
});

$('#guardar').click(function(){
  tr_s = $('.fila').length;
  if(tr_s > 0){   
  save_fac_confirm();
  }
});

$('#descuento_global').click(function(){
  tr_s = $('.fila').length;
  if(tr_s > 0){   
  set_descuento_global();
  }
});


$('#calculadora').click(function(){
  $('#exampleModalCenter').modal('show');
})


$('#add_cliente').click(function(){
  $('#modalContactForm').modal('show')
})

$('#rewardp').click(function(){
  $('#reward_modal').modal('show')
})

$('#cierre_caja').click(function(){
  $('#cierre_modal').modal('show')
})


function get_lineas(){
  $('#barra_c').show(); 
  fk_facture = $('#fk_facture').val();
  fk_soc = $('#fk_soc').val();
//envio por ajax
$.ajax({
    type: "POST",
    url: "ajax/cash_detalle.php",
    data: {
      fk_soc:fk_soc,
      fk_facture:fk_facture,
      action:'get_lineas'
    },
    dataType: "json",
    success: function(resp) {
    generar(resp)
    $('#barra_c').hide();
    }
    
    })
    //envio por ajax
}

$('.product_info').click(modal_product_info);
function modal_product_info(){
  $('#barra_c').show(); 
  fk_facture = $(this).attr('data-fk_facture');
  fk_soc = $('#fk_soc').val();
  rowid = $(this).attr('data-rowid');
  $('#body_modal_product_info').html('');  
//envio por ajax
$.ajax({
    type: "POST",
    url: "ajax/cash_detalle.php",
    data: {
      fk_soc:fk_soc,
      fk_facture:fk_facture,
      rowid:rowid,
      action:'modal_product_info'
    },
    dataType: "json",
    success: function(resp) {
      $('#barra_c').hide();  
    $('#body_modal_product_info').html(
      '<table>'+
      '<tr>'+
      '<td class="font-weight-bold">Id linea:</td>'+
      '<td>'+resp.productos[0].id+'</td>'+      
      '</tr>'+

      '<tr>'+
      '<td class="font-weight-bold">Id producto:</td>'+
      '<td>'+resp.productos[0].fk_product+'</td>'+      
      '</tr>'+
   
      '<tr>'+
      '<td class="font-weight-bold">Precio unitario:</td>'+
      '<td>'+resp.productos[0].subprice+'</td>'+      
      '</tr>'+
      
      '<tr>'+     
      '<td class="font-weight-bold">Precio unitario(I.V.A):</td>'+
      '<td>'+resp.productos[0].subprice_ttc+'</td>'+      
      '</tr>'+  

      '<tr>'+     
      '<td class="font-weight-bold">Descuento unitario:</td>'+
      '<td>'+resp.productos[0].descuento_ht+'</td>'+      
      '</tr>'+ 

      '<tr>'+        
      '<td class="font-weight-bold">Cantidad:</td>'+ 
      '<td>'+resp.productos[0].qty+'</td>'+      
      '</tr>'+  
      '<tr>'+     

      '<td class="font-weight-bold">Descuento total:</td>'+
      '<td>'+resp.productos[0].descuento+'</td>'+       
      '</tr>'+   

      '<td class="font-weight-bold">Sutotal:</td>'+
      '<td>'+resp.productos[0].total_ht+'</td>'+       
      '</tr>'+ 

      '<td class="font-weight-bold">Impuesto:</td>'+
      '<td>'+resp.productos[0].total_tva+'</td>'+       
      '</tr>'+ 
      
      '</tr>'+               
      '<td class="font-weight-bold">Total:</td>'+
      '<td>'+resp.productos[0].total_ttc+'</td>'+          
      '</tr>'+ 
      '</tr>'+
      '<tr>'+                 
      '</table>'
      );    
    $('#modal_product_info').modal('show');
    }
    
    })
    //envio por ajax      
}




  
//fin acciones de botones


function boton_tipo(valor,nombre,color){
$('#boton_validar').html('<button class="btn btn-'+color+' btn-lg btn-block btn-lg btn-block" data-tipo="'+valor+'" id="validar_"'+valor+'>VALIDAR '+nombre+'</button>')
$('#tipo').val($('#tipo_doc').val());

if($('#tipo').val() == 3){
  $('#tr_dolar_apartado').show();  
  }else{
  $('#tr_dolar_apartado').hide();   
  }

}

$('#tipo_doc').change(function(){
if($(this).val()==1){
boton_tipo('ticket','TICKET','success')
  } 

if($(this).val()==0){
boton_tipo('factura','FACTURA','primary')
  }
  
if($(this).val()==2){
boton_tipo('ndc','NDC','danger')
  }

if($(this).val()==3){
boton_tipo('apartado','APARTADO','secondary')
  }

if($(this).val()==4){
boton_tipo('cotizacion','COTIZACION','warning')
  }
    
})

 if($('#tipo').val()==1){
  $('#boton_validar').html('<button class="btn btn-success btn-lg btn-block btn-lg btn-block" data-tipo="ticket" id="validar_ticket">VALIDAR TICKET</button>');
  }

  
if($('#tipo').val()==0){

$('#boton_validar').html('<button class="btn btn-primary btn-lg btn-block btn-lg btn-block" data-tipo="factura" id="validar_factura">VALIDAR FACTURA</button>');
  }

  
if($('#tipo').val()==2){
$('#boton_validar').html('<button class="btn btn-danger btn-lg btn-block btn-lg btn-block" data-tipo="ndc" id="validar_ndc">VALIDAR NDC</button>');

  }

if($('#tipo').val()==3){

$('#boton_validar').html('<button class="btn btn-secondary btn-lg btn-block btn-lg btn-block" data-tipo="val" id="validar_apartado">VALIDAR APARTADO</button>');
  }

if($('#tipo').val()==4){

$('#boton_validar').html('<button class="btn btn-warning btn-lg btn-block btn-lg btn-block" data-tipo="cotizacion" id="validar_cotizacion">VALIDAR COTIZACION</button>');
  } 



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
  $.each(resp.metodos, function( index, value ) {
    $('#metodo'+num).append('<option data-metodo1_id="'+value.id+'" value="'+value.code+'">'+value.label+'</option>');
  })
  //$('#barra_c').hide();
  }
  
  })
  //envio por ajax    
});



$('#boton_validar').click(function(){
  tipo = $('#tipo').val();
  limitar_login = $('#limitar_login').val();
  cliente = $('#fk_soc').val();
  contado = $('#contado').val();
  total = parseFloat($('#g_total').attr('data-total_ttc'));
  credito_disponible = $('#credito_disponible').val();
  pago1 = parseFloat($('#pago1').val());
  pago2 = parseFloat($('#pago2').val());
  pagado = pago1 + pago2 ;
  vuelto = parseFloat($('#g_vuelto').attr('data-vuelto'));
  console.log(vuelto,contado,cliente);
  
  if(pagado == 0 && cliente == contado && tipo < 2){
    Swal.fire({
      type: 'error',
      title: 'No se puede Validar una factura Sin pago',
      showConfirmButton: false,
      timer: 3000
      
    })
    return
  }
  if(vuelto > 0 && cliente == contado){
    Swal.fire({
      type: 'error',
      title: 'No se puede Validar una factura con saldo pendiente',
      showConfirmButton: false,
      timer: 3000
      
    })
    return
  }

  
  tipo = parseInt($('#tipo').val());
  if(limitar_login ==1 && tipo !=3  && cliente != contado && vuelto >= 0 && pagado <=0 && credito_disponible < total){
    limitar = limitar_factura();
    console.log(limitar);
   if(limitar){
   $('#l_action').val('client');  
   show_login('Limite de credito sobre pasado');
   
   }else{
   confirm_valida_tipo();   
   }
     
  }else{
  confirm_valida_tipo();  
  }

});


function confirm_valida_tipo(){
  tipo = $('#tipo').val();

    if(tipo==1){doc = 'TICKET';color = 'success'; } 
    if(tipo==0){doc = 'FACTURA';color = 'primary'; } 
    if(tipo==2){doc = 'NDC';color = 'danger'; } 
    if(tipo==3){doc = 'APARTADO';color = 'secondary'; } 
    if(tipo==4){doc = 'COTIZACION';color = 'warning'; } 
    if(tipo !=2){
    user = $('#div_conten_user').html(); 
    tit = 'Autor de la venta';
    }else{
    user = 
    '<select class="browser-default custom-select" id="CodigoReferencia" name="CodigoReferencia">'+
    '<option value="0" selected="selected">Seleccione una opción</option>'+
    '<option value="1">Anula Documento de referencia</option>'+
    '<option value="3">Corrige Monto</option></select>';
    tit = 'Motivo de modificar factura '+$('#fk_facture_source_num').val()+'';
   
    }
    
    //$('#div_conten_user').remove();
  Swal.fire({
    title: 'VALIDAR '+doc+'?',
    html: '<br>'+tit+'<div id="div_user">'+user+'</div>',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Validar'
  }).then((result) => {     
    if (result.value) {
      if(tipo !=2){
      vendedor = $('#div_user').find('.log_user').val();  
      }else{
      vendedor = $('#div_user').find('#CodigoReferencia').val();    
      }
if($('#div_user').find('#CodigoReferencia').val() == 0){
  Swal.fire({
    type: 'error',
    title: 'Seleccione el motivo de la NDC',
    showConfirmButton: false,
    timer: 1000
    
  })
return;  
}      
      valida_tipo(vendedor);
    }
  }) 
}

  

  function valida_tipo(vendedor){
    $('#barra_c').show();  
  fk_facture = $('#fk_facture').val();
  fk_cierre = $('#fk_cierre').val();  
  moneda = $('#moneda').val();
  fk_soc = $('#fk_soc').val();
  forme_juridique_code = $("#forme_juridique_code").val();
  idprof1 = $("#idprof1").val();
  fk_vendedor = vendedor;
  default_vat_code = $('#default_vat_code').val();
  actividad = $('#actividad').val();
  vaucher_num = $('#vaucher_num').val();
  public_note = $('#public_note').val();
  options_sucursal = $('#options_sucursal').val();
  options_exoneracion = $('#options_exoneracion').val();
  options_orden_salida = $('#options_orden_salida').val();
  tipo = $('#tipo').val();
  total = $('#g_total').attr('data-total_ttc'); 
  pago1 = $('#pago1').val();
  pago2 = $('#pago2').val();
  pago3 = $('#pago3').val();
  total = $('#g_total').attr('data-total_ttc');   
  banco1 = $('#banco1').val();
  banco2 = $('#banco2').val();
  banco3 = $('#banco3').val();  
  metodo1 = $('#metodo1').val();
  metodo2 = $('#metodo2').val();
  metodo3 = $('#metodo3').val();  
  metodo1_id = $('#metodo1').find(':selected').attr('data-metodo1_id');
  metodo2_id = $('#metodo2').find(':selected').attr('data-metodo2_id');  
  metodo3_id = $('#metodo3').find(':selected').attr('data-metodo3_id');    
  pago1_dolar = $('#pago1_dolar').val();
  pago2_dolar = $('#pago2_dolar').val();
  pago3_dolar = $('#pago3_dolar').val();  
  rewards = $('#rew_points').val();
  rewards_points = $('#des_puntos').val();
  vuelto = $('#g_vuelto').attr('data-vuelto');  
  b_moneda1 = $('#banco1').find(':selected').attr('data-currency_code');
  b_moneda2 = $('#banco2').find(':selected').attr('data-currency_code'); 
  b_moneda3 = $('#banco3').find(':selected').attr('data-currency_code');   
  multicurrency_tx = $('#multicurrency_tx').val();
  ref_client = $('#ref_client').val();
  fk_facture_source = $('#fk_facture_source').val();
  feng_codref = vendedor;
  ndc_descuentos = $('#ndc_desc').val();
  
if(tipo == 0){

if(forme_juridique_code == 0 || forme_juridique_code == '' || forme_juridique_code == '' || idprof1 == ''){
  Swal.fire({
    type: 'error',
    title: 'Este cliente no tiene correctamente establecida la cedula',
    showConfirmButton: false,
    timer: 3000
    
  })
return; 
}




}


//envio por ajax
 $.ajax({
  type: "POST",
  url: "ajax/facturacion.php",
  data: {
    fk_soc:fk_soc,
    fk_facture:fk_facture,
    fk_cierre:fk_cierre,
    fk_facture_source:fk_facture_source,
    ref_client:ref_client,
    moneda:moneda,
    fk_vendedor:fk_vendedor,
    tipo:tipo,
    vaucher_num:vaucher_num,
    public_note:public_note,
    multicurrency_tx,
    default_vat_code:default_vat_code,
    actividad:actividad, 
    options_sucursal:options_sucursal,
    options_orden_salida:options_orden_salida,    
    feng_codref:feng_codref,
    ndc_descuentos:ndc_descuentos,
    options_exoneracion:options_exoneracion,
    tipo:tipo,
    banco1:banco1,
    banco2:banco2,
    banco3:banco3,    
    metodo1:metodo1,
    metodo2:metodo2,
    metodo3:metodo3,    
    metodo1_id:metodo1_id,
    metodo2_id:metodo2_id, 
    metodo3_id:metodo3_id,            
    b_moneda1:b_moneda1,
    b_moneda2:b_moneda2,
    b_moneda3:b_moneda3,    
    pago1_dolar:pago1_dolar,
    pago2_dolar:pago2_dolar, 
    pago3_dolar:pago3_dolar,        
    total:total,
    pago1:pago1,
    pago2:pago2,
    pago3:pago3,    
    vuelto:vuelto,
    rewards:rewards,
    rewards_points:rewards_points,
    action:'validar_doc'
  },
  dataType: "json",
  success: function(resp) {
  $('#barra_c').hide();
    if(parseInt(resp.error) < 0 ){
      Swal.fire(
        'Error!',
        resp.messaje,
        'error'
      )  
      return
    }   

  if(resp.tipo < 4){
  window.location.replace('doc_sucess.php?facid='+resp.id+'&tipo='+resp.tipo+'&id='+resp.id+'');   
  }else{
  window.location.replace('doc_sucess_propal.php?id='+resp.id+'');  
  }


  }
  
  }) 
  //envio por ajax

  }





 
//funcionalidad listado productos tactil
$('#l_productos').click(function(){
  $('#product_modal').modal('show'); 
get_categoria();
  
})


$('#back_cate').click(get_categoria);


function get_categoria(){
 //envio por ajax
$.ajax({
  type: "POST",
  url: "ajax/categorias.php",
  data: {
 
    action:'getCategories'
  },
  dataType: "json",
  success: function(resp) {
    $('#ul_categorie').html('');
  $.each(resp, function( index, value ) {
  $('#ul_categorie').append(
    '<li id="cat_'+value.id+'" data-id="'+value.id+'" data-levels="'+value.levels+'"  data-fk_parent="'+value.fk_parent+'" class="cat"><buttontype="button" class="btn bg-dark btn-rounded">'+value.label+'</button>'+
  '</li>');

  })

  $('.cat').click(get_prouct_cat);

  $('#barra_c').hide();
  
  }
  
  })
  //envio por ajax 
}



function get_prouct_cat(){
    $('.cat').find('.selected').addClass('bg-dark')
    $('.cat').find('.selected').removeClass('bg-primary selected')
    $(this).find('.btn').removeClass('bg-dark')
    $(this).find('.btn').addClass('bg-primary selected')
    fk_parent = $(this).attr('data-fk_parent');
    levels = $(this).attr('data-levels');
    id = $(this).attr('data-id');
if(levels > 0){
  get_cat_prent(id);  
    }
    
      data = {cat_id:$(this).attr('data-id'),cat_fk_parent:fk_parent}
      get_products(data);      
}



function get_cat_prent(fk_parent){
 //envio por ajax
 $.ajax({
  type: "POST",
  url: "ajax/categorias.php",
  data: {   action:'getCategories',parentcategory:fk_parent},
  dataType: "json",
  success: function(resp) {
    $('#ul_categorie').html('');
    $.each(resp, function( index, value ) {
      $('#ul_categorie').append(
        '<li id="cat_'+value.id+'" data-id="'+value.id+'" data-levels="'+value.levels+'" data-fk_parent="'+value.fk_parent+'" class="cat"><buttontype="button" class="btn bg-dark btn-rounded">'+value.label+'</button>'+
      '</li>');   
      }) 
      $('.cat').click(get_prouct_cat);
      
   
  }
  })
  //envio por ajax   
 


}




function get_atributes(fk_product){

    //envio por ajax
    $.ajax({
      type: "POST",
      url: "ajax/productos.php",
      data: {  
        action:'get_atributes',
        m : $("#moneda").val(),
        l : $("#price_level").val(),
        fk_parent : fk_product
      },
      dataType: "json",
      success: function(resp) { 
        var a = 1;
        $("#variant_body").html('');
        $.each(resp, function( index, value ) {
         $.each(value.attributes, function( index2, value2 ) {
              $("#variant_body").append(
                '<div class="card">'+
                '<div class="card-header btn-elegant">'+
                  index2+
                '</div>'+
                '<ul class="list-group list-group-flush" id="ul_attr_'+a+'">'+
                '</ul>'+
              '</div><br>'
                ) ;

                $.each(value2, function( index3, value3 ) {
                 $("#ul_attr_"+a+"").append('<li class="list-group-item">'+
                '<div class="custom-control custom-radio">'+
                '<input data-attr_id="'+value3.id_attr+'" data-attr_value="'+value3.id_value+'" type="radio" class="variant custom-control-input" id="radio_'+value3.id_value+'" name="attr_value_'+a+'" value="'+value3.id_value+'">'+
                '<label class="custom-control-label" for="radio_'+value3.id_value+'">'+index3+'</label>'+
                '</div>'+              
                 '</li>')
                  })   
                
                a++;

              })

        })

       }
            })
      //envio por ajax    
}


$('#aplicar_variant').click(aplicar_variant);

function aplicar_variant(){
cant_var = $(".variant:checked").length;  
cant_check = $("#cant_attributes").val();
if(cant_check < cant_var){
  alert('Debe seleccionar una combinación');
  return
}
comb ={};
$.each($(".variant:checked"), function( index, value ) {
  comb[$(this).attr('data-attr_id')]=$(this).val();
   })

fk_product= $("#fk_product").val();
 //envio por ajax
 $.ajax({
  type: "POST",
  url: "ajax/productos.php",
  data: {  
    action:'get_variant',
    m : $("#moneda").val(),
    l : $("#price_level").val(), 
    c : $("#cantidad").val(),
    fk_parent : fk_product,
    attributes : comb
  },
  dataType: "json",
  success: function(resp) { 
    if(resp==-1){
      alert('La combinacion seleccionada no existe');
    }
    $("#select_product").val(resp[0].text).trigger('change');
    $("#fk_product").val(resp[0].id);
    $("#entrepot_stock").val(resp[0].Stock);
    $("#max_discount").val(resp[0].options_descuento);  
    $("#cant_attributes").val(resp[0].cant_attributes); 
    if($("#cantidad").val().length > 0){
    $("#select_product").focus();
    }else{
    $("#cantidad").val(1); 
    $("#select_product").focus();
 
  } 
$("#variantModalCenter").modal('hide');
add_line();

  
   }
        })
  //envio por ajax    
}


function get_products(data){
  $("#product_lista").html('');
  var price_level = $("#price_level").val();
  moneda = $("#moneda").val();
  if(price_level==0){price_level=1}
  cantidad = parseFloat($("#cantidad").val());
  
    //envio por ajax
    $.ajax({
      type: "POST",
      url: "ajax/productos.php",
      data: {
     
        action:'get_categorias',
        m : $("#moneda").val(),
        l : $("#price_level").val(),
        cat_id : data.cat_id

      },
      dataType: "json",
      success: function(resp) { 
        
        $.each(resp, function( index, value ) {
              $("#product_lista").append(
                '<li class="p_lista nav-item" data-fk_product="'+value.id+'" data-entrepot_stock="'+value.Stock+'" data-max_discount="'+value.options_descuento+'">'+
                '<div  class="border border-danger card card_productos scrollbar scrollbar-primary">'+
                ' <div class="view overlay">'+
                    '<center><img class="card-img-top" src="'+value.image+'"'+
                      ' width="90%"></center>'+
                    '<a>'+
                      '<div class="mask rgba-white-slight"></div>'+
                    '</a>'+
                  '</div>'+
                   '<div class="card-body elegant-color white-text rounded-bottom force-overflow">'+
                    '<h6 class="card-title text-center">'+value.label+'</h6>'+
                    '<hr class="hr-light">'+
                    '<div class="card-title">Código: '+value.ref+'<br>'+ 
                    'Stock: '+value.Stock+'<br>'+                       
                    'Descuento: '+numeral(value.extrafields.options_descuento).format('0,0.00')+'<br>'+ 
                    '</div>'+                             
                  '</div>'+         
                '</div>'+
                '</li>'
                )

      
        })

        $(".p_lista").click(function(){
          $("#fk_product").val($(this).attr('data-fk_product'));
          $("#entrepot_stock").val($(this).attr('data-entrepot_stock'));
          $("#max_discount").val($(this).attr('data-max_discount'));           
          $("#cantidad").val(1);
          add_line();
          $("#product_modal").modal('hide');
          });             
      $('#barra_c').hide();
       }
            })
      //envio por ajax  
      
}
//fin funcionalidad listado productos tactil

$('#tipo_cierre').change(function(){
if($(this).val()==0){
  $('#modo_arqueo').show();
  $('#modo_cierre').hide();
}
if($(this).val()==1){
  $('#modo_cierre').show();
  $('#modo_arqueo').hide();
}
})
  

//AUTOCOMPLETE PRODUCTOS TACTIL

$("#b_product").keyup(get_producto);
$("#b_product").blur(get_producto);
function get_producto(){
  if($(this).val().length > 2){
  $("#product_lista").html('');
  var price_level = $("#price_level").val();
  moneda = $("#moneda").val();
  if(price_level==0){price_level=1}
  cantidad = parseFloat($("#cantidad").val());
  filter = $(this).val();
  
    //envio por ajax
    $.ajax({
      type: "POST",
      url: "ajax/productos.php",
      data: {
        c:1,
        l:price_level,
        m:moneda,
        q:filter,
        action:'get_product'
      },
      dataType: "json",
      success: function(resp) { 
        
        $.each(resp, function( index, value ) {
          
              $("#product_lista").append(
                '<li class="p_lista nav-item" data-fk_product="'+value.id+'" data-entrepot_stock="'+value.Stock+'"  data-max_discount="'+value.options_descuento+'">'+
                '<div  class="border border-danger card card_productos scrollbar scrollbar-primary">'+
                ' <div class="view overlay">'+
                    '<center><img class="card-img-top" src="'+value.image.url+'"'+
                      ' width="90%"></center>'+
                    '<a>'+
                      '<div class="mask rgba-white-slight"></div>'+
                    '</a>'+
                  '</div>'+
                   '<div class="card-body elegant-color white-text rounded-bottom force-overflow">'+
                    '<h6 class="card-title text-center">'+value.label+'</h6>'+
                    '<hr class="hr-light">'+
                    '<div class="card-title">Código: '+value.ref+'<br>'+ 
                    'Stock: '+value.Stock+'<br>'+                       
                    'Descuento: '+numeral(value.extrafields.options_descuento).format('0,0.00')+'<br>'+ 
                    '</div>'+                             
                  '</div>'+         
                '</div>'+
                '</li>'
                )

        })
             
        $(".p_lista").click(function(){
        $("#fk_product").val($(this).attr('data-fk_product'));
        $("#entrepot_stock").val($(this).attr('data-entrepot_stock'));
        $("#max_discount").val($(this).attr('data-max_discount'));
        $("#cantidad").val(1);  
        add_line();
        $("#product_modal").modal('hide');
        });


      $('#barra_c').hide();
       }
      
      })
      //envio por ajax  
    }     
}
//FIN AUTOCOMPLETE PRODUCTOS TACTIL



$('#nota_credito').click(function(){
$('#modal_ndc').modal('show');
})



$('#get_ndc_fac').click(function(){
fk_soc = $('#fk_soc').val();
nombre = $('#nombre_c').val();
fecha = $('#fecha').val();
producto = $('#producto_c').val();
moneda = $('#moneda').val();
col_busc = $('#col_busc').val();
col_busc_n = $('#col_busc_n').val();
ref_fac = $('#ref_fac').val();
  //envio por ajax
$.ajax({
  type: "POST",
  url: "ajax/ndc.php",
  data: {
  fk_soc : fk_soc,
  fecha : fecha,
  producto : producto,
  nombre : nombre,
  moneda : moneda,
  col_busc:col_busc,
  col_busc_n:col_busc_n,
  ref_fac:ref_fac,   
  action:'get_ndc'
  },
  dataType: "json",
  success: function(resp) {
  $('#fac_ndc').html('');
    $.each(resp, function( index, value ) {
    $('#fac_ndc').append(
      '<tr class="tr_fac" data-id="'+value.id+'" style="cursor:pointer">'+
      '<td>'+value.facnumber+'</td>'+
      '<td>'+value.nom+'</td>'+      
      '<td>'+value.ref_client+'</td>'+
      '<td>'+value.datef+'</td>'+ 
      '<td>'+value.total_ttc+'</td>'+
      '<td></td>'+      
      '</tr>'
      );
      $.each(value.detalle, function( index, value ) {
       $('#fac_ndc').append(
      '<tr class="fac_'+value.fk_facture+' fac_td" style="display:none">'+
      '<td>'+value.ref+'</td>'+
      '<td>'+value.label+'</td>'+
      '<td>'+value.qty+'</td>'+ 
      '<td>'+value.fd_total_ttc+'</td>'+
      '<td>'+
          '<div class="form-check">'+
          '<input data-idc="'+value.id+'" type="checkbox" class="check_'+value.fk_facture+' form-check-input" id="check_'+value.id+'">'+
          '<label class="form-check-label" for="check_'+value.id+'"></label>'+
      '</div>'+
      '</td>'+      
      '</tr>'
      );        
      })

    })
   $('.tr_fac').click(function(){ 
   $('.fac_td').hide('slow');   
   $('.nd_selected').removeClass('nd_selected'); 
   $(this).addClass('nd_selected');
   id = $(this).attr('data-id');     
   $('.fac_'+id).show('slow');

   });

  }
  
  })
  //envio por ajax    
})

$('#aplicar_ndc').click(function(){
id = $('.nd_selected').attr('data-id')
c_data = []
$('.check_'+id+':checked').each(function(){
  id_c = $(this).attr('data-idc');
  c_data.push(id_c)
})

  //envio por ajax
  $.ajax({
    type: "POST",
    url: "ajax/ndc.php",
    data: {
    lineas:c_data,
    fk_facture:id,
    action:'aplicar_ndc'
    },
    dataType: "json",
    success: function(resp) {
     generar(resp)
     $('#tipo_doc').val(resp.type).trigger('change'); 
     $('#fk_facture_source').val(resp.fk_facture_source); 
     $('#fk_soc').val(resp.fk_soc); 
     $('#fk_facture').val(resp.fk_facture);      
    }
    
    })
    //envio por ajax  
    $('#modal_ndc').modal('hide');   

})

function get_client_info(fk_soc){
  //envio por ajax
  $.ajax({
    type: "POST",
    url: "ajax/clientes.php",
    data: {
    fk_soc:fk_soc,
    action:'get_client_info'
    },
    dataType: "json",
    success: function(resp) {
      $("#info_cliente").html('<span class="font-weight-bold">ID:</span>'+resp[0].id+'</br>'+
        '<span class="font-weight-bold">Cedula:</span>'+resp[0].siren+'</br>'+
        '<span class="font-weight-bold">Forma juridica:</span> '+resp[0].forme_juridique+'</br>'+
        '<span class="font-weight-bold">Nombre:</span> '+resp[0].nom+'</br>'+
        '<span class="font-weight-bold">Apellidos / Nombre fantasia:</span> '+resp[0].name_alias+'</br>'+
        '<span class="font-weight-bold">Telefono:</span> '+resp[0].phone+'</br>'+
        '<span class="font-weight-bold">Correo:</span> '+resp[0].correo+'</br>'+
        '<span class="font-weight-bold">Codigo:</span> '+resp[0].code_client+'</br>'+
        '<span class="font-weight-bold">Esta afiliado:</span> '+resp[0].inscrito+'</br>'+ 
        '<span class="font-weight-bold">Puntos disponibles:</span> '+resp[0].puntos+'</br>'+                
        '<span class="font-weight-bold">Nivel de precio:</span> '+resp[0].price_level+'</br>'+
        '<span class="font-weight-bold">Dirección:</span> '+resp[0].address+'</br>'+
        '<span class="font-weight-bold">Condiciones de pago:</span> '+resp[0].cond_reglement+'</br>'+
        '<span class="font-weight-bold">Limite de credito:</span> '+resp[0].limite_credito+'</br>'+
        '<span class="font-weight-bold">Pendiente:</span> '+resp[0].credito_usado+'</br>'+
        '<span class="font-weight-bold">Descuentos / NDC:</span> '+resp[0].av_discounts+'</br>'+
        '<span class="font-weight-bold">Comercial:</span> '+resp[0].comercial+'</br>'+
        '<span class="font-weight-bold">Cotizaciones:</span> '+resp[0].pendiente_propal+'</br>'+
        '<span class="font-weight-bold">Pedidos:</span> '+resp[0].pendiente_propal+'</br>'+
        '<span class="font-weight-bold">Credito disponible:</span> '+resp[0].credito_disponible+'</br>'+
        '<span class="font-weight-bold">Descuento fijo:</span> '+resp[0].remise_client+'</br>'); 
    }
    
    })
    //envio por ajax  
}