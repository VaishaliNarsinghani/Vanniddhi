<?php
include("db.php");

/* ========= CATEGORY SETUP =========
   Slug => Human label (keep in sync with admin_products.php)
*/
$CATEGORY_MAP = [
  'preserved-nature-tabletops' => 'Preserved Nature Tabletops',
  'plants'                     => 'Plants',
  'flower-bulbs'               => 'Flower Bulbs',
  'moss-flowers'               => 'Moss Flowers',
  'succulents'                 => 'Succulents',
  'air-purifying'              => 'Air Purifying',
  'seeds'                      => 'Seeds',
  'pots-and-planters'          => 'Pots & Planters',
];

/* Helper: slugify a label so dataset/category tabs match reliably */
function cat_slug($label){
  $s = strtolower(trim($label));
  $s = preg_replace('/&/',' and ', $s);
  $s = preg_replace('/[^a-z0-9]+/','-', $s);
  $s = preg_replace('/-+/','-', $s);
  return trim($s, '-');
}

/* ========= READ FILTERS FROM URL ========= */
$category = isset($_GET['category']) ? strtolower(trim($_GET['category'])) : 'all';
$q        = isset($_GET['q']) ? trim($_GET['q']) : '';

/* ========= BUILD SQL (server-side filter; JS will still do client polish) ========= */
$where  = [];
$params = [];
$types  = '';

if ($category !== '' && $category !== 'all') {
  // Try to match by human label; if your DB has a slug column, switch to category_slug = ?
  $label   = $CATEGORY_MAP[$category] ?? $category; // fallback
  $where[] = "category = ?";
  $params[] = $label; 
  $types   .= 's';
}

if ($q !== '') {
  $where[]  = "(name LIKE ? OR description LIKE ?)";
  $like     = "%{$q}%";
  $params[] = $like; $types .= 's';
  $params[] = $like; $types .= 's';
}

$sql = "SELECT * FROM products";
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
  die("Query prepare failed.");
}
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$products = $stmt->get_result();

/* For labels/UI */
$currentLabel = ($category === 'all') ? 'All Products' : ($CATEGORY_MAP[$category] ?? $category);

/* ========= TICKER (unchanged) ========= */
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
/* ========= READ FILTERS FROM URL (robust) ========= */
$q      = isset($_GET['q']) ? trim($_GET['q']) : '';
$rawCat = $_GET['category'] ?? 'all';

/* Accept slugs and human labels; allow common variants */
$CATEGORY_SYNONYMS = [
  'air-purifying-plants' => 'air-purifying',
  'air-purifying'        => 'air-purifying',
  'pots-and-planter'     => 'pots-and-planters',
  'pots-planters'        => 'pots-and-planters',
  'succulents-cacti'     => 'succulents',
  'succulents-and-cacti' => 'succulents',
];

$CATEGORY_DB_VARIANTS = [
  'preserved-nature-tabletops' => ['Preserved Nature Tabletops', 'Preserved Nature'],
  'plants'                     => ['Plants'],
  'flower-bulbs'               => ['Flower Bulbs'],
  'moss-flowers'               => ['Moss Flowers', 'Moss Frames'],
  'succulents'                 => ['Succulents', 'Succulents & Cacti', 'Succulents and Cacti'],
  'air-purifying'              => ['Air Purifying', 'Air Purifying Plants'],
  'seeds'                      => ['Seeds'],
  'pots-and-planters'          => ['Pots & Planters', 'Pots and Planters'],
];

function slugify_for_compare($s){
  $s = urldecode($s);
  $s = str_replace('+',' ',$s);
  $s = strtolower(trim($s));
  $s = str_replace('&','and',$s);
  $s = preg_replace('/[^a-z0-9]+/','-',$s);
  $s = preg_replace('/-+/','-',$s);
  return trim($s,'-');
}
function resolve_slug($raw,$map,$syn){
  if(!$raw) return 'all';
  $s = slugify_for_compare($raw);
  if($s==='all') return 'all';
  if(isset($map[$s])) return $s;
  if(isset($syn[$s])) return $syn[$s];
  foreach($map as $slug=>$label){ if(slugify_for_compare($label)===$s) return $slug; }
  return 'all';
}

$category = resolve_slug($rawCat, $CATEGORY_MAP, $CATEGORY_SYNONYMS);

/* ========= BUILD SQL (forgiving category match) ========= */
$whereParts = [];

/* Category */
if ($category !== 'all') {
  $label = $CATEGORY_MAP[$category];
  $alts  = $CATEGORY_DB_VARIANTS[$category] ?? [$label];

  $eqs = [];
  foreach ($alts as $lab) {
    $eqs[] = "LOWER(TRIM(category)) = '" . $conn->real_escape_string(strtolower(trim($lab))) . "'";
  }

  // Plus word-based fallback: every word from the label must appear in category
  $tokens = preg_split('/[\s\-]+/', strtolower($label));
  $tokens = array_values(array_unique(array_diff($tokens, ['and','&',''])));
  $likes = [];
  foreach ($tokens as $t) {
    $likes[] = "LOWER(category) LIKE '%" . $conn->real_escape_string($t) . "%'";
  }

  $whereParts[] = '(' . implode(' OR ', $eqs) . ( $likes ? ' OR ('.implode(' AND ', $likes).')' : '' ) . ')';
}

/* Search (optional) */
if ($q !== '') {
  $qe = $conn->real_escape_string($q);
  $whereParts[] = "(name LIKE '%$qe%' OR category LIKE '%$qe%')";
}

$whereSQL = $whereParts ? ' WHERE ' . implode(' AND ', $whereParts) : '';

/* Sort */
$sort = $_GET['sort'] ?? 'latest';
switch ($sort) {
  case 'price_asc':  $orderBy = 'price ASC'; break;
  case 'price_desc': $orderBy = 'price DESC'; break;
  case 'name_asc':   $orderBy = 'name ASC'; break;
  default:           $orderBy = 'id DESC';
}

/* Query */
$sql = "SELECT * FROM products{$whereSQL} ORDER BY {$orderBy}";
$products = $conn->query($sql);
if (!$products) { die('DB error: '.$conn->error); }

/* Label for UI chips/header */
$currentLabel = ($category==='all') ? 'All Products'
                                   : ($CATEGORY_MAP[$category] ?? ucwords(str_replace('-',' ',$category)));

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>All Products • Vanniddhi</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">

<style>
  /* ===== Vanniddhi theme (same as index.php) ===== */
  :root{
    --primary:#2e7d32; --primary-light:#4caf50; --primary-dark:#1b5e20;
    --secondary:#ff9800; --accent:#8bc34a;
    --light:#f8fdf8; --dark:#1a331c; --text:#333; --white:#fff; --gray:#f5f5f5; --border:#e0e0e0;
  }
  *{ box-sizing:border-box; margin:0; padding:0 }
  html{ scroll-behavior:smooth }
  body{ font-family:'Open Sans',sans-serif; color:var(--text); background:var(--light); line-height:1.6; overflow-x:hidden }
  h1,h2,h3{ font-family:'Poppins',sans-serif; font-weight:600; line-height:1.3 }
  .container{ width:100%; max-width:1400px; margin:0 auto; padding:0 20px }
  section{ padding:70px 0 }

  .btn{ display:inline-block; padding:12px 28px; border-radius:10px; border:none; cursor:pointer; font-weight:700; transition:.25s; text-decoration:none; }
  .btn-primary{ background:linear-gradient(135deg,var(--primary),var(--primary-light)); color:#fff; box-shadow:0 10px 18px rgba(46,125,50,.25) }
  .btn-primary:hover{ filter:brightness(.98); transform:translateY(-2px) }

  .section-title{ text-align:center; margin-bottom:34px }
  .section-title h2{ color:var(--primary); font-size:2rem; margin-bottom:8px; display:inline-block; position:relative }
  .section-title h2::after{ content:""; position:absolute; left:50%; bottom:-10px; transform:translateX(-50%); width:78px; height:3px; background:var(--secondary) }
  .section-title p{ max-width:760px; margin:0 auto; color:#4a5a4a }

  /* ===== Top bar ticker (same look as index) ===== */
  .top-bar{ background:var(--primary-dark); color:#fff; font-size:.92rem }
  .top-bar-content{ min-height:40px; display:flex; align-items:center; gap:16px }
  .ticker-track{ overflow:hidden; white-space:nowrap; flex:1 }
  .ticker-line{ display:inline-block; padding-left:100%; animation:ticker 28s linear infinite }
  @keyframes ticker{ from{transform:translateX(0)} to{transform:translateX(-100%)} }
  .top-bar-links a{ color:#fff; text-decoration:none; margin-left:18px }
  .top-bar-links a:hover{ color:var(--accent) }

  /* ===== Header/Nav (from index) ===== */
  header{ background:#fff; box-shadow:0 2px 10px rgba(0,0,0,.08); position:sticky; top:0; z-index:1000 }
  .header-container{ display:flex; justify-content:space-between; align-items:center; padding:0px 0; gap:12px; flex-wrap:wrap }
  .logo{ display:flex; align-items:center; gap:10px; text-decoration:none }
  .logo-icon{ font-size:2rem; color:var(--primary) }
  .logo-text{ font-size:1.6rem; font-weight:700; color:var(--primary) }

  .search-bar{ flex:1; max-width:1120px; position:relative; order:3; width:100% }
  .search-bar input{ width:100%; height:44px; padding:0 16px; border:1px solid var(--border); border-radius:12px; outline:none }
  .search-bar input:focus{ border-color:var(--primary) }
  .search-bar button{ position:absolute; right:6px; top:5px; height:34px; padding:0 14px; border:none; border-radius:10px; cursor:pointer; background:var(--primary); color:#fff }

  .header-actions{ display:flex; align-items:center }
  .header-action{ position:relative; margin-left:20px; display:flex; flex-direction:column; align-items:center; color:var(--text); text-decoration:none }
  .header-action i{ font-size:1.4rem; margin-bottom:4px }
  .header-action:hover{ color:var(--primary) }
  #cartCount{ position:absolute; top:-8px; right:-8px; width:18px; height:18px; border-radius:50%; background:var(--secondary); color:#fff; display:flex; align-items:center; justify-content:center; font-size:.7rem; font-weight:800 }

  nav{ background:var(--gray); padding:15px 0 }
  .nav-container{ display:flex; justify-content:space-between; align-items:center }
  .nav-links{ list-style:none; display:flex; gap:15px }
  .nav-links a{ color:var(--text); text-decoration:none; font-weight:600 }
  .nav-links a:hover{ color:var(--primary) }
  .mobile-menu{ display:none; font-size:1.5rem; color:var(--primary); cursor:pointer }

  @media (max-width:992px){
    .mobile-menu{ display:block }
    .nav-links{ display:none; position:fixed; inset:60px 0 0 0; background:#fff; padding:18px 20px; flex-direction:column; gap:12px; overflow:auto; z-index:1200; box-shadow:0 12px 30px rgba(0,0,0,.12) }
    .nav-links.show{ display:flex }
  }
  @media (max-width:576px){ .header-action span{ display:none } }

  /* ===== Products page hero heading ===== */
  .shop-hero{ background:linear-gradient(180deg,#ffffff, #f4fbf4) }
  .shop-hero .intro{ text-align:center; padding:18px 0 6px }
  .shop-hero .intro h1{ color:var(--primary); font-size:2rem; margin-bottom:6px }
  .shop-hero .intro p{ color:#627062 }

  /* ===== Search section ===== */
  .search-section{
    background:linear-gradient(135deg,#f2fbf2 0%, #eaf7ea 100%);
    border-left:4px solid var(--primary);
    border-radius:16px; padding:22px; margin:22px 0;
    box-shadow:0 6px 20px rgba(46,125,50,.12); position:relative; overflow:hidden;
  }
  .search-header{ text-align:center; margin-bottom:14px }
  .search-title{ color:var(--primary); font-weight:700; font-size:1.6rem }
  .filters.search-field{ display:flex; max-width:560px; margin:0 auto; background:#fff; border-radius:14px; padding:3px; border:2px solid transparent; box-shadow:0 3px 12px rgba(0,0,0,.06); }
  .filters.search-field:focus-within{ border-color:var(--primary); box-shadow:0 8px 22px rgba(46,125,50,.18) }
  .search-input{ flex:1; border:none; outline:none; padding:12px 16px; background:transparent; font-size:15px; border-radius:12px }
  .search-btn{ background:linear-gradient(135deg,var(--primary),var(--primary-light)); color:#fff; border:none; border-radius:12px; padding:10px 18px; cursor:pointer; font-weight:700 }

  /* Toolbar (NEW: sort + count) */
  .shop-toolbar{
    display:flex; align-items:center; justify-content:space-between;
    gap:16px; margin:10px 0 4px;
  }
  .results-label{ color:#607568; font-weight:700 }
  .sort-wrap{ display:flex; align-items:center; gap:8px }
  .sort-wrap select{
    height:40px; border:1px solid var(--border); border-radius:10px; padding:0 12px; background:#fff;
    font-weight:700; color:#2e3c2f;
  }

  /* Tabs => chips */
  .category-tabs{ display:flex; justify-content:center; flex-wrap:wrap; gap:8px; margin:14px 0 6px }
  .category-tabs .tab{ padding:8px 14px; border-radius:999px; border:1px solid #cfe2cf; background:#fff; color:#305b34; font-weight:700; cursor:pointer }
  .category-tabs .tab.active{ background:var(--primary); color:#fff; border-color:var(--primary); box-shadow:0 10px 18px rgba(46,125,50,.24) }

  /* Product grid/cards */
  .product-grid{ display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:22px; padding:6px 0 }
  .product-card{
    position:relative; border-radius:18px; overflow:hidden; cursor:pointer; background:#fff;
    border:1px solid var(--border); box-shadow:0 8px 18px rgba(0,0,0,.06);
    transition:transform .3s cubic-bezier(.22,.61,.36,1), box-shadow .3s;
  }
  .product-card:hover{ transform:translateY(-8px); box-shadow:0 22px 46px rgba(0,0,0,.14) }

  .product-image{ height:230px; position:relative; overflow:hidden; background:#f2f5f2 }
  .product-image img{ width:100%; height:100%; object-fit:cover; display:block; transition:transform .6s cubic-bezier(.22,.61,.36,1), filter .35s }
  .product-card:hover .product-image img{ transform:scale(1.07); filter:brightness(1.02) }
  .product-image video{ position:absolute; inset:0; width:100%; height:100%; object-fit:cover; opacity:0; transition:opacity .3s ease; filter:saturate(1.04) contrast(1.02) }
  .product-card:hover .product-image video{ opacity:1 }

  .product-details{ padding:14px 14px 16px; display:flex; flex-direction:column; gap:8px }
  .product-details h3{
    color:var(--primary); font-size:1.05rem; margin:0;
    display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;
  }
  .product-details .category{
    width:max-content; padding:4px 10px; border-radius:999px;
    background:#e8f5e9; color:var(--primary-dark); font-size:.78rem; font-weight:800
  }
  .product-details .desc{ color:#65706b; font-size:.92rem; line-height:1.45; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden }
  .product-details .price{ color:var(--primary-dark); font-weight:900; font-size:1.12rem; margin:4px 0 0 }

  .actions{ display:flex; align-items:center; justify-content:space-between; margin-top:8px; gap:10px }
  .qty-input{ width:58px; height:40px; padding:6px 8px; border:1px solid var(--border); border-radius:10px; text-align:center; font-weight:700 }
  .add-to-cart{ background:linear-gradient(135deg,var(--primary),var(--primary-light)); color:#fff; border:none; height:40px; padding:0 16px; border-radius:12px; font-weight:800; cursor:pointer; box-shadow:0 10px 18px rgba(46,125,50,.25) }
  .add-to-cart:hover{ filter:brightness(.98) }

  /* Wishlist heart (NEW) */
  .wish{
    position:absolute; top:12px; right:12px; z-index:3;
    width:42px; height:42px; border:none; border-radius:50%;
    display:grid; place-items:center; cursor:pointer;
    background:rgba(255,255,255,.92); color:#999; box-shadow:0 8px 16px rgba(0,0,0,.12);
  }
  .wish i{ pointer-events:none }
  .wish.active{ color:#e53935; background:#fff }

  .no-results{ grid-column:1/-1; text-align:center; color:#6b6b6b; background:#fff; border:1px dashed #cfe2cf; border-radius:14px; padding:22px }

  /* Load more (NEW) */
  .load-more-wrap{ display:flex; justify-content:center; margin:18px 0 8px }

  /* Footer (match index) */
  footer{ background:var(--dark); color:#fff; padding:70px 0 30px; margin-top:40px }
  .footer-container{ display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:40px; margin-bottom:40px }
  .copyright{ text-align:center; padding-top:24px; border-top:1px solid rgba(255,255,255,.12); color:#bbb; font-size:.92rem }

  /* ===== Responsive polish ===== */
  @media (max-width:640px){
    .product-grid{ grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px }
    .product-image{ height:200px }
    .product-details{ padding:12px }
    .product-details .desc{ -webkit-line-clamp:2 }
    .actions{ gap:10px; }
    .qty-input{ width:52px; height:38px }
    .add-to-cart{ height:38px; padding:0 14px; font-size:.95rem }
  }
  @media (max-width:420px){
    .product-grid{ grid-template-columns:1fr }
    .product-details .desc{ display:none }
    .search-title{ font-size:1.35rem }
  }
  
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

  <!-- ===== TOP BAR (same as index) ===== -->
  <div class="top-bar">
    <div class="container top-bar-content">
      <div class="ticker-track" aria-label="Announcements">
        <div class="ticker-line">&nbsp;&nbsp;<?= $tickerText ?> &nbsp;&nbsp;&bull;&nbsp;&nbsp; <?= $tickerText ?></div>
      </div>
      <div class="top-bar-links">
        <a href="#"><i class="fas fa-question-circle"></i> Help Center</a>
        <a href="#"><i class="fas fa-map-marker-alt"></i> Store Locator</a>
        <a href="#"><i class="fas fa-truck"></i> Track Order</a>
      </div>
    </div>
  </div>

  <!-- ===== HEADER / NAV (same structure as index) ===== -->
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

      <!-- HEAD SEARCH: submits to products.php with q + current category -->
      <form class="search-bar" id="siteSearch" action="products.php" method="get">
        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search for plants, seeds, pots and more...">
        <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
        <button type="submit"><i class="fas fa-search"></i></button>
      </form>

      <div class="header-actions">
        <a href="account.php" class="header-action"><i class="fas fa-user"></i><span>Account</span></a>
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
          <li><a href="#">Pots & Planters</a></li>
          <li><a href="#">Gardening Tools</a></li>
          <li><a href="#">Soil & Fertilizers</a></li>
          <li><a href="#">Offers</a></li>
        </ul>
        <div class="nav-offer"><a href="products.php" class="btn btn-primary" style="padding:10px 18px">Special Offers</a></div>
      </div>
    </nav>
  </header>


  <!-- ===== PRODUCTS ===== -->
  <section class="products-page">
    <div class="container">
      <!-- Search -->
     <div class="search-section" hidden aria-hidden="true" style="display:none">
        <div class="search-header">
          <h3 class="search-title"><span class="search-icon-title">🔍</span> Search Products Here</h3>
          <div class="search-subtitle">Find exactly what you're looking for</div>
        </div>
        <div class="filters search-field">
          <input type="text" id="search" class="search-input" placeholder="Search products by name, category, or features..." value="<?= htmlspecialchars($q) ?>">
          <button type="button" class="search-btn" aria-label="Search">
            <span class="search-text">Search</span>
          </button>
        </div>
      </div>

      <!-- Toolbar -->
      <div class="shop-toolbar">
        <div class="results-label"><span id="resultsCount">0</span> items</div>
        <div class="sort-wrap">
          <label for="sortSelect" style="font-weight:700;color:#546a57">Sort by:</label>
          <select id="sortSelect">
            <option value="latest">Latest</option>
            <option value="price_asc">Price: Low → High</option>
            <option value="price_desc">Price: High → Low</option>
            <option value="name_asc">Name: A → Z</option>
          </select>
        </div>
      </div>

      <!-- Category chips (NEW categories) -->
      <div class="category-tabs" id="categoryTabs">
        <button class="tab <?= ($category==='all'?'active':'') ?>" data-category="all">All Products</button>
        <?php foreach ($CATEGORY_MAP as $slug => $label): ?>
          <button class="tab <?= ($category===$slug?'active':'') ?>" data-category="<?= htmlspecialchars($slug) ?>">
            <?= htmlspecialchars($label) ?>
          </button>
        <?php endforeach; ?>
      </div>

      <!-- Grid -->
      <div class="product-grid" id="grid">
        <?php $i=0; while($p = $products->fetch_assoc()): 
          $label = !empty($p['category']) ? $p['category'] : '';
          $slug  = cat_slug($label);
          $name  = strtolower($p['name'] ?? '');
          $desc  = strtolower($p['description'] ?? '');
          $keywords = trim($name . ' ' . $desc . ' ' . $slug . ' ' . strtolower($label));
        ?>
          <div class="product-card"
               data-name="<?= htmlspecialchars($keywords) ?>"
               data-category="<?= htmlspecialchars($slug) ?>"
               data-price="<?= (float)$p['price'] ?>"
               data-id="<?= (int)$p['id'] ?>"
               data-index="<?= $i++ ?>">
            <button class="wish" aria-label="Add to wishlist" data-id="<?= (int)$p['id'] ?>" onclick="toggleWishlist(event, <?= (int)$p['id'] ?>)"><i class="fas fa-heart"></i></button>
            <div class="product-image">
              <img src="<?= htmlspecialchars($p['thumbnail']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
              <?php if(!empty($p['video'])): ?>
                <video src="<?= htmlspecialchars($p['video']) ?>" muted loop playsinline></video>
              <?php endif; ?>
            </div>
            <div class="product-details">
              <h3><?= htmlspecialchars($p['name']) ?></h3>
              <p class="category"><?= htmlspecialchars($label) ?></p>
              <p class="desc"><?= !empty($p['description']) ? htmlspecialchars($p['description']) : "High quality product" ?></p>
              <p class="price">₹<?= htmlspecialchars($p['price']) ?></p>

              <div class="actions" onclick="event.stopPropagation();">
                <label for="qty-<?= (int)$p['id'] ?>" style="font-weight:700;color:#6b746e">Qty</label>
                <input id="qty-<?= (int)$p['id'] ?>" type="number" value="1" min="1" class="qty-input">
                <button class="add-to-cart"
                        onclick="addToCart('<?= htmlspecialchars($p['name'],ENT_QUOTES) ?>', <?= (float)$p['price'] ?>, '<?= htmlspecialchars($p['thumbnail'],ENT_QUOTES) ?>');">
                  Add to Cart
                </button>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>

      <!-- Load more -->
      <div class="load-more-wrap">
        <button id="loadMoreBtn" class="btn btn-primary" style="display:none">Load more</button>
      </div>
    </div>
  </section>

  <!-- ===== FOOTER ===== -->
  <footer>
    <div class="container">
      <div class="footer-container">
        <div class="footer-col">
          <h3>Vanniddhi</h3>
          <p>Bringing nature's beauty into your home with premium plants and exceptional service.</p>
        </div>
        <div class="footer-col">
          <h3>Quick Links</h3>
          <ul style="list-style:none">
            <li><a href="index.php" style="color:#ccc;text-decoration:none">Home</a></li>
            <li><a href="products.php" style="color:#ccc;text-decoration:none">Shop</a></li>
            <li><a href="#" style="color:#ccc;text-decoration:none">Blog</a></li>
            <li><a href="#" style="color:#ccc;text-decoration:none">Contact</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h3>Customer Service</h3>
          <ul style="list-style:none">
            <li><a href="#" style="color:#ccc;text-decoration:none">Shipping & Returns</a></li>
            <li><a href="#" style="color:#ccc;text-decoration:none">Plant Care Guide</a></li>
            <li><a href="#" style="color:#ccc;text-decoration:none">FAQ</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h3>Contact</h3>
          <ul style="list-style:none">
            <li><i class="fas fa-phone"></i> +91 94250 46286</li>
            <li><i class="fas fa-envelope"></i> hello@vanniddhi.com</li>
            <li><i class="fas fa-clock"></i> Mon-Sat: 9AM – 6PM</li>
          </ul>
        </div>
      </div>
      <div class="copyright">&copy; <?= date('Y') ?> Vanniddhi Plant Nursery. All rights reserved.</div>
    </div>
  </footer>

<!-- ====== SCRIPTS ====== -->
<script>
  // Mobile nav drawer
  const mobileMenu = document.querySelector('.mobile-menu');
  const navLinks = document.querySelector('.nav-links');
  if (mobileMenu && navLinks){
    mobileMenu.addEventListener('click', ()=> navLinks.classList.toggle('show'));
    navLinks.addEventListener('click', e=>{ if(e.target.closest('a')) navLinks.classList.remove('show'); });
  }

  // Make card clickable
  document.querySelectorAll(".product-card").forEach(card => {
    card.addEventListener("click", () => {
      const id = card.getAttribute("data-id");
      window.location.href = "product.php?id=" + id;
    });
  });
  // Stop propagation on controls
  document.querySelectorAll(".add-to-cart").forEach(btn => {
    btn.addEventListener("click", e => e.stopPropagation());
  });
  document.querySelectorAll(".qty-input").forEach(input => {
    input.addEventListener("click", e => e.stopPropagation());
    input.addEventListener("input", e => e.stopPropagation());
  });

  // Hover video play/pause on cards (if video exists)
  document.querySelectorAll('.product-card').forEach(card=>{
    const v = card.querySelector('video');
    if(!v) return;
    card.addEventListener('mouseenter', ()=>{ v.currentTime=0; v.play(); });
    card.addEventListener('mouseleave', ()=>{ v.pause(); v.currentTime=0; });
  });

  // ===== Wishlist =====
  function getWishlist(){ try{ return JSON.parse(localStorage.getItem('wishlist'))||[] }catch{ return [] } }
  function setWishlist(arr){ localStorage.setItem('wishlist', JSON.stringify(arr)); updateWishlistBadges(); }
  function toggleWishlist(ev, id){
    ev.preventDefault(); ev.stopPropagation();
    let list = getWishlist();
    const i = list.indexOf(id);
    if(i===-1) list.push(id); else list.splice(i,1);
    setWishlist(list);
    ev.currentTarget.classList.toggle('active', list.includes(id));
  }
  function updateWishlistBadges(){
    const list = getWishlist();
    document.querySelectorAll('.wish[data-id]').forEach(b=>{
      b.classList.toggle('active', list.includes(+b.dataset.id));
    });
  }

  // ===== Client-side filter + sort + paging =====
  const searchInput = document.getElementById("search");
  const grid = document.getElementById("grid");
  const allCards = Array.from(document.querySelectorAll(".product-card"));

  const tabs = document.querySelectorAll(".category-tabs .tab");
  const sortSelect = document.getElementById('sortSelect');
  const resultsCount = document.getElementById('resultsCount');
  const loadMoreBtn = document.getElementById('loadMoreBtn');

  const noResults = document.createElement("p");
  noResults.textContent = "No products found";
  noResults.className = "no-results";
  noResults.style.display = "none";
  grid.appendChild(noResults);

  const PAGE_SIZE = 24;
  let currentPage = 1;

  function applySort(arr){
    const mode = sortSelect.value || 'latest';
    if(mode === 'latest'){
      return arr.sort((a,b)=> (+a.dataset.index) - (+b.dataset.index));
    }
    if(mode === 'price_asc'){
      return arr.sort((a,b)=> (+a.dataset.price) - (+b.dataset.price));
    }
    if(mode === 'price_desc'){
      return arr.sort((a,b)=> (+b.dataset.price) - (+a.dataset.price));
    }
    if(mode === 'name_asc'){
      return arr.sort((a,b)=> (a.dataset.name||'').localeCompare(b.dataset.name||''));
    }
    return arr;
  }

  function filterProducts() {
    const searchText = (searchInput.value || "").toLowerCase().trim();
    const activeTab = document.querySelector(".category-tabs .tab.active");
    const category = activeTab ? activeTab.dataset.category : "all";

    // find matches
    let matches = [];
    allCards.forEach(p => {
      const keywords = p.dataset.name || "";
      const catSlug  = p.dataset.category || "";
      const okCat = (category === "all" || catSlug === category);
      const okText = !searchText || keywords.includes(searchText);
      if(okCat && okText) matches.push(p);
    });

    // sort
    matches = applySort(matches);

    // reorder DOM to match sorted order (only matched first)
    matches.forEach(node => grid.appendChild(node));
    // hide all then show paged matches
    allCards.forEach(p => p.style.display = "none");

    const maxVisible = currentPage * PAGE_SIZE;
    matches.forEach((p, idx) => {
      if(idx < maxVisible) p.style.display = "";
    });

    // counts + UI
    resultsCount.textContent = matches.length;
    noResults.style.display = matches.length === 0 ? "block" : "none";
    loadMoreBtn.style.display = matches.length > maxVisible ? "inline-block" : "none";
  }

  // events
  searchInput.addEventListener("input", ()=>{ currentPage = 1; filterProducts(); });
  tabs.forEach(tab => {
    tab.addEventListener("click", () => {
      const prev = document.querySelector(".category-tabs .tab.active");
      if(prev) prev.classList.remove("active");
      tab.classList.add("active");
      currentPage = 1;
      filterProducts();
    });
  });
  sortSelect.addEventListener('change', ()=>{ currentPage = 1; filterProducts(); });
  loadMoreBtn.addEventListener('click', ()=>{ currentPage++; filterProducts(); });

  // Search button cosmetic feedback
  const searchBtn = document.querySelector('.search-btn');
  const searchField = document.querySelector('.filters.search-field');
  if (searchBtn) {
    searchBtn.addEventListener('click', function() {
      this.classList.add('loading');
      currentPage = 1;
      filterProducts();
      searchField.classList.add('success');
      setTimeout(() => {
        this.classList.remove('loading');
        setTimeout(() => searchField.classList.remove('success'), 1200);
      }, 800);
    });
    searchInput.addEventListener('keypress', e => { if (e.key === 'Enter') searchBtn.click(); });
    searchInput.addEventListener('input', ()=> searchField.classList.remove('success'));
  }

  // Deep link support: ?category=..., ?q=..., ?sort=...
  (function initFromQuery(){
    const params = new URLSearchParams(location.search);
    const q = (params.get('q') || '').trim();
    const cat = (params.get('category') || '').trim().toLowerCase();
    const sort = (params.get('sort') || '').trim();

    if(q){ searchInput.value = q; }
    if(cat){
      const target = Array.from(tabs).find(t => t.dataset.category === cat);
      if(target){
        const prev = document.querySelector(".category-tabs .tab.active");
        if(prev) prev.classList.remove('active');
        target.classList.add('active');
      }
    }
    if(sort && document.querySelector(`#sortSelect option[value="${CSS.escape(sort)}"]`)){
      sortSelect.value = sort;
    }
  })();

  // init
  document.addEventListener('DOMContentLoaded', ()=>{
    updateCartCount();
    updateWishlistBadges();
    currentPage = 1;
    filterProducts();
  });

  // Cart (existing)
  function addToCart(name, price, img) {
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    price = parseFloat(price);
    const card = event.target.closest(".product-card");
    const qtyInput = card.querySelector("input[type='number']");
    const qty = parseInt(qtyInput.value) || 1;

    let existing = cart.find(item => item.name === name);
    if (existing) existing.qty += qty;
    else cart.push({ name, price, qty, img });

    localStorage.setItem("cart", JSON.stringify(cart));
    updateCartCount();
    alert(`${name} added to cart!`);
  }
  function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem("cart")) || [];
    const totalItems = cart.reduce((sum, item) => sum + (item.qty||0), 0);
    const cartCount = document.getElementById("cartCount");
    if (cartCount) cartCount.textContent = totalItems > 0 ? totalItems : 0;
  }

  window.addEventListener("storage", e => {
    if (e.key === "cart") updateCartCount();
    if (e.key === "wishlist") updateWishlistBadges();
  });
</script>

<!-- (Optional) Google translate — kept so functionality is not removed -->
<script type="text/javascript">
  function googleTranslateElementInit() {
    new google.translate.TranslateElement({
      pageLanguage: 'en',
      includedLanguages: 'en,hi',
      layout: google.translate.TranslateElement.InlineLayout.SIMPLE
    }, 'google_translate_element');
  }
  // Replace your current tabs.forEach(...) click handler with this:
tabs.forEach(tab => {
  tab.addEventListener("click", () => {
    const slug = tab.dataset.category || 'all';
    const url  = new URL(location.href);
    url.searchParams.set('category', slug);

    // keep search & sort selections
    const q = (searchInput && searchInput.value.trim()) ? searchInput.value.trim() : '';
    if (q) url.searchParams.set('q', q); else url.searchParams.delete('q');

    const sortVal = (sortSelect && sortSelect.value) ? sortSelect.value : 'latest';
    url.searchParams.set('sort', sortVal);

    location.href = url.toString(); // <— full page reload with new category
  });
});

</script>
<script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

</body>
</html>
