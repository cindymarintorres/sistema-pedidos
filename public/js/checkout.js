/**
 * Checkout — Renderiza el carrito y procesa la confirmación del pedido.
 */

const UMBRAL_DESCUENTO = 100.00;
const TASA_DESCUENTO   = 0.10;
const TASA_IVA         = 0.12;

/**
 * Escapa texto para prevenir XSS al insertar en innerHTML.
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function renderCart() {
    const cart = CartApp.get();
    if (cart.length === 0) {
        document.getElementById('empty-state').classList.remove('hidden');
        document.getElementById('checkout-content').classList.add('hidden');
        return;
    }

    document.getElementById('empty-state').classList.add('hidden');
    document.getElementById('checkout-content').classList.remove('hidden');

    const tbody = document.getElementById('cart-items');
    tbody.innerHTML = '';

    let subtotal = 0;

    cart.forEach((item) => {
        const itemSub = item.precio * item.cantidad;
        subtotal += itemSub;

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${escapeHtml(item.nombre)} <br><span class="text-xs text-gray-500">${escapeHtml(item.sku)}</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">$${parseFloat(item.precio).toFixed(2)}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                <div class="flex items-center justify-center gap-2">
                    <button onclick="updateQty(${item.id}, -1)" class="p-1 text-gray-500 hover:text-indigo-600 focus:outline-none"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg></button>
                    <span class="w-8 text-center font-medium">${item.cantidad}</span>
                    <button onclick="updateQty(${item.id}, 1)" ${item.cantidad >= item.maxStock ? 'disabled class="p-1 text-gray-300"' : 'class="p-1 text-gray-500 hover:text-indigo-600 focus:outline-none"'}><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg></button>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">$${itemSub.toFixed(2)}</td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button onclick="CartApp.remove(${item.id}); renderCart()" class="text-red-600 hover:text-red-900 transition-colors">Eliminar</button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    const descuento = subtotal > UMBRAL_DESCUENTO ? (subtotal * TASA_DESCUENTO) : 0;
    const base = subtotal - descuento;
    const iva = base * TASA_IVA;
    const total = base + iva;

    document.getElementById('res-subtotal').innerText = '$' + subtotal.toFixed(2);
    document.getElementById('res-descuento').innerText = '-$' + descuento.toFixed(2);
    document.getElementById('res-iva').innerText = '$' + iva.toFixed(2);
    document.getElementById('res-total').innerText = '$' + total.toFixed(2);
}

function updateQty(id, delta) {
    let cart = CartApp.get();
    const item = cart.find(p => p.id === id);
    if (item) {
        item.cantidad += delta;
        if (item.cantidad <= 0) {
            cart = cart.filter(p => p.id !== id);
        }
        if (item.cantidad > item.maxStock) {
            item.cantidad = item.maxStock;
            CartApp.showToast('No hay suficiente stock');
        }
        localStorage.setItem('cart', JSON.stringify(cart));
        CartApp.updateCount();
        renderCart();
    }
}

async function submitPedido() {
    const btn = document.getElementById('btn-submit');
    const errDiv = document.getElementById('error-message');
    errDiv.classList.add('hidden');
    btn.disabled = true;
    btn.innerText = 'Procesando...';

    const cart = CartApp.get();
    const items = cart.map(c => ({ producto_id: c.id, cantidad: c.cantidad }));

    try {
        const response = await fetch('/api/pedidos', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ items })
        });

        const data = await response.json();

        if (!response.ok) {
            let msg = data.message || 'Error al procesar pedido';
            if (data.errors && data.errors.items) {
                msg = data.errors.items[0];
            }
            errDiv.innerText = msg;
            errDiv.classList.remove('hidden');
            btn.disabled = false;
            btn.innerText = 'Confirmar Pedido';
        } else {
            CartApp.clear();
            window.location.href = '/pedidos/' + data.data.id + '?success=1';
        }
    } catch (err) {
        errDiv.innerText = 'Error de conexión';
        errDiv.classList.remove('hidden');
        btn.disabled = false;
        btn.innerText = 'Confirmar Pedido';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    renderCart();
});
