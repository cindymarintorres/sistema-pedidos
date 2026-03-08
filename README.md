# Sistema de Pedidos - Arquitectura Orientada a Datos

Aplicación web de gestión de ventas construyendo una arquitectura monolítica clara en Laravel 12 con operaciones de base de datos estrictamente encapsuladas en Stored Procedures.

## 1. ¿Cómo levanto esto con Docker? (máximo 3 comandos)

```bash
docker compose up -d
docker compose exec app composer install
```

*(El esquema BD y los seeds se ejecutan automáticamente en el build del contenedor db)*.

## 2. ¿Cómo accedo a la app?

- **Frontend Blade:** Abre [http://localhost:8080](http://localhost:8080) en tu navegador web.
- **REST API:** Usa la base `http://localhost:8080/api` (ej: `/api/productos`).

## 3. ¿Cómo corro los tests?

```bash
docker compose exec app php artisan test
```

## 4. Endpoints Disponibles

| Método | Endpoint                    | Descripción                                      |
|--------|-----------------------------|--------------------------------------------------|
| GET  | `/api/productos`            | Lista productos paginados y con búsqueda      |
| POST   | `/api/productos`            | Crea un nuevo producto (SKU único)               |
| GET  | `/api/productos/{id}`       | Obtiene un producto por ID                       |
| PUT    | `/api/productos/{id}`       | Actualiza información y stock de un producto   |
| DELETE | `/api/productos/{id}`       | Elimina un producto (si no tiene pedidos)      |
| GET  | `/api/pedidos`              | Lista el historial y resumen de todos los pedidos|
| POST   | `/api/pedidos`              | Crea un nuevo pedido a partir de items         |
| GET  | `/api/pedidos/{id}`         | Detalle completo de un pedido con sus items    |
| GET  | `/api/health`               | Ping para verificar el estado de la API          |

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
