<div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-900 tracking-tight mb-8">Checkout</h1>
    
    <div id="empty-state" class="hidden text-center py-16 bg-white border border-gray-200 rounded-xl">
        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        <h3 class="mt-2 text-lg font-medium text-gray-900">Tu carrito está vacío</h3>
        <p class="mt-1 text-sm text-gray-500">Agrega productos para procesar un pedido.</p>
        <div class="mt-6">
            <a href="/productos" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">Ver Productos</a>
        </div>
    </div>

    <div id="checkout-content" class="hidden">
        <div class="bg-white shadow overflow-hidden sm:rounded-lg border border-gray-200 mb-8">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Resumen de Compra</h3>
            </div>
            <div class="border-t border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Precio</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Cant</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                            <th scope="col" class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody id="cart-items" class="bg-white divide-y divide-gray-200">
                        <!-- Items rendered by JS -->
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50">
                            <td colspan="3" class="px-6 py-2 text-right text-sm font-medium text-gray-500">Subtotal:</td>
                            <td id="res-subtotal" class="px-6 py-2 text-right text-sm font-medium text-gray-900">$0.00</td>
                            <td></td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td colspan="3" class="px-6 py-2 text-right text-sm font-medium text-gray-500 pl-4">Descuento (10% si > $100):</td>
                            <td id="res-descuento" class="px-6 py-2 text-right text-sm font-medium text-red-600">-$0.00</td>
                            <td></td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td colspan="3" class="px-6 py-2 text-right text-sm font-medium text-gray-500">IVA (12%):</td>
                            <td id="res-iva" class="px-6 py-2 text-right text-sm font-medium text-gray-900">$0.00</td>
                            <td></td>
                        </tr>
                        <tr class="bg-indigo-50">
                            <td colspan="3" class="px-6 py-4 text-right text-base font-bold text-gray-900">Total:</td>
                            <td id="res-total" class="px-6 py-4 text-right text-base font-bold text-indigo-700">$0.00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="flex justify-end mt-4">
            <button id="btn-submit" onclick="submitPedido()" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                Confirmar Pedido
            </button>
        </div>
        
        <div id="error-message" class="mt-4 hidden p-4 bg-red-50 border-l-4 border-red-500 text-red-700"></div>
    </div>
</div>

@push('scripts')
<script src="/js/checkout.js"></script>
@endpush
