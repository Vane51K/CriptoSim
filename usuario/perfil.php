<?php
// CriptoSim - Mi Perfil

// Unica escritura de esta pagina: la recarga de saldo virtual, para seguir simulando sin saldo.

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/trading.php';
require_once __DIR__ . '/../includes/precios.php';

requiere_logueo();
verificar_estado_cuenta();

$usuario = usuario_actual();
$idUsuario = $usuario['id_usuario'];

// Tope por recarga. La consigna no lo define: se pone un valor
// alto solo para que no se pueda cargar un monto absurdo.
const RECARGA_MAXIMA = 1000000;

// ------------------------------------------------------------
// Procesar la recarga de saldo enviada por el formulario
// ------------------------------------------------------------
$exito = '';
$error = '';
$montoForm = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verificar_csrf($_POST['csrf'] ?? '')) {
        $error = 'La sesion del formulario expiro. Intente de nuevo.';
    } else {
        $monto = round((float) ($_POST['monto'] ?? 0), 2);
        $montoForm = is_finite($monto) && $monto > 0 ? (string) $monto : '';

        if (!is_finite($monto) || $monto <= 0) {
            $error = 'Ingresa un monto mayor a cero.';
        } elseif ($monto > RECARGA_MAXIMA) {
            $error = 'El monto maximo por recarga es ' . formato_q(RECARGA_MAXIMA) . '.';
        } elseif (recargar_saldo($idUsuario, $monto)) {
            $exito = 'Recarga aplicada: se sumaron ' . formato_q($monto) . ' a tu saldo virtual.';
        } else {
            $error = 'No se pudo aplicar la recarga.';
        }
    }
}

// La fecha de registro y el estado se leen de la base, no de la
// sesion: la sesion no los guarda y el administrador puede cambiar
// el estado en cualquier momento.
$stmt = $conexion->prepare(
    "SELECT nombre, correo, fecha_registro, estado, rol FROM usuarios WHERE id_usuario = ?"
);
$stmt->bind_param('i', $idUsuario);
$stmt->execute();
$cuenta = $stmt->get_result()->fetch_assoc();
$stmt->close();

$etiquetasEstado = [
    'activo'     => 'Activo',
    'suspendido' => 'Suspendido',
    'baja'       => 'Dada de baja',
];

$saldo = saldo_actual($idUsuario);
$portafolio = obtener_portafolio($idUsuario);
$resumen = obtener_resumen_operaciones($idUsuario);
$criptosPoseidas = contar_criptos_poseidas($idUsuario);

$titulo = 'Mi Perfil';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="saludo">
    <h1>Mi Perfil</h1>
    <p>Informacion de tu cuenta en el simulador</p>
</section>

<section class="seccion panel">
    <h2 class="seccion-titulo">Datos de la cuenta</h2>

    <div class="tabla-envoltura">
        <table class="tabla">
            <tbody>
                <tr>
                    <td><strong>Nombre</strong></td>
                    <td><?= e($cuenta['nombre']) ?></td>
                </tr>
                <tr>
                    <td><strong>Correo electronico</strong></td>
                    <td><?= e($cuenta['correo']) ?></td>
                </tr>
                <tr>
                    <td><strong>Fecha de registro</strong></td>
                    <td><?= e(fecha_legible($cuenta['fecha_registro'])) ?></td>
                </tr>
                <tr>
                    <td><strong>Estado de la cuenta</strong></td>
                    <td>
                        <span class="badge badge-<?= e($cuenta['estado']) ?>">
                            <?= e($etiquetasEstado[$cuenta['estado']] ?? $cuenta['estado']) ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td><strong>Tipo de cuenta</strong></td>
                    <td><?= $cuenta['rol'] === 'admin' ? 'Administrador' : 'Usuario' ?></td>
                </tr>
                <tr>
                    <td><strong>Saldo virtual</strong></td>
                    <td><?= e(formato_q($saldo)) ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

<section class="seccion panel">
    <h2 class="seccion-titulo">Recargar saldo virtual</h2>

    <?php if ($error !== ''): ?>
        <div class="alerta alerta-error"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if ($exito !== ''): ?>
        <div class="alerta alerta-exito"><?= e($exito) ?></div>
    <?php endif; ?>

    <p class="ayuda">
        El saldo es ficticio y la recarga no usa dinero real: sirve para
        seguir simulando cuando no te queda saldo disponible.
    </p>

    <form method="POST" action="" novalidate class="recarga">
        <input type="hidden" name="csrf" value="<?= e(token_csrf()) ?>">

        <label for="monto">Monto a recargar</label>
        <div class="recarga-fila">
            <input type="number" id="monto" name="monto"
                   min="0.01" max="1000000" step="0.01"
                   placeholder="5000.00" value="<?= e($montoForm) ?>">
            <button type="submit" class="boton boton-primario">Recargar</button>
        </div>
        <p class="ayuda">
            Entre Q0.01 y <?= e(formato_q(RECARGA_MAXIMA)) ?> por recarga.
        </p>
    </form>
</section>

<section class="seccion">
    <h2 class="seccion-titulo">Resumen de actividad</h2>

    <div class="grid-indicadores">
        <div class="indicador">
            <p class="indicador-etiqueta">Valor del Portafolio</p>
            <p class="indicador-valor"><?= e(formato_q($portafolio['total'])) ?></p>
            <p class="indicador-pie"><?= count($portafolio['monedas']) ?> criptomoneda(s)</p>
        </div>

        <div class="indicador">
            <p class="indicador-etiqueta">Operaciones totales</p>
            <p class="indicador-valor"><?= $resumen['total'] ?></p>
            <p class="indicador-pie"><?= $criptosPoseidas ?> en cartera</p>
        </div>

        <div class="indicador">
            <p class="indicador-etiqueta">Compras</p>
            <p class="indicador-valor"><?= $resumen['compras'] ?></p>
            <p class="indicador-pie">Operaciones de compra</p>
        </div>

        <div class="indicador">
            <p class="indicador-etiqueta">Ventas</p>
            <p class="indicador-valor"><?= $resumen['ventas'] ?></p>
            <p class="indicador-pie">Operaciones de venta</p>
        </div>
    </div>
</section>

<section class="seccion panel">
    <h2 class="seccion-titulo">Mis posiciones</h2>

    <?php if (empty($portafolio['monedas'])): ?>
        <p class="sin-datos">
            Todavia no posees posiciones. Podes comprar en el mercado.
        </p>
        <a href="<?= e(url('usuario/mercado.php')) ?>" class="boton boton-primario">Ir al mercado</a>
    <?php else: ?>
        <div class="tabla-envoltura">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Criptomoneda</th>
                        <th class="tabla-num">Cantidad</th>
                        <th class="tabla-num">Precio actual</th>
                        <th class="tabla-num">Valor estimado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($portafolio['monedas'] as $moneda): ?>
                        <tr>
                            <td>
                                <span class="tabla-cripto"><?= e($moneda['simbolo']) ?></span>
                                <?= e($moneda['nombre']) ?>
                            </td>
                            <td class="tabla-num"><?= e(formato_cantidad($moneda['cantidad'])) ?></td>
                            <td class="tabla-num"><?= e(formato_q($moneda['precio'])) ?></td>
                            <td class="tabla-num tabla-total"><?= e(formato_q($moneda['valor'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <a href="<?= e(url('usuario/portafolio.php')) ?>" class="enlace-subrayado">Ver mi portafolio completo</a>
    <?php endif; ?>
</section>

<p class="ayuda">
    Si necesitas cambiar tu correo o tu contrasena, dale de baja a la cuenta
    desde el administrador y registrate de nuevo. La consigna no pide un
    formulario de edicion de perfil.
</p>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
