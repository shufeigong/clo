function pageLoad(linkSplit, basicItems) {
    //var linkSplit = location.hash.substr(2);
    //$('img[usemap]').rwdImageMaps();

    $('area').on('click', function () {
        alert($(this).attr('alt') + ' clicked');
    });

    if (linkSplit != '') {
        if ($.inArray(linkSplit, basicItems) != -1) {
            pageRefresh(linkSplit);
            //$("#"+linkSplit).css("color", "#0075C9"); //keep selected item's text to blue
            //$("#"+linkSplit).next().children().attr("src", "/wp-content/themes/FoundationPress-child/images/"+linkSplit+"blue.svg"); //keep selected item's image to blue
        }
        else {
            //pageRefresh(linkSplit.split('/').shift());
            $.get("/wp-json/pages/" + linkSplit.split('/').pop(), function (response) {

                var content = response.content;

                grabMenu(linkSplit.split('/').shift());   //grab submenu according to itemId
                $("#" + linkSplit.split('/').shift()).nextAll(".contentdiv").html("<br/>");
                $("#" + linkSplit.split('/').shift()).nextAll(".contentdiv").append(content);
                $(".entry-title").slideUp();
                $(".entry-content").slideUp();
                $(".news-content").slideUp();
                $("#" + linkSplit.split('/').shift()).parent().siblings().children(".contentdiv").slideUp();    //close all other pages
                $("#" + linkSplit.split('/').shift()).parent().siblings().children(".menudiv").slideUp();       //close all other pages' submenu
                $("#" + linkSplit.split('/').shift()).parent().siblings().children(".blueline").css("display", "none"); //reback all other blueline none
                $("#" + linkSplit.split('/').shift()).parent().siblings().children(".menu-link").css("color", "#82BC00"); //reback all other pages' text color
                $("#" + linkSplit.split('/').shift()).parent().siblings().children(".image-link").children().attr("src", function () {
                    return this.src.replace('blue', '');
                });//reback all other pages' image
                $("#" + linkSplit.split('/').shift()).parent().siblings().children(".menu-link").mouseleave(function () {
                    hover2(this.id);
                });//leave other items' text to green
                $("#" + linkSplit.split('/').shift()).parent().siblings().children(".image-link").mouseleave(function () {
                    hover2($(this).prev().attr("id"));
                });//leave other items' image to green

                $("#" + linkSplit.split('/').shift()).nextAll(".blueline").css("display", "inline"); //create top blueline
                $("#" + linkSplit.split('/').shift()).css("color", "#0075C9"); //keep selected item's text to blue
                $("#" + linkSplit.split('/').shift()).next().children().attr("src", "/app/themes/clo_web_2015/src/img/" + linkSplit.split('/').shift() + "blue.svg"); //keep selected item's image to blue

                $("#" + linkSplit.split('/').shift()).nextAll(".contentdiv").slideDown();  //get content down
                $("#" + linkSplit.split('/').shift()).nextAll(".menudiv").slideDown(); //get submenu down
                $("#" + linkSplit.split('/').shift()).mouseleave(function () {
                    hover1(linkSplit.split('/').shift());
                }); //keep selected item's text to blue
                $("#" + linkSplit.split('/').shift()).next().mouseleave(function () {
                    hover1(linkSplit.split('/').shift());
                });  //keep selected item's image to blue

            }).fail(function () {
                alert("error");
            });
        }
    }
}
function grabPage(pageId) {
    $.get("/wp-json/pages/" + pageId, function (response) {

        var content = response.content;

        $("#" + pageId).nextAll(".contentdiv").html("<br/>");
        $("#" + pageId).nextAll(".contentdiv").append(content);
        if($(window).width()>640){$("#" + pageId).nextAll(".contentdiv").slideDown("normal",changeHeight(pageId));}
        else{$("#" + pageId).nextAll(".contentdiv").slideDown();}
        
        
        $("#" + pageId).nextAll(".menudiv").slideDown(); //get submenu down
      
       
    }).fail(function () {
        alert("error");
    });
}

function grabMenu(itemId) {
    $.get("/wp-json/menus", function (response) {
        for (var i = 0; i < response.length; i++) {
            if (response[i].slug == 'main-menu') {
                displayMenu(itemId, response[i].meta.links.self);
                break;
            }
        }
    }).fail(function () {
        alert("error");
    });
}
function UpperFirstLetter(str){
	 return str.replace(/\b\w+\b/g, function(word) {   
		   return word.substring(0,1).toUpperCase( ) +  word.substring(1);   
		 });   
	}
function displayMenu(itemId, menuUrl) {
    $.get(menuUrl, function (response) {

        var itemJsonId;
        for (var i = 0; i < response.items.length; i++) {
            if (response.items[i].attr == itemId) {
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
                        += '<li><a href="#" onclick="signclick(this); return false;" class="submenu" >[-]</a><a href="#" onclick="change(' + response.items[i].object_id + ',\'' + itemId + '\'); return false;"  id="' + response.items[i].ID + '" class="submenu">' + response.items[i].title.toUpperCase() + '</a></li>';
                }
                else {
                    output
                        += '<li><a href="#" onclick="change(' + response.items[i].object_id + ',\'' + itemId + '\'); return false;" style="padding-left:10%" id="' + response.items[i].ID + '" class="submenu">' + response.items[i].title.toUpperCase() + '</a></li>';
                }
            }
        }
        $("#" + itemId).nextAll(".menudiv").children().html(output);

        for (var i = 0; i < response.items.length; i++) {

            if (response.items[i].parent != itemJsonId && response.items[i].parent != 0) //it means this submenu is first submenus' child or grandchild
            {
                if (response.items[i].children.length > 0) {
                    $('#' + response.items[i].parent).parent().append('<ul><li><a href="#" onclick="signclick(this); return false;" class="submenu">[-]</a><a href="#" onclick="change(' + response.items[i].object_id + ',\'' + itemId + '\'); return false;" id="' + response.items[i].ID + '" class="submenu">' + UpperFirstLetter(response.items[i].title) + '</a></li></ul>');
                }
                else {
                    $('#' + response.items[i].parent).parent().append('<ul><li><a href="#" onclick="change(' + response.items[i].object_id + ',\'' + itemId + '\'); return false;" style="padding-left:10%" id="' + response.items[i].ID + '" class="submenu">' + UpperFirstLetter(response.items[i].title) + '</a></li></ul>');
                }
            }
        }

    }).fail(function () {
        alert("error");
    });
}

function change(objectId, itemId) {
    $.get("/wp-json/pages/" + objectId, function (response) {

        var content = response.content;

        $("#" + itemId).nextAll(".contentdiv").html("<br/>");
        $("#" + itemId).nextAll(".contentdiv").append(content);
        window.history.pushState(null, null, "#!" + itemId + "/" + response.slug);

    }).fail(function () {
        alert("error");
    });

}
function signclick(id) {
    $(id).nextAll("ul").slideToggle();
    if (id.text == "[+]") {
        id.text = "[-]";
    }
    else {
        id.text = "[+]";
    }
}
function contentToggle(id){
	
	$(id).parent().next().slideToggle();
	if (id.text == "[ + ]") {
        id.text =  "[ - ]";
    }
    else {
        id.text = "[ + ]";
    }
	
}


function hover1(itemId) {
    $("#" + itemId).css("color", "#0075C9");
    if(itemId=='tobehappy'||itemId=='tolove'||itemId=='tolearn'||itemId=='towork'||itemId=='tolive'||itemId=='about'||itemId=='news')
    	{$("#" + itemId).next().children().attr("src", "/app/themes/clo_web_2015/src/img/" + itemId + "blue.svg");}
    else
    	{$("#" + itemId).next().children().attr("src", "/app/themes/clo_web_2015/src/img/defaultblue.svg");}
    
}

function hover2(itemId) {
    $("#" + itemId).css("color", "#82BC00");
    if(itemId=='tobehappy'||itemId=='tolove'||itemId=='tolearn'||itemId=='towork'||itemId=='tolive'||itemId=='about'||itemId=='news')
        {$("#" + itemId).next().children().attr("src", "/app/themes/clo_web_2015/src/img/" + itemId + ".svg");}
    else
    	{$("#" + itemId).next().children().attr("src", "/app/themes/clo_web_2015/src/img/default.svg");}
}

function active1(itemId){
	$("#" + itemId).css("color", "#BBBDBF");
	if(itemId=='tobehappy'||itemId=='tolove'||itemId=='tolearn'||itemId=='towork'||itemId=='tolive'||itemId=='about'||itemId=='news')
	    {$("#" + itemId).next().children().attr("src", "/app/themes/clo_web_2015/src/img/" + itemId + "grey.svg");}
	else
		{$("#" + itemId).next().children().attr("src", "/app/themes/clo_web_2015/src/img/defaultgrey.svg");}
}
function active2(itemId){
	$("#" + itemId).css("color", "#0075C9");
	if(itemId=='tobehappy'||itemId=='tolove'||itemId=='tolearn'||itemId=='towork'||itemId=='tolive'||itemId=='about'||itemId=='news')
	     {$("#" + itemId).next().children().attr("src", "/app/themes/clo_web_2015/src/img/" + itemId + "blue.svg");}
	else
		{$("#" + itemId).next().children().attr("src", "/app/themes/clo_web_2015/src/img/defaultblue.svg");}
}
function itemClick(itemId) {
    //$(".entry-title").css("display", "none");
    //$(".entry-content").css("display", "none");
    $(".entry-title").slideUp();
    $(".entry-content").slideUp();
    $(".news-content").slideUp();
    $("#" + itemId).parent().siblings().children(".contentdiv").slideUp();    //close all other pages
    $("#" + itemId).parent().siblings().children(".menudiv").slideUp();      //close all other pages' submenu
    $("#" + itemId).parent().siblings().children(".blueline").css("display", "none"); //reback all other blueline none
    $("#" + itemId).parent().siblings().children(".menu-link").css("color", "#82BC00"); //reback all other pages' text color
    $("#" + itemId).parent().siblings().children(".image-link").children().attr("src", function () {
        return this.src.replace('blue', '');
    });//reback all other pages' image
    $("#" + itemId).parent().siblings().children(".menu-link").mouseleave(function () {
        hover2(this.id);
    });//leave other items' text to green
    $("#" + itemId).parent().siblings().children(".image-link").mouseleave(function () {
        hover2($(this).prev().attr("id"));
    });//leave other items' image to green

    //////////clear previous mass///////////
    $("#" + itemId).nextAll(".blueline").css("display", "inline"); //create top blueline
    window.history.pushState(null, null, "#!" + itemId);

    grabMenu(itemId);   //grab submenu according to itemId
    grabPage(itemId);   //grab page according to itemId
    //$("#"+itemId).next().next().slideDown();  //get content down
    //$("#"+itemId).next().next().next().slideDown(); //get submenu down
    $("#" + itemId).mouseleave(function () {
        hover1(itemId);
    }); //keep selected item's text to blue
    $("#" + itemId).next().mouseleave(function () {
        hover1(itemId);
    });  //keep selected item's image to blue
  

}
function pageRefresh(itemId) {
    $(".entry-title").slideUp();
    $(".entry-content").slideUp();
    $(".news-content").slideUp();
    $("#" + itemId).parent().siblings().children(".contentdiv").slideUp();    //close all other pages
    $("#" + itemId).parent().siblings().children(".menudiv").slideUp();       //close all other pages' submenu
    $("#" + itemId).parent().siblings().children(".blueline").css("display", "none"); //reback all other blueline none
    $("#" + itemId).parent().siblings().children(".menu-link").css("color", "#82BC00"); //reback all other pages' text color
    $("#" + itemId).parent().siblings().children(".image-link").children().attr("src", function () {
        return this.src.replace('blue', '');
    });//reback all other pages' image
    $("#" + itemId).parent().siblings().children(".menu-link").mouseleave(function () {
        hover2(this.id);
    });//leave other items' text to green
    $("#" + itemId).parent().siblings().children(".image-link").mouseleave(function () {
        hover2($(this).prev().attr("id"));
    });//leave other items' image to green

    //////////clear previous mass///////////
    $("#" + itemId).nextAll(".blueline").css("display", "inline"); //create top blueline
    $("#" + itemId).css("color", "#0075C9"); //keep selected item's text to blue
    $("#" + itemId).next().children().attr("src", "/app/themes/clo_web_2015/src/img/" + itemId + "blue.svg"); //keep selected item's image to blue

    grabMenu(itemId);   //grab submenu according to itemId
    grabPage(itemId);   //grab page according to itemId

    //$("#"+itemId).next().next().slideDown();  //get content down
    //$("#"+itemId).next().next().next().slideDown(); //get submenu down
    $("#" + itemId).mouseleave(function () {
        hover1(itemId);
    }); //keep selected item's text to blue
    $("#" + itemId).next().mouseleave(function () {
        hover1(itemId);
    });  //keep selected item's image to blue
}

function createMenu(menuUrl) {
    var linkSplit = location.hash.substr(2);
    var basicItems = new Array();

    $.get(menuUrl, function (response) {
        for (var i = 0; i < response.items.length; i++) {
            if (response.items[i].parent == 0) {
                var id = response.items[i].title.replace(/[ ]/g, "").toLowerCase();
                basicItems.push(id);

                $("#" + id).bind({
                    mouseenter: function () {
                        hover1(this.id);
                    }, mouseleave: function () {
                        hover2(this.id);
                    }
                }).bind("click", function () {
                    itemClick(this.id);
                }).bind("mousedown", function(){active1(this.id)}).bind("mouseup", function(){active2(this.id)});

                //$("#"+id).next().bind({mouseenter:function(){hover1($(this).prev().attr("id"));},mouseleave:function(){hover2($(this).prev().attr("id"));}}).bind("click", function(){itemClick($(this).prev().attr("id"));});

            }
        }

        pageLoad(linkSplit, basicItems);
        window.addEventListener('popstate', function (e) {

            var linkSplit = location.hash.substr(2);
            if (linkSplit != '') {
                pageLoad(linkSplit, basicItems)
            } else {
                location.reload();
            }
        });
    });
}

function bindEvent() {
    $.get("/wp-json/menus", function (response) {
        for (var i = 0; i < response.length; i++) {
            if (response[i].slug == 'main-menu') {
                createMenu(response[i].meta.links.self);
                break;
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
});



