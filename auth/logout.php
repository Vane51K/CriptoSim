<?php
// CriptoSim - Cierre de sesion

require_once __DIR__ . '/../includes/auth.php';

cerrar_sesion();
mensaje_flash('exito', 'Sesion cerrada correctamente.');
redirigir('index.php');