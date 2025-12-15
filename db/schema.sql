-- MySQL 8.0
CREATE DATABASE IF NOT EXISTS tiendanova CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tiendanova;

-- Categoría (Adjacency List)
CREATE TABLE categorias (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    nombre              VARCHAR(120) NOT NULL,
    descripcion         TEXT NULL,
    parent_id           INT NULL,
    nivel               INT NOT NULL DEFAULT 0,   
    CONSTRAINT fk_categorias_parent
        FOREIGN KEY (parent_id) REFERENCES categorias(id)
        ON DELETE SET NULL ON UPDATE CASCADE
);

-- Proveedores
CREATE TABLE proveedores (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    nombre_empresa      VARCHAR(150) NOT NULL,
    contacto            VARCHAR(120) NOT NULL,
    telefono            VARCHAR(40) NOT NULL,
    email               VARCHAR(150) NOT NULL,
    direccion           VARCHAR(200) NOT NULL
);

-- Productos
CREATE TABLE productos (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    nombre              VARCHAR(150) NOT NULL,
    descripcion         TEXT,
    precio              DECIMAL(12, 2) NOT NULL CHECK (precio >= 0),
    categoria_id        INT NOT NULL,
    proveedor_id        INT NOT NULL,
    fecha_registro      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    activo              TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT fk_prod_categoria FOREIGN KEY (categoria_id)
        REFERENCES categorias(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_prod_proveedor FOREIGN KEY (proveedor_id)
        REFERENCES proveedores(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_prod_categoria (categoria_id),
    INDEX idx_prod_proveedor (proveedor_id)
);

ALTER TABLE productos ADD COLUMN descuento INT DEFAULT NULL;
ALTER TABLE productos ADD COLUMN precio_original DECIMAL(10,2) DEFAULT NULL;
ALTER TABLE productos ADD activo_manual TINYINT(1) NOT NULL DEFAULT 1;

-- Stock
CREATE TABLE stocks (
    id                          INT AUTO_INCREMENT PRIMARY KEY,
    producto_id                 INT NOT NULL UNIQUE,
    cantidad                    INT NOT NULL DEFAULT 0 CHECK (cantidad >= 0),
    ubicacion                   VARCHAR(120) NOT NULL,
    fecha_ultima_actualizacion  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    stock_minimo                INT NOT NULL DEFAULT 0 CHECK (stock_minimo >= 0),
    CONSTRAINT fk_stock_producto FOREIGN KEY (producto_id)
        REFERENCES productos(id) ON DELETE CASCADE ON UPDATE CASCADE
);


-- Pedidos
CREATE TABLE pedidos (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    fecha_pedido        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    proveedor_id        INT NOT NULL,
    estado              ENUM('pendiente','recibido','cancelado') NOT NULL DEFAULT 'pendiente',
    total               DECIMAL(14, 2) NOT NULL DEFAULT 0,
    CONSTRAINT fk_pedido_proveedor FOREIGN KEY (proveedor_id)
        REFERENCES proveedores(id) ON DELETE RESTRICT ON UPDATE CASCADE
);


-- Detalles de pedido
CREATE TABLE detalle_pedidos (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id           INT NOT NULL,
    producto_id         INT NOT NULL,
    cantidad            INT NOT NULL CHECK (cantidad > 0),
    precio_unitario     DECIMAL(12, 2) NOT NULL CHECK (precio_unitario >= 0),
    CONSTRAINT fk_det_pedido FOREIGN KEY (pedido_id)
        REFERENCES pedidos(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_det_producto FOREIGN KEY (producto_id)
        REFERENCES productos(id) ON DELETE RESTRICT ON UPDATE CASCADE
);