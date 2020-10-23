$(document).ready(function(){

    function calculator(){
      var sum = "";
      var len;
      //var arr= [];
      var operators = ["+", "-", "*", "/"];
      var inputVal = document.getElementById("screen");
      $(".buttons2 .digit").on('click', function() {
        var num = $(this).attr('value');
        sum += num;
        //arr.push(num);
        $("#screen").html(sum);
      len = inputVal.innerHTML.split("");
      console.log(len);
        //console.log(arr);
  
      });
      $(".buttons2 .operator").on('click', function(e) {
        e.preventDefault();
        var ops = $(this).attr('value');
        sum += ops;
        //arr.push(num);
        $("#screen").html(sum);
         len = inputVal.innerHTML;
        if(/(?=(\D{2}))/g.test(sum)) {
          sum = len.substring(0, len.length - 1);
          $("#screen").html(sum);
        }
        //len = inputVal.innerHTML.split("");
          //console.log(len);
          
        //console.log(arr);
  
      });
  
  
      $("#equal").on('click', function() {
        var total =  eval(sum);
        //$("#screen").attr('value', total);
        $("#screen").html(total % 1 != 0 ? total.toFixed(2) : total);
      });
  
      $("#clear").on('click', function() {
         sum = "";
         arr = [];
          $("#screen").html(0);
      });
  
      };
            calculator();
  });
  