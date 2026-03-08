<!-- Product Modal Boilerplate -->
<div id="product-modal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeProductModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="product-form" onsubmit="submitProductForm(event)">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                        Crear/Editar Producto
                    </h3>
                    <div class="mt-4 space-y-4">
                        <input type="hidden" id="form-id" name="id">
                        
                        <div>
                            <label for="form-sku" class="block text-sm font-medium text-gray-700">SKU</label>
                            <input type="text" id="form-sku" name="sku" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <p class="text-xs text-red-500 hidden mt-1" id="err-sku"></p>
                        </div>
                        
                        <div>
                            <label for="form-nombre" class="block text-sm font-medium text-gray-700">Nombre</label>
                            <input type="text" id="form-nombre" name="nombre" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <p class="text-xs text-red-500 hidden mt-1" id="err-nombre"></p>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="form-precio" class="block text-sm font-medium text-gray-700">Precio</label>
                                <input type="number" step="0.01" id="form-precio" name="precio" required min="0" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <p class="text-xs text-red-500 hidden mt-1" id="err-precio"></p>
                            </div>
                            
                            <div>
                                <label for="form-stock" class="block text-sm font-medium text-gray-700">Stock</label>
                                <input type="number" id="form-stock" name="stock" required min="0" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <p class="text-xs text-red-500 hidden mt-1" id="err-stock"></p>
                            </div>
                        </div>
                    </div>
                    <div id="form-global-error" class="mt-4 hidden p-2 bg-red-50 text-red-700 text-sm rounded"></div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" id="form-submit-btn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Guardar
                    </button>
                    <button type="button" onclick="closeProductModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="/js/producto-form.js"></script>
@endpush
