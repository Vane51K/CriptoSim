<?php
// CriptoSim - Barra lateral comun

// Reemplaza a la cabecera convencional.

require_once __DIR__ . '/auth.php';

$titulo = $titulo ?? 'CriptoSim';
$flash = obtener_flash();

// Paginas que ya dicen su titulo dentro de una tarjeta (login) pueden
// apagar la barra con $ocultarCabecera = true.
$ocultarCabecera = $ocultarCabecera ?? false;

// Se usa para marcar la opcion activa del menu.
$paginaActual = basename($_SERVER['SCRIPT_NAME'] ?? '');

// Iconos en linea (SVG). Sin librerias externas: la pagina no depende
// de internet para mostrarlos y se colorean con color: currentColor.
$icoAtributos = 'viewBox="0 0 24 24" fill="none" stroke="currentColor" '
    . 'stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"';

$ico = [
    'inicio'    => '<path d="M3.6 10.8 12 4l8.4 6.8"/><path d="M5.8 9.6V20h12.4V9.6"/><path d="M9.8 20v-5.4h4.4V20"/>',
    'panel'     => '<rect x="3.6" y="3.6" width="7" height="7" rx="1.6"/><rect x="13.4" y="3.6" width="7" height="7" rx="1.6"/><rect x="3.6" y="13.4" width="7" height="7" rx="1.6"/><rect x="13.4" y="13.4" width="7" height="7" rx="1.6"/>',
    'mercado'   => '<path d="M3.5 20.4h17"/><rect x="5.2" y="11" width="3.6" height="7" rx="1"/><rect x="10.2" y="5.6" width="3.6" height="12.4" rx="1"/><rect x="15.2" y="14" width="3.6" height="4" rx="1"/>',
    'portafolio'=> '<rect x="3.2" y="6" width="17.6" height="12.6" rx="2.6"/><path d="M3.2 10.2h17.6"/><circle cx="16.6" cy="14.6" r="1.3"/>',
    'historial' => '<circle cx="12" cy="12" r="8.4"/><path d="M12 7.4V12l3.2 2"/>',
    'perfil'    => '<circle cx="12" cy="8.6" r="3.8"/><path d="M4.8 19.6c1.5-3.4 4.1-5.1 7.2-5.1s5.7 1.7 7.2 5.1"/>',
    'usuarios'  => '<circle cx="9.4" cy="8.4" r="3.6"/><path d="M3.4 19.4c1.3-3.1 3.6-4.7 6-4.7s4.7 1.6 6 4.7"/><path d="M16.4 5.8a3.3 3.3 0 0 1 0 6.2"/><path d="M18.6 14.8c1 .8 1.7 1.9 2 3.3"/>',
    'reportes'  => '<path d="M6.4 3.8h7l4.2 4.2v12.2H6.4z"/><path d="M13.4 3.8v4.2h4.2"/><path d="M9.2 12.8h5.6"/><path d="M9.2 16.2h5.6"/>',
    'listado'   => '<path d="M8 6h13M8 12h13M8 18h13"/><path d="M3.6 6h.01M3.6 12h.01M3.6 18h.01"/>',
    'config'    => '<circle cx="12" cy="12" r="3"/><path d="M12 3.6v2.2M12 18.2v2.2M3.6 12h2.2M18.2 12h2.2M6.1 6.1l1.6 1.6M16.3 16.3l1.6 1.6M17.9 6.1l-1.6 1.6M7.7 16.3l-1.6 1.6"/>',
    'entrar'    => '<path d="M9.4 4.4H6A1.8 1.8 0 0 0 4.2 6.2v11.6A1.8 1.8 0 0 0 6 19.6h3.4"/><path d="M14.4 8.4 18.4 12l-4 3.6"/><path d="M18.1 12H9.4"/>',
    'registrar' => '<circle cx="9.6" cy="8.6" r="3.6"/><path d="M3.4 19.4c1.3-3.2 3.6-4.8 6.2-4.8 1.1 0 2.1.3 3 .9"/><path d="M17.6 14.4v5.2M15 17h5.2"/>',
    'salir'     => '<path d="M14.6 4.4H18a1.8 1.8 0 0 1 1.8 1.8v11.6A1.8 1.8 0 0 1 18 19.6h-3.4"/><path d="M9.6 8.4 5.6 12l4 3.6"/><path d="M5.9 12h8.7"/>',
    'cerrar'    => '<path d="M14.6 6.4 9 12l5.6 5.6"/>',
];

// Marca la opcion que corresponde a la pagina donde esta el usuario.
function lateral_clase($paginaActual, $archivo)
{
    return $paginaActual === $archivo ? ' activo' : '';
}

function lateral_item($url, $icono, $texto, $activo = '', $claseExtra = '')
{
    global $icoAtributos, $ico;
    $extra = $claseExtra !== '' ? ' ' . $claseExtra : '';
    return '<a class="lateral-item' . $activo . $extra . '" href="' . e($url) . '">'
        . '<svg ' . $icoAtributos . ' aria-hidden="true">' . $ico[$icono] . '</svg>'
        . '<span>' . e($texto) . '</span></a>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo) ?> - CriptoSim</title>
    <link rel="stylesheet" href="<?= e(url('css/estilos.css')) ?>">
    <script src="<?= e(url('js/lateral.js')) ?>" defer></script>
</head>
<body class="<?= $ocultarCabecera ? 'sin-cabecera' : '' ?>">
    <?php if (!$ocultarCabecera): ?>
    <aside class="lateral" id="lateral">
        <div class="lateral-tope">
            <a href="<?= e(url('index.php')) ?>" class="lateral-marca">
                <span class="lateral-marca-texto">
                    <span class="logo-texto">CriptoSim</span>
                    <span class="logo-sub">Simulador educativo</span>
                </span>
            </a>
            <button type="button" class="lateral-toggle" id="lateralToggle"
                    aria-controls="lateral" aria-expanded="true"
                    title="Minimizar barra">
                <svg <?= $icoAtributos ?> aria-hidden="true"><?= $ico['cerrar'] ?></svg>
            </button>
        </div>

        <nav class="lateral-menu" aria-label="Menu principal">
            <?php if (usuario_logueado()): ?>
                <?php if (es_admin()): ?>
                    <?= lateral_item(url('admin/dashboard.php'), 'panel', 'Panel admin', lateral_clase($paginaActual, 'dashboard.php')) ?>
                    <?= lateral_item(url('admin/usuarios.php'), 'usuarios', 'Usuarios', lateral_clase($paginaActual, 'usuarios.php')) ?>
                    <?= lateral_item(url('admin/reportes.php'), 'reportes', 'Reportes', lateral_clase($paginaActual, 'reportes.php')) ?>
                    <?= lateral_item(url('admin/transacciones.php'), 'listado', 'Transacciones', lateral_clase($paginaActual, 'transacciones.php')) ?>
                    <?= lateral_item(url('admin/config.php'), 'config', 'Configuracion', lateral_clase($paginaActual, 'config.php')) ?>
                <?php else: ?>
                    <?= lateral_item(url('usuario/dashboard.php'), 'panel', 'Dashboard', lateral_clase($paginaActual, 'dashboard.php')) ?>
                    <?= lateral_item(url('usuario/mercado.php'), 'mercado', 'Mercado', lateral_clase($paginaActual, 'mercado.php')) ?>
                    <?= lateral_item(url('usuario/portafolio.php'), 'portafolio', 'Portafolio', lateral_clase($paginaActual, 'portafolio.php')) ?>
                    <?= lateral_item(url('usuario/historial.php'), 'historial', 'Historial', lateral_clase($paginaActual, 'historial.php')) ?>
                <?php endif; ?>
                <?= lateral_item(url('usuario/perfil.php'), 'perfil', 'Perfil', lateral_clase($paginaActual, 'perfil.php')) ?>
            <?php else: ?>
                <?= lateral_item(url('index.php'), 'inicio', 'Inicio', lateral_clase($paginaActual, 'index.php')) ?>
                <?= lateral_item(url('auth/login.php'), 'entrar', 'Iniciar sesion', lateral_clase($paginaActual, 'login.php')) ?>
                <?= lateral_item(url('auth/registro.php'), 'registrar', 'Crear cuenta', lateral_clase($paginaActual, 'registro.php'), 'lateral-item-destacado') ?>
            <?php endif; ?>
        </nav>

        <div class="lateral-pie">
            <?php if (usuario_logueado()): ?>
                <div class="lateral-usuario">
                    <img class="lateral-avatar" src="<?= e(url('img/simi/simi-avatar.jpg')) ?>" alt="">
                    <span class="lateral-nombre"><?= e($_SESSION['nombre']) ?></span>
                </div>
                <?= lateral_item(url('auth/logout.php'), 'salir', 'Salir') ?>
            <?php else: ?>
                <p class="lateral-nota">Saldo virtual. No maneja dinero real.</p>
            <?php endif; ?>
        </div>
    </aside>
    <?php endif; ?>

    <main class="contenedor">
        <?php if ($flash): ?>
            <div class="alerta alerta-<?= e($flash['tipo']) ?>">
                <?= e($flash['texto']) ?>
            </div>
        <?php endif; ?>
