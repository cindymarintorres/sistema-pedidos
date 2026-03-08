<?php

namespace App\Repositories;

use App\Repositories\Traits\CallsStoredProcedures;
use Illuminate\Support\Facades\DB;

class PedidoRepository
{
    use CallsStoredProcedures;

    public function crear(array $items): array
    {
        $jsonItems = json_encode($items);

        $result = $this->callWithOut('sp_crear_pedido', [
            $jsonItems
        ], ['@pedido_id', '@error']);

        return $result;
    }

    public function listar(?string $desde = null, ?string $hasta = null, ?float $minTotal = null): array
    {
        $pedidos = DB::select('CALL sp_listar_pedidos(?, ?, ?)', [
            $desde,
            $hasta,
            $minTotal
        ]);

        return array_map(function ($pedido) {
            $pedido = (array) $pedido;
            if (isset($pedido['items']) && is_string($pedido['items'])) {
                $pedido['items'] = json_decode($pedido['items'], true);
            }
            return $pedido;
        }, $pedidos);
    }

    public function obtenerPorId(int $id): ?array
    {
        $pedidos = DB::select('CALL sp_obtener_pedido(?)', [$id]);
        
        if (empty($pedidos)) {
            return null;
        }
        
        $pedido = (array) $pedidos[0];
        
        if (isset($pedido['items']) && is_string($pedido['items'])) {
            $pedido['items'] = json_decode($pedido['items'], true);
        }
        
        return $pedido;
    }
}
