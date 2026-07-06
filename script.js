// ---------------- tabs ----------------
document.querySelectorAll('.tab[data-screen]').forEach(t=>{
  t.addEventListener('click', ()=>{
    document.querySelectorAll('.tab[data-screen]').forEach(x=>x.classList.remove('active'));
    t.classList.add('active');
    document.querySelectorAll('.screen').forEach(s=>s.classList.remove('active'));
    document.getElementById(t.dataset.screen).classList.add('active');
    document.querySelectorAll('.reveal').forEach(r=>r.classList.add('in'));
    window.scrollTo({top:0,behavior:'smooth'});
  });
});

// ---------------- cursor glow ----------------
const glow = document.getElementById('glow');
document.addEventListener('mousemove', e=>{
  glow.style.left = e.clientX+'px';
  glow.style.top = e.clientY+'px';
});

// ---------------- ripple ----------------
function ripple(e){
  const btn = e.currentTarget;
  const rect = btn.getBoundingClientRect();
  const circle = document.createElement('span');
  const size = Math.max(rect.width, rect.height);
  circle.className='ripple';
  circle.style.width = circle.style.height = size+'px';
  circle.style.left = (e.clientX-rect.left-size/2)+'px';
  circle.style.top = (e.clientY-rect.top-size/2)+'px';
  btn.style.position='relative';
  btn.appendChild(circle);
  setTimeout(()=>circle.remove(),650);
}

// ---------------- particles ----------------
const pWrap = document.getElementById('particles');
for(let i=0;i<24;i++){
  const s = document.createElement('i');
  s.style.left = Math.random()*100+'%';
  s.style.bottom = '0px';
  s.style.animationDuration = (5+Math.random()*8)+'s';
  s.style.animationDelay = (Math.random()*8)+'s';
  pWrap.appendChild(s);
}

// ---------------- reveal on scroll ----------------
const io = new IntersectionObserver(entries=>{
  entries.forEach(en=>{ if(en.isIntersecting) en.target.classList.add('in'); });
},{threshold:0.15});
document.querySelectorAll('.reveal').forEach(el=>io.observe(el));

// ---------------- animated counters ----------------
const counted = new WeakSet();
const cio = new IntersectionObserver(entries=>{
  entries.forEach(en=>{
    if(en.isIntersecting && !counted.has(en.target)){
      counted.add(en.target);
      const target = +en.target.dataset.count;
      let cur = 0; const step = Math.max(1, target/40);
      const t = setInterval(()=>{ cur+=step; if(cur>=target){cur=target; clearInterval(t);} en.target.textContent = Math.round(cur); },30);
    }
  });
},{threshold:0.4});
document.querySelectorAll('[data-count]').forEach(el=>cio.observe(el));

// ---------------- data ----------------
const products = [
  {emoji:'👟', name:'Aeroflux Runner X2', cat:'Running', price:189, old:240, rating:4.9, reviews:2384, stock:true, ai:true},
  {emoji:'👞', name:'Urban Forge Chelsea', cat:'Casual', price:159, old:null, rating:4.7, reviews:842, stock:true, ai:false},
  {emoji:'🥾', name:'Nimbus Trail Pro', cat:'Trail', price:214, old:260, rating:4.8, reviews:1210, stock:true, ai:true},
  {emoji:'👟', name:'Volt Lab Sprint', cat:'Performance', price:175, old:null, rating:4.6, reviews:530, stock:false, ai:false},
];
const grid = document.getElementById('productGrid');
products.forEach(p=>{
  const el = document.createElement('div');
  el.className='glass pcard';
  el.innerHTML = `
    <div class="pcard-media">
      ${p.ai?'<div class="badge badge-ai">AI Match</div>':''}
      ${p.old?'<div class="badge badge-sale">-'+Math.round((1-p.price/p.old)*100)+'%</div>':''}
      <div class="wish-btn">♡</div>
      <div class="emoji">${p.emoji}</div>
    </div>
    <div class="pcard-body">
      <div class="cat">${p.cat}</div>
      <div class="name">${p.name}</div>
      <div class="stars">★★★★★ <span>${p.rating} (${p.reviews})</span></div>
      <div class="price-row">
        <div class="price">$${p.price}${p.old?'<span class="old">$'+p.old+'</span>':''}</div>
        <div class="stock-dot" style="${p.stock?'':'color:var(--danger)'}"><i style="${p.stock?'':'background:var(--danger); box-shadow:0 0 8px var(--danger);'}"></i>${p.stock?'In stock':'Low stock'}</div>
      </div>
      <button class="btn btn-primary quick-add" onclick="ripple(event)">Quick Add</button>
    </div>`;
  grid.appendChild(el);
});

// sizes
const sizeRow = document.getElementById('sizeRow');
[7,8,9,10,11,12].forEach((s,i)=>{
  const b = document.createElement('div');
  b.textContent = s;
  b.style.cssText = `width:46px; height:46px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-weight:700; cursor:pointer; border:1px solid var(--border); background:var(--card); transition:.25s;`;
  if(i===2){ b.style.background='var(--grad-primary)'; b.style.borderColor='transparent'; b.style.boxShadow='0 6px 18px rgba(59,130,246,.4)'; }
  b.onmouseenter=()=>{ if(i!==2){ b.style.borderColor='var(--border-strong)'; b.style.transform='translateY(-2px)'; } };
  b.onmouseleave=()=>{ if(i!==2){ b.style.borderColor='var(--border)'; b.style.transform='translateY(0)'; } };
  sizeRow.appendChild(b);
});

// frequently bought together
const fbt = [{emoji:'🧦',name:'Volt Lab Crew Socks',price:18},{emoji:'👟',name:'Nimbus Trail Pro',price:214},{emoji:'🧴',name:'Sole Care Cleaner Kit',price:24},{emoji:'👜',name:'Aeroflux Shoe Bag',price:32}];
const fbtGrid = document.getElementById('fbtGrid');
fbt.forEach(p=>{
  const el = document.createElement('div');
  el.className='glass pcard';
  el.innerHTML = `<div class="pcard-media"><div class="emoji" style="font-size:70px;">${p.emoji}</div></div><div class="pcard-body"><div class="name">${p.name}</div><div class="price-row"><div class="price">$${p.price}</div></div></div>`;
  fbtGrid.appendChild(el);
});

// admin orders
const orders = [
  {id:'#8241', cust:'Maya Chen', item:'Aeroflux Runner X2', status:'done', total:'$189'},
  {id:'#8240', cust:'Diego Ruiz', item:'Nimbus Trail Pro', status:'ship', total:'$214'},
  {id:'#8239', cust:'Amara Obi', item:'Urban Forge Chelsea', status:'pending', total:'$159'},
  {id:'#8238', cust:'Leo Park', item:'Volt Lab Sprint', status:'done', total:'$175'},
];
const stMap = {done:['Delivered','st-done'], ship:['Shipped','st-ship'], pending:['Processing','st-pending']};
document.getElementById('ordersBody').innerHTML = orders.map(o=>`
  <tr class="trow"><td>${o.id}</td><td>${o.cust}</td><td>${o.item}</td><td><span class="status-pill ${stMap[o.status][1]}">${stMap[o.status][0]}</span></td><td>${o.total}</td></tr>
`).join('');

// live feed
const feed = ['New order from Maya C. — $189','AI flagged restock for Nimbus Trail Pro','Wishlist add: Volt Lab Sprint','Return initiated — Order #8231','New 5★ review on Aeroflux Runner X2'];
document.getElementById('liveFeed').innerHTML = feed.map(f=>`<div class="live-feed-item"><div class="live-dot"></div><div style="font-size:13px; font-weight:600; color:var(--text-dim);">${f}</div></div>`).join('');

// revenue bars
const bars = [40,55,48,70,62,80,74,90,85,95,88,100];
document.getElementById('revBars').innerHTML = bars.map((h,i)=>`<div class="bar" style="height:${h}%; animation-delay:${i*0.05}s;"></div>`).join('');

// low stock table
const low = [
  {sku:'AFX-902-9', style:'Aeroflux Runner X2', size:'9', rem:3, vel:'High'},
  {sku:'NTP-114-11', style:'Nimbus Trail Pro', size:'11', rem:2, vel:'High'},
  {sku:'UFC-330-8', style:'Urban Forge Chelsea', size:'8', rem:5, vel:'Med'},
];
document.getElementById('lowStockBody').innerHTML = low.map(l=>`
  <tr class="trow"><td>${l.sku}</td><td>${l.style}</td><td>${l.size}</td><td style="color:var(--danger); font-weight:800;">${l.rem}</td><td>${l.vel}</td><td><button class="btn btn-ghost btn-sm" onclick="ripple(event)">Reorder</button></td></tr>
`).join('');

// heatmap
const hm = document.getElementById('heatmap');
for(let i=0;i<96;i++){
  const v = Math.random();
  const d = document.createElement('div');
  const color = v>0.75?'rgba(16,185,129,.8)': v>0.5?'rgba(59,130,246,.7)': v>0.3?'rgba(245,158,11,.6)':'rgba(244,63,94,.45)';
  d.style.background = color;
  hm.appendChild(d);
}
