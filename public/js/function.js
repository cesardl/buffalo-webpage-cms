// JavaScript Document

var y;
y = $(document);

y.ready(inicializarEventos);

function inicializarEventos() {

    $('#in_home').cycle({

        fx: 'scrollLeft',
        timeout: 5000,
        delay: -1000
    });

}

/*$(function()
			{
				$('.scroll-pane').jScrollPane({showArrows: true});
			});*/

// append scrollbar to all DOM nodes with class="css-scrollbar"
$(function () {

    $('.css-scrollbar').scrollbar();
    $('.fff').scrollbar();
});


