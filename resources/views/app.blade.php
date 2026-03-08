<!DOCTYPE html>
<html lang="es" class="bg-gray-50 text-gray-900 antialiased h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sistema de Pedidos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-full flex flex-col">
    <nav class="bg-indigo-600 shadow text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-6">
                    <a href="/" class="font-bold text-xl tracking-wide flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        PedidosApp
                    </a>
                    <div class="hidden md:block">
                        <div class="ml-10 flex items-baseline space-x-4">
                            <a href="/productos" class="{{ request()->is('productos') || request()->is('/') ? 'bg-indigo-700' : 'hover:bg-indigo-500' }} px-3 py-2 rounded-md text-sm font-medium transition-colors">Productos</a>
                            <a href="/pedidos" class="{{ request()->is('pedidos*') ? 'bg-indigo-700' : 'hover:bg-indigo-500' }} px-3 py-2 rounded-md text-sm font-medium transition-colors">Mis Pedidos</a>
                        </div>
                    </div>
                </div>
                <div>
                    <a href="/checkout" class="relative hover:text-indigo-200 transition-colors flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        <span id="cart-count" class="bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center absolute -top-2 -right-3 hidden">0</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <main class="flex-grow max-w-7xl mx-auto w-full px-4 py-8">
        @include('components.flash-message')

        @if($page === 'productos')
            @include('productos.index')
        @elseif($page === 'pedidos')
            @if(isset($action))
                @include('pedidos.show')
            @else
                @include('pedidos.index')
            @endif
        @elseif($page === 'checkout')
            @include('checkout.index')
        @else
            <div class="text-center py-20">
                <h1 class="text-4xl font-bold text-gray-800">404</h1>
                <p class="text-gray-500 mt-2">Página no encontrada</p>
                <a href="/" class="mt-6 inline-block bg-indigo-600 text-white px-6 py-2 rounded-md font-medium hover:bg-indigo-700 transition">Volver al inicio</a>
            </div>
        @endif
    </main>

    <script src="/js/cart.js"></script>
    @stack('scripts')
</body>
</html>
