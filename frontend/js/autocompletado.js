
$( document ).ready(function() {
    $("#select_product").focus(function(){
    //$("#select_product").val('');    
    });
});

//*******AUTOCOMPLETADO
var options = {

    url: function(q) {
      return "ajax/productos.php";
    },
  
    getValue: function(element) {
      return element.text;
    },
    
   template: {
          type: "iconLeft",
          fields: {
              iconSrc: function(element) {
      return element.icon;
    }
          }
      }, 
  
    ajaxSettings: {
      dataType: "json",
      method: "POST",
      data: {
        dataType: "json"
      }
    },
  
    preparePostData: function(data) {
      data.q = $("#select_product").val();
      data.l = $("#price_level").val();
      data.m = $("#moneda").val();
      data.c = $("#cantidad").val();
      return data;
    },
  
      requestDelay: 800,  
   list: {
  
      maxNumberOfElements: 5000,	 
  
          
          onChooseEvent: function() {
            var value = $("#select_product").getSelectedItemData().id;
            $("#fk_product").val(value);
            $("#entrepot_stock").val($("#select_product").getSelectedItemData().Stock);
            $("#max_discount").val($("#select_product").getSelectedItemData().extrafields.options_descuento);  
            $("#precio_min").val($("#select_product").getSelectedItemData().precio_min);
            $("#cant_attributes").val($("#select_product").getSelectedItemData().cant_attributes); 
            $("#product_type").val($("#select_product").getSelectedItemData().type); 
            if($("#cantidad").val().length > 0){
           // $("#cantidad").focus();
            }else{
            $("#cantidad").val(1); 
            //$("#cantidad").focus();
         
          } 
          
          },

      onShowListEvent: function() {
        //console.log($(".eac-item").length);
        $(".eac-item").click(function(){
         $("#eac-container-select_product ul").hide();
        })
        if ($("#eac-container-select_product .eac-item").length === 1){
          $("#eac-container-select_product .eac-item").eq(0).click();
         $("#sbmtEnvoyer").eq(0).click();  
         add_line(); 

        }
        }


      },


      
    
  };
  
  var options2 = {

    //url: "productos_json/results_"+$('#options_sucursal').val()+".json",
    url: "ajax/productos.php",
    getValue: function(element) {

      return element.text;
    },

    template: {
        type: "iconLeft",
                  fields: {
                    iconSrc: "icon"
                  }                 
                },  

    list: {
        match: {
			enabled: true
		},
          onChooseEvent: function() {
            var value = $("#select_product").getSelectedItemData().id;
            $("#fk_product").val(value);
            $("#cantidad").val(1); 
                       
              $("#eac-container-select_product ul").hide();       
        },

        
        onShowListEvent: function() {
            if ($("#eac-container-select_product .eac-item").length === 1){
             $("#eac-container-select_product .eac-item").eq(0).click();
             //$("#sbmtEnvoyer").eq(0).click();             
            }
            },


    }
};





  $("#select_product").easyAutocomplete(options);
  //FIN*******AUTOCOMPLETADO

 