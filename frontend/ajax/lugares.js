$(".lugares").change(function(){
    provincia = $('#provincia').val();
    canton = $('#canton').val();
    distrito = $('#distrito').val();
    barrio = $('#barrio').val();
    id = $(this).val();
    nom = $(this).data('nom');
    //envio por ajax
    $.ajax({
                type: "POST",
                url: "ajax/lugares.php",
                dataType: 'json',
                data: {provincia:provincia,canton:canton,distrito:distrito,barrio:barrio,id:id,nom:nom},
                success: function(resp) {
                
                console.log(resp.cantones);
                $("#canton").html('');
                $("#distrito").html('');
                $("#barrio").html('');
                
                //provincia                               
                $(resp.provincias).each(function(index,value) {
                    $("#provincia").html('<option value="'+value.id+'">'+value.provincia+'</option>');
                  });

                //canton 
                $("#canton").append('<option value=""></option>');   
                $(resp.cantones).each(function(index,value) {
                    if(canton==value.id){
                     $("#canton").append('<option value="'+value.id+'" selected>'+value.canton+'</option>');                       
                    }else{
                      $("#canton").append('<option value="'+value.id+'">'+value.canton+'</option>');                          
                    }
                  });
                  
                //distritos
                $("#distrito").append('<option value=""></option>'); 
                $(resp.distritos).each(function(index,value) {
                    if(distrito==value.id){
                     $("#distrito").append('<option value="'+value.id+'" selected>'+value.distrito+'</option>');                       
                    }else{
                      $("#distrito").append('<option value="'+value.id+'">'+value.distrito+'</option>');                          
                    }
                  });   
                  
                  
                 //barrios
                $("#barrio").append('<option value=""></option>');                  
                $(resp.barrios).each(function(index,value) {
                    if(barrio==value.id){
                     $("#barrio").append('<option value="'+value.id+'" selected>'+value.barrio+'</option>');                       
                    }else{
                      $("#barrio").append('<option value="'+value.id+'">'+value.barrio+'</option>');                          
                    }
                  });                   
                  


                }
            })
    //envio por ajax
        })