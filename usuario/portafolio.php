<?php
// CriptoSim - Mi Portafolio

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/trading.php';
require_once __DIR__ . '/../includes/precios.php';
require_once __DIR__ . '/../includes/mascota.php';

requiere_logueo();
verificar_estado_cuenta();

$usuario = usuario_actual();
$idUsuario = $usuario['id_usuario'];

// Los precios se actualizan ANTES de leer portafolio y saldo, para
// que el valor estimado use los precios recien movidos.
$avisoPrecios = actualizar_precios_simulados();
$estadoPrecios = estado_precios();

$error = '';
$exito = '';

// ------------------------------------------------------------
// Procesar la venta enviada por el modal
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verificar_csrf($_POST['csrf'] ?? '')) {
        $error = 'La sesion del formulario expiro. Intente de nuevo.';
    } elseif (($_POST['operacion'] ?? '') === 'venta') {
        $idCripto = (int) ($_POST['id_criptomoneda'] ?? 0);
        $cantidad = $_POST['cantidad'] ?? '';
        $resultado = registrar_venta($idUsuario, $idCripto, $cantidad);

        if ($resultado['exito']) {
            $exito = $resultado['mensaje'];
        } else {
            $error = $resultado['mensaje'];
        }
    } else {
        $error = 'Operacion no valida.';
    }
}

// Todo se relee de la base recien despues de operar, asi la tabla
// que se muestra ya incluye (o no) la operacion que se acaba de hacer.
$saldo = saldo_actual($idUsuario);
$portafolio = obtener_portafolio($idUsuario);

$titulo = 'Mi Portafolio';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="saludo">
    <h1>Mi Portafolio</h1>
    <p>
        Tus posiciones valoradas a los precios simulados. Saldo disponible:
        <strong><?= e(formato_q($saldo)) ?></strong>.
    </p>
</section>

<?php if ($error !== ''): ?>
    <?= simi_resultado(false, $error) ?>
<?php endif; ?>

<?php if ($exito !== ''): ?>
    <?= simi_resultado(true, $exito) ?>
<?php endif; ?>

<?php if ($avisoPrecios && !$avisoPrecios['exito'] && $estadoPrecios['actualizado_en'] === null): ?>
    <div class="alerta alerta-info"><?= e($avisoPrecios['mensaje']) ?></div>
<?php endif; ?>

<section class="seccion panel">
    <div class="filtros">
        <h2 class="seccion-titulo">Posiciones abiertas</h2>
        <span class="paginacion-info">
            <?= count($portafolio['monedas']) ?> criptomoneda(s)
        </span>
    </div>

    <?php if (empty($portafolio['monedas'])): ?>
        <div class="vacio">
            <?= simi_con_burbuja('alegre',
                'Todavia no posees ninguna criptomoneda. Compre en el mercado y esta pantalla se llena sola.',
                'vacia') ?>
            <p>Tu portafolio esta vacio.</p>
            <a href="<?= e(url('usuario/mercado.php')) ?>" class="boton boton-primario">Ir al mercado</a>
        </div>
    <?php else: ?>
        <div class="tabla-envoltura">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Criptomoneda</th>
                        <th class="tabla-num">Cantidad</th>
                        <th class="tabla-num">Precio actual</th>
                        <th class="tabla-num">Valor estimado</th>
                        <th class="tabla-num">Porcentaje</th>
                        <th>Vender</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($portafolio['monedas'] as $moneda):
                        $idCripto = (int) $moneda['id_criptomoneda'];
                        $cantidad = (float) $moneda['cantidad'];
                        ?>
                        <tr>
                            <td>
                                <span class="tabla-cripto"><?= e($moneda['simbolo']) ?></span>
                                <?= e($moneda['nombre']) ?>
                            </td>
                            <td class="tabla-num"><?= e(formato_cantidad($cantidad)) ?></td>
                            <td class="tabla-num"><?= e(formato_q($moneda['precio'])) ?></td>
                            <td class="tabla-num tabla-total"><?= e(formato_q($moneda['valor'])) ?></td>
                            <td class="tabla-num"><?= e((string) $moneda['porcentaje']) ?>%</td>
                            <td>
                                <!-- El mismo modal y los mismos atributos
                                     que usa el mercado, para que js/app.js
                                     funcione sin cambios. -->
                                <button type="button"
                                        class="boton boton-secundario boton-chico"
                                        data-operacion="venta"
                                        data-id="<?= $idCripto ?>"
                                        data-nombre="<?= e($moneda['nombre']) ?>"
                                        data-simbolo="<?= e($moneda['simbolo']) ?>"
                                        data-precio="<?= e((string) $moneda['precio']) ?>"
                                        data-disponible="<?= e((string) $cantidad) ?>"
                                        data-saldo="<?= e((string) $saldo) ?>">
                                    Vender
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3">Valor total del Portafolio</td>
                        <td class="tabla-num tabla-total"><?= e(formato_q($portafolio['total'])) ?></td>
                        <td class="tabla-num">100%</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <p class="ayuda">
            El valor estimado se calcula como cantidad multiplicada por el
            precio actual. El valor total es la suma de las filas.
            Los precios son simulados, no son valores reales de mercado.
        </p>

        <a href="<?= e(url('usuario/mercado.php')) ?>" class="boton boton-primario">Comprar mas</a>
    <?php endif; ?>
</section>

<!-- Modal unico de venta. Es una copia del de mercado.php para que
     js/app.js encuentre siempre los mismos id y no haya que duplicar
     ese JavaScript. -->
<dialog id="modal-operacion" class="modal">
    <form method="POST" action="" id="form-operacion" class="modal-tarjeta">
        <input type="hidden" name="csrf" value="<?= e(token_csrf()) ?>">
        <input type="hidden" name="operacion" id="campo-operacion" value="venta">
        <input type="hidden" name="id_criptomoneda" id="campo-id-cripto">

        <div class="modal-encabezado">
            <h2 id="modal-titulo">Vender</h2>
            <button type="button" class="modal-cerrar" id="modal-cerrar" aria-label="Cerrar">&times;</button>
        </div>

        <dl class="modal-datos">
            <div>
                <dt>Precio actual</dt>
                <dd id="modal-precio">-</dd>
            </div>
            <div>
                <dt>Cantidad disponible</dt>
                <dd id="modal-disponible">-</dd>
            </div>
        </dl>

        <label for="cantidad">Cantidad</label>
        <input type="number" id="cantidad" name="cantidad" step="0.00000001" min="0" value="0.01" required>
        <p class="ayuda" id="modal-ayuda">Use hasta 8 decimales.</p>

        <div class="modal-resumen">
            <div>
                <span>Total</span>
                <strong id="modal-total">Q0.00</strong>
            </div>
            <div>
                <span id="modal-leyenda-saldo">Recibiras</span>
                <strong id="modal-saldo">Q0.00</strong>
            </div>
        </div>

        <p class="modal-error" id="modal-error"></p>

        <div class="modal-acciones">
            <button type="button" class="boton boton-secundario" id="modal-cancelar">Cancelar</button>
            <button type="submit" class="boton boton-primario" id="modal-confirmar">Vender</button>
        </div>
    </form>
</dialog>

<script src="<?= e(url('js/app.js')) ?>"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
