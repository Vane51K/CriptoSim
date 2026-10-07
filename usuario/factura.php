<?php
// CriptoSim - Factura simulada de una operacion

// Documento imprimible, sin validez fiscal (uso educativo).

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/auth.php';

requiere_logueo();
verificar_estado_cuenta();

$usuario = usuario_actual();
$idTransaccion = (int) ($_GET['id'] ?? 0);

$stmt = $conexion->prepare(
    "SELECT t.id_transaccion, t.id_usuario, t.tipo, t.cantidad,
            t.precio_unitario, t.total, t.fecha,
            c.simbolo, c.nombre AS cripto,
            u.nombre, u.correo
     FROM transacciones t
     JOIN criptomonedas c ON c.id_criptomoneda = t.id_criptomoneda
     JOIN usuarios u ON u.id_usuario = t.id_usuario
     WHERE t.id_transaccion = ?"
);
$stmt->bind_param('i', $idTransaccion);
$stmt->execute();
$factura = $stmt->get_result()->fetch_assoc();
$stmt->close();

// La operacion no existe: cada rol vuelve a su propia area.
if (!$factura) {
    mensaje_flash('error', 'La operacion no existe.');
    redirigir(es_admin() ? 'admin/transacciones.php' : 'usuario/historial.php');
}

// Un usuario solo puede ver la factura de sus propias operaciones.
// El administrador puede consultar cualquiera.
$esPropia = (int) $factura['id_usuario'] === (int) $usuario['id_usuario'];

if (!$esPropia && !es_admin()) {
    mensaje_flash('error', 'No tenes acceso a esa factura.');
    redirigir('usuario/historial.php');
}

$simbolo = $factura['simbolo'];
$cantidad = formato_cantidad($factura['cantidad']);
$precio = formato_q($factura['precio_unitario']);
$total = formato_q($factura['total']);

$concepto = strtoupper($factura['tipo']) . ' - ' . $cantidad . ' ' . $simbolo
    . ' (' . $factura['cripto'] . ')';

$numero = 'F-' . str_pad((string) $factura['id_transaccion'], 6, '0', STR_PAD_LEFT);

$titulo = 'Factura ' . $numero;
require_once __DIR__ . '/../includes/header.php';
?>

<section class="seccion">
    <div class="factura" aria-label="Factura simulada">
        <header class="factura-cabecera">
            <div>
                <span class="logo-texto">CriptoSim</span>
                <h1 class="factura-titulo">Factura simulada</h1>
            </div>
            <div class="factura-meta">
                <div class="factura-numero"><?= e($numero) ?></div>
                <div><?= e(fecha_legible($factura['fecha'])) ?></div>
            </div>
        </header>

        <div class="factura-datos">
            <div class="factura-campo">
                <strong>Cliente</strong>
                <?= e($factura['nombre']) ?>
            </div>
            <div class="factura-campo">
                <strong>Correo</strong>
                <?= e($factura['correo']) ?>
            </div>
            <div class="factura-campo">
                <strong>NIT</strong>
                CF (Consumidor Final - simulada)
            </div>
            <div class="factura-campo">
                <strong>Operacion</strong>
                <?= e(strtoupper($factura['tipo'])) ?>
            </div>
        </div>

        <table class="factura-tabla">
            <thead>
                <tr>
                    <th>Concepto</th>
                    <th class="tabla-num">Cantidad</th>
                    <th class="tabla-num">Precio unitario</th>
                    <th class="tabla-num">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= e($concepto) ?></td>
                    <td class="tabla-num"><?= e($cantidad) ?></td>
                    <td class="tabla-num"><?= e($precio) ?></td>
                    <td class="tabla-num"><?= e($total) ?></td>
                </tr>
            </tbody>
        </table>

        <div class="factura-total">
            <span class="factura-pago">Pago con saldo virtual</span>
            <div>
                <span class="factura-total-monto"><?= e($total) ?></span>
            </div>
        </div>

        <p class="factura-leyenda">
            Documento generado por CriptoSim con fines educativos. Los importes
            corresponden a saldo virtual y no representan dinero real. No tiene
            validez fiscal.
        </p>
    </div>

    <div class="factura-acciones">
        <a href="<?= e(es_admin()
            ? url('admin/transacciones.php')
            : url('usuario/historial.php')) ?>"
           class="boton boton-secundario">Volver</a>
        <button type="button" class="boton boton-primario" onclick="window.print()">
            Imprimir
        </button>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>