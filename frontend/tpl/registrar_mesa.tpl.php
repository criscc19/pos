
<!-- Modal -->
<div class="modal fade" id="modal_mesa_reg" tabindex="-1" role="dialog" aria-labelledby="modal_mesa_reg" aria-hidden="true">

  <!-- Add .modal-dialog-centered to .modal-dialog to vertically center the modal -->
  <div class="modal-dialog modal-dialog-centered" role="document">


    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="chanegecModalLongTitle">Registro de orden para: <span id="name_mesa"></span></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="chanegec_body">
      <input class="form-control my-0 py-1" type="text" style="width: 450px;"  name="select_cliente3" id="select_cliente3" placeholder="Buscar Cliente" aria-label="Search" value="">      
      Capacidad: <span id="capacidad_mesa"></span><br>
      Descripcion: <span id="description_mesa"></span><br>  
      Ubicacion: <span id="ubicacion_mesa"></span><br>  
      <input type="hidden" name="fk_mesa" id="fk_mesa">           
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal" id="close_chanegec">Close</button>
        <button type="button" class="btn btn-primary" id="inciar_orden">Registrar y ordenar</button>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function(){


    //*******AUTOCOMPLETADO
var options_c = {
	
    url: function(q) {
      return "ajax/clientes.php";
    },
  
    getValue: function(element) {
      return element.text;
    },
    
/*    template: {
          type: "iconLeft",
          fields: {
              iconSrc: function(element) {
      return element.icon;
    }
          }
      },  */
  
    ajaxSettings: {
      dataType: "json",
      method: "POST",
      data: {
        dataType: "json"
      }
    },
  
    preparePostData: function(data) {
      data.q = $("#select_cliente3").val();
      data.e = 1;
      return data;
    },
  
      requestDelay: 500,  
   list: {
  
      maxNumberOfElements: 5000,	 
  
          
          onChooseEvent: function() {
                  $("#fk_soc").val($("#select_cliente3").getSelectedItemData().id);
                  $("#cliente").html($("#select_cliente3").getSelectedItemData().nom);
                  $("#forme_juridique_code").val($("#select_cliente3").getSelectedItemData().forme_juridique_code);  
                  $("#idprof1").val($("#select_cliente3").getSelectedItemData().siren); 
                  $("#limite_credito").val($("#select_cliente3").getSelectedItemData().limite_credito); 
                  $("#credito_usado").val($("#select_cliente3").getSelectedItemData().credito_usado);
                  $("#credito_disponible").val($("#select_cliente3").getSelectedItemData().credito_disponible);
                  //get_lineas();
                  //get_ndc_desc($("#select_cliente3").getSelectedItemData().id);
                  $("#eac-container-select_cliente3 ul").hide();
                  $("#exampleModalLongTitle").html($("#select_cliente3").getSelectedItemData().text);
                  $("#info_cliente").html('<span class="font-weight-bold">ID:</span>'+$("#select_cliente3").getSelectedItemData().id+'</br>'+
        '<span class="font-weight-bold">Cedula:</span>'+$("#select_cliente3").getSelectedItemData().siren+'</br>'+
        '<span class="font-weight-bold">Forma juridica:</span> '+$("#select_cliente3").getSelectedItemData().forme_juridique+'</br>'+
        '<span class="font-weight-bold">Nombre:</span> '+$("#select_cliente3").getSelectedItemData().nom+'</br>'+
        '<span class="font-weight-bold">Apellidos / Nombre fantasia:</span> '+$("#select_cliente3").getSelectedItemData().name_alias+'</br>'+
        '<span class="font-weight-bold">Telefono:</span> '+$("#select_cliente3").getSelectedItemData().phone+'</br>'+
        '<span class="font-weight-bold">Correo:</span> '+$("#select_cliente3").getSelectedItemData().correo+'</br>'+
        '<span class="font-weight-bold">Codigo:</span> '+$("#select_cliente3").getSelectedItemData().code_client+'</br>'+
        '<span class="font-weight-bold">Esta afiliado:</span> '+$("#select_cliente3").getSelectedItemData().inscrito+'</br>'+ 
        '<span class="font-weight-bold">Puntos disponibles:</span> '+$("#select_cliente3").getSelectedItemData().puntos+'</br>'+                
        '<span class="font-weight-bold">Nivel de precio:</span> '+$("#select_cliente3").getSelectedItemData().price_level+'</br>'+
        '<span class="font-weight-bold">Dirección:</span> '+$("#select_cliente3").getSelectedItemData().address+'</br>'+
        '<span class="font-weight-bold">Condiciones de pago:</span> '+$("#select_cliente3").getSelectedItemData().cond_reglement+'</br>'+
        '<span class="font-weight-bold">Limite de credito:</span> '+$("#select_cliente3").getSelectedItemData().limite_credito+'</br>'+
        '<span class="font-weight-bold">Pendiente:</span> '+$("#select_cliente3").getSelectedItemData().credito_usado+'</br>'+
        '<span class="font-weight-bold">Descuentos / NDC:</span> '+$("#select_cliente3").getSelectedItemData().av_discounts+'</br>'+
        '<span class="font-weight-bold">Comercial:</span> '+$("#select_cliente3").getSelectedItemData().comercial+'</br>'+
        '<span class="font-weight-bold">Cotizaciones:</span> '+$("#select_cliente3").getSelectedItemData().pendiente_propal+'</br>'+
        '<span class="font-weight-bold">Pedidos:</span> '+$("#select_cliente3").getSelectedItemData().pendiente_propal+'</br>'+
        '<span class="font-weight-bold">Credito disponible:</span> '+$("#select_cliente3").getSelectedItemData().credito_disponible+'</br>'+
        '<span class="font-weight-bold">Descuento fijo:</span> '+$("#select_cliente3").getSelectedItemData().remise_client+'</br>');
          cambio_cliente($("#select_cliente3").getSelectedItemData().id);
          //$('#modal_mesa_reg').modal('hide'); 
          $('#select_cliente').val(''); 
          }
      },
    
  };
  
  $("#select_cliente3").easyAutocomplete(options_c);
  //FIN*******AUTOCOMPLETADO   
  
  
function cambio_cliente(fk_soc){
fk_facture = $('#fk_facture').val();
fk_vendedor = $('#options_vendedor').val();
//envio por ajax
num = $(this).attr('data-banco');
$.ajax({
  type: "POST",
  url: "ajax/cash_detalle.php",
  data: {
    fk_facture:fk_facture,
    fk_soc:fk_soc,
    fk_vendedor:fk_vendedor,
    action:'cambiar_cliente'
  },
  dataType: "json",
  success: function(resp) {
console.log(resp)
  }

  
  })
  //envio por ajax   
}




})    


</script>    
