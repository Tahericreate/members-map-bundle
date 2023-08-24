$(document).ready(function() {	
    //partner map input toggler function
	 // Get partnermap input elements
     var $partnerinput1 = $('.haas-partner-map input[name="plz"]');
     var $partnerinput2 = $('.haas-partner-map input[name="city"]');
     
     function updateInput2Required() {
       if ($partnerinput1.val() === "") {
         $partnerinput2.prop("required", false);
       } else {
         $partnerinput2.prop("required", true);
       }
     }
     function updateInput1Required() {
       if ($partnerinput2.val() === "") {
         $partnerinput1.prop("required", false);
       } else {
         $partnerinput1.prop("required", true);
       }
     }
     
	 //default behavior
     updateInput2Required();
     updateInput1Required();
     // onchange input
     $partnerinput1.on("change", function() {
       updateInput2Required();
     });
     $partnerinput2.on("change", function() {
       updateInput1Required();
     });
});