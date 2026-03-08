@php
    $page = $currentPage;
@endphp

<div>
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Productos</h1>
            <p class="text-sm text-gray-500 mt-1">Explora y agrega productos a tu pedido.</p>
        </div>

        <!-- Search Form -->
        <form method="GET" action="/" class="flex flex-col md:flex-row w-full md:max-w-lg">
            <div class="flex flex-row mb-3 md:mb-0">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por nombre o SKU..."
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-l-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-colors">
                </div>
                <button type="submit"
                    class="-ml-px relative inline-flex items-center space-x-2 px-4 py-2 border border-gray-300 text-sm font-medium rounded-r-md text-gray-700 bg-gray-50 hover:bg-gray-100 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                    Buscar
                </button>
            </div>

            @if($search)
                <a href="/"
                    class="ml-2 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 transition-colors">
                    Limpiar
                </a>
            @endif
            <button type="button" onclick="openProductModal()"
                class="md:ml-4 px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                Nuevo Producto
            </button>
        </form>
    </div>

    <!-- Products Grid -->
    @if(count($productos) > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($productos as $p)
                <div
                    class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden flex flex-col">
                    <div class="p-5 flex-grow">
                        <div class="flex justify-between items-start">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                SKU: {{ $p['sku'] }}
                            </span>
                            @if($p['stock'] > 10)
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Stock:
                                    {{ $p['stock'] }}</span>
                            @elseif($p['stock'] > 0)
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Quedan:
                                    {{ $p['stock'] }}</span>
                            @else
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Agotado</span>
                            @endif
                        </div>

                        <h3
                            class="mt-4 text-lg font-semibold text-gray-900 leading-tight group-hover:text-indigo-600 transition-colors">
                            {{ $p['nombre'] }}
                        </h3>
                        <div class="mt-4 flex items-baseline text-2xl font-bold text-gray-900">
                            ${{ number_format($p['precio'], 2) }}
                        </div>
                    </div>

                    <div class="bg-gray-50 px-5 py-3 border-t border-gray-100">
                        <button @if($p['stock'] <= 0) disabled @endif
                            onclick="CartApp.add({{ json_encode(['id' => $p['id'], 'nombre' => $p['nombre'], 'precio' => $p['precio'], 'sku' => $p['sku'], 'maxStock' => $p['stock']]) }})"
                            class="w-full relative inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white shadow-sm @if($p['stock'] > 0) bg-indigo-600 hover:bg-indigo-700 hover:-translate-y-0.5 transform transition-all active:scale-95 @else bg-gray-400 cursor-not-allowed @endif">
                            <svg class="mr-2 -ml-1 text-white w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                            Agregar al Carrito
                        </button>
                        <div class="mt-3 flex justify-between space-x-2 text-xs">
                            <button onclick="openProductModal({{ json_encode($p) }})"
                                class="text-indigo-600 hover:text-indigo-800 font-medium w-1/2 text-center py-1">Editar</button>
                            <button onclick="deleteProduct({{ $p['id'] }})"
                                class="text-red-600 hover:text-red-800 font-medium w-1/2 text-center py-1">Eliminar</button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($totalPages > 1)
            <div class="mt-10 border-t border-gray-200 pt-6">
                <nav class="flex items-center justify-between">
                    <div class="w-0 flex-1 flex">
                        @if($page > 1)
                            <a href="/?search={{ $search }}&page={{ $page - 1 }}"
                                class="inline-flex items-center pt-4 pr-1 text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">
                                <svg class="mr-3 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                                Anterior
                            </a>
                        @endif
                    </div>
                    <div class="hidden md:-mt-px md:flex text-sm text-gray-500">
                        Página {{ $page }} de {{ $totalPages }}
                    </div>
                    <div class="w-0 flex-1 flex justify-end">
                        @if($page < $totalPages)
                            <a href="/?search={{ $search }}&page={{ $page + 1 }}"
                                class="inline-flex items-center pt-4 pl-1 text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">
                                Siguiente
                                <svg class="ml-3 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </a>
                        @endif
                    </div>
                </nav>
            </div>
        @endif
    @else
        <div class="bg-white border rounded-xl border-dashed border-gray-300 w-full py-16 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                </path>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-600">No se encontraron productos</h3>
            <p class="mt-1 text-gray-400 text-sm">Intenta con otro término de búsqueda.</p>
        </div>
    @endif

    @include('productos._form')
</div>