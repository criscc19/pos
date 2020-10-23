 $("#nuevocliente").on('click', function(e) {
    e.preventDefault();
    procesarnuevocliente();
});
/**Funcion encargada de los nuevos clientes validar */
function procesarnuevocliente(){

    barrio = $("#barrio").val();    
    distrito = $("#distrito").val();
    canton = $("#canton").val();
    provincia = $("#provincia").val();
    phone = $("#phone").val();
    direccion = $("#direccion").val();
    email = $("#email").val();
    lastname = $("#lastname").val();
    firstname= $("#firstname").val();
    cedula = $("#cedula").val();
    tipo_cedula = $("#tipo_cedula").val();
    informacionError="";

    //cedula,firs+name,lastname,email,direccion,phone,provincia,canton,distrito,barrio
    //validaciones para verificar que no esten vacios los datos antes de enviar.
    if(phone.length <= 0 ){
      informacionError += "Favor llenar Telefono." ;
    }

    if($("#tipo_cedula").val() == undefined){
      informacionError += "Seleccionar tipo de forma juridica." ;
     }
   /* if(direccion ===""){
      informacionError += "Favor llenar Dirección";
    }
    if(cedula ===""){
      informacionError += "Favor llenar Cedula.";
    }
    if(email ===""){
      informacionError += "Favor llenar Correo" ;
    }
    if(lastname ===""){
      informacionError += "Favor llenar Apellidos";
    }
    if(firsname ===""){
      informacionError += "Favor llenar Nombre";
    }*/

    if(informacionError!=""){
      mensaje("Error",informacionError,'warning');
    }else{
      procesarconsulta(cedula,firstname,lastname,email,direccion,phone,provincia,canton,distrito,barrio,tipo_cedula)
    }

}

/**Funcion diseñada para poder mostrar el mensaje emerjente */
function mensaje(titulo,informacion, logoss){
    Swal.fire({
        title: titulo,
        text: informacion,
        icon: logoss,
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'Close!'
      }).then((result) => {
        if (result.value) {
        
        }
      })

}
function procesarconsulta(cedula,firstname,lastname,email,direccion,phone,provincia,canton,distrito,barrio){
    $.ajax({
        type: "POST",
        url: "ajax/nuevocliente.php",
        data: {action:'crear',tipo_cedula:tipo_cedula,cedula:cedula,firstname:firstname,lastname:lastname,email:email,direccion:direccion,phone:phone,provincia:provincia,canton:canton,distrito:distrito,barrio:barrio},
        dataType: "json",
        success: function(resp) {
          console.log(resp);
          if(resp.id >0){
            $('#cliente').text(resp.nom+" "+resp.name_alias);
            $('#fk_soc').val(resp.id);
            mensaje("Exito"," Cliente registrado",'success');
            $('#modalContactForm').modal('hide');
            $('#modalContactForm').find('input').val('');
            $('#modalContactForm').find('select').val('').trigger('change');
          }else{
            mensaje("Error",resp.error,'error');
          }
        
          
        //mensaje("Mensaje",resp.nom,'success');
        }
        
        //}
        })
}
