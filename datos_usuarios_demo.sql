-- ============================================================
-- CriptoSim - Usuarios demo adicionales
-- Proyecto Final - Desarrollo Web
-- Universidad Mariano Galvez de Guatemala
-- ============================================================
--
-- PROPOSITO
--   Agregar dos usuarios de prueba mas (Luis y Keylie) con un
--   historial de operaciones, para que las graficas, los reportes
--   y la tabla global de transacciones del administrador tengan
--   mas vida durante la presentacion.
--
-- COMO USARLO
--   1. Importar database.sql PRIMERO (base limpia).
--   2. Importar datos_demo.sql si todavia no se hizo.
--   3. Importar ESTE archivo.
--
-- RE-EJECUCION
--   Los usuarios solo se crean si el correo no existe, y las
--   operaciones solo se insertan si el usuario no tiene ninguna.
--   Se puede ejecutar varias veces sin duplicar datos.
--
-- INTEGRIDAD
--   - total SIEMPRE es cantidad * precio_unitario.
--   - precio_unitario va tomado de la tabla criptomonedas en el
--     momento de la importacion, y total se redondea a 2 decimales.
--   - El saldo final se recalcula: saldo = 10000 - compras + ventas.
--   - La cantidad de cada moneda en 'portafolio' es la suma de las
--     compras menos la suma de las ventas de esa moneda.
--   - La contrasena los tres usuarios usa el mismo hash que el
--     usuario demo (contrasena: Usuario123*).
-- ============================================================

USE criptosim;

-- Precios simulados actuales (los usa el simulador hoy).
SET @pBTC  = (SELECT precio FROM criptomonedas WHERE id_criptomoneda = 1);
SET @pETH  = (SELECT precio FROM criptomonedas WHERE id_criptomoneda = 2);
SET @pDOGE = (SELECT precio FROM criptomonedas WHERE id_criptomoneda = 3);

-- Hash de contrasena reutilizado del usuario demo (Usuario123*).
SET @hashDemo = '$2y$10$80/ej3ueeH/DoLvkZXif0Ohki02T3pDiIZeurjmRYqv.f4wC7zg0W';

-- ============================================================
-- USUARIO 3 - Luis Angel Yuman (lyuman@criptosim.com)
-- ============================================================
INSERT INTO usuarios (nombre, correo, password, saldo, rol, estado)
SELECT 'Luis Angel Yuman', 'lyuman@criptosim.com', @hashDemo, 10000.00, 'usuario', 'activo'
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE correo = 'lyuman@criptosim.com');

SET @uidLuis = (SELECT id_usuario FROM usuarios WHERE correo = 'lyuman@criptosim.com');

-- 5 operaciones: compra BTC, compra y venta de ETH, compra y venta de DOGE.
-- Todas en un solo INSERT (UNION ALL) para que el guard de re-ejecucion
-- se evalue una sola vez contra el estado previo de la base.
INSERT INTO transacciones (id_usuario, id_criptomoneda, tipo, cantidad, precio_unitario, total, fecha)
SELECT @uidLuis, 1, 'compra', 0.00200000, @pBTC,  ROUND(0.00200000 * @pBTC,  2), DATE_SUB(CURDATE(), INTERVAL 45 DAY)
WHERE NOT EXISTS (SELECT 1 FROM transacciones WHERE id_usuario = @uidLuis)
UNION ALL
SELECT @uidLuis, 2, 'compra', 0.05000000, @pETH,  ROUND(0.05000000 * @pETH,  2), DATE_SUB(CURDATE(), INTERVAL 38 DAY)
WHERE NOT EXISTS (SELECT 1 FROM transacciones WHERE id_usuario = @uidLuis)
UNION ALL
SELECT @uidLuis, 2, 'venta', 0.01500000, @pETH,  ROUND(0.01500000 * @pETH,  2), DATE_SUB(CURDATE(), INTERVAL 31 DAY)
WHERE NOT EXISTS (SELECT 1 FROM transacciones WHERE id_usuario = @uidLuis)
UNION ALL
SELECT @uidLuis, 3, 'compra', 900.00000000, @pDOGE, ROUND(900.00000000 * @pDOGE, 2), DATE_SUB(CURDATE(), INTERVAL 20 DAY)
WHERE NOT EXISTS (SELECT 1 FROM transacciones WHERE id_usuario = @uidLuis)
UNION ALL
SELECT @uidLuis, 3, 'venta', 300.00000000, @pDOGE, ROUND(300.00000000 * @pDOGE, 2), DATE_SUB(CURDATE(), INTERVAL 6 DAY)
WHERE NOT EXISTS (SELECT 1 FROM transacciones WHERE id_usuario = @uidLuis);

-- Posiciones: BTC 0.002, ETH 0.035 (0.05 - 0.015), DOGE 600 (900 - 300).
INSERT INTO portafolio (id_usuario, id_criptomoneda, cantidad)
SELECT @uidLuis, 1, 0.00200000
WHERE NOT EXISTS (SELECT 1 FROM portafolio WHERE id_usuario = @uidLuis AND id_criptomoneda = 1);

INSERT INTO portafolio (id_usuario, id_criptomoneda, cantidad)
SELECT @uidLuis, 2, 0.03500000
WHERE NOT EXISTS (SELECT 1 FROM portafolio WHERE id_usuario = @uidLuis AND id_criptomoneda = 2);

INSERT INTO portafolio (id_usuario, id_criptomoneda, cantidad)
SELECT @uidLuis, 3, 600.00000000
WHERE NOT EXISTS (SELECT 1 FROM portafolio WHERE id_usuario = @uidLuis AND id_criptomoneda = 3);

-- Saldo coherente: 10000 - compras + ventas.
UPDATE usuarios u
SET saldo = 10000.00
  - (SELECT COALESCE(SUM(total), 0) FROM transacciones t WHERE t.id_usuario = u.id_usuario AND t.tipo = 'compra')
  + (SELECT COALESCE(SUM(total), 0) FROM transacciones t WHERE t.id_usuario = u.id_usuario AND t.tipo = 'venta')
WHERE u.correo = 'lyuman@criptosim.com';

-- ============================================================
-- USUARIO 4 - Keylie Sanchez (ksanchez@criptosim.com)
-- ============================================================
INSERT INTO usuarios (nombre, correo, password, saldo, rol, estado)
SELECT 'Keylie Sanchez', 'ksanchez@criptosim.com', @hashDemo, 10000.00, 'usuario', 'activo'
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE correo = 'ksanchez@criptosim.com');

SET @uidKeylie = (SELECT id_usuario FROM usuarios WHERE correo = 'ksanchez@criptosim.com');

-- 6 operaciones: BTC x2, ETH x2, DOGE x2 (una compra y una venta de cada una).
INSERT INTO transacciones (id_usuario, id_criptomoneda, tipo, cantidad, precio_unitario, total, fecha)
SELECT @uidKeylie, 1, 'compra', 0.00300000, @pBTC,  ROUND(0.00300000 * @pBTC,  2), DATE_SUB(CURDATE(), INTERVAL 50 DAY)
WHERE NOT EXISTS (SELECT 1 FROM transacciones WHERE id_usuario = @uidKeylie)
UNION ALL
SELECT @uidKeylie, 3, 'compra', 1200.00000000, @pDOGE, ROUND(1200.00000000 * @pDOGE, 2), DATE_SUB(CURDATE(), INTERVAL 44 DAY)
WHERE NOT EXISTS (SELECT 1 FROM transacciones WHERE id_usuario = @uidKeylie)
UNION ALL
SELECT @uidKeylie, 2, 'compra', 0.03000000, @pETH,  ROUND(0.03000000 * @pETH,  2), DATE_SUB(CURDATE(), INTERVAL 35 DAY)
WHERE NOT EXISTS (SELECT 1 FROM transacciones WHERE id_usuario = @uidKeylie)
UNION ALL
SELECT @uidKeylie, 3, 'venta', 500.00000000, @pDOGE, ROUND(500.00000000 * @pDOGE, 2), DATE_SUB(CURDATE(), INTERVAL 22 DAY)
WHERE NOT EXISTS (SELECT 1 FROM transacciones WHERE id_usuario = @uidKeylie)
UNION ALL
SELECT @uidKeylie, 1, 'compra', 0.00100000, @pBTC,  ROUND(0.00100000 * @pBTC,  2), DATE_SUB(CURDATE(), INTERVAL 12 DAY)
WHERE NOT EXISTS (SELECT 1 FROM transacciones WHERE id_usuario = @uidKeylie)
UNION ALL
SELECT @uidKeylie, 2, 'venta', 0.01200000, @pETH,  ROUND(0.01200000 * @pETH,  2), DATE_SUB(CURDATE(), INTERVAL 3 DAY)
WHERE NOT EXISTS (SELECT 1 FROM transacciones WHERE id_usuario = @uidKeylie);

-- Posiciones: BTC 0.004 (0.003 + 0.001), ETH 0.018 (0.03 - 0.012), DOGE 700 (1200 - 500).
INSERT INTO portafolio (id_usuario, id_criptomoneda, cantidad)
SELECT @uidKeylie, 1, 0.00400000
WHERE NOT EXISTS (SELECT 1 FROM portafolio WHERE id_usuario = @uidKeylie AND id_criptomoneda = 1);

INSERT INTO portafolio (id_usuario, id_criptomoneda, cantidad)
SELECT @uidKeylie, 2, 0.01800000
WHERE NOT EXISTS (SELECT 1 FROM portafolio WHERE id_usuario = @uidKeylie AND id_criptomoneda = 2);

INSERT INTO portafolio (id_usuario, id_criptomoneda, cantidad)
SELECT @uidKeylie, 3, 700.00000000
WHERE NOT EXISTS (SELECT 1 FROM portafolio WHERE id_usuario = @uidKeylie AND id_criptomoneda = 3);

-- Saldo coherente: 10000 - compras + ventas.
UPDATE usuarios u
SET saldo = 10000.00
  - (SELECT COALESCE(SUM(total), 0) FROM transacciones t WHERE t.id_usuario = u.id_usuario AND t.tipo = 'compra')
  + (SELECT COALESCE(SUM(total), 0) FROM transacciones t WHERE t.id_usuario = u.id_usuario AND t.tipo = 'venta')
WHERE u.correo = 'ksanchez@criptosim.com';

-- ============================================================
-- VERIFICACION
--   SELECT nombre, correo, saldo FROM usuarios;
--   SELECT id_usuario, tipo, COUNT(*) FROM transacciones GROUP BY id_usuario, tipo;
-- ============================================================