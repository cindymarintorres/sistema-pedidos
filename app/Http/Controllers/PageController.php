<?php

namespace App\Http\Controllers;

use App\Services\PedidoService;
use App\Services\ProductoService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(
        private readonly ProductoService $productoService,
        private readonly PedidoService $pedidoService
    ) {}

    public function productos(Request $request): View
    {
        $search = $request->query('search', '');
        $page   = (int) $request->query('page', 1);
        $limit  = 9;

        $resultado  = $this->productoService->listar($search, 'created_at', $page, $limit);
        $total      = $resultado['total'];
        $totalPages = (int) ceil($total / $limit);

        return view('app', [
            'page'       => 'productos',
            'productos'  => $resultado['data'],
            'total'      => $total,
            'totalPages' => $totalPages,
            'search'     => $search,
            'currentPage' => $page,
            'limit'      => $limit,
        ]);
    }

    public function pedidos(Request $request): View
    {
        $desde    = $request->query('desde');
        $hasta    = $request->query('hasta');
        $minTotal = $request->query('min_total');

        $pedidos = $this->pedidoService->listar(
            $desde,
            $hasta,
            $minTotal !== null ? (float) $minTotal : null
        );

        return view('app', [
            'page'    => 'pedidos',
            'pedidos' => $pedidos,
            'desde'   => $desde,
            'hasta'   => $hasta,
        ]);
    }

    public function pedidoDetalle(int $id): View
    {
        $pedido = $this->pedidoService->obtenerPorId($id);

        return view('app', [
            'page'   => 'pedidos',
            'action' => $id,
            'pedido' => $pedido,
        ]);
    }

    public function checkout(): View
    {
        return view('app', [
            'page' => 'checkout',
        ]);
    }
}
