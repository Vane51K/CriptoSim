<?php
// CriptoSim - Graficas sin libreria (barras de CSS)
// Separadas de trading.php a proposito: aqui solo se LEE transacciones
// (nunca un UPDATE/INSERT), y no se toca el codigo que mueve dinero.
// Las barras son de CSS pintadas por el servidor: no hay Chart.js ni
// CDN, la grafica sale igual con o sin internet.

require_once __DIR__ . '/funciones.php';

/**
 * Conteos de compras y ventas. $idUsuario filtra una cuenta
 * (grafica del dashboard); con null mira todo el simulador.
 */
function datos_compras_ventas($idUsuario = null)
{
    global $conexion;

    $sql = "SELECT tipo, COUNT(*) AS total
            FROM transacciones";

    if ($idUsuario !== null) {
        $sql .= ' WHERE id_usuario = ?';
    }

    $sql .= ' GROUP BY tipo';

    $stmt = $conexion->prepare($sql);

    if ($idUsuario !== null) {
        $stmt->bind_param('i', $idUsuario);
    }

    $stmt->execute();

    $filas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $compras = 0;
    $ventas = 0;

    foreach ($filas as $fila) {
        if ($fila['tipo'] === 'compra') {
            $compras = (int) $fila['total'];
        } elseif ($fila['tipo'] === 'venta') {
            $ventas = (int) $fila['total'];
        }
    }

    // Orden fijo de las barras: primero compras, luego ventas.
    return [
        'etiquetas' => ['Compras', 'Ventas'],
        'valores'   => [$compras, $ventas],
        'compras'   => $compras,
        'ventas'    => $ventas,
    ];
}

/**
 * Monedas mas operadas, con cantidad acumulada de compras y ventas.
 */
function datos_monedas_mas_usadas($idUsuario = null, $limite = 6)
{
    global $conexion;

    $sql = "SELECT c.simbolo, c.nombre,
                   SUM(t.cantidad) AS cantidad_total,
                   COUNT(*) AS operaciones
            FROM transacciones t
            INNER JOIN criptomonedas c ON c.id_criptomoneda = t.id_criptomoneda";

    if ($idUsuario !== null) {
        $sql .= ' WHERE t.id_usuario = ?';
    }

    // El LIMIT viaja ligado, no pegado a la cadena.
    $sql .= ' GROUP BY c.id_criptomoneda, c.simbolo, c.nombre
              ORDER BY cantidad_total DESC, operaciones DESC, c.simbolo ASC
              LIMIT ?';

    $stmt = $conexion->prepare($sql);

    if ($idUsuario !== null) {
        $stmt->bind_param('ii', $idUsuario, $limite);
    } else {
        $stmt->bind_param('i', $limite);
    }

    $stmt->execute();

    $filas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $etiquetas = [];
    $valores = [];

    foreach ($filas as $fila) {
        $etiquetas[] = $fila['simbolo'];
        // Por debajo de 6 decimales el numero ya no aporta al grafica.
        $valores[] = round((float) $fila['cantidad_total'], 6);
    }

    return [
        'etiquetas' => $etiquetas,
        'valores'   => $valores,
        'filas'     => $filas,
    ];
}

/**
 * Balance acumulado en el tiempo (ventas menos compras desde la
 * primera operacion). El acumulado se suma en PHP en lugar de usar
 * ventanas de MySQL 8.0, para que el SQL sea entendible a primera vista.
 */
function datos_balance_acumulado($idUsuario = null)
{
    global $conexion;

    $sql = "SELECT t.fecha,
                   CASE WHEN t.tipo = 'venta' THEN t.total ELSE -t.total END AS movimiento
            FROM transacciones t";

    if ($idUsuario !== null) {
        $sql .= ' WHERE t.id_usuario = ?';
    }

    $sql .= ' ORDER BY t.fecha ASC, t.id_transaccion ASC';

    $stmt = $conexion->prepare($sql);

    if ($idUsuario !== null) {
        $stmt->bind_param('i', $idUsuario);
    }

    $stmt->execute();

    $filas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $etiquetas = [];
    $valores = [];
    $acumulado = 0.0;

    foreach ($filas as $fila) {
        $acumulado += (float) $fila['movimiento'];
        $etiquetas[] = fecha_legible($fila['fecha']);
        $valores[] = round($acumulado, 2);
    }

    return [
        'etiquetas' => $etiquetas,
        'valores'   => $valores,
        'filas'     => $filas,
    ];
}

/**
 * Mismos cuatro tonos que la barra de portafolio del dashboard.
 */
function colores_grafica()
{
    return ['#853953', '#612D53', '#A5708F', '#C49BB8'];
}

/**
 * Colores por tipo de operacion, igual que .badge-compra y .badge-venta.
 */
function colores_operacion()
{
    return ['#30664C', '#7C3821'];
}

/**
 * Indica si los valores alcanzan para dibujar (todo en cero no informa).
 */
function hay_datos_grafica(array $valores)
{
    foreach ($valores as $valor) {
        if ((float) $valor != 0.0) {
            return true;
        }
    }

    return false;
}

/**
 * Pinta un bloque de grafica sin librerias: barras de CSS con el alto
 * (o ancho) calculado por el servidor e impreso en estilo inline, y
 * los numeros siempre presentes en HTML.
 *
 * $tipo solo cambia la disposicion: 'bar' vertical, 'doughnut'
 * vertical, 'line' horizontal (varias etiquetas entran mejor).
 */
function grafica($id, $tipo, $titulo, array $etiquetas, array $valores, $vacio, array $opciones = [])
{
    $formato = $opciones['formato'] ?? null;

    // Indices alineados con los valores: si vienen menos etiquetas,
    // se rellenan para que las barras no se corran.
    $total = count($valores);
    for ($i = 0; count($etiquetas) < $total; $i++) {
        $etiquetas[] = '#' . ($i + 1);
    }

    $html = '<div class="grafica" id="' . e($id) . '">';
    $html .= '<h3 class="grafica-titulo">' . e($titulo) . '</h3>';

    if (!hay_datos_grafica($valores)) {
        // Sin datos no se dibuja: barras en cero no informan.
        $html .= '<div class="grafica-vacia">' . e($vacio) . '</div>';
    } else {
        $html .= grafica_barras($tipo, $etiquetas, $valores, $opciones, $titulo);
    }

    // Los numeros van siempre, con datos o sin ellos.
    $html .= '<ul class="grafica-datos">';

    foreach ($valores as $i => $valor) {
        $etiqueta = $etiquetas[$i] ?? '';
        $texto = is_callable($formato) ? $formato($etiqueta, $valor) : (string) $valor;

        $html .= '<li><span>' . e($etiqueta) . '</span>'
               . '<span class="grafica-dato-valor">' . e($texto) . '</span></li>';
    }

    $html .= '</ul>';
    $html .= '</div>';

    return $html;
}

/**
 * Dibuja las barras: una columna (o fila) por valor, con color por
 * posicion y el alto/ancho ya calculado.
 */
function grafica_barras($tipo, array $etiquetas, array $valores, array $opciones, $titulo)
{
    $colores = $opciones['colores'] ?? (($tipo === 'bar') ? colores_operacion() : colores_grafica());
    $medidas = grafica_medidas($valores);
    $horizontal = ($tipo === 'line');

    $html = $horizontal
        ? '<div class="grafica-lienzo grafica-lienzo--horizontal" role="img" aria-label="' . e($titulo) . '">'
        : '<div class="grafica-lienzo" role="img" aria-label="' . e($titulo) . '">';

    $html .= '<div class="grafica-barras' . ($horizontal ? ' grafica-barras--horizontal' : '') . '">';

    foreach ($valores as $i => $valor) {
        $color = $colores[$i % count($colores)];
        $etiqueta = $etiquetas[$i] ?? '';

        if ($horizontal) {
            $html .= '<div class="grafica-bar-fila">'
                   . '<span class="grafica-bar-rotulo">' . e($etiqueta) . '</span>'
                   . '<div class="grafica-bar" style="width:' . $medidas[$i] . '%;background:' . e($color) . '"></div>'
                   . '</div>';
        } else {
            $html .= '<div class="grafica-bar-col">'
                   . '<div class="grafica-bar-relleno">'
                   . '<div class="grafica-bar" style="height:' . $medidas[$i] . '%;background:' . e($color) . '"></div>'
                   . '</div>'
                   . '<span class="grafica-bar-rotulo">' . e($etiqueta) . '</span>'
                   . '</div>';
        }
    }

    $html .= '</div>';
    $html .= '</div>';

    return $html;
}

/**
 * Tamano de cada barra entre 0 y 100. Se escala contra los extremos
 * de los datos (piso 0 o el minimo si hay negativos), no contra un
 * maximo fijo.
 */
function grafica_medidas(array $valores)
{
    $piso = min(0.0, (float) min($valores));
    $techo = max(0.0, (float) max($valores));
    $rango = $techo - $piso;

    if ($rango <= 0.0) {
        $rango = 1.0;
    }

    $medidas = [];

    foreach ($valores as $valor) {
        $numero = (float) $valor;

        if ($numero == 0.0) {
            // Sin altura: la etiqueta y el numero cuentan que no hubo.
            $medidas[] = 0.0;
            continue;
        }

        $porcentaje = ($numero - $piso) / $rango * 100;

        // Nunca a cero si el dato no es cero: una barra invisible
        // parece un error de dibujado.
        $medidas[] = max(4.0, round($porcentaje, 1));
    }

    return $medidas;
}