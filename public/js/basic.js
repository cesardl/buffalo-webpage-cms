/*
 * The product zoom dialog.
 *
 * It was SimpleModal 1.4.1, whose first statement reads $.browser.msie --
 * removed from jQuery in 1.9 -- and the overlay it assembled by hand is a
 * <dialog> now: the backdrop, Escape and the focus trap come with it. The
 * function name is what the inline onclick handlers call, so it stays.
 */
function showDevModal(id) {

    const contenido = document.querySelector('#' + id + ' #basic-modal-content');

    if (contenido === null) {
        return false;
    }

    const dialogo = document.createElement('dialog');

    dialogo.id = 'basic-modal-dialog';
    dialogo.append(contenido.cloneNode(true));

    const cerrar = document.createElement('a');

    cerrar.className = 'modalCloseImg';
    cerrar.title = 'Cerrar';
    cerrar.addEventListener('click', function () {
        dialogo.close();
    });
    dialogo.prepend(cerrar);

    // A click on the backdrop reports the dialog itself as its target.
    dialogo.addEventListener('click', function (evento) {
        if (evento.target === dialogo) {
            dialogo.close();
        }
    });
    dialogo.addEventListener('close', function () {
        dialogo.remove();
    });

    document.body.append(dialogo);
    dialogo.showModal();

    return false;
}
