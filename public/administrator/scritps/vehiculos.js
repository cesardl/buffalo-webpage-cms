/*
 * The vehicle table's delete action.
 *
 * It used to be inline in the page, which meant the CSRF token was written
 * into a JavaScript string literal. It travels on the table's data-token
 * attribute now.
 */
$(function () {

    const tabla = $('#tabla-vehiculos');

    if (tabla.length === 0) {
        return;
    }

    $('.a_delete').on('click', function (evento) {
        evento.preventDefault();

        if (!confirm('\u00bfSeguro que desea eliminar este veh\u00edculo?')) {
            return;
        }

        const id = $(this).attr('href').substr(1);

        $.post('VehiculoDelete.php', {
            id_producto: id,
            _token: tabla.data('token')
        }, function (respuesta) {
            $('#result').html(respuesta);
            $('#fila-' + id).remove();
        });
    });

    $('#result').on('click', function () {
        $(this).hide();
    });
});
