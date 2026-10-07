<?php
// CriptoSim - Mascota SIMI
// SIMI es la mascota del sistema. Cada reaccion busca su imagen en
// img/simi/; si la variante no existe, cae en silencio a la base para
// que la pagina nunca se rompa. Subir una imagen nueva no requiere codigo.

require_once __DIR__ . '/funciones.php';

/**
 * Extensiones aceptadas para una imagen de SIMI, probadas por orden.
 */
function extensiones_simi()
{
    return ['.jpg', '.jpeg', '.png', '.webp'];
}

/**
 * Reacciones conocidas: clave logica -> nombre de archivo sin extension.
 */
function reacciones_simi()
{
    return [
        'base'        => 'simi-base',
        'bienvenida'  => 'simi-bienvenida',
        'alegre'      => 'simi-alegre',
        'preocupado'  => 'simi-preocupado',
        'triste'      => 'simi-triste',
        'pensativo'   => 'simi-pensativo',
    ];
}

/**
 * Busca en disco la ruta relativa de una reaccion, o null si falta.
 */
function simi_archivo($reaccion)
{
    $reacciones = reacciones_simi();

    // Solo nombres conocidos, para evitar rutas de archivo arbitrarias.
    if (!isset($reacciones[$reaccion])) {
        $reaccion = 'base';
    }

    foreach (extensiones_simi() as $extension) {
        $relativa = 'img/simi/' . $reacciones[$reaccion] . $extension;

        if (is_file(__DIR__ . '/../' . $relativa)) {
            return $relativa;
        }
    }

    return null;
}

/**
 * URL de una reaccion de SIMI; si la variante falta, usa la base.
 */
function simi_imagen($reaccion = 'base')
{
    $encontrada = simi_archivo($reaccion);

    if ($encontrada !== null) {
        return $encontrada;
    }

    // Ultima garantia: devolver una ruta valida aunque falte todo.
    $base = simi_archivo('base');

    return $base ?? 'img/simi/simi-base.jpg';
}

/**
 * Indica si una reaccion especifica tiene imagen subida.
 */
function simi_tiene_reaccion($reaccion)
{
    return simi_archivo($reaccion) !== null;
}

/**
 * Renderiza a SIMI.
 *
 * @param string $reaccion Reaccion logica
 * @param string $estilo   'portada', 'vacia' o 'resultado'
 * @param bool   $flotar   Si debe animarse
 */
function simi($reaccion = 'base', $estilo = 'portada', $alt = '', $flotar = true)
{
    $ruta = simi_imagen($reaccion);

    // Tamano segun el contexto; un estilo desconocido cae a portada.
    $tamanos = [
        'portada'   => 236,
        'vacia'     => 104,
        'resultado' => 76,
    ];
    $estilo = isset($tamanos[$estilo]) ? $estilo : 'portada';

    $clases = ['mascota', 'mascota-' . $estilo];
    if ($flotar) {
        $clases[] = 'mascota-flotante';
    }

    $tamano = $tamanos[$estilo];

    return '<img src="' . e(url($ruta)) . '"'
         . ' alt="' . e($alt) . '"'
         . ' class="' . e(implode(' ', $clases)) . '"'
         . ' width="' . $tamano . '" height="' . $tamano . '">';
}

/**
 * Renderiza a SIMI con una burbuja de dialogo.
 *
 * OJO: $texto se imprime como HTML en la burbuja; quien llama debe
 * pasar el texto ya escapado con e(). Nunca entrada cruda del usuario.
 */
function simi_con_burbuja($reaccion, $texto, $estilo = 'portada', $alt = 'SIMI, la mascota de CriptoSim')
{
    $html = '<div class="simi-escena simi-escena-' . e($estilo) . '">';
    $html .= '<div class="simi-burbuja">' . $texto . '</div>';

    // La imagen y su halo van en .simi-figura para centrar el halo
    // sobre la imagen sola, sin depender de la burbuja.
    $html .= '<div class="simi-figura">';
    $html .= '<div class="mascota-halo" aria-hidden="true"></div>';
    $html .= simi($reaccion, $estilo, $alt, true);
    $html .= '</div>';

    $html .= '</div>';

    return $html;
}

/**
 * Reaccion segun el resultado: exito -> alegre, falla -> triste.
 */
function simi_reaccion_resultado($exito)
{
    return $exito ? 'alegre' : 'triste';
}

/**
 * Resultado de una operacion con SIMI y su mensaje. El mensaje se
 * escapa aqui porque viene de la base de datos.
 */
function simi_resultado($exito, $mensaje)
{
    $reaccion = simi_reaccion_resultado($exito);
    $variante = $exito ? 'exito' : 'error';
    $alt = $exito ? 'SIMI celebra la operacion' : 'SIMI avisa del problema';

    $html = '<div class="simi-resultado simi-resultado-' . $variante . '">';
    $html .= simi_con_burbuja($reaccion, e($mensaje), 'resultado', $alt);
    $html .= '</div>';

    return $html;
}