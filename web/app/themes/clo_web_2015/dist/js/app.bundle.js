webpackJsonp([0],[function(module,exports,__webpack_require__){eval("var scrollPost = __webpack_require__(1);\nvar menuAnimation = __webpack_require__(2);\n\n// Init foundation\n$(document).foundation();\n\n\n\n/*****************\n ** WEBPACK FOOTER\n ** ./src/js/app.js\n ** module id = 0\n ** module chunks = 0\n **/\n//# sourceURL=webpack:///./src/js/app.js?")},function(module,exports){eval('$(document).ready(function () {\n    // Check if current page is home\n    var isHome = $(\'body\').hasClass(\'home\');\n    if(isHome) {\n        setScrollPost();\n\n        $(window).resizeEnd({delay: 500}, function () {\n            setScrollPost2();\n        });\n    }\n});\n\nfunction setScrollPost() {\n    var singleheight = $("#rollPost").children("li").height() + 10;\n\n    $("#rollArea").css({"height": 2 * singleheight});\n\n    var textDiv = document.getElementById("rollPost");\n    var textList = textDiv.getElementsByTagName("li");\n\n    if (textList.length > 2) {\n        var textDat = textDiv.innerHTML;\n        var br = textDat.toLowerCase().indexOf("</li", textDat.toLowerCase().indexOf("</li") + 3);\n        //var textUp2 = textDat.substr(0,br);\n        textDiv.innerHTML = textDat + textDat + textDat.substr(0, br);\n        textDiv.style.cssText = "position:absolute; top:0";\n        var textDatH = textDiv.offsetHeight;\n        MaxRoll();\n    }\n    var minTime, maxTime, divTop, newTop = 0;\n\n    function MinRoll() {\n        newTop++;\n        if (newTop <= divTop + 2 * singleheight) {\n            textDiv.style.top = "-" + newTop + "px";\n        } else {\n            clearInterval(minTime);\n            maxTime = setTimeout(MaxRoll, 5000);\n        }\n    }\n\n    function MaxRoll() {\n        divTop = Math.abs(parseInt(textDiv.style.top));\n        if (divTop >= 0 && divTop < textDatH - 2 * singleheight) {\n            minTime = setInterval(MinRoll, 1);\n        } else {\n            textDiv.style.top = 0;\n            divTop = 0;\n            newTop = 0;\n            MaxRoll();\n        }\n    }\n\n    $(\'.slvj-link-lightbox\').simpleLightboxVideo();\n}\n\nfunction setScrollPost2() {\n    var singleheight = $("#rollPost").children("li").height() + 10;\n\n    $("#rollArea").css({"height": 2 * singleheight});\n\n    var textDiv2 = document.getElementById("rollPost");\n    var textList2 = textDiv2.getElementsByTagName("li");\n    if (textList2.length > 2) {\n        textDiv2.style.cssText = "position:absolute; top:0";\n    }\n\n    //$(\'.slvj-link-lightbox\').simpleLightboxVideo();\n}\n\n/*****************\n ** WEBPACK FOOTER\n ** ./src/js/scrollPost.js\n ** module id = 1\n ** module chunks = 0\n **/\n//# sourceURL=webpack:///./src/js/scrollPost.js?')},function(module,exports,__webpack_require__){eval("var path = __webpack_require__(3);\n\nvar image2 = {\n    bicycle_guy: $('#menu-main-menu-1 li .image-link').find('.bicycle-guy').parent()\n};\n\nvar image3 = {\n    small_car: $('#menu-main-menu-1 li .image-link').find('.small-car').parent(),\n    school_bus: $('#menu-main-menu-1 li .image-link').find('.school-bus').parent(),\n    wheel_chair: $('#menu-main-menu-1 li .image-link').find('.wheel-chair').parent()\n};\n\nvar image5 = {\n    old_man: $('#menu-main-menu-1 li .image-link').find('.old-man').parent(),\n    small_car2: $('#menu-main-menu-1 li .image-link').find('.small-car2').parent()\n};\n\n\ndoAnimation(image2.bicycle_guy, 'left');\ndoAnimation(image3.small_car, 'right');\ndoAnimation(image3.school_bus, 'right');\ndoAnimation(image3.wheel_chair, 'right');\ndoAnimation(image5.old_man, 'left');\ndoAnimation(image5.small_car2, 'left');\n\nfunction percentToPixel(_elem, _perc) {\n    return (_elem.parent().outerWidth() / 100) * parseFloat(_perc);\n}\n\nfunction doAnimation(element, startDirection) {\n    var tl = new TimelineMax({repeat: -1, repeatDelay: 1, paused: false});\n    var duration = Math.floor((Math.random() * 30) + 15);\n\n    var startPosition = $(element).position().left;\n\n    if (startDirection == 'right') {\n        tl\n            //.set(element, {x: startPosition - 340, delay: 1})\n            .to(element, duration, {x: $(element).closest('svg').outerWidth(), delay:1})\n            //.set(element, {rotationY: 180})\n            .set(element, {x: -(startPosition + 300)})\n            //.to(element, duration, {x:0})\n            //.set(element, {rotationY: 0})\n            .to(element, duration, {x: 0});\n    } else {\n        tl\n            //.set(element, {x: startPosition - 260, delay:2})\n            .to(element, duration, {x: -(startPosition + 300), delay: 1})\n            //.set(element, {rotationY: 180})\n            .set(element, {x: $(element).closest('svg').outerWidth()})\n            //.to(element, duration, {x:percentToPixel(element, 120)})\n            //.set(element, {rotationY: 0})\n            .to(element, duration, {x: 0});\n    }\n}\n\n$('#menu-main-menu-1 li a').click(function() {\n    $(this).parents('ul').find('li.selected').removeClass('selected');\n    $(this).parent().addClass('selected');\n});\n\n\n/*****************\n ** WEBPACK FOOTER\n ** ./src/js/components/menuAnimation.js\n ** module id = 2\n ** module chunks = 0\n **/\n//# sourceURL=webpack:///./src/js/components/menuAnimation.js?")},function(module,exports,__webpack_require__){eval("/* WEBPACK VAR INJECTION */(function(process) {// Copyright Joyent, Inc. and other Node contributors.\n//\n// Permission is hereby granted, free of charge, to any person obtaining a\n// copy of this software and associated documentation files (the\n// \"Software\"), to deal in the Software without restriction, including\n// without limitation the rights to use, copy, modify, merge, publish,\n// distribute, sublicense, and/or sell copies of the Software, and to permit\n// persons to whom the Software is furnished to do so, subject to the\n// following conditions:\n//\n// The above copyright notice and this permission notice shall be included\n// in all copies or substantial portions of the Software.\n//\n// THE SOFTWARE IS PROVIDED \"AS IS\", WITHOUT WARRANTY OF ANY KIND, EXPRESS\n// OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF\n// MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN\n// NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,\n// DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR\n// OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE\n// USE OR OTHER DEALINGS IN THE SOFTWARE.\n\n// resolves . and .. elements in a path array with directory names there\n// must be no slashes, empty elements, or device names (c:\\) in the array\n// (so also no leading and trailing slashes - it does not distinguish\n// relative and absolute paths)\nfunction normalizeArray(parts, allowAboveRoot) {\n  // if the path tries to go above the root, `up` ends up > 0\n  var up = 0;\n  for (var i = parts.length - 1; i >= 0; i--) {\n    var last = parts[i];\n    if (last === '.') {\n      parts.splice(i, 1);\n    } else if (last === '..') {\n      parts.splice(i, 1);\n      up++;\n    } else if (up) {\n      parts.splice(i, 1);\n      up--;\n    }\n  }\n\n  // if the path is allowed to go above the root, restore leading ..s\n  if (allowAboveRoot) {\n    for (; up--; up) {\n      parts.unshift('..');\n    }\n  }\n\n  return parts;\n}\n\n// Split a filename into [root, dir, basename, ext], unix version\n// 'root' is just a slash, or nothing.\nvar splitPathRe =\n    /^(\\/?|)([\\s\\S]*?)((?:\\.{1,2}|[^\\/]+?|)(\\.[^.\\/]*|))(?:[\\/]*)$/;\nvar splitPath = function(filename) {\n  return splitPathRe.exec(filename).slice(1);\n};\n\n// path.resolve([from ...], to)\n// posix version\nexports.resolve = function() {\n  var resolvedPath = '',\n      resolvedAbsolute = false;\n\n  for (var i = arguments.length - 1; i >= -1 && !resolvedAbsolute; i--) {\n    var path = (i >= 0) ? arguments[i] : process.cwd();\n\n    // Skip empty and invalid entries\n    if (typeof path !== 'string') {\n      throw new TypeError('Arguments to path.resolve must be strings');\n    } else if (!path) {\n      continue;\n    }\n\n    resolvedPath = path + '/' + resolvedPath;\n    resolvedAbsolute = path.charAt(0) === '/';\n  }\n\n  // At this point the path should be resolved to a full absolute path, but\n  // handle relative paths to be safe (might happen when process.cwd() fails)\n\n  // Normalize the path\n  resolvedPath = normalizeArray(filter(resolvedPath.split('/'), function(p) {\n    return !!p;\n  }), !resolvedAbsolute).join('/');\n\n  return ((resolvedAbsolute ? '/' : '') + resolvedPath) || '.';\n};\n\n// path.normalize(path)\n// posix version\nexports.normalize = function(path) {\n  var isAbsolute = exports.isAbsolute(path),\n      trailingSlash = substr(path, -1) === '/';\n\n  // Normalize the path\n  path = normalizeArray(filter(path.split('/'), function(p) {\n    return !!p;\n  }), !isAbsolute).join('/');\n\n  if (!path && !isAbsolute) {\n    path = '.';\n  }\n  if (path && trailingSlash) {\n    path += '/';\n  }\n\n  return (isAbsolute ? '/' : '') + path;\n};\n\n// posix version\nexports.isAbsolute = function(path) {\n  return path.charAt(0) === '/';\n};\n\n// posix version\nexports.join = function() {\n  var paths = Array.prototype.slice.call(arguments, 0);\n  return exports.normalize(filter(paths, function(p, index) {\n    if (typeof p !== 'string') {\n      throw new TypeError('Arguments to path.join must be strings');\n    }\n    return p;\n  }).join('/'));\n};\n\n\n// path.relative(from, to)\n// posix version\nexports.relative = function(from, to) {\n  from = exports.resolve(from).substr(1);\n  to = exports.resolve(to).substr(1);\n\n  function trim(arr) {\n    var start = 0;\n    for (; start < arr.length; start++) {\n      if (arr[start] !== '') break;\n    }\n\n    var end = arr.length - 1;\n    for (; end >= 0; end--) {\n      if (arr[end] !== '') break;\n    }\n\n    if (start > end) return [];\n    return arr.slice(start, end - start + 1);\n  }\n\n  var fromParts = trim(from.split('/'));\n  var toParts = trim(to.split('/'));\n\n  var length = Math.min(fromParts.length, toParts.length);\n  var samePartsLength = length;\n  for (var i = 0; i < length; i++) {\n    if (fromParts[i] !== toParts[i]) {\n      samePartsLength = i;\n      break;\n    }\n  }\n\n  var outputParts = [];\n  for (var i = samePartsLength; i < fromParts.length; i++) {\n    outputParts.push('..');\n  }\n\n  outputParts = outputParts.concat(toParts.slice(samePartsLength));\n\n  return outputParts.join('/');\n};\n\nexports.sep = '/';\nexports.delimiter = ':';\n\nexports.dirname = function(path) {\n  var result = splitPath(path),\n      root = result[0],\n      dir = result[1];\n\n  if (!root && !dir) {\n    // No dirname whatsoever\n    return '.';\n  }\n\n  if (dir) {\n    // It has a dirname, strip trailing slash\n    dir = dir.substr(0, dir.length - 1);\n  }\n\n  return root + dir;\n};\n\n\nexports.basename = function(path, ext) {\n  var f = splitPath(path)[2];\n  // TODO: make this comparison case-insensitive on windows?\n  if (ext && f.substr(-1 * ext.length) === ext) {\n    f = f.substr(0, f.length - ext.length);\n  }\n  return f;\n};\n\n\nexports.extname = function(path) {\n  return splitPath(path)[3];\n};\n\nfunction filter (xs, f) {\n    if (xs.filter) return xs.filter(f);\n    var res = [];\n    for (var i = 0; i < xs.length; i++) {\n        if (f(xs[i], i, xs)) res.push(xs[i]);\n    }\n    return res;\n}\n\n// String.prototype.substr - negative index don't work in IE8\nvar substr = 'ab'.substr(-1) === 'b'\n    ? function (str, start, len) { return str.substr(start, len) }\n    : function (str, start, len) {\n        if (start < 0) start = str.length + start;\n        return str.substr(start, len);\n    }\n;\n\n/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(4)))\n\n/*****************\n ** WEBPACK FOOTER\n ** (webpack)/~/node-libs-browser/~/path-browserify/index.js\n ** module id = 3\n ** module chunks = 0\n **/\n//# sourceURL=webpack:///(webpack)/~/node-libs-browser/~/path-browserify/index.js?")},function(module,exports){eval("// shim for using process in browser\n\nvar process = module.exports = {};\nvar queue = [];\nvar draining = false;\nvar currentQueue;\nvar queueIndex = -1;\n\nfunction cleanUpNextTick() {\n    draining = false;\n    if (currentQueue.length) {\n        queue = currentQueue.concat(queue);\n    } else {\n        queueIndex = -1;\n    }\n    if (queue.length) {\n        drainQueue();\n    }\n}\n\nfunction drainQueue() {\n    if (draining) {\n        return;\n    }\n    var timeout = setTimeout(cleanUpNextTick);\n    draining = true;\n\n    var len = queue.length;\n    while(len) {\n        currentQueue = queue;\n        queue = [];\n        while (++queueIndex < len) {\n            if (currentQueue) {\n                currentQueue[queueIndex].run();\n            }\n        }\n        queueIndex = -1;\n        len = queue.length;\n    }\n    currentQueue = null;\n    draining = false;\n    clearTimeout(timeout);\n}\n\nprocess.nextTick = function (fun) {\n    var args = new Array(arguments.length - 1);\n    if (arguments.length > 1) {\n        for (var i = 1; i < arguments.length; i++) {\n            args[i - 1] = arguments[i];\n        }\n    }\n    queue.push(new Item(fun, args));\n    if (queue.length === 1 && !draining) {\n        setTimeout(drainQueue, 0);\n    }\n};\n\n// v8 likes predictible objects\nfunction Item(fun, array) {\n    this.fun = fun;\n    this.array = array;\n}\nItem.prototype.run = function () {\n    this.fun.apply(null, this.array);\n};\nprocess.title = 'browser';\nprocess.browser = true;\nprocess.env = {};\nprocess.argv = [];\nprocess.version = ''; // empty string to avoid regexp issues\nprocess.versions = {};\n\nfunction noop() {}\n\nprocess.on = noop;\nprocess.addListener = noop;\nprocess.once = noop;\nprocess.off = noop;\nprocess.removeListener = noop;\nprocess.removeAllListeners = noop;\nprocess.emit = noop;\n\nprocess.binding = function (name) {\n    throw new Error('process.binding is not supported');\n};\n\nprocess.cwd = function () { return '/' };\nprocess.chdir = function (dir) {\n    throw new Error('process.chdir is not supported');\n};\nprocess.umask = function() { return 0; };\n\n\n/*****************\n ** WEBPACK FOOTER\n ** (webpack)/~/node-libs-browser/~/process/browser.js\n ** module id = 4\n ** module chunks = 0\n **/\n//# sourceURL=webpack:///(webpack)/~/node-libs-browser/~/process/browser.js?")}]);