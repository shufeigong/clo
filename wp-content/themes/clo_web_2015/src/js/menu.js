function pageLoad(linkSplit, basicItems) {

    if (linkSplit != '') {
        if ($.inArray(linkSplit[1], basicItems) != -1 && (location.hash == ''||location.hash=='#main-content')) {//basic item, hash is empty or main-content
        	
        	if($(window).width() < 641){
        		$.get("/wp-json/pages/"+linkSplit[1], function(response){
            		var content =response.content;
            		$(".entry-content").html(content);
            	});
        	}
        	
        	pageRefresh(linkSplit[1]);
        }
        //not basic item, is subitem in side menu
        else if ($.inArray(linkSplit[1], basicItems) != -1 && location.hash != ''&& location.hash!='#main-content') {
        	
        	$("#" + linkSplit[1]).parent().siblings().children(".overarea").slideUp();
        	grabSubMenu(linkSplit[1]);
        	
            $.get("/wp-json/pages/" + location.hash.substr(1), function (response) {
            })
                .always(function(response) {
                    var content = response.content;

                    $("#" + linkSplit[1]).nextAll(".overarea").children(".contentdiv").html(content);

                    $(".entry-content-mobile").html(content);

                  //get submenu down
                    $("#" + linkSplit[1]).nextAll(".overarea").slideDown("normal",changeHeight(linkSplit[1]));
                    $("#" + linkSplit[1]).parents('ul').find('li.selected').removeClass('selected');
                    $("#" + linkSplit[1]).parent().addClass('selected');
                    
                    var newurl=$(".menu-item-language:last a").attr("href").split("#")[0]+"#"+$(location.hash).attr("otherurl");
                    if($(location.hash).length>0){$(".menu-item-language:last a").attr("href", newurl);}
                    

                    var idt;
                    var n = 0;
                    window.onresize = function () {
                        clearTimeout(idt);
                        idt = setTimeout(function () {
                            if ($(window).width() < 641) {
                                location.href = "/" + linkSplit[1];
                            }
                        }, 10);
                    };

                    $('.slvj-link-lightbox').simpleLightboxVideo();
                    if($(".btn_show").length>0){timeline()};
                    campaignMonitor();
                    album();
                    locationMap();
                })
                .fail(function () {
                alert("error");
            });
        }
        else if(linkSplit[1]!='' && $.inArray(linkSplit[1], basicItems) == -1 && location.hash == '' && $("article").hasClass("page")){//utility menu item or other pages, but exclude post type
        	$.get("/wp-json/pages/"+linkSplit[1], function(response){
        	})
            .always(function(response) {
                    var content =response.content;
                    if($(".entry-content2").length<1){$(".entry-content").html(content).slideDown();}
                    else{$(".entry-content2").slideDown();}
                    
                    
                    $('.slvj-link-lightbox').simpleLightboxVideo();
                    if($(".btn_show").length>0){timeline()};
                    campaignMonitor();
                    album();
                    locationMap();
                });
        }
        else if(linkSplit[1]==''){//home page, no /
        	if($(".menu-main-menu-container").length>0)
        		{
	        		$.get("/wp-json/pages/home", function(response){
	            	})
                    .always(function(response) {
                            var content =response.content;
                            $(".entry-content").html(content).slideDown("normal", function(){
                            	$("#rollArea").jCarouselLite({
                            		vertical: true,
                            		hoverPause:true,
                            		visible: 2,
                            		auto:500,
                            		speed:parseInt($("#myspeed").attr("speed"))
                            	});
                            });
                            
                            $('.slvj-link-lightbox').simpleLightboxVideo();
                            if($(".btn_show").length>0){timeline()};
                            campaignMonitor();
                            album();
                            locationMap();

                        });
        		}
        	else{
        		//accueil
            	$.get("/wp-json/pages/accueil", function(response){
            	})
                .always(function(response) {
                        var content =response.content;
                        $(".entry-content").html(content).slideDown("normal", function(){
                        	$("#rollArea").jCarouselLite({
                        		vertical: true,
                        		hoverPause:true,
                        		visible: 2,
                        		auto:500,
                        		speed:parseInt($("#myspeed").attr("speed"))
                        	});
                        });

                        $('.slvj-link-lightbox').simpleLightboxVideo();
                        if($(".btn_show").length>0){timeline()};
                        campaignMonitor();
                        album();
                        locationMap();
                    });
        	}
        }else{$(".entry-content").slideDown();}
    }
}
var lastopen;
function grabPage(pageId) {

    if(itemFlagArr[pageId]==false){
    	
    $.get("/wp-json/pages/" + pageId, function (response) {
    })
        .always(function(response) {
            var content = response.content;
            
            itemFlagArr[pageId]=content;
            lastopen=pageId;
            $("#" + pageId).nextAll(".overarea").children(".contentdiv").html(content).parent(".overarea").delay(300).slideDown("normal", changeHeight(pageId));
            $(".entry-content-mobile").html(content);
            
            $("#" + pageId).parents('ul').find('li.selected').removeClass('selected');
            $("#" + pageId).parent().addClass('selected');

            var idt;
            var n = 0;
            window.onresize = function () {
                clearTimeout(idt);
                idt = setTimeout(function () {
                    if ($(window).width() < 641) {
                        location.href = "/" + pageId;

                    }
                }, 10);
            };

            $('.slvj-link-lightbox').simpleLightboxVideo();
            if($(".btn_show").length>0){timeline()};
            campaignMonitor();
            album();
            locationMap();
        })
        .fail(function () {
        alert("error");
    });
    }else{
         if(lastopen!=pageId){
         lastopen=pageId;	 
         $("#" + pageId).nextAll(".overarea").children(".contentdiv").html(itemFlagArr[pageId]);
         $(".entry-content-mobile").html(itemFlagArr[pageId]);
         
         $("#" + pageId).nextAll(".overarea").slideUp().delay(300).slideDown("normal", changeHeight(pageId));

         $("#" + pageId).parents('ul').find('li.selected').removeClass('selected');
         $("#" + pageId).parent().addClass('selected');

         var idt;
         var n = 0;
         window.onresize = function () {
             clearTimeout(idt);
             idt = setTimeout(function () {
                 if ($(window).width() < 641) {
                     location.href = "/" + pageId;

                 }
             }, 10);
         };

         $('.slvj-link-lightbox').simpleLightboxVideo();
         if($(".btn_show").length>0){timeline()};
         campaignMonitor();
         album();
         locationMap();}
    }
}

function grabSubMenu(itemId) {
	if(menuContainer==false){
	    $.get("/wp-json/menus", function (response) {
	    })
	        .done(function(response) {
	        	menuContainer = response;
	            for (var i = 0; i < response.length; i++) {
	                if (response[i].slug == 'main-menu' && $("#" + itemId).parents(".menu-main-menu-container").length > 0) {
	                    displayMenu(itemId, response[i].meta.links.self);
	
	                }
	                if (response[i].slug == 'main-menu-french' && $("#" + itemId).parents(".menu-main-menu-french-container").length > 0) {
	                    displayMenu(itemId, response[i].meta.links.self + '?lang=fr');
	
	                }
	
	            }
	        })
	        .fail(function () {
	        alert("error");
	    });
	}else{
		for (var i = 0; i < menuContainer.length; i++) {
            if (menuContainer[i].slug == 'main-menu' && $("#" + itemId).parents(".menu-main-menu-container").length > 0) {
                displayMenu(itemId, menuContainer[i].meta.links.self);

            }
            if (menuContainer[i].slug == 'main-menu-french' && $("#" + itemId).parents(".menu-main-menu-french-container").length > 0) {
                displayMenu(itemId, menuContainer[i].meta.links.self + '?lang=fr');

            }

        }
		
	}
}

function displayMenu(itemId, menuUrl) {
    if(subMenuLoad==false){
	    $.get(menuUrl, function (response) {
	    })
	        .done(function(response) {
	        	
	        	subMenuLoad=response;
	            var itemJsonId;
	            for (var i = 0; i < response.items.length; i++) {
	                if (response.items[i].url.split('/')[3].split('?')[0] == itemId) {
	                    itemJsonId = response.items[i].ID;
	                }
	
	                response.items[i].children = new Array();
	
	                for (var j = i + 1; j < response.items.length; j++) {
	                    if (response.items[j].parent == response.items[i].ID) {
	                        response.items[i].children.push(response.items[j].ID);
	                    }
	                }
	            }
	
	            var output = '';
	            for (var i = 0; i < response.items.length; i++) {
	                if (response.items[i].parent == itemJsonId) {
	                    if (response.items[i].children.length > 0) {
	                        output
	                            += '<li style="line-height:1; margin-bottom:15px;"><a href="#" onclick="signclick(this); return false;" class="submenu" style="width:10%;float:left;">[+]</a><a href="#" onclick="change(' + response.items[i].object_id + ',\'' + itemId + '\', '+response.items[i].ID+'); return false;"  id="' + response.items[i].ID + '" class="submenu" style="width:90%;display:inline-block;" slug="'+response.items[i].url.split('/')[3].split('?')[0]+'">' + response.items[i].title.toUpperCase() + '</a></li>';
	                    }
	                    else {
	                        output
	                            += '<li style="margin-left:10%;line-height:1;margin-bottom:15px;"><a href="#" onclick="change(' + response.items[i].object_id + ',\'' + itemId + '\', '+response.items[i].ID+'); return false;"  id="' + response.items[i].ID + '" class="submenu" slug="'+response.items[i].url.split('/')[3].split('?')[0]+'">' + response.items[i].title.toUpperCase() + '</a></li>';
	                    }
	                }
	            }
	            $("#" + itemId).nextAll(".overarea").children(".menudiv").children().html(output);
	
	            for (var i = 0; i < response.items.length; i++) {
	
	                if (response.items[i].parent != itemJsonId && response.items[i].parent != 0) //it means this submenu is first submenus' child or grandchild
	                {
	                    if (response.items[i].children.length > 0) {
	                    	$("#" + itemId).nextAll(".overarea").children(".menudiv").children().find('#' + response.items[i].parent).parent().append('<ul style="margin-top:15px;" slug="0"><li style="line-height:1;"><a href="#" onclick="signclick(this); return false;" class="submenu" style="width:10%;float:left;">[+]</a><a href="#" onclick="change(' + response.items[i].object_id + ',\'' + itemId + '\', '+response.items[i].ID+'); return false;" id="' + response.items[i].ID + '" class="submenu" style="width:90%;display:inline-block;text-transform:capitalize;" slug="'+response.items[i].url.split('/')[3].split('?')[0]+'">' + response.items[i].title+ '</a></li></ul>');
	                    }
	                    else {
	                    	$("#" + itemId).nextAll(".overarea").children(".menudiv").children().find('#' + response.items[i].parent).parent().append('<ul style="margin-top:15px;" slug="0"><li style="margin-left:10%;line-height:1;"><a href="#" onclick="change(' + response.items[i].object_id + ',\'' + itemId + '\', '+response.items[i].ID+'); return false;"  id="' + response.items[i].ID + '" class="submenu"  style="text-transform:capitalize;" slug="'+response.items[i].url.split('/')[3].split('?')[0]+'">' + response.items[i].title + '</a></li></ul>');
	                    }
	                }
	            }
	            if(location.hash.substr(1)!=""){
	                $("[slug="+location.hash.substr(1)+"]").nextAll("ul").css("display","block");
	
	                $("[slug="+location.hash.substr(1)+"]").prev().html("[–]");
	
	                $("[slug="+location.hash.substr(1)+"]").css("color","#0075c9");
	
	                $("[slug="+location.hash.substr(1)+"]").parents("ul[slug]").css("display","block");
	                $("[slug="+location.hash.substr(1)+"]").parents("ul[slug]").siblings("ul").css("display","block");
	
	                $("[slug="+location.hash.substr(1)+"]").parents("ul[slug]").each(function(){
	                    $(this).parent("li").children("a:first").html("[–]");});
	
	            }
	
	        })
	        .fail(function () {
	        alert("error");
	    });
    }else{
    	 var itemJsonId;
         for (var i = 0; i < subMenuLoad.items.length; i++) {
             if (subMenuLoad.items[i].url.split('/')[3].split('?')[0] == itemId) {
                 itemJsonId = subMenuLoad.items[i].ID;
             }

             subMenuLoad.items[i].children = new Array();

             for (var j = i + 1; j < subMenuLoad.items.length; j++) {
                 if (subMenuLoad.items[j].parent == subMenuLoad.items[i].ID) {
                	 subMenuLoad.items[i].children.push(subMenuLoad.items[j].ID);
                 }
             }
         }

         var output = '';
         for (var i = 0; i < subMenuLoad.items.length; i++) {
             if (subMenuLoad.items[i].parent == itemJsonId) {
                 if (subMenuLoad.items[i].children.length > 0) {
                     output
                         += '<li style="line-height:1; margin-bottom:15px;"><a href="#" onclick="signclick(this); return false;" class="submenu" style="width:10%;float:left;">[+]</a><a href="#" onclick="change(' + subMenuLoad.items[i].object_id + ',\'' + itemId + '\', '+subMenuLoad.items[i].ID+'); return false;"  id="' + subMenuLoad.items[i].ID + '" class="submenu" style="width:90%;display:inline-block;" slug="'+subMenuLoad.items[i].url.split('/')[3].split('?')[0]+'">' + subMenuLoad.items[i].title.toUpperCase() + '</a></li>';
                 }
                 else {
                     output
                         += '<li style="margin-left:10%;line-height:1;margin-bottom:15px;"><a href="#" onclick="change(' + subMenuLoad.items[i].object_id + ',\'' + itemId + '\', '+subMenuLoad.items[i].ID+'); return false;"  id="' + subMenuLoad.items[i].ID + '" class="submenu" slug="'+subMenuLoad.items[i].url.split('/')[3].split('?')[0]+'">' + subMenuLoad.items[i].title.toUpperCase() + '</a></li>';
                 }
             }
         }
         $("#" + itemId).nextAll(".overarea").children(".menudiv").children().html(output);

         for (var i = 0; i < subMenuLoad.items.length; i++) {

             if (subMenuLoad.items[i].parent != itemJsonId && subMenuLoad.items[i].parent != 0) //it means this submenu is first submenus' child or grandchild
             {
                 if (subMenuLoad.items[i].children.length > 0) {
                 	$("#" + itemId).nextAll(".overarea").children(".menudiv").children().find('#' + subMenuLoad.items[i].parent).parent().append('<ul style="margin-top:15px;" slug="0"><li style="line-height:1;"><a href="#" onclick="signclick(this); return false;" class="submenu" style="width:10%;float:left;">[+]</a><a href="#" onclick="change(' + subMenuLoad.items[i].object_id + ',\'' + itemId + '\', '+subMenuLoad.items[i].ID+'); return false;" id="' + subMenuLoad.items[i].ID + '" class="submenu" style="width:90%;display:inline-block;text-transform:capitalize;" slug="'+subMenuLoad.items[i].url.split('/')[3].split('?')[0]+'">' + subMenuLoad.items[i].title+ '</a></li></ul>');
                 }
                 else {
                 	$("#" + itemId).nextAll(".overarea").children(".menudiv").children().find('#' + subMenuLoad.items[i].parent).parent().append('<ul style="margin-top:15px;" slug="0"><li style="margin-left:10%;line-height:1;"><a href="#" onclick="change(' + subMenuLoad.items[i].object_id + ',\'' + itemId + '\', '+subMenuLoad.items[i].ID+'); return false;"  id="' + subMenuLoad.items[i].ID + '" class="submenu"  style="text-transform:capitalize;" slug="'+subMenuLoad.items[i].url.split('/')[3].split('?')[0]+'">' + subMenuLoad.items[i].title + '</a></li></ul>');
                 }
             }
         }
         if(location.hash.substr(1)!=""){
             $("[slug="+location.hash.substr(1)+"]").nextAll("ul").css("display","block");

             $("[slug="+location.hash.substr(1)+"]").prev().html("[–]");

             $("[slug="+location.hash.substr(1)+"]").css("color","#0075c9");

             $("[slug="+location.hash.substr(1)+"]").parents("ul[slug]").css("display","block");
             $("[slug="+location.hash.substr(1)+"]").parents("ul[slug]").siblings("ul").css("display","block");

             $("[slug="+location.hash.substr(1)+"]").parents("ul[slug]").each(function(){
                 $(this).parent("li").children("a:first").html("[–]");});

         }
    }
}

function change(objectId, itemId, thisid) {
    $.get("/wp-json/pages/" + objectId, function (response) {
    })
        .done(function(response) {
            var content = response.content;

            $("#" + itemId).nextAll(".overarea").slideUp("normal", function(){
            	$(this).children(".contentdiv").html(content).parent(".overarea").delay(100).slideDown("normal", changeHeight(itemId));
            
            $("#"+thisid).prev().html("[–]");

            $(".menudiv").find("a").css("color","");
            $("#"+thisid).css("color","#0075c9");


            $("#"+thisid).nextAll("ul").slideDown();

            window.history.pushState(null, null, "/" + itemId + "/#" + response.slug);

            $('.slvj-link-lightbox').simpleLightboxVideo();
            if($(".btn_show").length>0){timeline()};
            campaignMonitor();
            album();
            locationMap();
            /////
            var newurl=$(".menu-item-language:last a").attr("href").split("#")[0]+"#"+$("#"+$("#"+thisid).attr("slug")).attr("otherurl");
            $(".menu-item-language:last a").attr("href", newurl);
            });
            
        })
        .fail(function () {
        alert("error");
    });
}

function signclick(id) {
    if (id.text == "[+]") {
        id.text = "[–]";
        
        $(id).nextAll("ul").slideToggle("slow");
    }
    else {
        id.text = "[+]"; 
        
        $(id).nextAll("ul").slideToggle("slow");
    }
}

function contentToggle(id) {
   
        $(id).parent().next().slideToggle();
        if (id.text == "[ + ]") {
            id.text = "[ - ]";
        }
        else {
            id.text = "[ + ]";
        }
   
}

function itemClick(itemId) {
    $(".entry-title").slideUp();
    $(".entry-content p,.entry-content2 p,.entry-content2 form").slideUp();
    $(".entry-content,.entry-content2,.entry-content2 form").slideUp();
    $(".news-content").css("display","none");
    $(".entry-content,.entry-content2,.entry-content2 form").css({"margin":"0px", "min-height":"0"});
    $("#" + itemId).parent().siblings().children(".overarea").slideUp();    //close all other pages
    
    //////////clear previous mass///////////
    window.history.pushState(null, null, "/" + itemId + "/");
 
	    
    	grabSubMenu(itemId);   //grab submenu according to itemId
	     
	    grabPage(itemId);     //grab page according to itemId
    
        $(".menu-item-language:last a").attr("href", $("#"+itemId).attr("otherurl"));

}
function pageRefresh(itemId) {

    $("#" + itemId).parent().siblings().children(".contentdiv").slideUp();    //close all other pages
    $("#" + itemId).parent().siblings().children(".menudiv").slideUp();       //close all other pages' submenu

    grabSubMenu(itemId);   //grab submenu according to itemId
    grabPage(itemId);   //grab page according to itemId
}

var itemFlagArr = new Array();
var menuContainer = false;
var subMenuLoad = false;

function createMenu(menuUrl) {
    var linkSplit = location.pathname.split('/');
    var basicItems = new Array();

    $.get(menuUrl, function (response) {
    })
        .done(function(response) {
            for (var i = 0; i < response.items.length; i++) {
                if (response.items[i].parent == 0) {
                    var id = response.items[i].url.split('/')[3].split('?')[0];
                    basicItems.push(id);
                    itemFlagArr[id]=false;
                    $("#" + id).bind("click", function () {
                        itemClick(this.id);
                    });
                }
            }

            pageLoad(linkSplit, basicItems);

            window.addEventListener('popstate', function (e) {

                var linkSplit = location.pathname.split('/');
                if (linkSplit != '') {
                    pageLoad(linkSplit, basicItems)
                } else {
                    location.reload();
                }
            });
        })
        .fail(function () {
        alert("error");
    });
}

function bindEvent() {
    $.get("/wp-json/menus", function (response) {
    })
        .done(function(response) {
            for (var i = 0; i < response.length; i++) {
                if (response[i].slug == 'main-menu' && $(".menu-main-menu-container").length>0) {
                    createMenu(response[i].meta.links.self);
                }
                if (response[i].slug == 'main-menu-french'&& $(".menu-main-menu-french-container").length>0) {
                    createMenu(response[i].meta.links.self + '?lang=fr');
                }
            }

        })
        .fail(function () {
        alert("error");
    });
}

function init() {
    bindEvent();
}

$(document).ready(function () {
	//parseInt($("#myspeed").attr("speed"));
    init();
    $("#skiplinks").children("a").click(function(e){
    	                                   if($(this).html()=="Skip to content"||$(this).html()=="Aller au contenu"){return false;}
                                           e.preventDefault();
                                           if($(".menu-main-menu-container").length>0){location.href="/site-map";}else{location.href="/fr-site-map";}
                                            
                                         });
    
    window.onload = externallinks;

});
/////////////////Timeline//////////////////////

function timeline()
{
	/////click event/////////
	$.artwl_bind({ showbtnid: "btn_show", title: "Community Living Ontario: Milestones", content: $("#timeline").html() });
	$(".btn_show").click(function(){
		
		$(".yearlinebox li a").each(function(){
			var el = $(this).attr('class').substr(0, 4);
			if($("#artwl_message div[yearid="+el+"]").length>0){
				
				$(this).attr('topv',$("#artwl_message div[yearid="+el+"]").position().top);
			}
			
		});
		
	});

	var isclick=false;
	
	$(".yearlinebox li a").click(function() {
		isclick=true;
		var el = $(this).attr('class').substr(0, 4);
		
		if($("#artwl_message div[yearid="+el+"]").length>0){	
			$('#artwl_showbox .bluebox').animate({
	         	scrollTop: $(this).attr('topv')
	     	}, 300, function(){isclick=false;});
			
			$(this).addClass("timeselected").next().css("visibility","visible").parent().siblings().children(".timearrow").css("visibility","hidden").prev().removeClass("timeselected");
		}
		else{
			isclick=false;
		}
		
 	});
   ////////scroll event//////
	
	$('#artwl_showbox .bluebox').scroll(function(){
		
    	var scroH = parseInt($(this).scrollTop());
    	
	    	$(".yearlinebox li a[topv]").each(function(){
	    		if(scroH>=parseInt($(this).attr("topv")) && isclick==false){ 
	        		$(this).addClass("timeselected").next().css("visibility","visible").parent().siblings().children(".timearrow").css("visibility","hidden").prev().removeClass("timeselected");
	    		}
	    	});
			
		});
	
	////////resize window////
	$(window).resize(function(){
		$('#artwl_showbox .bluebox').scrollTop(0);
		$(".yearlinebox li .1945").addClass("timeselected").next().css("visibility","visible").parent().siblings().children(".timearrow").css("visibility","hidden").prev().removeClass("timeselected");
		
		$(".yearlinebox li a").each(function(){
			var el = $(this).attr('class').substr(0, 4);
			if($("#artwl_message div[yearid="+el+"]").length>0){
				
				$(this).attr('topv',$("#artwl_message div[yearid="+el+"]").position().top);
			}
					
		});
		  
		});
}

////album/////
function album()
{
	$.album_bind({ showbtnid: "album_show"});
	//$(".imgShow").imagefill();
}


function gridClick(thisGrid)
{
	var newSrc=$(thisGrid).children().attr("src");
	var newAlt=$(thisGrid).children().attr("alt");
	var newCap=$(thisGrid).children().attr("cap");
	$(thisGrid).parents("table").find("td").each(function(){$(this).removeClass("tdSelected")});
	$(thisGrid).parent().addClass("tdSelected");
	$(thisGrid).parents(".albumGrid").nextAll(".imgShowBox").children(".imgShow").children().attr({"src":newSrc, "alt":newAlt});
	$(thisGrid).parents(".albumGrid").nextAll(".imgShowBox").children(".imgCaption").html(newCap);
}

function leftArrowClick(thisArrow)
{
	var currentIndex=parseInt($("#album_message").find("table").find(".tdSelected").children().attr("gridindex"));
	
	if(currentIndex>0){
		var prevIndex = currentIndex-1;
		
		$("#album_message").find("table").find("td").each(function(){$(this).removeClass("tdSelected")});
		
		$("#album_message").find("table").find("a[gridindex="+prevIndex+"]").parent().addClass("tdSelected");
		
		var newSrc=$("#album_message").find("table").find("a[gridindex="+prevIndex+"]").children().attr("src");
		var newAlt=$("#album_message").find("table").find("a[gridindex="+prevIndex+"]").children().attr("alt");
		var newCap=$("#album_message").find("table").find("a[gridindex="+prevIndex+"]").children().attr("cap");
		
		
		$(thisArrow).nextAll(".imgShow").children().attr({"src":newSrc, "alt":newAlt});
		$(thisArrow).nextAll(".imgCaption").html(newCap);
	}
}

function rightArrowClick(thisArrow)
{
	var currentIndex=parseInt($("#album_message").find("table").find(".tdSelected").children().attr("gridindex"));
	
	var nextIndex = currentIndex+1;
	
	if($("#album_message").find("table").find("a[gridindex="+nextIndex+"]").length>0){
		
        $("#album_message").find("table").find("td").each(function(){$(this).removeClass("tdSelected")});
		
		$("#album_message").find("table").find("a[gridindex="+nextIndex+"]").parent().addClass("tdSelected");
		
		var newSrc=$("#album_message").find("table").find("a[gridindex="+nextIndex+"]").children().attr("src");
		var newAlt=$("#album_message").find("table").find("a[gridindex="+nextIndex+"]").children().attr("alt");
		var newCap=$("#album_message").find("table").find("a[gridindex="+nextIndex+"]").children().attr("cap");
		
		
		$(thisArrow).prevAll(".imgShow").children().attr({"src":newSrc, "alt":newAlt});
		$(thisArrow).prevAll(".imgCaption").html(newCap);
		
	}
	
}
////////////Campaign Monitor Pop///////
function campaignMonitor()
{
	$('a.campaign').click(function(e) {
	    e.preventDefault(); 
	    var url= "/app/plugins/tenzing-campaign-monitor/campaignRetriever.php" + "?" + "campaignURL=" + $(this).data('url'); //
	    var myWindow = window.open(url, "MsgWindow", "width=650, height=800, scrollbars=yes");
	});
}

////////Location Map Pop///////
function locationMap(){
	$('.location_map').click(function(e) {
	    e.preventDefault(); 
	    var url= "/locationmap"; //
	    var myWindow = window.open(url, "MsgWindow", "width=650, height=800, scrollbars=yes");
	});
	
	$('.location_mapfr').click(function(e) {
	    e.preventDefault(); 
	    var url= "/locationmapfr"; //
	    var myWindow = window.open(url, "MsgWindow", "width=650, height=800, scrollbars=yes");
	});
	
}
function mapToggle(thislocation){
	$(thislocation).parents(".results_row_left_column").next().slideToggle();
	if($(thislocation).html()=="[+]")
		{$(thislocation).html("[–]");}
	else{$(thislocation).html("[+]");}
}

$("#addressSubmit").click(function(){
	$("#sl_before").css("display","inline");
});


/////////externalliks for social list////////
function externallinks() { 
	if (!document.getElementsByTagName) return; 
	var anchors = document.getElementsByTagName("a"); 
	for (var i=0; i<anchors.length; i++) { 
	    var anchor = anchors[i]; 
	   if (anchor.getAttribute("href") && 
	        anchor.getAttribute("rel") == "external") 
	      anchor.target = "_blank"; 
	} 
	} 







