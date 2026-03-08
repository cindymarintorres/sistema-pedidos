# Notas Técnicas y Decisiones Arquitectónicas

### 1. ¿Por qué Stored Procedures y no Eloquent?

Un ORM como Eloquent abstrae el lenguaje de base de datos asumiendo que el negocio vive completamente en la capa aplicativa. Sin embargo, en arquitecturas donde se ha definido una restricción estricta de no tocar tablas de forma directa, los Stored Procedures (SPs) operan como una API interna de la base de datos.
Al consolidar la transacción, los bloqueos FOR UPDATE y el control de coherencia ACID dentro de un SP, el Request pasa por el Gateway (Repository) y ejecuta todo con un único viaje de red. Centralización, acoplamiento mínimo nivel tabla a nivel PHP y una fuerte portabilidad son sus características principales a esta escala.

### 2. Parámetros OUT en PDO y emulación

MySQL requiere dos llamadas para recolectar un parámetro OUT: el `CALL` que muta el valor en memoria y un subsiguiente `SELECT @variable`. Se implementó un Helper Repository que hace uso de `DB::connection()->getPdo()` directamente porque el driver Fluent y Eloquent dificulta la recolección de variables pasadas por referencia cuando la query devuelve múltiples resultados. La manipulación mediante variables temporales de sesión MySQL `@` es el estándar más portable utilizando prepare statements explícitos.

### 3. PDO::ATTR_EMULATE_PREPARES = false

Desactivar las preparaciones emuladas (en Laravel esto sucede por default dependiendo de versión/configuración en `config/database.php`) es fundamental por dos motivos:  
a) Previene que PHP interpole consultas en el cliente y delega la sustitución al motor de MySQL en binario.
b) Al consultar variables OUT o múltiples record sets consecutivamente (CALL y SELECT en PDO), la emulación activada produce severas desincronías de metadatos de "Out of Sync" en el fetchAll() debido a que PDO no limpia el packet buffer resultset adecuadamente.

### 4. Anti N+1 consultando Pedidos via SP

Evitar las consultas en loop a BD siempre es imperativo. En Eloquent tendríamos Eager Loading (`with('items')`), pero con DB Engine Puro, la forma de imitar eficientemente este comportamiento es utilizando agrupaciones relacionales estructuradas. 
En `sp_listar_pedidos`, se unió el cabecero de los pedidos con `LEFT JOIN` hacia sus items y `JSON_ARRAYAGG(...)`. Esto entrega las relaciones empaquetadas en un único String JSON por fila que es interpretado automáticamente por PHP como Array Associativo. Una sola tabla de resultados, performance garantizada sin importar cuántos registros existan.

### 5. FOR UPDATE en `sp_crear_pedido`

Para asegurar la coherencia de inventario concurrente, el cálculo del SP recorre en transacción las variables usando: 
`SELECT precio, stock FROM productos WHERE id = ? FOR UPDATE;`
Esto bloquea (Pessimistic Locking) de manera exclusiva el índice de cada tabla en ese momento, asegurando que si dos Requests ingresan paralelamente el mismo milisegundo intentando robarse el último stock, la Base de Datos ponga en cola al segundo Request de escritura impidiendo la condición de carrera y evitando un stock negativo inservible.

### 6. Blade + Tailwind vs Frameworks Reactivos (SPA)

Dada la restricción de crear valor funcional rápidamente sin un exceso de configuración "porque se vea bien", React/Vue exigirían dependencias complejas de Vite, compilación, Node y librerías de estados.
Una vista Blade renderiza casi inmediatamente. Tailwind en modo CDN funciona a la perfección para maquetas simples y la orquestación del carrito mediante 50 líneas de Javascript logran la reactividad requerida, ofreciendo la misma experiencia sin penalizar el Developer Experience (DX) inicial y sin overhead de setup.
