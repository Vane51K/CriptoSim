// CriptoSim - JavaScript de la interfaz
// Controla el modal de compra y venta y el calculo del total.

(function () {
    'use strict';

    const modal = document.getElementById('modal-operacion');

    // Si la pagina no tiene modal, no hay nada que hacer.
    if (!modal) {
        return;
    }

    const form = document.getElementById('form-operacion');
    const campoOperacion = document.getElementById('campo-operacion');
    const campoCripto = document.getElementById('campo-id-cripto');
    const campoCantidad = document.getElementById('cantidad');

    const titulo = document.getElementById('modal-titulo');
    const precio = document.getElementById('modal-precio');
    const disponible = document.getElementById('modal-disponible');
    const ayuda = document.getElementById('modal-ayuda');
    const total = document.getElementById('modal-total');
    const saldo = document.getElementById('modal-saldo');
    const leyendaSaldo = document.getElementById('modal-leyenda-saldo');
    const error = document.getElementById('modal-error');
    const confirmar = document.getElementById('modal-confirmar');

    const botonCerrar = document.getElementById('modal-cerrar');
    const botonCancelar = document.getElementById('modal-cancelar');

    let datos = { operacion: 'compra', precio: 0, saldo: 0, disponible: 0, simbolo: '' };

    function formatearCantidad(valor) {
        return String(valor).replace(/(\.\d*?)0+$/, '$1').replace(/\.$/, '');
    }

    function formatearQ(valor) {
        return 'Q' + Number(valor).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // Recalcula el total a partir de la cantidad escrita y avisa si
    // no alcanza el saldo (compra) o la cantidad poseida (venta).
    function recalcular() {
        const cantidad = parseFloat(campoCantidad.value);
        error.textContent = '';

        if (isNaN(cantidad) || cantidad <= 0) {
            total.textContent = 'Q0.00';
            return;
        }

        const importe = Math.round(cantidad * datos.precio * 100) / 100;
        total.textContent = formatearQ(importe);

        if (datos.operacion === 'compra') {
            if (importe > datos.saldo) {
                error.textContent = 'Saldo insuficiente.';
                confirmar.disabled = true;
            } else {
                confirmar.disabled = false;
            }
        } else {
            if (cantidad > datos.disponible) {
                error.textContent = 'No posee suficiente cantidad para realizar esta venta.';
                confirmar.disabled = true;
            } else {
                confirmar.disabled = false;
            }
        }
    }

    // Abre el modal con los datos de la criptomoneda elegida.
    function abrir(boton) {
        const op = boton.dataset.operacion;
        datos = {
            operacion: op,
            precio: parseFloat(boton.dataset.precio),
            saldo: parseFloat(boton.dataset.saldo),
            disponible: parseFloat(boton.dataset.disponible),
            simbolo: boton.dataset.simbolo
        };

        const nombre = boton.dataset.nombre;

        campoOperacion.value = op;
        campoCripto.value = boton.dataset.id;

        if (op === 'compra') {
            titulo.textContent = 'Comprar ' + nombre;
            disponible.textContent = 'No aplica en una compra';
            leyendaSaldo.textContent = 'Saldo disponible';
            saldo.textContent = formatearQ(datos.saldo);
            ayuda.textContent = 'Use hasta 8 decimales.';
            confirmar.textContent = 'Comprar';
        } else {
            titulo.textContent = 'Vender ' + nombre;
            disponible.textContent = formatearCantidad(datos.disponible) + ' ' + datos.simbolo;
            leyendaSaldo.textContent = 'Recibiras';
            saldo.textContent = formatearQ(Math.round(datos.disponible * datos.precio * 100) / 100);
            ayuda.textContent = 'Posee ' + formatearCantidad(datos.disponible) + ' ' + datos.simbolo + '.';
            confirmar.textContent = 'Vender';
            campoCantidad.value = formatearCantidad(datos.disponible);
        }

        precio.textContent = formatearQ(datos.precio);
        error.textContent = '';

        modal.showModal();
        recalcular();
        campoCantidad.focus();
    }

    // Botones de comprar y vender.
    const botones = document.querySelectorAll('[data-operacion]');
    botones.forEach(function (boton) {
        boton.addEventListener('click', function () {
            abrir(boton);
        });
    });

    campoCantidad.addEventListener('input', recalcular);

    botonCerrar.addEventListener('click', function () {
        modal.close();
    });

    botonCancelar.addEventListener('click', function () {
        modal.close();
    });

    // Evita confirmar si hay un error de validacion en pantalla.
    form.addEventListener('submit', function (evento) {
        if (confirmar.disabled) {
            evento.preventDefault();
        }
    });
})();