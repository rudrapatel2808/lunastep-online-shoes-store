// ================================================
// LunaStep — Main Script
// Cart System, Product Rendering, Navigation, Utilities
// Now connected to PHP backend with graceful fallback
// ================================================

// ---------------- Scroll Effects ----------------
window.addEventListener('scroll', () => {
  const topbar = document.getElementById('topbar');
  if (topbar) {
    topbar.classList.toggle('scrolled', window.scrollY > 20);
  }
});

// ---------------- Reveal on Scroll ----------------
const io = new IntersectionObserver(entries => {
  entries.forEach(en => { if (en.isIntersecting) en.target.classList.add('in'); });
}, { threshold: 0.1 });
document.querySelectorAll('.reveal').forEach(el => io.observe(el));

// ================================================
// CUSTOM MOUSE TRACKER — trailing glow cursor
// (skips touch/coarse-pointer devices automatically)
// ================================================
(function initMouseTracker() {
  const isCoarsePointer = window.matchMedia('(hover: none), (pointer: coarse)').matches;
  if (isCoarsePointer) return;

  const dot = document.createElement('div');
  dot.className = 'cursor-dot';
  const ring = document.createElement('div');
  ring.className = 'cursor-ring';
  document.body.appendChild(dot);
  document.body.appendChild(ring);

  let mouseX = window.innerWidth / 2, mouseY = window.innerHeight / 2;
  let ringX = mouseX, ringY = mouseY;
  let started = false;

  window.addEventListener('mousemove', (e) => {
    mouseX = e.clientX;
    mouseY = e.clientY;
    dot.style.transform = `translate(${mouseX}px, ${mouseY}px) translate(-50%,-50%)`;
    if (!started) { started = true; dot.style.opacity = '1'; ring.style.opacity = '1'; }
  });

  document.addEventListener('mouseleave', () => {
    dot.style.opacity = '0';
    ring.style.opacity = '0';
  });
  document.addEventListener('mouseenter', () => {
    if (started) { dot.style.opacity = '1'; ring.style.opacity = '1'; }
  });

  // Smoothly trail the ring behind the dot for a "tracking" feel
  function animateRing() {
    ringX += (mouseX - ringX) * 0.15;
    ringY += (mouseY - ringY) * 0.15;
    ring.style.transform = `translate(${ringX}px, ${ringY}px) translate(-50%,-50%)`;
    requestAnimationFrame(animateRing);
  }
  animateRing();

  // Grow the ring when hovering interactive / shoe elements
  const hoverSelector = 'a, button, input, select, textarea, .pcard, .cat-circle, ' +
    '.icon-btn, .filter-btn, .size-btn, .color-dot, .hamburger, .stat-card, .value-card, .team-card';
  document.addEventListener('mouseover', (e) => {
    if (e.target.closest(hoverSelector)) ring.classList.add('hover');
  });
  document.addEventListener('mouseout', (e) => {
    if (e.target.closest(hoverSelector)) ring.classList.remove('hover');
  });
  window.addEventListener('mousedown', () => ring.classList.add('click'));
  window.addEventListener('mouseup', () => ring.classList.remove('click'));
})();

// ================================================
// MOUSE-DRIVEN 3D TILT — on shoe cards & category circles
// Uses event delegation so it works on cards rendered later
// ================================================
(function initTilt() {
  const isCoarsePointer = window.matchMedia('(hover: none), (pointer: coarse)').matches;
  if (isCoarsePointer) return;

  const tiltSelector = '.pcard, .cat-circle, .product-image-box';
  let activeEl = null;

  document.addEventListener('mousemove', (e) => {
    const el = e.target.closest(tiltSelector);

    if (activeEl && activeEl !== el) {
      activeEl.style.transition = 'transform .5s cubic-bezier(.2,.9,.3,1)';
      activeEl.style.transform = '';
      activeEl = null;
    }
    if (!el) return;

    activeEl = el;
    const rect = el.getBoundingClientRect();
    const px = (e.clientX - rect.left) / rect.width - 0.5;
    const py = (e.clientY - rect.top) / rect.height - 0.5;
    const rotateY = px * 8;
    const rotateX = -py * 8;
    el.style.transition = 'transform .08s linear';
    el.style.transform = `perspective(900px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-6px)`;
  });

  document.addEventListener('mouseleave', () => {
    if (activeEl) {
      activeEl.style.transition = 'transform .5s cubic-bezier(.2,.9,.3,1)';
      activeEl.style.transform = '';
      activeEl = null;
    }
  }, true);
})();

// ---------------- Mobile Navigation ----------------
const hamburgerBtn = document.getElementById('hamburgerBtn');
const mobileNav = document.getElementById('mobileNav');
const mobileOverlay = document.getElementById('mobileOverlay');

if (hamburgerBtn) {
  hamburgerBtn.addEventListener('click', () => {
    hamburgerBtn.classList.toggle('open');
    mobileNav.classList.toggle('open');
    mobileOverlay.classList.toggle('show');
  });
}
if (mobileOverlay) {
  mobileOverlay.addEventListener('click', () => {
    hamburgerBtn.classList.remove('open');
    mobileNav.classList.remove('open');
    mobileOverlay.classList.remove('show');
  });
}

// ---------------- Currency System ----------------
const exchangeRates = {
  USD: { rate: 1, symbol: '$' },
  EUR: { rate: 0.92, symbol: '€' },
  GBP: { rate: 0.79, symbol: '£' },
  INR: { rate: 83.5, symbol: '₹' }
};
let currentCurrency = localStorage.getItem('currency') || 'USD';

function formatPrice(priceUSD) {
  const { rate, symbol } = exchangeRates[currentCurrency];
  const converted = priceUSD * rate;
  return symbol + (converted % 1 === 0 ? converted : converted.toFixed(2));
}

const curSel = document.getElementById('currencySelector');
if (curSel) {
  curSel.value = currentCurrency;
  curSel.addEventListener('change', (e) => {
    currentCurrency = e.target.value;
    localStorage.setItem('currency', currentCurrency);
    renderProducts();
    renderFBT();
    renderOrders();
    renderShopProducts && renderShopProducts();
    renderCartPage && renderCartPage();
    renderCheckoutSummary && renderCheckoutSummary();
    renderProductDetail && renderProductDetail();
  });
}

// ---------------- Toast Notifications ----------------
function showToast(message, type = '') {
  const container = document.getElementById('toastContainer');
  if (!container) return;
  const toast = document.createElement('div');
  toast.className = 'toast ' + type;
  toast.innerHTML = `<span>${type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ'}</span> ${escapeHtml(message)}`;
  container.appendChild(toast);
  setTimeout(() => { toast.style.opacity = '0'; toast.style.transform = 'translateY(10px)'; }, 2500);
  setTimeout(() => toast.remove(), 3000);
}

// ---------------- HTML escaping (XSS defense for user-generated content) ----------------
function escapeHtml(value) {
  if (value === null || value === undefined) return '';
  return String(value).replace(/[&<>"']/g, ch => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
  })[ch]);
}

// ================================================
// PRODUCT DATA — Fallback + Backend Loading
// ================================================
const fallbackProducts = [
  { id: 1, img: 'img/runner.png', name: "Men's Nike T-Shirt Shoes", cat: 'Running', price: 189, old: 240, rating: 4.9, reviews: 2384, sale: true, desc: 'Lightweight and responsive running shoes designed for speed and comfort. Features breathable mesh upper and cushioned midsole.', colors: ['#111', '#DD3333', '#2563EB'], sizes: [6, 7, 8, 9, 10, 11, 12] },
  { id: 2, img: 'img/casual.png', name: 'Quilted Gleit With Hood', cat: 'Casual', price: 159, old: null, rating: 4.7, reviews: 842, sale: false, desc: 'Classic casual shoes perfect for everyday wear. Premium leather upper with soft interior lining for all-day comfort.', colors: ['#8B4513', '#111', '#F5F5DC'], sizes: [7, 8, 9, 10, 11] },
  { id: 3, img: 'img/trail.png', name: 'Jogers with Black Strip', cat: 'Trail', price: 214, old: 260, rating: 4.8, reviews: 1210, sale: true, desc: 'Rugged trail shoes built for off-road adventures. Aggressive tread pattern and waterproof upper for any terrain.', colors: ['#111', '#4CAF50', '#FF9800'], sizes: [7, 8, 9, 10, 11, 12] },
  { id: 4, img: 'img/performance.png', name: 'Rolex Gold Gilet Shoes', cat: 'Performance', price: 175, old: null, rating: 4.6, reviews: 530, sale: false, desc: 'High-performance athletic shoes with advanced cushioning technology. Designed for serious athletes who demand the best.', colors: ['#FFD700', '#111', '#C0C0C0'], sizes: [6, 7, 8, 9, 10, 11] },
  { id: 5, img: 'img/runner.png', name: 'AeroFlex Runner X2', cat: 'Running', price: 199, old: 249, rating: 4.9, reviews: 1850, sale: true, desc: 'Next-generation running shoe with carbon fiber plate and energy-return foam. Perfect for marathons and daily training.', colors: ['#2563EB', '#111', '#10B981'], sizes: [7, 8, 9, 10, 11, 12] },
  { id: 6, img: 'img/casual.png', name: 'Urban Forge Chelsea', cat: 'Casual', price: 145, old: null, rating: 4.5, reviews: 678, sale: false, desc: 'Sleek chelsea boot style sneaker hybrid. Perfect for smart-casual occasions with waterproof leather.', colors: ['#111', '#8B4513', '#666'], sizes: [7, 8, 9, 10, 11] },
  { id: 7, img: 'img/trail.png', name: 'Nimbus Trail Pro', cat: 'Trail', price: 229, old: 280, rating: 4.8, reviews: 1543, sale: true, desc: 'Professional trail running shoes with GORE-TEX waterproofing and Vibram outsole. Conquers any mountain.', colors: ['#4CAF50', '#111', '#FF5722'], sizes: [6, 7, 8, 9, 10, 11, 12] },
  { id: 8, img: 'img/performance.png', name: 'Volt Lab Sprint', cat: 'Performance', price: 195, old: null, rating: 4.7, reviews: 920, sale: false, desc: 'Competition-grade sprinting shoes with minimal weight and maximum energy return. Track and field certified.', colors: ['#FFD700', '#DD3333', '#111'], sizes: [7, 8, 9, 10, 11] },
];

// Active product list — starts with fallback, gets replaced if backend responds
let allProducts = [...fallbackProducts];
let products = allProducts.slice(0, 4);

/**
 * Try to load products from PHP backend.
 * Falls back silently to hardcoded data if backend is unavailable.
 */
async function loadProductsFromBackend() {
  if (typeof apiFetch === 'undefined') return; // api-config.js not loaded

  const result = await apiFetch('/products.php');
  if (result && Array.isArray(result) && result.length > 0) {
    // Map backend format to frontend format
    allProducts = result.map(p => ({
      id: p.id,
      img: p.image_url || 'img/runner.png',
      name: p.name,
      cat: p.category_name || 'Other',
      price: parseFloat(p.discount_price || p.base_price),
      old: p.discount_price ? parseFloat(p.base_price) : null,
      rating: 4.5 + Math.random() * 0.5, // Will be replaced by real reviews later
      reviews: Math.floor(Math.random() * 2000) + 100,
      sale: p.discount_price !== null && p.discount_price !== undefined,
      desc: p.description || '',
      brand: p.brand || '',
      colors: ['#111', '#DD3333', '#2563EB'],
      sizes: [7, 8, 9, 10, 11],
      variants: p.variants || [],
      dbId: p.id // Keep original DB id
    }));
    products = allProducts.slice(0, 4);

    // Re-render everything
    renderProducts();
    renderShopProducts();
    renderFBT();
    renderProductDetail();
    console.log('✓ Products loaded from backend:', allProducts.length);
  }
}

// Load from backend on page load
loadProductsFromBackend();

// ---------------- Categories (Circles) ----------------
const categories = [
  { name: 'Running', img: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=400&fit=crop' },
  { name: 'Casual', img: 'https://images.unsplash.com/photo-1520639888713-7851133b1ed0?w=400&h=400&fit=crop' },
  { name: 'Trail', img: 'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=400&h=400&fit=crop' },
  { name: 'Performance', img: 'https://images.unsplash.com/photo-1562183241-b937e95585b6?w=400&h=400&fit=crop' }
];
const catGrid = document.getElementById('catGrid');
if (catGrid) {
  categories.forEach(c => {
    const el = document.createElement('div');
    el.className = 'cat-circle';
    el.innerHTML = `
      <img src="${c.img}" alt="${c.name}">
      <div class="cat-label">${c.name}</div>
    `;
    el.addEventListener('click', () => {
      window.location.href = `products.html?category=${c.name}`;
    });
    catGrid.appendChild(el);
  });
}

// ================================================
// RENDER PRODUCT CARDS (Home Page)
// ================================================
function createProductCard(p) {
  const el = document.createElement('div');
  el.className = 'pcard';
  el.innerHTML = `
    <div class="pcard-media">
      ${p.sale ? '<div class="badge badge-sale">Sale</div>' : ''}
      <img src="${escapeHtml(p.img)}" alt="${escapeHtml(p.name)}">
    </div>
    <div class="pcard-body">
      <div class="cat">${escapeHtml(p.cat)}</div>
      <div class="name">${escapeHtml(p.name)}</div>
      <div class="stars">★★★★★ <span>(${p.reviews})</span></div>
      <div class="price-row">
        <div class="price">${p.old ? '<span class="old">' + formatPrice(p.old) + '</span>' : ''}${formatPrice(p.price)}</div>
      </div>
      <button class="btn btn-ghost quick-add" onclick="event.stopPropagation(); addToCart(${p.id}); return false;">Add to Cart</button>
    </div>`;
  el.addEventListener('click', () => {
    window.location.href = `product-detail.html?id=${p.id}`;
  });
  return el;
}

function renderProducts() {
  const grid = document.getElementById('productGrid');
  if (!grid) return;
  grid.innerHTML = '';
  products.forEach(p => {
    grid.appendChild(createProductCard(p));
  });
}
renderProducts();

// Frequently Bought Together
const fbt = [
  { id: 101, img: 'img/socks.png', name: 'Volt Lab Crew Socks', price: 18, cat: 'Accessory' },
  { id: 3, img: 'img/trail.png', name: 'Nimbus Trail Pro', price: 214, cat: 'Trail' },
  { id: 102, img: 'img/cleaner.png', name: 'Sole Care Cleaner Kit', price: 24, cat: 'Accessory' },
  { id: 4, img: 'img/performance.png', name: 'Volt Lab Sprint', price: 175, cat: 'Performance' }
];
function renderFBT() {
  const fbtGrid = document.getElementById('fbtGrid');
  if (!fbtGrid) return;
  fbtGrid.innerHTML = '';
  fbt.forEach(p => {
    const el = document.createElement('div');
    el.className = 'pcard';
    el.innerHTML = `<div class="pcard-media" style="height:160px;"><img src="${escapeHtml(p.img)}" alt="${escapeHtml(p.name)}"></div><div class="pcard-body"><div class="name" style="font-size:14px;">${escapeHtml(p.name)}</div><div class="price-row"><div class="price">${formatPrice(p.price)}</div></div></div>`;
    fbtGrid.appendChild(el);
  });
}
renderFBT();

// ================================================
// CART SYSTEM (localStorage)
// ================================================
function getCart() {
  try { return JSON.parse(localStorage.getItem('shoestore_cart')) || []; }
  catch { return []; }
}

function saveCart(cart) {
  localStorage.setItem('shoestore_cart', JSON.stringify(cart));
  updateCartBadge();
}

function addToCart(productId, size, color, qty) {
  size = size || '9';
  color = color || '#111';
  qty = qty || 1;
  const cart = getCart();
  // Find product in allProducts or fbt
  let product = allProducts.find(p => p.id === productId);
  if (!product) {
    product = fbt.find(p => p.id === productId);
  }
  if (!product) return;

  const existingIndex = cart.findIndex(item => item.id === productId && item.size === size && item.color === color);
  if (existingIndex >= 0) {
    cart[existingIndex].qty += qty;
  } else {
    cart.push({
      id: product.id,
      name: product.name,
      img: product.img,
      price: product.price,
      old: product.old || null,
      cat: product.cat,
      size: size,
      color: color,
      qty: qty
    });
  }
  saveCart(cart);
  showToast(`${product.name} added to cart!`, 'success');
}

function removeFromCart(index) {
  const cart = getCart();
  cart.splice(index, 1);
  saveCart(cart);
  if (typeof renderCartPage === 'function') renderCartPage();
}

function updateCartQty(index, newQty) {
  const cart = getCart();
  if (newQty <= 0) {
    cart.splice(index, 1);
  } else {
    cart[index].qty = newQty;
  }
  saveCart(cart);
  if (typeof renderCartPage === 'function') renderCartPage();
}

function getCartTotal() {
  return getCart().reduce((sum, item) => sum + item.price * item.qty, 0);
}

function getCartCount() {
  return getCart().reduce((sum, item) => sum + item.qty, 0);
}

function updateCartBadge() {
  const badge = document.getElementById('cartBadge');
  if (!badge) return;
  const count = getCartCount();
  badge.textContent = count;
  badge.classList.toggle('show', count > 0);
}
updateCartBadge();

// ---------------- Logged-in nav state (customer OR staff) ----------------
// Signed-in staff see "Dashboard"; signed-in customers see their name / "My Account".
// This keeps staff recognised while browsing the store (they are NOT logged out).
function updateAuthNav() {
  const role = localStorage.getItem('role');
  const cust = (typeof getCurrentCustomer === 'function') ? getCurrentCustomer() : null;

  let label, href;
  if (role === 'admin')        { label = '🛠️ Dashboard'; href = 'admin.html'; }
  else if (role === 'manager') { label = '🛠️ Dashboard'; href = 'inventory.html'; }
  else if (cust)               { label = '👤 ' + (cust.first_name || 'My Account'); href = 'account.html'; }
  else return; // not signed in — leave the "Login" button as-is

  document.querySelectorAll('.topbar-actions a.btn.btn-primary.btn-sm').forEach(a => {
    if (/login/i.test(a.textContent)) { a.textContent = label; a.setAttribute('href', href); }
  });
  document.querySelectorAll('#mobileNav a').forEach(a => {
    const h = a.getAttribute('href') || '';
    if (/login/i.test(a.textContent) && /login\.html/.test(h)) { a.textContent = label; a.setAttribute('href', href); }
  });
}
updateAuthNav();

// ================================================
// ADMIN ORDERS — Now fetches from backend
// ================================================
// Fallback data
const fallbackOrders = [
  { id: '#8241', cust: 'Maya Chen', item: 'Aeroflux Runner X2', status: 'done', total: 189 },
  { id: '#8240', cust: 'Diego Ruiz', item: 'Nimbus Trail Pro', status: 'ship', total: 214 },
  { id: '#8239', cust: 'Amara Obi', item: 'Urban Forge Chelsea', status: 'pending', total: 159 },
  { id: '#8238', cust: 'Leo Park', item: 'Volt Lab Sprint', status: 'done', total: 175 },
];
const stMap = { done: ['Delivered', 'st-done'], ship: ['Shipped', 'st-ship'], pending: ['Processing', 'st-pending'], delivered: ['Delivered', 'st-done'], shipped: ['Shipped', 'st-ship'], processing: ['Processing', 'st-pending'], cancelled: ['Cancelled', 'st-pending'] };

function renderOrders(orderData) {
  const tbody = document.getElementById('ordersBody');
  if (!tbody) return;

  if (orderData && Array.isArray(orderData)) {
    // Render backend data
    tbody.innerHTML = orderData.map(o => {
      const statusKey = o.status || 'pending';
      const statusInfo = stMap[statusKey] || ['Unknown', 'st-pending'];
      const customerName = (o.first_name && o.last_name) ? escapeHtml(`${o.first_name} ${o.last_name}`) : 'Guest';
      return `
        <tr>
          <td>${escapeHtml(o.order_number || '#' + o.id)}</td>
          <td>${customerName}</td>
          <td>${escapeHtml(o.first_item_name || 'Order')}</td>
          <td>
            <select class="status-select status-pill ${statusInfo[1]}" onchange="updateOrderStatus(${o.id}, this.value)" style="cursor:pointer; border:none; font-weight:700; font-size:11px; padding:4px 8px; border-radius:20px;">
              <option value="pending" ${statusKey === 'pending' ? 'selected' : ''}>Processing</option>
              <option value="processing" ${statusKey === 'processing' ? 'selected' : ''}>Processing</option>
              <option value="shipped" ${statusKey === 'shipped' ? 'selected' : ''}>Shipped</option>
              <option value="delivered" ${statusKey === 'delivered' ? 'selected' : ''}>Delivered</option>
              <option value="cancelled" ${statusKey === 'cancelled' ? 'selected' : ''}>Cancelled</option>
            </select>
          </td>
          <td>${formatPrice(parseFloat(o.total_amount))}</td>
        </tr>`;
    }).join('');
    return;
  }

  // Fallback render
  tbody.innerHTML = fallbackOrders.map(o => `
    <tr><td>${o.id}</td><td>${o.cust}</td><td>${o.item}</td><td><span class="status-pill ${stMap[o.status][1]}">${stMap[o.status][0]}</span></td><td>${formatPrice(o.total)}</td></tr>
  `).join('');
}
renderOrders();

// Update order status (admin)
async function updateOrderStatus(orderId, newStatus) {
  if (typeof apiFetch === 'undefined') return;
  const result = await apiFetch('/orders.php', {
    method: 'PUT',
    body: { id: orderId, status: newStatus }
  });
  if (result && !result.error) {
    showToast(`Order status updated to "${newStatus}"`, 'success');
  } else {
    showToast('Failed to update order status', 'error');
  }
}

// Load orders from backend for admin page
async function loadAdminOrders() {
  if (typeof apiFetch === 'undefined') return;
  const tbody = document.getElementById('ordersBody');
  if (!tbody) return;

  const result = await apiFetch('/orders.php?limit=20');
  if (result && Array.isArray(result)) {
    renderOrders(result);
  }
}
loadAdminOrders();

// Load dashboard stats from backend
async function loadDashboardStats() {
  if (typeof apiFetch === 'undefined') return;
  const result = await apiFetch('/stats.php');
  if (!result || result.error) return;

  // Update KPI values if elements exist
  const kpiVals = document.querySelectorAll('.kpi .val');
  if (kpiVals.length >= 4) {
    kpiVals[0].textContent = '$' + (result.today_revenue >= 1000 ? (result.today_revenue / 1000).toFixed(1) + 'k' : result.today_revenue.toFixed(0));
    kpiVals[1].textContent = result.total_orders;
    kpiVals[2].textContent = result.total_customers.toLocaleString();
    kpiVals[3].textContent = result.low_stock_alerts;
  }
}
loadDashboardStats();

// Low stock table
const low = [
  { sku: 'AFX-902-9', style: 'Aeroflux Runner X2', size: '9', rem: 3, vel: 'High' },
  { sku: 'NTP-114-11', style: 'Nimbus Trail Pro', size: '11', rem: 2, vel: 'High' },
  { sku: 'UFC-330-8', style: 'Urban Forge Chelsea', size: '8', rem: 5, vel: 'Med' },
];
const lsb = document.getElementById('lowStockBody');
if (lsb) {
  lsb.innerHTML = low.map(l => `
    <tr><td>${l.sku}</td><td>${l.style}</td><td>${l.size}</td><td style="color:var(--danger); font-weight:800;">${l.rem}</td><td>${l.vel}</td><td><button class="btn btn-ghost btn-sm">Reorder</button></td></tr>
  `).join('');
}

// ================================================
// SHOP / PRODUCTS PAGE
// ================================================
function renderShopProducts() {
  const grid = document.getElementById('shopProductGrid');
  if (!grid) return;

  const params = new URLSearchParams(window.location.search);
  const categoryFilter = params.get('category') || 'All';
  const searchQuery = (document.getElementById('shopSearch')?.value || '').toLowerCase();
  const sortBy = document.getElementById('shopSort')?.value || 'default';

  let filtered = [...allProducts];

  // Category filter
  if (categoryFilter !== 'All') {
    filtered = filtered.filter(p => p.cat === categoryFilter);
  }

  // Search
  if (searchQuery) {
    filtered = filtered.filter(p => p.name.toLowerCase().includes(searchQuery) || p.cat.toLowerCase().includes(searchQuery));
  }

  // Sort
  if (sortBy === 'price-low') filtered.sort((a, b) => a.price - b.price);
  else if (sortBy === 'price-high') filtered.sort((a, b) => b.price - a.price);
  else if (sortBy === 'rating') filtered.sort((a, b) => b.rating - a.rating);
  else if (sortBy === 'popular') filtered.sort((a, b) => b.reviews - a.reviews);

  // Update count
  const countEl = document.getElementById('resultCount');
  if (countEl) countEl.textContent = `${filtered.length} product${filtered.length !== 1 ? 's' : ''} found`;

  // Update active filter
  document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.classList.toggle('active', btn.dataset.cat === categoryFilter);
  });

  grid.innerHTML = '';
  if (filtered.length === 0) {
    grid.innerHTML = '<div style="grid-column:1/-1; text-align:center; padding:60px 20px;"><h3 style="margin-bottom:8px;">No products found</h3><p style="color:var(--text-dim);">Try adjusting your search or filters.</p></div>';
    return;
  }
  filtered.forEach(p => grid.appendChild(createProductCard(p)));
}

// Filter button clicks
document.querySelectorAll('.filter-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const cat = btn.dataset.cat;
    const url = new URL(window.location);
    if (cat === 'All') url.searchParams.delete('category');
    else url.searchParams.set('category', cat);
    window.history.replaceState({}, '', url);
    renderShopProducts();
  });
});

// Search input
const shopSearch = document.getElementById('shopSearch');
if (shopSearch) {
  shopSearch.addEventListener('input', renderShopProducts);
}

// Sort select
const shopSort = document.getElementById('shopSort');
if (shopSort) {
  shopSort.addEventListener('change', renderShopProducts);
}

// Initial render for shop page
renderShopProducts();

// ================================================
// PRODUCT DETAIL PAGE
// ================================================
function renderProductDetail() {
  const container = document.getElementById('productDetailContainer');
  if (!container) return;

  const params = new URLSearchParams(window.location.search);
  const productId = parseInt(params.get('id'));
  const product = allProducts.find(p => p.id === productId);

  if (!product) {
    container.innerHTML = '<div style="text-align:center; padding:80px 20px;"><h2>Product not found</h2><p style="color:var(--text-dim); margin-top:12px;">The product you\'re looking for doesn\'t exist.</p><a href="products.html" class="btn btn-primary" style="margin-top:20px;">Browse Products</a></div>';
    return;
  }

  document.title = `${product.name} — LunaStep`;

  let selectedSize = product.sizes[2] || product.sizes[0];
  let selectedColor = product.colors[0];
  let qty = 1;

  function renderDetail() {
    const discount = product.old ? Math.round((1 - product.price / product.old) * 100) : 0;
    container.innerHTML = `
      <div class="product-image-box">
        ${product.sale ? '<div class="badge badge-sale" style="position:absolute;top:20px;right:20px;">Sale</div>' : ''}
        <img src="${escapeHtml(product.img)}" alt="${escapeHtml(product.name)}" />
      </div>
      <div class="product-info">
        <div class="breadcrumb"><a href="index.html">Home</a> / <a href="products.html">Shop</a> / <a href="products.html?category=${encodeURIComponent(product.cat)}">${escapeHtml(product.cat)}</a> / ${escapeHtml(product.name)}</div>
        <h1>${escapeHtml(product.name)}</h1>
        <div class="product-rating">
          <span class="star-display">${'★'.repeat(Math.round(product.rating))}${'☆'.repeat(5 - Math.round(product.rating))}</span>
          <span>${product.rating} (${product.reviews.toLocaleString()} reviews)</span>
        </div>
        <div class="product-price-block">
          <span class="current-price">${formatPrice(product.price)}</span>
          ${product.old ? `<span class="old-price">${formatPrice(product.old)}</span><span class="discount-tag">-${discount}% OFF</span>` : ''}
        </div>
        <p class="product-description">${product.desc}</p>

        <div class="option-label">Size</div>
        <div class="size-selector">
          ${product.sizes.map(s => `<button class="size-btn ${s === selectedSize ? 'selected' : ''}" data-size="${s}">${s}</button>`).join('')}
        </div>

        <div class="option-label">Color</div>
        <div class="color-selector">
          ${product.colors.map(c => `<div class="color-dot ${c === selectedColor ? 'selected' : ''}" data-color="${c}" style="background:${c};" title="${c}"></div>`).join('')}
        </div>

        <div class="option-label">Quantity</div>
        <div class="qty-selector">
          <button class="qty-btn" id="qtyMinus">−</button>
          <span class="qty-value" id="qtyDisplay">${qty}</span>
          <button class="qty-btn" id="qtyPlus">+</button>
        </div>

        <div class="add-to-cart-row">
          <button class="btn btn-primary btn-lg" id="addToCartBtn">🛒 Add to Cart</button>
          <button class="btn btn-ghost btn-lg" onclick="showToast('Added to wishlist! ❤️','success')">♡ Wishlist</button>
        </div>

        <div class="product-features">
          <div class="feature-item"><div class="feature-icon">🚚</div> Free shipping on orders over $100</div>
          <div class="feature-item"><div class="feature-icon">↩️</div> 30-day easy returns</div>
          <div class="feature-item"><div class="feature-icon">✓</div> 100% authentic guaranteed</div>
          <div class="feature-item"><div class="feature-icon">💬</div> 24/7 customer support</div>
        </div>
      </div>
    `;

    // Size buttons
    container.querySelectorAll('.size-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        selectedSize = parseInt(btn.dataset.size);
        renderDetail();
      });
    });

    // Color dots
    container.querySelectorAll('.color-dot').forEach(dot => {
      dot.addEventListener('click', () => {
        selectedColor = dot.dataset.color;
        renderDetail();
      });
    });

    // Quantity
    document.getElementById('qtyMinus')?.addEventListener('click', () => { if (qty > 1) { qty--; renderDetail(); } });
    document.getElementById('qtyPlus')?.addEventListener('click', () => { qty++; renderDetail(); });

    // Add to Cart
    document.getElementById('addToCartBtn')?.addEventListener('click', () => {
      addToCart(product.id, selectedSize.toString(), selectedColor, qty);
    });
  }

  renderDetail();

  // Render related products
  const relatedGrid = document.getElementById('relatedGrid');
  if (relatedGrid) {
    relatedGrid.innerHTML = '';
    allProducts.filter(p => p.cat === product.cat && p.id !== product.id).slice(0, 4).forEach(p => {
      relatedGrid.appendChild(createProductCard(p));
    });
    // If not enough same-category, fill with others
    if (relatedGrid.children.length < 4) {
      allProducts.filter(p => p.id !== product.id && p.cat !== product.cat).slice(0, 4 - relatedGrid.children.length).forEach(p => {
        relatedGrid.appendChild(createProductCard(p));
      });
    }
  }
}
renderProductDetail();

// ================================================
// CART PAGE
// ================================================
function renderCartPage() {
  const cartItemsEl = document.getElementById('cartItemsList');
  const cartSummaryEl = document.getElementById('cartSummary');
  const cartContentEl = document.getElementById('cartContent');
  const cartEmptyEl = document.getElementById('cartEmpty');
  if (!cartItemsEl) return;

  const cart = getCart();

  if (cart.length === 0) {
    if (cartContentEl) cartContentEl.style.display = 'none';
    if (cartEmptyEl) cartEmptyEl.style.display = 'block';
    return;
  }

  if (cartContentEl) cartContentEl.style.display = 'grid';
  if (cartEmptyEl) cartEmptyEl.style.display = 'none';

  cartItemsEl.innerHTML = cart.map((item, i) => `
    <div class="cart-item">
      <div class="cart-item-img"><img src="${escapeHtml(item.img)}" alt="${escapeHtml(item.name)}"></div>
      <div class="cart-item-details">
        <div>
          <div class="item-name">${escapeHtml(item.name)}</div>
          <div class="item-meta">Size: ${item.size} · Color: <span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:${item.color};vertical-align:middle;border:1px solid var(--border);"></span></div>
        </div>
        <div style="display:flex; align-items:center; justify-content:space-between; margin-top:12px;">
          <div class="cart-item-actions">
            <div class="qty-selector" style="margin-bottom:0;">
              <button class="qty-btn" onclick="updateCartQty(${i}, ${item.qty - 1})">−</button>
              <span class="qty-value">${item.qty}</span>
              <button class="qty-btn" onclick="updateCartQty(${i}, ${item.qty + 1})">+</button>
            </div>
            <button class="cart-remove" onclick="removeFromCart(${i})">Remove</button>
          </div>
          <div class="item-price">${formatPrice(item.price * item.qty)}</div>
        </div>
      </div>
    </div>
  `).join('');

  // Summary
  if (cartSummaryEl) {
    const subtotal = getCartTotal();
    const shipping = subtotal >= 100 ? 0 : 9.99;
    const tax = subtotal * 0.08;
    const total = subtotal + shipping + tax;

    cartSummaryEl.innerHTML = `
      <h3>Order Summary</h3>
      <div class="summary-row"><span>Subtotal (${getCartCount()} items)</span><span>${formatPrice(subtotal)}</span></div>
      <div class="summary-row"><span>Shipping</span><span class="${shipping === 0 ? 'free' : ''}">${shipping === 0 ? 'FREE' : formatPrice(shipping)}</span></div>
      <div class="summary-row"><span>Tax (8%)</span><span>${formatPrice(tax)}</span></div>
      <div class="summary-row total"><span>Total</span><span>${formatPrice(total)}</span></div>
      <a href="checkout.html" class="btn btn-primary btn-block" style="margin-top:20px;">Proceed to Checkout</a>
      <a href="products.html" style="display:block; text-align:center; margin-top:14px; font-size:13px; font-weight:700; color:var(--text-dim);">← Continue Shopping</a>
    `;
  }

  updateCartBadge();
}
renderCartPage();

// ================================================
// CHECKOUT PAGE — Now POSTs to backend
// ================================================
function renderCheckoutSummary() {
  const summaryEl = document.getElementById('checkoutSummary');
  if (!summaryEl) return;

  const cart = getCart();
  const subtotal = getCartTotal();
  const shipping = subtotal >= 100 ? 0 : 9.99;
  const tax = subtotal * 0.08;
  const total = subtotal + shipping + tax;

  summaryEl.innerHTML = `
    <h3>Order Summary</h3>
    <div style="margin:20px 0;">
      ${cart.map(item => `
        <div style="display:flex; gap:12px; padding:10px 0; border-bottom:1px solid var(--border);">
          <div style="width:50px; height:50px; background:var(--bg2); border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0; overflow:hidden;">
            <img src="${item.img}" style="width:80%; object-fit:contain;" alt="${item.name}">
          </div>
          <div style="flex:1;">
            <div style="font-size:13px; font-weight:700;">${item.name}</div>
            <div style="font-size:12px; color:var(--text-faint);">Size: ${item.size} × ${item.qty}</div>
          </div>
          <div style="font-weight:800; font-size:14px;">${formatPrice(item.price * item.qty)}</div>
        </div>
      `).join('')}
    </div>
    <div class="summary-row"><span>Subtotal</span><span>${formatPrice(subtotal)}</span></div>
    <div class="summary-row"><span>Shipping</span><span class="${shipping === 0 ? 'free' : ''}">${shipping === 0 ? 'FREE' : formatPrice(shipping)}</span></div>
    <div class="summary-row"><span>Tax (8%)</span><span>${formatPrice(tax)}</span></div>
    <div class="summary-row total"><span>Total</span><span>${formatPrice(total)}</span></div>
  `;
}
renderCheckoutSummary();

// Payment method selection
document.querySelectorAll('.payment-option').forEach(opt => {
  opt.addEventListener('click', () => {
    document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
    opt.classList.add('selected');
    opt.querySelector('input[type="radio"]').checked = true;
  });
});

// Checkout form submit — POST to backend
const checkoutForm = document.getElementById('checkoutForm');
if (checkoutForm) {
  checkoutForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const cart = getCart();
    if (cart.length === 0) {
      showToast('Your cart is empty!', 'error');
      return;
    }

    const subtotal = getCartTotal();
    const shipping = subtotal >= 100 ? 0 : 9.99;
    const tax = subtotal * 0.08;
    const total = subtotal + shipping + tax;

    // Build shipping address
    const address = [
      document.getElementById('address')?.value,
      document.getElementById('city')?.value,
      document.getElementById('state')?.value,
      document.getElementById('zip')?.value,
      document.getElementById('country')?.value
    ].filter(Boolean).join(', ');

    const paymentMethod = document.querySelector('input[name="payment"]:checked')?.value || 'cod';

    // Build order data
    const orderData = {
      items: cart.map(item => ({
        variant_id: null, // We don't have variant IDs in frontend cart yet
        quantity: item.qty,
        price: item.price
      })),
      total_amount: total,
      shipping_address: address,
      payment_method: paymentMethod
    };

    // Try backend
    let orderId = 'ORD-' + Math.random().toString(36).substr(2, 8).toUpperCase();

    if (typeof apiFetch !== 'undefined') {
      const result = await apiFetch('/orders.php', {
        method: 'POST',
        body: orderData
      });

      if (result && !result.error && result.order_number) {
        orderId = result.order_number;
      }
    }

    // Show confirmation
    document.getElementById('checkoutFormSection').style.display = 'none';
    const confirmEl = document.getElementById('orderConfirmation');
    confirmEl.style.display = 'block';
    document.getElementById('confirmOrderId').textContent = orderId;

    // Clear cart
    localStorage.removeItem('shoestore_cart');
    updateCartBadge();

    showToast('Order placed successfully! 🎉', 'success');
  });
}
