<?php
session_start();
if(!isset($_SESSION['admin'])) { header("Location: admin_login.php"); exit; }
include "db.php";

$adminUser = $_SESSION['admin'] ?? 'Admin';

// Stats
$total    = (int)$conn->query("SELECT COUNT(*) AS c FROM products")->fetch_assoc()['c'];
$featured = (int)$conn->query("SELECT COUNT(*) AS c FROM products WHERE featured=1")->fetch_assoc()['c'];
$lowstock = (int)$conn->query("SELECT COUNT(*) AS c FROM products WHERE online_stock < 5")->fetch_assoc()['c'];

// Recent activities (top 10)
$activities = $conn->query("SELECT action, created_at FROM activities ORDER BY created_at DESC LIMIT 10");

// Ticker messages (same as the rest of your site)
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
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Admin Dashboard • Vanniddhi</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
  :root{
    --primary:#2e7d32; --primary-light:#4caf50; --primary-dark:#1b5e20;
    --secondary:#ff9800; --accent:#8bc34a;
    --light:#f8fdf8; --dark:#1a331c; --text:#333; --white:#fff; --gray:#f5f5f5; --border:#e0e0e0;
  }
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Open Sans',sans-serif;background:var(--light);color:var(--text);line-height:1.6}

  /* ===== Top bar ticker ===== */
  .top-bar{background:var(--primary-dark);color:#fff;font-size:.92rem}
  .top-bar-content{min-height:40px;display:flex;align-items:center;gap:16px}
  .container{width:100%;max-width:1200px;margin:0 auto;padding:0 16px}
  .ticker-track{overflow:hidden;white-space:nowrap;flex:1}
  .ticker-line{display:inline-block;padding-left:100%;animation:ticker 28s linear infinite}
  @keyframes ticker{from{transform:translateX(0)} to{transform:translateX(-100%)}}
  .top-bar-links a{color:#fff;text-decoration:none;margin-left:18px}
  .top-bar-links a:hover{color:var(--accent)}

  /* ===== Header / Nav (site theme) ===== */
  header{background:#fff;box-shadow:0 2px 10px rgba(0,0,0,.08);position:sticky;top:0;z-index:1000}
  .header-container{display:flex;justify-content:space-between;align-items:center;padding:0px 0;gap:12px;flex-wrap:wrap}
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

  /* ===== Admin layout ===== */
  .wrap{display:grid;grid-template-columns:260px 1fr;gap:18px;align-items:start;padding:20px 16px 30px}
  @media (max-width:980px){ .wrap{grid-template-columns:1fr} }

  .sidebar{
    background:#fff;border:1px solid var(--border);border-radius:14px;padding:14px;
    position:sticky; top:86px;
  }
  .sb-title{font-family:'Poppins',sans-serif;color:var(--primary);font-size:1.1rem;margin-bottom:8px}
  .sb-links{display:flex;flex-direction:column;gap:8px;margin-top:8px}
  .sb-links a{
    display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;text-decoration:none;
    color:#2f3b33;font-weight:700;border:1px solid transparent;
  }
  .sb-links a:hover{background:#f4fbf4;border-color:#e0f2e0}
  .sb-links a.active{background:#e8f5e9;border-color:#cfe8cf;color:var(--primary-dark)}
  .sb-mobile{display:none;margin-bottom:10px}
  @media (max-width:980px){
    .sidebar{position:static}
    .sb-mobile{display:flex;justify-content:space-between;align-items:center}
  }

  .main{
    background:transparent
  }
  .main .head{
    display:flex;align-items:flex-end;justify-content:space-between;gap:10px;margin-top:6px
  }
  .main .head h1{font-family:'Poppins',sans-serif;color:#214a26;font-size:1.6rem}
  .sub{color:#6b7a6d;font-weight:700}

  /* Stats cards */
  .stats{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin:16px 0 10px}
  @media (max-width:760px){ .stats{grid-template-columns:1fr 1fr} }
  @media (max-width:520px){ .stats{grid-template-columns:1fr} }
  .card{
    background:#fff;border:1px solid var(--border);border-radius:14px;padding:16px;text-align:center;
    box-shadow:0 6px 16px rgba(0,0,0,.06);transition:transform .2s, box-shadow .2s;cursor:pointer
  }
  .card:hover{transform:translateY(-4px);box-shadow:0 14px 28px rgba(0,0,0,.12)}
  .card h2{font-size:1rem;color:#55645a;margin:0 0 8px}
  .card span{font-size:2rem;font-weight:900;color:var(--primary-dark)}

  /* Quick actions */
  .qa{display:flex;flex-wrap:wrap;gap:10px;margin:6px 0 22px}
  .btn{display:inline-grid;place-items:center;height:40px;padding:0 14px;border:none;border-radius:12px;font-weight:800;cursor:pointer;text-decoration:none}
  .btn-primary{background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;box-shadow:0 12px 20px rgba(46,125,50,.25)}
  .btn-outline{background:#fff;color:var(--primary);border:2px solid var(--primary)}
  .btn-plain{background:#fff;border:1px solid var(--border);color:#2f3b33}

  /* Recent activities */
  .activities{
    background:#fff;border:1px solid var(--border);border-radius:14px;padding:14px;box-shadow:0 6px 16px rgba(0,0,0,.06)
  }
  .activities h3{color:var(--primary);font-family:'Poppins',sans-serif;margin-bottom:8px}
  .activities ul{list-style:none}
  .activities li{padding:10px 6px;border-bottom:1px solid #f0f0f0}
  .activities li:last-child{border-bottom:none}
  .activities small{color:#7d897e}

  /* Details overlay */
  .details-box{
    position:fixed; inset:auto 0 0 0; top:60px; margin:auto; width:min(100%, 1100px); max-height:78vh;
    background:#fff;border:1px solid var(--border);border-radius:14px;box-shadow:0 18px 40px rgba(0,0,0,.25);
    padding:14px; z-index:1400; display:none; overflow:auto
  }
  .db-head{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
  .db-head h3{color:var(--primary);font-family:'Poppins',sans-serif;margin-right:auto}
  .db-tools{display:flex;gap:8px;align-items:center}
  .filter{height:38px;border:1px solid var(--border);border-radius:10px;padding:0 12px}
  .close-btn{height:38px;padding:0 12px;border:none;border-radius:10px;background:#e53935;color:#fff;font-weight:800;cursor:pointer}
  .db-content{margin-top:10px;overflow:auto}
  .db-content table{width:100%;border-collapse:collapse}
  .db-content th,.db-content td{padding:10px;border-bottom:1px solid #eee;text-align:left;font-size:.95rem}
  .export{height:38px;padding:0 12px;border:none;border-radius:10px;background:#1b5e20;color:#fff;font-weight:800;cursor:pointer}

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
       <a href="index.php" class="logo" aria-label="Vanniddhi – home">
 <div class="logo-icon">
    <img src="vanniddhi.png" alt="Vanniddhi logo" class="logo-img">
  </div>
  <span class="logo-wordmark">
    <span class="logo-name">Vanniddhi</span>
    <span class="logo-tag">Plant Nursery</span>
  </span>
</a>

      <div class="search-bar">
        <input type="text" placeholder="Search products, orders... (UI only)">
        <button><i class="fas fa-search"></i></button>
      </div>

      <div class="header-actions">
        <a href="admin_dashboard.php" class="header-action" style="color:var(--primary)"><i class="fas fa-shield-alt"></i><span><?= htmlspecialchars($adminUser) ?></span></a>
        <a href="logout.php" class="header-action"><i class="fas fa-right-from-bracket"></i><span>Logout</span></a>
      </div>

      <div class="mobile-menu"><i class="fas fa-bars"></i></div>
    </div>
    <nav>
      <div class="container nav-container">
        <ul class="nav-links">
          <li><a href="index.php">Home</a></li>
          <li><a href="products.php">Products</a></li>
          <li><a href="cart.php">Cart</a></li>
          <li><a href="admin_dashboard.php" style="color:var(--primary);font-weight:700">Admin</a></li>
        </ul>
        <div class="nav-offer"><a href="products.php" class="btn btn-primary" style="height:38px;padding:0 16px">Special Offers</a></div>
      </div>
    </nav>
  </header>

  <!-- ===== ADMIN WRAP ===== -->
  <main class="container">
    <div class="wrap">
      <!-- Sidebar -->
      <aside class="sidebar">
        <div class="sb-mobile">
          <div class="sb-title">Admin Panel</div>
          <a class="btn btn-plain" href="logout.php" title="Logout">🚪 Logout</a>
        </div>
        <div class="sb-title" style="display:none">Admin Panel</div>
        <nav class="sb-links">
          <a href="admin_dashboard.php" class="active"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
          <a href="admin_products.php"><i class="fa-solid fa-seedling"></i> Products</a>
          <a href="admin_orders.php"><i class="fa-solid fa-box"></i> Orders</a>
          <a href="admin_check.php"><i class="fa-solid fa-clipboard-check"></i> Order Check</a>
          <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </nav>
      </aside>

      <!-- Main -->
      <section class="main">
        <div class="head">
          <div>
            <h1>Dashboard</h1>
            <div class="sub">Welcome back, <?= htmlspecialchars($adminUser) ?>!</div>
          </div>
          <div class="qa">
            <a class="btn btn-outline" href="admin_products.php">Manage Products</a>
            <a class="btn btn-outline" href="admin_orders.php">Manage Orders</a>
            <a class="btn btn-outline" href="admin_check.php">Order Check</a>
          </div>
        </div>

        <!-- Stats -->
        <div class="stats">
          <div class="card" onclick="showDetails('all')" title="Click to view">
            <h2>Total Products</h2>
            <span><?= $total ?></span>
          </div>
          <div class="card" onclick="showDetails('featured')" title="Click to view">
            <h2>Featured Products</h2>
            <span><?= $featured ?></span>
          </div>
          <div class="card" onclick="showDetails('lowstock')" title="Click to view">
            <h2>Low Stock (&lt; 5)</h2>
            <span><?= $lowstock ?></span>
          </div>
        </div>

        <!-- Recent Activities -->
        <div class="activities">
          <h3>Recent Activities</h3>
          <ul>
            <?php if($activities && $activities->num_rows): ?>
              <?php while($row = $activities->fetch_assoc()): ?>
                <li>
                  <?= htmlspecialchars($row['action']) ?>
                  <br><small><?= htmlspecialchars($row['created_at']) ?></small>
                </li>
              <?php endwhile; ?>
            <?php else: ?>
              <li>No recent activity.</li>
            <?php endif; ?>
          </ul>
        </div>
      </section>
    </div>
  </main>

  <!-- Details overlay -->
  <div class="details-box" id="detailsBox" role="dialog" aria-modal="true" aria-labelledby="detailsTitle">
    <div class="db-head">
      <h3 id="detailsTitle">Details</h3>
      <div class="db-tools">
        <input id="dbFilter" class="filter" type="search" placeholder="Filter rows…">
        <button class="export" id="exportBtn" type="button"><i class="fa-solid fa-file-arrow-down"></i>&nbsp; CSV</button>
        <button class="close-btn" onclick="closeDetails()" type="button">Close ✖</button>
      </div>
    </div>
    <div id="detailsContent" class="db-content"></div>
  </div>

  <!-- Footer -->
  <footer>
    <div class="container footer-inner">
      <p>&copy; <?= date('Y') ?> Vanniddhi Plant Nursery. All rights reserved.</p>
    </div>
  </footer>

<script>
  // Mobile nav toggle
  (function(){
    const mobileMenu = document.querySelector('.mobile-menu');
    const navLinks = document.querySelector('.nav-links');
    if(!mobileMenu || !navLinks) return;
    mobileMenu.addEventListener('click', ()=> navLinks.classList.toggle('show'));
    navLinks.addEventListener('click', e=>{ if(e.target.closest('a')) navLinks.classList.remove('show'); });
  })();

  // Cart badge
  function updateCartCount(){
    const cart = JSON.parse(localStorage.getItem('cart')||'[]');
    const total = cart.reduce((s,i)=> s + (i.qty||0), 0);
    const badge = document.getElementById('cartCount');
    if (badge) badge.textContent = total>0 ? total : 0;
  }
  window.addEventListener('storage', e=>{ if(e.key==='cart') updateCartCount(); });
  document.addEventListener('DOMContentLoaded', updateCartCount);

  // ===== Details overlay logic =====
  const box = document.getElementById('detailsBox');
  const content = document.getElementById('detailsContent');
  const titleEl = document.getElementById('detailsTitle');
  const filterInput = document.getElementById('dbFilter');
  const exportBtn = document.getElementById('exportBtn');

  function showDetails(type){
    if(type==='all')      titleEl.textContent = 'All Products';
    if(type==='featured') titleEl.textContent = 'Featured Products';
    if(type==='lowstock') titleEl.textContent = 'Low Stock Products';

    content.innerHTML = '<div style="padding:10px">Loading…</div>';
    box.style.display = 'block';
    document.body.style.overflow = 'hidden';

    fetch('admin_products_ajax.php?filter='+encodeURIComponent(type))
      .then(r => r.text())
      .then(html => { content.innerHTML = html; })
      .catch(()=> { content.innerHTML = '<div style="padding:10px;color:#a33">Failed to load data.</div>'; });
  }
  function closeDetails(){
    box.style.display = 'none';
    document.body.style.overflow = '';
    filterInput.value = '';
  }
  document.addEventListener('keydown', e=>{
    if(e.key==='Escape' && box.style.display==='block') closeDetails();
  });
  box.addEventListener('click', e=>{
    // click outside content closes (only if empty area)
    if(e.target === box) closeDetails();
  });

  // Quick filter (client-side) for any table returned
  filterInput.addEventListener('input', ()=>{
    const q = filterInput.value.trim().toLowerCase();
    const table = content.querySelector('table');
    if(!table) return;
    const rows = table.querySelectorAll('tbody tr, tr'); // fallback if no <tbody>
    rows.forEach(tr=>{
      const txt = tr.textContent.toLowerCase();
      tr.style.display = txt.includes(q) ? '' : 'none';
    });
  });

  // Export CSV of the first table in details
  exportBtn.addEventListener('click', ()=>{
    const table = content.querySelector('table');
    if(!table){ alert('No table to export.'); return; }
    const rows = Array.from(table.querySelectorAll('tr'))
      .map(tr => Array.from(tr.querySelectorAll('th,td'))
        .map(td => {
          let t = td.innerText.replace(/\r?\n+/g,' ').trim();
          // escape quotes
          t = '"'+t.replace(/"/g,'""')+'"';
          return t;
        }).join(','));
    const csv = rows.join('\r\n');
    const blob = new Blob([csv], {type:'text/csv;charset=utf-8;'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    const stamp = new Date().toISOString().slice(0,19).replace(/[:T]/g,'-');
    a.download = (titleEl.textContent || 'export') + '_' + stamp + '.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  });

  // (Optional) dummy search bar in header
  document.querySelector('.search-bar button')?.addEventListener('click', ()=>{
    alert('This is a UI search bar for now. Wire it to your admin search when ready.');
  });
</script>

<!-- (Optional) Google translate for parity -->
<script type="text/javascript">
  function googleTranslateElementInit() {
    new google.translate.TranslateElement({
      pageLanguage: 'en',
      includedLanguages: 'en,hi',
      layout: google.translate.TranslateElement.InlineLayout.SIMPLE
    }, 'google_translate_element');
  }
</script>
<script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</body>
</html>
