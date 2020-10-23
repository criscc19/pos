function save_position(id,position){
//envio por ajax
$.ajax({
    type: "POST",
    url: "ajax/save_mesa.php",
    data: {
      id:id,
      position:position,
      action:'save_position'
    },
    dataType: "json",
    success: function(resp) {
    console.log(resp);

    }
    
    })
    //envio por ajax  
}







$('.c_mesa').click(function(){
estado = $(this).attr('data-estado')
if(estado > 0){
    $('#mesa_modal').modal('show');
    get_restaurant_categoria('#ul_mesa_categorie_item');    
}else{
   name = $(this).attr('data-name');
   description = $(this).attr('data-description');
   capacidad = $(this).attr('data-capacidad');
   ubicacion = $(this).attr('data-ubicacion'); 
   id =  $(this).attr('data-id'); 
    $('#name_mesa').text(name); 
    $('#description_mesa').text(description); 
    $('#capacidad_mesa').text(capacidad); 
    $('#ubicacion_mesa').text(ubicacion);  
    $('#fk_mesa').val(id);  
   $('#modal_mesa_reg').modal('show');   
}

})


$('#inciar_orden').click(function(){
    ref_client = $('#select_cliente3').val();
    save_fac(ref_client);
})




$('#init_div_tipo').click(dividir_fac);


function dividir_fac(){
 if(parseInt($('#div_num').val()) <= 0){
    Swal.fire({
        type: 'error',
        title: 'La cantida de partes es obligatoria',
        showConfirmButton: false,
        timer: 1000
        
      })
  return;    
 } 
tipo_div = $('#div_tipo').val();
num_div = parseInt($('#div_num').val());
monto = parseFloat($('#g_total').attr('data-total_ttc'));
total = monto/num_div;
if(tipo_div == 'todo'){
  $('#partes_div_table').html('');
  send_div_fac();  
}

if(tipo_div == 'individual'){
html_div = '<table>';
for(i=1;i<=num_div;i++){
html_div += '<tr>'+
'<td>Monto parte '+i+'</td>'+
'<td><input class="div_part form-control" type="text" name="num_div_part[]" id="num_div_part_'+i+'" value="'+total+'"></td>'+  
'</tr>';
}
html_div += '</table>';
html_div += 'Total :<span id="div_part_total">'+numeral(monto).format('0,0.00')+'</span> <br>Direferencia: <span id="div_part_total_dif">'+0+'</span><br>';
html_div += '<button id="init_div_tipo_in" type="button" class="btn btn-success btn-sm waves-effect waves-light">APLICAR</button>';
$('#partes_div_table').html(html_div);  
$('#init_div_tipo_in').click(send_div_fac);
$('.div_part').keyup(cal_div_part);
}

}

function cal_div_part(){
total = 0;
num = parseInt($('#div_num').val());
monto = parseFloat($('#g_total').attr('data-total_ttc'));
div_part_monto = $("input[name='num_div_part[]']").map(function(){return $(this).val();}).get();
$.each(div_part_monto,function(index,value){
    total += parseFloat(value)    
    })
$('#div_part_total').text(numeral(total).format('0,0.00'));
$('#div_part_total_dif').text(numeral(monto - total).format('0,0.00'));
}

function send_div_fac(){

tipo_div = $('#div_tipo').val();
num_div = $('#div_num').val();
fk_facture = $('#fk_facture').val();
num_div_part = $("input[name='num_div_part[]']").map(function(){return $(this).val();}).get();
//envio por ajax
$.ajax({
type: "POST",
url: "ajax/add_line_restaurant.php",
data: {
tipo_div:tipo_div,
num_div:num_div,
fk_facture:fk_facture,
num_div_part:num_div_part,
action:'dividir_fac'
},
dataType: "json",
success: function(resp) {
$('#modal_dividir').modal('hide');
$('#barra_c').hide();
if(resp.leng.length > 0){
    location.reload();
}
}

})
//envio por ajax 
}

