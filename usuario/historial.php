<?php
// CriptoSim - Historial de transacciones del usuario

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/trading.php';
require_once __DIR__ . '/../includes/mascota.php';

requiere_logueo();
verificar_estado_cuenta();

$usuario = usuario_actual();
$idUsuario = $usuario['id_usuario'];

// El filtro llega por GET y solo se aceptan los tres valores
// conocidos. Cualquier otra cosa se cae a "todas".
$opcionesFiltro = ['todas', 'compra', 'venta'];
$filtro = $_GET['filtro'] ?? 'todas';

if (!in_array($filtro, $opcionesFiltro, true)) {
    $filtro = 'todas';
}

$porPagina = 10;
$pagina = max(1, (int) ($_GET['pagina'] ?? 1));
$total = contar_historial($idUsuario, $filtro);
$totalPaginas = max(1, (int) ceil($total / $porPagina));

// Si se pide una pagina que ya no existe, se vuelve a la ultima
if ($pagina > $totalPaginas) {
    $pagina = $totalPaginas;
}

$offset = ($pagina - 1) * $porPagina;
$transacciones = obtener_historial($idUsuario, $filtro, $porPagina, $offset);

// Totales del filtro actual, no del historial completo
$totalCompras = 0;
$totalVentas = 0;
foreach ($transacciones as $mov) {
    if ($mov['tipo'] === 'compra') {
        $totalCompras += (float) $mov['total'];
    } else {
        $totalVentas += (float) $mov['total'];
    }
}

// Construye una URL de la pagina conservando el filtro activo
function enlace_pagina($pagina, $filtro)
{
    return url('usuario/historial.php?' . http_build_query([
        'pagina' => $pagina,
        'filtro' => $filtro,
    ]));
}

$etiquetas = [
    'todas'  => 'Todas',
    'compra' => 'Compras',
    'venta'  => 'Ventas',
];

$titulo = 'Mi Historial';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="saludo">
    <h1>Mi Historial</h1>
    <p>Todas tus operaciones registradas en el simulador</p>
</section>

<section class="seccion">
    <div class="grid-indicadores">
        <div class="indicador">
            <p class="indicador-etiqueta">Operaciones en vista</p>
            <p class="indicador-valor"><?= $total ?></p>
            <p class="indicador-pie"><?= e($etiquetas[$filtro]) ?></p>
        </div>

        <div class="indicador">
            <p class="indicador-etiqueta">Comprado en esta pagina</p>
            <p class="indicador-valor"><?= e(formato_q($totalCompras)) ?></p>
            <p class="indicador-pie">Suma de las compras visibles</p>
        </div>

        <div class="indicador">
            <p class="indicador-etiqueta">Vendido en esta pagina</p>
            <p class="indicador-valor"><?= e(formato_q($totalVentas)) ?></p>
            <p class="indicador-pie">Suma de las ventas visibles</p>
        </div>
    </div>
</section>

<section class="seccion panel">
    <div class="filtros">
        <h2 class="seccion-titulo">Operaciones</h2>

        <div class="filtros-grupo" role="group" aria-label="Filtrar por tipo de operacion">
            <?php foreach ($etiquetas as $clave => $texto): ?>
                <a href="<?= e(url('usuario/historial.php?filtro=' . $clave)) ?>"
                   class="filtro-chip <?= $filtro === $clave ? 'filtro-activo' : '' ?>">
                    <?= e($texto) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (empty($transacciones)): ?>
        <div class="vacio">
            <?php if ($filtro === 'todas'): ?>
                <?= simi_con_burbuja('alegre',
                    'Tu historial arranca en blanco. La primera operacion es la que mas enseña: probá con una compra pequeña.',
                    'vacia') ?>
                <p>
                    Todavia no tenes operaciones registradas.
                </p>
<?php else: ?>
                <?php /* No hay 'preocupado' subida. Pedirla caeria en
                         silencio a la base y el usuario veria a Simi
                         neutra, que es justo lo que no queremos. */ ?>
                <?= simi_con_burbuja('triste',
                    'No hay operaciones de tipo "' . e($etiquetas[$filtro]) . '". Quizá las hiciste y usaste otro filtro.',
                    'vacia') ?>
                <p>
                    No tenes operaciones de tipo "<?= e($etiquetas[$filtro]) ?>" todavia.
                </p>
            <?php endif; ?>
            <a href="<?= e(url('usuario/mercado.php')) ?>" class="boton boton-primario">Ir al mercado</a>
        </div>
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
                        <th>Factura</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transacciones as $mov): ?>
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
                            <td>
                                <a href="<?= e(url('usuario/factura.php?id=' . (int) $mov['id_transaccion'])) ?>"
                                   class="boton boton-secundario boton-compacto">Factura</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPaginas > 1): ?>
            <nav class="paginacion" aria-label="Paginacion del historial">
                <?php if ($pagina > 1): ?>
                    <a href="<?= e(enlace_pagina($pagina - 1, $filtro)) ?>" class="paginacion-enlace">&larr; Anterior</a>
                <?php else: ?>
                    <span class="paginacion-enlace paginacion-inactiva">&larr; Anterior</span>
                <?php endif; ?>

                <span class="paginacion-info">
                    Pagina <?= $pagina ?> de <?= $totalPaginas ?>
                </span>

                <?php if ($pagina < $totalPaginas): ?>
                    <a href="<?= e(enlace_pagina($pagina + 1, $filtro)) ?>" class="paginacion-enlace">Siguiente &rarr;</a>
                <?php else: ?>
                    <span class="paginacion-enlace paginacion-inactiva">Siguiente &rarr;</span>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
