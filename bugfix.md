# Ejercicio de Bugfix & Mitigaciones

## Sección 1 — SQL Injection

### El problema: Fragmento PHP Hipotético Vulnerable
Un desarrollador inexperto podría intentar construir una consulta dinámica para el ordenamiento y filtrado interpolando variables directamente en la cadena SQL:

```php
// CÓDIGO VULNERABLE - NUNCA USAR
$search = $_GET['search']; // Ej: "1' OR '1'='1"
$sortColumn = $_GET['sort']; // Ej: "precio; DROP TABLE productos;"

$sql = "SELECT * FROM productos WHERE nombre LIKE '%$search%' ORDER BY $sortColumn";
$resultados = DB::select($sql);
```
En este escenario, si un usuario malintencionado inyecta parámetros como `; DROP TABLE productos;`, el motor de base de datos ejecutará múltiples sentencias arruinando la información.

### La Solución Implementada: Prepared Statements y Whitelisting
En nuestra arquitectura garantizamos la sanidad de dos maneras:
1. **PDO con Prepare Statements:** Utilizando los Stored Procedures con parámetros bind (`?`), MySQL recibe los datos separados de la instrucción. Es imposible que intente compilar y ejecutar el String del usuario.
2. **Whitelist para `ORDER BY` en el SP:** Dado que los Prepared Statements no pueden bindear nombres de columnas, en el SP `sp_listar_productos` utilizamos una lista blanca con `CASE WHEN`:
```sql
ORDER BY 
    CASE WHEN p_sort = 'id' THEN id END DESC,
    CASE WHEN p_sort = 'precio' THEN precio END ASC,
    CASE WHEN p_sort = 'nombre' THEN nombre END ASC,
    -- Fallback si meten algo raro, se va por el primary key
    id DESC
```
Si el usuario envía `sort=DROP TABLE`, no aplicará a ningún case y MySQL ordenará por ID con total seguridad.

---

## Sección 2 — El Problema de N+1 (Consultas en Bucle)

### El problema: Código Naive (Query en Bucle)
Cuando listamos un historial de pedidos y luego consultamos los items uno por uno en la capa aplicativa.

```php
// CÓDIGO INEFICIENTE - N+1 (Si hay 50 pedidos, se ejecutan 51 queries)
$pedidos = DB::select("SELECT * FROM pedidos");

foreach ($pedidos as $pedido) {
    // Por CADA pedido, hacemos OTRA query
    $items = DB::select("SELECT * FROM pedido_items WHERE pedido_id = ?", [$pedido->id]);
    $pedido->items = $items;
}
return $pedidos;
```
A medida que la base de datos crece, el tiempo de respuesta aumenta drásticamente debido a la latencia de red repetitiva.

### La Solución Implementada: Joins y Agrupación JSON (JSON_ARRAYAGG)
En vez de pedir a la base de datos registros individuales, delegamos en MySQL la tarea de agrupar todo el grafo relacional en un solo llamado.
En `sp_listar_pedidos` realizamos un `LEFT JOIN` y anidamos los items en un único campo JSON usando `JSON_ARRAYAGG`:

```sql
SELECT 
    p.id, p.subtotal, p.descuento, p.iva, p.total, p.created_at,
    JSON_ARRAYAGG(
        JSON_OBJECT(
            'producto_id', pi.producto_id,
            'cantidad', pi.cantidad,
            'precio_unitario', pi.precio_unitario
        )
    ) AS items
FROM pedidos p
LEFT JOIN pedido_items pi ON p.id = pi.pedido_id
GROUP BY p.id;
```
Con una (1) sola query desde PHP se traen de regreso todos los pedidos completos con su detalle empaquetado y listo para invocar `$pedido['items'] = json_decode(...)`.
