<?php
// CriptoSim - Precios simulados
// Los precios SIEMPRE son simulados: salen de la tabla criptomonedas
// y no hay ninguna llamada a internet, tal como pide INSTRUCCIONES.MD.
// Se mueven un porcentaje aleatorio cada PRECIOS_INTERVALO_SEGUNDOS
// desde la ultima actualizacion; si nadie visita, no cambian.

const PRECIOS_INTERVALO_SEGUNDOS = 60;

function leer_ultima_actualizacion_precios()
{
    global $conexion;

    $stmt = $conexion->prepare("SELECT valor FROM config_sistema WHERE clave = ?");
    $clave = 'ultima_actualizacion_precios';
    $stmt->bind_param('s', $clave);
    $stmt->execute();

    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $fila ? $fila['valor'] : null;
}

function estado_precios()
{
    return [
        'actualizado_en' => leer_ultima_actualizacion_precios(),
        'exito'          => true,
    ];
}

/**
 * Mueve los precios si paso el intervalo desde la ultima vez: cada
 * moneda activa varia entre -2% y +2%. El momento se guarda en
 * config_sistema con INSERT ... ON DUPLICATE KEY (funciona aunque la
 * fila no exista, sin obligar a reimportar database.sql).
 */
function actualizar_precios_simulados($intervaloSegundos = PRECIOS_INTERVALO_SEGUNDOS)
{
    global $conexion;

    $ultima = leer_ultima_actualizacion_precios();

    if ($ultima !== null) {
        $ultimoTimestamp = strtotime($ultima);

        if ($ultimoTimestamp !== false && (time() - $ultimoTimestamp) < $intervaloSegundos) {
            return ['exito' => true, 'mensaje' => 'Precios al dia.', 'actualizado_en' => $ultima];
        }
    }

    try {
        $filas = $conexion->query(
            "SELECT id_criptomoneda, precio FROM criptomonedas WHERE estado = 'activo'"
        )->fetch_all(MYSQLI_ASSOC);

        $stmt = $conexion->prepare(
            "UPDATE criptomonedas SET precio = ?, variacion = ? WHERE id_criptomoneda = ?"
        );

        foreach ($filas as $fila) {
            // Entre -2% y +2%, con dos decimales de porcentaje.
            $drift = mt_rand(-200, 200) / 10000;
            $nuevoPrecio = round((float) $fila['precio'] * (1 + $drift), 8);

            if ($nuevoPrecio <= 0) {
                continue;
            }

            $variacion = round($drift * 100, 2);
            $stmt->bind_param('ddi', $nuevoPrecio, $variacion, $fila['id_criptomoneda']);
            $stmt->execute();
        }

        $stmt->close();

        $ahora = date('Y-m-d H:i:s');
        $stmt = $conexion->prepare(
            "INSERT INTO config_sistema (clave, valor)
             VALUES ('ultima_actualizacion_precios', ?)
             ON DUPLICATE KEY UPDATE valor = VALUES(valor)"
        );
        $stmt->bind_param('s', $ahora);
        $stmt->execute();
        $stmt->close();

        return ['exito' => true, 'mensaje' => 'Precios actualizados.', 'actualizado_en' => $ahora];
    } catch (mysqli_sql_exception $e) {
        return ['exito' => false, 'mensaje' => 'No se pudieron actualizar los precios simulados.'];
    }
}

function formato_precio($monto)
{
    return formato_q($monto);
}

/**
 * Precio efectivo para operar, tomado de la fila que ya trae la
 * consulta. Las claves se leen con default a proposito: cada query
 * puede traer menos columnas y el fallback evita warnings.
 */
function precio_efectivo($cripto)
{
    return [
        'precio'    => (float) ($cripto['precio'] ?? 0),
        'variacion' => (float) ($cripto['variacion'] ?? 0),
        'origen'    => 'simulado',
        'fuente'    => 'Base de datos',
        'fecha'     => null,
    ];
}

/**
 * Marca cada criptomoneda como precio simulado para la vista.
 */
function aplicar_precios($criptos)
{
    foreach ($criptos as $i => $cripto) {
        $criptos[$i]['precio_origen'] = 'simulado';
        $criptos[$i]['precio_fecha'] = null;
    }

    return $criptos;
}