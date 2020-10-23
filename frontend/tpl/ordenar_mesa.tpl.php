<!-- Central Modal Small -->
<div class="modal fade" id="mesa_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">

  <!-- Change class .modal-sm to change the size of the modal -->
  <div class="modal-dialog modal-fluid" role="document">


    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title w-100" id="myModalLabel">Categorias / Platillos <div id="back_cate" class="btn btn-primary btn-sm waves-effect waves-light"><i class="fas fa-angle-double-left"></i></div></h4>
        <div class="input-group mb-3">
 <input  type="text" class="form-control"  name="b_product" id="b_product" placeholder="Buscar producto" value=""/>
 <div class="input-group-prepend"><span id="in_pago1" class="input-group-text btn-primary"><i class="fas fa-search"></i></span></div>
</div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      <!-- Contenido  -->
      <div class="container-fluid">
      <div class="row">

      <div id="ul_mesa_categorie" class="carousel slide carousel-multi-item" data-ride="carousel">
  <!--Controls-->
  <div class="controls-top">
    <a class="btn-floating iz" href="#ul_mesa_categorie" data-slide="next"><i class="fas fa-chevron-right"></i></a>
    <a class="btn-floating de" href="#ul_mesa_categorie" data-slide="prev"><i class="fas fa-chevron-left"></i></a>

  </div>
  <!--/.Controls-->

  <!--Indicators-->
   <!--<ol class="carousel-indicators">
    <li data-target="#ul_mesa_categorie" data-slide-to="0" class="active"></li>
    <li data-target="#ul_mesa_categorie" data-slide-to="1"></li>
    <li data-target="#ul_mesa_categorie" data-slide-to="2"></li>
    <li data-target="#ul_mesa_categorie" data-slide-to="3"></li>    
  </ol>-->
  <!--/.Indicators-->

  <!--Slides-->
  <div id="ul_mesa_categorie_item" class="carousel-inner carousel_item" role="listbox">

<div class="carousel-item active" id="cat_8" data-id="8" data-levels="0" data-fk_parent="0" class="cat"><button type="button" class="btn bg-dark btn-rounded">ACCESORIOS DE VINO, BAR Y HOTEL</button></div>
<div id="cat_10" data-id="10" data-levels="0" data-fk_parent="0" class="cat"><button type="button" class="btn bg-dark btn-rounded">BOLSAS</button></div>
<div id="cat_1" data-id="1" data-levels="1" data-fk_parent="0" class="cat"><button type="button" class="btn bg-dark btn-rounded">Categoria de prueba1</button></div>
<div id="cat_2" data-id="2" data-levels="0" data-fk_parent="0" class="cat"><button type="button" class="btn bg-dark btn-rounded">Categoria de prueba2</button></div>


<div class="carousel-item ">  
<div id="cat_8" data-id="8" data-levels="0" data-fk_parent="0" class="cat"><button type="button" class="btn bg-dark btn-rounded">ACCESORIOS DE VINO, BAR Y HOTEL</button></div>
<div id="cat_10" data-id="10" data-levels="0" data-fk_parent="0" class="cat"><button type="button" class="btn bg-dark btn-rounded">BOLSAS</button></div>
<div id="cat_1" data-id="1" data-levels="1" data-fk_parent="0" class="cat"><button type="button" class="btn bg-dark btn-rounded">Categoria de prueba1</button></div>
<div id="cat_2" data-id="2" data-levels="0" data-fk_parent="0" class="cat"><button type="button" class="btn bg-dark btn-rounded">Categoria de prueba2</button></div>
</div>


</div>
</div>


      </div>
<hr>
<div class="row">
<ul id="product_mesa_lista" class="nav grey lighten-4 py-4">


</ul>
</div>


      </div>
       <!-- /Contenido  -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>
<!-- Central Modal Small -->