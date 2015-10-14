function pageLoad(linkSplit, basicItems) {
	//$.cookie('the_cookie', "", { expires: -1 });
    if (linkSplit != '') {
        if ($.inArray(linkSplit[1], basicItems) != -1 && location.hash == '') {
            pageRefresh(linkSplit[1]);
        }
        else if ($.inArray(linkSplit[1], basicItems) != -1 && location.hash != '') {

            $.get("/wp-json/pages/" + location.hash.substr(1), function (response) {

                var content = response.content;

                grabMenu(linkSplit[1]);   //grab submenu according to itemId
                $("#" + linkSplit[1]).nextAll(".contentdiv").html("<br/>");
                $("#" + linkSplit[1]).nextAll(".contentdiv").append(content);
                $(".entry-title").css("display","none");
                $(".entry-content").css("display","none");
                $(".news-content").css("display","none");
                $("#" + linkSplit[1]).parent().siblings().children(".contentdiv").slideUp();    //close all other pages
                $("#" + linkSplit[1]).parent().siblings().children(".menudiv").slideUp();       //close all other pages' submenu
                $("#" + linkSplit[1]).nextAll(".menudiv").slideDown(); //get submenu down
                $("#" + linkSplit[1]).nextAll(".contentdiv").slideDown("normal",changeHeight(linkSplit[1]));  //get content down
                
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
        $(".entry-content-mobile").html(content);
        if ($(window).width() > 640) {
            $("#" + pageId).nextAll(".contentdiv").slideDown("normal", changeHeight(pageId));
        }
        else {
            $("#" + pageId).nextAll(".contentdiv").slideDown();
        }

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
                    $.get("/setSession.php?open=0").fail(function () {
                        alert("error");
                    });
                }else{
                	$.get("/setSession.php?open=1").fail(function () {
                        alert("error");
                    });
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
                        += '<li style="line-height:0.8; margin-bottom:15px;"><a href="#" onclick="signclick(this); return false;" class="submenu" style="width:10%;float:left;">[+]</a><a href="#" onclick="change(' + response.items[i].object_id + ',\'' + itemId + '\'); return false;"  id="' + response.items[i].ID + '" class="submenu" style="margin-bottom:15px; width:90%;float:left;">' + response.items[i].title.toUpperCase() + '</a></li>';
                }
                else {
                    output
                        += '<li style="margin-left:10%;line-height:0.8;margin-bottom:15px;"><a href="#" onclick="change(' + response.items[i].object_id + ',\'' + itemId + '\'); return false;"  id="' + response.items[i].ID + '" class="submenu" >' + response.items[i].title.toUpperCase() + '</a></li>';
                }
            }
        }
        $("#" + itemId).nextAll(".menudiv").children().html(output);

        for (var i = 0; i < response.items.length; i++) {

            if (response.items[i].parent != itemJsonId && response.items[i].parent != 0) //it means this submenu is first submenus' child or grandchild
            {
                if (response.items[i].children.length > 0) {
                    $('#' + response.items[i].parent).parent().append('<ul style="margin-top:15px;display:none;"><li style="line-height:0.8;"><a href="#" onclick="signclick(this); return false;" class="submenu" style="width:10%;float:left;">[+]</a><a href="#" onclick="change(' + response.items[i].object_id + ',\'' + itemId + '\'); return false;" id="' + response.items[i].ID + '" class="submenu" style="margin-bottom:15px;width:90%;float:left;">' + UpperFirstLetter(response.items[i].title) + '</a></li></ul>');
                }
                else {
                    $('#' + response.items[i].parent).parent().append('<ul style="margin-top:15px;display:none;"><li style="margin-left:10%;line-height:0.8;"><a href="#" onclick="change(' + response.items[i].object_id + ',\'' + itemId + '\'); return false;"  id="' + response.items[i].ID + '" class="submenu">' + UpperFirstLetter(response.items[i].title) + '</a></li></ul>');
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
    var linkSplit = location.hash.substr(2);
    if (linkSplit != '') {
        $(id).parent().next().slideToggle();
        if (id.text == "[ + ]") {
            id.text = "[ - ]";
        }
        else {
            id.text = "[ + ]";
        }
    } else {
        if (id.text == "[ + ]") {
            id.text = "[ - ]";
        }
        else {
            id.text = "[ + ]";
        }
    }
}

function itemClick(itemId) {
    $(".entry-title").slideUp();
    $(".entry-content").slideUp();
    $(".news-content").slideUp();
    $.get("/setSession.php?open=1").fail(function () {
        alert("error");
    });
    
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
    //}

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
    });
}

function bindEvent() {
    $.get("/wp-json/menus", function (response) {
        for (var i = 0; i < response.length; i++) {
            if (response[i].slug == 'main-menu') {
                createMenu(response[i].meta.links.self);
            }
            if (response[i].slug == 'main-menu-french') {
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
    
    $(".header").click(function(){$.get("/setSession.php?open=0").fail(function () {
        alert("error");
    });});
    
});


