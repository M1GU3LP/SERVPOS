-- SERVPOS - Sistema de punto de venta para San Juan Comidas Rapidas
-- Script de creacion de base de datos (MVP: Usuarios/Roles, Inventario, Pedidos)

CREATE DATABASE IF NOT EXISTS servpos_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE servpos_db;

-- =========================================================
-- DRF1 / DRF2 - Usuarios y roles (RF1.1-RF1.7, RF2.1-RF2.6)
-- =========================================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    rol ENUM('propietario', 'gerente', 'cajero') NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================================
-- DRF3 - Inventario (RF3.1-RF3.9)
-- =========================================================
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    descripcion VARCHAR(255) NULL,
    categoria VARCHAR(60) NULL,
    precio DECIMAL(10,2) NOT NULL,
    stock_actual INT NOT NULL DEFAULT 0,
    stock_minimo INT NOT NULL DEFAULT 5,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_stock_actual CHECK (stock_actual >= 0),
    CONSTRAINT chk_stock_minimo CHECK (stock_minimo >= 0)
) ENGINE=InnoDB;

CREATE TABLE variantes_producto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    nombre VARCHAR(60) NOT NULL,
    precio_adicional DECIMAL(10,2) NOT NULL DEFAULT 0,
    CONSTRAINT fk_variante_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE movimientos_inventario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    tipo ENUM('entrada', 'salida', 'venta') NOT NULL,
    cantidad INT NOT NULL,
    usuario_id INT NOT NULL,
    motivo VARCHAR(255) NULL,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_movimiento_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT,
    CONSTRAINT fk_movimiento_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    CONSTRAINT chk_movimiento_cantidad CHECK (cantidad > 0)
) ENGINE=InnoDB;

-- =========================================================
-- DRF4 - Pedidos (RF4.1-RF4.10)
-- =========================================================
CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mesa_o_cliente VARCHAR(100) NOT NULL,
    -- borrador: en armado por el cajero. pendiente/en_preparacion/listo: ya confirmado, visible en cocina.
    -- confirmado: entregado/cerrado. cancelado: anulado.
    estado ENUM('borrador', 'pendiente', 'en_preparacion', 'listo', 'confirmado', 'cancelado') NOT NULL DEFAULT 'borrador',
    usuario_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL DEFAULT 0,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pedido_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE detalle_pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    producto_id INT NOT NULL,
    variante_id INT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_detalle_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    CONSTRAINT fk_detalle_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT,
    CONSTRAINT fk_detalle_variante FOREIGN KEY (variante_id) REFERENCES variantes_producto(id) ON DELETE RESTRICT,
    CONSTRAINT chk_detalle_cantidad CHECK (cantidad > 0)
) ENGINE=InnoDB;

-- =========================================================
-- Datos semilla
-- =========================================================

-- Usuario propietario inicial. Correo: propietario@sanjuancomidas.com / Password: Propietario123
-- (hash bcrypt real, compatible con password_verify() de PHP)
INSERT INTO usuarios (nombre, correo, password_hash, rol) VALUES
('Juan Herrera', 'propietario@sanjuancomidas.com', '$2a$10$O8BAQJ5NpOIkk5G2hKvEheKpteTTlm0qWJjP6hhegrAaELFgfvQPa', 'propietario');

-- Productos de ejemplo (menu de comida rapida)
INSERT INTO productos (nombre, descripcion, categoria, precio, stock_actual, stock_minimo) VALUES
('Hamburguesa sencilla', 'Carne, queso, lechuga y tomate', 'Comidas', 12000.00, 30, 5),
('Hamburguesa doble', 'Doble carne y doble queso', 'Comidas', 16000.00, 20, 5),
('Papas fritas', 'Porcion de papas fritas', 'Acompañamientos', 6000.00, 40, 10),
('Gaseosa', 'Bebida gaseosa 400ml', 'Bebidas', 4000.00, 50, 10);

INSERT INTO variantes_producto (producto_id, nombre, precio_adicional) VALUES
(1, 'Pequeña', 0.00),
(1, 'Grande', 3000.00),
(3, 'Pequeña', 0.00),
(3, 'Grande', 2000.00);
