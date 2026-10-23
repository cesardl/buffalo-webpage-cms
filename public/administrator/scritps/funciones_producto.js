/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$(document).ready(function () {
    $("#vehiculo").change(function () {
        $.post("VehiculoSelect.php", {
            veh_id: $(this).val(),
            // Every POST is checked at the router. Without this the class
            // dropdown answers 403, which is what it had been doing: the
            // golden master missed it because its harness adds a token to
            // every POST it sends, including the ones the page forgets.
            _token: $('#formProducto input[name="_token"]').val()
        }, function (data) {
            $("#master").html(data);
        })
    });

    $('#btn_acept').click(function () {
        const master = $('#master');
        const modelo = $('#modelo');
        const v_imagen = $('#v_imagen');
        const imagen = $('#imagen');
        const descripcion = $('#descripcion');

        // These compare a string against 0, so no alert can ever fire. That
        // is how the legacy form behaved; making the validation work is a
        // change in the administrator's behaviour and belongs in its own commit.
        if (master.val() === 0) {
            alert('Debe seleccionar un vehiculo y su clase');
        } else {
            if ((modelo.val() || '').trim() === 0) {
                alert('Debe ingresar un modelo de vehiculo');
            } else {
                if ((v_imagen.val() || '').trim() === 0 && (imagen.val() || '').trim() === 0) {
                    alert('Debe seleccionar una imagen');
                } else {
                    if ((descripcion.val() || '').trim() === 0) {
                        alert('Debe ingresar un descripcion del vehiculo');
                    } else {
                        $('#formProducto').submit();
                    }
                }
            }
        }
    });
});
