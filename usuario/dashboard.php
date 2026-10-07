<?php
// CriptoSim - Dashboard del usuario

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/trading.php';
require_once __DIR__ . '/../includes/precios.php';
require_once __DIR__ . '/../includes/mascota.php';
require_once __DIR__ . '/../includes/graficas.php';
require_once __DIR__ . '/../includes/consejos.php';

requiere_logueo();
verificar_estado_cuenta();

$usuario = usuario_actual();
$idUsuario = $usuario['id_usuario'];

// Mueve los precios simulados si ya paso el intervalo, antes de
// leer el portafolio y el mercado para que muestren valores frescos.
actualizar_precios_simulados();

// Los datos se leen de la base, no de la sesion, para que
// el saldo y el portafolio esten siempre actualizados
$saldo = saldo_actual($idUsuario);
$portafolio = obtener_portafolio($idUsuario);
$resumen = obtener_resumen_operaciones($idUsuario);
$criptosPoseidas = contar_criptos_poseidas($idUsuario);
$ultimas = obtener_historial($idUsuario, 'todas', 5);
$mercado = aplicar_precios(listar_mercado());
$mes = operaciones_este_mes($idUsuario);

// Consejo de SIMI para el cuadro de pensamientos. Se calcula aca
// para pintarlo ya listo en la primera carga; despues js/consejo.js
// lo refresca por JSON sin recargar la pagina.
$consejo = consejo_simi($mercado);

$primerNombre = explode(' ', $usuario['nombre'])[0];

// Variacion de operaciones contra el mes anterior
if ($mes['anterior'] > 0) {
    $variacion = round((($mes['actual'] - $mes['anterior']) / $mes['anterior']) * 100);
    $textoVariacion = ($variacion >= 0 ? '+' : '') . $variacion . '% vs mes anterior';
} elseif ($mes['actual'] > 0) {
    $variacion = 100;
    $textoVariacion = 'Primer mes con actividad';
} else {
    $variacion = 0;
    $textoVariacion = 'Aun sin operaciones';
}

$titulo = 'Mi Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="saludo">
    <h1>Bienvenido nuevamente, <?= e($primerNombre) ?>.</h1>
    <p>Listo para continuar con tu simulacion?</p>
</section>

<section class="seccion">
    <div class="grid-indicadores">
        <div class="indicador">
            <p class="indicador-etiqueta">Saldo disponible</p>
            <p class="indicador-valor"><?= e(formato_q($saldo)) ?></p>
            <p class="indicador-pie">Saldo virtual de simulacion</p>
        </div>

        <div class="indicador">
            <p class="indicador-etiqueta">Valor del portafolio</p>
            <p class="indicador-valor"><?= e(formato_q($portafolio['total'])) ?></p>
            <p class="indicador-pie"><?= $criptosPoseidas ?> criptomoneda<?= $criptosPoseidas == 1 ? '' : 's' ?></p>
        </div>

        <div class="indicador">
            <p class="indicador-etiqueta">Operaciones</p>
            <p class="indicador-valor"><?= $resumen['total'] ?></p>
            <p class="indicador-pie"><?= e($textoVariacion) ?></p>
        </div>

        <div class="indicador">
            <p class="indicador-etiqueta">Criptos que posee</p>
            <p class="indicador-valor"><?= $criptosPoseidas ?></p>
            <p class="indicador-pie">de <?= count($mercado) ?> disponibles</p>
        </div>
    </div>
</section>

<div class="dashboard-columnas">
    <div class="dashboard-columna">
        <section class="seccion panel">
            <h2 class="seccion-titulo">Resumen de portafolio</h2>

            <?php if (empty($portafolio['monedas'])): ?>
                <div class="vacio">
                    <?= simi_con_burbuja('alegre',
                        'Tu portafolio esta vacio, asi que todavia no podiste perder ni ganar. Vamos al mercado y armamos la primera posicion.',
                        'vacia') ?>
                    <a href="<?= e(url('usuario/mercado.php')) ?>" class="boton boton-primario">Ir al mercado</a>
                </div>
            <?php else: ?>
                <?php
                $colores = ['#853953', '#612D53', '#A5708F', '#C49BB8'];
                $porcentajes = [];
                ?>
                <div class="barra-portafolio">
                    <?php foreach ($portafolio['monedas'] as $i => $moneda): ?>
                        <div class="barra-segmento"
                             style="width: <?= e((string) $moneda['porcentaje']) ?>%; background-color: <?= e($colores[$i % count($colores)]) ?>"
                             title="<?= e($moneda['nombre']) ?> <?= e((string) $moneda['porcentaje']) ?>%"></div>
                    <?php endforeach; ?>
                </div>

                <ul class="leyenda-portafolio">
                    <?php foreach ($portafolio['monedas'] as $i => $moneda): ?>
                        <li>
                            <span class="leyenda-punto" style="background-color: <?= e($colores[$i % count($colores)]) ?>"></span>
                            <span class="leyenda-nombre"><?= e($moneda['nombre']) ?></span>
                            <span class="leyenda-valor"><?= e(formato_q($moneda['valor'])) ?></span>
                            <span class="leyenda-porcentaje"><?= e((string) $moneda['porcentaje']) ?>%</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <section class="seccion panel consejo" id="consejo-simi"
                 data-url="<?= e(url('usuario/consejo.php')) ?>">
            <div class="consejo-encabezado">
                <img class="consejo-cara"
                     src="<?= e(url('img/simi/simi-pensativo-web.png')) ?>"
                     alt="SIMI pensando" width="88" height="88">
                <div>
                    <h2 class="seccion-titulo">Pensamientos de Simi</h2>
                    <p class="consejo-cuando" data-consejo-cuando>Actualizado <?= e($consejo['cuando']) ?></p>
                </div>
            </div>

            <p class="consejo-frase" data-consejo-texto><?= e($consejo['texto']) ?></p>

            <p class="consejo-aviso">Lectura educativa del mercado simulado. SIMI no asesora sobre inversiones reales.</p>
        </section>
    </div>

    <section class="seccion panel">
        <h2 class="seccion-titulo">Tu actividad</h2>

        <div class="actividad-resumen">
            <div class="actividad-dato">
                <span class="actividad-numero"><?= $resumen['compras'] ?></span>
                <span class="actividad-etiqueta">Compras</span>
            </div>
            <div class="actividad-dato">
                <span class="actividad-numero"><?= $resumen['ventas'] ?></span>
                <span class="actividad-etiqueta">Ventas</span>
            </div>
        </div>

        <h3 class="subtitulo-panel">Ultimas transacciones</h3>

        <?php if (empty($ultimas)): ?>
            <p class="sin-datos">No tenes operaciones registradas todavia.</p>
        <?php else: ?>
            <ul class="actividad-lista">
                <?php foreach ($ultimas as $mov): ?>
                    <li class="actividad-item">
                        <span class="badge badge-<?= e($mov['tipo']) ?>">
                            <?= $mov['tipo'] === 'compra' ? 'Compra' : 'Venta' ?>
                        </span>
                        <span class="actividad-descripcion">
                            <?= e($mov['cantidad']) ?> <?= e($mov['simbolo']) ?>
                        </span>
                        <span class="actividad-hora"><?= e(fecha_legible($mov['fecha'])) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <a href="<?= e(url('usuario/historial.php')) ?>" class="enlace-subrayado">Ver historial completo</a>
        <?php endif; ?>
    </section>
</div>

<section class="seccion panel">
    <h2 class="seccion-titulo">Actividad del usuario</h2>
    <p class="ayuda">Visualizacion sencilla con datos que ya existen en tus transacciones.</p>

    <?php
    $datosCvV = datos_compras_ventas($idUsuario);
    $datosMonedas = datos_monedas_mas_usadas($idUsuario, 6);
    ?>
    <div class="grid-graficas">
        <?= grafica(
            'grafica-cvv',
            'bar',
            'Compras vs Ventas',
            $datosCvV['etiquetas'],
            $datosCvV['valores'],
            'No hay operaciones registradas todavia.',
            ['formato' => function ($etiqueta, $valor) {
                return (string) $valor . ' operacion' . ((int) $valor == 1 ? '' : 'es');
            }]
        ) ?>
        <?= grafica(
            'grafica-monedas',
            'doughnut',
            'Monedas mas utilizadas',
            $datosMonedas['etiquetas'],
            $datosMonedas['valores'],
            'No hay operaciones registradas todavia.',
            ['formato' => function ($etiqueta, $valor) {
                return formato_cantidad($valor) . ' ' . $etiqueta;
            }]
        ) ?>
    </div>
</section>

<section class="seccion">
    <h2 class="seccion-titulo">Criptografias disponibles</h2>

    <div class="estado-mercado">
        <span class="punto-estado"></span> Mercado activo
        <span class="estado-nota">
            Precios simulados con fines educativos
        </span>
    </div>

    <div class="grid-tarjetas">
        <?php foreach ($mercado as $cripto):
            $poseida = cantidad_poseida($idUsuario, $cripto['id_criptomoneda']);
            ?>
            <div class="tarjeta cripto-tarjeta">
                <div class="cripto-encabezado">
                    <span class="cripto-simbolo"><?= e($cripto['simbolo']) ?></span>
                    <div>
                        <h3><?= e($cripto['nombre']) ?></h3>
                        <span class="cripto-simbolo-texto"><?= e($cripto['simbolo']) ?></span>
                    </div>
                </div>

                <p class="cripto-precio"><?= e(formato_precio($cripto['precio'])) ?></p>

                <span class="variacion <?= $cripto['variacion'] >= 0 ? 'variacion-sube' : 'variacion-baja' ?>">
                    <?= $cripto['variacion'] >= 0 ? '+' : '' ?><?= e((string) $cripto['variacion']) ?>%
                </span>

                <?php if ($poseida > 0): ?>
                    <p class="poseida">Posee <?= e(formato_cantidad($poseida)) ?> <?= e($cripto['simbolo']) ?></p>
                <?php endif; ?>

                <div class="cripto-acciones">
                    <a href="<?= e(url('usuario/mercado.php?operacion=comprar&cripto=' . $cripto['id_criptomoneda'])) ?>"
                       class="boton boton-primario boton-chico">Comprar</a>
                    <a href="<?= e(url('usuario/mercado.php?operacion=vender&cripto=' . $cripto['id_criptomoneda'])) ?>"
                       class="boton boton-secundario boton-chico">Vender</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<script src="<?= e(url('js/consejo.js')) ?>" defer></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
