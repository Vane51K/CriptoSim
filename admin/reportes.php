<?php
// CriptoSim - Reportes administrativos

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/graficas.php';

requiere_admin();
verificar_estado_cuenta();

// Cuantas filas muestran los reportes 3, 4 y 5. El LIMIT tambien
// viaja como parametro ligado, nunca concatenado en la cadena SQL.
$limiteTop = 10;

// ------------------------------------------------------------
// Reporte 1: movimientos por usuario
//
// El LEFT JOIN sale desde usuarios y no desde transacciones para
// que tambien aparezcan las cuentas que todavia no operaron, con
// sus totales en cero.
// ------------------------------------------------------------
$stmt = $conexion->query(
    "SELECT u.id_usuario, u.nombre, u.correo,
            COUNT(t.id_transaccion) AS movimientos,
            COALESCE(SUM(t.tipo = 'compra'), 0) AS compras,
            COALESCE(SUM(t.tipo = 'venta'), 0) AS ventas,
            COALESCE(SUM(CASE WHEN t.tipo = 'compra' THEN t.total ELSE 0 END), 0) AS monto_compras,
            COALESCE(SUM(CASE WHEN t.tipo = 'venta' THEN t.total ELSE 0 END), 0) AS monto_ventas,
            COALESCE(SUM(CASE WHEN t.tipo = 'venta' THEN t.total ELSE -t.total END), 0) AS neto
     FROM usuarios u
     LEFT JOIN transacciones t ON t.id_usuario = u.id_usuario
     GROUP BY u.id_usuario, u.nombre, u.correo
     ORDER BY movimientos DESC, u.id_usuario ASC"
);
$movimientos = $stmt->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// El ORDER BY es descendente por cantidad, asi que si el primero de
// la lista tiene cero es que no hay ninguna transaccion en la base.
$hayOperaciones = !empty($movimientos) && (int) $movimientos[0]['movimientos'] > 0;

$totales = [
    'movimientos'    => 0,
    'compras'        => 0,
    'ventas'         => 0,
    'monto_compras'  => 0.0,
    'monto_ventas'   => 0.0,
    'neto'           => 0.0,
];

foreach ($movimientos as $fila) {
    $totales['movimientos'] += (int) $fila['movimientos'];
    $totales['compras'] += (int) $fila['compras'];
    $totales['ventas'] += (int) $fila['ventas'];
    $totales['monto_compras'] += (float) $fila['monto_compras'];
    $totales['monto_ventas'] += (float) $fila['monto_ventas'];
    $totales['neto'] += (float) $fila['neto'];
}

// ------------------------------------------------------------
// Reporte 2: balance general
// ------------------------------------------------------------
$stmt = $conexion->query(
    "SELECT COUNT(*) AS operaciones,
            COUNT(DISTINCT id_usuario) AS usuarios_con_movimiento,
            COALESCE(SUM(CASE WHEN tipo = 'compra' THEN total ELSE 0 END), 0) AS gastado,
            COALESCE(SUM(CASE WHEN tipo = 'venta' THEN total ELSE 0 END), 0) AS recibido,
            COALESCE(SUM(CASE WHEN tipo = 'venta' THEN total ELSE -total END), 0) AS neto
     FROM transacciones"
);
$balance = $stmt->fetch_assoc();
$stmt->close();

// El saldo vive en usuarios, no en transacciones: es el dinero
// virtual que los usuarios todavia tienen disponible.
$stmt = $conexion->query("SELECT COALESCE(SUM(saldo), 0) AS saldo_total FROM usuarios");
$saldoTotal = (float) ($stmt->fetch_assoc()['saldo_total'] ?? 0);
$stmt->close();

// ------------------------------------------------------------
// Reportes 3 y 4: rankings de las mas compradas y las mas vendidas
// ------------------------------------------------------------
function ranking_criptos($tipo, $limite)
{
    global $conexion;

    $stmt = $conexion->prepare(
        "SELECT c.nombre, c.simbolo,
                SUM(t.cantidad) AS cantidad_total,
                COUNT(*) AS operaciones,
                SUM(t.total) AS monto
         FROM transacciones t
         INNER JOIN criptomonedas c ON c.id_criptomoneda = t.id_criptomoneda
         WHERE t.tipo = ?
         GROUP BY c.id_criptomoneda, c.nombre, c.simbolo
         ORDER BY cantidad_total DESC, operaciones DESC, c.simbolo ASC
         LIMIT ?"
    );
    $stmt->bind_param('si', $tipo, $limite);
    $stmt->execute();
    $filas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $filas;
}

$masCompradas = ranking_criptos('compra', $limiteTop);
$masVendidas = ranking_criptos('venta', $limiteTop);

// ------------------------------------------------------------
// Reporte 5: usuarios con mayor cantidad de transacciones
// ------------------------------------------------------------
$stmt = $conexion->prepare(
    "SELECT u.nombre, u.correo,
            COUNT(*) AS operaciones,
            SUM(t.total) AS monto,
            COALESCE(SUM(t.tipo = 'compra'), 0) AS compras,
            COALESCE(SUM(t.tipo = 'venta'), 0) AS ventas
     FROM transacciones t
     INNER JOIN usuarios u ON u.id_usuario = t.id_usuario
     GROUP BY u.id_usuario, u.nombre, u.correo
     ORDER BY operaciones DESC, monto DESC, u.id_usuario ASC
     LIMIT ?"
);
$stmt->bind_param('i', $limiteTop);
$stmt->execute();
$mayoresOperaciones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$titulo = 'Reportes';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="saludo">
    <h1>Reportes</h1>
    <p>Resumen de la actividad de todos los usuarios del simulador</p>
</section>

<nav class="filtros" aria-label="Ir a un reporte">
    <h2 class="seccion-titulo">Reportes</h2>
    <div class="filtros-grupo">
        <a href="#reporte-1" class="filtro-chip">1. Movimientos por usuario</a>
        <a href="#reporte-2" class="filtro-chip">2. Balance general</a>
        <a href="#reporte-3" class="filtro-chip">3. Mas compradas</a>
        <a href="#reporte-4" class="filtro-chip">4. Mas vendidas</a>
        <a href="#reporte-5" class="filtro-chip">5. Mas operaciones</a>
    </div>
</nav>

<!-- Reporte 1 -->
<section class="seccion panel" id="reporte-1">
    <h2 class="seccion-titulo">Reporte 1: Movimientos por usuario</h2>
    <p class="ayuda">
        Operaciones, compras, ventas y monto neto de cada cuenta.
        El neto es lo vendido menos lo comprado.
    </p>

    <?php if (!$hayOperaciones): ?>
        <div class="vacio">
            <p>Ningun usuario ha registrado operaciones todavia.</p>
            <p>Este reporte se llena solo con las compras y ventas del simulador.</p>
        </div>
    <?php else: ?>
        <div class="tabla-envoltura">
            <table class="tabla tabla-ancha">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th class="tabla-num">Operaciones</th>
                        <th class="tabla-num">Compras</th>
                        <th class="tabla-num">Ventas</th>
                        <th class="tabla-num">Total compras</th>
                        <th class="tabla-num">Total ventas</th>
                        <th class="tabla-num">Neto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($movimientos as $fila): ?>
                        <tr>
                            <td>
                                <strong><?= e($fila['nombre']) ?></strong>
                                <span class="tabla-correo"><?= e($fila['correo']) ?></span>
                            </td>
                            <td class="tabla-num"><?= (int) $fila['movimientos'] ?></td>
                            <td class="tabla-num"><?= (int) $fila['compras'] ?></td>
                            <td class="tabla-num"><?= (int) $fila['ventas'] ?></td>
                            <td class="tabla-num"><?= e(formato_q($fila['monto_compras'])) ?></td>
                            <td class="tabla-num"><?= e(formato_q($fila['monto_ventas'])) ?></td>
                            <td class="tabla-num tabla-total">
                                <?= e(formato_q($fila['neto'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td>Total general</td>
                        <td class="tabla-num"><?= $totales['movimientos'] ?></td>
                        <td class="tabla-num"><?= $totales['compras'] ?></td>
                        <td class="tabla-num"><?= $totales['ventas'] ?></td>
                        <td class="tabla-num"><?= e(formato_q($totales['monto_compras'])) ?></td>
                        <td class="tabla-num"><?= e(formato_q($totales['monto_ventas'])) ?></td>
                        <td class="tabla-num"><?= e(formato_q($totales['neto'])) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php endif; ?>
</section>

<!-- Reporte 2 -->
<section class="seccion panel" id="reporte-2">
    <h2 class="seccion-titulo">Reporte 2: Balance general</h2>
    <p class="ayuda">Totales de todo el simulador, sin importar el usuario.</p>

    <div class="grid-indicadores">
        <div class="indicador">
            <p class="indicador-etiqueta">Total gastado en compras</p>
            <p class="indicador-valor"><?= e(formato_q($balance['gastado'])) ?></p>
            <p class="indicador-pie">Saldo que salio de las cuentas</p>
        </div>

        <div class="indicador">
            <p class="indicador-etiqueta">Total recibido por ventas</p>
            <p class="indicador-valor"><?= e(formato_q($balance['recibido'])) ?></p>
            <p class="indicador-pie">Saldo que entro a las cuentas</p>
        </div>

        <div class="indicador">
            <p class="indicador-etiqueta">Flujo neto</p>
            <p class="indicador-valor"><?= e(formato_q($balance['neto'])) ?></p>
            <p class="indicador-pie">Ventas menos compras</p>
        </div>

        <div class="indicador">
            <p class="indicador-etiqueta">Saldo total en el sistema</p>
            <p class="indicador-valor"><?= e(formato_q($saldoTotal)) ?></p>
            <p class="indicador-pie">Saldo simulado, no es dinero real</p>
        </div>
    </div>

    <?php if ((int) $balance['operaciones'] === 0): ?>
        <div class="vacio">
            <p>Todavia no hay transacciones registradas.</p>
            <p>
                Los saldos de las cuentas se muestran igual porque son
                valores simulados de arranque.
            </p>
        </div>
    <?php else: ?>
        <p class="sin-datos">
            <?= (int) $balance['operaciones'] ?> operacion(es) de
            <?= (int) $balance['usuarios_con_movimiento'] ?> usuario(s).
        </p>
    <?php endif; ?>
</section>

<!-- Reporte 3 -->
<section class="seccion panel" id="reporte-3">
    <h2 class="seccion-titulo">Reporte 3: Criptomonedas mas compradas</h2>
    <p class="ayuda">Ordenadas por cantidad acumulada, mostrando las <?= $limiteTop ?> primeras.</p>

    <?php if (empty($masCompradas)): ?>
        <div class="vacio">
            <p>Nadie ha comprado todavia.</p>
            <p>Este ranking se arma con las compras del simulador.</p>
        </div>
    <?php else: ?>
        <div class="tabla-envoltura">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Criptomoneda</th>
                        <th class="tabla-num">Cantidad comprada</th>
                        <th class="tabla-num">Operaciones</th>
                        <th class="tabla-num">Monto pagado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($masCompradas as $fila): ?>
                        <tr>
                            <td>
                                <span class="tabla-cripto"><?= e($fila['simbolo']) ?></span>
                                <?= e($fila['nombre']) ?>
                            </td>
                            <td class="tabla-num"><?= e(formato_cantidad($fila['cantidad_total'])) ?></td>
                            <td class="tabla-num"><?= (int) $fila['operaciones'] ?></td>
                            <td class="tabla-num tabla-total"><?= e(formato_q($fila['monto'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<!-- Reporte 4 -->
<section class="seccion panel" id="reporte-4">
    <h2 class="seccion-titulo">Reporte 4: Criptomonedas mas vendidas</h2>
    <p class="ayuda">Ordenadas por cantidad acumulada, mostrando las <?= $limiteTop ?> primeras.</p>

    <?php if (empty($masVendidas)): ?>
        <div class="vacio">
            <p>Nadie ha vendido todavia.</p>
            <p>Este ranking se arma con las ventas del simulador.</p>
        </div>
    <?php else: ?>
        <div class="tabla-envoltura">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Criptomoneda</th>
                        <th class="tabla-num">Cantidad vendida</th>
                        <th class="tabla-num">Operaciones</th>
                        <th class="tabla-num">Monto recibido</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($masVendidas as $fila): ?>
                        <tr>
                            <td>
                                <span class="tabla-cripto"><?= e($fila['simbolo']) ?></span>
                                <?= e($fila['nombre']) ?>
                            </td>
                            <td class="tabla-num"><?= e(formato_cantidad($fila['cantidad_total'])) ?></td>
                            <td class="tabla-num"><?= (int) $fila['operaciones'] ?></td>
                            <td class="tabla-num tabla-total"><?= e(formato_q($fila['monto'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<!-- Reporte 5 -->
<section class="seccion panel" id="reporte-5">
    <h2 class="seccion-titulo">Reporte 5: Usuarios con mayor cantidad de transacciones</h2>
    <p class="ayuda">Ordenados por numero de operaciones, mostrando los <?= $limiteTop ?> primeros.</p>

    <?php if (empty($mayoresOperaciones)): ?>
        <div class="vacio">
            <p>No hay transacciones registradas.</p>
            <p>Este ranking se arma con la actividad de todos los usuarios.</p>
        </div>
    <?php else: ?>
        <div class="tabla-envoltura">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th class="tabla-num">Operaciones</th>
                        <th class="tabla-num">Compras</th>
                        <th class="tabla-num">Ventas</th>
                        <th class="tabla-num">Monto movido</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mayoresOperaciones as $fila): ?>
                        <tr>
                            <td>
                                <strong><?= e($fila['nombre']) ?></strong>
                                <span class="tabla-correo"><?= e($fila['correo']) ?></span>
                            </td>
                            <td class="tabla-num tabla-total"><?= (int) $fila['operaciones'] ?></td>
                            <td class="tabla-num"><?= (int) $fila['compras'] ?></td>
                            <td class="tabla-num"><?= (int) $fila['ventas'] ?></td>
                            <td class="tabla-num"><?= e(formato_q($fila['monto'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="seccion panel">
    <h2 class="seccion-titulo">Visualizacion de datos</h2>
    <p class="ayuda">Tres graficas sencillas, alimentadas solamente con lo que hay en la tabla transacciones.</p>

    <?php
    $datosCvVAdmin = datos_compras_ventas(null);
    $datosMonedasAdmin = datos_monedas_mas_usadas(null, 6);
    $datosAcum = datos_balance_acumulado(null);
    ?>
    <div class="grid-graficas">
        <?= grafica(
            'grafica-cvv-admin',
            'bar',
            'Compras vs Ventas (global)',
            $datosCvVAdmin['etiquetas'],
            $datosCvVAdmin['valores'],
            'No hay operaciones registradas todavia.',
            ['formato' => function ($etiqueta, $valor) {
                return (string) $valor . ' operacion' . ((int) $valor == 1 ? '' : 'es');
            }]
        ) ?>
        <?= grafica(
            'grafica-monedas-admin',
            'doughnut',
            'Monedas mas utilizadas (global)',
            $datosMonedasAdmin['etiquetas'],
            $datosMonedasAdmin['valores'],
            'No hay operaciones registradas todavia.',
            ['formato' => function ($etiqueta, $valor) {
                return formato_cantidad($valor) . ' ' . $etiqueta;
            }]
        ) ?>
        <?= grafica(
            'grafica-acum-admin',
            'line',
            'Balance acumulado (ventas menos compras)',
            $datosAcum['etiquetas'],
            $datosAcum['valores'],
            'No hay operaciones registradas todavia.',
            ['formato' => function ($etiqueta, $valor) {
                return e(formato_q($valor));
            }]
        ) ?>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
