-- ============================================================
-- CriptoSim - Simulador de Compra y Venta de Criptomonedas
-- Proyecto Final - Desarrollo Web
-- Universidad Mariano Galvez de Guatemala
-- Base de datos MySQL 8.0
-- Importable desde MySQL Workbench
-- ============================================================

DROP DATABASE IF EXISTS criptosim;
CREATE DATABASE criptosim CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE criptosim;

-- ------------------------------------------------------------
-- Tabla: usuarios
-- Almacena las cuentas de la simulacion y su saldo ficticio.
-- ------------------------------------------------------------
CREATE TABLE usuarios (
    id_usuario      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre          VARCHAR(100) NOT NULL,
    correo          VARCHAR(120) NOT NULL,
    password        VARCHAR(255) NOT NULL,
    -- DEFAULT 0 a proposito: el saldo de una cuenta nueva SIEMPRE lo
    -- escribe registro.php leyendo config_sistema.saldo_inicial, que
    -- es el unico lugar del proyecto donde vive el monto inicial.
    saldo           DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    rol             ENUM('usuario','admin') NOT NULL DEFAULT 'usuario',
    estado          ENUM('activo','suspendido','baja') NOT NULL DEFAULT 'activo',
    fecha_registro  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_usuario),
    UNIQUE KEY uq_usuarios_correo (correo),
    KEY idx_usuarios_estado (estado),
    CONSTRAINT chk_usuarios_saldo CHECK (saldo >= 0)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabla: criptomonedas
-- Precios simulados. NO son precios reales de mercado.
-- ------------------------------------------------------------
CREATE TABLE criptomonedas (
    id_criptomoneda  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre           VARCHAR(50) NOT NULL,
    simbolo          VARCHAR(10) NOT NULL,
    precio           DECIMAL(18,8) NOT NULL,
    variacion        DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    estado           ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
    PRIMARY KEY (id_criptomoneda),
    UNIQUE KEY uq_cripto_simbolo (simbolo),
    CONSTRAINT chk_cripto_precio CHECK (precio > 0)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabla: portafolio
-- Cantidad que posee cada usuario de cada criptomoneda.
-- El UNIQUE evita tener dos filas para la misma moneda.
-- ------------------------------------------------------------
CREATE TABLE portafolio (
    id_portafolio     INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_usuario        INT UNSIGNED NOT NULL,
    id_criptomoneda   INT UNSIGNED NOT NULL,
    cantidad          DECIMAL(18,8) NOT NULL DEFAULT 0,
    PRIMARY KEY (id_portafolio),
    UNIQUE KEY uq_portafolio (id_usuario, id_criptomoneda),
    KEY idx_portafolio_usuario (id_usuario),
    CONSTRAINT fk_portafolio_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuarios (id_usuario) ON DELETE CASCADE,
    CONSTRAINT fk_portafolio_cripto FOREIGN KEY (id_criptomoneda)
        REFERENCES criptomonedas (id_criptomoneda) ON DELETE RESTRICT,
    CONSTRAINT chk_portafolio_cantidad CHECK (cantidad >= 0)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabla: transacciones
-- Historial de compras y ventas. Nunca se modifica ni se borra.
-- ------------------------------------------------------------
CREATE TABLE transacciones (
    id_transaccion   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_usuario       INT UNSIGNED NOT NULL,
    id_criptomoneda  INT UNSIGNED NOT NULL,
    tipo             ENUM('compra','venta') NOT NULL,
    cantidad         DECIMAL(18,8) NOT NULL,
    precio_unitario  DECIMAL(18,8) NOT NULL,
    total            DECIMAL(15,2) NOT NULL,
    fecha            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_transaccion),
    KEY idx_trans_usuario (id_usuario),
    KEY idx_trans_cripto (id_criptomoneda),
    KEY idx_trans_fecha (fecha),
    CONSTRAINT fk_trans_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuarios (id_usuario) ON DELETE CASCADE,
    CONSTRAINT fk_trans_cripto FOREIGN KEY (id_criptomoneda)
        REFERENCES criptomonedas (id_criptomoneda) ON DELETE RESTRICT,
    CONSTRAINT chk_trans_cantidad CHECK (cantidad > 0),
    CONSTRAINT chk_trans_total CHECK (total > 0)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabla: config_sistema
-- Parametros modificables sin tocar codigo PHP.
-- ------------------------------------------------------------
CREATE TABLE config_sistema (
    clave   VARCHAR(60) NOT NULL,
    valor   VARCHAR(255) NOT NULL,
    PRIMARY KEY (clave)
) ENGINE=InnoDB;

-- Nota: no existe una tabla de cache de precios. Los precios son
-- simulados y se leen directamente de la tabla 'criptomonedas'.
-- El sistema no consulta ninguna API externa. La variacion se simula
-- en includes/precios.php (actualizar_precios_simulados), que mueve
-- los precios sobre la propia base y guarda el momento en
-- config_sistema bajo la clave 'ultima_actualizacion_precios'.
-- Esa fila se crea sola en la primera actualizacion, no hace falta
-- declararla aca.

-- ============================================================
-- DATOS INICIALES
-- ============================================================

-- Criptografias con precios simulados
INSERT INTO criptomonedas (nombre, simbolo, precio, variacion, estado) VALUES
('Bitcoin',   'BTC',  500000.00000000,  2.45, 'activo'),
('Ethereum',  'ETH',   25000.00000000, -1.30, 'activo'),
('Dogecoin',  'DOGE',      1.50000000,  4.80, 'activo');

-- Parametros del sistema
INSERT INTO config_sistema (clave, valor) VALUES
-- ESTA ES LA UNICA LINEA DEL PROYECTO DONDE VIVE EL SALDO INICIAL.
-- Para cambiarlo se edita este valor y nada mas: registro.php, index.php
-- y los usuarios de prueba lo leen de aca (ver saldo_inicial() en
-- includes/funciones.php).
('saldo_inicial',      '10000.00'),
('nombre_sistema',     'CriptoSim'),
('moneda',             'Q');
-- No se guardan claves de API ni de tipo de cambio: los precios son
-- simulados y salen de la tabla 'criptancias'. Ver la nota de
-- arriba y el comentario de cabecera de includes/precios.php.

-- Usuario administrador de prueba
-- Correo: admin@criptosim.com
-- Contrasena: Admin123*
-- El saldo se toma de config_sistema.saldo_inicial en vez de escribir
-- el monto otra vez: asi el inicial se cambia en un solo lugar.
INSERT INTO usuarios (nombre, correo, password, saldo, rol, estado)
SELECT 'Administrador', 'admin@criptosim.com',
 '$2y$10$2k6.j1BFTDGX612dYMqjoeXoRcLHrXLIVzcE3o8ltX2E3KLjqTUYC',
 (SELECT CAST(valor AS DECIMAL(15,2)) FROM config_sistema WHERE clave = 'saldo_inicial'),
 'admin', 'activo';

-- Usuario de prueba
-- Correo: usuario@criptosim.com
-- Contrasena: Usuario123*
INSERT INTO usuarios (nombre, correo, password, saldo, rol, estado)
SELECT 'Usuario Demo', 'usuario@criptosim.com',
 '$2y$10$80/ej3ueeH/DoLvkZXif0Ohki02T3pDiIZeurjmRYqv.f4wC7zg0W',
 (SELECT CAST(valor AS DECIMAL(15,2)) FROM config_sistema WHERE clave = 'saldo_inicial'),
 'usuario', 'activo';
