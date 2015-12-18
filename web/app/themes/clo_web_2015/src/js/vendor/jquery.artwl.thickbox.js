/* File Created: 三月 1, 2012     Author:artwl  blog:http://artwl.cnblogs.com */
;(function ($) {
    $.extend({
        artwl_bind: function (options) {
            options=$.extend({
                showbtnid:"",
                title:"",
                content:""
                },options);
            var mask = '<div id="artwl_mask"></div>';
            var boxcontain = '<div id="artwl_boxcontain">\
                                  <a id="artwl_close" href="#" onclick="return false;" title="Close"></a>\
                                  <div id="artwl_showbox">\
                                      <div id="artwl_title">\
                                          <h1>\
                                              Title</h1>\
                                      </div>\
                                      <div id="artwl_message">\
                                          Content2<br />\
                                      </div>\
                                  </div>\
                              </div>';
     
            if ($("#artwl_mask").length == 0) {
                $("body").append(mask + boxcontain);
                //$("head").append("<style type='text/css'>" + cssCode + "</style>");
                if(options.title!=""){
                    $("#artwl_title h1").html(options.title);
                }
                if(options.content!=""){
                    $("#artwl_message").html(options.content);
                }
            }
            $("."+options.showbtnid).click(function () {
                var height = $("#artwl_boxcontain").height();
                var width = $("#artwl_boxcontain").width();
                $("#artwl_mask").show();
                $("#artwl_boxcontain").css("top", "5%").css("left", "1%").show();
                $(".row").find("a").attr("tabindex","-1");
                /*if ($.browser.msie && $.browser.version.substr(0, 1) < 7) {
                    width = $(window).width() > 600 ? 600 : $(window).width() - 40;
                    $("#artwl_boxcontain").css("width", width + "px").css("top", ($(window).height() - height) / 2).css("left", ($(window).width() - width) / 2).show();
                    $("#artwl_mask").css("width", $(window).width() + "px").css("height", $(window).height() + "px").css("background", "#888");
                    $("#artwl_close").css("top", "30px").css("right", "30px").css("font-size", "20px").text("关闭");
                }*/
            });
            $("#artwl_close").click(function () {
                $("#artwl_mask").hide();
                $("#artwl_boxcontain").hide();
                $(".row").find("a").removeAttr("tabindex");
            });
        },
        artwl_close:function(options){
            options=$.extend({
                callback:null
                },options);
            $("#artwl_mask").hide();
            $("#artwl_boxcontain").hide();
            if(options.callback!=null){
                options.callback();
            }
        }
    });
})(jQuery);