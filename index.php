<?php
// CriptoSim - Pagina de inicio

require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/mascota.php';

// Si ya hay sesion, va directo a su panel
if (usuario_logueado()) {
    redirigir(es_admin() ? 'admin/dashboard.php' : 'usuario/dashboard.php');
}

$saldoInicial = saldo_inicial();

$criptos = $conexion->query("SELECT nombre, simbolo, precio, variacion FROM criptomonedas WHERE estado = 'activo' ORDER BY id_criptomoneda")->fetch_all(MYSQLI_ASSOC);

$titulo = 'Inicio';
require_once __DIR__ . '/includes/header.php';
?>

<section class="portada">
    <div class="portada-escena">
        <div class="portada-texto">
            <h1>Simulador de compra y venta de criptomonedas</h1>
            <p class="portada-sub">
                Practica como se comportaria el mercado sin usar dinero real.
                Registrate y recibe <?= e(formato_q($saldoInicial)) ?> de saldo virtual.
            </p>
        </div>

        <?= simi_con_burbuja('bienvenida',
            'Hola, soy <strong>SIMI</strong>. Te acompañare mientras aprendes a operar. Ningun riesgo, todo simulacion.') ?>


        <div class="portada-botonera">
            <a href="<?= e(url('auth/registro.php')) ?>" class="boton boton-primario">Crear mi cuenta</a>
            <a href="<?= e(url('auth/login.php')) ?>" class="boton boton-secundario">Iniciar sesion</a>
        </div>
    </div>
</section>

<section class="seccion">
    <h2 class="seccion-titulo">Criptografias disponibles</h2>
    <div class="grid-tarjetas">
        <?php foreach ($criptos as $cripto): ?>
            <div class="tarjeta cripto-tarjeta">
                <div class="cripto-encabezado">
                    <span class="cripto-simbolo"><?= e($cripto['simbolo']) ?></span>
                    <div>
                        <h3><?= e($cripto['nombre']) ?></h3>
                        <span class="cripto-simbolo-texto"><?= e($cripto['simbolo']) ?></span>
                    </div>
                </div>
                <p class="cripto-precio"><?= e(formato_q($cripto['precio'])) ?></p>
                <span class="variacion <?= $cripto['variacion'] >= 0 ? 'variacion-sube' : 'variacion-baja' ?>">
                    <?= $cripto['variacion'] >= 0 ? '+' : '' ?><?= e($cripto['variacion']) ?>%
                </span>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="seccion">
    <h2 class="seccion-titulo">Como funciona</h2>
    <div class="grid-pasos">
        <div class="paso">
            <span class="paso-numero">1</span>
            <h3>Registrate</h3>
            <p>Crea tu cuenta y recibes saldo virtual de inmediato.</p>
        </div>
        <div class="paso">
            <span class="paso-numero">2</span>
            <h3>Compra</h3>
            <p>Elige una criptomoneda e invierte tu saldo simulado.</p>
        </div>
        <div class="paso">
            <span class="paso-numero">3</span>
            <h3>Vende</h3>
            <p>Revierte tu portafolio y observa como cambia tu saldo.</p>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
