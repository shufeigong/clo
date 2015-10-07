var path = require('path');

var image2 = {
    bicycle_guy: $('#menu-main-menu-1 li .image-link').find('.bicycle-guy').parent()
};

var image3 = {
    small_car: $('#menu-main-menu-1 li .image-link').find('.small-car').parent(),
    school_bus: $('#menu-main-menu-1 li .image-link').find('.school-bus').parent(),
    wheel_chair: $('#menu-main-menu-1 li .image-link').find('.wheel-chair').parent()
};

var image5 = {
    old_man: $('#menu-main-menu-1 li .image-link').find('.old-man').parent(),
    small_car2: $('#menu-main-menu-1 li .image-link').find('.small-car2').parent()
};


doAnimation(image2.bicycle_guy, 'left');
doAnimation(image3.small_car, 'right');
doAnimation(image3.school_bus, 'right');
doAnimation(image3.wheel_chair, 'right');
doAnimation(image5.old_man, 'left');
doAnimation(image5.small_car2, 'left');

function percentToPixel(_elem, _perc) {
    return (_elem.parent().outerWidth() / 100) * parseFloat(_perc);
}

function doAnimation(element, startDirection) {
    var tl = new TimelineMax({repeat: -1, repeatDelay: 1, paused: false});
    var duration = Math.floor((Math.random() * 30) + 15);

    var startPosition = $(element).position().left;

    if (startDirection == 'right') {
        tl
            //.set(element, {x: startPosition - 340, delay: 1})
            .to(element, duration, {x: $(element).closest('svg').outerWidth(), delay:1})
            //.set(element, {rotationY: 180})
            .set(element, {x: -(startPosition + 300)})
            //.to(element, duration, {x:0})
            //.set(element, {rotationY: 0})
            .to(element, duration, {x: 0});
    } else {
        tl
            //.set(element, {x: startPosition - 260, delay:2})
            .to(element, duration, {x: -(startPosition + 300), delay: 1})
            //.set(element, {rotationY: 180})
            .set(element, {x: $(element).closest('svg').outerWidth()})
            //.to(element, duration, {x:percentToPixel(element, 120)})
            //.set(element, {rotationY: 0})
            .to(element, duration, {x: 0});
    }
}

$('#menu-main-menu-1 li a').click(function() {
    $(this).parents('ul').find('li.selected').removeClass('selected');
    $(this).parent().addClass('selected');
});
