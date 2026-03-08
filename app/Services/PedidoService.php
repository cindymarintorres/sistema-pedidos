<?php

namespace App\Services;

use App\Repositories\PedidoRepository;
use RuntimeException;

class PedidoService
{
    public function __construct(
        private readonly PedidoRepository $pedidoRepository
    ) {}

    public function listar(?string $desde = null, ?string $hasta = null, ?float $minTotal = null): array
    {
        return $this->pedidoRepository->listar($desde, $hasta, $minTotal);
    }

    public function obtenerPorId(int $id): ?array
    {
        return $this->pedidoRepository->obtenerPorId($id);
    }

    public function crear(array $items): array
    {
        // $items viene validado del FormRequest como [['producto_id' => 1, 'cantidad' => 2], ...]
        $resultado = $this->pedidoRepository->crear($items);

        if (!empty($resultado['error'])) {
            throw new RuntimeException($resultado['error']);
        }

        return $this->pedidoRepository->obtenerPorId((int) $resultado['pedido_id']);
    }
}
