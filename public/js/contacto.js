/*
 * The contact form.
 *
 * It submits without leaving the page, puts each complaint the server makes
 * next to the field that caused it, and shows what was received in a
 * <dialog>. Everything the server echoes back is whatever the visitor typed,
 * so it goes into the page as text nodes and never as markup.
 */
$(function () {

    const formulario = $('#formContacto');

    if (formulario.length === 0) {
        return;
    }

    const ETIQUETAS = {
        nombre: 'Nombre',
        email: 'Email',
        telefono: 'Tel\u00e9fono',
        direccion: 'Direcci\u00f3n',
        ciudad: 'Ciudad',
        comentarios: 'Comentarios'
    };

    function limpiarErrores() {
        $('.campo_error').text('').hide();
    }

    function mostrarErrores(errores) {
        Object.keys(errores).forEach(function (campo) {
            const caja = $('#error_' + campo);

            (caja.length === 0 ? $('#error_formulario') : caja).text(errores[campo]).show();
        });
    }

    function fila(etiqueta, valor) {
        const tr = document.createElement('tr');
        const th = document.createElement('th');
        const td = document.createElement('td');

        th.textContent = etiqueta;
        td.textContent = valor;
        tr.append(th, td);

        return tr;
    }

    function mostrarConfirmacion(datos) {
        const dialogo = document.createElement('dialog');

        dialogo.id = 'contacto-dialog';

        const titulo = document.createElement('h3');
        titulo.textContent = 'Gracias por escribirnos';
        dialogo.append(titulo);

        const intro = document.createElement('p');
        intro.textContent = 'Recibimos estos datos y nuestro departamento de Ventas se comunicar\u00e1 con usted:';
        dialogo.append(intro);

        const tabla = document.createElement('table');

        Object.keys(ETIQUETAS).forEach(function (campo) {
            const valor = datos[campo] || '';

            if (valor !== '') {
                tabla.append(fila(ETIQUETAS[campo], valor));
            }
        });

        dialogo.append(tabla);

        const cerrar = document.createElement('button');
        cerrar.type = 'button';
        cerrar.className = 'btn_cerrar';
        cerrar.textContent = 'Cerrar';
        cerrar.addEventListener('click', function () {
            dialogo.close();
        });
        dialogo.append(cerrar);

        dialogo.addEventListener('close', function () {
            dialogo.remove();
        });

        document.body.append(dialogo);
        dialogo.showModal();
    }

    formulario.on('submit', function (evento) {
        evento.preventDefault();
        limpiarErrores();

        const boton = $('#btn_enviar');

        boton.prop('disabled', true);

        $.post(formulario.attr('action'), formulario.serialize())
            .done(function (respuesta) {
                formulario[0].reset();
                mostrarConfirmacion(respuesta.datos || {});
            })
            .fail(function (xhr) {
                const respuesta = xhr.responseJSON;

                if (respuesta && respuesta.errores) {
                    mostrarErrores(respuesta.errores);
                } else {
                    $('#error_formulario')
                        .text('No pudimos enviar el formulario. Intente nuevamente.')
                        .show();
                }
            })
            .always(function () {
                boton.prop('disabled', false);
            });
    });
});
