<?php
include "db.php";

// --- Safe fetch
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { die("Invalid product"); }

$product = $conn->query("SELECT * FROM products WHERE id={$id}")->fetch_assoc();
if (!$product) { die("Product not found"); }

// Helper: extract YouTube video ID from common URL formats
function youtube_id($url){
  if(!$url) return null;
  if(preg_match('~youtu\.be/([A-Za-z0-9_-]{11})~', $url, $m)) return $m[1];
  if(preg_match('~youtube\.com/(?:embed/|shorts/)([A-Za-z0-9_-]{11})~', $url, $m)) return $m[1];
  if(preg_match('~youtube\.com.*[?&]v=([A-Za-z0-9_-]{11})~', $url, $m)) return $m[1];
  return null;
}

// Collect media
$imgs = [];
if (!empty($product['thumbnail'])) $imgs[] = $product['thumbnail'];
if (!empty($product['image1']))    $imgs[] = $product['image1'];
if (!empty($product['image2']))    $imgs[] = $product['image2'];
if (!empty($product['image3']))    $imgs[] = $product['image3'];
$hasVideo = !empty($product['video']);

// Build media array (images + optional video/youtube) for JS gallery
$media = [];
foreach ($imgs as $src) { $media[] = ['type'=>'image','src'=>$src]; }
if ($hasVideo) {
  $yt = youtube_id($product['video']);
  if ($yt) {
    $media[] = [
      'type'  => 'youtube',
      'src'   => $product['video'],
      'id'    => $yt,
      'thumb' => "https://i.ytimg.com/vi/{$yt}/hqdefault.jpg"
    ];
  } else {
    $media[] = [
      'type'  => 'video',
      'src'   => $product['video'],
      'thumb' => $product['thumbnail'] ?: ($imgs[0] ?? '')
    ];
  }
}

// Related products (same category), fallback to latest if no category
$related = [];
if (!empty($product['category'])) {
  $cat = $conn->real_escape_string($product['category']);
  $q = $conn->query("SELECT id,name,price,thumbnail,category,video FROM products WHERE category='{$cat}' AND id<>{$id} ORDER BY id DESC LIMIT 8");
} else {
  $q = $conn->query("SELECT id,name,price,thumbnail,category,video FROM products WHERE id<>{$id} ORDER BY id DESC LIMIT 8");
}
while ($row = $q->fetch_assoc()) { $related[] = $row; }

// Ticker messages (same as index.php)
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
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($product['name']) ?> • Vanniddhi</title>
  <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    :root{
      --primary:#2e7d32;
      --primary-light:#4caf50;
      --primary-dark:#1b5e20;
      --secondary:#ff9800;
      --accent:#8bc34a;
      --light:#f8fdf8;
      --dark:#1a331c;
      --text:#333333;
      --white:#ffffff;
      --gray:#f5f5f5;
      --border:#e0e0e0;
    }
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Open Sans',sans-serif;background:var(--light);color:var(--text);line-height:1.6}

    /* ===== Top bar ticker ===== */
    .top-bar{background:var(--primary-dark);color:#fff;font-size:.92rem}
    .top-bar-content{display:flex;justify-content:space-between;align-items:center;gap:16px;min-height:40px}
    .container{width:100%;max-width:1200px;margin:0 auto;padding:0 16px}
    .ticker-track{overflow:hidden;white-space:nowrap;flex:1}
    .ticker-line{display:inline-block;padding-left:100%;animation:ticker 28s linear infinite}
    @keyframes ticker{from{transform:translateX(0)}to{transform:translateX(-100%)}}
    .top-bar-links a{color:#fff;text-decoration:none;margin-left:18px}
    .top-bar-links a:hover{color:var(--accent)}

    /* ===== Header / nav ===== */
    header{background:#fff;box-shadow:0 2px 10px rgba(0,0,0,.08);position:sticky;top:0;z-index:1000}
    .header-container{display:flex;justify-content:space-between;align-items:center;padding:0px 0;gap:12px;flex-wrap:wrap}
    .logo{display:flex;align-items:center;gap:10px;text-decoration:none}
    .logo-icon{font-size:2rem;color:var(--primary)}
    .logo-text{font-size:1.5rem;font-weight:800;color:var(--primary)}
    .search-bar{flex:1;max-width:520px;position:relative}
    .search-bar input{width:100%;padding:12px 16px;border:1px solid var(--border);border-radius:10px;outline:none}
    .search-bar input:focus{border-color:var(--primary)}
    .search-bar button{position:absolute;right:6px;top:6px;background:var(--primary);color:#fff;border:none;border-radius:8px;padding:8px 12px;cursor:pointer}
    .header-actions{display:flex;align-items:center}
    .header-action{position:relative;display:flex;flex-direction:column;align-items:center;margin-left:18px;text-decoration:none;color:var(--text)}
    .header-action i{font-size:1.4rem;margin-bottom:3px}
    #cartCount{position:absolute;top:-8px;right:-8px;background:var(--secondary);color:#fff;border-radius:50%;width:18px;height:18px;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700}
    .mobile-menu{display:none;font-size:1.5rem;cursor:pointer;color:var(--primary)}
    nav{background:var(--gray);padding:10px 0}
    .nav-container{display:flex;justify-content:space-between;align-items:center}
    .nav-links{display:flex;list-style:none}
    .nav-links li{margin-right:18px;position:relative}
    .nav-links a{text-decoration:none;color:var(--text);font-weight:600}
    .nav-links a:hover{color:var(--primary)}
    @media (max-width:992px){ .nav-links{display:none} .mobile-menu{display:block} }

    /* ===== Page: Product ===== */
    .product-wrap{padding:24px 0}
    .breadcrumbs{font-size:.9rem;margin-bottom:12px}
    .breadcrumbs a{text-decoration:none;color:var(--primary)}
    .product-grid{display:grid;grid-template-columns:1.1fr .9fr;gap:28px}
    @media (max-width: 900px){ .product-grid{grid-template-columns:1fr} }

    /* Gallery */
    .gallery{background:#fff;border:1px solid var(--border);border-radius:14px;padding:12px;box-shadow:0 5px 14px rgba(0,0,0,.06); position:relative}
    .stage{position:relative;border-radius:10px;overflow:hidden;background:#fff;height:min(56vh,560px);min-height:300px;display:grid;place-items:center}
    .stage img,.stage video{width:100%;height:100%;object-fit:contain;background:#fff}
    .stage video{display:none}
    .stage iframe{width:100%;height:100%;display:none;background:#000;border:0}
    .stage .nav{position:absolute;top:50%;transform:translateY(-50%);width:42px;height:42px;border-radius:50%;border:none;background:rgba(0,0,0,.45);color:#fff;cursor:pointer;display:grid;place-items:center}
    .stage .nav.prev{left:10px}.stage .nav.next{right:10px}
    .thumbs{display:flex;gap:10px;flex-wrap:wrap;margin-top:10px;max-height:120px;overflow:auto}
    .thumb{width:72px;height:72px;border-radius:10px;border:2px solid transparent;overflow:hidden;cursor:pointer;background:#f7f7f7;position:relative}
    .thumb img{width:100%;height:100%;object-fit:cover}
    .thumb.video::after{content:"\f04b"; font-family:"Font Awesome 6 Free"; font-weight:900; position:absolute; inset:auto auto 6px 6px; background:rgba(0,0,0,.65); color:#fff; padding:4px 6px; border-radius:6px; font-size:.8rem;}
    .thumb.active{border-color:var(--primary)}
    @media (max-width:520px){ .thumb{width:60px;height:60px} }

    /* Wishlist heart */
    .wish{
      position:absolute; top:14px; right:14px; z-index:3;
      width:44px; height:44px; border:none; border-radius:50%;
      display:grid; place-items:center; cursor:pointer;
      background:rgba(255,255,255,.95); color:#999; box-shadow:0 8px 16px rgba(0,0,0,.12);
    }
    .wish.active{ color:#e53935; background:#fff }

    /* Lightbox */
    .lightbox{position:fixed;inset:0;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.9);z-index:1200}
    .lightbox.show{display:flex}
    .lightbox img{max-width:92vw;max-height:86vh;object-fit:contain;border-radius:8px}
    .lightbox .close,.lightbox .prev,.lightbox .next{
      position:absolute;border:none;background:rgba(255,255,255,.2);color:#fff;cursor:pointer;border-radius:50%;width:44px;height:44px;display:grid;place-items:center
    }
    .lightbox .close{top:16px;right:16px;font-size:22px}
    .lightbox .prev{left:18px;font-size:22px}
    .lightbox .next{right:18px;font-size:22px}
    @media (max-width:520px){ .lightbox .prev,.lightbox .next{display:none} }

    /* Info */
    .info{background:#fff;border:1px solid var(--border);border-radius:14px;padding:16px 16px 18px;box-shadow:0 5px 14px rgba(0,0,0,.06)}
    .info h1{font-family:'Poppins',sans-serif;font-size:1.8rem;color:var(--primary);margin-bottom:8px}
    .meta{color:#6b7a6d;margin-bottom:10px;font-weight:600}
    .price{font-size:1.6rem;color:var(--primary-dark);font-weight:900;margin:10px 0 14px}
    .desc{color:#444;margin-bottom:16px}
    .qty-row{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
    .qty-row input[type=number]{width:80px;height:42px;border:1px solid var(--border);border-radius:10px;text-align:center;font-weight:700}
    .btn{display:inline-block;padding:12px 18px;border-radius:12px;border:none;cursor:pointer;font-weight:800;letter-spacing:.2px}
    .btn-primary{background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;box-shadow:0 12px 20px rgba(46,125,50,.25)}
    .btn-primary:hover{filter:brightness(.97)}
    .btn-outline{background:#fff;color:var(--primary);border:2px solid var(--primary);}
    .safe{margin-top:18px;padding:12px;border-radius:10px;border:1px dashed var(--border);background:#fcfffb}

    /* Share row */
    .share-row{display:flex;gap:10px;flex-wrap:wrap;margin:12px 0}
    .share-row .btn{padding:10px 14px}

    /* Related & Recent grids */
    .section-title{display:flex;align-items:flex-end;justify-content:space-between;margin:28px 0 12px}
    .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:18px}
    .card{border:1px solid var(--border);border-radius:14px;overflow:hidden;background:#fff;box-shadow:0 6px 16px rgba(0,0,0,.06);cursor:pointer;transition:transform .25s, box-shadow .25s}
    .card:hover{transform:translateY(-6px); box-shadow:0 18px 34px rgba(0,0,0,.12)}
    .card .img{height:180px;background:#f2f5f2;overflow:hidden}
    .card .img img, .card .img video{width:100%;height:100%;object-fit:cover;display:block}
    .card .body{padding:10px 12px}
    .card .body h4{font-size:1rem;color:var(--primary);margin:0 0 4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .card .body .cat{font-size:.8rem;color:#6b7a6d}
    .card .body .price{font-weight:900;color:var(--primary-dark);margin-top:6px; text-decoration:none;}

    /* Sticky CTA (mobile) */
    .sticky-cta{
      position:fixed; left:0; right:0; bottom:0; z-index:1100;
      background:#fff; border-top:1px solid var(--border);
      display:none; padding:10px 14px; gap:10px; align-items:center;
      box-shadow:0 -8px 22px rgba(0,0,0,.08);
    }
    .sticky-cta .title{flex:1; font-weight:800; color:#214a26; white-space:nowrap; overflow:hidden; text-overflow:ellipsis}
    .sticky-cta .price{font-weight:900; color:var(--primary-dark); margin-right:6px}
    .sticky-cta input{width:64px; height:40px; border:1px solid var(--border); border-radius:10px; text-align:center; font-weight:700}
    .sticky-cta .btn-primary{height:42px}
    @media (max-width: 860px){ .sticky-cta{ display:flex } }

    /* Footer */
    footer{background:var(--dark);color:#fff;margin-top:50px}
    .footer-inner{padding:36px 0;text-align:center}
    .footer-inner a{color:#ffeb3b;text-decoration:none}

    /* Small polish */
    @media (max-width:768px){
      .search-bar{order:3;width:100%;max-width:none}
      .header-actions span{display:none}
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

  <!-- Structured data: Product + Breadcrumb -->
  <script type="application/ld+json">
  {
    "@context":"https://schema.org",
    "@type":"Product",
    "name": <?= json_encode($product['name'] ?? '') ?>,
    "image": <?= json_encode($imgs, JSON_UNESCAPED_SLASHES) ?>,
    "description": <?= json_encode($product['description'] ?? '') ?>,
    "sku": "PID-<?= (int)$product['id'] ?>",
    "brand": {"@type":"Brand","name":"Vanniddhi"},
    "offers": {
      "@type":"Offer",
      "priceCurrency":"INR",
      "price":"<?= number_format((float)$product['price'],2,'.','') ?>",
      "availability":"https://schema.org/InStock",
      "url":"<?= htmlspecialchars((isset($_SERVER['HTTPS'])?'https':'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']) ?>"
    }
  }
  </script>
  <script type="application/ld+json">
  {
    "@context":"https://schema.org",
    "@type":"BreadcrumbList",
    "itemListElement":[
      {"@type":"ListItem","position":1,"name":"Home","item":"<?= htmlspecialchars((isset($_SERVER['HTTPS'])?'https':'http').'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME'] ?? '/').'index.php') ?>"},
      {"@type":"ListItem","position":2,"name":"Products","item":"<?= htmlspecialchars((isset($_SERVER['HTTPS'])?'https':'http').'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME'] ?? '/').'products.php') ?>"},
      {"@type":"ListItem","position":3,"name":<?= json_encode($product['name'] ?? '') ?>}
    ]
  }
  </script>
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
        <input type="text" placeholder="Search for plants, seeds, pots and more...">
        <button><i class="fas fa-search"></i></button>
      </div>

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
          <li><a href="products.php?category=Plants">Plants</a></li>
          <li><a href="products.php?category=Seeds">Seeds</a></li>
          <li><a href="products.php?category=Pots%20and%20Planters">Pots & Planters</a></li>
          <li><a href="products.php?category=Gardening%20Tools">Gardening Tools</a></li>
          <li><a href="products.php?category=Soil%20%26%20Fertilizers">Soil & Fertilizers</a></li>
        </ul>
        <div><a href="products.php" class="btn btn-primary" style="padding:10px 14px">All Products</a></div>
      </div>
    </nav>
  </header>

  <!-- ===== PRODUCT ===== -->
  <section class="product-wrap">
    <div class="container">
      <div class="breadcrumbs">
        <a href="index.php">Home</a> / <a href="products.php">Products</a> / <span><?= htmlspecialchars($product['name']) ?></span>
      </div>

      <div class="product-grid">
        <!-- Gallery -->
        <div class="gallery">
          <button class="wish" id="wishBtn" aria-label="Add to wishlist" data-id="<?= (int)$product['id'] ?>">
            <i class="fas fa-heart"></i>
          </button>

          <div class="stage" id="stage">
            <?php if (count($media) > 1): ?>
              <button class="nav prev" id="prevMedia" aria-label="Previous">‹</button>
              <button class="nav next" id="nextMedia" aria-label="Next">›</button>
            <?php endif; ?>
            <img id="mainImg" src="<?= htmlspecialchars($imgs[0] ?? 'placeholder.png') ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            <video id="mainVid" muted playsinline controls></video>
            <iframe id="mainYt" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
          </div>

          <?php if (!empty($media)): ?>
            <div class="thumbs" id="thumbs">
              <?php foreach($media as $i => $m): ?>
                <?php if ($m['type']==='image'): ?>
                  <div class="thumb<?= $i===0 ? ' active':'' ?>" data-index="<?= $i ?>" data-type="image">
                    <img src="<?= htmlspecialchars($m['src']) ?>" alt="thumb <?= $i+1 ?>">
                  </div>
                <?php else: ?>
                  <div class="thumb video<?= $i===0 ? ' active':'' ?>" data-index="<?= $i ?>" data-type="<?= htmlspecialchars($m['type']) ?>" title="Video">
                    <?php if (!empty($m['thumb'])): ?>
                      <img src="<?= htmlspecialchars($m['thumb']) ?>" alt="Video thumbnail">
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <?php if ($hasVideo): ?>
            <div style="margin-top:12px;display:flex;align-items:center;gap:10px;">
              <a class="btn btn-primary" href="<?= htmlspecialchars($product['video']) ?>" target="_blank" rel="noopener">
                <i class="fa-solid fa-circle-play"></i>&nbsp; Watch product video
              </a>
              <small style="color:#667; font-style:italic;">Opens in a new tab</small>
            </div>
          <?php endif; ?>
        </div>

        <!-- Info -->
        <div class="info">
          <h1><?= htmlspecialchars($product['name']) ?></h1>
          <?php if (!empty($product['category'])): ?>
            <div class="meta"><?= htmlspecialchars($product['category']) ?></div>
          <?php endif; ?>

          <div class="price">₹<?= number_format((float)$product['price'], 2) ?></div>

          <?php if (!empty($product['description'])): ?>
            <div class="desc"><?= nl2br(htmlspecialchars($product['description'])) ?></div>
          <?php endif; ?>

          <div class="share-row">
            <button class="btn btn-outline" id="copyLinkBtn"><i class="fa-solid fa-link"></i> Copy link</button>
            <button class="btn btn-outline" id="shareBtn"><i class="fa-solid fa-share-nodes"></i> Share</button>
            <a class="btn btn-outline" id="waShare" target="_blank" rel="noopener" href="#">
              <i class="fa-brands fa-whatsapp"></i> WhatsApp
            </a>
          </div>

          <form class="qty-row" onsubmit="addToCart(event,'<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>', <?= (float)$product['price'] ?>, '<?= htmlspecialchars($product['thumbnail'] ?: ($imgs[0] ?? ''), ENT_QUOTES) ?>')">
            <label for="qty" style="font-weight:700;color:#576160;">Qty</label>
            <input type="number" id="qty" value="1" min="1" inputmode="numeric">
            <button type="submit" class="btn btn-primary">🛒 Add to Cart</button>
          </form>

          <div class="safe">
            <strong>Care & Tips:</strong>
            <ul style="margin:8px 0 0 18px">
              <li>Place in indirect sunlight; water when topsoil feels dry.</li>
              <li>Avoid overwatering; ensure proper drainage.</li>
              <li>Fertilize lightly during growing season.</li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Related products -->
      <?php if (!empty($related)): ?>
        <div class="section-title">
          <h3 style="color:var(--primary);font-family:Poppins,sans-serif;">Related products</h3>
          <a href="products.php<?= !empty($product['category']) ? '?category='.urlencode($product['category']) : '' ?>" class="btn btn-outline">View all</a>
        </div>
        <div class="grid">
          <?php foreach($related as $rp): ?>
            <a class="card" href="product.php?id=<?= (int)$rp['id'] ?>">
              <div class="img">
                <?php if(!empty($rp['thumbnail'])): ?>
                  <img src="<?= htmlspecialchars($rp['thumbnail']) ?>" alt="<?= htmlspecialchars($rp['name']) ?>">
                <?php else: ?>
                  <img src="placeholder.png" alt="<?= htmlspecialchars($rp['name']) ?>">
                <?php endif; ?>
              </div>
              <div class="body">
                <h4><?= htmlspecialchars($rp['name']) ?></h4>
                <?php if(!empty($rp['category'])): ?><div class="cat"><?= htmlspecialchars($rp['category']) ?></div><?php endif; ?>
                <div class="price">₹<?= number_format((float)$rp['price'], 2) ?></div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Recently viewed (client-side only) -->
      <div class="section-title" style="margin-top:28px;">
        <h3 style="color:var(--primary);font-family:Poppins,sans-serif;">Recently viewed</h3>
        <button type="button" id="clearRecent" class="btn btn-outline" style="display:none">Clear</button>
      </div>
      <div class="grid" id="recentGrid"></div>

    </div>
  </section>

  <!-- Sticky CTA (mobile) -->
  <div class="sticky-cta" id="stickyCta">
    <div class="title"><?= htmlspecialchars($product['name']) ?></div>
    <div class="price">₹<?= number_format((float)$product['price'], 2) ?></div>
    <input type="number" id="qtySticky" value="1" min="1" inputmode="numeric">
    <button class="btn btn-primary" id="addSticky">Add</button>
  </div>

  <!-- Lightbox -->
  <div class="lightbox" id="lightbox" aria-modal="true" role="dialog">
    <button class="close" id="lbClose" aria-label="Close">&times;</button>
    <button class="prev" id="lbPrev" aria-label="Previous">‹</button>
    <img id="lbImg" src="" alt="">
    <button class="next" id="lbNext" aria-label="Next">›</button>
  </div>

  <!-- Footer -->
  <footer>
    <div class="container">
      <div class="footer-container" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:40px;margin-bottom:40px;padding:36px 0">
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
            <li><i class="fas fa-clock"></i> Mon–Sat: 9AM – 6PM</li>
          </ul>
        </div>
      </div>
      <div class="copyright" style="text-align:center;padding:16px 0;border-top:1px solid rgba(255,255,255,.12);color:#bbb;font-size:.92rem">&copy; <?= date('Y') ?> Vanniddhi Plant Nursery. All rights reserved.</div>
    </div>
  </footer>

  <script>
    // Mobile nav toggle
    (function(){
      const btn = document.querySelector('.mobile-menu');
      const links = document.querySelector('.nav-links');
      if(!btn || !links) return;
      btn.addEventListener('click', ()=> {
        links.style.display = (links.style.display === 'flex') ? 'none' : 'flex';
      });
      links.addEventListener('click', e=>{ if(e.target.closest('a')) links.style.display = 'none'; });
    })();

    // Wishlist (matches products.php)
    function getWishlist(){ try{ return JSON.parse(localStorage.getItem('wishlist'))||[] }catch{ return [] } }
    function setWishlist(arr){ localStorage.setItem('wishlist', JSON.stringify(arr)); }
    (function initWishlist(){
      const btn = document.getElementById('wishBtn');
      if(!btn) return;
      const id = +btn.dataset.id;
      const list = getWishlist();
      btn.classList.toggle('active', list.includes(id));
      btn.addEventListener('click', (ev)=>{
        ev.preventDefault(); ev.stopPropagation();
        let w = getWishlist();
        const i = w.indexOf(id);
        if(i===-1) w.push(id); else w.splice(i,1);
        setWishlist(w);
        btn.classList.toggle('active', w.includes(id));
      });
    })();

    // Add to cart (localStorage) + badge update
    function addToCart(e,name,price,img){
      e && e.preventDefault();
      let cart = JSON.parse(localStorage.getItem('cart')) || [];
      const qtyInput = document.getElementById('qty');
      const qty = qtyInput ? Math.max(1, parseInt(qtyInput.value)||1) : 1;
      const ex = cart.find(i=>i.name===name);
      if (ex) ex.qty += qty; else cart.push({name, price:parseFloat(price), qty, img});
      localStorage.setItem('cart', JSON.stringify(cart));
      updateCartCount();
      alert(name + ' added to cart!');
    }
    function updateCartCount(){
      const cart = JSON.parse(localStorage.getItem('cart')) || [];
      const total = cart.reduce((s,i)=> s + (i.qty||0), 0);
      const badge = document.getElementById('cartCount');
      if (badge) badge.textContent = total>0 ? total : 0;
    }
    window.addEventListener('storage', (e)=>{ if (e.key==='cart') updateCartCount(); });
    document.addEventListener('DOMContentLoaded', updateCartCount);

    // Sticky CTA (mobile)
    (function(){
      const add = document.getElementById('addSticky');
      const qtySticky = document.getElementById('qtySticky');
      add.addEventListener('click', (e)=>{
        const q = document.getElementById('qty');
        if (q) q.value = Math.max(1, parseInt(qtySticky.value)||1);
        addToCart(e,'<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>', <?= (float)$product['price'] ?>, '<?= htmlspecialchars($product['thumbnail'] ?: ($imgs[0] ?? ''), ENT_QUOTES) ?>');
      });
    })();

    // Share / copy link
    (function(){
      const link = location.href;
      const copyBtn = document.getElementById('copyLinkBtn');
      const shareBtn = document.getElementById('shareBtn');
      const wa = document.getElementById('waShare');
      if(copyBtn){
        copyBtn.addEventListener('click', async ()=>{
          try{
            await navigator.clipboard.writeText(link);
            copyBtn.textContent = "Copied!";
            setTimeout(()=> copyBtn.innerHTML = '<i class="fa-solid fa-link"></i> Copy link', 1200);
          }catch{
            const ta = document.createElement('textarea'); ta.value = link; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta);
            alert('Link copied.');
          }
        });
      }
      if(shareBtn){
        shareBtn.addEventListener('click', async ()=>{
          if(navigator.share){
            try{
              await navigator.share({ title: document.title, text: "Check this out:", url: link });
            }catch{}
          }else{
            window.open('https://wa.me/?text='+encodeURIComponent(document.title+' '+link), '_blank');
          }
        });
      }
      if(wa){ wa.href = 'https://wa.me/?text='+encodeURIComponent(document.title+' '+link); }
    })();

    // Simple gallery slider + thumbs + lightbox (image only in lightbox) with YouTube support
    (function(){
      const media = <?php echo json_encode(array_values($media), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
      if (!media || !media.length) return;

      const mainImg = document.getElementById('mainImg');
      const mainVid = document.getElementById('mainVid');
      const mainYt  = document.getElementById('mainYt');

      const thumbs = Array.from(document.querySelectorAll('.thumb'));
      const prev = document.getElementById('prevMedia');
      const next = document.getElementById('nextMedia');

      let idx = 0;

      function hideAll(){
        // image
        mainImg.style.display = 'none';
        // file video
        mainVid.pause(); mainVid.removeAttribute('src'); mainVid.style.display = 'none';
        // youtube
        mainYt.src = ''; mainYt.style.display = 'none';
      }

      function show(i){
        idx = (i + media.length) % media.length;
        const item = media[idx];
        thumbs.forEach((t,ti)=> t.classList.toggle('active', ti===idx));

        hideAll();

        if(item.type === 'image'){
          mainImg.src = item.src;
          mainImg.style.display = 'block';
        } else if(item.type === 'video'){
          mainVid.src = item.src;
          mainVid.style.display = 'block';
        } else if(item.type === 'youtube'){
          const embed = 'https://www.youtube.com/embed/' + (item.id || '') + '?rel=0';
          mainYt.src = embed;
          mainYt.style.display = 'block';
        }
      }

      thumbs.forEach(t => t.addEventListener('click', ()=>{
        const i = parseInt(t.dataset.index,10)||0;
        show(i);
      }));
      prev && prev.addEventListener('click', ()=> show(idx-1));
      next && next.addEventListener('click', ()=> show(idx+1));

      // Lightbox (only for images)
      const lb = document.getElementById('lightbox');
      const lbImg = document.getElementById('lbImg');
      const lbPrev = document.getElementById('lbPrev');
      const lbNext = document.getElementById('lbNext');
      const lbClose= document.getElementById('lbClose');

      function openLB(i){
        const item = media[i];
        if(item.type !== 'image') return; // skip video/youtube in lightbox
        show(i);
        lbImg.src = item.src; lb.classList.add('show'); document.body.style.overflow='hidden';
      }
      function syncLB(){
        const item = media[idx];
        if(item.type === 'image') lbImg.src = item.src;
      }
      function closeLB(){ lb.classList.remove('show'); document.body.style.overflow=''; }

      // Open on clicking the stage when image is visible
      document.getElementById('stage').addEventListener('click', ()=>{
        if (media[idx].type === 'image') openLB(idx);
      });
      lbPrev.addEventListener('click', ()=> { show(idx-1); syncLB(); });
      lbNext.addEventListener('click', ()=> { show(idx+1); syncLB(); });
      lbClose.addEventListener('click', closeLB);
      lb.addEventListener('click', e=>{ if(e.target===lb) closeLB(); });
      document.addEventListener('keydown', e=>{
        if(!lb.classList.contains('show')) return;
        if(e.key==='Escape') closeLB();
        if(e.key==='ArrowLeft'){ show(idx-1); syncLB(); }
        if(e.key==='ArrowRight'){ show(idx+1); syncLB(); }
      });

      show(0);
    })();

    // Recently viewed (client-side)
    (function(){
      const REC_KEY = 'recent_products';
      const me = {
        id: <?= (int)$product['id'] ?>,
        name: <?= json_encode($product['name'] ?? '') ?>,
        price: <?= json_encode((float)$product['price']) ?>,
        img: <?= json_encode($product['thumbnail'] ?: ($imgs[0] ?? '')) ?>,
        category: <?= json_encode($product['category'] ?? '') ?>,
        link: 'product.php?id=<?= (int)$product['id'] ?>'
      };
      let list = [];
      try{ list = JSON.parse(localStorage.getItem(REC_KEY)) || []; }catch{}
      // remove same id if exists
      list = list.filter(x => x && +x.id !== +me.id);
      list.unshift(me);
      if(list.length > 12) list.length = 12;
      localStorage.setItem(REC_KEY, JSON.stringify(list));

      const grid = document.getElementById('recentGrid');
      const clearBtn = document.getElementById('clearRecent');
      const render = ()=>{
        let items = [];
        try{ items = JSON.parse(localStorage.getItem(REC_KEY)) || []; }catch{}
        items = items.filter(x => x && +x.id !== +me.id);
        grid.innerHTML = '';
        clearBtn.style.display = items.length ? 'inline-block' : 'none';
        items.forEach(p=>{
          const a = document.createElement('a');
          a.className='card';
          a.href = p.link;
          a.innerHTML = `
            <div class="img">
              <img src="${p.img || 'placeholder.png'}" alt="${(p.name||'').replace(/"/g,'&quot;')}">
            </div>
            <div class="body">
              <h4>${p.name||''}</h4>
              ${p.category ? `<div class="cat">${p.category}</div>` : ``}
              <div class="price">₹${(+p.price).toFixed(2)}</div>
            </div>
          `;
          grid.appendChild(a);
        });
      };
      clearBtn && clearBtn.addEventListener('click', ()=>{ localStorage.removeItem(REC_KEY); render(); });
      render();
    })();
  </script>
</body>
</html>
