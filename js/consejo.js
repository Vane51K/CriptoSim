// CriptoSim - Cuadro "Pensamientos de Simi"
// Refresca el consejo del dashboard desde usuario/consejo.php cada
// pocos segundos, sin recargar la pagina. Si la peticion falla
// (sesion vencida, servidor apagado) queda el ultimo consejo que ya
// pinto PHP y se reintenta en el proximo intervalo.

(function () {
    'use strict';

    var INTERVALO_MS = 30000; // 30 segundos
    var caja = document.getElementById('consejo-simi');

    if (!caja) {
        return;
    }

    var url = caja.getAttribute('data-url');
    var texto = caja.querySelector('[data-consejo-texto]');
    var cuando = caja.querySelector('[data-consejo-cuando]');

    if (!url || !texto || !cuando) {
        return;
    }

    var pendiente = false;

    function refrescar() {
        // Si la anterior todavia no volvio, no se apila otra.
        if (pendiente) {
            return;
        }

        pendiente = true;

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (respuesta) {
                if (!respuesta.ok) {
                    throw new Error('HTTP ' + respuesta.status);
                }

                return respuesta.json();
            })
            .then(function (datos) {
                if (!datos.ok || !datos.texto) {
                    return;
                }

                texto.textContent = datos.texto;
                cuando.textContent = 'Actualizado ' + datos.cuando;
            })
            .catch(function () {
                // Silencio a proposito: se conserva el ultimo consejo.
            })
            .then(function () {
                pendiente = false;
            });
    }

    setInterval(refrescar, INTERVALO_MS);
})();