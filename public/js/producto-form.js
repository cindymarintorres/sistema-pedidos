/**
 * Producto Form — Modal CRUD para productos.
 * Maneja abrir/cerrar el modal, enviar datos a la API,
 * y mostrar errores de validación.
 */

function openProductModal(product = null) {
    document.querySelectorAll('[id^="err-"]').forEach(el => el.classList.add('hidden'));
    document.getElementById('form-global-error').classList.add('hidden');

    if (product) {
        document.getElementById('modal-title').innerText = 'Editar Producto';
        document.getElementById('form-id').value = product.id;
        document.getElementById('form-sku').value = product.sku;
        document.getElementById('form-nombre').value = product.nombre;
        document.getElementById('form-precio').value = parseFloat(product.precio).toFixed(2);
        document.getElementById('form-stock').value = product.stock;
    } else {
        document.getElementById('modal-title').innerText = 'Nuevo Producto';
        document.getElementById('form-id').value = '';
        document.getElementById('product-form').reset();
    }
    document.getElementById('product-modal').classList.remove('hidden');
}

function closeProductModal() {
    document.getElementById('product-modal').classList.add('hidden');
}

async function submitProductForm(e) {
    e.preventDefault();
    const btn = document.getElementById('form-submit-btn');
    btn.disabled = true;
    btn.innerText = 'Guardando...';

    document.querySelectorAll('[id^="err-"]').forEach(el => el.classList.add('hidden'));
    const globalErr = document.getElementById('form-global-error');
    globalErr.classList.add('hidden');

    const id = document.getElementById('form-id').value;
    const payload = {
        sku: document.getElementById('form-sku').value,
        nombre: document.getElementById('form-nombre').value,
        precio: parseFloat(document.getElementById('form-precio').value),
        stock: parseInt(document.getElementById('form-stock').value, 10)
    };

    const url = id ? `/api/productos/${id}` : '/api/productos';
    const method = id ? 'PUT' : 'POST';

    try {
        const res = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (!res.ok) {
            if (data.errors) {
                for (const [key, msgs] of Object.entries(data.errors)) {
                    const errEl = document.getElementById(`err-${key}`);
                    if (errEl) {
                        errEl.innerText = msgs[0];
                        errEl.classList.remove('hidden');
                    }
                }
            } else if (data.message) {
                globalErr.innerText = data.message;
                globalErr.classList.remove('hidden');
            }
        } else {
            closeProductModal();
            window.location.reload();
        }
    } catch (err) {
        globalErr.innerText = 'Error de conexión';
        globalErr.classList.remove('hidden');
    } finally {
        btn.disabled = false;
        btn.innerText = 'Guardar';
    }
}

async function deleteProduct(id) {
    if (!confirm('¿Estás seguro de que deseas eliminar este producto?')) return;

    try {
        const res = await fetch(`/api/productos/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        const data = await res.json();

        if (!res.ok) {
            alert(data.message || 'Error al eliminar');
        } else {
            window.location.reload();
        }
    } catch (err) {
        alert('Error de conexión');
    }
}
