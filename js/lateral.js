// CriptoSim - Barra lateral
// Guarda la posicion de la barra en el navegador, asi la proxima
// visita el usuario la encuentra como la dejo.

(function () {
    'use strict';

    var CLAVE = 'criptosim_lateral';
    var cuerpo = document.body;
    var boton = document.getElementById('lateralToggle');

    if (!boton) {
        return;
    }

    var guardado = null;
    try {
        guardado = localStorage.getItem(CLAVE);
    } catch (e) {
        // Sin localStorage igual funciona, solo no recuerda el estado.
    }

    // En pantallas chicas arranca minimizada para no tapar el contenido.
    var minimizada = guardado !== null
        ? guardado === 'min'
        : window.innerWidth <= 768;

    if (minimizada) {
        cuerpo.classList.add('lateral-min');
    }

    function pintarEstado() {
        var estaMin = cuerpo.classList.contains('lateral-min');
        var texto = estaMin ? 'Expandir barra' : 'Minimizar barra';

        boton.setAttribute('aria-expanded', estaMin ? 'false' : 'true');
        boton.setAttribute('aria-label', texto);
        boton.title = texto;
    }

    pintarEstado();

    boton.addEventListener('click', function () {
        cuerpo.classList.toggle('lateral-min');

        try {
            localStorage.setItem(
                CLAVE,
                cuerpo.classList.contains('lateral-min') ? 'min' : 'abierta'
            );
        } catch (e) {
            // Ignora: el estado solo sirve para recordarlo.
        }

        pintarEstado();
    });
})();