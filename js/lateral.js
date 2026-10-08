// CriptoSim - Barra lateral
// En las paginas internas la barra se puede minimizar y la preferencia
// se guarda. En login y registro la barra funciona como un cajon: arranca
// fuera de pantalla y se despliega con el boton flotante de la esquina.

(function () {
    'use strict';

    var CLAVE = 'criptosim_lateral';
    var cuerpo = document.body;

    // ----- Barra interna: minimizar / expandir -----

    var boton = document.getElementById('lateralToggle');

    if (boton) {
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

        pintarEstado();

        boton.addEventListener('click', function () {
            cuerpo.classList.toggle('lateral-min');
            try {
                localStorage.setItem(CLAVE, cuerpo.classList.contains('lateral-min') ? 'min' : 'max');
            } catch (e) {
                // Sin localStorage no se recuerda, pero la barra sigue funcionando.
            }
            pintarEstado();
        });

        function pintarEstado() {
            var estaMin = cuerpo.classList.contains('lateral-min');
            var texto = estaMin ? 'Expandir barra' : 'Minimizar barra';
            boton.setAttribute('aria-expanded', estaMin ? 'false' : 'true');
            boton.title = texto;
        }
    }

    // ----- Login y registro: barra como cajon -----

    var flotante = document.getElementById('lateralFlotante');
    var fondo = document.getElementById('lateralFondo');
    var cerrar = document.getElementById('lateralCerrar');

    if (flotante) {
        var alternar = function (abrir) {
            cuerpo.classList.toggle('lateral-abierta', abrir);
            flotante.setAttribute('aria-expanded', abrir ? 'true' : 'false');
            flotante.title = abrir ? 'Cerrar menu' : 'Abrir menu';
        };

        flotante.addEventListener('click', function () {
            alternar(!cuerpo.classList.contains('lateral-abierta'));
        });

        if (fondo) {
            fondo.addEventListener('click', function () { alternar(false); });
        }

        if (cerrar) {
            cerrar.addEventListener('click', function () { alternar(false); });
        }

        // Escape cierra el cajon (accesibilidad por teclado).
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { alternar(false); }
        });
    }
})();