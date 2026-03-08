<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePedidoRequest;
use App\Services\PedidoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class PedidoController extends Controller
{
    public function __construct(
        private readonly PedidoService $pedidoService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');
        $minTotal = $request->query('min_total');

        if ($minTotal !== null) {
            $minTotal = (float) $minTotal;
        }

        try {
            $pedidos = $this->pedidoService->listar($desde, $hasta, $minTotal);
            return response()->json([
                'success' => true,
                'data' => $pedidos
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al listar pedidos'], 500);
        }
    }

    public function store(StorePedidoRequest $request): JsonResponse
    {
        try {
            $pedido = $this->pedidoService->crear($request->validated('items'));
            return response()->json([
                'success' => true,
                'data' => $pedido,
                'message' => 'Pedido creado exitosamente'
            ], 201);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error interno del servidor'], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        $pedido = $this->pedidoService->obtenerPorId($id);

        if (!$pedido) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $pedido
        ]);
    }
}
