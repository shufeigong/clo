function pageLoad(linkSplit, basicItems) {
	//$.cookie('the_cookie', "", { expires: -1 });
	
    if (linkSplit != '') {
        if ($.inArray(linkSplit[1], basicItems) != -1 && (location.hash == ''||location.hash=='#main-content')) {
        	
        	if($(window).width() < 641){
        		$.get("/wp-json/pages/"+linkSplit[1], function(response){
            		var content =response.content;
            		$(".entry-content").html(content);
            	});
        	}
        	
        	pageRefresh(linkSplit[1]);
        }
        else if ($.inArray(linkSplit[1], basicItems) != -1 && location.hash != ''&& location.hash!='#main-content') {
        	//alert(location.hash);
        	$("#" + linkSplit[1]).parent().siblings().children(".contentdiv").slideUp();    //close all other pages
            $("#" + linkSplit[1]).parent().siblings().children(".menudiv").slideUp();       //close all other pages' submenu
            grabMenu(linkSplit[1]);
        	
            $.get("/wp-json/pages/" + location.hash.substr(1), function (response) {

                var content = response.content;

                //grabMenu(linkSplit[1]);   //grab submenu according to itemId
                
                $("#" + linkSplit[1]).nextAll(".contentdiv").html("<br/>");
                $("#" + linkSplit[1]).nextAll(".contentdiv").append(content);
                $(".entry-content-mobile").html(content);
                //$(".entry-title").css("display","none");
                //$(".entry-content").css("display","none");
                //$(".news-content").css("display","none");
               //$("#" + linkSplit[1]).parent().siblings().children(".contentdiv").slideUp();    //close all other pages
               //$("#" + linkSplit[1]).parent().siblings().children(".menudiv").slideUp();       //close all other pages' submenu
                
                $("#" + linkSplit[1]).nextAll(".contentdiv").slideDown("normal",changeHeight(linkSplit[1]));  //get content down
                $("#" + linkSplit[1]).nextAll(".menudiv").slideDown(); //get submenu down
                
                $("#" + linkSplit[1]).parents('ul').find('li.selected').removeClass('selected');
                $("#" + linkSplit[1]).parent().addClass('selected');
                 
                
                var idt;
                var n = 0;
                window.onresize = function () {
                    clearTimeout(idt);
                    idt = setTimeout(function () {
                        if ($(window).width() < 641) {
                            location.href = "/" + linkSplit[1];
                        }
                    }, 10);
                }
               $('.slvj-link-lightbox').simpleLightboxVideo();
                
            }).fail(function () {
                alert("error");
            });
        }
        else if(linkSplit[1]!='' && $.inArray(linkSplit[1], basicItems) == -1 && location.hash == ''){
        	$.get("/wp-json/pages/"+linkSplit[1], function(response){
        		var content =response.content;
        		$(".entry-content").html(content);
        	});
        	//alert("hello");
        }
        else if(linkSplit[1]==''){
        	$.get("/wp-json/pages/home", function(response){
        		var content =response.content;
        		$(".entry-content").html(content);
        		$(".news-content").css("visibility","visible");
        	});
        	//accueil
        }
    }
}

function grabPage(pageId) {
    $.get("/wp-json/pages/" + pageId, function (response) {

        var content = response.content;

        $("#" + pageId).nextAll(".contentdiv").html("<br/>");
        $("#" + pageId).nextAll(".contentdiv").append(content);
        $(".entry-content-mobile").html(content);
        if ($(window).width() > 640) {
            $("#" + pageId).nextAll(".contentdiv").slideDown("normal", changeHeight(pageId));
        }
        else {
            $("#" + pageId).nextAll(".contentdiv").slideDown();
        }
        //alert($("#" + pageId).parent().offset().left);
        $("#" + pageId).nextAll(".menudiv").slideDown(); //get submenu down

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

    }).fail(function () {
        alert("error");
    });
}

function grabMenu(itemId) {
    $.get("/wp-json/menus", function (response) {

        for (var i = 0; i < response.length; i++) {
            if (response[i].slug == 'main-menu' && $("#" + itemId).parents(".menu-main-menu-container").length > 0) {
                displayMenu(itemId, response[i].meta.links.self);

            }
            if (response[i].slug == 'main-menu-french' && $("#" + itemId).parents(".menu-main-menu-french-container").length > 0) {
                displayMenu(itemId, response[i].meta.links.self + '?lang=fr');

            }

        }
    }).fail(function () {
        alert("error");
    });
    
}

function UpperFirstLetter(str) {
    return str.replace(/\b\w+\b/g, function (word) {
        return word.substring(0, 1).toUpperCase() + word.substring(1);
    });
}


function displayMenu(itemId, menuUrl) {

    $.get(menuUrl, function (response) {

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
                        += '<li style="line-height:1; margin-bottom:15px;"><a href="#" onclick="signclick(this); return false;" class="submenu" style="width:10%;float:left;">[+]</a><a href="#" onclick="change(' + response.items[i].object_id + ',\'' + itemId + '\', this); return false;"  id="' + response.items[i].ID + '" class="submenu" style="margin-bottom:15px; width:90%;float:left;" slug="'+response.items[i].url.split('/')[3]+'">' + response.items[i].title.toUpperCase() + '</a></li>';
                }
                else {
                    output
                        += '<li style="margin-left:10%;line-height:1;margin-bottom:15px;"><a href="#" onclick="change(' + response.items[i].object_id + ',\'' + itemId + '\', this); return false;"  id="' + response.items[i].ID + '" class="submenu" slug="'+response.items[i].url.split('/')[3]+'">' + response.items[i].title.toUpperCase() + '</a></li>';
                }
            }
        }
        $("#" + itemId).nextAll(".menudiv").children().html(output);

        for (var i = 0; i < response.items.length; i++) {

            if (response.items[i].parent != itemJsonId && response.items[i].parent != 0) //it means this submenu is first submenus' child or grandchild
            {
                if (response.items[i].children.length > 0) {
                    $('#' + response.items[i].parent).parent().append('<ul style="margin-top:15px;" slug=""><li style="line-height:1;"><a href="#" onclick="signclick(this); return false;" class="submenu" style="width:10%;float:left;">[+]</a><a href="#" onclick="change(' + response.items[i].object_id + ',\'' + itemId + '\', this); return false;" id="' + response.items[i].ID + '" class="submenu" style="margin-bottom:15px;width:90%;float:left;text-transform:capitalize;" slug="'+response.items[i].url.split('/')[3]+'">' + response.items[i].title+ '</a></li></ul>');
                }
                else {
                    $('#' + response.items[i].parent).parent().append('<ul style="margin-top:15px;" slug=""><li style="margin-left:10%;line-height:1;"><a href="#" onclick="change(' + response.items[i].object_id + ',\'' + itemId + '\', this); return false;"  id="' + response.items[i].ID + '" class="submenu"  style="text-transform:capitalize;" slug="'+response.items[i].url.split('/')[3]+'">' + response.items[i].title + '</a></li></ul>');
                }
            }
        }
        if(location.hash.substr(1)!=""){
        	$("[slug="+location.hash.substr(1)+"]").nextAll("ul").css("display","block");
       
        	$("[slug="+location.hash.substr(1)+"]").prev().html("[-]");
        	
        	$("[slug="+location.hash.substr(1)+"]").css("color","#0075c9");
        	
        	$("[slug="+location.hash.substr(1)+"]").parents("ul[slug]").css("display","block");
        	$("[slug="+location.hash.substr(1)+"]").parents("ul[slug]").siblings("ul").css("display","block");
        
        	$("[slug="+location.hash.substr(1)+"]").parents("ul[slug]").each(function(){
        		$(this).parent("li").children("a:first").html("[-]");});
        	
        	//parent("li").children("a:first").html("[-]");
        	
        	}
        
        //alert($("[slug=sub-about-page2]").html());
    }).fail(function () {
        alert("error");
    });
}

function change(objectId, itemId, thisid) {
    $.get("/wp-json/pages/" + objectId, function (response) {

        var content = response.content;

        $("#" + itemId).nextAll(".contentdiv").html("<br/>");
        $("#" + itemId).nextAll(".contentdiv").append(content);
        
        $(thisid).prev().html("[-]");
        
        $(".menudiv").find("a").css("color","");
        $(thisid).css("color","#0075c9");//#808083;
        
        $(thisid).nextAll("ul").css("display","block");
        
        window.history.pushState(null, null, "/" + itemId + "/#" + response.slug);
        changeHeight(itemId);
    }).fail(function () {
        alert("error");
    });

}

function signclick(id) {
    if (id.text == "[+]") {
        id.text = "[-]";
        $(id).nextAll("ul").css("display","block");
    }
    else {
        id.text = "[+]";
        $(id).nextAll("ul").css("display","none");
    }
}

function contentToggle(id) {
    //var linkSplit = location.hash.substr(2);
    //if (linkSplit != '') {
        $(id).parent().next().slideToggle();
        if (id.text == "[ + ]") {
            id.text = "[ - ]";
        }
        else {
            id.text = "[ + ]";
        }
   // } else {
    //    if (id.text == "[ + ]") {
          //  id.text = "[ - ]";
    //    }
     //   else {
     //       id.text = "[ + ]";
     //   }
    //}
}

function itemClick(itemId) {
    $(".entry-title").slideUp();
    $(".entry-content").slideUp();
    $(".news-content").slideUp();
    //$.get("/setSession.php?open=1").fail(function () {
    //    alert("error");
    //});
    //alert($("#" + itemId).parent().offset().left);
    $("#" + itemId).parent().siblings().children(".contentdiv").slideUp();    //close all other pages
    $("#" + itemId).parent().siblings().children(".menudiv").slideUp();      //close all other pages' submenu

    //////////clear previous mass///////////
    window.history.pushState(null, null, "/" + itemId + "/");

    grabMenu(itemId);   //grab submenu according to itemId
    grabPage(itemId);   //grab page according to itemId
    
    

}
function pageRefresh(itemId) {
   // if ($(window).width() > 641) {
   //     $(".entry-title").css("display","none");
   //     $(".entry-content").css("display","none");
   //     $(".news-content").css("display","none");
   // }

    $("#" + itemId).parent().siblings().children(".contentdiv").slideUp();    //close all other pages
    $("#" + itemId).parent().siblings().children(".menudiv").slideUp();       //close all other pages' submenu

    grabMenu(itemId);   //grab submenu according to itemId
    grabPage(itemId);   //grab page according to itemId
}

function createMenu(menuUrl) {
    var linkSplit = location.pathname.split('/');
    var basicItems = new Array();

    $.get(menuUrl, function (response) {
        for (var i = 0; i < response.items.length; i++) {
            if (response.items[i].parent == 0) {
                var id = response.items[i].url.split('/')[3].split('?')[0];
                basicItems.push(id);

                $("#" + id).bind({
                    mouseenter: function () {
                    }, mouseleave: function () {
                    }
                }).bind("click", function () {
                    itemClick(this.id);
                }).bind("mousedown", function () {
                }).bind("mouseup", function () {
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
    }).fail(function () {
        alert("error");
    });
}

function bindEvent() {
    $.get("/wp-json/menus", function (response) {
        for (var i = 0; i < response.length; i++) {
            if (response[i].slug == 'main-menu' && $(".menu-main-menu-container").length>0) {
                createMenu(response[i].meta.links.self);
            }
            if (response[i].slug == 'main-menu-french'&& $(".menu-main-menu-french-container").length>0) {
                createMenu(response[i].meta.links.self + '?lang=fr');
            }
        }

    }).fail(function () {
        alert("error");
    });
}

function init() {
    bindEvent();
}

$(document).ready(function () {
    init();
    $("#skiplinks").children("a").click(function(){return false;});
});


