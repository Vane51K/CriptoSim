-- ============================================================
-- CriptoSim - Datos de demostracion
-- Proyecto Final - Desarrollo Web
-- Universidad Mariano Galvez de Guatemala
-- ============================================================
--
-- PROPOSITO
--   Poblar la base con un historial de operaciones ficticio pero
--   coherente, para que los 5 reportes del administrador y las
--   3 graficas tengan datos que mostrar durante la presentacion.
--
-- COMO USARLO
--   1. Importar database.sql PRIMERO (crea la base limpia).
--   2. Importar este archivo DESPUES.
--   3. Para volver al estado limpio, ejecutar el bloque de
--      limpieza de la seccion final de este archivo.
--
-- ADVERTENCIA
--   Este archivo BORRA el historial de los dos usuarios de prueba
--   (id 1 = administrador, id 2 = usuario demo) antes de insertar.
--   No lo ejecutes sobre una base con datos reales.
--
-- SOBRE EL SALDO
--   A diferencia de database.sql, aqui el saldo SI se recalcula,
--   porque las operaciones de abajo son un historico completo y
--   van dejando la cuenta en un punto coherente:
--       saldo_final = 10000 - compras + ventas
--   Si no se recalculara, el usuario tendria un saldo de Q10,000
--   con posiciones por mas de eso, y la cartera no cuadraria.
--
-- INTEGRIDAD
--   - total SIEMPRE es igual a cantidad * precio_unitario.
--   - precio_unitario SIEMPRE coincide con el precio simulado de
--     la tabla 'criptomonedas' (BTC 500000, ETH 25000, DOGE 1.50).
--   - La cantidad de cada fila de 'portafolio' es la suma de las
--     compras menos la suma de las ventas de esa moneda.
--   - Ninguna cantidad queda negativa.
-- ============================================================

USE criptosim;

-- ------------------------------------------------------------
-- Limpieza previa (hace que el archivo sea re-ejecutable)
-- ------------------------------------------------------------
DELETE FROM transacciones WHERE id_usuario IN (1, 2);
DELETE FROM portafolio    WHERE id_usuario IN (1, 2);

-- ------------------------------------------------------------
-- USUARIO 1 - Administrador (admin@criptosim.com)
-- Saldo: Q10,000.00 -> Q5,800.00
-- ------------------------------------------------------------
INSERT INTO transacciones (id_usuario, id_criptomoneda, tipo, cantidad, precio_unitario, total, fecha) VALUES
(1, 1, 'compra', 0.00300000, 500000.00000000,  1500.00, DATE_SUB(CURDATE(), INTERVAL 90 DAY)),
(1, 2, 'compra', 0.08000000,  25000.00000000,  2000.00, DATE_SUB(CURDATE(), INTERVAL 85 DAY)),
(1, 3, 'compra',  800.00000000,      1.50000000,  1200.00, DATE_SUB(CURDATE(), INTERVAL 78 DAY)),
(1, 3, 'venta',   300.00000000,      1.50000000,   450.00, DATE_SUB(CURDATE(), INTERVAL 70 DAY)),
(1, 1, 'venta',   0.00100000, 500000.00000000,   500.00, DATE_SUB(CURDATE(), INTERVAL 62 DAY)),
(1, 2, 'venta',   0.02000000,  25000.00000000,   500.00, DATE_SUB(CURDATE(), INTERVAL 55 DAY)),
(1, 2, 'compra', 0.04000000,  25000.00000000,  1000.00, DATE_SUB(CURDATE(), INTERVAL 48 DAY)),
(1, 1, 'compra', 0.00100000, 500000.00000000,   500.00, DATE_SUB(CURDATE(), INTERVAL 40 DAY)),
(1, 3, 'venta',   100.00000000,      1.50000000,   150.00, DATE_SUB(CURDATE(), INTERVAL 33 DAY)),
(1, 3, 'compra',  600.00000000,      1.50000000,   900.00, DATE_SUB(CURDATE(), INTERVAL 26 DAY)),
(1, 1, 'venta',   0.00100000, 500000.00000000,   500.00, DATE_SUB(CURDATE(), INTERVAL 19 DAY)),
(1, 3, 'compra',  200.00000000,      1.50000000,   300.00, DATE_SUB(CURDATE(), INTERVAL 13 DAY)),
(1, 3, 'venta',   400.00000000,      1.50000000,   600.00, DATE_SUB(CURDATE(), INTERVAL  7 DAY)),
(1, 2, 'venta',   0.02000000,  25000.00000000,   500.00, DATE_SUB(CURDATE(), INTERVAL  2 DAY));

-- Posiciones del administrador, derivadas de las 14 operaciones de arriba:
--   BTC : +0.003 -0.001 +0.001 -0.001 = 0.002
--   ETH : +0.080 -0.020 +0.040 -0.020 = 0.080
--   DOGE: +800 -300 -100 +600 +200 -400 = 800
INSERT INTO portafolio (id_usuario, id_criptomoneda, cantidad) VALUES
(1, 1, 0.00200000),
(1, 2, 0.08000000),
(1, 3, 800.00000000);

-- Compras: 7400.00   Ventas: 3200.00   10000 - 7400 + 3200 = 5800.00
UPDATE usuarios SET saldo = 5800.00 WHERE id_usuario = 1;

-- ------------------------------------------------------------
-- USUARIO 2 - Usuario Demo (usuario@criptosim.com)
-- Saldo: Q10,000.00 -> Q4,400.00
-- ------------------------------------------------------------
INSERT INTO transacciones (id_usuario, id_criptomoneda, tipo, cantidad, precio_unitario, total, fecha) VALUES
(2, 1, 'compra', 0.00400000, 500000.00000000,  2000.00, DATE_SUB(CURDATE(), INTERVAL 92 DAY)),
(2, 2, 'compra', 0.04000000,  25000.00000000,  1000.00, DATE_SUB(CURDATE(), INTERVAL 84 DAY)),
(2, 3, 'compra', 1000.00000000,     1.50000000,  1500.00, DATE_SUB(CURDATE(), INTERVAL 76 DAY)),
(2, 3, 'venta',  200.00000000,     1.50000000,   300.00, DATE_SUB(CURDATE(), INTERVAL 68 DAY)),
(2, 1, 'venta',  0.00100000, 500000.00000000,   500.00, DATE_SUB(CURDATE(), INTERVAL 60 DAY)),
(2, 1, 'compra', 0.00200000, 500000.00000000,  1000.00, DATE_SUB(CURDATE(), INTERVAL 52 DAY)),
(2, 3, 'compra',  400.00000000,     1.50000000,   600.00, DATE_SUB(CURDATE(), INTERVAL 44 DAY)),
(2, 2, 'venta',  0.01000000,  25000.00000000,   250.00, DATE_SUB(CURDATE(), INTERVAL 36 DAY)),
(2, 1, 'venta',  0.00100000, 500000.00000000,   500.00, DATE_SUB(CURDATE(), INTERVAL 28 DAY)),
(2, 2, 'compra', 0.02000000,  25000.00000000,   500.00, DATE_SUB(CURDATE(), INTERVAL 21 DAY)),
(2, 3, 'venta',  100.00000000,     1.50000000,   150.00, DATE_SUB(CURDATE(), INTERVAL 15 DAY)),
(2, 1, 'compra', 0.00100000, 500000.00000000,   500.00, DATE_SUB(CURDATE(), INTERVAL  9 DAY)),
(2, 2, 'venta',  0.01000000,  25000.00000000,   250.00, DATE_SUB(CURDATE(), INTERVAL  5 DAY)),
(2, 3, 'compra',  300.00000000,     1.50000000,   450.00, DATE_SUB(CURDATE(), INTERVAL  1 DAY));

-- Posiciones del usuario demo, derivadas de las 14 operaciones de arriba:
--   BTC : +0.004 -0.001 +0.002 -0.001 +0.001 = 0.005
--   ETH : +0.040 -0.010 +0.020 -0.010 = 0.040
--   DOGE: +1000 -200 +400 -100 +300 = 1400
INSERT INTO portafolio (id_usuario, id_criptomoneda, cantidad) VALUES
(2, 1, 0.00500000),
(2, 2, 0.04000000),
(2, 3, 1400.00000000);

-- Compras: 7550.00   Ventas: 1950.00   10000 - 7550 + 1950 = 4400.00
UPDATE usuarios SET saldo = 4400.00 WHERE id_usuario = 2;

-- ------------------------------------------------------------
-- COMO VOLVER AL ESTADO LIMPIO
-- Ejecutar este bloque en phpMyAdmin o por consola:
--
--   DELETE FROM transacciones WHERE id_usuario IN (1, 2);
--   DELETE FROM portafolio    WHERE id_usuario IN (1, 2);
--   UPDATE usuarios SET saldo = 10000.00 WHERE id_usuario IN (1, 2);
--
-- Resultado: 2 usuarios, 3 criptomonedas, 0 transacciones,
--            0 posiciones de portafolio, Q10,000.00 cada uno.
-- ============================================================