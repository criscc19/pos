<!-- Modal -->
<div class="modal fade" id="modal_ndc" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-fluid" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Buscar Factura para vincular nota de credito</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      Fecha
      <input class="form-control" type="date" name="fecha" id="fecha">
      
      <div class="btn-group" role="group"> 
      <input class="form-control" type="text" name="nombre_c" id="nombre_c" placeholder="Cliente"><br>
      <select id="col_busc_n">
      <option value="ref_client">Ref cliente</option> 
      </select>       
      </div>


      
      <div class="btn-group" role="group">  
      <input placeholder="Buscar producto" class="form-control" type="text" name="producto" id="producto_c">
      <select id="col_busc">
      <option value="ref">Codigo</option>
      <option value="label">Nombre</option>
      <option value="description">Descripcion</option>
      <option value="barcode">Codigo de barras</option>
      </select>   
      </div>

      <div class="btn-group" role="group"> 
     <input class="form-control" type="text" name="ref_fac" id="ref_fac" placeholder="Ref Factura"><br> 
      </div>


     <div id="ndc_content">
     <table id="tabla_venta" class="table table-bordered" width="100%">
     <thead>
     <tr class="btn-dark">
     <td>Factura</td>
     <td>Cliente</td> 
     <td>Ref.cliente</td> 
     <td>Fecha</td>
     <td>Total</td> 
     <td></td>             
     </tr>
     </thead>
     <tbody id="fac_ndc">
     </tbody>     
     </table>
     </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary btn-sm" id="get_ndc_fac">Iniciar busqueda</button>
        <button id="aplicar_ndc" type="button" class="btn btn-success btn-sm" id="get_ndc_fac">APLICAR NDC</button>
      </div>
    </div>
  </div>
</div>