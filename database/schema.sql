-- productos
CREATE TABLE productos (
    id         INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    sku        VARCHAR(50)       NOT NULL,
    nombre     VARCHAR(150)      NOT NULL,
    precio     DECIMAL(10,2)     NOT NULL,
    stock      INT UNSIGNED      NOT NULL DEFAULT 0,
    created_at TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_sku (sku)
) ENGINE=InnoDB;

-- pedidos
CREATE TABLE pedidos (
    id         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    subtotal   DECIMAL(10,2) NOT NULL,
    descuento  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    iva        DECIMAL(10,2) NOT NULL,
    total      DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

-- pedido_items
CREATE TABLE pedido_items (
    id              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    pedido_id       INT UNSIGNED  NOT NULL,
    producto_id     INT UNSIGNED  NOT NULL,
    cantidad        INT UNSIGNED  NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal_item   DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (id),
    KEY idx_pedido   (pedido_id),
    KEY idx_producto (producto_id),
    CONSTRAINT fk_item_pedido   FOREIGN KEY (pedido_id)   REFERENCES pedidos(id)   ON DELETE CASCADE,
    CONSTRAINT fk_item_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Revoke write permissions from normal app user if necessary (will be handled by Docker MySQL initialization automatically)
