
<!-- Modal -->
<div class="modal fade" id="reward_modal" tabindex="-1" role="dialog" aria-labelledby="reward_modal"
  aria-hidden="true">

  <!-- Add .modal-dialog-centered to .modal-dialog to vertically center the modal -->
  <div class="modal-dialog modal-dialog-centered" role="document">


    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="variantModalLongTitle">Conversion de puntos</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="variant_body">
      <input type="hidden" name="total_points" id="total_points" value="<?php echo $puntos; ?>">   
      <input type="hidden" name="rewards_discount" id="rewards_discount" value="<?php echo $conf->global->REWARDS_DISCOUNT; ?>"> 
       Puntos disponibles : <span id="text_total_points"><?php echo price($puntos); ?></span><br>
      <div class="input-group mb-3">
      <input style="width:100px;color:black;background-color:#dcdcdc" type="text" class="form-control" name="rewar_points" id="rewar_points" value="0">
      <div class="input-group-prepend"><span id="all_points" class="input-group-text btn-primary"><i class="fas fa-share" aria-hidden="true"></i></span></div>
      </div><br>
    Serán convertidos en una bonificación de <span id="total_rewards_discount" class="font-weight-bold">0</span> CRC
       <input type="hidden" name="des_puntos" id="des_puntos" value="0">
       <input type="hidden" name="conv_points" id="conv_points" value="0">      
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal" id="close_rewards">Retirar puntos</button>
        <button type="button" class="btn btn-primary" id="aplicar_rewards">Aplicar puntos</button>
      </div>
    </div>
  </div>
</div>
<script>

$('#all_points').click(function(){
total_rwp =  parseFloat($('#total_points').val());
rewards_discount = parseFloat($('#rewards_discount').val());
rew_p = parseFloat($('#rewar_points').val());  
total_facp =  parseFloat($('#g_total').attr('data-total_ttc')) / rewards_discount;
if(total_rwp >= total_facp){
$('#rewar_points').val(total_facp);
}

if(total_rwp < total_facp){
$('#rewar_points').val(total_rwp);
}
cal_reward();

});


$('#close_rewards').click(function(){
$('#rewar_points').val(0);
$('#des_puntos').val(0);
$('#reward_modal').modal('hide');
$('#rew_points').val(0);
$('#conv_points').val(0);
cal_reward();
calculos();
});


$('#aplicar_rewards').click(function(){
$('#reward_modal').modal('hide');
$('#rew_points').val(1);
cal_reward();
calculos();
});

$('#rewar_points').keyup(cal_reward);

function cal_reward(){
console.log($('#rewar_points').val());
total_rwp =  parseFloat($('#total_points').val());
rewards_discount = parseFloat($('#rewards_discount').val());
rew_p = parseFloat($('#rewar_points').val()); 
rewards_cov = rew_p * rewards_discount;
total_facp =  parseFloat($('#g_total').attr('data-total_ttc') / rewards_discount);
if(rew_p > total_rwp){
total_points = total_rwp;
total_points_cov = total_points * rewards_discount;
$('#rewar_points').val(total_points);
if(rew_p > total_facp){
total_points = total_facp; 
total_points_cov = total_points * rewards_discount; 
$('#rewar_points').val(total_points);
}

res_point = total_rwp - total_points;

}else{
total_points = rew_p;
total_points_cov = rew_p * rewards_discount;  
res_point = total_rwp - total_points;
}

$('#des_puntos').val(total_points);
$('#conv_points').val(total_points_cov);

$('#text_total_points').text(numeral(res_point).format('0,0.00'));
$('#total_rewards_discount').text(numeral(total_points_cov).format('0,0.00')); 
}
</script>