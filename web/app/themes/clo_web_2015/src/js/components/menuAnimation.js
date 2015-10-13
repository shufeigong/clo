'use strict';
var path = require('path');

var image2 = {
    bicycle_guy: $('#menu-main-menu-1 li .image-link').find('.bicycle-guy').parent()
};

var image3 = {
    small_car: $('#menu-main-menu-1 li .image-link').find('.small-car').parent(),
    school_bus: $('#menu-main-menu-1 li .image-link').find('.school-bus').parent(),
    wheel_chair: $('#menu-main-menu-1 li .image-link').find('.wheel-chair').parent()
};

var image4 = {
    running_man: $('#menu-main-menu-1 li .image-link').find('.running-man').parent()
};

var image5 = {
    old_man: $('#menu-main-menu-1 li .image-link').find('.old-man').parent(),
    man_with_dog: $('#menu-main-menu-1 li .image-link').find('.man-with-dog').parent(),
    small_car2: $('#menu-main-menu-1 li .image-link').find('.small-car2').parent()
};

doAnimation(image2.bicycle_guy, 'left', false);
doAnimation(image3.small_car, 'right', true);
doAnimation(image3.school_bus, 'right', true);
doAnimation(image3.wheel_chair, 'right', false);
doAnimation(image4.running_man, 'left', false);
doAnimation(image5.old_man, 'left', false);
doAnimation(image5.man_with_dog, 'right', false);
doAnimation(image5.small_car2, 'left', true);


var items = [
    {
        element: $('#menu-main-menu-1 li .image-link').find('.bicycle-guy').parent(),
        direction: 'left',
        isVehicle: false
    },
    {
        element: $('#menu-main-menu-1 li .image-link').find('.small-car').parent(),
        direction: 'right',
        isVehicle: true

    },
    {
        element: $('#menu-main-menu-1 li .image-link').find('.school-bus').parent(),
        direction: 'right',
        isVehicle: true

    },
    {
        element: $('#menu-main-menu-1 li .image-link').find('.wheel-chair').parent(),
        direction: 'right',
        isVehicle: false

    },
    {
        element: $('#menu-main-menu-1 li .image-link').find('.running-man').parent(),
        direction: 'left',
        isVehicle: false
    },
    {
        element: $('#menu-main-menu-1 li .image-link').find('.old-man').parent(),
        direction: 'left',
        isVehicle: false
    },
    {
        element: $('#menu-main-menu-1 li .image-link').find('.man-with-dog').parent(),
        direction: 'right',
        isVehicle: false
    },
    {
        element: $('#menu-main-menu-1 li .image-link').find('.small-car2').parent(),
        direction: 'left',
        isVehicle: true
    }
];

//doAnimationV2(items);

function percentToPixel(_elem, _perc) {
    return (_elem.parent().outerWidth() / 100) * parseFloat(_perc);
}

function doAnimation(element, startDirection, isVehicle) {
    var tl = new TimelineMax({repeat: -1, repeatDelay: 1, paused: false});
    var duration = 0;
    if (isVehicle) {
        duration = Math.floor((Math.random() * 30) + 15);
    } else {
        duration = Math.floor((Math.random() * 40) + 35);
    }

    var startPosition = $(element).position().left;
    var outerWidth = $(element).closest('svg').outerWidth();
    var xPosition = 0;
    if (startDirection == 'right') {
        xPosition = outerWidth - startPosition;
        tl
            .to(element, duration, {x: xPosition + 100, delay: 1})
            .set(element, {x: -(startPosition + 100)})
            .to(element, duration, {x: 0});
    } else {
        xPosition = outerWidth - startPosition;
        tl
            .to(element, duration, {x: -(startPosition + 100), delay: 1})
            .set(element, {x: outerWidth + 100})
            .to(element, duration, {x: 0});
    }
}
function doAnimationV2(elements) {
    var tl = new TimelineMax({repeat: -1, repeatDelay: 1, paused: true});
    var duration = 0;

    $.each(elements, function() {
        var element = this.element;
        if(this.isVehicle) {
            duration = Math.floor((Math.random() * 30) + 15);
        } else {
            duration = Math.floor((Math.random() * 40) + 35);
        }

        var startPosition = $(element).position().left;
        var outerWidth = $(element).closest('svg').outerWidth();
        if (this.direction == 'right') {
            tl
                .insert(TweenMax.to(element, duration, {x: outerWidth, delay: 1}))
                .insert(TweenMax.set(element, {x: -(startPosition + 300)}))
                .insert(TweenMax.to(element, duration, {x: 0}));
        } else {
            tl
                .insert(TweenMax.to(element, duration, {x: -(startPosition + 300), delay: 1}))
                .insert(TweenMax.set(element, {x: outerWidth}))
                .insert(TweenMax.to(element, duration, {x: 0}));
        }
    });

    tl.restart();
}

$('#menu-main-menu-1 li a').click(function () {
    $(this).parents('ul').find('li.selected').removeClass('selected');
    $(this).parent().addClass('selected');
});
