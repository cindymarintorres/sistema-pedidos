<?php

namespace App\Repositories;

use App\Repositories\Traits\CallsStoredProcedures;
use Illuminate\Support\Facades\DB;

class ProductoRepository
{
    use CallsStoredProcedures;

    public function listar(string $search = '', string $sort = 'id', int $page = 1, int $limit = 10): array
    {
        $pdo = DB::connection()->getPdo();
        
        // Al NO tener variables OUT que necesitemos leer *después* de procesar resultsets en este caso particular (MySQL no permite extraer OUT si hay un ResultSet activo),
        // y como este SP devuelve un dataset además del OUT param, la mejor manera en PDO sin emulación es 
        // bindValues. Pero Laravel y PDO pueden ser truculentos con OUT params y result sets al mismo tiempo.
        // Dado que solo queremos listar y necesitamos el total, en Laravel/PDO crudo:
        
        $stmt = $pdo->prepare("CALL sp_listar_productos(?, ?, ?, ?, @total_count)");
        $stmt->execute([$search, $sort, $page, $limit]);
        
        $productos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt->closeCursor(); // Necesario para liberar la conexión y poder leer el OUT param
        
        $totalResult = $pdo->query("SELECT @total_count AS total")->fetch(\PDO::FETCH_ASSOC);
        $total = (int) ($totalResult['total'] ?? 0);

        return [
            'data' => $productos,
            'total' => $total,
        ];
    }

    public function obtenerPorId(int $id): ?array
    {
        $productos = DB::select('CALL sp_obtener_producto(?)', [$id]);
        if (empty($productos)) {
            return null;
        }
        
        return (array) $productos[0];
    }

    public function crear(array $data): array
    {
        $result = $this->callWithOut('sp_crear_producto', [
            $data['sku'],
            $data['nombre'],
            $data['precio'],
            $data['stock']
        ], ['@nuevo_id', '@error']);
        
        return $result;
    }

    public function actualizar(int $id, array $data): array
    {
        $result = $this->callWithOut('sp_actualizar_producto', [
            $id,
            $data['sku'],
            $data['nombre'],
            $data['precio'],
            $data['stock']
        ], ['@filas', '@error']);
        
        return $result;
    }

    public function eliminar(int $id): array
    {
        $result = $this->callWithOut('sp_eliminar_producto', [
            $id
        ], ['@filas', '@error']);
        
        return $result;
    }
}
