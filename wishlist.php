<?php
include 'db.php';

/* Top-bar ticker (same as your other pages) */
$news = [
  "💠 Welcome to Vanniddhi Plant Nursery — premium plants & decor 💠",
  "💠 Free delivery on orders above ₹999 (Indore city limits) 💠",
  "💠 Same-day dispatch on prepaid orders placed before 4 PM 💠",
  "💠 Diwali Season: Extended hours — store open till 11 PM, all week 💠",
  "💠 Pick-up: Shop No. 04, Temp. Cracker Market, Chhota Bangarda 💠",
  "💠 Need help choosing? Expert support & plant care guidance 💠",
  "💠 Preserved Nature Tabletops & Moss Frames now in stock 💠",
  "💠 Combo packs & bulk orders available — limited stock 💠",
  "💠 Secure payments: UPI / Card / Cash on pickup 💠",
  "💠 Helpline: 94250 46286 💠"
];

$tickerText = implode(' • ', array_map('htmlspecialchars', $news));
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Wishlist • Vanniddhi</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
:root{
  --primary:#2e7d32; --primary-light:#4caf50; --primary-dark:#1b5e20;
  --secondary:#ff9800; --accent:#8bc34a;
  --light:#f8fdf8; --dark:#1a331c; --text:#333; --white:#fff; --gray:#f5f5f5; --border:#e0e0e0;
}
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:'Open Sans',sans-serif;color:var(--text);background:var(--light);line-height:1.6;overflow-x:hidden}
h1,h2,h3{font-family:'Poppins',sans-serif;font-weight:600;line-height:1.3}
.container{width:100%;max-width:1200px;margin:0 auto;padding:0 16px}

/* ===== Top bar ticker ===== */
.top-bar{background:var(--primary-dark);color:#fff;font-size:.92rem}
.top-bar-content{min-height:40px;display:flex;align-items:center;gap:16px}
.ticker-track{overflow:hidden;white-space:nowrap;flex:1}
.ticker-line{display:inline-block;padding-left:100%;animation:ticker 28s linear infinite}
@keyframes ticker{from{transform:translateX(0)} to{transform:translateX(-100%)}}
.top-bar-links a{color:#fff;text-decoration:none;margin-left:18px}
.top-bar-links a:hover{color:var(--accent)}

/* ===== Header / Nav (matches products/product pages) ===== */
header{background:#fff;box-shadow:0 2px 10px rgba(0,0,0,.08);position:sticky;top:0;z-index:1000}
.header-container{display:flex;justify-content:space-between;align-items:center;padding:14px 0;gap:12px;flex-wrap:wrap}
.logo{display:flex;align-items:center;gap:10px;text-decoration:none}
.logo-icon{font-size:2rem;color:var(--primary)}
.logo-text{font-size:1.6rem;font-weight:700;color:var(--primary)}
.search-bar{flex:1;max-width:720px;position:relative;order:3;width:100%}
.search-bar input{width:100%;height:44px;padding:0 16px;border:1px solid var(--border);border-radius:12px;outline:none}
.search-bar input:focus{border-color:var(--primary)}
.search-bar button{position:absolute;right:6px;top:5px;height:34px;padding:0 14px;border:none;border-radius:10px;cursor:pointer;background:var(--primary);color:#fff}
.header-actions{display:flex;align-items:center}
.header-action{position:relative;margin-left:20px;display:flex;flex-direction:column;align-items:center;color:var(--text);text-decoration:none}
.header-action i{font-size:1.4rem;margin-bottom:4px}
.header-action:hover{color:var(--primary)}
#cartCount{position:absolute;top:-8px;right:-8px;width:18px;height:18px;border-radius:50%;background:var(--secondary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:800}
nav{background:var(--gray);padding:12px 0}
.nav-container{display:flex;justify-content:space-between;align-items:center}
.nav-links{list-style:none;display:flex;gap:15px}
.nav-links a{color:var(--text);text-decoration:none;font-weight:600}
.nav-links a:hover{color:var(--primary)}
.mobile-menu{display:none;font-size:1.5rem;color:var(--primary);cursor:pointer}
@media (max-width:992px){
  .mobile-menu{display:block}
  .nav-links{display:none;position:fixed;inset:60px 0 0 0;background:#fff;padding:18px 20px;flex-direction:column;gap:12px;overflow:auto;z-index:1200;box-shadow:0 12px 30px rgba(0,0,0,.12)}
  .nav-links.show{display:flex}
}
@media (max-width:576px){ .header-action span{display:none} }

/* ===== Page: Wishlist ===== */
main{padding:22px 0 40px}
.page-head{display:flex;flex-wrap:wrap;gap:10px;align-items:center;justify-content:space-between;margin:6px 0 14px}
.page-head h1{color:var(--primary);font-size:1.6rem}
.count-pill{background:#e8f5e9;color:var(--primary-dark);padding:6px 10px;border-radius:999px;font-weight:800;font-size:.9rem}

/* Controls */
.controls{
  display:flex;flex-wrap:wrap;gap:10px;align-items:center;
  background:#fff;border:1px solid var(--border);border-radius:14px;padding:10px;box-shadow:0 6px 16px rgba(0,0,0,.06)
}
.controls .search{flex:1;min-width:220px;position:relative}
.controls .search input{width:100%;height:40px;border:1px solid var(--border);border-radius:10px;padding:0 12px}
.controls select{height:40px;border:1px solid var(--border);border-radius:10px;padding:0 10px;background:#fff}
.btn{height:40px;padding:0 14px;border:none;border-radius:10px;cursor:pointer;font-weight:800}
.btn-primary{background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;box-shadow:0 8px 16px rgba(46,125,50,.25)}
.btn-ghost{background:#fff;border:1px solid var(--border)}
.btn-danger{background:#fff;border:1px solid #ef9a9a;color:#c62828}

/* Summary */
.summary{display:flex;gap:10px;align-items:center;margin:12px 0;color:#556}
.summary .total{font-weight:900;color:var(--primary-dark)}

/* Grid/cards */
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:18px;margin:18px 0}
.card{background:#fff;border:1px solid var(--border);border-radius:16px;overflow:hidden;position:relative;box-shadow:0 6px 16px rgba(0,0,0,.06)}
.card .select{position:absolute;top:10px;left:10px;z-index:2;background:#fff;border:1px solid var(--border);width:22px;height:22px;border-radius:6px;display:grid;place-items:center}
.card .img{height:210px;overflow:hidden;background:#f2f5f2;cursor:pointer}
.card .img img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .4s}
.card:hover .img img{transform:scale(1.04)}
.info{padding:14px}
h3{margin:0 0 8px;color:var(--primary);font-size:1.05rem;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.meta{color:#6b7a6d;font-size:.9rem;margin:0 0 6px}
.price{font-weight:900;color:var(--primary-dark)}
.row{display:flex;gap:8px;align-items:center;margin-top:10px}
.row input{width:58px;height:38px;border:1px solid var(--border);border-radius:10px;text-align:center;font-weight:700}
.add{background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff}
.remove{background:#fff;border:1px solid var(--border)}
.empty{padding:30px;text-align:center;background:#fff;border:1px dashed var(--border);border-radius:12px;margin:18px 0}

/* Footer (simple) */
footer{background:var(--dark);color:#fff;margin-top:40px}
.footer-inner{padding:36px 0;text-align:center}
.footer-inner a{color:#ffeb3b;text-decoration:none}
</style>
</head>
<body>

<!-- ===== TOP BAR ===== -->
<div class="top-bar">
  <div class="container top-bar-content">
    <div class="ticker-track" aria-label="Announcements">
      <div class="ticker-line">&nbsp;&nbsp;<?= $tickerText ?> &nbsp;&nbsp;&bull;&nbsp;&nbsp; <?= $tickerText ?></div>
    </div>
    <div class="top-bar-links">
      <a href="#"><i class="fas fa-question-circle"></i> Help</a>
      <a href="#"><i class="fas fa-map-marker-alt"></i> Store</a>
      <a href="#"><i class="fas fa-truck"></i> Track</a>
    </div>
  </div>
</div>

<!-- ===== HEADER / NAV ===== -->
<header>
  <div class="container header-container">
    <a href="index.php" class="logo">
      <div class="logo-icon"><i class="fas fa-leaf"></i></div>
      <div class="logo-text">Vanniddhi</div>
    </a>

    <div class="search-bar">
      <input type="text" placeholder="Search for plants, seeds, pots and more...">
      <button><i class="fas fa-search"></i></button>
    </div>

    <div class="header-actions">
      <a href="account.php" class="header-action"><i class="fas fa-user"></i><span>Account</span></a>
      <a href="admin_dashboard.php" class="header-action"><i class="fas fa-shield-alt"></i><span>Admin</span></a>
      <a href="cart.php" class="header-action" style="position:relative;">
        <i class="fas fa-shopping-cart"></i><span>Cart</span>
        <div id="cartCount">0</div>
      </a>
    </div>

    <div class="mobile-menu"><i class="fas fa-bars"></i></div>
  </div>

  <nav>
    <div class="container nav-container">
      <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="products.php" style="color:var(--primary);font-weight:700">Products</a></li>
        <li><a href="products.php?category=Pots%20and%20Planters">Pots & Planters</a></li>
        <li><a href="products.php?category=Gardening%20Tools">Gardening Tools</a></li>
        <li><a href="products.php?category=Soil%20%26%20Fertilizers">Soil & Fertilizers</a></li>
      </ul>
      <div class="nav-offer"><a href="products.php" class="btn btn-primary" style="padding:10px 18px">Special Offers</a></div>
    </div>
  </nav>
</header>

<!-- ===== MAIN ===== -->
<main>
  <div class="container">
    <div class="page-head">
      <h1>Your Wishlist</h1>
      <span class="count-pill" id="countPill">0 items</span>
    </div>

    <div class="controls" id="controls">
      <div class="search">
        <input type="text" id="wishSearch" placeholder="Search in wishlist (name, category)...">
      </div>
      <select id="sortSel" aria-label="Sort wishlist">
        <option value="new">Sort: Newest</option>
        <option value="name_asc">Name A–Z</option>
        <option value="price_asc">Price: Low → High</option>
        <option value="price_desc">Price: High → Low</option>
      </select>
      <button class="btn btn-primary" id="addSelected">Add selected</button>
      <button class="btn btn-ghost" id="removeSelected">Remove selected</button>
      <button class="btn btn-primary" id="addAll">Add all</button>
      <button class="btn btn-danger" id="clearAll">Clear all</button>
      <button class="btn btn-ghost" id="shareBtn"><i class="fa-solid fa-share-nodes"></i>&nbsp;Share</button>
    </div>

    <div class="summary">
      <span id="visibleCount">0 shown</span> •
      <span>Potential total: <span class="total" id="visibleTotal">₹0.00</span></span>
    </div>

    <div id="wrap"></div>
  </div>
</main>

<!-- ===== FOOTER ===== -->
<footer>
  <div class="container footer-inner">
    <p>&copy; <?= date('Y') ?> Vanniddhi Plant Nursery. All rights reserved.</p>
  </div>
</footer>

<script>
/* ===== Mobile nav drawer ===== */
const mobileMenu = document.querySelector('.mobile-menu');
const navLinks = document.querySelector('.nav-links');
if (mobileMenu && navLinks){
  mobileMenu.addEventListener('click', ()=> navLinks.classList.toggle('show'));
  navLinks.addEventListener('click', e=>{ if(e.target.closest('a')) navLinks.classList.remove('show'); });
}

/* ===== Wishlist helpers ===== */
function getWishlist(){
  try{
    const raw = localStorage.getItem('wishlist');
    const arr = raw ? JSON.parse(raw) : [];
    return Array.isArray(arr) ? arr.map(x=>+x).filter(Boolean) : [];
  }catch{ return []; }
}
function setWishlist(arr){
  localStorage.setItem('wishlist', JSON.stringify((arr||[]).map(x=>+x)));
}
function removeFromWishlist(id){
  let l = getWishlist(); const i = l.indexOf(+id);
  if(i!==-1){ l.splice(i,1); setWishlist(l); render(); }
}
function addToCart(name, price, img, qty){
  let cart = JSON.parse(localStorage.getItem('cart')||'[]');
  qty = Math.max(1, parseInt(qty)||1);
  const found = cart.find(i=>i.name===name);
  if(found) found.qty+=qty; else cart.push({name,price:parseFloat(price),qty,img});
  localStorage.setItem('cart', JSON.stringify(cart));
  updateCartCount();
}

/* ===== Import from shared link (?ids=1,2,3) ===== */
(function importShared(){
  const params = new URLSearchParams(location.search);
  const idsParam = params.get('ids');
  if(!idsParam) return;
  const incoming = idsParam.split(',').map(x=>+x).filter(Boolean);
  if(!incoming.length) return;
  setWishlist([...new Set(incoming)]);
})();

/* ===== State ===== */
let DATA = [];         // fetched product objects
let SELECTED = new Set(); // selected product ids across renders
let FILTER = '';
let SORT = 'new';

/* ===== Escaper ===== */
function esc(s){ return (s??'').toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/"/g,'&quot;'); }

/* ===== Badge ===== */
function updateCartCount(){
  const cart = JSON.parse(localStorage.getItem('cart') || '[]');
  const totalItems = cart.reduce((sum, item) => sum + (item.qty||0), 0);
  const cartCount = document.getElementById("cartCount");
  if (cartCount) cartCount.textContent = totalItems > 0 ? totalItems : 0;
}
window.addEventListener("storage", e => { if (e.key === "cart") updateCartCount(); });

/* ===== Render ===== */
async function render(){
  const ids = getWishlist();
  const wrap = document.getElementById('wrap');
  document.getElementById('countPill').textContent = `${ids.length} item${ids.length!==1?'s':''}`;

  if(!ids.length){
    DATA = []; SELECTED.clear();
    wrap.innerHTML = `<div class="empty">Your wishlist is empty. <a href="products.php">Browse products</a></div>`;
    document.getElementById('visibleCount').textContent = '0 shown';
    document.getElementById('visibleTotal').textContent = '₹0.00';
    return;
  }

  // Fetch only if DATA is stale (ids changed length or we don't have same ids)
  const haveSame = (DATA.length === ids.length) && ids.every(id => DATA.some(p=>+p.id===+id));
  if(!haveSame){
    wrap.innerHTML = `<div class="empty">Loading your wishlist…</div>`;
    try{
      const r = await fetch('get_products.php?ids='+ids.join(','));
      DATA = await r.json();
      if(!Array.isArray(DATA)) DATA = [];
    }catch{
      DATA = [];
    }
  }

  // Filter + sort
  const needle = (FILTER||'').toLowerCase();
  let rows = DATA.filter(p=>{
    const hay = ((p.name||'')+' '+(p.category||'')).toLowerCase();
    return hay.includes(needle);
  });

  if(SORT==='name_asc'){
    rows.sort((a,b)=> (a.name||'').localeCompare(b.name||''));
  }else if(SORT==='price_asc'){
    rows.sort((a,b)=> (+a.price) - (+b.price));
  }else if(SORT==='price_desc'){
    rows.sort((a,b)=> (+b.price) - (+a.price));
  }else{
    // default: keep by incoming ids order (newest first if your endpoint returns DESC by id)
    rows.sort((a,b)=> ids.indexOf(+a.id) - ids.indexOf(+b.id));
  }

  // Summary
  const total = rows.reduce((sum,p)=> sum + (+p.price||0), 0);
  document.getElementById('visibleCount').textContent = `${rows.length} shown`;
  document.getElementById('visibleTotal').textContent = `₹${total.toFixed(2)}`;

  if(!rows.length){
    wrap.innerHTML = `<div class="empty">No matches. Try clearing search or changing sort.</div>`;
    return;
  }

  // Build grid
  const gridHTML = rows.map(p=>{
    const id = +p.id;
    const checked = SELECTED.has(id) ? 'checked' : '';
    return `
      <div class="card">
        <label class="select"><input type="checkbox" ${checked} onchange="toggleSelect(${id}, this.checked)"></label>
        <div class="img" onclick="location='product.php?id=${id}'">
          <img src="${esc(p.thumbnail||'placeholder.png')}" alt="${esc(p.name||'')}">
        </div>
        <div class="info">
          <h3 title="${esc(p.name||'')}">${esc(p.name||'')}</h3>
          <p class="meta">${esc(p.category||'')}</p>
          <div class="price">₹${(+p.price||0).toFixed(2)}</div>
          <div class="row">
            <input type="number" min="1" value="1" id="q_${id}">
            <button class="btn add" onclick="addToCart('${esc(p.name||'')}', ${+p.price||0}, '${esc(p.thumbnail||'')}', document.getElementById('q_${id}').value)">Add</button>
            <button class="btn remove" onclick="removeFromWishlist(${id})">Remove</button>
          </div>
        </div>
      </div>
    `;
  }).join('');

  wrap.innerHTML = `<div class="grid">${gridHTML}</div>`;
  updateCartCount();
}

/* Selection helpers */
function toggleSelect(id, on){ if(on){ SELECTED.add(+id);} else { SELECTED.delete(+id);} }
function addSelected(){
  if(!SELECTED.size) return alert('Select at least one item.');
  DATA.filter(p=> SELECTED.has(+p.id)).forEach(p=>{
    const qty = 1;
    addToCart(p.name, +p.price||0, p.thumbnail||'', qty);
  });
  alert('Selected items added to cart!');
}
function removeSelectedItems(){
  if(!SELECTED.size) return alert('Select at least one item.');
  if(!confirm('Remove selected items from wishlist?')) return;
  let ids = getWishlist().filter(id=> !SELECTED.has(+id));
  setWishlist(ids);
  SELECTED.clear();
  render();
}
function addAll(){
  DATA.forEach(p=> addToCart(p.name, +p.price||0, p.thumbnail||'', 1));
  alert('All wishlist items added to cart!');
}
function clearAll(){
  if(!confirm('Clear all items from your wishlist?')) return;
  setWishlist([]);
  SELECTED.clear();
  render();
}

/* Share wishlist (via Web Share API or WhatsApp fallback) */
async function shareWishlist(){
  const ids = getWishlist();
  if(!ids.length) return alert('Your wishlist is empty.');
  const url = new URL(location.href);
  url.search = ''; // clean current params
  url.searchParams.set('ids', ids.join(','));
  const link = url.toString();
  if(navigator.share){
    try{ await navigator.share({title:'My Vanniddhi wishlist', text:'Check out my wishlist:', url: link}); return; }catch{}
  }
  // Fallback: WhatsApp
  window.open('https://wa.me/?text='+encodeURIComponent('My Vanniddhi wishlist: '+link),'_blank');
}

/* Controls events */
document.getElementById('wishSearch').addEventListener('input', e=>{ FILTER = e.target.value||''; render(); });
document.getElementById('sortSel').addEventListener('change', e=>{ SORT = e.target.value; render(); });
document.getElementById('addSelected').addEventListener('click', addSelected);
document.getElementById('removeSelected').addEventListener('click', removeSelectedItems);
document.getElementById('addAll').addEventListener('click', addAll);
document.getElementById('clearAll').addEventListener('click', clearAll);
document.getElementById('shareBtn').addEventListener('click', shareWishlist);

/* Initial render */
document.addEventListener('DOMContentLoaded', ()=>{ updateCartCount(); render(); });
</script>

</body>
</html>
