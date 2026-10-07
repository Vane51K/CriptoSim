<?php
// CriptoSim - Transacciones globales

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/auth.php';

requiere_admin();
verificar_estado_cuenta();

$admin = usuario_actual();
unset($idAdmin);

// El filtro llega por GET y solo se aceptan los tres valores
// conocidos. Cualquier otra cosa se cae a "todas".
$opcionesFiltro = ['todas', 'compra', 'venta'];
$filtro = $_GET['filtro'] ?? 'todas';

if (!in_array($filtro, $opcionesFiltro, true)) {
    $filtro = 'todas';
}

$sql = "SELECT t.id_transaccion, t.tipo, t.cantidad, t.precio_unitario,
               t.total, t.fecha,
               u.nombre, u.correo,
               c.simbolo, c.nombre AS cripto
        FROM transacciones t
        JOIN usuarios u ON u.id_usuario = t.id_usuario
        JOIN criptomonedas c ON c.id_criptomoneda = t.id_criptomoneda";

if ($filtro !== 'todas') {
    $sql .= " WHERE t.tipo = ?";
}

// El segundo criterio del ORDER BY evita que dos operaciones con la
// misma fecha se intercambien de lugar entre una peticion y otra.
$sql .= " ORDER BY t.fecha DESC, t.id_transaccion DESC";

$stmt = $conexion->prepare($sql);

if ($filtro !== 'todas') {
    $stmt->bind_param('s', $filtro);
}

$stmt->execute();
$transacciones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Totales del filtro actual, no del historial completo
$totalOperaciones = count($transacciones);
$totalCompras = 0;
$totalVentas = 0;

foreach ($transacciones as $mov) {
    if ($mov['tipo'] === 'compra') {
        $totalCompras += (float) $mov['total'];
    } else {
        $totalVentas += (float) $mov['total'];
    }
}

$etiquetas = [
    'todas'  => 'Todas',
    'compra' => 'Compras',
    'venta'  => 'Ventas',
];

$titulo = 'Transacciones';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="saludo">
    <h1>Transacciones</h1>
    <p>Operaciones de todas las cuentas del simulador</p>
</section>

<section class="seccion">
    <div class="grid-indicadores">
        <div class="indicador">
            <p class="indicador-etiqueta">Operaciones en vista</p>
            <p class="indicador-valor"><?= $totalOperaciones ?></p>
            <p class="indicador-pie"><?= e($etiquetas[$filtro]) ?></p>
        </div>

        <div class="indicador">
            <p class="indicador-etiqueta">Compras</p>
            <p class="indicador-valor"><?= e(formato_q($totalCompras)) ?></p>
            <p class="indicador-pie">Suma del total comprado</p>
        </div>

        <div class="indicador">
            <p class="indicador-etiqueta">Ventas</p>
            <p class="indicador-valor"><?= e(formato_q($totalVentas)) ?></p>
            <p class="indicador-pie">Suma del total vendido</p>
        </div>
    </div>
</section>

<section class="seccion panel">
    <div class="filtros">
        <h2 class="seccion-titulo">Todas las operaciones</h2>

        <div class="filtros-grupo" role="group" aria-label="Filtrar por tipo de operacion">
            <?php foreach ($etiquetas as $clave => $texto): ?>
                <a href="<?= e(url('admin/transacciones.php?filtro=' . $clave)) ?>"
                   class="filtro-chip <?= $filtro === $clave ? 'filtro-activo' : '' ?>">
                    <?= e($texto) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (empty($transacciones)): ?>
        <div class="vacio">
            <p>No hay operaciones que coincidan con el filtro.</p>
            <a href="<?= e(url('admin/transacciones.php')) ?>" class="boton boton-secundario">
                Quitar filtro
            </a>
        </div>
    <?php else: ?>
        <div class="tabla-envoltura">
            <table class="tabla tabla-ancha">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Operacion</th>
                        <th>Criptomoneda</th>
                        <th class="tabla-num">Cantidad</th>
                        <th class="tabla-num">Precio unitario</th>
                        <th class="tabla-num">Total</th>
                        <th>Fecha</th>
                        <th>Factura</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transacciones as $mov): ?>
                        <tr>
                            <td>
                                <strong><?= e($mov['nombre']) ?></strong>
                                <span class="tabla-correo"><?= e($mov['correo']) ?></span>
                            </td>
                            <td>
                                <span class="badge badge-<?= e($mov['tipo']) ?>">
                                    <?= $mov['tipo'] === 'compra' ? 'Compra' : 'Venta' ?>
                                </span>
                            </td>
                            <td>
                                <span class="tabla-cripto"><?= e($mov['simbolo']) ?></span>
                                <?= e($mov['cripto']) ?>
                            </td>
                            <td class="tabla-num"><?= e(formato_cantidad($mov['cantidad'])) ?></td>
                            <td class="tabla-num"><?= e(formato_q($mov['precio_unitario'])) ?></td>
                            <td class="tabla-num tabla-total"><?= e(formato_q($mov['total'])) ?></td>
                            <td class="tabla-fecha"><?= e(fecha_legible($mov['fecha'])) ?></td>
                            <td>
                                <a href="<?= e(url('usuario/factura.php?id=' . (int) $mov['id_transaccion'])) ?>"
                                   class="boton boton-secundario boton-compacto">Ver</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>