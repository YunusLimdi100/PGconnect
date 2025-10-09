console.log("✅ app.js loaded successfully");
const PGs = [
    { id: 'pg1', title: 'Sunny Stay PG - Single Room', city: 'Bengaluru', price: 6500, rating: 4.6, discount: 10, type: 'single', amenities: ['wifi', 'attached', 'security'], img: 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=800&q=60', femaleOnly: true, vegOnly: true },
    { id: 'pg2', title: 'Comfy Shared PG - 3 Sharing', city: 'Hyderabad', price: 3500, rating: 4.1, discount: 0, type: 'sharing', amenities: ['wifi', 'ac'], img: 'pgs/p5.jpeg', femaleOnly: false, vegOnly: true },
    { id: 'pg3', title: 'Elite Rooms PG - Near College', city: 'Delhi', price: 8800, rating: 4.8, discount: 15, type: 'single', amenities: ['wifi', 'attached', 'ac'], img: 'pgs/pg1.jpeg', femaleOnly: false, vegOnly: false },
    { id: 'pg4', title: 'Budget Stay - Sharing', city: 'Mumbai', price: 3000, rating: 3.9, discount: 5, type: 'sharing', amenities: ['wifi'], img: 'pgs/p7.jpeg', femaleOnly: false, vegOnly: true },
    { id: 'pg5', title: 'College Corner PG - AC Rooms', city: 'Kolkata', price: 7200, rating: 4.3, discount: 12, type: 'single', amenities: ['ac', 'attached', 'wifi'], img: 'pgs/p4.jpeg', femaleOnly: true, vegOnly: false },
    { id: 'pg6', title: 'City Hub PG - Economy', city: 'Bengaluru', price: 4200, rating: 4.0, discount: 8, type: 'sharing', amenities: ['wifi', 'security'], img: 'https://images.unsplash.com/photo-1554995207-c18c203602cb?auto=format&fit=crop&w=800&q=60', femaleOnly: false, vegOnly: false },
    { id: 'pg7', title: 'Green Leaf PG - Single', city: 'Pune', price: 5800, rating: 4.4, discount: 5, type: 'single', amenities: ['attached', 'security'], img: 'pgs/p3.jpeg', femaleOnly: true, vegOnly: true },
    { id: 'pg8', title: 'Metro Stay - Short Distance', city: 'Chennai', price: 4900, rating: 4.2, discount: 0, type: 'sharing', amenities: ['wifi', 'ac'], img: 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=800&q=60', femaleOnly: false, vegOnly: false },
    { id: 'pg9', title: 'Comfort Plus PG', city: 'Jaipur', price: 6300, rating: 4.5, discount: 10, type: 'single', amenities: ['ac', 'wifi', 'attached'], img: 'pgs/p2.jpeg', femaleOnly: false, vegOnly: true },
    { id: 'pg10', title: 'Neighborhood PG - Family Run', city: 'Lucknow', price: 5200, rating: 4.0, discount: 7, type: 'sharing', amenities: ['security', 'attached'], img: 'pgs/p6.jpeg', femaleOnly: false, vegOnly: false }
];

// State
let state = {
    query: '', sort: 'popular', type: 'all', amenity: 'any', femaleOnly: false, vegOnly: false, delivery: 'standard', coupon: null
};

// cart
const CART_KEY = 'pgconnects_cart_v1';

// helpers
const qs = (sel) => document.querySelector(sel);
const qsa = (sel) => Array.from(document.querySelectorAll(sel));

// coupon definitions
const VALID_COUPONS = { 'STUDENT10': { type: 'percent', value: 10 }, 'FLAT500': { type: 'flat', value: 500 } };
let appliedCoupon = null;

// initialize
(function init() {
    // theme
    const themeToggle = qs('#themeToggle');
    const savedTheme = localStorage.getItem('pg-theme');
    if (savedTheme === 'dark') document.documentElement.classList.add('dark');
    themeToggle.addEventListener('click', () => {
        document.documentElement.classList.toggle('dark');
        localStorage.setItem('pg-theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
    });

    // binds - search/sort/type/amenity
    qs('#searchInput').addEventListener('input', (e) => { state.query = e.target.value; renderListings(); });
    qs('#sortSelect').addEventListener('change', (e) => { state.sort = e.target.value; renderListings(); });
    qs('#typeFilter').addEventListener('change', (e) => { state.type = e.target.value; renderListings(); });
    qs('#amenityFilter').addEventListener('change', (e) => { state.amenity = e.target.value; syncQuickPills(); renderListings(); });

    // female/veg filters
    qs('#filterFemale').addEventListener('change', (e) => { state.femaleOnly = e.target.checked; renderListings(); });
    qs('#filterVeg').addEventListener('change', (e) => { state.vegOnly = e.target.checked; renderListings(); });

    // delivery option affects summary
    qs('#deliveryOption').addEventListener('change', (e) => { state.delivery = e.target.value; saveCart(); updateSummary(); });

    // quick filter pills (toggle)
    qsa('.pill').forEach(btn => btn.addEventListener('click', () => {
        const f = btn.dataset.filter;
        // toggle
        if (state.amenity === f) {
            state.amenity = 'any';
        } else {
            state.amenity = f;
        }
        syncAmenitySelect();
        syncQuickPills();
        renderListings();
    }));

    // coupon apply
    qs('#applyCoupon').addEventListener('click', applyCoupon);

    // cart controls
    qs('#cartBtn').addEventListener('click', toggleCart);
    qs('#closeCart').addEventListener('click', () => { toggleCart(false); });
    qs('#checkoutBtn').addEventListener('click', openCheckout);

    // checkout controls
    qs('#closeModal').addEventListener('click', closeCheckout);
    qs('#payNow').addEventListener('click', confirmPayment);
    qsa('.payment-card').forEach(pc => pc.addEventListener('click', () => { selectPayment(pc.dataset.method); }));

    // sign in mock

    // book now quick (adds first listing)
    qs('#bookNow').addEventListener('click', () => { if (PGs.length) addToCart(PGs[0].id); toggleCart(true); });

    // initial render and cart
    renderListings();
    renderCart();
})();

// keep quick pill visuals synced with amenity select
function syncQuickPills() {
    qsa('.pill').forEach(p => p.classList.toggle('active', p.dataset.filter === state.amenity));
}
function syncAmenitySelect() {
    const sel = qs('#amenityFilter');
    sel.value = state.amenity || 'any';
}

// render listings with all filters + sorting + search
function renderListings() {
    const root = qs('#listings'); root.innerHTML = '';
    const filtered = PGs.filter(p => {
        // search
        const q = state.query.trim().toLowerCase();
        if (q) {
            const hay = (p.title + ' ' + p.city + ' ' + p.amenities.join(' ')).toLowerCase();
            if (!hay.includes(q)) return false;
        }
        // type
        if (state.type !== 'all' && p.type !== state.type) return false;
        // amenity
        if (state.amenity !== 'any' && !p.amenities.includes(state.amenity)) return false;
        // femaleOnly
        if (state.femaleOnly && !p.femaleOnly) return false;
        // vegOnly
        if (state.vegOnly && !p.vegOnly) return false;
        return true;
    });

    // sort
    filtered.sort((a, b) => {
        switch (state.sort) {
            case 'price-asc': return a.price - b.price;
            case 'price-desc': return b.price - a.price;
            case 'rating': return b.rating - a.rating;
            case 'discount': return b.discount - a.discount;
            default: return b.rating - a.rating; // popular by rating
        }
    });

    qs('#resultsCount').textContent = filtered.length;

    filtered.forEach(p => {
        // compute effective price display (show discount if any; delivery and coupons are for cart/checkout)
        const discountLabel = p.discount ? `<div class='tag'>${p.discount}% off</div>` : '';
        const el = document.createElement('div'); el.className = 'card';
        el.innerHTML = `
          <img src="${p.img}" alt="${escapeHtml(p.title)}" />
          <div class="meta">
            <div style="display:flex; justify-content:space-between; align-items:center">
              <div style="font-weight:800">${escapeHtml(p.title)}</div>
              <div class="price">₹${p.price}</div>
            </div>
            <div class="muted">${p.city} · ${p.rating} ★ ${p.femaleOnly ? ' · Female-only' : ''}${p.vegOnly ? ' · Veg-only' : ''}</div>
            <div class="tags">${discountLabel}${p.amenities.map(a => `<div class='tag'>${a}</div>`).join('')}</div>
            <div style="margin-top:8px; display:flex; gap:8px">
              <button class="btn addBtn" data-id="${p.id}">Add</button>
              <button class="ghost" data-id="${p.id}" onclick="viewDetails('${p.id}')">View</button>
            </div>
          </div>
        `;
        root.appendChild(el);
    });

    // attach add handlers
    qsa('.addBtn').forEach(b => b.addEventListener('click', () => addToCart(b.dataset.id)));
    // sync pill visuals
    syncQuickPills();
}

function viewDetails(id) {
    const p = PGs.find(x => x.id === id);
    if (!p) return;
    alert(`${p.title}\n\nCity: ${p.city}\nPrice: ₹${p.price}\nRating: ${p.rating} ★\nDiscount: ${p.discount}%\nAmenities: ${p.amenities.join(', ')}\nFemale-only: ${p.femaleOnly ? 'Yes' : 'No'}\nVeg-only: ${p.vegOnly ? 'Yes' : 'No'}`);
}

// CART: localStorage-backed
function getCart() { try { return JSON.parse(localStorage.getItem(CART_KEY) || '[]'); } catch (e) { return []; } }
function saveCart(items) { localStorage.setItem(CART_KEY, JSON.stringify(items || getCart())); renderCart(); }

function addToCart(id) {
    const items = getCart();
    const p = PGs.find(x => x.id === id);
    if (!p) return;
    const exists = items.find(i => i.id === id);
    if (exists) { exists.qty++; } else { items.push({ id: p.id, title: p.title, price: p.price, qty: 1 }); }
    saveCart(items);
    toggleCart(true);
}

function removeFromCart(id) { let items = getCart(); items = items.filter(i => i.id !== id); saveCart(items); }

function changeQty(id, delta) { const items = getCart(); const it = items.find(i => i.id === id); if (!it) return; it.qty = Math.max(1, it.qty + delta); saveCart(items); }

function renderCart() {
    const items = getCart();
    qs('#cartItems').innerHTML = '';
    if (!items.length) qs('#cartItems').innerHTML = '<div class="muted">No items yet. Shortlist PGs to compare or book.</div>';
    items.forEach(it => {
        const row = document.createElement('div'); row.className = 'cart-item';
        row.innerHTML = `<div style="flex:1"><div style="font-weight:700">${escapeHtml(it.title)}</div><div class="muted">₹${it.price} x ${it.qty}</div></div><div style="display:flex; flex-direction:column; gap:6px"><button class="icon-btn dec" data-id="${it.id}">-</button><button class="icon-btn inc" data-id="${it.id}">+</button><button class="icon-btn rem" data-id="${it.id}">🗑</button></div>`;
        qs('#cartItems').appendChild(row);
    });

    qsa('.dec').forEach(b => b.addEventListener('click', () => changeQty(b.dataset.id, -1)));
    qsa('.inc').forEach(b => b.addEventListener('click', () => changeQty(b.dataset.id, +1)));
    qsa('.rem').forEach(b => b.addEventListener('click', () => removeFromCart(b.dataset.id)));

    // subtotal
    const subtotal = items.reduce((s, i) => s + i.price * i.qty, 0);
    qs('#subtotal').textContent = '₹' + subtotal;
    qs('#cartCount').textContent = items.length;

    updateSummary();
}

function toggleCart(force) {
    const panel = qs('#cartPanel');
    if (typeof force === 'boolean') {
        if (force) { panel.classList.remove('hide'); panel.setAttribute('aria-hidden', 'false'); } else { panel.classList.add('hide'); panel.setAttribute('aria-hidden', 'true'); }
    } else {
        panel.classList.toggle('hide');
        panel.setAttribute('aria-hidden', panel.classList.contains('hide') ? 'true' : 'false');
    }
}

// COUPONS
function applyCoupon() {
    const code = qs('#couponInput').value.trim().toUpperCase();
    if (!code) return showCouponMsg('Enter a code');
    if (VALID_COUPONS[code]) {
        appliedCoupon = { code, ...VALID_COUPONS[code] };
        qs('#couponMsg').textContent = `Applied ${code}`;
        qs('#couponMsg').style.color = '';
        updateSummary();
    } else {
        appliedCoupon = null;
        qs('#couponMsg').textContent = 'Invalid coupon';
        qs('#couponMsg').style.color = 'crimson';
        updateSummary();
    }
    // clear the message after 3s if valid
    setTimeout(() => { if (qs('#couponMsg')) qs('#couponMsg').textContent = (appliedCoupon ? `Applied ${appliedCoupon.code}` : ''); }, 3000);
}
function showCouponMsg(t) { qs('#couponMsg').textContent = t; setTimeout(() => qs('#couponMsg').textContent = '', 3000); }

// CHECKOUT
function openCheckout() { const items = getCart(); if (!items.length) { alert('Cart empty. Add some PGs first.'); return; } qs('#modalBackdrop').style.display = 'flex'; updateSummary(); }
function closeCheckout() { qs('#modalBackdrop').style.display = 'none'; }

let selectedPayment = 'upi';
function selectPayment(method) { selectedPayment = method; qsa('.payment-card').forEach(pc => pc.classList.toggle('selected', pc.dataset.method === method)); }

function updateSummary() {
    const items = getCart();
    const sub = items.reduce((s, i) => s + i.price * i.qty, 0);
    const delivery = (state.delivery === 'standard') ? 0 : (state.delivery === 'assisted' ? 499 : 999);
    let discount = 0;
    if (appliedCoupon) {
        if (appliedCoupon.type === 'percent') discount = Math.round(sub * (appliedCoupon.value / 100));
        else discount = appliedCoupon.value;
    }
    const total = Math.max(0, sub + delivery - discount);
    qs('#summaryList').innerHTML = items.map(i => `<div style="display:flex; justify-content:space-between"><div>${escapeHtml(i.title)} x ${i.qty}</div><div>₹${i.price * i.qty}</div></div>`).join('') || '<div class="muted">No items in order.</div>';
    qs('#summaryDelivery').textContent = '₹' + delivery;
    qs('#summaryDiscount').textContent = '-₹' + discount;
    qs('#summaryTotal').textContent = '₹' + total;
    qs('#summaryTotal').dataset.amount = total;
}

function confirmPayment() {
    const name = qs('#guestName').value.trim();
    const phone = qs('#guestPhone').value.trim();
    if (!name || !phone) { alert('Please add guest name and phone'); return; }
    const amount = Number(qs('#summaryTotal').dataset.amount || 0);
    // Mock flow -- in real app you integrate payment SDKs
    if (selectedPayment === 'cod') {
        alert(`Order confirmed for ${name}. Pay ₹${amount} on arrival (Cash on Delivery).`);
    } else {
        alert(`Simulating online payment via ${selectedPayment.toUpperCase()}. Amount: ₹${amount}. (This is a demo — no real payment processed.)`);
    }
    // clear cart and coupon
    saveCart([]);
    appliedCoupon = null; qs('#couponInput').value = ''; qs('#couponMsg').textContent = '';
    closeCheckout(); toggleCart(false);
}

// utilities
function escapeHtml(s) { return String(s).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c])); }

// Ensure amenity select and quick pills stay visually in sync on page load
(function syncOnLoad() {
    syncAmenitySelect();
    syncQuickPills();
})();