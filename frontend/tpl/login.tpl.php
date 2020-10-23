<!-- Modal -->
<div class="modal fade" id="modal_login" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
  aria-hidden="true">

  <!-- Add .modal-dialog-centered to .modal-dialog to vertically center the modal -->
  <div class="modal-dialog modal-dialog-centered" role="document">


    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="loginModalLongTitle">Autententicacion de usuario requerida</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="info_login">
      <div id="mess_login" class="text-center"></div><br>
      <div class="input-group mb-3">
            <div class="input-group-prepend"><span id="in_pago1" class="input-group-text"><i class="fas fa-user"></i></span></div>    
            <input type="text" class="form-control" name="l_usuario" id="l_usuario" placeholder="Usuario">
            </div>

            <div class="input-group mb-3">
            <div class="input-group-prepend"><span id="in_pago1" class="input-group-text"><i class="fas fa-lock"></i></span></div>    
            <input type="password" class="form-control" name="l_pass" id="l_pass" placeholder="Contraseña">
            </div><br> 
             <input type="hidden" class="form-control" name="l_action" id="l_action" value="">
      <div id="mess_error" class="text-center"></div><br>           
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="btn_login">Login</button>
      </div>
    </div>
  </div>
</div>