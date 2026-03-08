<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use App\Services\ProductoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class ProductoController extends Controller
{
    public function __construct(
        private readonly ProductoService $productoService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search', '');
        $sort   = $request->query('sort', 'id');
        $page   = (int) $request->query('page', 1);
        $limit  = (int) $request->query('limit', 10);

        try {
            $resultado = $this->productoService->listar($search, $sort, $page, $limit);
            return response()->json([
                'success' => true,
                'data' => $resultado['data'],
                'meta' => [
                    'total' => $resultado['total'],
                    'page'  => $page,
                    'limit' => $limit
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al listar productos'], 500);
        }
    }

    public function store(StoreProductoRequest $request): JsonResponse
    {
        try {
            $producto = $this->productoService->crear($request->validated());
            return response()->json([
                'success' => true,
                'data' => $producto,
                'message' => 'Producto creado exitosamente'
            ], 201);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 409);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error interno del servidor'], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        $producto = $this->productoService->obtenerPorId($id);

        if (!$producto) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $producto
        ]);
    }

    public function update(UpdateProductoRequest $request, int $id): JsonResponse
    {
        try {
            $producto = $this->productoService->actualizar($id, $request->validated());
            return response()->json([
                'success' => true,
                'data' => $producto,
                'message' => 'Producto actualizado exitosamente'
            ]);
        } catch (RuntimeException $e) {
            $code = str_contains($e->getMessage(), 'encontrado') ? 404 : 409;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $code);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error interno del servidor'], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->productoService->eliminar($id);
            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado exitosamente'
            ]);
        } catch (RuntimeException $e) {
            $code = str_contains($e->getMessage(), 'encontrado') ? 404 : 409;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $code);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error interno del servidor'], 500);
        }
    }
}
