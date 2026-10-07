<?php
// CriptoSim - Dashboard administrativo

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/auth.php';

requiere_admin();
verificar_estado_cuenta();

// Los seis indicadores salen de dos consultas agregadas y no de seis
// viajes a la base. COUNT(*) mas SUM(condicion) alcanza: MySQL
// resuelve la condicion a 1 o 0 por fila y las suma.
$stmt = $conexion->query(
    "SELECT COUNT(*) AS registrados,
            COALESCE(SUM(estado = 'activo'), 0) AS activos,
            COALESCE(SUM(estado = 'suspendido'), 0) AS suspendidos,
            COALESCE(SUM(estado = 'baja'), 0) AS dados_de_baja
     FROM usuarios"
);
$usuarios = $stmt->fetch_assoc();
$stmt->close();

$stmt = $conexion->query(
    "SELECT COUNT(*) AS total,
            COALESCE(SUM(tipo = 'compra'), 0) AS compras,
            COALESCE(SUM(tipo = 'venta'), 0) AS ventas,
            COALESCE(SUM(CASE WHEN tipo = 'compra' THEN total ELSE 0 END), 0) AS monto_compras,
            COALESCE(SUM(CASE WHEN tipo = 'venta' THEN total ELSE 0 END), 0) AS monto_ventas
     FROM transacciones"
);
$movimientos = $stmt->fetch_assoc();
$stmt->close();

$titulo = 'Panel administrativo';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="saludo">
    <h1>Area administrativa</h1>
    <p>Resumen del simulador y acceso a la gestion de usuarios y reportes</p>
</section>

<section class="seccion">
    <div class="grid-indicadores">
        <div class="indicador">
            <p class="indicador-etiqueta">Usuarios registrados</p>
            <p class="indicador-valor"><?= (int) $usuarios['registrados'] ?></p>
            <p class="indicador-pie">Cuentas creadas en el simulador</p>
        </div>

        <div class="indicador">
            <p class="indicador-etiqueta">Usuarios activos</p>
            <p class="indicador-valor"><?= (int) $usuarios['activos'] ?></p>
            <p class="indicador-pie">Pueden entrar y operar con normalidad</p>
        </div>

        <div class="indicador">
            <p class="indicador-etiqueta">Usuarios suspendidos</p>
            <p class="indicador-valor"><?= (int) $usuarios['suspendidos'] ?></p>
            <p class="indicador-pie">Cuentas bloqueadas por el administrador</p>
        </div>

        <div class="indicador">
            <p class="indicador-etiqueta">Total de transacciones</p>
            <p class="indicador-valor"><?= (int) $movimientos['total'] ?></p>
            <p class="indicador-pie">Compras y ventas registradas</p>
        </div>

        <div class="indicador">
            <p class="indicador-etiqueta">Total de compras</p>
            <p class="indicador-valor"><?= (int) $movimientos['compras'] ?></p>
            <p class="indicador-pie"><?= e(formato_q($movimientos['monto_compras'])) ?> en compras</p>
        </div>

        <div class="indicador">
            <p class="indicador-etiqueta">Total de ventas</p>
            <p class="indicador-valor"><?= (int) $movimientos['ventas'] ?></p>
            <p class="indicador-pie"><?= e(formato_q($movimientos['monto_ventas'])) ?> en ventas</p>
        </div>
    </div>
</section>

<section class="seccion panel">
    <h2 class="seccion-titulo">Gestion</h2>

    <div class="grid-tarjetas">
        <div class="tarjeta">
            <h3>Usuarios</h3>
            <p>Consultar cuentas y cambiar su estado: suspender, reactivar o dar de baja.</p>
            <a href="<?= e(url('admin/usuarios.php')) ?>" class="boton boton-primario boton-chico">
                Administrar usuarios
            </a>
        </div>

        <div class="tarjeta">
            <h3>Reportes</h3>
            <p>Movimientos por usuario, balance general y rankings de criptomonedas.</p>
            <a href="<?= e(url('admin/reportes.php')) ?>" class="boton boton-primario boton-chico">
                Ver reportes
            </a>
        </div>

        <div class="tarjeta">
            <h3>Estado del sistema</h3>
            <p>
                <?= (int) $usuarios['dados_de_baja'] ?> cuenta(s) dada(s) de baja
                en el historial.
            </p>
            <p class="sin-datos">
                Dar de baja es un cambio de estado: nunca borra transacciones
                ni portafolio.
            </p>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>