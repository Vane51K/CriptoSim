<?php
// CriptoSim - Consultas y operaciones de trading

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/precios.php';

/**
 * Saldo actual desde la base. Se usa antes de cada operacion para no
 * confiar en el valor de la sesion, que puede quedarse viejo.
 */
function saldo_actual($idUsuario)
{
    global $conexion;

    $stmt = $conexion->prepare("SELECT saldo FROM usuarios WHERE id_usuario = ?");
    $stmt->bind_param('i', $idUsuario);
    $stmt->execute();

    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $fila ? (float) $fila['saldo'] : 0.0;
}

/**
 * Recarga de saldo virtual (Mi Perfil). Es un incremento puro sobre
 * la misma columna de compra/venta: la consigna no pide medios de
 * pago, el dinero es ficticio. La validacion del monto la hace la
 * pagina que llama.
 */
function recargar_saldo($idUsuario, $monto)
{
    global $conexion;

    $stmt = $conexion->prepare(
        "UPDATE usuarios SET saldo = saldo + ? WHERE id_usuario = ?"
    );
    $stmt->bind_param('di', $monto, $idUsuario);
    $stmt->execute();
    $modificadas = $conexion->affected_rows;
    $stmt->close();

    return $modificadas > 0;
}

function listar_mercado()
{
    global $conexion;

    return $conexion->query(
        "SELECT id_criptomoneda, nombre, simbolo, precio, variacion
         FROM criptomonedas WHERE estado = 'activo' ORDER BY id_criptomoneda"
    )->fetch_all(MYSQLI_ASSOC);
}

function obtener_cripto($idCripto)
{
    global $conexion;

    $stmt = $conexion->prepare(
        "SELECT id_criptomoneda, nombre, simbolo, precio, variacion, estado
         FROM criptomonedas WHERE id_criptomoneda = ?"
    );
    $stmt->bind_param('i', $idCripto);
    $stmt->execute();

    $cripto = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $cripto;
}

/**
 * Portafolio valorado al precio efectivo, no al de la tabla, para que
 * cuadre con lo que se cobra al operar.
 */
function obtener_portafolio($idUsuario)
{
    global $conexion;

    $stmt = $conexion->prepare(
        "SELECT p.id_criptomoneda, c.nombre, c.simbolo, c.precio, c.variacion, p.cantidad
         FROM portafolio p
         INNER JOIN criptomonedas c ON c.id_criptomoneda = p.id_criptomoneda
         WHERE p.id_usuario = ? AND p.cantidad > 0
         ORDER BY (p.cantidad * c.precio) DESC"
    );
    $stmt->bind_param('i', $idUsuario);
    $stmt->execute();

    $filas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $total = 0.0;
    foreach ($filas as &$fila) {
        $precio = precio_efectivo($fila);
        $fila['precio'] = $precio['precio'];
        $fila['valor'] = round((float) $fila['cantidad'] * $precio['precio'], 2);
        $total += $fila['valor'];
    }
    unset($fila);

    // Se reordena en PHP porque el precio vigente puede venir de la
    // cache de precios y no de la tabla.
    usort($filas, function ($a, $b) {
        return $b['valor'] <=> $a['valor'];
    });

    foreach ($filas as &$fila) {
        $fila['porcentaje'] = $total > 0 ? round(($fila['valor'] / $total) * 100, 2) : 0.0;
    }
    unset($fila);

    return ['monedas' => $filas, 'total' => round($total, 2)];
}

function cantidad_poseida($idUsuario, $idCripto)
{
    global $conexion;

    $stmt = $conexion->prepare(
        "SELECT cantidad FROM portafolio WHERE id_usuario = ? AND id_criptomoneda = ?"
    );
    $stmt->bind_param('ii', $idUsuario, $idCripto);
    $stmt->execute();

    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $fila ? (float) $fila['cantidad'] : 0.0;
}

/**
 * Registrar compra, todo dentro de una transaccion: descuenta saldo,
 * suma al portafolio y guarda el historial, o no deja nada a medias.
 */
function registrar_compra($idUsuario, $idCripto, $cantidad)
{
    global $conexion;

    $cripto = obtener_cripto($idCripto);

    if (!$cripto || $cripto['estado'] !== 'activo') {
        return ['exito' => false, 'mensaje' => 'La criptomoneda no esta disponible.'];
    }

    if (!validar_cantidad($cantidad)) {
        return ['exito' => false, 'mensaje' => 'Cantidad no válida.'];
    }

    // Precio resuelto aca para usar siempre el vigente, no el que la
    // pagina guardo en la sesion.
    $precio = precio_efectivo($cripto);
    $total = round($precio['precio'] * $cantidad, 2);

    if ($total <= 0) {
        return ['exito' => false, 'mensaje' => 'Cantidad no válida.'];
    }

    $conexion->begin_transaction();

    try {
        // El saldo se relee DENTRO de la transaccion: si dos compras
        // llegan a la vez y se usara un valor previo, la segunda
        // sobrescribiria un saldo viejo y el dinero apareceria dos veces.
        $saldo = saldo_actual($idUsuario);

        if ($total > $saldo) {
            $conexion->rollback();
            return ['exito' => false, 'mensaje' => 'Saldo insuficiente.'];
        }

        $nuevoSaldo = round($saldo - $total, 2);

        $stmt = $conexion->prepare("UPDATE usuarios SET saldo = ? WHERE id_usuario = ?");
        $stmt->bind_param('di', $nuevoSaldo, $idUsuario);
        $stmt->execute();
        $stmt->close();

        $stmt = $conexion->prepare(
            "INSERT INTO portafolio (id_usuario, id_criptomoneda, cantidad)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE cantidad = cantidad + VALUES(cantidad)"
        );
        $stmt->bind_param('idd', $idUsuario, $idCripto, $cantidad);
        $stmt->execute();
        $stmt->close();

        $stmt = $conexion->prepare(
            "INSERT INTO transacciones
                (id_usuario, id_criptomoneda, tipo, cantidad, precio_unitario, total)
             VALUES (?, ?, 'compra', ?, ?, ?)"
        );
        $stmt->bind_param('iiddd', $idUsuario, $idCripto, $cantidad, $precio['precio'], $total);
        $stmt->execute();
        $stmt->close();

        $conexion->commit();

        return [
            'exito'   => true,
            'mensaje' => 'Compra realizada correctamente.',
            'total'   => $total,
            'precio'  => $precio['precio'],
        ];
    } catch (mysqli_sql_exception $e) {
        $conexion->rollback();
        return ['exito' => false, 'mensaje' => 'No se pudo completar la operacion.'];
    }
}

/**
 * Registrar venta, todo dentro de una transaccion: descuenta del
 * portafolio, suma al saldo y guarda el historial.
 */
function registrar_venta($idUsuario, $idCripto, $cantidad)
{
    global $conexion;

    $cripto = obtener_cripto($idCripto);

    if (!$cripto || $cripto['estado'] !== 'activo') {
        return ['exito' => false, 'mensaje' => 'La criptomoneda no esta disponible.'];
    }

    if (!validar_cantidad($cantidad)) {
        return ['exito' => false, 'mensaje' => 'Cantidad no válida.'];
    }

    $precio = precio_efectivo($cripto);
    $total = round($precio['precio'] * $cantidad, 2);

    if ($total <= 0) {
        return ['exito' => false, 'mensaje' => 'Cantidad no válida.'];
    }

    $conexion->begin_transaction();

    try {
        // La posicion se lee con FOR UPDATE dentro de la transaccion:
        // el bloqueo impide que dos ventas simultaneas lean la misma
        // cantidad y vendan dos veces lo que el usuario tiene.
        $stmt = $conexion->prepare(
            "SELECT cantidad FROM portafolio
             WHERE id_usuario = ? AND id_criptomoneda = ? FOR UPDATE"
        );
        $stmt->bind_param('ii', $idUsuario, $idCripto);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $disponible = $fila ? (float) $fila['cantidad'] : 0.0;

        if ($cantidad > $disponible) {
            $conexion->rollback();
            return ['exito' => false, 'mensaje' => 'No posee suficiente cantidad para realizar esta venta.'];
        }

        $saldo = saldo_actual($idUsuario);
        $nuevoSaldo = round($saldo + $total, 2);

        $stmt = $conexion->prepare("UPDATE usuarios SET saldo = ? WHERE id_usuario = ?");
        $stmt->bind_param('di', $nuevoSaldo, $idUsuario);
        $stmt->execute();
        $stmt->close();

        $restante = round($disponible - $cantidad, 8);

        if ($restante <= 0) {
            // Al vender todo se borra la fila: el portafolio no queda
            // con posiciones en cero y el conteo de criptos no miente.
            $stmt = $conexion->prepare(
                "DELETE FROM portafolio WHERE id_usuario = ? AND id_criptomoneda = ?"
            );
            $stmt->bind_param('ii', $idUsuario, $idCripto);
            $stmt->execute();
            $stmt->close();
        } else {
            $stmt = $conexion->prepare(
                "UPDATE portafolio SET cantidad = ? WHERE id_usuario = ? AND id_criptomoneda = ?"
            );
            $stmt->bind_param('dii', $restante, $idUsuario, $idCripto);
            $stmt->execute();
            $stmt->close();
        }

        $stmt = $conexion->prepare(
            "INSERT INTO transacciones
                (id_usuario, id_criptomoneda, tipo, cantidad, precio_unitario, total)
             VALUES (?, ?, 'venta', ?, ?, ?)"
        );
        $stmt->bind_param('iiddd', $idUsuario, $idCripto, $cantidad, $precio['precio'], $total);
        $stmt->execute();
        $stmt->close();

        $conexion->commit();

        return [
            'exito'   => true,
            'mensaje' => 'Venta realizada correctamente.',
            'total'   => $total,
            'precio'  => $precio['precio'],
        ];
    } catch (mysqli_sql_exception $e) {
        $conexion->rollback();
        return ['exito' => false, 'mensaje' => 'No se pudo completar la operacion.'];
    }
}

/**
 * Cantidad positiva con maximo 8 decimales, la precision de la columna.
 */
function validar_cantidad($cantidad)
{
    if (!is_numeric($cantidad)) {
        return false;
    }

    $numero = (float) $cantidad;

    if ($numero <= 0 || $numero > 1000000) {
        return false;
    }

    // Cuenta los decimales reales, quitando los ceros sobrantes.
    $texto = rtrim(rtrim(sprintf('%.10f', $numero), '0'), '.');

    if (strpos($texto, '.') === false) {
        return true;
    }

    $decimales = strlen(substr(strrchr($texto, '.'), 1));

    return $decimales <= 8;
}

/**
 * Ultimas transacciones del usuario. $limite nulo trae todas;
 * con $limite, $offset pagina. Acepta filtro compra/venta.
 */
function obtener_historial($idUsuario, $filtro = 'todas', $limite = null, $offset = 0)
{
    global $conexion;

    $sql = "SELECT t.id_transaccion, c.nombre, c.simbolo, t.tipo,
                   t.cantidad, t.precio_unitario, t.total, t.fecha
            FROM transacciones t
            INNER JOIN criptomonedas c ON c.id_criptomoneda = t.id_criptomoneda
            WHERE t.id_usuario = ?";

    if ($filtro === 'compra' || $filtro === 'venta') {
        $sql .= " AND t.tipo = ?";
    }

    $sql .= " ORDER BY t.fecha DESC, t.id_transaccion DESC";

    if ($limite !== null) {
        $sql .= " LIMIT ? OFFSET ?";
    }

    $stmt = $conexion->prepare($sql);

    $tipos = 'i';
    $params = [$idUsuario];

    if ($filtro === 'compra' || $filtro === 'venta') {
        $tipos .= 's';
        $params[] = $filtro;
    }

    if ($limite !== null) {
        $tipos .= 'ii';
        $params[] = (int) $limite;
        $params[] = max(0, (int) $offset);
    }

    $stmt->bind_param($tipos, ...$params);

    $stmt->execute();
    $filas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $filas;
}

/**
 * Total de transacciones con el mismo filtro que obtener_historial.
 * Se usa para paginar.
 */
function contar_historial($idUsuario, $filtro = 'todas')
{
    global $conexion;

    $sql = "SELECT COUNT(*) AS total
            FROM transacciones t
            WHERE t.id_usuario = ?";

    if ($filtro === 'compra' || $filtro === 'venta') {
        $sql .= " AND t.tipo = ?";
    }

    $stmt = $conexion->prepare($sql);

    if ($filtro === 'compra' || $filtro === 'venta') {
        $stmt->bind_param('is', $idUsuario, $filtro);
    } else {
        $stmt->bind_param('i', $idUsuario);
    }

    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return (int) $fila['total'];
}

/**
 * Operaciones del usuario desglosadas por tipo.
 */
function obtener_resumen_operaciones($idUsuario)
{
    global $conexion;

    $stmt = $conexion->prepare(
        "SELECT tipo, COUNT(*) AS total FROM transacciones
         WHERE id_usuario = ? GROUP BY tipo"
    );
    $stmt->bind_param('i', $idUsuario);
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

    return [
        'compras'   => $compras,
        'ventas'    => $ventas,
        'total'     => $compras + $ventas,
        'distintas' => count($filas),
    ];
}

function contar_criptos_poseidas($idUsuario)
{
    global $conexion;

    $stmt = $conexion->prepare(
        "SELECT COUNT(*) AS total FROM portafolio WHERE id_usuario = ? AND cantidad > 0"
    );
    $stmt->bind_param('i', $idUsuario);
    $stmt->execute();

    $total = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    return (int) $total;
}

/**
 * Operaciones de este mes contra las del mes anterior, para mostrar
 * la variacion en el dashboard.
 */
function operaciones_este_mes($idUsuario)
{
    global $conexion;

    $stmt = $conexion->prepare(
        "SELECT
            SUM(YEAR(fecha) = YEAR(CURRENT_DATE) AND MONTH(fecha) = MONTH(CURRENT_DATE)) AS actual,
            SUM(YEAR(fecha) = YEAR(DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH))
                AND MONTH(fecha) = MONTH(DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH))) AS anterior
         FROM transacciones WHERE id_usuario = ?"
    );
    $stmt->bind_param('i', $idUsuario);
    $stmt->execute();

    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return [
        'actual'   => (int) ($fila['actual'] ?? 0),
        'anterior' => (int) ($fila['anterior'] ?? 0),
    ];
}