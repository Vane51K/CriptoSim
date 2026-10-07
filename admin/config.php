<?php
// CriptoSim - Panel de configuracion del sistema
// La consigna (seccion 9) promete poder cambiar el saldo inicial
// desde el panel; esta pantalla cumple esa promesa.
// Cambiar saldo_inicial NO toca el saldo de cuentas existentes: se
// lee una sola vez al crear la cuenta, en auth/registro.php.
// El origen de los precios no es configurable: son simulados de la BD.

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/auth.php';

requiere_admin();
verificar_estado_cuenta();

$errores = [];

// Se relee de la base en cada peticion para mostrar lo guardado,
// no lo que el visitante escribio la vez anterior.
$config = obtener_config_completa();

// ------------------------------------------------------------
// Procesar el formulario
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verificar_csrf($_POST['csrf'] ?? '')) {
        mensaje_flash('error', 'La sesion del formulario expiro. Intente de nuevo.');
        redirigir('admin/config.php');
    }

    $nombre = trim((string) ($_POST['nombre_sistema'] ?? ''));
    $saldo = trim((string) ($_POST['saldo_inicial'] ?? ''));
    $moneda = trim((string) ($_POST['moneda'] ?? ''));

    // ---- Validacion en el servidor ----
    // El navegador valida con required y type, pero estas reglas valen.

    if ($nombre === '') {
        $errores[] = 'El nombre del sistema no puede quedar vacio.';
    } elseif (mb_strlen($nombre) < 3) {
        $errores[] = 'El nombre del sistema debe tener al menos 3 caracteres.';
    } elseif (mb_strlen($nombre) > 60) {
        $errores[] = 'El nombre del sistema no puede superar los 60 caracteres.';
    }

    // is_numeric acepta notacion cientifica (1e3); se descarta a mano.
    if ($saldo === '' || !is_numeric($saldo) || stripos($saldo, 'e') !== false) {
        $errores[] = 'El saldo inicial debe ser un numero, por ejemplo 10000.00.';
    } else {
        $saldoNumero = (float) $saldo;

        if ($saldoNumero <= 0) {
            $errores[] = 'El saldo inicial debe ser mayor que cero.';
        } elseif ($saldoNumero > 999999999.00) {
            $errores[] = 'El saldo inicial no puede superar Q999,999,999.00.';
        } else {
            // La columna usuarios.saldo es DECIMAL(15,2): guardar mas
            // decimales haria que MySQL redondeara en silencio.
            $decimales = strpos($saldo, '.') === false
                ? 0
                : strlen(substr(strrchr($saldo, '.'), 1));

            if ($decimales > 2) {
                $errores[] = 'El saldo inicial admite como maximo dos decimales.';
            }
        }
    }

    if ($moneda === '') {
        $errores[] = 'Debe indicar el simbolo de la moneda.';
    } elseif (mb_strlen($moneda) > 5) {
        $errores[] = 'El simbolo de la moneda no puede superar los 5 caracteres.';
    }

    // ---- Guardado ----
    if (empty($errores)) {
        $conexion->begin_transaction();

        try {
            $guardado = guardar_config('nombre_sistema', $nombre)
                     && guardar_config('saldo_inicial', number_format((float) $saldo, 2, '.', ''))
                     && guardar_config('moneda', $moneda);

            if ($guardado) {
                $conexion->commit();
                mensaje_flash(
                    'exito',
                    'Configuracion guardada. El nuevo saldo inicial se aplica a las cuentas que se creen desde ahora en adelante; las cuentas existentes conservan su saldo.'
                );
            } else {
                $conexion->rollback();
                $errores[] = 'No se pudo guardar la configuracion. Intente de nuevo.';
            }
        } catch (mysqli_sql_exception $excepcion) {
            $conexion->rollback();
            $errores[] = 'Ocurrio un error al guardar. Intente de nuevo.';
        }
    }
}

$titulo = 'Configuracion';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="saludo">
    <h1>Configuracion del sistema</h1>
    <p>Valores que el simulador usa para arrancar. Se guardan en la tabla config_sistema.</p>
</section>

<section class="seccion panel">
    <h2 class="seccion-titulo">Datos editables</h2>

    <?php if (!empty($errores)): ?>
        <div class="alerta alerta-error">
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= e(url('admin/config.php')) ?>" novalidate>
        <input type="hidden" name="csrf" value="<?= e(token_csrf()) ?>">

        <label for="nombre_sistema">Nombre del sistema</label>
        <input type="text" id="nombre_sistema" name="nombre_sistema"
               value="<?= e($config['nombre_sistema'] ?? '') ?>" maxlength="60" required>

        <label for="saldo_inicial">Saldo inicial para cuentas nuevas (<?= e($config['moneda'] ?? 'Q') ?>)</label>
        <input type="text" id="saldo_inicial" name="saldo_inicial"
               value="<?= e($config['saldo_inicial'] ?? '') ?>" maxlength="20" required>
        <p class="ayuda">
            Se aplica solo a las cuentas creadas DESPUES de guardar este valor.
            No cambia el saldo de las cuentas que ya existen.
        </p>

        <label for="moneda">Simbolo de la moneda</label>
        <input type="text" id="moneda" name="moneda"
               value="<?= e($config['moneda'] ?? '') ?>" maxlength="5" required>
        <p class="ayuda">Por ejemplo Q, $ o EUR.</p>

        <button type="submit" class="boton boton-primario">Guardar cambios</button>
    </form>
</section>

<section class="seccion panel">
    <h2 class="seccion-titulo">Valores de solo lectura</h2>
    <p class="ayuda">
        Se muestran para que se vean, pero no se editan desde aqui.
        El interruptor de precios en vivo se decide aparte.
    </p>

    <div class="tabla-envoltura">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Clave</th>
                    <th>Valor actual</th>
                    <th>Para que sirve</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="tabla-cripto">origen de los precios</span></td>
                    <td>
                        <span class="badge badge-activo">Simulados</span>
                    </td>
                    <td class="tabla-fecha">
                        Los precios no vienen de internet. Salen de la tabla
                        <code>criptomonedas</code> de la base de datos y son
                        ficticios, tal como pide la documentacion del proyecto.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>