$(document).ready(function () {
    
	

	
	//alert($(".contentdiv").height());
	
	
});

var minHeight = 200;
var menuHeight=290;
var headerHeight=$(".header").height();

function changeHeight(itemId)
{
  	
	//alert($(".header").height()+210+$("#" + itemId).nextAll(".contentdiv").height());
	if(($(".header").height()+minHeight+menuHeight)<=$(window).height())
		{			
		    if($(".header").height()+menuHeight+$("#" + itemId).nextAll(".contentdiv").height()>$(window).height())
		    	{
		    	backHeight(itemId);
		    	 $(".header").css({"position":"fixed", "z-index":"100", "background-color":"#fff"});//make header area to be fixed
		  		  
		  		//////////////prev all li/////////////////
		  		 $("#" + itemId).parent().prevAll().each(function(){    //relocate prev all li according to li index
		  			  $(this).css("top", headerHeight+50+($(this).index()+1)*20);
		  		  });
		  		                                                        //make prev all li to be fixed
		  		 $("#" + itemId).parent().prevAll().css({"position":"fixed", "width":"87.4%", "z-index":"100", "background-color":"#fff"});
		  		
		  		 /////////////////////////next all li////////////  
		  	     
		  		 var lilength=$("#" + itemId).parent().siblings().length;
		  	    
		  	     $("#" + itemId).parent().nextAll().each(function(){     //relocate next all li according to li index
	  			 $(this).css("bottom", (lilength-$(this).index())*20);
	  		     });
		  	                                                           //make prev next all li to be fixed
		  	     $("#" + itemId).parent().nextAll().css({"position":"fixed", "width":"87.4%", "z-index":"100", "background-color":"#fff"});
		    	
		    	////////////current li//////////////
		  	     var currentli= $("#" + itemId).parent().index();
		  	    
			  	 $("#" + itemId).parent().css("margin-top",headerHeight+currentli*20+58);
			  	 $("#" + itemId).parent().css("margin-bottom",(lilength-currentli)*20+2);
			  	 $("#" + itemId).css({"position":"fixed", "top":headerHeight+currentli*20+70});  
			  	 $("#" + itemId).nextAll("hr").css({"position":"fixed", "top":headerHeight+(currentli+1)*20+63});  
			  	 $("#" + itemId).nextAll(".contentdiv").css("margin-left","9%");
			  	 $("#" + itemId).nextAll(".image-link").css({"position":"fixed", "bottom":(lilength-currentli)*20+2});
		    	}
		    else{
		    	backHeight(itemId);
		    }
		     
		}
	
	else{
		//$(".header").css("position", "static");
		//$("#" + itemId).parent().prevAll().css({"position":"static", "width":"auto"});
		//alert("ss");
		//$(".header").css("position","static");
		backHeight(itemId);
	}
	
 
}

function backHeight(itemId)
{
	$(".header").css("position","static");
     //////////////prev all li////////////////
	$("#" + itemId).parent().siblings().css({"position":"relative", "top":"auto", "bottom":"auto","z-index":"auto", "width":"auto"});
	
	 /////////////////////////next all li////////////  
	$("#" + itemId).parent().css({"position":"relative", "top":"auto", "bottom":"auto", "z-index":"auto","width":"auto"});
	
	///////////////last li//////////
	$("#" + itemId).parent().siblings().css("margin","0");
	$("#" + itemId).parent().siblings().children(".menu-link").css({"position":"static", "top":"auto"});
	$("#" + itemId).parent().siblings().children("hr").css({"position":"absolute", "top":"14px"});  
	$("#" + itemId).parent().siblings().children(".contentdiv").css("margin-left","4px");
	$("#" + itemId).parent().siblings().children(".image-link").css({"position":"absolute", "bottom":"5px"});
	
	$("#" + itemId).parent().css("margin","0");
	$("#" + itemId).parent().children(".menu-link").css({"position":"static", "top":"auto"});
	$("#" + itemId).parent().children("hr").css({"position":"absolute", "top":"14px"});  
	$("#" + itemId).parent().children(".contentdiv").css("margin-left","4px");
	$("#" + itemId).parent().children(".image-link").css({"position":"absolute", "bottom":"5px"});
}




