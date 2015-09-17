$(document).ready(function () {
    $(".sub-menu").css("display","none");
    
    $(".mobileMainMenu").find("a").mouseenter(function(){$(this).css("color","#0075c9");});
    $(".mobileMainMenu").find("a").mouseleave(function(){$(this).css("color","#666666");});
    
    $(".mobileSign").click(function(){
    	
    	$(this).nextAll(".sub-menu").slideToggle();
         
    	if ($(this).text() == "[+]") {
            $(this).html('[-] ');
        }
        else {
            $(this).html('[+]');
        }
    	
    	
    	
    	
    });
    
    
    
    
    
    
    
    
});