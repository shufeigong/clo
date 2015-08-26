function pageLoad(linkSplit, basicItems)
{
	//var linkSplit = location.hash.substr(2);
    
	if(linkSplit!='')
		{ 
		      if($.inArray(linkSplit, basicItems)!=-1)
		      {
			      pageRefresh(linkSplit);
				  //$("#"+linkSplit).css("color", "#0075C9"); //keep selected item's text to blue
				  //$("#"+linkSplit).next().children().attr("src", "/wp-content/themes/FoundationPress-child/images/"+linkSplit+"blue.png"); //keep selected item's image to blue
		      }
		      else
		      {
		    	  //pageRefresh(linkSplit.split('/').shift());
		    	  $.get("/wp-json/pages/"+linkSplit.split('/').pop(), function(response) {
		    		  
		   		   var content=response.content;
		   		   $("#"+linkSplit.split('/').shift()).next().next().html(content);
		   		   grabMenu(linkSplit.split('/').shift());   //grab submenu according to itemId
		   		    $(".entry-title").slideUp();
			   		$(".entry-content").slideUp();
			   		$(".dynamic-content").slideUp();
			   		$("#"+linkSplit.split('/').shift()).parent().siblings().children(".contentdiv").slideUp();    //close all other pages
			   		$("#"+linkSplit.split('/').shift()).parent().siblings().children(".menudiv").slideUp();       //close all other pages' submenu
			   		$("#"+linkSplit.split('/').shift()).parent().siblings().children(".menu-link").css("color", "#82BC00"); //reback all other pages' text color
			   		$("#"+linkSplit.split('/').shift()).parent().siblings().children(".image-link").children().attr("src", function(){return this.src.replace('blue', '');});//reback all other pages' image
			   		$("#"+linkSplit.split('/').shift()).parent().siblings().children(".menu-link").mouseleave(function(){hover2(this.id);});//leave other items' text to green
			   		$("#"+linkSplit.split('/').shift()).parent().siblings().children(".image-link").mouseleave(function(){hover2($(this).prev().attr("id"));});//leave other items' image to green
			   		
			   		$("#"+linkSplit.split('/').shift()).next().next().css("border-top", "solid 4px #0075C9"); //create top border
			   		$("#"+linkSplit.split('/').shift()).css("color", "#0075C9"); //keep selected item's text to blue
			   	    $("#"+linkSplit.split('/').shift()).next().children().attr("src", "/app/themes/clo_web_2015/src/img/"+linkSplit.split('/').shift()+"blue.png"); //keep selected item's image to blue
			   		
		   		    
			   		$("#"+linkSplit.split('/').shift()).next().next().slideDown();  //get content down
			   	    $("#"+linkSplit.split('/').shift()).next().next().next().slideDown(); //get submenu down
			   	    $("#"+linkSplit.split('/').shift()).mouseleave(function(){hover1(linkSplit.split('/').shift());}); //keep selected item's text to blue
			   	    $("#"+linkSplit.split('/').shift()).next().mouseleave(function(){hover1(linkSplit.split('/').shift());});  //keep selected item's image to blue
		   		   
		   		   
		   	    }).fail(function() {
		   		    alert( "error" );
		   	  });
		      }
		}
}
function grabPage(pageId)
{
	
     
    $.get("/wp-json/pages/"+pageId, function(response) {
		  
		   var content=response.content;
		   
		
		   $("#"+pageId).next().next().html(content);
		   $("#"+pageId).next().next().slideDown();  //get content down
		   $("#"+pageId).next().next().next().slideDown(); //get submenu down
		 
	    }).fail(function() {
		    alert( "error" );
	  });
}


function grabMenu(itemId)
{
	$.get("/wp-json/menus", function(response){
		for(var i=0; i<response.length; i++)
			{
			   if(response[i].name=='topnav')
				   {displayMenu(itemId, response[i].meta.links.self); break;}
			}
	}).fail(function(){alert("error");});
}

function displayMenu(itemId, menuUrl)
{
   $.get(menuUrl, function(response){

	  var itemJsonId;
	   for(var i=0; i<response.items.length; i++)
		  {
		      if(response.items[i].attr==itemId)
		    	 { itemJsonId=response.items[i].ID;}
		      
		      response.items[i].children=new Array();
		      
		      for(var j=i+1; j<response.items.length; j++)
		    	  {
		    	     if(response.items[j].parent==response.items[i].ID)
		    	    	 {response.items[i].children.push(response.items[j].ID);}
		    	  }
		  }
	  
	   
	  var output=''; 
	  for(var i=0; i<response.items.length; i++)
		  {
		     if(response.items[i].parent==itemJsonId)
		    	 {
		    	   if(response.items[i].children.length>0)
		    	       {output+='<li><a href="#" onclick="signclick(this); return false;" class="submenu" >[-]</a><a href="#" onclick="change('+response.items[i].object_id+',\''+itemId+'\'); return false;"  id="'+response.items[i].ID+'" class="submenu">'+response.items[i].title+'</a></li>';}
		    	   else
		    		   {output+='<li><a href="#" onclick="change('+response.items[i].object_id+',\''+itemId+'\'); return false;" style="padding-left:10%" id="'+response.items[i].ID+'" class="submenu">'+response.items[i].title+'</a></li>';}
		    	 }
		  }
	  $("#"+itemId).next().next().next().children().html(output);
	   
	  
	  for(var i=0; i<response.items.length; i++)
	  {
	     
		  if(response.items[i].parent!=itemJsonId&&response.items[i].parent!=0) //it means this submenu is first submenus' child or grandchild
	    	 {
			  if(response.items[i].children.length>0)
			      {$('#'+response.items[i].parent).parent().append('<ul><li><a href="#" onclick="signclick(this); return false;" class="submenu">[-]</a><a href="#" onclick="change('+response.items[i].object_id+',\''+itemId+'\'); return false;" id="'+response.items[i].ID+'" class="submenu">'+response.items[i].title+'</a></li></ul>');}
			  else
				  {$('#'+response.items[i].parent).parent().append('<ul><li><a href="#" onclick="change('+response.items[i].object_id+',\''+itemId+'\'); return false;" style="padding-left:10%" id="'+response.items[i].ID+'" class="submenu">'+response.items[i].title+'</a></li></ul>');}
	    	 }
	  }
	  
	  
	  
   }).fail(function(){alert("error");});
}

function change(objectId, itemId)
{
	$.get("/wp-json/pages/"+objectId, function(response) {
		  
		   var content=response.content;
		   
		  // $("#grabResult").remove();
		   $("#"+itemId).next().next().html(content);
		   window.history.pushState(null, null, "#!"+itemId+"/"+response.slug);
		   // $("#grabResult").append(response[0].content);
	    }).fail(function() {
		    alert( "error" );
	  });
	
	
}
function signclick(id)
{
	
	$(id).nextAll("ul").slideToggle();
	if(id.text=="[+]")
		{id.text="[-]";}
	else
	    {id.text="[+]";}
}

function hover1(itemId)
{
	$("#"+itemId).css("color", "#0075C9");
	$("#"+itemId).next().children().attr("src", "/app/themes/clo_web_2015/src/img/"+itemId+"blue.png");
}

function hover2(itemId)
{
	$("#"+itemId).css("color", "#82BC00");
	$("#"+itemId).next().children().attr("src", "/app/themes/clo_web_2015/src/img/"+itemId+".png");
	
}

function itemClick(itemId)
{
	//$(".entry-title").css("display", "none");
	//$(".entry-content").css("display", "none");
	$(".entry-title").slideUp();
	$(".entry-content").slideUp();
	$(".dynamic-content").slideUp();
	$("#"+itemId).parent().siblings().children(".contentdiv").slideUp();    //close all other pages
	$("#"+itemId).parent().siblings().children(".menudiv").slideUp();      //close all other pages' submenu
	$("#"+itemId).parent().siblings().children(".menu-link").css("color", "#82BC00"); //reback all other pages' text color
	$("#"+itemId).parent().siblings().children(".image-link").children().attr("src", function(){return this.src.replace('blue', '');});//reback all other pages' image
	$("#"+itemId).parent().siblings().children(".menu-link").mouseleave(function(){hover2(this.id);});//leave other items' text to green
	$("#"+itemId).parent().siblings().children(".image-link").mouseleave(function(){hover2($(this).prev().attr("id"));});//leave other items' image to green
	
	//////////clear previous mass///////////
	$("#"+itemId).next().next().css("border-top", "solid 4px #0075C9"); //create top border
	window.history.pushState(null, null, "#!"+itemId);
	
	grabMenu(itemId);   //grab submenu according to itemId
	grabPage(itemId);   //grab page according to itemId
    //$("#"+itemId).next().next().slideDown();  //get content down
    //$("#"+itemId).next().next().next().slideDown(); //get submenu down
    $("#"+itemId).mouseleave(function(){hover1(itemId);}); //keep selected item's text to blue
    $("#"+itemId).next().mouseleave(function(){hover1(itemId);});  //keep selected item's image to blue
	
}
function pageRefresh(itemId)
{   
	$(".entry-title").slideUp();
	$(".entry-content").slideUp();
	$(".dynamic-content").slideUp();
	$("#"+itemId).parent().siblings().children(".contentdiv").slideUp();    //close all other pages
	$("#"+itemId).parent().siblings().children(".menudiv").slideUp();       //close all other pages' submenu
	$("#"+itemId).parent().siblings().children(".menu-link").css("color", "#82BC00"); //reback all other pages' text color
	$("#"+itemId).parent().siblings().children(".image-link").children().attr("src", function(){return this.src.replace('blue', '');});//reback all other pages' image
	$("#"+itemId).parent().siblings().children(".menu-link").mouseleave(function(){hover2(this.id);});//leave other items' text to green
	$("#"+itemId).parent().siblings().children(".image-link").mouseleave(function(){hover2($(this).prev().attr("id"));});//leave other items' image to green
	
	//////////clear previous mass///////////
	$("#"+itemId).next().next().css("border-top", "solid 4px #0075C9"); //create top border
	$("#"+itemId).css("color", "#0075C9"); //keep selected item's text to blue
    $("#"+itemId).next().children().attr("src", "/app/themes/clo_web_2015/src/img/"+itemId+"blue.png"); //keep selected item's image to blue
    
    grabMenu(itemId);   //grab submenu according to itemId
    grabPage(itemId);   //grab page according to itemId
    
    //$("#"+itemId).next().next().slideDown();  //get content down
    //$("#"+itemId).next().next().next().slideDown(); //get submenu down
    $("#"+itemId).mouseleave(function(){hover1(itemId);}); //keep selected item's text to blue
    $("#"+itemId).next().mouseleave(function(){hover1(itemId);});  //keep selected item's image to blue
}



$(document).ready(function()
		{
	      
         	
	    
	     
	    var linkSplit = location.hash.substr(2);
	    var basicItems=new Array();
	   
	    
	      $.get("/wp-json/menus", function(response){
	  		for(var i=0; i<response.length; i++)
	  			{
	  			   if(response[i].name=='topnav')
	  				   {createMenu(response[i].meta.links.self); break;}
	  			}
	  	}).fail(function(){alert("error");});
	      
	      function createMenu(menuUrl)
	      {
	    	  $.get(menuUrl, function(response){
	    		  for(var i=0; i<response.items.length; i++)
	    			  {
	    			    if(response.items[i].parent==0)
	    			    	{
	    			    	   var id=response.items[i].title.replace(/[ ]/g,"").toLowerCase();
	    			    	   basicItems.push(id);
	    			    	   $("#"+id).bind({mouseenter:function(){hover1(this.id);},mouseleave:function(){hover2(this.id);}}).bind("click", function(){itemClick(this.id);});
	    			    	   $("#"+id).next().bind({mouseenter:function(){hover1($(this).prev().attr("id"));},mouseleave:function(){hover2($(this).prev().attr("id"));}}).bind("click", function(){itemClick($(this).prev().attr("id"));});
	    			    	   
	    			    	  }
	    			  }
	    		  
	    		  pageLoad(linkSplit, basicItems);
	    		 　　window.addEventListener('popstate', function(e) {     
				
					      
					      var linkSplit = location.hash.substr(2);
					   if(linkSplit!=''){
						  pageLoad(linkSplit, basicItems)
					 　　}else{location.reload();}
				          });	       
					       
	    		  
	    	  });
	      }
	      
       
					   	       
					   	       
				   	       
		});



