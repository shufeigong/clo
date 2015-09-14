$(document).ready(function () {
    
	

	
	//alert($(".contentdiv").height());
	
	
});

var minHeight = 200;
var menuHeight=290;
var headerHeight=$(".header").height();
var itemId2;
function changeHeight(itemId)
{
  	
	//alert($(".header").height()+210+$("#" + itemId).nextAll(".contentdiv").height());
	if(($(".header").height()+minHeight+menuHeight)<=$(window).height())
		{			
		    if($(".header").height()+menuHeight+$("#" + itemId).nextAll(".contentdiv").height()>$(window).height())
		    	{
		    	 backHeight(itemId);
		    	 var leftv=$("#" + itemId).parent().offset().left+$("#" + itemId).width()+4;
		    	 var widthw=$(window).width()*0.875*0.88;
		    	
		    	 

		    	 /////////////header///////////////
		    	 
		    	 if(itemId=="tobehappy")
		    		 {
		    		   $(".header").css({"position":"fixed", "z-index":"100", "background-color":"#fff","height":"172px"});
		    		 }
		    	 else{
		    		 $(".header").css({"position":"fixed", "z-index":"100", "background-color":"#fff","height":"142px"});//make header area to be fixed
		    	      }
		    	 
		    	 if($(window).width()>=1000)
		    		 {
		    		   $(".header").css({"width":"85%", "max-width":"962px"});
		    		 }
		    	 else
		    		 {
		    		   $(".header").css("width","auto");
		    		 }
		    	 
		    	 
		    	 headerHeight=142;
		    	 
		    	 
		    	
		  		//////////////prev all li/////////////////
		  		 $("#" + itemId).parent().prevAll().each(function(){    //relocate prev all li according to li index
		  			  $(this).css("top", headerHeight+($(this).index()+1)*20);
		  		  });
		  		                                                        //make prev all li to be fixed
		  		 $("#" + itemId).parent().prevAll().css({"position":"fixed", "width":"87.4%", "max-width":"962px", "z-index":"100", "background-color":"#fff"});
		  		
		  		 /////////////////////////next all li////////////  
		  	     
		  		 var lilength=$("#" + itemId).parent().siblings().length;
		  	    
		  	     $("#" + itemId).parent().nextAll().each(function(){     //relocate next all li according to li index
	  			 $(this).css("bottom", (lilength-$(this).index())*20);
	  		     });
		  	                                                           //make prev next all li to be fixed
		  	     $("#" + itemId).parent().nextAll().css({"position":"fixed", "width":"87.4%", "max-width":"962px", "z-index":"100", "background-color":"#fff"});
		    	
		    	////////////current li//////////////
		  	   
		  	   
		  	     var currentli= $("#" + itemId).parent().index();
		  	    
			  	 $("#" + itemId).parent().css("margin-top",headerHeight+(currentli+1)*20);
			  	 $("#" + itemId).parent().css("margin-bottom",(lilength-currentli)*20+2);
			  	 $("#" + itemId).css({"position":"fixed", "top":headerHeight+(currentli+1)*20,"z-index":"100"});  
			  	 $("#" + itemId).nextAll(".blueline").css({"position":"fixed", "top":headerHeight+(currentli+1)*20, "z-index":"100", "left":leftv, "width":widthw, "max-width":"848px","border-top":"12px solid #fff" });  
			  	 $("#" + itemId).nextAll(".contentdiv").css("margin-left","11%");
			  	 $("#" + itemId).nextAll(".image-link").css({"position":"fixed", "bottom":(lilength-currentli)*20+2, "z-index":"100", "left":leftv+2, "width":widthw-2, "max-width":"846px"});
			  	 $("#"+itemId).nextAll(".menudiv").css({"position":"fixed", "left":leftv+$("#" + itemId).nextAll(".contentdiv").width()})
			  	 
			  	 $(window).resize( itemId, function(event){
		    		 var leftv=$("#"+event.data).parent().offset().left+$("#"+event.data).width()+4;
			    	 var widthw=$(window).width()*0.875*0.88;
			    	 $("#" + event.data).nextAll(".blueline").css({"position":"fixed", "top":headerHeight+(currentli+1)*20, "z-index":"100", "left":leftv, "width":widthw, "border-top":"12px solid #fff" });
			    	 $("#" + event.data).nextAll(".image-link").css({"position":"fixed", "bottom":(lilength-currentli)*20+2, "z-index":"100", "left":leftv+2, "width":widthw-2});
			    	 $("#"+itemId).nextAll(".menudiv").css({"position":"fixed", "left":leftv+$("#" + itemId).nextAll(".contentdiv").width()})
			    	 if($(window).width()>=1000)
		    		 {$(".header").css("width","85%");}
		    	     else
		    		 {$(".header").css("width","auto");}
			    	 
		    	} );
		    	
		    	}
		    else{
		    	backHeight(itemId);
		    }
		     
		}
	
	else{

		backHeight(itemId);
	}
	
 
}

function backHeight(itemId)
{
	$(".header").css({"position":"static", "height":"auto","z-index":"auto", "width":"auto"});
	
	////////////////////recover brothers///////
	$("#" + itemId).parent().siblings().css({"position":"relative", "top":"auto", "bottom":"auto","z-index":"auto", "width":"auto", "max-width":"962px"});
	$("#" + itemId).parent().siblings().css("margin","0");
	$("#" + itemId).parent().siblings().children(".menu-link").css({"position":"static", "top":"auto"});
	$("#" + itemId).parent().siblings().children(".blueline").css({"position":"absolute", "top":"12px", "left":"95px", "width":"88%", "max-width":"848px","border-top":"none"});  
	$("#" + itemId).parent().siblings().children(".contentdiv").css("margin-left","4px");
	$("#" + itemId).parent().siblings().children(".image-link").css({"position":"absolute", "bottom":"5px", "width":"88%", "left":"95px"});
	$("#"+itemId).parent().siblings().children(".menudiv").css({"position":"static"})
	////////////////recover itself////////
	$("#" + itemId).parent().css("margin","0");
	$("#" + itemId).parent().css({"position":"relative", "top":"auto", "bottom":"auto", "z-index":"auto","width":"auto", "max-width":"962px"});
	$("#" + itemId).parent().children(".menu-link").css({"position":"static", "top":"auto"});
	$("#" + itemId).parent().children(".blueline").css({"position":"absolute", "top":"12px", "left":"95px", "width":"88%", "max-width":"848px", "border-top":"none"});  
	$("#" + itemId).parent().children(".contentdiv").css("margin-left","4px");
	$("#" + itemId).parent().children(".image-link").css({"position":"absolute", "bottom":"5px", "width":"88%", "left":"95px"});
	$("#"+itemId).parent().children(".menudiv").css({"position":"static"})
	////unbind resize event//////
	$(window).unbind("resize");
}





