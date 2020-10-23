$(document).keydown(function(e) {
console.log(e.which);
    switch(e.which) {
        case 37: // left

        break;

        case 38: // up

idtr = $('.tableList').find('.lineSelected').attr('id')
posicion = 0;
if(idtr==undefined){posicion = 0}else{posicion = $('.tableList').find('.lineSelected').index()}
indice = parseInt(posicion);
$('.tableList').find('tr:eq('+indice+')').click();
return false
        break;

        case 39: // right

        break;

        case 40: // down
idtr = $('.tableList').find('.lineSelected').attr('id')
posicion = 0;
if(idtr==undefined){posicion = 0}else{posicion = $('.tableList').find('.lineSelected').index()}
indice = parseInt(posicion+2);
$('.tableList').find('tr:eq('+indice+')').click();
return false
        break;
		
        case 46: // right
$('.delete:visible').click();		

$('.tableList').find('tr:eq(1)').click();	
        break;		
		

        default: return; // exit this handler for other keys
    }
    e.preventDefault(); // prevent the default action (scroll / move caret)
});




$( document ).ready(function() {
$( "#arrow" ).click(function() {
$('#ticketLeft').toggleClass('lefthide');
$('#ticketRight').toggleClass('lefthide2');
});
});
