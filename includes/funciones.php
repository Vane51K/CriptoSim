<?php
// CriptoSim - Funciones de apoyo

if (defined('CRIPTOSIM_INICIADO')) {
    exit;
}
define('CRIPTOSIM_INICIADO', true);

date_default_timezone_set('America/Guatemala');

if (!defined('BASE_URL')) {
    $raizProyecto = str_replace('\\', '/', dirname(__DIR__));
    $docroot = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/'));

    if ($docroot !== '' && strpos($raizProyecto, $docroot) === 0) {
        define('BASE_URL', rtrim(substr($raizProyecto, strlen($docroot)), '/'));
    } else {
        define('BASE_URL', '');
    }
}

/**
 * URL completa a partir de una ruta interna, con la base del proyecto.
 */
function url($ruta = '')
{
    return BASE_URL . '/' . ltrim($ruta, '/');
}

/**
 * Inicia la sesion. httponly evita que JavaScript lea la cookie.
 */
function iniciar_sesion()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

/**
 * Cierra la sesion y elimina el archivo de sesion del servidor.
 */
function cerrar_sesion()
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }

    session_destroy();
}

/**
 * Escapa texto para mostrarlo en HTML.
 */
function e($texto)
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

/**
 * Token unico por sesion, para proteger los formularios contra CSRF.
 */
function token_csrf()
{
    iniciar_sesion();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Verifica que el token recibido sea el de la sesion.
 */
function verificar_csrf($token)
{
    iniciar_sesion();

    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string) $token)) {
        return false;
    }

    return true;
}

/**
 * Guarda un mensaje para mostrarlo en la pagina siguiente.
 */
function mensaje_flash($tipo, $texto)
{
    iniciar_sesion();
    $_SESSION['flash'] = ['tipo' => $tipo, 'texto' => $texto];
}

/**
 * Recupera y borra el mensaje guardado.
 */
function obtener_flash()
{
    iniciar_sesion();

    if (empty($_SESSION['flash'])) {
        return null;
    }

    $mensaje = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $mensaje;
}

/**
 * Cierra la sesion del usuario pero conserva un mensaje para mostrar.
 * No se usa session_destroy() porque borraria el mensaje: se vacia el
 * arreglo, se regenera el id (evita fixation) y se guarda el mensaje.
 */
function cerrar_sesion_con_mensaje($tipo, $texto)
{
    iniciar_sesion();

    $_SESSION = [];
    session_regenerate_id(true);
    $_SESSION['flash'] = ['tipo' => $tipo, 'texto' => $texto];
}

/**
 * Redirige a una ruta del proyecto y detiene la ejecucion.
 */
function redirigir($ruta)
{
    header('Location: ' . url($ruta));
    exit;
}

/**
 * Formato para montos: 1234.5 -> Q1,234.50
 */
function formato_q($monto)
{
    return 'Q' . number_format((float) $monto, 2, '.', ',');
}

/**
 * Formatea una cantidad de criptomoneda con hasta 8 decimales,
 * quitando los ceros sobrantes.
 */
function formato_cantidad($cantidad)
{
    $formateado = rtrim(rtrim(number_format((float) $cantidad, 8, '.', ''), '0'), '.');

    return $formateado === '' ? '0' : $formateado;
}

/**
 * Lee una clave de config_sistema (ej: saldo_inicial, moneda).
 */
function obtener_config($clave)
{
    global $conexion;

    $stmt = $conexion->prepare("SELECT valor FROM config_sistema WHERE clave = ?");
    $stmt->bind_param('s', $clave);
    $stmt->execute();

    $resultado = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $resultado ? $resultado['valor'] : null;
}

/**
 * Toda la tabla config_sistema como ['clave' => 'valor'].
 * admin/config.php la usa para pintar lo que YA esta guardado,
 * no lo que dice database.sql (pueden haber quedado distintos).
 */
function obtener_config_completa()
{
    global $conexion;

    $filas = $conexion->query("SELECT clave, valor FROM config_sistema")
        ->fetch_all(MYSQLI_ASSOC);

    $config = [];

    foreach ($filas as $fila) {
        $config[$fila['clave']] = $fila['valor'];
    }

    return $config;
}

/**
 * Guarda un valor en config_sistema con sentencia preparada. La
 * lista blanca de claves editables la define la pagina que llama.
 */
function guardar_config($clave, $valor)
{
    global $conexion;

    $stmt = $conexion->prepare(
        "UPDATE config_sistema SET valor = ? WHERE clave = ?"
    );
    $stmt->bind_param('ss', $valor, $clave);

    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

/**
 * Saldo inicial que recibe cada cuenta nueva. Vive en config_sistema
 * ('saldo_inicial'); el 10000.00 es solo el respaldo por si la fila
 * no existe, para que el registro nunca se caiga.
 */
function saldo_inicial()
{
    $valor = obtener_config('saldo_inicial');

    return $valor !== null ? (float) $valor : 10000.00;
}

/**
 * Fecha en formato legible: 2026-10-07 13:45 -> 07/10/2026 13:45
 */
function fecha_legible($fecha)
{
    $timestamp = strtotime($fecha);

    if ($timestamp === false) {
        return $fecha;
    }

    return date('d/m/Y H:i', $timestamp);
}