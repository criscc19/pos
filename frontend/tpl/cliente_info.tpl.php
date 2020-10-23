<!-- Modal -->
<div class="modal fade" id="info_c" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
  aria-hidden="true">

  <!-- Add .modal-dialog-centered to .modal-dialog to vertically center the modal -->
  <div class="modal-dialog modal-dialog-centered" role="document">


    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle"><?php echo $cliente->nom.' '.$cliente->name_alias ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="info_cliente">
        <span class="font-weight-bold">ID:</span> <?php echo $cliente->id ?></br>
        <span class="font-weight-bold">Cedula:</span> <?php echo $cliente->idprof1 ?></br>
        <span class="font-weight-bold">Forma juridica:</span> <?php echo $cliente->forme_juridique ?></br>
        <span class="font-weight-bold">Nombre:</span> <?php echo $cliente->nom ?></br>
        <span class="font-weight-bold">Apellidos / Nombre fantasia:</span> <?php echo $cliente->name_alias ?></br>
        <span class="font-weight-bold">Telefono:</span> <?php echo $cliente->phone ?></br>
        <span class="font-weight-bold">Correo:</span> <?php  echo $cliente->email ?></br>
        <span class="font-weight-bold">Codigo:</span> <?php echo $cliente->code_client ?></br>
        <span class="font-weight-bold">Esta afiliado:</span> <?php echo $inscrito ?></br>          
        <span class="font-weight-bold">Puntos disponibles:</span> <?php echo $puntos ?></br>             
        <span class="font-weight-bold">Nivel de precio:</span> <?php echo $cliente->price_level ?></br>
        <span class="font-weight-bold">Dirección:</span> <?php echo $cliente->address ?></br>
        <span class="font-weight-bold">Condiciones de pago:</span> <?php echo $cliente->cond_reglement ?></br>
        <span class="font-weight-bold">Limite de credito:</span> <?php echo $cliente->outstanding_limit ?></br>
        <span class="font-weight-bold">Pendiente:</span> <?php echo (float)$pendiente['opened'] ?></br>
        <span class="font-weight-bold">Descuentos / NDC:</span> <?php echo $av_discounts ?></br>
        <span class="font-weight-bold">Comercial:</span> <?php echo implode(',',$comercial) ?></br>
        <span class="font-weight-bold">Cotizaciones:</span> <?php echo (float)$pendiente_propal['opened'] ?></br>
        <span class="font-weight-bold">Pedidos:</span> <?php echo (float)$pendiente_commande['opened'] ?></br>
        <span class="font-weight-bold">Credito disponible:</span> <?php echo (float)$cliente->outstanding_limit - (float)$pendiente['opened'] ?></br>
        <span class="font-weight-bold">Descuento fijo:</span> <?php echo (float)$cliente->remise_client ?></br>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>