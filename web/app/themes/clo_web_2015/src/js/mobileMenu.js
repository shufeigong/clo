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
    
    
    $("#gohome").click(function(){location.href="/";});
    
    if($(window).width()<641)
    	{
    	   $(".entry-content").css("padding-top", $(".mbx-dh").height()+$(".mbx-dh").offset().top-$(".header").height()+5);
    	   $("#gohome").next().children("span:last-child").children("a").css("color","#0075c9");
    	}
    
    
    
});