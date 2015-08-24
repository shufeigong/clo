
$(document).ready(function()
	      {
       pageLoad();
        
	$("#home").click(function(e) {   // Method when click create new task of
										// newTask form
	    e.preventDefault();                 // Prevent default method
	    
	    //alert($(this).attr('id'));
	    
	    grabPage($(this).attr('id'));
	    
	    
	   /* window.history.pushState(null, null, '#!home');
	    // location.pathname='#home';
	    // alert(location.pathname);
	    $.get("/wp-api-test/wp-json/pages?filter[name]=home", function(response) {
 		  
 		   var content=response[0].content;
 		   
 		  // $("#grabResult").remove();
 		   $("#grabResult").html(content);
	    	 
 		   // $("#grabResult").append(response[0].content);
 	    }).fail(function() {
 		    alert( "error" );
 		  });
	  */
	    });


	$("#camps").click(function(e) {   // Method when click create new task of
										// newTask form
	    e.preventDefault();                 // Prevent default method
	    grabPage($(this).attr('id'));
	  
	    });
	
	$("#ridingandparties").click(function(e) {   // Method when click create new task of
										// newTask form
	    e.preventDefault();                 // Prevent default method
	    grabPage($(this).attr('id'));
	  
	    });
	
	$("#clubsandlessons").click(function(e) {   // Method when click create new task of
										// newTask form
	    e.preventDefault();                 // Prevent default method
	    
	    grabPage($(this).attr('id'));
	  
	    });
	
	$("#registration").click(function(e) {   // Method when click create new
												// task of newTask form
	    e.preventDefault();                 // Prevent default method
	    
	    grabPage($(this).attr('id'));
	  
	    });
	
	
	　　window.addEventListener('popstate', function(e) {     
	　　　　// anchorClick(location.pathname);
	      
	      var linkSplit = location.hash.substr(2);

	      $.get("/wp-json/pages?filter[name]="+linkSplit, function(response) {
		  
		   var content=response[0].content;
		   
		  // $("#grabResult").remove();
		   $("#grabResult").html(content);
	    	 
		   // $("#grabResult").append(response[0].content);
	    }).fail(function() {
 		    alert( "error" );
		  });
	
	
	 　　});
	
	 });


function pageLoad()
{
	var linkSplit = location.hash.substr(2);
    
	if(linkSplit!='')
		{
		$.get("/wp-json/pages?filter[name]="+linkSplit, function(response) {
			  
			   var content=response[0].content;
			   
			  // $("#grabResult").remove();
			   $("#grabResult").html(content);
		    	 
			   // $("#grabResult").append(response[0].content);
		    }).fail(function() {
	 		    alert( "error" );
			  });
		}
}

function grabPage(pageId)
{
	window.history.pushState(null, null, "#!"+pageId);
     
    $.get("/wp-json/pages?filter[name]="+pageId, function(response) {
		  
		   var content=response[0].content;
		   
		  // $("#grabResult").remove();
		   $("#grabResult").html(content);
    	 
		   // $("#grabResult").append(response[0].content);
	    }).fail(function() {
		    alert( "error" );
	  });
}

