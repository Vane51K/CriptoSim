<?php
// CriptoSim - Control de acceso y sesion

require_once __DIR__ . '/funciones.php';

iniciar_sesion();

/**
 * Indica si hay un usuario con sesion iniciada.
 */
function usuario_logueado()
{
    return !empty($_SESSION['id_usuario']);
}

/**
 * Indica si el usuario actual tiene rol de administrador.
 */
function es_admin()
{
    return usuario_logueado() && ($_SESSION['rol'] ?? '') === 'admin';
}

/**
 * Devuelve los datos del usuario en sesion, o null si no hay sesion.
 */
function usuario_actual()
{
    if (!usuario_logueado()) {
        return null;
    }

    return [
        'id_usuario' => $_SESSION['id_usuario'],
        'nombre'     => $_SESSION['nombre'],
        'correo'     => $_SESSION['correo'],
        'rol'        => $_SESSION['rol'],
        'saldo'      => $_SESSION['saldo'],
    ];
}

/**
 * Impide el acceso a quien no tenga sesion iniciada. Guarda el
 * retorno para poder volver a la pagina de origen tras iniciar sesion.
 */
function requiere_logueo($retorno = null)
{
    if (!usuario_logueado()) {
        if ($retorno === null) {
            $retorno = $_SERVER['REQUEST_URI'] ?? 'index.php';
        }

        mensaje_flash('error', 'Debe iniciar sesion para acceder a esa seccion.');
        $_SESSION['retorno'] = $retorno;

        redirigir('auth/login.php');
    }
}

/**
 * Impide el acceso a quien no sea administrador.
 */
function requiere_admin()
{
    requiere_logueo();

    if (!es_admin()) {
        mensaje_flash('error', 'No tiene permisos para acceder al area administrativa.');
        redirigir('usuario/dashboard.php');
    }
}

/**
 * Cierra la sesion si la cuenta fue suspendida o dada de baja
 * mientras el usuario tenia la pagina abierta. El administrador
 * puede cambiar el estado en cualquier momento, asi que se verifica
 * en cada pagina protegida.
 */
function verificar_estado_cuenta()
{
    global $conexion;

    if (!usuario_logueado()) {
        return;
    }

    $stmt = $conexion->prepare("SELECT estado FROM usuarios WHERE id_usuario = ?");
    $stmt->bind_param('i', $_SESSION['id_usuario']);
    $stmt->execute();

    $resultado = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$resultado) {
        cerrar_sesion_con_mensaje('error', 'La cuenta ya no existe.');
        redirigir('auth/login.php');
    }

    if ($resultado['estado'] === 'suspendido') {
        cerrar_sesion_con_mensaje('error', 'Su cuenta se encuentra suspendida.');
        redirigir('auth/login.php');
    }

    if ($resultado['estado'] === 'baja') {
        cerrar_sesion_con_mensaje('error', 'Su cuenta se encuentra dada de baja.');
        redirigir('auth/login.php');
    }
}