<?php

namespace App\Services;

use App\Repositories\ProductoRepository;
use RuntimeException;

class ProductoService
{
    public function __construct(
        private readonly ProductoRepository $productoRepository
    ) {}

    public function listar(string $search = '', string $sort = 'id', int $page = 1, int $limit = 10): array
    {
        return $this->productoRepository->listar($search, $sort, $page, $limit);
    }

    public function obtenerPorId(int $id): ?array
    {
        return $this->productoRepository->obtenerPorId($id);
    }

    public function crear(array $data): array
    {
        $result = $this->productoRepository->crear($data);

        if (!empty($result['error'])) {
            throw new RuntimeException($result['error']);
        }

        return $this->productoRepository->obtenerPorId((int) $result['nuevo_id']);
    }

    public function actualizar(int $id, array $data): array
    {
        $result = $this->productoRepository->actualizar($id, $data);

        if (!empty($result['error'])) {
            throw new RuntimeException($result['error']);
        }

        return $this->productoRepository->obtenerPorId($id);
    }

    public function eliminar(int $id): bool
    {
        $result = $this->productoRepository->eliminar($id);

        if (!empty($result['error'])) {
            throw new RuntimeException($result['error']);
        }

        return true;
    }
}
