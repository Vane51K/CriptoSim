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

// Moneda mas comprada (por cantidad acumulada)
$stmt = $conexion->query(
    "SELECT c.nombre, c.simbolo, COALESCE(SUM(t.cantidad), 0) AS cantidad_total,
            COUNT(*) AS operaciones
     FROM transacciones t
     INNER JOIN criptomonedas c ON c.id_criptomoneda = t.id_criptomoneda
     WHERE t.tipo = 'compra'
     GROUP BY c.id_criptomoneda, c.nombre, c.simbolo
     ORDER BY cantidad_total DESC, operaciones DESC, c.simbolo ASC
     LIMIT 1"
);
$masComprada = $stmt->fetch_assoc();
$stmt->close();

// Moneda mas vendida (por cantidad acumulada)
$stmt = $conexion->query(
    "SELECT c.nombre, c.simbolo, COALESCE(SUM(t.cantidad), 0) AS cantidad_total,
            COUNT(*) AS operaciones
     FROM transacciones t
     INNER JOIN criptomonedas c ON c.id_criptomoneda = t.id_criptomoneda
     WHERE t.tipo = 'venta'
     GROUP BY c.id_criptomoneda, c.nombre, c.simbolo
     ORDER BY cantidad_total DESC, operaciones DESC, c.simbolo ASC
     LIMIT 1"
);
$masVendida = $stmt->fetch_assoc();
$stmt->close();

// Top Traders: Patrimonio total (saldo + valor actual del portafolio)
// Cruza usuarios, portafolio y criptomonedas para demostrar uso de JOIN/agrupacion
$stmt = $conexion->query(
    "SELECT u.id_usuario, u.nombre, u.correo, u.saldo,
            COALESCE(SUM(p.cantidad * c.precio), 0) AS valor_portafolio,
            (u.saldo + COALESCE(SUM(p.cantidad * c.precio), 0)) AS patrimonio,
            COUNT(DISTINCT p.id_criptomoneda) AS criptos_distintas
     FROM usuarios u
     LEFT JOIN portafolio p ON p.id_usuario = u.id_usuario AND p.cantidad > 0
     LEFT JOIN criptomonedas c ON c.id_criptomoneda = p.id_criptomoneda
     WHERE u.rol = 'usuario' AND u.estado = 'activo'
     GROUP BY u.id_usuario, u.nombre, u.correo, u.saldo
     ORDER BY patrimonio DESC, u.id_usuario ASC
     LIMIT 5"
);
$topTraders = $stmt->fetch_all(MYSQLI_ASSOC);
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

        <div class="indicador">
            <p class="indicador-etiqueta">Moneda más comprada</p>
            <?php if ($masComprada): ?>
                <p class="indicador-valor"><?= e($masComprada['simbolo']) ?></p>
                <p class="indicador-pie"><?= e($masComprada['nombre']) ?> · <?= e(formato_cantidad($masComprada['cantidad_total'])) ?> comprados (<?= (int) $masComprada['operaciones'] ?> ops)</p>
            <?php else: ?>
                <p class="indicador-valor">-</p>
                <p class="indicador-pie">Aún sin compras registradas</p>
            <?php endif; ?>
        </div>

        <div class="indicador">
            <p class="indicador-etiqueta">Moneda más vendida</p>
            <?php if ($masVendida): ?>
                <p class="indicador-valor"><?= e($masVendida['simbolo']) ?></p>
                <p class="indicador-pie"><?= e($masVendida['nombre']) ?> · <?= e(formato_cantidad($masVendida['cantidad_total'])) ?> vendidos (<?= (int) $masVendida['operaciones'] ?> ops)</p>
            <?php else: ?>
                <p class="indicador-valor">-</p>
                <p class="indicador-pie">Aún sin ventas registradas</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="seccion panel">
    <h2 class="seccion-titulo">Top Traders</h2>
    <p class="ayuda">Ranking de los 5 usuarios activos con mayor patrimonio (saldo + valor actual de su portafolio).</p>

    <?php if (empty($topTraders)): ?>
        <p class="sin-datos">Aún no hay usuarios activos para mostrar en el ranking.</p>
    <?php else: ?>
        <div class="tabla-envoltura">
            <table class="tabla tabla-ancha">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Usuario</th>
                        <th class="tabla-num">Saldo virtual</th>
                        <th class="tabla-num">Valor portafolio</th>
                        <th class="tabla-num">Patrimonio total</th>
                        <th class="tabla-num">Criptos distintas</th>
                        <th>Ver</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topTraders as $i => $trader): 
                        $posicion = $i + 1;
                    ?>
                        <tr>
                            <td><strong><?= $posicion ?></strong></td>
                            <td>
                                <strong><?= e($trader['nombre']) ?></strong>
                                <span class="tabla-correo"><?= e($trader['correo']) ?></span>
                            </td>
                            <td class="tabla-num"><?= e(formato_q($trader['saldo'])) ?></td>
                            <td class="tabla-num"><?= e(formato_q($trader['valor_portafolio'])) ?></td>
                            <td class="tabla-num tabla-total"><?= e(formato_q($trader['patrimonio'])) ?></td>
                            <td class="tabla-num"><?= (int) $trader['criptos_distintas'] ?></td>
                            <td>
                                <a href="<?= e(url('admin/usuario_detalle.php?id=' . (int) $trader['id_usuario'])) ?>"
                                   class="boton boton-secundario boton-compacto">Detalle</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
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
            <h3>Transacciones globales</h3>
            <p>Listado completo de compras y ventas de todos los usuarios con filtro por tipo.</p>
            <a href="<?= e(url('admin/transacciones.php')) ?>" class="boton boton-primario boton-chico">
                Ver transacciones
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