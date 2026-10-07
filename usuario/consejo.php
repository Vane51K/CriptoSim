<?php
// CriptoSim - Endpoint JSON del consejo de SIMI

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/trading.php';   // trae auth, sesion y listar_mercado()
require_once __DIR__ . '/../includes/consejos.php';

header('Content-Type: application/json; charset=utf-8');

if (!usuario_logueado()) {
    // 401 en JSON y no un redirect a login: el fetch no debe
    // terminar parsiando HTML como si fuera JSON.
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Sesion no valida.']);
    exit;
}

actualizar_precios_simulados();

$mercado = aplicar_precios(listar_mercado());

echo json_encode(['ok' => true] + consejo_simi($mercado), JSON_UNESCAPED_UNICODE);