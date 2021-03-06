﻿/* File Created: 三月 1, 2012     Author:album  blog:http://album.cnblogs.com */
;(function ($) {
    $.extend({
        album_bind: function (options) {
            options=$.extend({
                showbtnid:"",
                title:"",
                content:""
                },options);
            var mask = '<div id="album_mask"></div>';
            var boxcontain = '<div id="album_boxcontain">\
                                  <a id="album_close" href="#" onclick="return false;" title="Close"></a>\
                                  <div id="album_showbox">\
                                      <div id="album_message">\
                                          AlbumContent2<br />\
                                      </div>\
                                  </div>\
                              </div>';
     
            if ($("#album_mask").length == 0) {
                $("body").append(mask + boxcontain);
            }
            $("."+options.showbtnid).click(function () {
                
                $("#album_message").html($("#"+$(this).attr("albumid")).html());
                $("#album_mask").show();
                $("#album_boxcontain").show();
                $(".row").find("a").attr("tabindex","-1");
            });
            $("#album_close").click(function () {
                $("#album_mask").hide();
                $("#album_boxcontain").hide();
                $(".row").find("a").removeAttr("tabindex");
            });
          
            
        },
        album_close:function(options){
            options=$.extend({
                callback:null
                },options);
            $("#album_mask").hide();
            $("#album_boxcontain").hide();
            if(options.callback!=null){
                options.callback();
            }
        }
    });
})(jQuery);