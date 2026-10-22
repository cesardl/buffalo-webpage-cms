/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$(document).ready(function () {
    $("#vehiculo").change(function () {
        $.post("VehiculoSelect.php", {
            veh_id: $(this).val()
        }, function (data) {
            $("#master").html(data);
        })
    });

    $(".custom-file-input").change(function () {
        const fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });

    $('#btn_acept').click(function () {
        const master = $('#master');
        const modelo = $('#modelo');
        const v_imagen = $('#v_imagen');
        const imagen = $('#imagen');
        const descripcion = $('#descripcion');

        if (master.val() === 0) {
            alert('Debe seleccionar un vehiculo y su clase');
        } else {
            if ($.trim(modelo.val()) === 0) {
                alert('Debe ingresar un modelo de vehiculo');
            } else {
                if ($.trim(v_imagen.val()) === 0 && jQuery.trim(imagen.val()) === 0) {
                    alert('Debe seleccionar una imagen');
                } else {
                    if ($.trim(descripcion.val()) === 0) {
                        alert('Debe ingresar un descripcion del vehiculo');
                    } else {
                        $('#formProducto').submit();
                    }
                }
            }
        }
    });
});
