// ===== BURGER MENU =====
const burger = document.getElementById('burger');
const navMain = document.querySelector('.nav-main');
if (burger && navMain) {
    burger.addEventListener('click', () => {
        navMain.classList.toggle('open');
        burger.classList.toggle('open');
    });
}

// ===== TOAST NOTIFICATIONS =====
function showToast(message, type = 'success') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }
    const icons = { success: '✓', error: '✕', info: 'ℹ' };
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `<span>${icons[type] || '✓'}</span>${message}`;
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.animation = 'toastOut 0.3s ease forwards';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ===== QUANTITY CONTROL =====
function initQtyControls() {
    document.querySelectorAll('.qty-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = btn.closest('.qty-input').querySelector('.qty-num');
            let val = parseInt(input.textContent);
            if (btn.dataset.action === 'inc') val = Math.min(val + 1, 99);
            if (btn.dataset.action === 'dec') val = Math.max(val - 1, 1);
            input.textContent = val;
            const hidden = document.getElementById('qty-hidden');
            if (hidden) hidden.value = val;
        });
    });
}

// ===== CONFIRM MODAL =====
function confirm(title, message, onConfirm) {
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay active';
    overlay.innerHTML = `
        <div class="modal">
            <h3>${title}</h3>
            <p>${message}</p>
            <div class="modal-actions">
                <button class="btn btn-ghost" id="modal-cancel">Отмена</button>
                <button class="btn btn-danger" id="modal-confirm">Подтвердить</button>
            </div>
        </div>`;
    document.body.appendChild(overlay);
    overlay.querySelector('#modal-cancel').addEventListener('click', () => overlay.remove());
    overlay.querySelector('#modal-confirm').addEventListener('click', () => { overlay.remove(); onConfirm(); });
    overlay.addEventListener('click', e => { if (e.target === overlay) overlay.remove(); });
}

// ===== CART AJAX =====
async function addToCart(caseId, qty, customDesign = '') {
    try {
        const fd = new FormData();
        fd.append('case_id', caseId);
        fd.append('qty', qty);
        fd.append('custom_design', customDesign);
        const res = await fetch((window.BASE_URL||'') + '/api/cart_add.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            showToast('Добавлено в корзину!');
            const badge = document.querySelector('.cart-badge');
            const btnCart = document.querySelector('.btn-cart');
            if (badge) badge.textContent = data.count;
            else if (btnCart) {
                const b = document.createElement('span');
                b.className = 'cart-badge'; b.textContent = data.count;
                btnCart.appendChild(b);
            }
        } else {
            showToast(data.error || 'Ошибка', 'error');
        }
    } catch (e) { showToast('Ошибка сети', 'error'); }
}

async function removeFromCart(caseId) {
    confirm('Удалить товар?', 'Товар будет удалён из корзины.', async () => {
        const fd = new FormData();
        fd.append('case_id', caseId);
        const res = await fetch((window.BASE_URL||'') + '/api/cart_remove.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) { location.reload(); }
        else showToast('Ошибка', 'error');
    });
}

// ===== FILTER DEBOUNCE =====
function debounce(fn, delay = 300) {
    let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), delay); };
}

const searchInput = document.getElementById('search-input');
if (searchInput) {
    searchInput.addEventListener('input', debounce(() => {
        document.getElementById('filter-form')?.submit();
    }));
}

// ===== PRODUCT IMAGE PREVIEW =====
const imageInput = document.getElementById('image-upload');
if (imageInput) {
    imageInput.addEventListener('change', function () {
        const preview = document.getElementById('image-preview');
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = e => { if (preview) { preview.src = e.target.result; preview.style.display = 'block'; } };
            reader.readAsDataURL(this.files[0]);
        }
    });
}

// ===== DELETE CONFIRM (admin tables) =====
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', e => {
        e.preventDefault();
        const href = btn.href || btn.dataset.href;
        confirm('Удалить запись?', 'Это действие необратимо.', () => { location.href = href; });
    });
});

// ===== INIT =====
initQtyControls();
