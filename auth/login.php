<?php
// CriptoSim - Inicio de sesion

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/auth.php';

if (usuario_logueado()) {
    redirigir('index.php');
}

$error = '';
$correo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verificar_csrf($_POST['csrf'] ?? '')) {
        $error = 'La sesion del formulario expiro. Intente de nuevo.';
    } else {
        $correo = trim($_POST['correo'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($correo === '' || $password === '') {
            $error = 'Debe completar el correo y la contrasena.';
        } else {
            $stmt = $conexion->prepare(
                "SELECT id_usuario, nombre, password, saldo, rol, estado
                 FROM usuarios WHERE correo = ?"
            );
            $stmt->bind_param('s', $correo);
            $stmt->execute();

            $usuario = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            // El mismo mensaje para correo inexistente y contrasena
            // incorrecta, asi no se revela que correos estan registrados.
            // Mensaje exigido por la consigna (seccion 26).
            if (!$usuario || !password_verify($password, $usuario['password'])) {
                $error = 'Usuario o contraseña incorrectos.';
            } elseif ($usuario['estado'] === 'suspendido') {
                $error = 'Su cuenta se encuentra suspendida.';
            } elseif ($usuario['estado'] === 'baja') {
                $error = 'Su cuenta se encuentra dada de baja.';
            } else {
                // Identificador nuevo para evitar fijacion de sesion
                session_regenerate_id(true);

                $_SESSION['id_usuario'] = (int) $usuario['id_usuario'];
                $_SESSION['nombre'] = $usuario['nombre'];
                $_SESSION['correo'] = $usuario['correo'];
                $_SESSION['rol'] = $usuario['rol'];
                $_SESSION['saldo'] = $usuario['saldo'];

                // Si venia de una pagina privada, se le devuelve ahi
                $retorno = $_SESSION['retorno'] ?? null;
                unset($_SESSION['retorno']);

                if ($usuario['rol'] === 'admin') {
                    mensaje_flash('exito', 'Bienvenido, ' . $usuario['nombre'] . '.');
                    redirigir($retorno ?? 'admin/dashboard.php');
                }

                mensaje_flash('exito', 'Bienvenido nuevamente, ' . $usuario['nombre'] . '.');
                redirigir($retorno ?? 'usuario/dashboard.php');
            }
        }
    }
}

$titulo = 'Iniciar sesion';
$ocultarCabecera = true;
require_once __DIR__ . '/../includes/header.php';
?>

<section class="auth-contenedor">
    <div class="auth-tarjeta">
        <img src="<?= e(url('img/simi/Logotipo_superior_web.png')) ?>"
             alt="CriptoSim"
             class="auth-logo">

        <h1>Iniciar sesion</h1>
        <p class="auth-subtitulo">Accede a tu simulador para continuar.</p>

        <?php if ($error !== ''): ?>
            <div class="alerta alerta-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="" novalidate>
            <input type="hidden" name="csrf" value="<?= e(token_csrf()) ?>">

            <label for="correo">Correo electronico</label>
            <input type="email" id="correo" name="correo" value="<?= e($correo) ?>" required>

            <label for="password">Contrasena</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" class="boton boton-primario boton-bloque">Entrar</button>
        </form>

        <p class="auth-pie">No tenes cuenta? <a href="<?= e(url('auth/registro.php')) ?>">Registrate</a></p>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>