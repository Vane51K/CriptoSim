<?php
// CriptoSim - Detalle de un usuario

// Solo lectura: las acciones de estado se hacen desde admin/usuarios.php.

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/trading.php';

requiere_admin();
verificar_estado_cuenta();

// El id llega por GET. Se castea a entero, de modo que un valor
// malicioso se convierte en 0 y no llega a la consulta como texto.
$idUsuario = (int) ($_GET['id'] ?? 0);

$stmt = $conexion->prepare(
    "SELECT id_usuario, nombre, correo, saldo, rol, estado, fecha_registro
     FROM usuarios WHERE id_usuario = ?"
);
$stmt->bind_param('i', $idUsuario);
$stmt->execute();
$cuenta = $stmt->get_result()->fetch_assoc();
$stmt->close();

$titulo = 'Usuario';
require_once __DIR__ . '/../includes/header.php';
?>

<?php if (!$cuenta): ?>
    <section class="saludo">
        <h1>Usuario no encontrado</h1>
        <p>La cuenta solicitada no existe en el simulador.</p>
    </section>

    <section class="seccion panel">
        <a href="<?= e(url('admin/usuarios.php')) ?>" class="boton boton-primario">
            Volver a la lista
        </a>
    </section>
<?php else: ?>
    <?php
    $etiquetasEstado = [
        'activo'     => 'Activo',
        'suspendido' => 'Suspendido',
        'baja'       => 'Dada de baja',
    ];

    $idObjetivo = (int) $cuenta['id_usuario'];
    $saldo = (float) $cuenta['saldo'];
    $portafolio = obtener_portafolio($idObjetivo);
    $historial = obtener_historial($idObjetivo, 'todas', 20);
    $resumen = obtener_resumen_operaciones($idObjetivo);
    $esPropia = $idObjetivo === (int) usuario_actual()['id_usuario'];
    ?>

    <section class="saludo">
        <h1><?= e($cuenta['nombre']) ?></h1>
        <p>
            <a href="<?= e(url('admin/usuarios.php')) ?>" class="enlace-subrayado">Volver a usuarios</a>
        </p>
    </section>

    <section class="seccion">
        <div class="grid-indicadores">
            <div class="indicador">
                <p class="indicador-etiqueta">Saldo virtual</p>
                <p class="indicador-valor"><?= e(formato_q($saldo)) ?></p>
                <p class="indicador-pie">Dinero de simulacion</p>
            </div>

            <div class="indicador">
                <p class="indicador-etiqueta">Valor del Portafolio</p>
                <p class="indicador-valor"><?= e(formato_q($portafolio['total'])) ?></p>
                <p class="indicador-pie"><?= count($portafolio['monedas']) ?> criptomoneda(s)</p>
            </div>

            <div class="indicador">
                <p class="indicador-etiqueta">Operaciones</p>
                <p class="indicador-valor"><?= $resumen['total'] ?></p>
                <p class="indicador-pie"><?= $resumen['compras'] ?> compras / <?= $resumen['ventas'] ?> ventas</p>
            </div>

            <div class="indicador">
                <p class="indicador-etiqueta">Estado</p>
                <p class="indicador-valor" style="font-size: 1.1rem;">
                    <span class="badge badge-<?= e($cuenta['estado']) ?>">
                        <?= e($etiquetasEstado[$cuenta['estado']] ?? $cuenta['estado']) ?>
                    </span>
                </p>
                <p class="indicador-pie">
                    <?= $cuenta['rol'] === 'admin' ? 'Administrador' : 'Usuario' ?>
                </p>
            </div>
        </div>
    </section>

    <section class="seccion panel">
        <h2 class="seccion-titulo">Datos de la cuenta</h2>

        <div class="tabla-envoltura">
            <table class="tabla">
                <tbody>
                    <tr>
                        <td><strong>Identificador</strong></td>
                        <td>#<?= $idObjetivo ?></td>
                    </tr>
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
                        <td><?= e($etiquetasEstado[$cuenta['estado']] ?? $cuenta['estado']) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <?php if (!$esPropia): ?>
            <a href="<?= e(url('admin/usuarios.php?q=' . urlencode($cuenta['correo']))) ?>"
               class="boton boton-primario">Administrar esta cuenta</a>
        <?php else: ?>
            <p class="ayuda">
                Esta es su propia cuenta de administrador. Por seguridad no
                puede cambiar su propio estado.
            </p>
        <?php endif; ?>
    </section>

    <section class="seccion panel">
        <h2 class="seccion-titulo">Posiciones del portafolio</h2>

        <?php if (empty($portafolio['monedas'])): ?>
            <p class="sin-datos">Este usuario no posee posiciones.</p>
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
                    <tfoot>
                        <tr>
                            <td colspan="3">Valor total</td>
                            <td class="tabla-num tabla-total"><?= e(formato_q($portafolio['total'])) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="seccion panel">
        <h2 class="seccion-titulo">Ultimas operaciones</h2>

        <?php if (empty($historial)): ?>
            <p class="sin-datos">Este usuario todavia no registro operaciones.</p>
        <?php else: ?>
            <div class="tabla-envoltura">
                <table class="tabla">
                    <thead>
                        <tr>
                            <th>Operacion</th>
                            <th>Criptomoneda</th>
                            <th class="tabla-num">Cantidad</th>
                            <th class="tabla-num">Precio unitario</th>
                            <th class="tabla-num">Total</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historial as $mov): ?>
                            <tr>
                                <td>
                                    <span class="badge badge-<?= e($mov['tipo']) ?>">
                                        <?= $mov['tipo'] === 'compra' ? 'Compra' : 'Venta' ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="tabla-cripto"><?= e($mov['simbolo']) ?></span>
                                    <?= e($mov['nombre']) ?>
                                </td>
                                <td class="tabla-num"><?= e(formato_cantidad($mov['cantidad'])) ?></td>
                                <td class="tabla-num"><?= e(formato_q($mov['precio_unitario'])) ?></td>
                                <td class="tabla-num tabla-total"><?= e(formato_q($mov['total'])) ?></td>
                                <td class="tabla-fecha"><?= e(fecha_legible($mov['fecha'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="ayuda">Se muestran las 20 operaciones mas recientes.</p>
        <?php endif; ?>
    </section>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
