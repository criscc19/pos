<?php
 $logo = DOL_MAIN_URL_ROOT.'/viewimage.php?modulepart=mycompany&file=logos/thumbs/'.$mysoc->logo_small;

 ?>
  <!--Double navigation-->
  <header>
    <!-- Sidebar navigation -->
    <div id="slide-out" class="side-nav sn-bg-4">
      <ul class="custom-scrollbar">
        <!-- Logo -->
        <li>
          <div class="logo-wrapper waves-light">
            <a href="<?php echo  DOL_MAIN_URL_ROOT.'/pos/frontend/'; ?>tpv.php"><img src="<?php echo $logo ?>" class="img-fluid flex-center"></a>
          </div>
        </li>
        <!--/. Logo -->
  <li>
  <select name="tipo_doc" id="tipo_doc" class="custom-select">
<option value="1" <?php if($_SESSION['TIPO_DOC']== 1)  echo 'selected';?>>TICKET</option>
<option value="0" <?php if($_SESSION['TIPO_DOC']== 0)  echo 'selected';?>>FACTURA</option>
<option value="3" <?php if($_SESSION['TIPO_DOC']== 3)  echo 'selected';?>>APARTADO</option>
<option value="4" <?php if($_SESSION['TIPO_DOC']== 4)  echo 'selected';?>>COTIZACION</option>
<option value="2" <?php if($_SESSION['TIPO_DOC']== 2)  echo 'selected';?>>NDC</option>

</select>  
</li>
        <!-- Side navigation links -->
        <li>
          <ul class="collapsible collapsible-accordion">
          <li><a class="collapsible-header waves-effect" href="abonar.php?tipo_doc=0,1">Pagar facturas</a></li>
            <li><a class="collapsible-header waves-effect" href="abonar.php?tipo_doc=3">Pagar apartados</a></li>
            <li><a class="collapsible-header waves-effect" href="lista_facturas.php?fk_cierre=<?php echo $control->id ?>">Listado de facturas</a></li>              
            <li><a class="collapsible-header waves-effect" href="lista_cotizaciones.php?fk_cierre=<?php echo $control->id ?>">Listado de cotizaciones</a></li>

          </ul>
        </li>
        <!--/. Side navigation links -->
      </ul>
      <div class="sidenav-bg mask-strong"></div>
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
        
      </div>
      
      <ul class="navbar-nav ml-auto nav-flex-icons">
      <li class="nav-item" data-toggle="tooltip" data-placement="bottom">
             <a href="tpv.php">
             <i class="fas fa-redo fa-2x"></i>
              </a>
            </li>  
          </ul>
          
    </nav>
    <!-- /.Navbar -->
   
  </header>