// JavaScript Document

// The homepage slideshow. It was the jQuery Cycle plugin, which calls
// $.browser and $.isFunction -- both long gone from jQuery -- so the one
// effect this page used of it (fx: 'scrollLeft', timeout: 5000) is inlined.
$(function () {

    const marco = $('#in_home');
    const fotos = marco.children('img');

    if (fotos.length < 2) {
        return;
    }

    const ancho = marco.width();

    fotos.css({position: 'absolute', top: 0, left: ancho}).first().css('left', 0);

    let actual = 0;

    setInterval(function () {
        const siguiente = (actual + 1) % fotos.length;

        fotos.eq(siguiente).css('left', ancho).animate({left: 0}, 600);
        fotos.eq(actual).animate({left: -ancho}, 600);

        actual = siguiente;
    }, 5000);
});
