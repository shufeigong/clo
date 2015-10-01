$(document).ready(function () {
	setScrollPost();	
});




$(window).resizeEnd({delay: 500}, function(){
	setScrollPost2();
});

function setScrollPost(){
	var singleheight = $("#rollPost").children("li").height()+10;
	
	$("#rollArea").css({"height":2*singleheight});
	
	var textDiv = document.getElementById("rollPost");
	var textList = textDiv.getElementsByTagName("li");
	if(textList.length > 2){
	var textDat = textDiv.innerHTML;
	var br = textDat.toLowerCase().indexOf("</li",textDat.toLowerCase().indexOf("</li")+3);
	//var textUp2 = textDat.substr(0,br);
	textDiv.innerHTML = textDat+textDat+textDat.substr(0,br);
	textDiv.style.cssText = "position:absolute; top:0";
	var textDatH = textDiv.offsetHeight;
	MaxRoll();
	}
	var minTime,maxTime,divTop,newTop=0;
	
	function MinRoll(){
	 newTop++;
	 if(newTop<=divTop+2*singleheight){
	   textDiv.style.top = "-" + newTop + "px";
	  }else{
	   clearInterval(minTime);
	   maxTime = setTimeout(MaxRoll,5000);
	  }
	}
	function MaxRoll(){
	  divTop = Math.abs(parseInt(textDiv.style.top));
	  if(divTop>=0 && divTop<textDatH-2*singleheight){
	   minTime = setInterval(MinRoll,1);
	  }else{
	   textDiv.style.top = 0;divTop = 0;newTop=0;MaxRoll();
	  }
	}
	
	$('.slvj-link-lightbox').simpleLightboxVideo();
}


function setScrollPost2(){
	var singleheight = $("#rollPost").children("li").height()+10;
	
	$("#rollArea").css({"height":2*singleheight});
	
	var textDiv2 = document.getElementById("rollPost");
	var textList2 = textDiv2.getElementsByTagName("li");
	if(textList2.length > 2){
	textDiv2.style.cssText = "position:absolute; top:0";
	}
	
	//$('.slvj-link-lightbox').simpleLightboxVideo();
}