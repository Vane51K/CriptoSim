<?php
// CriptoSim - Administracion de usuarios

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/auth.php';

requiere_admin();
verificar_estado_cuenta();

$admin = usuario_actual();
$idAdmin = (int) $admin['id_usuario'];

// Tope por movimiento de saldo. Misma regla que la recarga del perfil:
// la consigna no lo define y se pone un valor alto solo para que no se
// pueda cargar un monto absurdo.
const RECARGA_MAXIMA = 1000000;

// Los tres estados validos del ENUM mas "todas". Cualquier otra cosa
// que venga por GET se descarta antes de tocar el SQL.
$opcionesEstado = ['todas', 'activo', 'suspendido', 'baja'];

$etiquetasEstado = [
    'todas'      => 'Todas',
    'activo'     => 'Activos',
    'suspendido' => 'Suspendidos',
    'baja'       => 'Dadas de baja',
];

// Singular para la etiqueta de cada fila de la tabla.
$etiquetasFila = [
    'activo'     => 'Activo',
    'suspendido' => 'Suspendido',
    'baja'       => 'Dada de baja',
];

/**
 * Devuelve el estado pedido y solo si esta en la lista blanca.
 */
function estado_permitido($valor, array $opciones)
{
    $valor = (string) $valor;

    return in_array($valor, $opciones, true) ? $valor : $opciones[0];
}

/**
 * Arma la ruta de la lista conservando los filtros activos.
 * Devuelve una ruta interna, no una URL completa: redirigir() y url()
 * se encargan de anteponer la base del proyecto.
 */
function enlace_usuarios($estado, $buscar)
{
    $params = ['estado' => $estado];

    if ($buscar !== '') {
        $params['q'] = $buscar;
    }

    return 'admin/usuarios.php?' . http_build_query($params);
}

/**
 * Convierte un texto en un literal de JavaScript de comilla simple.
 * Escapa tambien la comilla doble: el resultado se mete dentro de un
 * atributo HTML con comillas dobles, asi que sin esto un nombre con
 * comilla doble romperia el atributo y el confirm().
 */
function literal_js($texto)
{
    return "'" . str_replace(
        ['\\', "'", '"', "\r", "\n"],
        ['\\\\', "\\'", '\\"', '', ' '],
        $texto
    ) . "'";
}

// ------------------------------------------------------------
// Acciones sobre una cuenta
//
// Cada accion declara a que estado lleva la cuenta y desde cuales
// estados se permite. "Dar de baja" es un cambio de estado y nunca
// un DELETE: las claves foraneas son ON DELETE CASCADE y borrar la
// fila se llevaria por delante transacciones y portafolio, rompiendo
// todos los reportes.
// ------------------------------------------------------------
$acciones = [
    'suspender' => [
        'nuevo'     => 'suspendido',
        'permitidos' => ['activo'],
        'verbo'     => 'suspender',
        'mensaje'   => 'La cuenta fue suspendida.',
    ],
    'reactivar' => [
        'nuevo'     => 'activo',
        'permitidos' => ['suspendido'],
        'verbo'     => 'reactivar',
        'mensaje'   => 'La cuenta fue reactivada.',
    ],
    'baja' => [
        'nuevo'     => 'baja',
        'permitidos' => ['activo', 'suspendido'],
        'verbo'     => 'dar de baja',
        'mensaje'   => 'La cuenta fue dada de baja.',
    ],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $estadoFiltro = estado_permitido($_POST['filtro_estado'] ?? '', $opcionesEstado);
    $buscarFiltro = trim((string) ($_POST['filtro_q'] ?? ''));
    $destino = enlace_usuarios($estadoFiltro, $buscarFiltro);

    if (!verificar_csrf($_POST['csrf'] ?? '')) {
        mensaje_flash('error', 'La sesion del formulario expiro. Intente de nuevo.');
        redirigir('admin/usuarios.php');
    }

    $accion = (string) ($_POST['accion'] ?? '');

    // ----------------------------------------------------------
    // Agregar saldo: no cambia el estado de la cuenta, solo le suma
    // saldo virtual. Se atiende antes de la logica de suspender /
    // reactivar / dar de baja, que usa el mapa $acciones.
    // ----------------------------------------------------------
    if ($accion === 'agregar_saldo') {
        $idObjetivo = (int) ($_POST['id_usuario'] ?? 0);
        $monto = round((float) ($_POST['monto'] ?? 0), 2);

        // El administrador no se agrega saldo a si mismo: para eso
        // esta la recarga de su perfil.
        if ($idObjetivo === $idAdmin) {
            mensaje_flash('error', 'Para sumar saldo a su propia cuenta use la recarga del perfil.');
            redirigir($destino);
        }

        if (!is_finite($monto) || $monto <= 0) {
            mensaje_flash('error', 'Ingresa un monto mayor a cero.');
            redirigir($destino);
        }

        if ($monto > RECARGA_MAXIMA) {
            mensaje_flash('error', 'El monto maximo por operacion es ' . formato_q(RECARGA_MAXIMA) . '.');
            redirigir($destino);
        }

        $stmt = $conexion->prepare("SELECT nombre, saldo FROM usuarios WHERE id_usuario = ?");
        $stmt->bind_param('i', $idObjetivo);
        $stmt->execute();
        $objetivo = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$objetivo) {
            mensaje_flash('error', 'El usuario ya no existe.');
            redirigir($destino);
        }

        $stmt = $conexion->prepare("UPDATE usuarios SET saldo = saldo + ? WHERE id_usuario = ?");
        $stmt->bind_param('di', $monto, $idObjetivo);
        $stmt->execute();
        $filasAfectadas = $stmt->affected_rows;
        $stmt->close();

        if ($filasAfectadas === 1) {
            mensaje_flash('exito', 'Se agregaron ' . formato_q($monto) . ' al saldo de '
                . $objetivo['nombre'] . '.');
        } else {
            mensaje_flash('error', 'No se pudo agregar el saldo. Intente de nuevo.');
        }

        redirigir($destino);
    }

    if (!isset($acciones[$accion])) {
        mensaje_flash('error', 'Operacion no valida.');
        redirigir($destino);
    }

    $regla = $acciones[$accion];
    $idObjetivo = (int) ($_POST['id_usuario'] ?? 0);

    // El administrador no puede cambiar el estado de su propia cuenta.
    // Si lo pudiera, se quedaria fuera del panel a mitad de una sesion
    // de trabajo sin poder volver.
    if ($idObjetivo === $idAdmin) {
        mensaje_flash('error', 'No puede cambiar el estado de su propia cuenta.');
        redirigir($destino);
    }

    $stmt = $conexion->prepare("SELECT nombre, estado FROM usuarios WHERE id_usuario = ?");
    $stmt->bind_param('i', $idObjetivo);
    $stmt->execute();
    $objetivo = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$objetivo) {
        mensaje_flash('error', 'El usuario ya no existe.');
        redirigir($destino);
    }

    // El estado se relee de la base y se valida contra la lista blanca
    // de la accion. Asi no depende de lo que mando el formulario.
    if (!in_array($objetivo['estado'], $regla['permitidos'], true)) {
        mensaje_flash('error', 'Esa cuenta no se puede ' . $regla['verbo'] . ' en su estado actual.');
        redirigir($destino);
    }

    // La condicion del WHERE repite el estado leido: si otra peticion
    // lo cambio entre medio, el UPDATE no toca filas y no se pisa nadie.
    $stmt = $conexion->prepare("UPDATE usuarios SET estado = ? WHERE id_usuario = ? AND estado = ?");
    $stmt->bind_param('sis', $regla['nuevo'], $idObjetivo, $objetivo['estado']);
    $stmt->execute();
    $filasAfectadas = $stmt->affected_rows;
    $stmt->close();

    if ($filasAfectadas === 1) {
        mensaje_flash('exito', $regla['mensaje'] . ' Cuenta: ' . $objetivo['nombre'] . '.');
    } else {
        mensaje_flash('error', 'No se pudo aplicar el cambio. Intente de nuevo.');
    }

    redirigir($destino);
}

// ------------------------------------------------------------
// Filtros de la lista (GET)
// ------------------------------------------------------------
$estado = estado_permitido($_GET['estado'] ?? '', $opcionesEstado);
$buscar = trim((string) ($_GET['q'] ?? ''));

// Una sola consulta con LEFT JOIN: el conteo de transacciones de cada
// usuario sale del mismo recorrido y no de una consulta por fila.
$sql = "SELECT u.id_usuario, u.nombre, u.correo, u.rol, u.estado,
               u.saldo, u.fecha_registro,
               COUNT(t.id_transaccion) AS transacciones
        FROM usuarios u
        LEFT JOIN transacciones t ON t.id_usuario = u.id_usuario";

$tipos = '';
$params = [];
$condiciones = [];

if ($estado !== 'todas') {
    $condiciones[] = 'u.estado = ?';
    $params[] = $estado;
    $tipos .= 's';
}

if ($buscar !== '') {
    $condiciones[] = '(u.nombre LIKE ? OR u.correo LIKE ?)';
    $like = '%' . $buscar . '%';
    $params[] = $like;
    $params[] = $like;
    $tipos .= 'ss';
}

if ($condiciones !== []) {
    $sql .= ' WHERE ' . implode(' AND ', $condiciones);
}

// El segundo criterio del ORDER BY evita que dos cuentas con la misma
// fecha de registro se intercambien de lugar entre una peticion y otra.
$sql .= ' GROUP BY u.id_usuario, u.nombre, u.correo, u.rol, u.estado, u.saldo, u.fecha_registro
          ORDER BY u.fecha_registro DESC, u.id_usuario DESC';

$stmt = $conexion->prepare($sql);

if ($tipos !== '') {
    $stmt->bind_param($tipos, ...$params);
}

$stmt->execute();
$usuarios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$totalUsuarios = count($usuarios);

// Lista para el selector de "Agregar saldo": cuentas activas de
// terceros, ordenadas por nombre. El administrador queda afuera.
$stmt = $conexion->prepare(
    "SELECT id_usuario, nombre, correo, saldo
     FROM usuarios
     WHERE estado = 'activo' AND id_usuario <> ?
     ORDER BY nombre, id_usuario"
);
$stmt->bind_param('i', $idAdmin);
$stmt->execute();
$paraSaldo = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$titulo = 'Usuarios';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="saludo">
    <h1>Usuarios</h1>
    <p>Consulta las cuentas del simulador y administra su estado</p>
</section>

<section class="seccion panel">
    <h2 class="seccion-titulo">Agregar saldo virtual</h2>

    <p class="ayuda">
        Suma saldo ficticio a la cuenta de un usuario activo. La operacion
        no se puede deshacer.
    </p>

    <?php if (empty($paraSaldo)): ?>
        <p class="sin-datos">No hay cuentas activas de otros usuarios.</p>
    <?php else: ?>
        <form method="POST" action="<?= e(url('admin/usuarios.php')) ?>"
              class="admin-filtros"
              onsubmit="return confirm('Esta seguro de que desea agregar Q' + this.elements['monto'].value + ' al saldo del usuario seleccionado?');">
            <input type="hidden" name="csrf" value="<?= e(token_csrf()) ?>">
            <input type="hidden" name="accion" value="agregar_saldo">
            <input type="hidden" name="filtro_estado" value="<?= e($estado) ?>">
            <input type="hidden" name="filtro_q" value="<?= e($buscar) ?>">

            <div>
                <label for="saldo_usuario">Usuario</label>
                <select id="saldo_usuario" name="id_usuario" required>
                    <?php foreach ($paraSaldo as $c):
                        $etiqueta = $c['nombre'] . ' (' . $c['correo'] . ') - saldo '
                            . formato_q($c['saldo']); ?>
                        <option value="<?= (int) $c['id_usuario'] ?>">
                            <?= e($etiqueta) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="saldo_monto">Monto (Q)</label>
                <input type="number" id="saldo_monto" name="monto"
                       min="0.01" max="1000000" step="0.01" required
                       placeholder="0.00">
            </div>

            <div>
                <button type="submit" class="boton boton-primario">Agregar saldo</button>
            </div>
        </form>
        <p class="ayuda">
            Entre Q0.01 y <?= e(formato_q(RECARGA_MAXIMA)) ?> por operacion.
        </p>
    <?php endif; ?>
</section>

<section class="seccion panel">
    <div class="filtros">
        <h2 class="seccion-titulo">Cuentas registradas</h2>
        <span class="paginacion-info"><?= $totalUsuarios ?> resultado(s)</span>
    </div>

    <form method="GET" action="<?= e(url('admin/usuarios.php')) ?>" class="admin-filtros">
        <div>
            <label for="q">Buscar</label>
            <input type="text" id="q" name="q" value="<?= e($buscar) ?>"
                   placeholder="Nombre o correo" maxlength="120">
        </div>

        <div>
            <label for="estado">Estado</label>
            <select id="estado" name="estado">
                <?php foreach ($opcionesEstado as $opcion): ?>
                    <option value="<?= e($opcion) ?>" <?= $estado === $opcion ? 'selected' : '' ?>>
                        <?= e($etiquetasEstado[$opcion]) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <button type="submit" class="boton boton-primario">Filtrar</button>
        </div>
    </form>

    <?php if (empty($usuarios)): ?>
        <div class="vacio">
            <p>No hay cuentas que coincidan con el filtro.</p>
            <a href="<?= e(url('admin/usuarios.php')) ?>" class="boton boton-secundario">
                Quitar filtros
            </a>
        </div>
    <?php else: ?>
        <div class="tabla-envoltura">
            <table class="tabla tabla-ancha">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th class="tabla-num">Saldo</th>
                        <th class="tabla-num">Operaciones</th>
                        <th>Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u):
                        $idUsuario = (int) $u['id_usuario'];
                        $esPropia = $idUsuario === $idAdmin;
                        ?>
                        <tr>
                            <td>
                                <a href="<?= e(url('admin/usuario_detalle.php?id=' . $idUsuario)) ?>">
                                    <strong><?= e($u['nombre']) ?></strong>
                                </a>
                                <span class="tabla-correo"><?= e($u['correo']) ?></span>
                            </td>
                            <td>
                                <?php if ($u['rol'] === 'admin'): ?>
                                    <strong>Administrador</strong>
                                <?php else: ?>
                                    Usuario
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?= e($u['estado']) ?>">
                                    <?= e($etiquetasFila[$u['estado']]) ?>
                                </span>
                            </td>
                            <td class="tabla-num"><?= e(formato_q($u['saldo'])) ?></td>
                            <td class="tabla-num"><?= (int) $u['transacciones'] ?></td>
                            <td class="tabla-fecha"><?= e(fecha_legible($u['fecha_registro'])) ?></td>
                            <td>
                                <?php if ($esPropia): ?>
                                    <span class="sin-datos">Su propia cuenta</span>
                                <?php else: ?>
                                    <div class="tabla-acciones">
                                        <?php foreach ($acciones as $clave => $regla):
                                            if (!in_array($u['estado'], $regla['permitidos'], true)) {
                                                continue;
                                            }
                                            ?>
                                            <form method="POST" action="<?= e(url('admin/usuarios.php')) ?>"
                                                  onsubmit="return confirm(<?= e(literal_js(
                                                      'Esta seguro de que desea ' . $regla['verbo']
                                                      . ' la cuenta de ' . $u['nombre'] . '?'
                                                  )) ?>)">
                                                <input type="hidden" name="csrf" value="<?= e(token_csrf()) ?>">
                                                <input type="hidden" name="accion" value="<?= e($clave) ?>">
                                                <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">
                                                <input type="hidden" name="filtro_estado" value="<?= e($estado) ?>">
                                                <input type="hidden" name="filtro_q" value="<?= e($buscar) ?>">
                                                <button type="submit"
                                                        class="boton <?= $clave === 'baja'
                                                            ? 'boton-peligro' : 'boton-secundario' ?>">
                                                    <?= $clave === 'suspender' ? 'Suspender'
                                                        : ($clave === 'reactivar' ? 'Reactivar' : 'Dar de baja') ?>
                                                </button>
                                            </form>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>