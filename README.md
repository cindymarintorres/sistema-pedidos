# Sistema de Pedidos - Arquitectura Orientada a Datos

Aplicación web de gestión de ventas construyendo una arquitectura monolítica clara en Laravel 12 con operaciones de base de datos estrictamente encapsuladas en Stored Procedures.

## 1. ¿Cómo levanto esto con Docker? (máximo 3 comandos)

```bash
git clone https://github.com/cindymarintorres/sistema-pedidos.git
cd sistema-pedidos
docker-compose build --no-cache && docker-compose up -d
```

_(El esquema BD y los seeds se ejecutan automáticamente en el build del contenedor db)_.

## 2. ¿Cómo accedo a la app?

- **Frontend Blade:** Abre [http://localhost:8080](http://localhost:8080) en tu navegador web.
- **REST API:** Usa la base `http://localhost:8080/api` (ej: `/api/productos`).

## 3. ¿Cómo corro los tests?

```bash
docker compose exec app php artisan test
```

## 4. Endpoints Disponibles

| Método | Endpoint              | Descripción                                       |
| ------ | --------------------- | ------------------------------------------------- |
| GET    | `/api/productos`      | Lista productos paginados y con búsqueda          |
| POST   | `/api/productos`      | Crea un nuevo producto (SKU único)                |
| GET    | `/api/productos/{id}` | Obtiene un producto por ID                        |
| PUT    | `/api/productos/{id}` | Actualiza información y stock de un producto      |
| DELETE | `/api/productos/{id}` | Elimina un producto (si no tiene pedidos)         |
| GET    | `/api/pedidos`        | Lista el historial y resumen de todos los pedidos |
| POST   | `/api/pedidos`        | Crea un nuevo pedido a partir de items            |
| GET    | `/api/pedidos/{id}`   | Detalle completo de un pedido con sus items       |
| GET    | `/api/health`         | Ping para verificar el estado de la API           |

## 5. Seguridad: Permisos del usuario MySQL

Tal y como indica el principio de mínima exposición para este diseño de arquitectura, la aplicación **NO** necesita realizar sentencias de Data Manipulation Language (DML) ni de Data Query Language (DQL) directamente sobre las tablas.

Por lo tanto, el usuario transaccional `pedidos_user` de MySQL **SOLAMENTE** necesita el permiso `EXECUTE`:

```sql
GRANT EXECUTE ON pedidos_db.* TO 'pedidos_user'@'%';
-- Se aseguran de REVOKE SELECT, INSERT, UPDATE, DELETE privileges
```

## 6. Administración de la Base de Datos (Root)

Dado que la aplicación web se conecta y está restringida a usar el usuario transaccional `pedidos_user` (que solo tiene permisos `EXECUTE` para los Stored Procedures), **para ver las tablas, hacer SELECTs manuales, o crear nuevas tablas / SPs, debes conectarte como usuario `root`.**

### Credenciales de Root:

- **Host:** `127.0.0.1` o `localhost` (Puerto: `3306`)
- **Usuario:** `root`
- **Contraseña:** `rootsecret`
- **Base de Datos:** `pedidos_db`

### Acceso interactivo por CLI de Docker:

```bash
docker compose exec db mysql -u root -prootsecret pedidos_db
```

## 7. Pruebas de API (Postman Collection)

Para facilitar la interacción y prueba de todos los endpoints disponibles, el proyecto incluye una colección completa de Postman:

- **Archivo:** `sistema_pedidos_postman_collection.json` (ubicado en la raíz del proyecto).
- **Contenido:** 14 peticiones preconfiguradas con cabeceras correctas (`Accept: application/json`), cuerpos de prueba para simular éxitos y errores (404, 422), y variables de entorno (`{{baseUrl}}`).
- **Uso:**
    1. Abre Postman y haz clic en **Import**.
    2. Selecciona el archivo `sistema_pedidos_postman_collection.json`.
    3. La colección ya trae la variable `baseUrl` configurada por defecto a `http://localhost:8080`.

## 8. Comportamiento del Carrito (Checkout)

El carrito de compras guarda su estado en el `localStorage` del navegador. Esto significa:

- El carrito **persiste** aunque recargues la página o reinicies Docker.
- El carrito **no se comparte** entre navegadores ni entre usuarios.
- El carrito **sí se limpia** al confirmar un pedido exitosamente.

Si necesitas vaciar el carrito manualmente durante pruebas, abre las herramientas
del navegador y ejecuta esto en la consola:

```javascript
localStorage.clear();
location.reload();
```

O abre la aplicación en una ventana de incógnito para empezar con el carrito vacío.
