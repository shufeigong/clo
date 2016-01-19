var minHeight = 200;

var headerHeight=$(".header").height();
var itemId2;
function changeHeight(itemId)
{
  	//alert($(".header").height());
	//alert($(".header").height()+210+$("#" + itemId).nextAll(".contentdiv").height());
	var menuHeight=22*$("#" + itemId).parent().siblings().length+22;
	//alert(menuHeight);
	backOver(itemId);
	if(($(".header").height()+minHeight+menuHeight)<=$(window).height())
		{			
		    if($(".header").height()+menuHeight+parseInt($("#" + itemId).nextAll(".overarea").css("height"))+20>$(window).height())
		    	{
		    	   //alert(parseInt($("#" + itemId).nextAll(".overarea").css("height")));
		    	   //backOver();
		    	   setOver(menuHeight, itemId); 
		    	}
		    else{
		    	  //backOver();
		    	}
		    
		
		}
		    
	
	else{
		//backOver();
	}
	
 
}

function backOver(itemId)
{
	$("#" + itemId).parent().children(".overarea").css({"height":"auto", "overflow-y":"visible"});
	$("#" + itemId).parent().siblings().children(".overarea").css({"height":"auto", "overflow-y":"visible"});
	$("body").css("overflow-y","visible");
}



function setOver(menuHeight,itemId)
{
	var overheight=$(window).height()-menuHeight-$(".header").height()-80;
	$("#" + itemId).nextAll(".overarea").css({"height":overheight+"px","overflow-y":"scroll"});
	$("body").css("overflow-y","hidden");
}

//////////////
function setfixed(itemId)
{
	var leftv=$("#" + itemId).parent().offset().left+$("#" + itemId).width()+4;
	 var widthw=$(window).width()*0.875*0.88;
	
	 

	 /////////////header///////////////
	 
	 //if($("#"+itemId).parent().index()==0)
	//	 {
		   $(".header").css({"position":"fixed", "z-index":"100", "background-color":"#fff","height":"172px"});
	//	 }
	// else{
	//	 $(".header").css({"position":"fixed", "z-index":"100", "background-color":"#fff","height":"142px"});//make header area to be fixed
	  //    }
	 
	 if($(window).width()>=1000)
		 {
		   $(".header").css({"width":"85%", "max-width":"962px"});
		 }
	 else
		 {
		   $(".header").css("width","100%");
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
 	 
 	 if($(window).width()>=1000)
 	 { $("#" + itemId).nextAll(".blueline").css({"position":"fixed", "top":headerHeight+(currentli+1)*20, "z-index":"100", "left":leftv, "width":widthw, "max-width":"848px","border-top":"12px solid #fff" });  
 	   $("#" + itemId).nextAll(".contentdiv").css("margin-left","11%");
 	   $("#" + itemId).nextAll(".image-link").css({"position":"fixed", "bottom":(lilength-currentli)*20+2, "z-index":"100", "left":leftv+1, "width":widthw-2, "max-width":"846px"});
 	  }
 	   else
 	 {
 	    $("#" + itemId).nextAll(".blueline").css({"position":"fixed", "top":headerHeight+(currentli+1)*20, "z-index":"100", "left":leftv+8, "width":widthw, "max-width":"848px","border-top":"12px solid #fff" });  
	  	$("#" + itemId).nextAll(".contentdiv").css("margin-left","14%");
	  	$("#" + itemId).nextAll(".image-link").css({"position":"fixed", "bottom":(lilength-currentli)*20, "z-index":"100", "left":leftv+8, "width":widthw, "max-width":"846px"});
 	 }
 	 
 	 /*if($("#"+itemId).nextAll(".menudiv").height()<$("#"+itemId).nextAll(".contentdiv").height()){
 		$("#"+itemId).nextAll(".menudiv").css({"position":"fixed", "left":leftv+20+$("#" + itemId).nextAll(".contentdiv").outerWidth()});
 	 }*/
 	 
 	 
 	 $(window).resize( itemId, function(event){
		 var leftv=$("#"+event.data).parent().offset().left+$("#"+event.data).width()+4;
   	 var widthw=$(window).width()*0.875*0.88;
   	 
   	 
   	 if($(window).width()>=1000)
		 {
   		 $(".header").css("width","85%");
   		 $("#" + event.data).nextAll(".contentdiv").css("margin-left","11%");
   		 $("#" + event.data).nextAll(".blueline").css({"position":"fixed", "top":headerHeight+(currentli+1)*20, "z-index":"100", "left":leftv, "width":widthw, "border-top":"12px solid #fff" });
	    	 $("#" + event.data).nextAll(".image-link").css({"position":"fixed", "bottom":(lilength-currentli)*20+2, "z-index":"100", "left":leftv+1, "width":widthw-2});
	    	 //$("#"+event.data).nextAll(".menudiv").css({"position":"fixed", "left":leftv+20+$("#" + event.data).nextAll(".contentdiv").outerWidth()})
		 
		 }
	     else
		 {
	    	 $(".header").css("width","100%");
	    	 $("#" + event.data).nextAll(".contentdiv").css("margin-left","14%");
	    	 $("#" + event.data).nextAll(".blueline").css({"position":"fixed", "top":headerHeight+(currentli+1)*20, "z-index":"100", "left":leftv+8, "width":widthw, "border-top":"12px solid #fff" });
	    	 $("#" + event.data).nextAll(".image-link").css({"position":"fixed", "bottom":(lilength-currentli)*20, "z-index":"100", "left":leftv+8, "width":widthw});
	    	 //$("#"+event.data).nextAll(".menudiv").css({"position":"fixed", "left":leftv+20+$("#" + event.data).nextAll(".contentdiv").outerWidth()})
		 }
   	 
	  } );
}

function setfixedFr(itemId)
{
	var leftv=$("#" + itemId).parent().offset().left+$("#" + itemId).width()+4;
	var widthw=$(window).width()*0.934*0.79;
	
	 

	 /////////////header///////////////
	 
	 //if($("#"+itemId).parent().index()==0)
	//	 {
		   $(".header").css({"position":"fixed", "z-index":"100", "background-color":"#fff","height":"172px"});
	//	 }
	// else{
	//	 $(".header").css({"position":"fixed", "z-index":"100", "background-color":"#fff","height":"142px"});//make header area to be fixed
	//      }
	 
	 if($(window).width()>=1000)
		 {
		   $(".header").css({"width":"85%", "max-width":"962px"});
		 }
	 else
		 {
		   $(".header").css("width","100%");
		 }
	 
	 
	 headerHeight=142;
	 
	 
	
		//////////////prev all li/////////////////
		 $("#" + itemId).parent().prevAll().each(function(){    //relocate prev all li according to li index
			  $(this).css("top", headerHeight+($(this).index()+1)*20);
		  });
		                                                        //make prev all li to be fixed
		 $("#" + itemId).parent().prevAll().css({"position":"fixed", "width":"93.4%", "max-width":"962px", "z-index":"100", "background-color":"#fff"});
		
		 /////////////////////////next all li////////////  
	     
		 var lilength=$("#" + itemId).parent().siblings().length;
	    
	     $("#" + itemId).parent().nextAll().each(function(){     //relocate next all li according to li index
		 $(this).css("bottom", (lilength-$(this).index())*20);
	     });
	                                                           //make prev next all li to be fixed
	     $("#" + itemId).parent().nextAll().css({"position":"fixed", "width":"93.4%", "max-width":"962px", "z-index":"100", "background-color":"#fff"});
	
	////////////current li//////////////
	   
	   
	     var currentli= $("#" + itemId).parent().index();
	    
 	 $("#" + itemId).parent().css("margin-top",headerHeight+(currentli+1)*20);
 	 $("#" + itemId).parent().css("margin-bottom",(lilength-currentli)*20+2);
 	 $("#" + itemId).css({"position":"fixed", "top":headerHeight+(currentli+1)*20,"z-index":"100"});  
 	 
 	 if($(window).width()>=1000)
 	 { $("#" + itemId).nextAll(".blueline").css({"position":"fixed", "top":headerHeight+(currentli+1)*20, "z-index":"100", "left":leftv+2, "width":widthw, "max-width":"762px","border-top":"12px solid #fff" });  
 	   $("#" + itemId).nextAll(".contentdiv").css("margin-left","16%");
 	   $("#" + itemId).nextAll(".image-link").css({"position":"fixed", "bottom":(lilength-currentli)*20+2, "z-index":"100", "left":leftv+3, "width":widthw-2, "max-width":"760px"});
 	  }
 	   else
 	 {
 	    $("#" + itemId).nextAll(".blueline").css({"position":"fixed", "top":headerHeight+(currentli+1)*20, "z-index":"100", "left":leftv+10, "width":widthw, "max-width":"762px","border-top":"12px solid #fff" });  
	  	$("#" + itemId).nextAll(".contentdiv").css("margin-left","23%");
	  	$("#" + itemId).nextAll(".image-link").css({"position":"fixed", "bottom":(lilength-currentli)*20, "z-index":"100", "left":leftv+10, "width":widthw, "max-width":"760px"});
 	 }
 	 
 	 /*if($("#"+itemId).nextAll(".menudiv").height()<$("#"+itemId).nextAll(".contentdiv").height()){
 		$("#"+itemId).nextAll(".menudiv").css({"position":"fixed", "left":leftv+20+$("#" + itemId).nextAll(".contentdiv").outerWidth()});
 	 }*/
 	 
 	 
 	 $(window).resize( itemId, function(event){
		 var leftv=$("#"+event.data).parent().offset().left+$("#"+event.data).width()+4;
   	 var widthw=$(window).width()*0.934*0.79;
   	 
   	 
   	 if($(window).width()>=1000)
		 {
   		 $(".header").css("width","85%");
   		 $("#" + event.data).nextAll(".contentdiv").css("margin-left","16%");
   		 $("#" + event.data).nextAll(".blueline").css({"position":"fixed", "top":headerHeight+(currentli+1)*20, "z-index":"100", "left":leftv+2, "width":widthw, "border-top":"12px solid #fff" });
	     $("#" + event.data).nextAll(".image-link").css({"position":"fixed", "bottom":(lilength-currentli)*20+2, "z-index":"100", "left":leftv+3, "width":widthw-2});
	    	 //$("#"+event.data).nextAll(".menudiv").css({"position":"fixed", "left":leftv+20+$("#" + event.data).nextAll(".contentdiv").outerWidth()})
		 
		 }
	     else
		 {
	    	 $(".header").css("width","100%");
	    	 $("#" + event.data).nextAll(".contentdiv").css("margin-left","23%");
	    	 $("#" + event.data).nextAll(".blueline").css({"position":"fixed", "top":headerHeight+(currentli+1)*20, "z-index":"100", "left":leftv+10, "width":widthw, "border-top":"12px solid #fff" });
	    	 $("#" + event.data).nextAll(".image-link").css({"position":"fixed", "bottom":(lilength-currentli)*20, "z-index":"100", "left":leftv+10, "width":widthw});
	    	 //$("#"+event.data).nextAll(".menudiv").css({"position":"fixed", "left":leftv+20+$("#" + event.data).nextAll(".contentdiv").outerWidth()})
		 }
   	 
	  } );
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

function backHeightFr(itemId)
{
	$(".header").css({"position":"static", "height":"auto","z-index":"auto", "width":"auto"});
	
	////////////////////recover brothers///////
	$("#" + itemId).parent().siblings().css({"position":"relative", "top":"auto", "bottom":"auto","z-index":"auto", "width":"auto", "max-width":"962px"});
	$("#" + itemId).parent().siblings().css("margin","0");
	$("#" + itemId).parent().siblings().children(".menu-link").css({"position":"static", "top":"auto"});
	$("#" + itemId).parent().siblings().children(".blueline").css({"position":"absolute", "top":"12px", "left":"150px", "width":"79%", "max-width":"848px","border-top":"none"});  
	$("#" + itemId).parent().siblings().children(".contentdiv").css("margin-left","4px");
	$("#" + itemId).parent().siblings().children(".image-link").css({"position":"absolute", "bottom":"5px", "width":"79%", "left":"150px"});
	$("#"+itemId).parent().siblings().children(".menudiv").css({"position":"static"})
	////////////////recover itself////////
	$("#" + itemId).parent().css("margin","0");
	$("#" + itemId).parent().css({"position":"relative", "top":"auto", "bottom":"auto", "z-index":"auto","width":"auto", "max-width":"962px"});
	$("#" + itemId).parent().children(".menu-link").css({"position":"static", "top":"auto"});
	$("#" + itemId).parent().children(".blueline").css({"position":"absolute", "top":"12px", "left":"150px", "width":"79%", "max-width":"848px", "border-top":"none"});  
	$("#" + itemId).parent().children(".contentdiv").css("margin-left","4px");
	$("#" + itemId).parent().children(".image-link").css({"position":"absolute", "bottom":"5px", "width":"79%", "left":"150px"});
	$("#"+itemId).parent().children(".menudiv").css({"position":"static"})
	////unbind resize event//////
	$(window).unbind("resize");
}



