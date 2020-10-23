<!-- Modal -->
<div class="modal fade" id="modal_dividir" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-fluid" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Division de factura</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      <div class="input-group">
       <input type="text" class="form-control col-md-4" name="div_num" id="div_num" placeholder="Numero de partes" value="0">
      <select name="div_tipo" id="div_tipo" class="form-control col-md-4">
      <option value="todo">Partes iguales</option
      ><option value="individual">Individualmente</option>
      </select>
      <button id="init_div_tipo" type="button" class="btn btn-info btn-sm waves-effect waves-light">APLICAR</button>      
      </div>
      <div id="partes_div_table">
      
      </div>
      
      
      </div>
      <div class="modal-footer">
<!--         <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary btn-sm" id="get_ndc_fac">Iniciar busqueda</button>
        <button id="aplicar_ndc" type="button" class="btn btn-success btn-sm" id="get_ndc_fac">APLICAR NDC</button> -->
      </div>
    </div>
  </div>
</div>