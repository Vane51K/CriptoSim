<?php
// CriptoSim - Mercado de criptomonedas

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/trading.php';
require_once __DIR__ . '/../includes/precios.php';
// SIMI muestra el resultado de la operacion. Se carga explicito y no
// por herencia: el helper se usa en esta pagina, no en las demas.
require_once __DIR__ . '/../includes/mascota.php';

requiere_logueo();
verificar_estado_cuenta();

$usuario = usuario_actual();
$idUsuario = $usuario['id_usuario'];

// Los precios se actualizan ANTES de procesar la operacion, para
// que comprar y vender siempre use el precio vigente del mercado.
actualizar_precios_simulados();

$error = '';
$exito = '';

// ------------------------------------------------------------
// Procesar compra o venta enviada por el formulario
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verificar_csrf($_POST['csrf'] ?? '')) {
        $error = 'La sesion del formulario expiro. Intente de nuevo.';
    } else {
        $operacion = $_POST['operacion'] ?? '';
        $idCripto = (int) ($_POST['id_criptomoneda'] ?? 0);
        $cantidad = $_POST['cantidad'] ?? '';

        if ($operacion === 'compra') {
            $resultado = registrar_compra($idUsuario, $idCripto, $cantidad);
        } elseif ($operacion === 'venta') {
            $resultado = registrar_venta($idUsuario, $idCripto, $cantidad);
        } else {
            $resultado = ['exito' => false, 'mensaje' => 'Operacion no valida.'];
        }

        if ($resultado['exito']) {
            $exito = $resultado['mensaje'];
        } else {
            $error = $resultado['mensaje'];
        }
    }
}

$saldo = saldo_actual($idUsuario);
$mercado = aplicar_precios(listar_mercado());
$portafolio = obtener_portafolio($idUsuario);

// Cantidad que el usuario posee de cada moneda, para el formulario de venta
$poseidas = [];
foreach ($portafolio['monedas'] as $moneda) {
    $poseidas[(int) $moneda['id_criptomoneda']] = (float) $moneda['cantidad'];
}

// El dashboard y Mi Portafolio enlazan al mercado con
// ?operacion=comprar&cripto=N para abrir directamente la modal de esa
// moneda. Sin esto el enlace llegaba acá y no pasaba nada, porque el
// modal solo se abre al hacer clic. Los tres valores validos se
// mapean al nombre interno de la operacion: comprar -> compra,
// vender -> venta. Cualquier otra cosa se ignora.
$operacionPedida = '';
$criptoPedida = 0;

$etiquetasOperacion = ['comprar' => 'compra', 'vender' => 'venta'];
$pedido = (string) ($_GET['operacion'] ?? '');

if (isset($etiquetasOperacion[$pedido])) {
    $operacionPedida = $etiquetasOperacion[$pedido];
    $criptoPedida = (int) ($_GET['cripto'] ?? 0);
}

$titulo = 'Mercado';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="saludo">
    <h1>Mercado</h1>
    <p>Precios simulados con fines educativos. Tu saldo actual es
       <strong><?= e(formato_q($saldo)) ?></strong>.</p>
</section>

<?php if ($error !== ''): ?>
    <?= simi_resultado(false, $error) ?>
<?php endif; ?>

<?php if ($exito !== ''): ?>
    <?= simi_resultado(true, $exito) ?>
<?php endif; ?>

<section class="seccion">
    <div class="estado-mercado">
        <span class="punto-estado"></span> Mercado activo

        <span class="estado-nota">Los precios no son reales y no representan valores de mercado</span>
    </div>

    <div class="grid-tarjetas">
        <?php foreach ($mercado as $cripto):
            $idCripto = (int) $cripto['id_criptomoneda'];
            $poseida = $poseidas[$idCripto] ?? 0.0;

            // Solo se marca un boton: el de la moneda pedida por la URL
            // y el de la operacion que tambien pide la URL. Con un solo
            // flag los dos botones quedaban marcados y el script abria
            // el primero, o sea el equivocado al pedir una venta.
            // El de Vender queda marcado aunque no tenga posicion: en
            // ese caso esta deshabilitado y el navegador no le hace
            // clic, que es lo correcto.
            $esLaMonedaPedida = $criptoPedida === $idCripto;
            $abrirCompra = $esLaMonedaPedida && $operacionPedida === 'compra';
            $abrirVenta  = $esLaMonedaPedida && $operacionPedida === 'venta';
            ?>
            <div class="tarjeta cripto-tarjeta">
                <div class="cripto-encabezado">
                    <span class="cripto-simbolo"><?= e($cripto['simbolo']) ?></span>
                    <div>
                        <h3><?= e($cripto['nombre']) ?></h3>
                        <span class="cripto-simbolo-texto"><?= e($cripto['simbolo']) ?></span>
                    </div>
                </div>

                <p class="cripto-precio"
                   data-precio="<?= e(rtrim(rtrim(number_format((float) $cripto['precio'], 8, '.', ''), '0'), '.')) ?>">
                    <?= e(formato_precio($cripto['precio'])) ?>
                </p>

                <span class="variacion <?= $cripto['variacion'] >= 0 ? 'variacion-sube' : 'variacion-baja' ?>">
                    <?= $cripto['variacion'] >= 0 ? '+' : '' ?><?= e((string) $cripto['variacion']) ?>%
                </span>

                <?php if ($poseida > 0): ?>
                    <p class="poseida">
                        Posee <?= e(formato_cantidad($poseida)) ?> <?= e($cripto['simbolo']) ?>
                    </p>
                <?php endif; ?>

                <div class="cripto-acciones">
                    <button type="button"
                            class="boton boton-primario boton-chico"
                            data-operacion="compra"
                            data-id="<?= $idCripto ?>"
                            data-nombre="<?= e($cripto['nombre']) ?>"
                            data-simbolo="<?= e($cripto['simbolo']) ?>"
                            data-precio="<?= e((string) $cripto['precio']) ?>"
                            data-disponible="<?= e((string) $poseida) ?>"
                            data-saldo="<?= e((string) $saldo) ?>"
                            <?= $abrirCompra ? 'data-autofoco="1"' : '' ?>>
                        Comprar
                    </button>

                    <button type="button"
                            class="boton boton-secundario boton-chico"
                            data-operacion="venta"
                            data-id="<?= $idCripto ?>"
                            data-nombre="<?= e($cripto['nombre']) ?>"
                            data-simbolo="<?= e($cripto['simbolo']) ?>"
                            data-precio="<?= e((string) $cripto['precio']) ?>"
                            data-disponible="<?= e((string) $poseida) ?>"
                            data-saldo="<?= e((string) $saldo) ?>"
                            <?= $abrirVenta ? 'data-autofoco="1"' : '' ?>
                            <?= $poseida > 0 ? '' : 'disabled' ?>>
                        Vender
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Modal unico reutilizado para comprar y vender -->
<dialog id="modal-operacion" class="modal">
    <form method="POST" action="" id="form-operacion" class="modal-tarjeta">
        <input type="hidden" name="csrf" value="<?= e(token_csrf()) ?>">
        <input type="hidden" name="operacion" id="campo-operacion">
        <input type="hidden" name="id_criptomoneda" id="campo-id-cripto">

        <div class="modal-encabezado">
            <h2 id="modal-titulo">Operacion</h2>
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
                <span id="modal-leyenda-saldo">Saldo disponible</span>
                <strong id="modal-saldo">Q0.00</strong>
            </div>
        </div>

        <p class="modal-error" id="modal-error"></p>

        <div class="modal-acciones">
            <button type="button" class="boton boton-secundario" id="modal-cancelar">Cancelar</button>
            <button type="submit" class="boton boton-primario" id="modal-confirmar">Confirmar</button>
        </div>
    </form>
</dialog>

<script src="<?= e(url('js/app.js')) ?>"></script>

<?php if ($criptoPedida > 0 && $operacionPedida !== ''): ?>
    <script>
        // El dashboard y Mi Portafolio llegan con ?operacion=...&cripto=N.
        // app.js solo abre la modal cuando recibe un clic, asi que se
        // lo damos nosotros. El boton marcado ya lleva los datos de la
        // moneda, por eso no hace falta pasarle nada mas.
        (function () {
            var boton = document.querySelector('[data-autofoco]');

            if (boton && !boton.disabled) {
                boton.click();
            }
        })();
    </script>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
