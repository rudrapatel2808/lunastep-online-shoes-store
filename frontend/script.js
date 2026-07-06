

// ---------------- reveal on scroll ----------------
const io = new IntersectionObserver(entries=>{
  entries.forEach(en=>{ if(en.isIntersecting) en.target.classList.add('in'); });
},{threshold:0.15});
document.querySelectorAll('.reveal').forEach(el=>io.observe(el));

// ---------------- data & pricing ----------------
const exchangeRates = {
  USD: { rate: 1, symbol: '$' },
  EUR: { rate: 0.92, symbol: '€' },
  GBP: { rate: 0.79, symbol: '£' },
  INR: { rate: 83.5, symbol: '₹' }
};
let currentCurrency = 'USD';

function formatPrice(priceUSD) {
  const { rate, symbol } = exchangeRates[currentCurrency];
  const converted = priceUSD * rate;
  return symbol + (converted % 1 === 0 ? converted : converted.toFixed(2));
}

const curSel = document.getElementById('currencySelector');
if(curSel) {
  curSel.addEventListener('change', (e) => {
    currentCurrency = e.target.value;
    renderProducts();
    renderFBT();
    renderOrders();
  });
}

// ---------------- Categories (Circles) ----------------
const categories = [
  {name: 'Basketball', img: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=400&fit=crop'},
  {name: 'Air Jordans', img: 'https://images.unsplash.com/photo-1520639888713-7851133b1ed0?w=400&h=400&fit=crop'},
  {name: 'Football', img: 'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=400&h=400&fit=crop'},
  {name: 'Golf', img: 'https://images.unsplash.com/photo-1562183241-b937e95585b6?w=400&h=400&fit=crop'}
];
const catGrid = document.getElementById('catGrid');
if(catGrid) {
  categories.forEach(c => {
    const el = document.createElement('div');
    el.className = 'cat-circle';
    el.innerHTML = `
      <img src="${c.img}" alt="${c.name}">
      <div class="cat-label">${c.name}</div>
    `;
    catGrid.appendChild(el);
  });
}

// ---------------- Products ----------------
const products = [
  {img:'img/runner.png', name:'Men\'s Nike T-Shirt Shoes', cat:'Running', price:189, old:240, rating:4.9, reviews:2384, sale:true},
  {img:'img/casual.png', name:'Quilted Gleit With Hood', cat:'Casual', price:159, old:null, rating:4.7, reviews:842, sale:false},
  {img:'img/trail.png', name:'Jogers with Black strip', cat:'Trail', price:214, old:260, rating:4.8, reviews:1210, sale:true},
  {img:'img/performance.png', name:'Rolex Gold Gilet Shoes', cat:'Performance', price:175, old:null, rating:4.6, reviews:530, sale:false},
];
function renderProducts() {
  const grid = document.getElementById('productGrid');
  if(!grid) return;
  grid.innerHTML = '';
  products.forEach(p=>{
    const el = document.createElement('div');
    el.className='pcard';
    el.innerHTML = `
      <div class="pcard-media">
        ${p.sale?'<div class="badge badge-sale">Sale</div>':''}
        <img src="${p.img}" alt="shoe">
      </div>
      <div class="pcard-body">
        <div class="cat">${p.cat}</div>
        <div class="name">${p.name}</div>
        <div class="stars">★★★★★ <span>(${p.reviews})</span></div>
        <div class="price-row">
          <div class="price">${p.old?'<span class="old">'+formatPrice(p.old)+'</span>':''}${formatPrice(p.price)}</div>
        </div>
        <button class="btn btn-ghost quick-add">Add to Cart</button>
      </div>`;
    grid.appendChild(el);
  });
}
renderProducts();

// frequently bought together
const fbt = [{img:'img/socks.png',name:'Volt Lab Crew Socks',price:18},{img:'img/trail.png',name:'Nimbus Trail Pro',price:214},{img:'img/cleaner.png',name:'Sole Care Cleaner Kit',price:24},{img:'img/performance.png',name:'Volt Lab Sprint',price:175}];
function renderFBT() {
  const fbtGrid = document.getElementById('fbtGrid');
  if(!fbtGrid) return;
  fbtGrid.innerHTML = '';
  fbt.forEach(p=>{
    const el = document.createElement('div');
    el.className='pcard';
    el.innerHTML = `<div class="pcard-media" style="height:160px;"><img src="${p.img}"></div><div class="pcard-body"><div class="name" style="font-size:14px;">${p.name}</div><div class="price-row"><div class="price">${formatPrice(p.price)}</div></div></div>`;
    fbtGrid.appendChild(el);
  });
}
renderFBT();

// admin orders
const orders = [
  {id:'#8241', cust:'Maya Chen', item:'Aeroflux Runner X2', status:'done', total:189},
  {id:'#8240', cust:'Diego Ruiz', item:'Nimbus Trail Pro', status:'ship', total:214},
  {id:'#8239', cust:'Amara Obi', item:'Urban Forge Chelsea', status:'pending', total:159},
  {id:'#8238', cust:'Leo Park', item:'Volt Lab Sprint', status:'done', total:175},
];
const stMap = {done:['Delivered','st-done'], ship:['Shipped','st-ship'], pending:['Processing','st-pending']};
function renderOrders() {
  const tbody = document.getElementById('ordersBody');
  if(!tbody) return;
  tbody.innerHTML = orders.map(o=>`
    <tr><td>${o.id}</td><td>${o.cust}</td><td>${o.item}</td><td><span class="status-pill ${stMap[o.status][1]}">${stMap[o.status][0]}</span></td><td>${formatPrice(o.total)}</td></tr>
  `).join('');
}
renderOrders();

// low stock table
const low = [
  {sku:'AFX-902-9', style:'Aeroflux Runner X2', size:'9', rem:3, vel:'High'},
  {sku:'NTP-114-11', style:'Nimbus Trail Pro', size:'11', rem:2, vel:'High'},
  {sku:'UFC-330-8', style:'Urban Forge Chelsea', size:'8', rem:5, vel:'Med'},
];
const lsb = document.getElementById('lowStockBody');
if(lsb){
  lsb.innerHTML = low.map(l=>`
    <tr><td>${l.sku}</td><td>${l.style}</td><td>${l.size}</td><td style="color:var(--danger); font-weight:800;">${l.rem}</td><td>${l.vel}</td><td><button class="btn btn-ghost btn-sm">Reorder</button></td></tr>
  `).join('');
}
