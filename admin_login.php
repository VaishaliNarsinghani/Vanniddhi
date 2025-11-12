<?php
session_start();
include "db.php";

/* ---------- Small hardening ---------- */
if (!isset($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$error = null;

/* If already logged in, go to dashboard */
if (!empty($_SESSION['admin'])) {
  header("Location: admin_dashboard.php");
  exit;
}

/* Handle login */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';
  $csrf = $_POST['csrf'] ?? '';

  if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrf)) {
    $error = "Invalid session. Please try again.";
  } else {
    $stmt = $conn->prepare("SELECT id, username, password FROM admin WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
      $stored = $row['password'];
      $ok = false;
      // Prefer password_hash() / password_verify() if your DB is migrated
      if (password_verify($password, $stored)) $ok = true;
      // Backward-compatible md5 fallback (legacy)
      else if (hash_equals($stored, md5($password))) $ok = true;

      if ($ok) {
        session_regenerate_id(true);
        $_SESSION['admin'] = $row['username'];

        // Log activity AFTER successful login
        $admin_user = $conn->real_escape_string($row['username']);
        $action = $conn->real_escape_string("Admin logged in");
        $conn->query("INSERT INTO activities (admin_user, action) VALUES ('$admin_user', '$action')");

        header("Location: admin_dashboard.php");
        exit;
      } else {
        $error = "Invalid credentials!";
      }
    } else {
      $error = "Invalid credentials!";
    }
    // Rotate CSRF either way
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
}

/* Top-bar ticker (same content you use across pages) */
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
  <title>Admin Login • Vanniddhi</title>

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

    /* ===== Header / Nav (same theme) ===== */
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

    /* ===== Login layout ===== */
    .wrap{display:grid;place-items:center;padding:36px 16px}
    .auth-card{
      width:100%; max-width:420px; background:#fff; border:1px solid var(--border); border-radius:16px;
      box-shadow:0 10px 24px rgba(0,0,0,.08); padding:20px 18px 18px;
    }
    .auth-head{display:flex;align-items:center;gap:10px;margin-bottom:10px}
    .auth-head .badge{margin-left:auto;background:#e8f5e9;color:var(--primary-dark);padding:4px 10px;border-radius:999px;font-weight:800;font-size:.82rem}
    .auth-card h1{font-size:1.4rem;color:var(--primary);margin:0}
    .field{margin-top:12px}
    .label{display:flex;align-items:center;justify-content:space-between;color:#576160;font-weight:700;font-size:.92rem;margin-bottom:6px}
    .input{width:100%;height:44px;border:1px solid var(--border);border-radius:12px;padding:0 14px;outline:none}
    .input:focus{border-color:var(--primary)}
    .pw-wrap{position:relative}
    .pw-toggle{
      position:absolute; right:10px; top:50%; transform:translateY(-50%);
      border:none; background:transparent; color:#6f7b71; cursor:pointer; font-size:1rem;
    }
    .btn{width:100%;height:44px;border:none;border-radius:12px;cursor:pointer;font-weight:800;letter-spacing:.2px}
    .btn-primary{background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;box-shadow:0 12px 20px rgba(46,125,50,.25)}
    .btn-primary:hover{filter:brightness(.98)}
    .help-row{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-top:10px}
    .link{color:#1b5e20;text-decoration:none;font-weight:700}
    .error{background:#ffebee;border:1px solid #ffcdd2;color:#c62828;padding:10px;border-radius:10px;margin-bottom:10px;font-weight:700}
    .hint{color:#667;font-size:.9rem;margin-top:10px}

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

  <!-- ===== HEADER ===== -->
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
        <input type="text" placeholder="Search for plants, seeds, pots and more...">
        <button><i class="fas fa-search"></i></button>
      </div>

      <div class="header-actions">
        <a href="wishlist.php" class="header-action"><i class="fas fa-heart"></i><span>Wishlist</span></a>
        <a href="admin_login.php" class="header-action" style="color:var(--primary)"><i class="fas fa-shield-alt"></i><span>Admin</span></a>
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
          <li><a href="products.php">Products</a></li>
          <li><a href="products.php?category=Gardening%20Tools">Gardening Tools</a></li>
          <li><a href="products.php?category=Soil%20%26%20Fertilizers">Soil & Fertilizers</a></li>
        </ul>
        <div class="nav-offer"><a href="products.php" class="btn btn-primary" style="height:38px;display:inline-grid;place-items:center;padding:0 16px">Special Offers</a></div>
      </div>
    </nav>
  </header>

  <!-- ===== LOGIN CARD ===== -->
  <main class="wrap container">
    <div class="auth-card">
      <div class="auth-head">
        <h1>Admin Login</h1>
        <span class="badge">Secure</span>
      </div>

      <?php if(!empty($error)): ?>
        <div class="error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="post" autocomplete="off" novalidate>
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <div class="field">
          <label class="label" for="username">Username</label>
          <input class="input" id="username" name="username" type="text" placeholder="Admin username" required autofocus>
        </div>

        <div class="field">
          <label class="label" for="password">Password</label>
          <div class="pw-wrap">
            <input class="input" id="password" name="password" type="password" placeholder="Password" required>
            <button class="pw-toggle" type="button" aria-label="Show password" onclick="togglePw()">
              <i class="fa-regular fa-eye"></i>
            </button>
          </div>
        </div>

        <div class="help-row">
          <label style="display:flex;gap:8px;align-items:center;font-weight:700;color:#576160">
            <input type="checkbox" id="remember" style="accent-color:var(--primary)"> Remember username
          </label>
          <a href="#" class="link" onclick="alert('Ask your super admin to reset your password.');return false;">Forgot password?</a>
        </div>

        <div class="field" style="margin-top:14px">
          <button class="btn btn-primary" type="submit">Log in</button>
        </div>
      </form>

      <div class="hint"><i class="fa-solid fa-lock"></i> Tip: Migrate your admin table to <code>password_hash()</code> for stronger security. This form still accepts old MD5 hashes so you can migrate smoothly.</div>
    </div>
  </main>

  <!-- ===== FOOTER ===== -->
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
      const total = cart.reduce((s,i)=> s+(i.qty||0), 0);
      const badge = document.getElementById('cartCount');
      if (badge) badge.textContent = total>0 ? total : 0;
    }
    window.addEventListener('storage', e=>{ if(e.key==='cart') updateCartCount(); });
    document.addEventListener('DOMContentLoaded', updateCartCount);

    // Show/hide password
    function togglePw(){
      const ip = document.getElementById('password');
      const btn = event.currentTarget;
      if(ip.type==='password'){ ip.type='text'; btn.innerHTML='<i class="fa-regular fa-eye-slash"></i>'; }
      else { ip.type='password'; btn.innerHTML='<i class="fa-regular fa-eye"></i>'; }
    }

    // Remember username (local)
    (function rememberInit(){
      const KEY='admin_u';
      const u = localStorage.getItem(KEY);
      if(u){ document.getElementById('username').value = u; document.getElementById('remember').checked = true; }
      document.getElementById('remember').addEventListener('change', e=>{
        if(e.target.checked){
          localStorage.setItem(KEY, document.getElementById('username').value.trim());
        }else{
          localStorage.removeItem(KEY);
        }
      });
      document.getElementById('username').addEventListener('input', ()=>{
        if(document.getElementById('remember').checked){
          localStorage.setItem(KEY, document.getElementById('username').value.trim());
        }
      });
    })();
  </script>

  <!-- (Optional) Google translate for parity with other pages -->
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
