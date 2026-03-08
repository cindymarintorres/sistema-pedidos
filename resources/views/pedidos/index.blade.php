<div>
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Historial de Pedidos</h1>
            <p class="text-sm text-gray-500 mt-1">Revisa el detalle de todas tus compras.</p>
        </div>
        <!-- Filters -->
        <form method="GET" action="/pedidos" class="flex flex-wrap gap-3">
            <div>
                <input type="date" name="desde" value="{{ $desde }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
            </div>
            <div>
                <input type="date" name="hasta" value="{{ $hasta }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
            </div>
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">Filtrar</button>
            @if($desde || $hasta)
            <a href="/pedidos" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Limpiar</a>
            @endif
        </form>
    </div>

    @if(count($pedidos) > 0)
    <div class="bg-white shadow overflow-hidden sm:rounded-md border border-gray-200">
        <ul class="divide-y divide-gray-200">
            @foreach($pedidos as $pedido)
            <li>
                <a href="/pedidos/{{ $pedido['id'] }}" class="block hover:bg-gray-50 transition duration-150 ease-in-out">
                    <div class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between pointer">
                            <div class="text-sm font-semibold text-indigo-600 truncate">
                                Pedido #{{ str_pad($pedido['id'], 6, '0', STR_PAD_LEFT) }}
                            </div>
                            <div class="ml-2 flex-shrink-0 flex">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    ${{ number_format($pedido['total'], 2) }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-2 sm:flex sm:justify-between">
                            <div class="sm:flex text-sm text-gray-500 gap-4">
                                <p class="flex items-center text-sm text-gray-500">
                                    <svg class="flex-shrink-0 mr-1.5 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                    {{ count($pedido['items'] ?? []) }} artículos
                                </p>
                            </div>
                            <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                <svg class="flex-shrink-0 mr-1.5 h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                  <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                </svg>
                                <p>Ingresado: <time datetime="{{ $pedido['created_at'] }}">{{ date('d M Y H:i', strtotime($pedido['created_at'])) }}</time></p>
                            </div>
                        </div>
                    </div>
                </a>
            </li>
            @endforeach
        </ul>
    </div>
    @else
        <div class="text-center py-12 bg-white rounded-xl border border-gray-200 shadow-sm text-gray-500">
            No tienes pedidos.
        </div>
    @endif
</div>
