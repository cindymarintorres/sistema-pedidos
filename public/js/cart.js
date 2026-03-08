/**
 * CartApp — Carrito de compras con localStorage.
 * Gestiona el estado del carrito en el navegador sin tocar el servidor
 * hasta que el usuario confirma el pedido.
 */
const CartApp = {
    init() {
        if (!localStorage.getItem('cart')) {
            localStorage.setItem('cart', JSON.stringify([]));
        }
        this.updateCount();
    },

    get() {
        return JSON.parse(localStorage.getItem('cart'));
    },

    add(product) {
        const cart = this.get();
        const existing = cart.find(p => p.id === product.id);
        if (existing) {
            existing.cantidad++;
        } else {
            cart.push({ ...product, cantidad: 1 });
        }
        localStorage.setItem('cart', JSON.stringify(cart));
        this.updateCount();
        this.showToast('Producto agregado al carrito');
    },

    remove(id) {
        let cart = this.get();
        cart = cart.filter(p => p.id !== id);
        localStorage.setItem('cart', JSON.stringify(cart));
        this.updateCount();
    },

    clear() {
        localStorage.setItem('cart', JSON.stringify([]));
        this.updateCount();
    },

    updateCount() {
        const cart = this.get();
        const btn = document.getElementById('cart-count');
        if (cart.length > 0) {
            btn.innerText = cart.reduce((acc, p) => acc + p.cantidad, 0);
            btn.classList.remove('hidden');
        } else {
            btn.classList.add('hidden');
        }
    },

    showToast(msg) {
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-4 right-4 bg-green-600 text-white px-4 py-2 rounded shadow-lg transform transition-all duration-300';
        toast.textContent = msg;
        document.body.appendChild(toast);
        setTimeout(() => { toast.classList.add('opacity-0'); setTimeout(() => toast.remove(), 300); }, 2000);
    }
};

CartApp.init();
