DELIMITER //

-- --------------------------------------------------------------------------
-- Procedure: sp_listar_productos
-- Describe: Lista productos con búsqueda, ordenación estricta y paginación.
-- In:
--   p_search (VARCHAR): Término de búsqueda (SKU o nombre)
--   p_sort (VARCHAR): Campo por el que ordenar ('id', 'precio', 'nombre', 'stock', 'created_at')
--   p_page (INT): Número de página (1-based)
--   p_limit (INT): Límite de resultados por página
-- Out:
--   p_total (INT): Total de registros sin paginar para poder rellenar meta de paginación.
-- --------------------------------------------------------------------------
CREATE PROCEDURE sp_listar_productos(
    IN p_search VARCHAR(150),
    IN p_sort VARCHAR(50),
    IN p_page INT,
    IN p_limit INT,
    OUT p_total INT
)
BEGIN
    DECLARE v_offset INT;
    SET v_offset = (p_page - 1) * p_limit;

    -- Calcular el total de registros filtrados
    SELECT COUNT(*) INTO p_total
    FROM productos
    WHERE p_search IS NULL OR p_search = '' 
       OR sku LIKE CONCAT('%', p_search, '%') 
       OR nombre LIKE CONCAT('%', p_search, '%');

    -- Retornar los registros
    SELECT id, sku, nombre, precio, stock, created_at, updated_at
    FROM productos
    WHERE p_search IS NULL OR p_search = '' 
       OR sku LIKE CONCAT('%', p_search, '%') 
       OR nombre LIKE CONCAT('%', p_search, '%')
    ORDER BY 
        CASE WHEN p_sort = 'id' THEN id END DESC,
        CASE WHEN p_sort = 'precio' THEN precio END ASC,
        CASE WHEN p_sort = 'precio_desc' THEN precio END DESC,
        CASE WHEN p_sort = 'nombre' THEN nombre END ASC,
        CASE WHEN p_sort = 'stock' THEN stock END ASC,
        CASE WHEN p_sort = 'created_at' THEN created_at END DESC,
        -- fallback seguro
        id DESC
    LIMIT p_limit OFFSET v_offset;
END //


-- --------------------------------------------------------------------------
-- Procedure: sp_obtener_producto
-- Describe: Obtiene un producto por su ID.
-- In:
--   p_id (INT): ID del producto
-- Retorna el dataset del producto.
-- --------------------------------------------------------------------------
CREATE PROCEDURE sp_obtener_producto(IN p_id INT)
BEGIN
    SELECT id, sku, nombre, precio, stock, created_at, updated_at
    FROM productos
    WHERE id = p_id;
END //


-- --------------------------------------------------------------------------
-- Procedure: sp_crear_producto
-- Describe: Crea un nuevo producto validando el SKU único.
-- In:
--   p_sku, p_nombre, p_precio, p_stock
-- Out:
--   p_nuevo_id (INT): El ID del producto creado (NULL si falla).
--   p_error (VARCHAR): Mensaje de error, si ocurre (NULL si es éxito).
-- --------------------------------------------------------------------------
CREATE PROCEDURE sp_crear_producto(
    IN p_sku VARCHAR(50), 
    IN p_nombre VARCHAR(150), 
    IN p_precio DECIMAL(10,2), 
    IN p_stock INT,
    OUT p_nuevo_id INT,
    OUT p_error VARCHAR(255)
)
BEGIN
    DECLARE v_exists INT;
    SET p_error = NULL;
    SET p_nuevo_id = NULL;

    SELECT COUNT(*) INTO v_exists FROM productos WHERE sku = p_sku;
    
    IF v_exists > 0 THEN
        SET p_error = 'El SKU proporcionado ya existe.';
    ELSE
        INSERT INTO productos (sku, nombre, precio, stock)
        VALUES (p_sku, p_nombre, p_precio, p_stock);
        
        SET p_nuevo_id = LAST_INSERT_ID();
    END IF;
END //


-- --------------------------------------------------------------------------
-- Procedure: sp_actualizar_producto
-- Describe: Actualiza un producto existente.
-- --------------------------------------------------------------------------
CREATE PROCEDURE sp_actualizar_producto(
    IN p_id INT,
    IN p_sku VARCHAR(50),
    IN p_nombre VARCHAR(150),
    IN p_precio DECIMAL(10,2),
    IN p_stock INT,
    OUT p_filas INT,
    OUT p_error VARCHAR(255)
)
BEGIN
    DECLARE v_exists INT;
    SET p_error = NULL;
    SET p_filas = 0;

    -- Validar si el SKU pertenece a OTRO producto
    SELECT COUNT(*) INTO v_exists FROM productos WHERE sku = p_sku AND id != p_id;
    
    IF v_exists > 0 THEN
        SET p_error = 'El SKU proporcionado ya está siendo usado por otro producto.';
    ELSE
        UPDATE productos 
        SET sku = p_sku, nombre = p_nombre, precio = p_precio, stock = p_stock
        WHERE id = p_id;
        
        SET p_filas = ROW_COUNT();
        IF p_filas = 0 THEN
            -- No hubo error, pero o no existe o no se modificó nada.
            -- Validamos si existe.
            SELECT COUNT(*) INTO v_exists FROM productos WHERE id = p_id;
            IF v_exists = 0 THEN
                SET p_error = 'Producto no encontrado.';
            END IF;
        END IF;
    END IF;
END //


-- --------------------------------------------------------------------------
-- Procedure: sp_eliminar_producto
-- Describe: Elimina un producto.
-- --------------------------------------------------------------------------
CREATE PROCEDURE sp_eliminar_producto(
    IN p_id INT,
    OUT p_filas INT,
    OUT p_error VARCHAR(255)
)
BEGIN
    DECLARE v_has_pedidos INT;
    SET p_error = NULL;
    SET p_filas = 0;

    -- Validar que no tenga pedidos
    SELECT COUNT(*) INTO v_has_pedidos FROM pedido_items WHERE producto_id = p_id;

    IF v_has_pedidos > 0 THEN
        SET p_error = 'No se puede eliminar el producto porque tiene pedidos asociados.';
    ELSE
        DELETE FROM productos WHERE id = p_id;
        SET p_filas = ROW_COUNT();
        IF p_filas = 0 THEN
            SET p_error = 'Producto no encontrado.';
        END IF;
    END IF;
END //


-- --------------------------------------------------------------------------
-- Procedure: sp_crear_pedido
-- Describe: Crea un pedido a partir de un JSON estructurado. Valida stock y 
--           calcula totales/impuestos usando la lógica definida.
-- In:
--   p_items_json (JSON): [{"producto_id": 1, "cantidad": 2}, ...]
-- Out:
--   p_pedido_id (INT): ID del pedido creado (NULL si falla)
--   p_error (VARCHAR): Mensaje de error (NULL si éxito)
-- --------------------------------------------------------------------------
CREATE PROCEDURE sp_crear_pedido(
    IN p_items_json JSON,
    OUT p_pedido_id INT,
    OUT p_error VARCHAR(255)
)
sp_main:BEGIN
    DECLARE v_idx INT DEFAULT 0;
    DECLARE v_len INT;
    DECLARE v_producto_id INT;
    DECLARE v_cantidad INT;
    DECLARE v_precio DECIMAL(10,2);
    DECLARE v_stock INT;
    DECLARE v_nombre VARCHAR(150);
    
    DECLARE v_subtotal DECIMAL(10,2) DEFAULT 0.00;
    DECLARE v_descuento DECIMAL(10,2) DEFAULT 0.00;
    DECLARE v_iva DECIMAL(10,2) DEFAULT 0.00;
    DECLARE v_total DECIMAL(10,2) DEFAULT 0.00;
    DECLARE v_subtotal_item DECIMAL(10,2) DEFAULT 0.00;

    -- Exit handler for exceptions
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_error = 'Error interno en base de datos al crear pedido. Rollback ejecutado.';
    END;

    SET p_error = NULL;
    SET p_pedido_id = NULL;

    -- Validar JSON
    IF p_items_json IS NULL OR JSON_LENGTH(p_items_json) = 0 THEN
        SET p_error = 'El listado de items está vacío.';
        LEAVE sp_main;
    END IF;

    SET v_len = JSON_LENGTH(p_items_json);

    START TRANSACTION;

    -- Primero pasamos calculando todo y bloqueando items (FOR UPDATE)
    WHILE v_idx < v_len DO
        SET v_producto_id = JSON_UNQUOTE(JSON_EXTRACT(p_items_json, CONCAT('$[', v_idx, '].producto_id')));
        SET v_cantidad = JSON_UNQUOTE(JSON_EXTRACT(p_items_json, CONCAT('$[', v_idx, '].cantidad')));
        
        IF v_cantidad <= 0 THEN
            SET p_error = 'La cantidad debe ser mayor a 0.';
            ROLLBACK;
            LEAVE sp_main;
        END IF;

        -- FOR UPDATE es innegociable para lockear la fila y evitar race conditions en stock
        SELECT precio, stock, nombre INTO v_precio, v_stock, v_nombre 
        FROM productos 
        WHERE id = v_producto_id 
        FOR UPDATE;

        IF v_precio IS NULL THEN
            SET p_error = CONCAT('El producto ID ', v_producto_id, ' no existe.');
            ROLLBACK;
            LEAVE sp_main;
        END IF;

        IF v_stock < v_cantidad THEN
            SET p_error = CONCAT('Stock insuficiente para ', v_nombre, '. Disponible: ', v_stock, ', solicitado: ', v_cantidad);
            ROLLBACK;
            LEAVE sp_main;
        END IF;

        SET v_subtotal_item = v_precio * v_cantidad;
        SET v_subtotal = v_subtotal + v_subtotal_item;
        
        SET v_idx = v_idx + 1;
    END WHILE;

    -- Descuento 10% si subtotal > 100
    IF v_subtotal > 100.00 THEN
        SET v_descuento = ROUND(v_subtotal * 0.10, 2);
    END IF;

    -- IVA = 12% sobre (subtotal - descuento)
    SET v_iva = ROUND((v_subtotal - v_descuento) * 0.12, 2);
    
    -- Total Final
    SET v_total = v_subtotal - v_descuento + v_iva;

    -- 10. INSERT en pedidos
    INSERT INTO pedidos (subtotal, descuento, iva, total)
    VALUES (v_subtotal, v_descuento, v_iva, v_total);
    
    SET p_pedido_id = LAST_INSERT_ID();

    -- 11. y 12. Segundo ciclo: INSERT en pedido_items y UPDATE stock
    SET v_idx = 0;
    WHILE v_idx < v_len DO
        SET v_producto_id = JSON_UNQUOTE(JSON_EXTRACT(p_items_json, CONCAT('$[', v_idx, '].producto_id')));
        SET v_cantidad = JSON_UNQUOTE(JSON_EXTRACT(p_items_json, CONCAT('$[', v_idx, '].cantidad')));

        SELECT precio INTO v_precio FROM productos WHERE id = v_producto_id;
        SET v_subtotal_item = v_precio * v_cantidad;

        INSERT INTO pedido_items (pedido_id, producto_id, cantidad, precio_unitario, subtotal_item)
        VALUES (p_pedido_id, v_producto_id, v_cantidad, v_precio, v_subtotal_item);

        UPDATE productos 
        SET stock = stock - v_cantidad 
        WHERE id = v_producto_id;

        SET v_idx = v_idx + 1;
    END WHILE;

    COMMIT;
END //


-- --------------------------------------------------------------------------
-- Procedure: sp_listar_pedidos
-- Describe: Lista todos los pedidos con sus items en una sola consulta estructurada,
--           previniendo el problema N+1.
-- In:
--   p_desde (DATE): Fecha de inicio opcional
--   p_hasta (DATE): Fecha de fin opcional
--   p_min_total (DECIMAL): Mínimo del total opcional
-- --------------------------------------------------------------------------
CREATE PROCEDURE sp_listar_pedidos(
    IN p_desde DATE,
    IN p_hasta DATE,
    IN p_min_total DECIMAL(10,2)
)
BEGIN
    SELECT 
        p.id, 
        p.subtotal, 
        p.descuento, 
        p.iva, 
        p.total, 
        p.created_at,
        JSON_ARRAYAGG(
            JSON_OBJECT(
                'item_id', pi.id,
                'producto_id', pi.producto_id,
                'producto_nombre', pr.nombre,
                'cantidad', pi.cantidad,
                'precio_unitario', pi.precio_unitario,
                'subtotal_item', pi.subtotal_item
            )
        ) AS items
    FROM pedidos p
    LEFT JOIN pedido_items pi ON p.id = pi.pedido_id
    LEFT JOIN productos pr ON pi.producto_id = pr.id
    WHERE (p_desde IS NULL OR DATE(p.created_at) >= p_desde)
      AND (p_hasta IS NULL OR DATE(p.created_at) <= p_hasta)
      AND (p_min_total IS NULL OR p.total >= p_min_total)
    GROUP BY p.id
    ORDER BY p.created_at DESC;
END //


-- --------------------------------------------------------------------------
-- Procedure: sp_obtener_pedido
-- Describe: Obtiene el detalle completo de un pedido, con items como JSON_ARRAY.
-- --------------------------------------------------------------------------
CREATE PROCEDURE sp_obtener_pedido(IN p_id INT)
BEGIN
    SELECT 
        p.id, 
        p.subtotal, 
        p.descuento, 
        p.iva, 
        p.total, 
        p.created_at,
        JSON_ARRAYAGG(
            JSON_OBJECT(
                'item_id', pi.id,
                'producto_id', pi.producto_id,
                'producto_nombre', pr.nombre,
                'producto_sku', pr.sku,
                'cantidad', pi.cantidad,
                'precio_unitario', pi.precio_unitario,
                'subtotal_item', pi.subtotal_item
            )
        ) AS items
    FROM pedidos p
    LEFT JOIN pedido_items pi ON p.id = pi.pedido_id
    LEFT JOIN productos pr ON pi.producto_id = pr.id
    WHERE p.id = p_id
    GROUP BY p.id;
END //


DELIMITER ;
