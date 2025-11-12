<?php
// (No DB required here, but we keep the same top-bar ticker for visual consistency)
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
<title>Account • Vanniddhi</title>

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

/* ===== Header / Nav (same look as products/product pages) ===== */
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

/* ===== Page: Account ===== */
main{padding:22px 0 46px}
.page-head{display:flex;flex-wrap:wrap;gap:10px;align-items:center;justify-content:space-between;margin:6px 0 16px}
.page-head h1{color:var(--primary);font-size:1.6rem}
.tag{background:#e8f5e9;color:var(--primary-dark);padding:6px 10px;border-radius:999px;font-weight:800;font-size:.9rem}

/* Cards */
.card{background:#fff;border:1px solid var(--border);border-radius:16px;padding:16px;margin:14px 0;box-shadow:0 6px 16px rgba(0,0,0,.06)}
.card h2{color:var(--primary);margin:0 0 12px;font-size:1.2rem}
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px}
@media (max-width:820px){ .grid-2,.grid-3{grid-template-columns:1fr} }
.input, textarea.input{
  width:100%;height:42px;border:1px solid var(--border);border-radius:10px;padding:0 12px;outline:none
}
textarea.input{height:auto;min-height:84px;padding:10px 12px;resize:vertical}
label.small{font-size:.9rem;color:#556;margin-bottom:4px;display:block}
.btn{height:42px;padding:0 16px;border:none;border-radius:10px;background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;font-weight:800;cursor:pointer}
.btn-ghost{background:#fff;border:1px solid var(--border);color:#214a26}
.btn-danger{background:#fff;border:1px solid #ef9a9a;color:#c62828}
.row-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:12px}

/* Avatar */
.profile-top{display:flex;gap:14px;align-items:center;margin-bottom:12px}
.avatar{
  width:64px;height:64px;border-radius:50%;display:grid;place-items:center;
  background:#e8f5e9;color:var(--primary-dark);font-weight:900;font-size:1.2rem;overflow:hidden
}
.avatar img{width:100%;height:100%;object-fit:cover;display:block}

/* Addresses */
.addr-list{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:12px}
.addr{border:1px solid var(--border);border-radius:12px;padding:12px;background:#fff;position:relative}
.addr .title{font-weight:800;color:#214a26;margin-bottom:4px}
.addr .meta{color:#6b7a6d;font-size:.9rem;margin-bottom:6px}
.addr .a-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px}
.addr .badge{position:absolute;top:10px;right:10px;background:#e8f5e9;color:var(--primary-dark);font-weight:800;padding:4px 8px;border-radius:999px;font-size:.78rem}

/* Orders (placeholder) */
.order-empty{padding:16px;border:1px dashed var(--border);border-radius:12px;background:#fff;color:#556}

/* Footer */
footer{background:var(--dark);color:#fff;margin-top:40px}
.footer-inner{padding:36px 0;text-align:center}
.footer-inner a{color:#ffeb3b;text-decoration:none}
/* ---- Logo container (reuses your .logo) ---- */
.logo{
  display:flex; align-items:center; gap:0px;
  text-decoration:none;
}

/* ---- Icon with gradient ring + soft glow ---- */
.logo-icon{
  width:100px; height:100px;
  position:relative; border-radius:12px;
}

.logo-icon img{
  position:relative; z-index:1;
  width:100%; height:100%; object-fit:contain; display:block;
  filter: saturate(1.1) contrast(1.02);
}

/* ---- Wordmark ---- */
.logo-wordmark{ display:flex; flex-direction:column; line-height:1; }
.logo-name{
  font-family: 'Poppins', system-ui, -apple-system, Segoe UI, Roboto, 'Open Sans', sans-serif;
  font-weight:800; letter-spacing:.2px;
  font-size: clamp(1.15rem, 2.2vw, 1.4rem);
  background: linear-gradient(135deg, var(--primary,#2e7d32), var(--primary-light,#4caf50));
  -webkit-background-clip: text; background-clip: text; color: transparent;
  text-shadow: 0 1px 0 rgba(255,255,255,.25);
}
.logo-tag{
  margin-top:4px;
  font-weight:700; font-size:.72rem; letter-spacing:.12em; text-transform:uppercase;
  color: #5c7a60;
  opacity:.95;
}

/* ---- Hover micro-interaction ---- */
.logo:hover .logo-icon{ transform: translateY(-1px); }
.logo:hover .logo-name{ filter: brightness(1.05); }

/* ---- Compact on small screens ---- */
@media (max-width: 560px){
  .logo{ gap:10px; }
  .logo-icon{ width:40px; height:40px; flex-basis:40px; }
  .logo-tag{ display:none; }   /* keep header tidy on phones */
}

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
      <a href="wishlist.php" class="header-action"><i class="fas fa-heart"></i><span>Wishlist</span></a>
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
      <div class="nav-offer"><a href="products.php" class="btn" style="padding:10px 18px">Special Offers</a></div>
    </div>
  </nav>
</header>

<!-- ===== MAIN ===== -->
<main>
  <div class="container">
    <div class="page-head">
      <h1>Account</h1>
      <span class="tag" id="wishTag">0 in wishlist</span>
    </div>

    <!-- Profile -->
    <section class="card">
      <h2>Profile</h2>
      <div class="profile-top">
        <div class="avatar" id="avatar"><span>VN</span></div>
        <div>
          <label class="small">Profile Photo</label>
          <input type="file" id="avatarInput" accept="image/*">
        </div>
      </div>
      <div class="grid-2">
        <div>
          <label class="small" for="name">Full name</label>
          <input class="input" id="name" placeholder="Full name">
        </div>
        <div>
          <label class="small" for="email">Email</label>
          <input class="input" id="email" placeholder="Email">
        </div>
        <div>
          <label class="small" for="phone">Phone</label>
          <input class="input" id="phone" placeholder="Phone">
        </div>
        <div>
          <label class="small" for="address">Address (quick)</label>
          <textarea class="input" id="address" placeholder="Address"></textarea>
        </div>
      </div>
      <div class="row-actions">
        <button class="btn" onclick="saveProfile()">Save</button>
        <button class="btn-ghost" onclick="exportData()">Export (.json)</button>
        <label class="btn-ghost" style="display:inline-grid;place-items:center;padding:0 14px;cursor:pointer">
          Import
          <input type="file" id="importFile" accept="application/json" style="display:none">
        </label>
        <button class="btn-danger" onclick="logoutLocal()">Log out (local)</button>
      </div>
      <small style="color:#667;display:block;margin-top:6px">Tip: This page uses local storage right now. Hook it to your real auth/db when ready.</small>
    </section>

    <!-- Addresses -->
    <section class="card">
      <h2>Addresses</h2>
      <div class="grid-3" id="addrForm">
        <div>
          <label class="small" for="alabel">Label</label>
          <input class="input" id="alabel" placeholder="e.g., Home / Office">
        </div>
        <div>
          <label class="small" for="acity">City</label>
          <input class="input" id="acity" placeholder="City">
        </div>
        <div>
          <label class="small" for="astate">State</label>
          <input class="input" id="astate" placeholder="State">
        </div>
        <div style="grid-column:1/-1">
          <label class="small" for="aline1">Address</label>
          <textarea class="input" id="aline1" placeholder="Flat/Street/Area"></textarea>
        </div>
        <div>
          <label class="small" for="apincode">Pincode</label>
          <input class="input" id="apincode" placeholder="Pincode">
        </div>
        <div class="row-actions">
          <button class="btn" onclick="addAddress()">Add address</button>
          <button class="btn-ghost" onclick="clearAddrForm()">Clear</button>
        </div>
      </div>

      <div class="addr-list" id="addrList" style="margin-top:12px"></div>
    </section>

    <!-- Orders (placeholder) -->
    <section class="card">
      <h2>Orders</h2>
      <div id="ordersBox" class="order-empty">No orders yet. <a href="products.php">Start shopping</a></div>
    </section>

    <!-- Data tools -->
    <section class="card">
      <h2>Data & Privacy</h2>
      <div class="row-actions">
        <button class="btn-ghost" onclick="copyAddressForWhatsApp()"><i class="fa-brands fa-whatsapp"></i>&nbsp;Share default address</button>
        <button class="btn-danger" onclick="clearAllLocal()">Clear ALL local data (profile, wishlist, cart, addresses)</button>
      </div>
    </section>
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

/* ===== Cart badge & wishlist count ===== */
function updateCartCount(){
  const cart = JSON.parse(localStorage.getItem('cart')||'[]');
  const total = cart.reduce((s,i)=> s+(i.qty||0), 0);
  const badge = document.getElementById('cartCount');
  if (badge) badge.textContent = total>0 ? total : 0;
}
function updateWishTag(){
  let w=[]; try{ w=JSON.parse(localStorage.getItem('wishlist'))||[] }catch{}
  const tag=document.getElementById('wishTag'); if(tag) tag.textContent=`${w.length} in wishlist`;
}
window.addEventListener('storage', e=>{ if(e.key==='cart') updateCartCount(); if(e.key==='wishlist') updateWishTag(); });

/* ===== Profile (local) ===== */
const KEY_PROF='vann_profile';
const KEY_AVA='avatarDataUrl';
const avatarEl=document.getElementById('avatar');
function initialsFromName(n){
  if(!n) return 'VN';
  const parts=n.trim().split(/\s+/).filter(Boolean).slice(0,2);
  return parts.map(p=>p[0].toUpperCase()).join('') || 'VN';
}
function renderAvatar(){
  const data=localStorage.getItem(KEY_AVA);
  avatarEl.innerHTML='';
  if(data){
    const img=new Image(); img.src=data; avatarEl.appendChild(img);
  }else{
    avatarEl.innerHTML='<span>'+initialsFromName(document.getElementById('name').value)+'</span>';
  }
}
document.getElementById('avatarInput').addEventListener('change', e=>{
  const f=e.target.files?.[0]; if(!f) return;
  const r=new FileReader();
  r.onload=()=>{ localStorage.setItem(KEY_AVA, r.result); renderAvatar(); };
  r.readAsDataURL(f);
});

function loadProfile(){
  try{
    const p=JSON.parse(localStorage.getItem(KEY_PROF))||{};
    ['name','email','phone','address'].forEach(k=>{ if(p[k]) document.getElementById(k).value=p[k]; });
  }catch{}
  renderAvatar();
}
function validateProfile(p){
  const errs=[];
  if(!p.name) errs.push('Name is required.');
  if(p.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(p.email)) errs.push('Invalid email.');
  if(p.phone && !/^\+?[0-9]{7,15}$/.test(p.phone.replace(/\s/g,''))) errs.push('Invalid phone.');
  return errs;
}
function saveProfile(){
  const p={
    name:name.value.trim(), email:email.value.trim(),
    phone:phone.value.trim(), address:address.value.trim()
  };
  const errs=validateProfile(p);
  if(errs.length){ alert(errs.join('\n')); return; }
  localStorage.setItem(KEY_PROF, JSON.stringify(p));
  renderAvatar();
  alert('Profile saved locally.');
}
function logoutLocal(){
  if(!confirm('This removes local profile (not a real sign-out). Continue?')) return;
  localStorage.removeItem(KEY_PROF); localStorage.removeItem(KEY_AVA); location.reload();
}

/* ===== Addresses (local) ===== */
const KEY_ADDR='vann_addresses';
function getAddrs(){ try{ return JSON.parse(localStorage.getItem(KEY_ADDR))||[] }catch{ return []; } }
function setAddrs(a){ localStorage.setItem(KEY_ADDR, JSON.stringify(a||[])); }
function clearAddrForm(){ ['alabel','acity','astate','aline1','apincode'].forEach(id=> document.getElementById(id).value=''); }
function addAddress(){
  const a={
    id: Date.now(),
    label: alabel.value.trim(),
    line1: aline1.value.trim(),
    city: acity.value.trim(),
    state: astate.value.trim(),
    pincode: apincode.value.trim(),
    isDefault: false
  };
  if(!a.label||!a.line1){ alert('Label and Address are required.'); return; }
  const list=getAddrs(); list.unshift(a); setAddrs(list); clearAddrForm(); renderAddrs();
}
function setDefaultAddr(id){
  const list=getAddrs().map(x=>({...x, isDefault: x.id===id}));
  setAddrs(list); renderAddrs();
}
function editAddr(id){
  const list=getAddrs(); const a=list.find(x=>x.id===id); if(!a) return;
  alabel.value=a.label; aline1.value=a.line1; acity.value=a.city; astate.value=a.state; apincode.value=a.pincode;
  // replace Add button behavior once: save changes then restore
  const btn= document.querySelector('#addrForm .btn');
  const old= btn.onclick;
  btn.textContent='Save changes';
  btn.onclick=()=>{
    a.label=alabel.value.trim(); a.line1=aline1.value.trim();
    a.city=acity.value.trim(); a.state=astate.value.trim(); a.pincode=apincode.value.trim();
    setAddrs(list); clearAddrForm(); renderAddrs(); btn.textContent='Add address'; btn.onclick=old;
  };
}
function delAddr(id){
  if(!confirm('Delete this address?')) return;
  const list=getAddrs().filter(x=>x.id!==id);
  setAddrs(list); renderAddrs();
}
function renderAddrs(){
  const box=document.getElementById('addrList'); box.innerHTML='';
  const list=getAddrs();
  if(!list.length){ box.innerHTML='<div class="order-empty">No addresses saved.</div>'; return; }
  box.innerHTML = list.map(a=>`
    <div class="addr">
      ${a.isDefault ? '<div class="badge">Default</div>' : ''}
      <div class="title">${(a.label||'Address')}</div>
      <div class="meta">${(a.line1||'')}<br>${(a.city||'')}${a.state?', '+a.state:''}${a.pincode?' - '+a.pincode:''}</div>
      <div class="a-actions">
        ${!a.isDefault ? `<button class="btn-ghost" onclick="setDefaultAddr(${a.id})">Set default</button>` : ''}
        <button class="btn-ghost" onclick="editAddr(${a.id})">Edit</button>
        <button class="btn-danger" onclick="delAddr(${a.id})">Delete</button>
        <button class="btn-ghost" onclick="shareOneAddr(${a.id})"><i class="fa-solid fa-share"></i> Share</button>
      </div>
    </div>
  `).join('');
}

/* Share default address (or specific) */
function shareText(text){
  if(navigator.share){ navigator.share({title:'My address', text}).catch(()=>{}); }
  else{ window.open('https://wa.me/?text='+encodeURIComponent(text),'_blank'); }
}
function shareOneAddr(id){
  const a=getAddrs().find(x=>x.id===id); if(!a) return;
  const t=`${a.label}\n${a.line1}\n${a.city}${a.state?', '+a.state:''}${a.pincode?' - '+a.pincode:''}`;
  shareText(t);
}
function copyAddressForWhatsApp(){
  const a=getAddrs().find(x=>x.isDefault) || getAddrs()[0];
  if(!a){ alert('No address saved.'); return; }
  const t=`${a.label}\n${a.line1}\n${a.city}${a.state?', '+a.state:''}${a.pincode?' - '+a.pincode:''}`;
  shareText(t);
}

/* ===== Export/Import JSON ===== */
function exportData(){
  const data={
    profile: JSON.parse(localStorage.getItem(KEY_PROF)||'{}'),
    avatar: localStorage.getItem(KEY_AVA)||'',
    addresses: getAddrs(),
    wishlist: JSON.parse(localStorage.getItem('wishlist')||'[]'),
    cart: JSON.parse(localStorage.getItem('cart')||'[]')
  };
  const blob=new Blob([JSON.stringify(data,null,2)], {type:'application/json'});
  const a=document.createElement('a');
  a.href=URL.createObjectURL(blob); a.download='vanniddhi_account_backup.json'; a.click();
  setTimeout(()=> URL.revokeObjectURL(a.href), 1000);
}
document.getElementById('importFile').addEventListener('change', e=>{
  const f=e.target.files?.[0]; if(!f) return;
  const r=new FileReader();
  r.onload=()=>{
    try{
      const obj=JSON.parse(r.result);
      if(obj.profile) localStorage.setItem(KEY_PROF, JSON.stringify(obj.profile));
      if(obj.avatar) localStorage.setItem(KEY_AVA, obj.avatar);
      if(obj.addresses) setAddrs(obj.addresses);
      if(obj.wishlist) localStorage.setItem('wishlist', JSON.stringify(obj.wishlist));
      if(obj.cart) localStorage.setItem('cart', JSON.stringify(obj.cart));
      alert('Imported successfully.');
      loadProfile(); renderAddrs(); updateWishTag(); updateCartCount();
    }catch{ alert('Invalid file.'); }
  };
  r.readAsText(f);
});

/* ===== Clear all local ===== */
function clearAllLocal(){
  if(!confirm('This will clear profile, addresses, wishlist and cart stored locally. Continue?')) return;
  localStorage.removeItem(KEY_PROF);
  localStorage.removeItem(KEY_AVA);
  localStorage.removeItem('wishlist');
  localStorage.removeItem('cart');
  localStorage.removeItem(KEY_ADDR);
  location.reload();
}

/* ===== Init ===== */
document.addEventListener('DOMContentLoaded', ()=>{
  updateCartCount();
  updateWishTag();
  loadProfile();
  renderAddrs();
});
</script>

</body>
</html>
