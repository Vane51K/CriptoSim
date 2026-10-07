<?php
// CriptoSim - Consejos de SIMI
// Arma el cuadro "Pensamientos de Simi" del dashboard. No es una
// prediccion: se basa en las variaciones que genera el simulador
// sobre la base, tal como pide INSTRUCCIONES.MD (sin APIs reales).

require_once __DIR__ . '/precios.php';

/**
 * Tips generales que rotan alineados al intervalo de precios.
 */
function tips_simi()
{
    return [
        'Tip: no pongas todo en una sola moneda, con poco tambien se diversifica.',
        'Tip: compra de a poco y varias veces antes que meter todo de golpe.',
        'Tip: fijate en la variacion antes de operar: cuanto mas bajo el precio, mas barata es la entrada.',
        'Tip: vendar cuando tengas ganancia tambien es ganar, no hace falta esperar el maximo.',
        'Tip: anota por que compraste cada vez, despues sirve para no repetir el mismo error.',
        'Tip: este mercado se mueve solo cada cierto intervalo, la prisa no cambia el precio.',
    ];
}

/**
 * Formatea una variacion con signo obligatorio y dos decimales.
 */
function variacion_simi($variacion)
{
    return sprintf('%+.2f%%', (float) $variacion);
}

/**
 * Consejo a partir de una lista de mercado ya precificada. Con una
 * sola moneda activa se cae en un texto sin comparacion.
 */
function consejo_simi($mercado)
{
    $base = [
        'titulo'   => 'Pensamientos de Simi',
        'reaccion' => 'pensativo',
        'cuando'   => date('H:i'),
    ];

    if (empty($mercado)) {
        return $base + [
            'texto' => 'Todavia no hay criptomonedas activas en el simulador, '
                     . 'asi que no hay nada que analizar. Cuando el mercado '
                     . 'se active vuelvo a pensar un poco.',
        ];
    }

    // Recorre una sola vez buscando la que mas bajo y la que mas subio.
    $min = $mercado[0];
    $max = $mercado[0];

    foreach ($mercado as $cripto) {
        if ((float) $cripto['variacion'] < (float) $min['variacion']) {
            $min = $cripto;
        }
        if ((float) $cripto['variacion'] > (float) $max['variacion']) {
            $max = $cripto;
        }
    }

    $frases = [];

    if ((string) $min['id_criptomoneda'] === (string) $max['id_criptomoneda']) {
        // Un solo activo en el mercado: no hay comparacion posible.
        $frases[] = 'Hoy solo hay ' . $min['nombre'] . ' (' . $min['simbolo'] . ') en el mercado '
                  . 'con una variacion de ' . variacion_simi($min['variacion'])
                  . ', asi que no hay mucho que comparar.';
    } else {
        if ((float) $min['variacion'] < 0) {
            $frases[] = 'La que mas bajo hoy es ' . $min['nombre'] . ' (' . $min['simbolo'] . ') con '
                      . variacion_simi($min['variacion'])
                      . ', asi que es la entrada mas barata del momento.';
        } else {
            $frases[] = 'Hoy no bajo ninguna: ' . $min['nombre'] . ' (' . $min['simbolo'] . ') es la que '
                      . 'menos se mueve con ' . variacion_simi($min['variacion'])
                      . ', la opcion mas tranquila de las dos.';
        }

        if ((float) $max['variacion'] > 0) {
            $frases[] = $max['nombre'] . ' (' . $max['simbolo'] . ') ya subio '
                      . variacion_simi($max['variacion'])
                      . ', ojo: es el precio mas caro de la jornada.';
        } else {
            $frases[] = 'Si todas bajan, no hay prisa: el mercado sigue cayendo '
                      . 'y siempre podes esperar el proximo movimiento.';
        }
    }

    // El tip rota con el mismo intervalo que los precios.
    $tips = tips_simi();
    $indice = (int) floor(time() / PRECIOS_INTERVALO_SEGUNDOS) % count($tips);
    $frases[] = $tips[$indice];

    return $base + ['texto' => implode(' ', $frases)];
}