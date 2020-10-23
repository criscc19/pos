
function trigger_autocomplete(value,id){
    var e = jQuery.Event("keyup");
    e.which = 8; //choose the one you want
    e.keyCode = 8;
    $('#'+id).val(value).trigger(e);
    }

$('#select_product')
	.keyboard({
		openOn : null,
		stayOpen : true,
		layout : 'qwerty',
		//layout : 'num',
		  accepted : function(event, keyboard, el) {
		  trigger_autocomplete(el.value,$(this).attr('id'))
          
  }
    });

    
$('#select_cliente')
	.keyboard({
		openOn : null,
		stayOpen : true,
		layout : 'qwerty',
		//layout : 'num',
		  accepted : function(event, keyboard, el) {
		  trigger_autocomplete(el.value,$(this).attr('id'))
          
  }
    });  


    $('#cantidad')
	.keyboard({
		openOn : null,
		stayOpen : true,
		//layout : 'qwerty',
		layout : 'num'
    });


        $('#descuento')
	.keyboard({
		openOn : null,
		stayOpen : true,
		//layout : 'qwerty',
		layout : 'num'
    });


    $('.teclado_virtual').click(function(){
        id_n = $(this).attr('data-id');
        var kb = $('#'+id_n).getkeyboard();
        // close the keyboard if the keyboard is visible and the button is clicked a second time
        if ( kb.isOpen ) {
            kb.close();
        } else {
            kb.reveal();
        }
    });