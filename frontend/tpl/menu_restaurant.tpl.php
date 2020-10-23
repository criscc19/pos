  <!--Double navigation-->
   <header>
    <!-- Sidebar navigation -->
    <div id="slide-out" class="side-nav sn-bg-4">
      <ul class="custom-scrollbar">
        <!-- Logo -->
        <li>
          <div class="logo-wrapper waves-light">
            <a href="tpv.php"><img src="<?php echo $logo ?>" class="img-fluid flex-center"></a>
          </div>
        </li>
        <!--/. Logo -->
  <li>
  <select name="tipo_doc" id="tipo_doc" class="custom-select">
<option value="1" <?php if($_SESSION['TIPO_DOC']== 1)  echo 'selected';?>>TICKET</option>
<option value="0" <?php if($_SESSION['TIPO_DOC']== 0)  echo 'selected';?>>FACTURA</option>
<?php
  if($user->rights->pos->pulled_apart){//gestion de permiso de apartados
?>
<option value="3" <?php if($_SESSION['TIPO_DOC']== 3)  echo 'selected';?>>APARTADO</option>
<?php
  }
  if($user->rights->pos->quotation){//gestion de permiso de cotizacion
?>
<option value="4" <?php if($_SESSION['TIPO_DOC']== 4)  echo 'selected';?>>COTIZACION</option>
<?php
  }
  if($user->rights->pos->crear_ndc){//gestion de permiso de NDC (Creacion de nota de credito) 
?>
<option value="2" <?php if($_SESSION['TIPO_DOC']== 2)  echo 'selected';?>>NDC</option>
<?php
  }
?>

</select>  
</li>

<!-- Side navigation links -->
<?php if(!$conf->global->restaurant){ ?>

<li>
<ul class="collapsible collapsible-accordion">
<li><a class="collapsible-header waves-effect" href="ordenar.php">Tomar orden</a></li>
<li><a class="collapsible-header waves-effect" href="comanda.php">Comanda</a></li>
</ul>
</li>
<?php
}
?>
        <li>
<ul class="collapsible collapsible-accordion">
<?php 
if($user->rights->pos->pay_bill){//gestion de permiso pagar factura
?>
<li><a class="collapsible-header waves-effect" href="abonar.php?tipo_doc=0,1&fk_currency=<?php echo $_SESSION['MULTICURRENCY_CODE'] ?>">Pagar Facturas</a></li>
<?php
} 
if($user->rights->pos->pay_sections){//gestion de permiso de pagar apartados
?>
<li><a class="collapsible-header waves-effect" href="abonar.php?tipo_doc=3">Pagar Apartados</a></li>
<?php
} 
if($user->rights->pos->invoice_list){//gestion de permiso de lista de facturas
?>
<li><a class="collapsible-header waves-effect" href="lista_facturas.php?fk_cierre=<?php echo $control->id ?>">Listado De Facturas</a></li>              
<?php 
} 
if($user->rights->pos->list_of_quotes){//gestion de permiso de lista de cotizaciones
?>
<li><a class="collapsible-header waves-effect" href="lista_cotizaciones.php?fk_cierre=<?php echo $control->id ?>">Listado De Cotizaciones</a></li>
<?php 
} 
?>
<li><a class="collapsible-header waves-effect" href="cierres.php?fk_cierre=<?php echo $control->id ?>&fk_cash=<?php echo $cash->id ?>">Mis cierres</a></li>        
</ul>
</li>
<!--/. Side navigation links -->
</ul>
      <hr>
      <div class="sidenav-bg mask-strong"></div>
<div id="caja_info">
      Terminal: <br><?php echo $cash->name; ?><br>
      Cierre: <?php echo $control->ref; ?><br>
      Fecha: <?php echo date('d-m-Y H:i:s',strtotime($control->date_open)); ?><br>
      Inicial: <?php echo price($control->amount_reel); ?><br>
      Teorico: <?php echo price($control->amount_teoric); ?><br>
      Diferencia: <?php echo price($control->amount_diff); ?><br>
      <center><a href="disconect.php"><i class="fas fa-sign-out-alt fa-1x"></i> SALIR</a></center>
</div>
    </div>
    <!--/. Sidebar navigation -->
    <!-- Navbar -->
    <nav class="navbar fixed-top navbar-toggleable-md navbar-expand-lg scrolling-navbar double-nav">
      <!-- SideNav slide-out button -->
      <div class="float-left">
        <a href="#" data-activates="slide-out" class="button-collapse"><i class="fas fa-bars"></i></a>
      </div>
      <!-- Breadcrumb-->
      <div class="breadcrumb-dn mr-auto">
        <p>Atendiendo a: <span data-toggle="modal" style="cursor:pointer" data-target="#info_c" id="cliente" class="font-weight-bold text-success"><?php echo $cliente->nom.' '.$cliente->name_alias ?></span>  | Cambio dolar:<?php echo price($cambio_dolar) ?></p>
        
      </div>
      
      <ul class="navbar-nav ml-auto nav-flex-icons">

            <li class="nav-item" data-toggle="tooltip" data-placement="bottom" title="Menu izquierdo">
             <a id="menuiz" class="menuiz nav-link waves-effect waves-light" data-activates="slide-out">
              <i class="fas fa-bars fa-2x"></i>
              </a>
            </li>  

            <li class="nav-item" data-toggle="tooltip" data-placement="bottom" title="Refrescar">
              <a id="recargar" class="nav-link waves-effect waves-light">
              <i class="fas fa-redo fa-2x"></i>
              </a>
            </li> 
             
            <li class="nav-item" data-toggle="tooltip" data-placement="bottom" title="Convertir puntos">
              <a id="rewardp" class="nav-link waves-effect waves-light">
              <i class="fas fa-gift fa-2x"></i>
              </a>
            </li> 

            <li class="nav-item" data-toggle="tooltip" data-placement="bottom" title="Nuevo Cliente">
              <a id="add_cliente" class="nav-link waves-effect waves-light">
              <i class="fas fa-user-plus fa-2x"></i>
              </a>
            </li>               
            
            <li class="nav-item" data-toggle="tooltip" data-placement="bottom" title="Guardar Venta">
              <a id="guardar" class="nav-link waves-effect waves-light">
              <i class="fas fa-cart-plus fa-2x"></i>
              </a>
            </li>

            <li class="nav-item" data-toggle="tooltip" data-placement="bottom" title="Recuperar Venta">
                <a id="recuperar" class="nav-link waves-effect waves-light">
                <i class="fas fa-cart-arrow-down fa-2x"></i>
                </a>
              </li> 
                         
            <li class="nav-item" data-toggle="tooltip" data-placement="bottom" title="Calculadora">
              <a id="calculadora" class="nav-link waves-effect waves-light">
              <i class="fas fa-calculator fa-2x"></i>
              </a>
            </li>

              <li class="nav-item" data-toggle="tooltip" data-placement="bottom" title="Eliminar Producto">
                  <a id="eliminar" class="nav-link waves-effect waves-light">
                  <i class="fas fa-trash-alt fa-2x"></i>
                  </a>
                </li>
                <?php
                  //if($user->rights->pos->edit_product){//gestion de permiso de editar producto
                ?>
                <li class="nav-item" data-toggle="tooltip" data-placement="bottom" title="Editar Producto">
                    <a class="nav-link waves-effect waves-light">
                    <i id="editar" class="fas fa-edit fa-2x"></i>
                    </a>
                  </li>
                <?php
                  //}
                  if($user->rights->pos->global_discount){//gestion de permiso de descuento global
                ?>
                  <li id="descuento_global" class="nav-item" data-toggle="tooltip" data-placement="bottom" title="Descuento Global">
                      <a class="nav-link waves-effect waves-light">
                      <i class="fas fa-percent fa-2x"></i>
                      </a>
                    </li>
                  <?php
                  }


                  if($user->rights->pos->crear_ndc){//gestion para poder crear la nota de credito
                   ?>  
                  <li id="nota_credito" class="nav-item" data-toggle="tooltip" data-placement="bottom" title="Nota De Credito">
                      <a class="nav-link waves-effect waves-light" style="color:red">
                      <i class="fas fa-file-invoice fa-2x"></i>
                      </a>
                    </li>                    
                  <?php
                  }


                  
                   ?>  
                      <li class="nav-item" title="Facturar desde un documento">
                          <a id="comercial_doc"  class="nav-link waves-effect waves-light">
                          <i class="fas fa-briefcase fa-2x"></i>
                          </a>
                        </li>                
                                       
                    <li class="nav-item" data-toggle="tooltip" data-placement="bottom" title="Listado De Productos">
                        <a id="l_productos" class="nav-link waves-effect waves-light">
                        <i class="fas fa-th-list fa-2x"></i>
                        </a>
                      </li>
                      <?php 
                      if($user->rights->pos->box_closure){//gestion para poder Cierre de caja
                      ?>
                      <li class="nav-item" title="Cierre de caja">
                          <a id="cierre_caja"  class="nav-link waves-effect waves-light">
                          <i class="fas fa-cash-register fa-2x"></i>
                          </a>
                        </li>
                      <?php }?>
          </ul>
          
    </nav>
    <!-- /.Navbar -->
   
  </header>
  <?php 
  include('tpl/cliente_info.tpl.php');
  ?>