$('#btn_login_arqueo').click(function(){
usuario = $('#arqueo_user').val();
pass = $('#arqueo_pass').val();
login(usuario,pass);


})

function login(usuario,pass){
//envio por ajax
$('#btn_login_arqueo').hide();
$('#login_arqueo_loader').show(); 

$.ajax({
  type: "POST",
  url: "ajax/login.php",
  data: {
    usuario:usuario,
    pass:pass
  },
  dataType: "json",
  success: function(resp) {
    loginr = parseInt(resp.id);

    if( loginr > 0){
$('#login_div').show(); 
$('#login_div_msg_green').show();
$('#fk_responsable').val(resp.id);
$('#login_div_msg_green').html('Usuario:'+resp.firstname+' '+resp.lastname);
$('#login_div_msg_red').hide(); 
$('#btn_login_arqueo').show();
$('#login_arqueo_loader').hide(); 
$('#arqueo_pass').val('');
    }else{
$('#login_div').hide();
$('#login_div_msg_green').hide();
$('#login_div_msg_red').show();
$('#btn_login_arqueo').show();
$('#login_arqueo_loader').hide(); 

    }
  
  }
  
  })
  //envio por ajax  
}