webpackJsonp([0],[function(module,exports,__webpack_require__){eval("var scrollPost = __webpack_require__(1);\nvar menuAnimation = __webpack_require__(2);\n\n// Init foundation\n$(document).foundation();\n\n\n\n/*****************\n ** WEBPACK FOOTER\n ** ./src/js/app.js\n ** module id = 0\n ** module chunks = 0\n **/\n//# sourceURL=webpack:///./src/js/app.js?")},function(module,exports){eval('$(document).ready(function () {\n    // Check if current page is home\n    var isHome = $(\'body\').hasClass(\'home\');\n    if (isHome) {\n    	$("#rollArea").jCarouselLite({\n    		vertical: true,\n    		hoverPause:true,\n    		visible: 2,\n    		auto:500,\n    		speed:1500\n    	});\n\n    }\n});\n\n\n(function($) {                                          // Compliant with jquery.noConflict()\n	$.fn.jCarouselLite = function(o) {\n	    o = $.extend({\n	        btnPrev: null,\n	        btnNext: null,\n	        btnGo: null,\n	        mouseWheel: false,\n	        auto: null,\n	        hoverPause: false,\n\n	        speed: 200,\n	        easing: null,\n\n	        vertical: false,\n	        circular: true,\n	        visible: 3,\n	        start: 0,\n	        scroll: 1,\n\n	        beforeStart: null,\n	        afterEnd: null\n	    }, o || {});\n\n	    return this.each(function() {                           // Returns the element collection. Chainable.\n\n	        var running = false, animCss=o.vertical?"top":"left", sizeCss=o.vertical?"height":"width";\n	        var div = $(this), ul = $("ul", div), tLi = $("li", ul), tl = tLi.size(), v = o.visible;\n\n	        if(o.circular) {\n	            ul.prepend(tLi.slice(tl-v+1).clone())\n	              .append(tLi.slice(0,o.scroll).clone());\n	            o.start += v-1;\n	        }\n\n	        var li = $("li", ul), itemLength = li.size(), curr = o.start;\n	        div.css("visibility", "visible");\n\n	        li.css({overflow: "hidden", float: o.vertical ? "none" : "left"});\n	        ul.css({margin: "0", padding: "0", position: "relative", "list-style-type": "none", "z-index": "1"});\n	        div.css({overflow: "hidden", position: "relative", "z-index": "2", left: "0px"});\n\n	        var liSize = o.vertical ? height(li) : width(li);   // Full li size(incl margin)-Used for animation\n	        var ulSize = liSize * itemLength;                   // size of full ul(total length, not just for the visible items)\n	        var divSize = liSize * v;                           // size of entire div(total length for just the visible items)\n\n	        li.css({width: li.width(), height: li.height()});\n	        ul.css(sizeCss, ulSize+"px").css(animCss, -(curr*liSize));\n\n	        div.css(sizeCss, divSize+"px");                     // Width of the DIV. length of visible images\n\n	        if(o.btnPrev) {\n	            $(o.btnPrev).click(function() {\n	                return go(curr-o.scroll);\n	            });\n	            if(o.hoverPause) {\n	                $(o.btnPrev).hover(function(){stopAuto();}, function(){startAuto();});\n	            }\n	        }\n\n\n	        if(o.btnNext) {\n	            $(o.btnNext).click(function() {\n	                return go(curr+o.scroll);\n	            });\n	            if(o.hoverPause) {\n	                $(o.btnNext).hover(function(){stopAuto();}, function(){startAuto();});\n	            }\n	        }\n\n	        if(o.btnGo)\n	            $.each(o.btnGo, function(i, val) {\n	                $(val).click(function() {\n	                    return go(o.circular ? o.visible+i : i);\n	                });\n	            });\n\n	        if(o.mouseWheel && div.mousewheel)\n	            div.mousewheel(function(e, d) {\n	                return d>0 ? go(curr-o.scroll) : go(curr+o.scroll);\n	            });\n\n	        var autoInterval;\n\n	        function startAuto() {\n	          stopAuto();\n	          autoInterval = setInterval(function() {\n	                  go(curr+o.scroll);\n	              }, o.auto+o.speed);\n	        };\n\n	        function stopAuto() {\n	            clearInterval(autoInterval);\n	        };\n\n	        if(o.auto) {\n	            if(o.hoverPause) {\n	                div.hover(function(){stopAuto();}, function(){startAuto();});\n	            }\n	            startAuto();\n	        };\n\n	        function vis() {\n	            return li.slice(curr).slice(0,v);\n	        };\n\n	        function go(to) {\n	            if(!running) {\n\n	                if(o.beforeStart)\n	                    o.beforeStart.call(this, vis());\n\n	                if(o.circular) {            // If circular we are in first or last, then goto the other end\n	                    if(to<0) {           // If before range, then go around\n	                        ul.css(animCss, -( (curr + tl) * liSize)+"px");\n	                        curr = to + tl;\n	                    } else if(to>itemLength-v) { // If beyond range, then come around\n	                        ul.css(animCss, -( (curr - tl) * liSize ) + "px" );\n	                        curr = to - tl;\n	                    } else curr = to;\n	                } else {                    // If non-circular and to points to first or last, we just return.\n	                    if(to<0 || to>itemLength-v) return;\n	                    else curr = to;\n	                }                           // If neither overrides it, the curr will still be "to" and we can proceed.\n\n	                running = true;\n\n	                ul.animate(\n	                    animCss == "left" ? { left: -(curr*liSize) } : { top: -(curr*liSize) } , o.speed, o.easing,\n	                    function() {\n	                        if(o.afterEnd)\n	                            o.afterEnd.call(this, vis());\n	                        running = false;\n	                    }\n	                );\n	                // Disable buttons when the carousel reaches the last/first, and enable when not\n	                if(!o.circular) {\n	                    $(o.btnPrev + "," + o.btnNext).removeClass("disabled");\n	                    $( (curr-o.scroll<0 && o.btnPrev)\n	                        ||\n	                       (curr+o.scroll > itemLength-v && o.btnNext)\n	                        ||\n	                       []\n	                     ).addClass("disabled");\n	                }\n\n	            }\n	            return false;\n	        };\n	    });\n	};\n\n	function css(el, prop) {\n	    return parseInt($.css(el[0], prop)) || 0;\n	};\n	function width(el) {\n	    return  el[0].offsetWidth + css(el, \'marginLeft\') + css(el, \'marginRight\');\n	};\n	function height(el) {\n	    return el[0].offsetHeight + css(el, \'marginTop\') + css(el, \'marginBottom\');\n	};\n\n	})(jQuery);/* 代码整理：懒人之家 lanrenzhijia.com */\n\n\n/////////////////////////////\nfunction setScrollPost() {\n    var singleheight = $("#rollPost").children("li").height() + 10;\n\n    $("#rollArea").css({"height": 2 * singleheight});\n\n    var textDiv = document.getElementById("rollPost");\n    var textList = textDiv.getElementsByTagName("li");\n\n    if (textList.length > 2) {\n        var textDat = textDiv.innerHTML;\n        var br = textDat.toLowerCase().indexOf("</li", textDat.toLowerCase().indexOf("</li") + 3);\n        textDiv.innerHTML = textDat + textDat + textDat.substr(0, br);\n        textDiv.style.cssText = "position:absolute; top:0";\n        var textDatH = textDiv.offsetHeight;\n        MaxRoll();\n    }\n    var minTime, maxTime, divTop, newTop = 0;\n\n    function MinRoll() {\n        newTop++;\n        if (newTop <= divTop + (1 * singleheight)) {\n            textDiv.style.top = "-" + newTop + "px";\n        } else {\n            clearInterval(minTime);\n            maxTime = setTimeout(MaxRoll, 5000);\n        }\n    }\n\n    function MaxRoll() {\n        divTop = Math.abs(parseInt(textDiv.style.top));\n        if (divTop >= 0 && divTop < textDatH - (1 * singleheight)) {\n            minTime = setInterval(MinRoll, 1);\n        } else {\n            textDiv.style.top = 0;\n            divTop = 0;\n            newTop = 0;\n            MaxRoll();\n        }\n    }\n\n    $(\'.slvj-link-lightbox\').simpleLightboxVideo();\n}\n\nfunction setScrollPost2() {\n    var singleheight = $("#rollPost").children("li").height() + 10;\n\n    $("#rollArea").css({"height": 2 * singleheight});\n\n    var textDiv2 = document.getElementById("rollPost");\n    var textList2 = textDiv2.getElementsByTagName("li");\n    if (textList2.length > 2) {\n        textDiv2.style.cssText = "position:absolute; top:0";\n    }\n}\n\n/*****************\n ** WEBPACK FOOTER\n ** ./src/js/scrollPost.js\n ** module id = 1\n ** module chunks = 0\n **/\n//# sourceURL=webpack:///./src/js/scrollPost.js?')},function(module,exports,__webpack_require__){eval("'use strict';\nvar path = __webpack_require__(3);\n\nvar image2 = {\n    bicycle_guy: $('.animation-menu li .image-link').find('.bicycle-guy').parent()\n};\n\nvar image3 = {\n    small_car: $('.animation-menu li .image-link').find('.small-car').parent(),\n    school_bus: $('.animation-menu li .image-link').find('.school-bus').parent(),\n    wheel_chair: $('.animation-menu li .image-link').find('.wheel-chair').parent(),\n    double_students: $('.animation-menu li .image-link').find('.double-students').parent()\n};\n\nvar image4 = {\n    running_man: $('.animation-menu li .image-link').find('.running-man').parent()\n};\n\nvar image5 = {\n    old_man: $('.animation-menu li .image-link').find('.old-man').parent(),\n    man_with_dog: $('.animation-menu li .image-link').find('.man-with-dog').parent(),\n    small_car2: $('.animation-menu li .image-link').find('.small-car2').parent()\n};\n\nimage2.bicycle_guy.css({\"stroke\":\"#ffffff\", \"stroke-width\":\"0.5px\", \"stroke-miterlimit\":\"10\"});\nimage3.wheel_chair.css({\"stroke\":\"#ffffff\", \"stroke-width\":\"0.5px\", \"stroke-miterlimit\":\"10\"});\nimage3.double_students.css({\"stroke\":\"#ffffff\", \"stroke-width\":\"0.5px\", \"stroke-miterlimit\":\"10\"});\nimage4.running_man.css({\"stroke\":\"#ffffff\", \"stroke-width\":\"0.5px\", \"stroke-miterlimit\":\"10\"});\nimage5.old_man.css({\"stroke\":\"#ffffff\", \"stroke-width\":\"0.5px\", \"stroke-miterlimit\":\"10\"});\nimage5.man_with_dog.css({\"stroke\":\"#ffffff\", \"stroke-width\":\"0.5px\", \"stroke-miterlimit\":\"10\"});\n\ndoAnimation(image2.bicycle_guy, 'left', false);\ndoAnimation(image3.small_car, 'right', true);\ndoAnimation(image3.school_bus, 'right', true);\ndoAnimation(image3.wheel_chair, 'right', false);\ndoAnimation(image3.double_students, 'left', false);\ndoAnimation(image4.running_man, 'left', false);\ndoAnimation(image5.old_man, 'left', false);\ndoAnimation(image5.man_with_dog, 'right', false);\ndoAnimation(image5.small_car2, 'left', true);\n\n\nvar items = [\n    {\n        element: $('.animation-menu li .image-link').find('.bicycle-guy').parent(),\n        direction: 'left',\n        isVehicle: false\n    },\n    {\n        element: $('.animation-menu li .image-link').find('.small-car').parent(),\n        direction: 'right',\n        isVehicle: true\n\n    },\n    {\n        element: $('.animation-menu li .image-link').find('.school-bus').parent(),\n        direction: 'right',\n        isVehicle: true\n\n    },\n    {\n        element: $('.animation-menu li .image-link').find('.wheel-chair').parent(),\n        direction: 'right',\n        isVehicle: false\n\n    },\n    {\n        element: $('.animation-menu li .image-link').find('.running-man').parent(),\n        direction: 'left',\n        isVehicle: false\n    },\n    {\n        element: $('.animation-menu li .image-link').find('.old-man').parent(),\n        direction: 'left',\n        isVehicle: false\n    },\n    {\n        element: $('.animation-menu li .image-link').find('.man-with-dog').parent(),\n        direction: 'right',\n        isVehicle: false\n    },\n    {\n        element: $('.animation-menu li .image-link').find('.small-car2').parent(),\n        direction: 'left',\n        isVehicle: true\n    }\n];\n\n//doAnimationV2(items);\n\nfunction percentToPixel(_elem, _perc) {\n    return (_elem.parent().outerWidth() / 100) * parseFloat(_perc);\n}\n\nfunction doAnimation(element, startDirection, isVehicle) {\n    var tl = new TimelineMax({repeat: -1, repeatDelay: 1, paused: false});\n    var duration = 0;\n    if (isVehicle) {\n        duration = Math.floor((Math.random() * 30) + 15);\n    } else {\n        duration = Math.floor((Math.random() * 40) + 35);\n    }\n\n    var startPosition = $(element).position().left;\n    var outerWidth = $(element).closest('svg').outerWidth();\n    var xPosition = 0;\n    if (startDirection == 'right') {\n        xPosition = outerWidth - startPosition;\n        tl\n            .to(element, duration, {x: xPosition + 100, delay: 1})\n            .set(element, {x: -(startPosition + 100)})\n            .to(element, duration, {x: 0});\n    } else {\n        xPosition = outerWidth - startPosition;\n        tl\n            .to(element, duration, {x: -(startPosition + 100), delay: 1})\n            .set(element, {x: outerWidth + 100})\n            .to(element, duration, {x: 0});\n    }\n}\nfunction doAnimationV2(elements) {\n    var tl = new TimelineMax({repeat: -1, repeatDelay: 1, paused: true});\n    var duration = 0;\n\n    $.each(elements, function() {\n        var element = this.element;\n        if(this.isVehicle) {\n            duration = Math.floor((Math.random() * 30) + 15);\n        } else {\n            duration = Math.floor((Math.random() * 40) + 35);\n        }\n\n        var startPosition = $(element).position().left;\n        var outerWidth = $(element).closest('svg').outerWidth();\n        if (this.direction == 'right') {\n            tl\n                .insert(TweenMax.to(element, duration, {x: outerWidth, delay: 1}))\n                .insert(TweenMax.set(element, {x: -(startPosition + 300)}))\n                .insert(TweenMax.to(element, duration, {x: 0}));\n        } else {\n            tl\n                .insert(TweenMax.to(element, duration, {x: -(startPosition + 300), delay: 1}))\n                .insert(TweenMax.set(element, {x: outerWidth}))\n                .insert(TweenMax.to(element, duration, {x: 0}));\n        }\n    });\n\n    tl.restart();\n}\n\n$('.animation-menu li a').click(function () {\n    $(this).parents('ul').find('li.selected').removeClass('selected');\n    $(this).parent().addClass('selected');\n});\n\n\n/*****************\n ** WEBPACK FOOTER\n ** ./src/js/components/menuAnimation.js\n ** module id = 2\n ** module chunks = 0\n **/\n//# sourceURL=webpack:///./src/js/components/menuAnimation.js?")},function(module,exports,__webpack_require__){eval("/* WEBPACK VAR INJECTION */(function(process) {// Copyright Joyent, Inc. and other Node contributors.\n//\n// Permission is hereby granted, free of charge, to any person obtaining a\n// copy of this software and associated documentation files (the\n// \"Software\"), to deal in the Software without restriction, including\n// without limitation the rights to use, copy, modify, merge, publish,\n// distribute, sublicense, and/or sell copies of the Software, and to permit\n// persons to whom the Software is furnished to do so, subject to the\n// following conditions:\n//\n// The above copyright notice and this permission notice shall be included\n// in all copies or substantial portions of the Software.\n//\n// THE SOFTWARE IS PROVIDED \"AS IS\", WITHOUT WARRANTY OF ANY KIND, EXPRESS\n// OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF\n// MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN\n// NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,\n// DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR\n// OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE\n// USE OR OTHER DEALINGS IN THE SOFTWARE.\n\n// resolves . and .. elements in a path array with directory names there\n// must be no slashes, empty elements, or device names (c:\\) in the array\n// (so also no leading and trailing slashes - it does not distinguish\n// relative and absolute paths)\nfunction normalizeArray(parts, allowAboveRoot) {\n  // if the path tries to go above the root, `up` ends up > 0\n  var up = 0;\n  for (var i = parts.length - 1; i >= 0; i--) {\n    var last = parts[i];\n    if (last === '.') {\n      parts.splice(i, 1);\n    } else if (last === '..') {\n      parts.splice(i, 1);\n      up++;\n    } else if (up) {\n      parts.splice(i, 1);\n      up--;\n    }\n  }\n\n  // if the path is allowed to go above the root, restore leading ..s\n  if (allowAboveRoot) {\n    for (; up--; up) {\n      parts.unshift('..');\n    }\n  }\n\n  return parts;\n}\n\n// Split a filename into [root, dir, basename, ext], unix version\n// 'root' is just a slash, or nothing.\nvar splitPathRe =\n    /^(\\/?|)([\\s\\S]*?)((?:\\.{1,2}|[^\\/]+?|)(\\.[^.\\/]*|))(?:[\\/]*)$/;\nvar splitPath = function(filename) {\n  return splitPathRe.exec(filename).slice(1);\n};\n\n// path.resolve([from ...], to)\n// posix version\nexports.resolve = function() {\n  var resolvedPath = '',\n      resolvedAbsolute = false;\n\n  for (var i = arguments.length - 1; i >= -1 && !resolvedAbsolute; i--) {\n    var path = (i >= 0) ? arguments[i] : process.cwd();\n\n    // Skip empty and invalid entries\n    if (typeof path !== 'string') {\n      throw new TypeError('Arguments to path.resolve must be strings');\n    } else if (!path) {\n      continue;\n    }\n\n    resolvedPath = path + '/' + resolvedPath;\n    resolvedAbsolute = path.charAt(0) === '/';\n  }\n\n  // At this point the path should be resolved to a full absolute path, but\n  // handle relative paths to be safe (might happen when process.cwd() fails)\n\n  // Normalize the path\n  resolvedPath = normalizeArray(filter(resolvedPath.split('/'), function(p) {\n    return !!p;\n  }), !resolvedAbsolute).join('/');\n\n  return ((resolvedAbsolute ? '/' : '') + resolvedPath) || '.';\n};\n\n// path.normalize(path)\n// posix version\nexports.normalize = function(path) {\n  var isAbsolute = exports.isAbsolute(path),\n      trailingSlash = substr(path, -1) === '/';\n\n  // Normalize the path\n  path = normalizeArray(filter(path.split('/'), function(p) {\n    return !!p;\n  }), !isAbsolute).join('/');\n\n  if (!path && !isAbsolute) {\n    path = '.';\n  }\n  if (path && trailingSlash) {\n    path += '/';\n  }\n\n  return (isAbsolute ? '/' : '') + path;\n};\n\n// posix version\nexports.isAbsolute = function(path) {\n  return path.charAt(0) === '/';\n};\n\n// posix version\nexports.join = function() {\n  var paths = Array.prototype.slice.call(arguments, 0);\n  return exports.normalize(filter(paths, function(p, index) {\n    if (typeof p !== 'string') {\n      throw new TypeError('Arguments to path.join must be strings');\n    }\n    return p;\n  }).join('/'));\n};\n\n\n// path.relative(from, to)\n// posix version\nexports.relative = function(from, to) {\n  from = exports.resolve(from).substr(1);\n  to = exports.resolve(to).substr(1);\n\n  function trim(arr) {\n    var start = 0;\n    for (; start < arr.length; start++) {\n      if (arr[start] !== '') break;\n    }\n\n    var end = arr.length - 1;\n    for (; end >= 0; end--) {\n      if (arr[end] !== '') break;\n    }\n\n    if (start > end) return [];\n    return arr.slice(start, end - start + 1);\n  }\n\n  var fromParts = trim(from.split('/'));\n  var toParts = trim(to.split('/'));\n\n  var length = Math.min(fromParts.length, toParts.length);\n  var samePartsLength = length;\n  for (var i = 0; i < length; i++) {\n    if (fromParts[i] !== toParts[i]) {\n      samePartsLength = i;\n      break;\n    }\n  }\n\n  var outputParts = [];\n  for (var i = samePartsLength; i < fromParts.length; i++) {\n    outputParts.push('..');\n  }\n\n  outputParts = outputParts.concat(toParts.slice(samePartsLength));\n\n  return outputParts.join('/');\n};\n\nexports.sep = '/';\nexports.delimiter = ':';\n\nexports.dirname = function(path) {\n  var result = splitPath(path),\n      root = result[0],\n      dir = result[1];\n\n  if (!root && !dir) {\n    // No dirname whatsoever\n    return '.';\n  }\n\n  if (dir) {\n    // It has a dirname, strip trailing slash\n    dir = dir.substr(0, dir.length - 1);\n  }\n\n  return root + dir;\n};\n\n\nexports.basename = function(path, ext) {\n  var f = splitPath(path)[2];\n  // TODO: make this comparison case-insensitive on windows?\n  if (ext && f.substr(-1 * ext.length) === ext) {\n    f = f.substr(0, f.length - ext.length);\n  }\n  return f;\n};\n\n\nexports.extname = function(path) {\n  return splitPath(path)[3];\n};\n\nfunction filter (xs, f) {\n    if (xs.filter) return xs.filter(f);\n    var res = [];\n    for (var i = 0; i < xs.length; i++) {\n        if (f(xs[i], i, xs)) res.push(xs[i]);\n    }\n    return res;\n}\n\n// String.prototype.substr - negative index don't work in IE8\nvar substr = 'ab'.substr(-1) === 'b'\n    ? function (str, start, len) { return str.substr(start, len) }\n    : function (str, start, len) {\n        if (start < 0) start = str.length + start;\n        return str.substr(start, len);\n    }\n;\n\n/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(4)))\n\n/*****************\n ** WEBPACK FOOTER\n ** (webpack)/~/node-libs-browser/~/path-browserify/index.js\n ** module id = 3\n ** module chunks = 0\n **/\n//# sourceURL=webpack:///(webpack)/~/node-libs-browser/~/path-browserify/index.js?")},function(module,exports){eval("// shim for using process in browser\n\nvar process = module.exports = {};\nvar queue = [];\nvar draining = false;\nvar currentQueue;\nvar queueIndex = -1;\n\nfunction cleanUpNextTick() {\n    draining = false;\n    if (currentQueue.length) {\n        queue = currentQueue.concat(queue);\n    } else {\n        queueIndex = -1;\n    }\n    if (queue.length) {\n        drainQueue();\n    }\n}\n\nfunction drainQueue() {\n    if (draining) {\n        return;\n    }\n    var timeout = setTimeout(cleanUpNextTick);\n    draining = true;\n\n    var len = queue.length;\n    while(len) {\n        currentQueue = queue;\n        queue = [];\n        while (++queueIndex < len) {\n            if (currentQueue) {\n                currentQueue[queueIndex].run();\n            }\n        }\n        queueIndex = -1;\n        len = queue.length;\n    }\n    currentQueue = null;\n    draining = false;\n    clearTimeout(timeout);\n}\n\nprocess.nextTick = function (fun) {\n    var args = new Array(arguments.length - 1);\n    if (arguments.length > 1) {\n        for (var i = 1; i < arguments.length; i++) {\n            args[i - 1] = arguments[i];\n        }\n    }\n    queue.push(new Item(fun, args));\n    if (queue.length === 1 && !draining) {\n        setTimeout(drainQueue, 0);\n    }\n};\n\n// v8 likes predictible objects\nfunction Item(fun, array) {\n    this.fun = fun;\n    this.array = array;\n}\nItem.prototype.run = function () {\n    this.fun.apply(null, this.array);\n};\nprocess.title = 'browser';\nprocess.browser = true;\nprocess.env = {};\nprocess.argv = [];\nprocess.version = ''; // empty string to avoid regexp issues\nprocess.versions = {};\n\nfunction noop() {}\n\nprocess.on = noop;\nprocess.addListener = noop;\nprocess.once = noop;\nprocess.off = noop;\nprocess.removeListener = noop;\nprocess.removeAllListeners = noop;\nprocess.emit = noop;\n\nprocess.binding = function (name) {\n    throw new Error('process.binding is not supported');\n};\n\nprocess.cwd = function () { return '/' };\nprocess.chdir = function (dir) {\n    throw new Error('process.chdir is not supported');\n};\nprocess.umask = function() { return 0; };\n\n\n/*****************\n ** WEBPACK FOOTER\n ** (webpack)/~/node-libs-browser/~/process/browser.js\n ** module id = 4\n ** module chunks = 0\n **/\n//# sourceURL=webpack:///(webpack)/~/node-libs-browser/~/process/browser.js?")}]);