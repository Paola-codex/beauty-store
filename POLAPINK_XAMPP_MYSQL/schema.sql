CREATE DATABASE IF NOT EXISTS polapink CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE polapink;
CREATE TABLE IF NOT EXISTS clientes (
 id_cliente INT AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(100) NOT NULL,
 correo VARCHAR(120) NOT NULL UNIQUE,
 telefono VARCHAR(20)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS productos (
 id_producto INT AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(100) NOT NULL,
 categoria VARCHAR(60) NOT NULL,
 precio DECIMAL(12,2) NOT NULL,
 stock INT NOT NULL DEFAULT 0,
 imagen VARCHAR(1000),
 CONSTRAINT chk_precio CHECK (precio >= 0),
 CONSTRAINT chk_stock CHECK (stock >= 0)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS pedidos (
 id_pedido INT AUTO_INCREMENT PRIMARY KEY,
 cliente_id INT NOT NULL,
 producto_id INT NOT NULL,
 cantidad INT NOT NULL,
 total DECIMAL(12,2) NOT NULL,
 fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 estado ENUM('Pendiente','Enviado','Entregado','Cancelado') NOT NULL DEFAULT 'Pendiente',
 FOREIGN KEY (cliente_id) REFERENCES clientes(id_cliente),
 FOREIGN KEY (producto_id) REFERENCES productos(id_producto),
 CONSTRAINT chk_cantidad CHECK (cantidad > 0),
 CONSTRAINT chk_total CHECK (total >= 0)
) ENGINE=InnoDB;
INSERT INTO productos(nombre,categoria,precio,stock,imagen)
SELECT 'Brillo Labial Rimocoo','Labios',25000,20,'imagenes/brillo-labial.png'
WHERE NOT EXISTS(SELECT 1 FROM productos WHERE nombre='Brillo Labial Rimocoo');
INSERT INTO productos(nombre,categoria,precio,stock,imagen)
SELECT 'Polvo Compacto Karité','Rostro',42000,15,'imagenes/polvo-compacto.png'
WHERE NOT EXISTS(SELECT 1 FROM productos WHERE nombre='Polvo Compacto Karité');
INSERT INTO productos(nombre,categoria,precio,stock,imagen)
SELECT 'Brillo de Labios Rosa','Labios',29000,18,'imagenes/brillo-rosa.png'
WHERE NOT EXISTS(SELECT 1 FROM productos WHERE nombre='Brillo de Labios Rosa');
INSERT INTO productos(nombre,categoria,precio,stock,imagen)
SELECT 'Lip Gloss Cute','Labios',32000,25,'imagenes/lip-gloss.png'
WHERE NOT EXISTS(SELECT 1 FROM productos WHERE nombre='Lip Gloss Cute');
