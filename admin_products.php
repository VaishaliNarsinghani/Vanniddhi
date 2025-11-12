<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();
if(!isset($_SESSION['admin'])) { header("Location: admin_login.php"); exit; }
include "db.php";

/* ---------- helpers: YouTube id + thumb ---------- */
function youtube_id_from_url(?string $url): ?string {
  if(!$url) return null;
  $url = trim($url);
  // Query param ?v=
  if (preg_match('~v=([A-Za-z0-9_-]{6,})~', $url, $m)) return $m[1];
  // youtu.be/<id>
  if (preg_match('~youtu\.be/([A-Za-z0-9_-]{6,})~', $url, $m)) return $m[1];
  // /embed/<id> or /shorts/<id> or /watch/<id> (rare)
  if (preg_match('~youtube\.com/(?:embed|shorts|watch)/([A-Za-z0-9_-]{6,})~', $url, $m)) return $m[1];
  // Fallback: common forms
  if (preg_match('~youtube\.com/.*[?&]v=([A-Za-z0-9_-]{6,})~', $url, $m)) return $m[1];
  return null;
}
function youtube_thumb(?string $url): ?string {
  $id = youtube_id_from_url($url);
  return $id ? "https://img.youtube.com/vi/{$id}/hqdefault.jpg" : null;
}

/* ---------- add product ---------- */
$msg = $err = null;
if (isset($_POST['add'])) {
  try {
    $name         = $_POST['name'] ?? '';
    $category     = $_POST['category'] ?? '';
    $price        = (float)($_POST['price'] ?? 0);
    $actual_stock = (int)($_POST['actual_stock'] ?? 0);
    $online_stock = (int)($_POST['online_stock'] ?? 0);
    $featured     = isset($_POST['featured']) ? 1 : 0;
    $description  = $_POST['description'] ?? '';
    $video        = $_POST['video'] ?? '';

    // Log action
    $admin = $_SESSION['admin'] ?? 'admin';
    $log = $conn->prepare("INSERT INTO activities (admin_user, action) VALUES (?, ?)");
    $action = "Added product: $name";
    $log->bind_param("ss", $admin, $action);
    $log->execute();

    // Uploads dir
    $uploadDir = __DIR__ . "/uploads/";
    if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
    if (!is_writable($uploadDir)) { throw new Exception("Uploads folder not writable: /uploads"); }

    // Simple safe name helper
    $now = time();
    $mkname = function(string $field, string $suffix) use ($now) {
      if (empty($_FILES[$field]['name'])) return '';
      $base = preg_replace('~[^A-Za-z0-9._-]+~','_', basename($_FILES[$field]['name']));
      return "uploads/{$now}_{$suffix}_{$base}";
    };

    $img1 = $mkname('image1','1');
    $img2 = $mkname('image2','2');
    $img3 = $mkname('image3','3');
    $thum = $mkname('thumbnail','thumb');

    if ($img1) move_uploaded_file($_FILES['image1']['tmp_name'], __DIR__ . "/$img1");
    if ($img2) move_uploaded_file($_FILES['image2']['tmp_name'], __DIR__ . "/$img2");
    if ($img3) move_uploaded_file($_FILES['image3']['tmp_name'], __DIR__ . "/$img3");
    if ($thum) move_uploaded_file($_FILES['thumbnail']['tmp_name'], __DIR__ . "/$thum");

    // Insert
    $stmt = $conn->prepare("INSERT INTO products 
      (name, category, price, actual_stock, online_stock, image1, image2, image3, thumbnail, video, description, featured)
      VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("ssdiissssssi",
      $name, $category, $price, $actual_stock, $online_stock,
      $img1, $img2, $img3, $thum, $video, $description, $featured
    );
    $stmt->execute();

    $msg = "✅ Product added successfully.";
  } catch (Throwable $e) {
    $err = "❌ ".$e->getMessage();
  }
}

/* ---------- products list ---------- */
$products = $conn->query("SELECT * FROM products ORDER BY id DESC");

/* ---------- theme ticker ---------- */
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
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin • Manage Products | Vanniddhi</title>
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
  .container{width:100%;max-width:1200px;margin:0 auto;padding:0 16px}

  /* Top ticker */
  .top-bar{background:var(--primary-dark);color:#fff;font-size:.92rem}
  .top-bar-content{min-height:40px;display:flex;align-items:center;gap:16px}
  .ticker-track{overflow:hidden;white-space:nowrap;flex:1}
  .ticker-line{display:inline-block;padding-left:100%;animation:ticker 28s linear infinite}
  @keyframes ticker{from{transform:translateX(0)} to{transform:translateX(-100%)}}
  .top-bar-links a{color:#fff;text-decoration:none;margin-left:18px}
  .top-bar-links a:hover{color:var(--accent)}

  /* Header/Nav */
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

  /* Layout */
  main{padding:18px 0 28px}
  .wrap{display:grid;grid-template-columns:260px 1fr;gap:18px}
  @media (max-width:980px){ .wrap{grid-template-columns:1fr} }

  .sidebar{
    background:#fff;border:1px solid var(--border);border-radius:14px;padding:14px;
    position:sticky;top:86px
  }
  .sb-title{font-family:'Poppins',sans-serif;color:var(--primary);font-size:1.1rem;margin-bottom:8px}
  .sb-links{display:flex;flex-direction:column;gap:8px;margin-top:8px}
  .sb-links a{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;text-decoration:none;color:#2f3b33;font-weight:700;border:1px solid transparent}
  .sb-links a:hover{background:#f4fbf4;border-color:#e0f2e0}
  .sb-links a.active{background:#e8f5e9;border-color:#cfe8cf;color:var(--primary-dark)}

  .panel{background:#fff;border:1px solid var(--border);border-radius:14px;padding:16px;box-shadow:0 6px 16px rgba(0,0,0,.06)}
  .panel h2{font-family:'Poppins',sans-serif;color:#214a26;font-size:1.2rem;margin-bottom:10px}

  /* Form */
  .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
  @media (max-width:680px){ .form-grid{grid-template-columns:1fr} }
  .input,.select,.textarea{width:100%;border:1px solid var(--border);border-radius:10px;padding:10px 12px}
  .textarea{min-height:90px;resize:vertical}
  .row{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
  .btn{display:inline-grid;place-items:center;height:42px;padding:0 16px;border:none;border-radius:12px;font-weight:800;cursor:pointer;text-decoration:none}
  .btn-primary{background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;box-shadow:0 12px 20px rgba(46,125,50,.25)}
  .btn-plain{background:#fff;border:1px solid var(--border)}
  .note{font-size:.9rem;color:#6b7a6d}
  .flash{margin-bottom:12px;border-radius:10px;padding:10px 12px}
  .flash.ok{background:#e8f5e9;border:1px solid #cfe8cf;color:#214a26}
  .flash.err{background:#fff3f3;border:1px solid #f3cdcd;color:#9b2c2c}

  /* Previews */
  .previews{display:flex;flex-wrap:wrap;gap:10px;margin-top:8px}
  .pv{width:90px;height:90px;border-radius:10px;border:1px solid var(--border);overflow:hidden;background:#f5f5f5;display:grid;place-items:center}
  .pv img{width:100%;height:100%;object-fit:cover}

  /* Table */
  .tools{display:flex;flex-wrap:wrap;gap:8px;margin:16px 0 10px}
  .table-wrap{overflow:auto;border:1px solid var(--border);border-radius:14px;background:#fff;box-shadow:0 6px 16px rgba(0,0,0,.06)}
  table{width:100%;border-collapse:collapse}
  th,td{padding:12px;border-bottom:1px solid #eee;text-align:left;font-size:.95rem;vertical-align:middle}
  th{background:#f9faf9}
  tr:hover{background:#fafdfa}
  .thumb{width:60px;height:48px;border-radius:8px;object-fit:cover;background:#f2f5f2}
  .yt-thumb{position:relative;width:80px;height:60px;border-radius:8px;overflow:hidden}
  .yt-thumb img{width:100%;height:100%;object-fit:cover;display:block}
  .yt-thumb .pl{position:absolute;inset:auto auto 6px 6px;background:rgba(0,0,0,.65);color:#fff;border-radius:6px;padding:2px 6px;font-size:.8rem}
  .tag{display:inline-block;padding:3px 8px;border-radius:999px;border:1px solid #cfe2cf;background:#f5fff5;color:#214a26;font-weight:800;font-size:.78rem}
  .act a{color:var(--primary-dark);text-decoration:none;font-weight:800}
  .act a:hover{text-decoration:underline}
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

  <!-- Top ticker -->
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

  <!-- Header / Nav -->
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
        <input type="text" placeholder="Search (UI only)">
        <button><i class="fas fa-search"></i></button>
      </div>

      <div class="header-actions">
        <a href="admin_dashboard.php" class="header-action"><i class="fas fa-shield-halved"></i><span>Admin</span></a>
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
          <li><a href="admin_dashboard.php">Admin</a></li>
        </ul>
        <div class="nav-offer"><a href="products.php" class="btn btn-primary" style="height:38px;padding:0 16px">Special Offers</a></div>
      </div>
    </nav>
  </header>

  <main class="container">
    <div class="wrap">
      <!-- Sidebar -->
      <aside class="sidebar">
        <div class="sb-title">Admin Panel</div>
        <nav class="sb-links">
          <a href="admin_dashboard.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
          <a class="active" href="admin_products.php"><i class="fa-solid fa-seedling"></i> Products</a>
          <a href="admin_orders.php"><i class="fa-solid fa-box"></i> Orders</a>
          <a href="admin_check.php"><i class="fa-solid fa-clipboard-check"></i> Order Check</a>
          <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </nav>
      </aside>

      <!-- Main -->
      <section>
        <!-- Add product form -->
        <div class="panel">
          <h2>Add Product</h2>
          <?php if($msg): ?><div class="flash ok"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
          <?php if($err): ?><div class="flash err"><?= htmlspecialchars($err) ?></div><?php endif; ?>

          <form method="post" enctype="multipart/form-data">
            <div class="form-grid">
              <input class="input" type="text" name="name" placeholder="Product name" required>
              <select class="select" style="height:40px" id="tblCat" name="category">
  <option value="all" selected>All categories</option>
  <option value="preserved-nature-tabletops">Preserved Nature Tabletops</option>
  <option value="plants">Plants</option>
  <option value="flower-bulbs">Flower Bulbs</option>
  <option value="moss-flowers">Moss Flowers</option>
  <option value="succulents">Succulents</option>
  <option value="air-purifying">Air Purifying</option>
  <option value="seeds">Seeds</option>
  <option value="pots-and-planters">Pots &amp; Planters</option>
</select>


              <input class="input" type="number" step="0.01" min="0" name="price" placeholder="Price (₹)" required>
              <input class="input" type="number" min="0" name="actual_stock" placeholder="Actual stock" required>
              <input class="input" type="number" min="0" name="online_stock" placeholder="Online stock" required>

              <div>
                <label class="note">Image 1</label>
                <input class="input" type="file" name="image1" accept="image/*" id="f1">
                <div class="previews"><div class="pv" id="pv1"></div></div>
              </div>
              <div>
                <label class="note">Image 2</label>
                <input class="input" type="file" name="image2" accept="image/*" id="f2">
                <div class="previews"><div class="pv" id="pv2"></div></div>
              </div>
              <div>
                <label class="note">Image 3</label>
                <input class="input" type="file" name="image3" accept="image/*" id="f3">
                <div class="previews"><div class="pv" id="pv3"></div></div>
              </div>
              <div>
                <label class="note">Thumbnail</label>
                <input class="input" type="file" name="thumbnail" accept="image/*" id="ft">
                <div class="previews"><div class="pv" id="pvt"></div></div>
              </div>

              <input class="input" type="text" name="video" id="videoUrl" placeholder="YouTube/Vimeo/Video URL e.g. https://youtu.be/ID">
              <div class="previews" id="videoPreview"></div>
            </div>

            <div style="margin-top:10px">
              <textarea class="textarea" name="description" placeholder="Product description…"></textarea>
            </div>

            <div class="row" style="margin-top:8px">
              <label class="row"><input type="checkbox" name="featured" value="1"> <span class="note">&nbsp;Mark as Featured</span></label>
              <button class="btn btn-primary" type="submit" name="add">Add Product</button>
              <span class="note">Images are optional. Thumbnail is recommended.</span>
            </div>
          </form>
        </div>

        <!-- Tools -->
        <div class="tools">
          <input class="input" style="height:40px" id="tblSearch" type="search" placeholder="Search products…">
          <select class="select" style="height:40px" id="tblCat" name="category">
  <option value="all" selected>All categories</option>
  <option value="preserved-nature-tabletops">Preserved Nature Tabletops</option>
  <option value="plants">Plants</option>
  <option value="flower-bulbs">Flower Bulbs</option>
  <option value="moss-flowers">Moss Flowers</option>
  <option value="succulents">Succulents</option>
  <option value="air-purifying">Air Purifying</option>
  <option value="seeds">Seeds</option>
  <option value="pots-and-planters">Pots &amp; Planters</option>
</select>

          <label class="row" style="font-weight:700"><input type="checkbox" id="tblFeatured"> &nbsp;Featured only</label>
          <label class="row" style="font-weight:700"><input type="checkbox" id="tblLow"> &nbsp;Low stock (&lt; 5)</label>
        </div>

        <!-- Products table -->
        <div class="table-wrap">
          <table id="prodTable">
            <thead>
              <tr>
                <th>#</th>
                <th>Thumb</th>
                <th>Video</th>
                <th>Name</th>
                <th>Category</th>
                <th>Actual</th>
                <th>Online</th>
                <th>Price</th>
                <th>Featured</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
            <?php $i=1; while($p = $products->fetch_assoc()): 
              $yt = youtube_thumb($p['video'] ?? '');
              $isVideoFile = $p['video'] && !$yt && preg_match('~\.(mp4|webm|ogg)$~i', $p['video']);
            ?>
              <tr data-cat="<?= htmlspecialchars(strtolower($p['category'] ?? '')) ?>"
                  data-featured="<?= (int)$p['featured'] ?>"
                  data-online="<?= (int)$p['online_stock'] ?>">
                <td><?= $i++ ?></td>
                <td>
                  <?php if(!empty($p['thumbnail'])): ?>
                    <img class="thumb" src="<?= htmlspecialchars($p['thumbnail']) ?>" alt="">
                  <?php else: ?>
                    <span class="note">—</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if($yt): ?>
                    <a class="yt-thumb" href="<?= htmlspecialchars($p['video']) ?>" target="_blank" rel="noopener">
                      <img src="<?= htmlspecialchars($yt) ?>" alt="YouTube">
                      <span class="pl">▶</span>
                    </a>
                  <?php elseif($isVideoFile): ?>
                    <video src="<?= htmlspecialchars($p['video']) ?>" width="80" height="60" controls style="border-radius:8px"></video>
                  <?php elseif(!empty($p['video'])): ?>
                    <a href="<?= htmlspecialchars($p['video']) ?>" target="_blank" rel="noopener" title="Open video link">🔗</a>
                  <?php else: ?>
                    <span class="note">—</span>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td><span class="tag"><?= htmlspecialchars($p['category'] ?: '—') ?></span></td>
                <td><?= (int)$p['actual_stock'] ?></td>
                <td><?= (int)$p['online_stock'] ?></td>
                <td>₹<?= number_format((float)$p['price'],2) ?></td>
                <td><?= $p['featured'] ? "✅" : "❌" ?></td>
                <td class="act">
                  <a href="edit_product.php?id=<?= (int)$p['id'] ?>">Edit</a> |
                  <a href="delete_product.php?id=<?= (int)$p['id'] ?>" onclick="return confirm('Delete this product?')">Delete</a>
                </td>
              </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </main>

  <footer style="background:var(--dark);color:#fff">
    <div class="container" style="padding:26px 0;text-align:center">&copy; <?= date('Y') ?> Vanniddhi Plant Nursery. All rights reserved.</div>
  </footer>

<script>
  // Mobile nav
  (function(){
    const b=document.querySelector('.mobile-menu'), n=document.querySelector('.nav-links');
    if(!b||!n) return; b.addEventListener('click',()=>n.classList.toggle('show'));
    n.addEventListener('click',e=>{ if(e.target.closest('a')) n.classList.remove('show'); });
  })();

  // Live image previews
  function bindPreview(inputId, pvId){
    const inp = document.getElementById(inputId), pv = document.getElementById(pvId);
    if(!inp || !pv) return;
    inp.addEventListener('change', ()=>{
      pv.innerHTML = '';
      const f = inp.files && inp.files[0];
      if(!f) return;
      const r = new FileReader();
      r.onload = e => { pv.innerHTML = '<img src="'+e.target.result+'">'; };
      r.readAsDataURL(f);
    });
  }
  bindPreview('f1','pv1'); bindPreview('f2','pv2'); bindPreview('f3','pv3'); bindPreview('ft','pvt');

  // Video URL quick preview (YouTube)
  const videoUrl = document.getElementById('videoUrl');
  const videoPreview = document.getElementById('videoPreview');
  function ytId(u){
    if(!u) return null;
    let m = u.match(/v=([A-Za-z0-9_-]{6,})/); if(m) return m[1];
    m = u.match(/youtu\.be\/([A-Za-z0-9_-]{6,})/); if(m) return m[1];
    m = u.match(/youtube\.com\/(?:embed|shorts|watch)\/([A-Za-z0-9_-]{6,})/); if(m) return m[1];
    m = u.match(/youtube\.com\/.*[?&]v=([A-Za-z0-9_-]{6,})/); if(m) return m[1];
    return null;
  }
  function renderVidPrev(){
    if(!videoPreview) return;
    const u = (videoUrl?.value||'').trim();
    videoPreview.innerHTML = '';
    if(!u) return;
    const id = ytId(u);
    if(id){
      const img = 'https://img.youtube.com/vi/'+id+'/hqdefault.jpg';
      videoPreview.innerHTML = `<div class="pv" style="width:140px;height:92px"><img src="${img}" alt="YouTube"></div>`;
    }else if(/\.(mp4|webm|ogg)$/i.test(u)){
      videoPreview.innerHTML = `<video src="${u}" width="160" height="100" controls style="border-radius:10px"></video>`;
    }else{
      videoPreview.innerHTML = `<span class="note">Link will be saved as-is.</span>`;
    }
  }
  videoUrl?.addEventListener('input', renderVidPrev);
  renderVidPrev();

  // Table filters (client-side)
  const q = document.getElementById('tblSearch');
  const cat = document.getElementById('tblCat');
  const fOnly = document.getElementById('tblFeatured');
  const low = document.getElementById('tblLow');
  const rows = Array.from(document.querySelectorAll('#prodTable tbody tr'));
  function applyFilter(){
    const text = (q?.value||'').toLowerCase();
    const c = (cat?.value||'all').toLowerCase();
    const onlyF = !!(fOnly && fOnly.checked);
    const onlyLow = !!(low && low.checked);
    rows.forEach(r=>{
      const hay = r.textContent.toLowerCase();
      const rc = (r.dataset.cat||'');
      const rf = (r.dataset.featured==='1');
      const ro = parseInt(r.dataset.online||'0',10);
      let show = true;
      if(text && !hay.includes(text)) show = false;
      if(c!=='all' && rc!==c) show = false;
      if(onlyF && !rf) show = false;
      if(onlyLow && !(ro < 5)) show = false;
      r.style.display = show ? '' : 'none';
    });
  }
  [q,cat,fOnly,low].forEach(el=> el && el.addEventListener('input', applyFilter));
  [fOnly,low].forEach(el=> el && el.addEventListener('change', applyFilter));
  applyFilter();
</script>

<!-- Optional translate for parity -->
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
