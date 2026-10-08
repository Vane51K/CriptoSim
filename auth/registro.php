<?php
// CriptoSim - Registro de usuarios

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/auth.php';

if (usuario_logueado()) {
    redirigir('index.php');
}

$errores = [];
$nombre = '';
$correo = '';

// Saldo inicial tomado de la configuracion del sistema
$saldoInicial = saldo_inicial();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verificar_csrf($_POST['csrf'] ?? '')) {
        $errores[] = 'La sesion del formulario expiro. Intente de nuevo.';
    }

    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    if ($nombre === '') {
        $errores[] = 'El nombre completo es obligatorio.';
    } elseif (mb_strlen($nombre) < 3) {
        $errores[] = 'El nombre debe tener al menos 3 caracteres.';
    } elseif (mb_strlen($nombre) > 100) {
        $errores[] = 'El nombre no puede superar los 100 caracteres.';
    }

    if ($correo === '') {
        $errores[] = 'El correo electronico es obligatorio.';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo electronico no tiene un formato valido.';
    } elseif (mb_strlen($correo) > 120) {
        $errores[] = 'El correo no puede superar los 120 caracteres.';
    } else {
        $stmt = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE correo = ?");
        $stmt->bind_param('s', $correo);
        $stmt->execute();
        $existe = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($existe) {
            // Mensaje exigido por la consigna (seccion 26).
            $errores[] = 'Correo electrónico ya registrado.';
        }
    }

    if ($password === '') {
        $errores[] = 'La contrasena es obligatoria.';
    } elseif (mb_strlen($password) < 8) {
        $errores[] = 'La contrasena debe tener al menos 8 caracteres.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errores[] = 'La contrasena debe incluir al menos una letra mayuscula.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errores[] = 'La contrasena debe incluir al menos un numero.';
    }

    if ($password !== $confirmar) {
        $errores[] = 'Las contrasenas no coinciden.';
    }

    if (empty($errores)) {
        // El hash se genera en PHP, nunca se guarda la contrasena en texto plano
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conexion->prepare(
            "INSERT INTO usuarios (nombre, correo, password, saldo, rol, estado)
             VALUES (?, ?, ?, ?, 'usuario', 'activo')"
        );
        $stmt->bind_param('sssd', $nombre, $correo, $hash, $saldoInicial);

        if ($stmt->execute()) {
            mensaje_flash('exito', 'Cuenta creada correctamente. Ya podes iniciar sesion.');
            redirigir('auth/login.php');
        } else {
            $errores[] = 'No se pudo crear la cuenta. Intente nuevamente.';
        }

        $stmt->close();
    }
}

$titulo = 'Crear cuenta';
$ocultarCabecera = true;
require_once __DIR__ . '/../includes/header.php';
?>

<section class="auth-contenedor">
    <div class="auth-tarjeta">
        <h1>Crear cuenta</h1>
        <p class="auth-subtitulo">Recibiras <?= e(formato_q($saldoInicial)) ?> de saldo virtual para simular.</p>

        <?php if (!empty($errores)): ?>
            <div class="alerta alerta-error">
                <ul>
                    <?php foreach ($errores as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="" novalidate>
            <input type="hidden" name="csrf" value="<?= e(token_csrf()) ?>">

            <label for="nombre">Nombre completo</label>
            <input type="text" id="nombre" name="nombre" value="<?= e($nombre) ?>"
                   maxlength="100" required>

            <label for="correo">Correo electronico</label>
            <input type="email" id="correo" name="correo" value="<?= e($correo) ?>"
                   maxlength="120" required>

            <label for="password">Contrasena</label>
            <input type="password" id="password" name="password" required>
            <p class="ayuda">Minimo 8 caracteres, una mayuscula y un numero.</p>

            <label for="confirmar">Confirmar contrasena</label>
            <input type="password" id="confirmar" name="confirmar" required>

            <button type="submit" class="boton boton-primario boton-bloque">Crear cuenta</button>
        </form>

        <p class="auth-pie">Ya tenes cuenta? <a href="<?= e(url('auth/login.php')) ?>">Inicia sesion</a></p>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
