 
<!--/.Navbar -->
  <div class="container-fluid contenido">
<br><br><br>
<div class="row" >
 <div class="alert alert-danger" role="alert">
  <h4 class="alert-heading">NO HAY CONTROL DE CAJA ABIERTO!</h4>
  <p>Para usar el punto de venta debe abrir un control de caja.</p>
  <hr>
  <form method="POST" action="<?php $_SERVER['PHP_SELF'] ?>">
  <input type="hidden" name="action" value="abrir_caja">
  MONTO INICIAL COLONES: <br>
  <div class="btn-group" role="group">
  <input type="text" name="amount_reel" id="amount_reel" value="0"> 
  
</div><br>
MONTO INICIAL DOLARES: <br>
<div class="btn-group" role="group">
  <input type="text" name="multicurrency_amount_reel" id="multicurrency_amount_reel" value="0">  
</div> <br>

<button type="submit" id="btn_caja" class="btn btn-primary">ABRIR CAJA</button>
</form>
</div>


</div></div>  