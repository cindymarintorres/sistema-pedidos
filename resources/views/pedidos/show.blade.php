@if(!$pedido)
<div class="text-center py-20">
    <h1 class="text-3xl font-bold text-gray-900">Pedido no encontrado</h1>
    <a href="/pedidos" class="mt-4 inline-block text-indigo-600 hover:text-indigo-800">Volver a mis pedidos</a>
</div>
@else
<div class="max-w-4xl mx-auto">
    <div class="mb-4">
        <a href="/pedidos" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 inline-flex items-center">
            <svg class="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Volver a pedidos
        </a>
    </div>
    
    <div class="bg-white shadow overflow-hidden sm:rounded-lg border border-gray-200">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900">Detalle del Pedido #{{ str_pad($pedido['id'], 6, '0', STR_PAD_LEFT) }}</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Realizado el {{ date('d/m/Y H:i', strtotime($pedido['created_at'])) }}</p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                Pagado
            </span>
        </div>
        
        <div class="border-t border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Cant</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Precio U.</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($pedido['items'] ?? [] as $item)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $item['producto_nombre'] }}<br>
                            <span class="text-xs text-gray-500">SKU: {{ $item['producto_sku'] ?? 'N/A' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">{{ $item['cantidad'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">${{ number_format($item['precio_unitario'], 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">${{ number_format($item['subtotal_item'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50">
                        <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-500">Subtotal:</td>
                        <td class="px-6 py-3 text-right text-sm font-semibold text-gray-900">${{ number_format($pedido['subtotal'], 2) }}</td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-500">Descuento:</td>
                        <td class="px-6 py-3 text-right text-sm font-semibold text-red-600">-${{ number_format($pedido['descuento'], 2) }}</td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-500">IVA (12%):</td>
                        <td class="px-6 py-3 text-right text-sm font-semibold text-gray-900">${{ number_format($pedido['iva'], 2) }}</td>
                    </tr>
                    <tr class="bg-indigo-50">
                        <td colspan="3" class="px-6 py-4 text-right text-base font-bold text-gray-900">Total:</td>
                        <td class="px-6 py-4 text-right text-base font-bold text-indigo-700">${{ number_format($pedido['total'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endif
