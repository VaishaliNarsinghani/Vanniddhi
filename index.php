<?php
include("db.php");
$featuredProducts = $conn->query("SELECT * FROM products WHERE featured=1 ORDER BY id DESC LIMIT 50");

// Ticker messages (from your original functionality)
$news = [
  " 💠 Welcome to Vanniddhi Plant Nursery — premium plants & decor  💠",
  " 💠 Free delivery on orders above ₹999 (Indore city limits)  💠",
  " 💠 Same-day dispatch on prepaid orders placed before 4 PM  💠",
  " 💠 Diwali Season: Extended hours — store open till 11 PM, all week  💠",
  " 💠 Pick-up: Shop No. 04, Temp. Cracker Market, Chhota Bangarda  💠",
  " 💠 Need help choosing? Expert support & plant care guidance  💠",
  " 💠 Preserved Nature Tabletops & Moss Frames now in stock  💠",
  " 💠 Combo packs & bulk orders available — limited stock  💠",
  " 💠 Secure payments: UPI / Card / Cash on pickup  💠",
  " 💠 Helpline: 94250 46286  💠"
];

$tickerText = implode(' • ', array_map('htmlspecialchars', $news));
?>
<?php
// --- DYNAMIC CATEGORIES for "Explore Vanniddhi"
$categoryCards = $conn->query("
  SELECT 
    TRIM(category) AS category,
    COUNT(*) AS cnt,
    COALESCE(NULLIF(MIN(NULLIF(thumbnail, '')), ''), 'image2.jpg') AS thumb
  FROM products
  WHERE TRIM(category) <> ''
  GROUP BY TRIM(category)
  ORDER BY category
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Vanniddhi | Premium Plant Nursery</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #2e7d32;
      --primary-light: #4caf50;
      --primary-dark: #1b5e20;
      --secondary: #ff9800;
      --accent: #8bc34a;
      --light: #f8fdf8;
      --dark: #1a331c;
      --text: #333333;
      --white: #ffffff;
      --gray: #f5f5f5;
      --border: #e0e0e0;
    }
    * { margin:0; padding:0; box-sizing:border-box }
    html { scroll-behavior:smooth }
    body { font-family:'Open Sans', sans-serif; background:var(--light); color:var(--text); overflow-x:hidden; line-height:1.6 }
    h1,h2,h3,h4,h5 { font-family:'Poppins', sans-serif; font-weight:600; line-height:1.3 }
    .container { width:100%; max-width:1400px; margin:0 auto; padding:0 20px }
    section { padding:80px 0 }
    .btn { display:inline-block; padding:12px 28px; border-radius:4px; text-decoration:none; font-weight:600; font-size:1rem; transition:.3s; border:none; cursor:pointer; text-align:center }
    .btn-primary { background:var(--primary); color:var(--white) }
    .btn-primary:hover { background:var(--primary-dark); transform:translateY(-2px); box-shadow:0 5px 15px rgba(46,125,50,.3) }
    .btn-outline { background:transparent; color:var(--primary); border:2px solid var(--primary) }
    .btn-outline:hover { background:var(--primary); color:var(--white) }
    .section-title { text-align:center; margin-bottom:50px }
    .section-title h2 { font-size:2.2rem; color:var(--primary); margin-bottom:12px; display:inline-block; position:relative }
    .section-title h2::after { content:""; position:absolute; width:80px; height:3px; background:var(--secondary); bottom:-10px; left:50%; transform:translateX(-50%) }
    .section-title p { font-size:1.05rem; color:var(--text); max-width:760px; margin:0 auto }

    /* ===== TOP BAR with your ticker ===== */
    .top-bar { background:var(--primary-dark); color:var(--white); font-size:.92rem }
    .top-bar-content { display:flex; justify-content:space-between; align-items:center; gap:16px; min-height:40px }
    .ticker-track { overflow:hidden; white-space:nowrap; flex:1 }
    .ticker-line { display:inline-block; padding-left:100%; animation: ticker 28s linear infinite }
    @keyframes ticker { from{ transform:translateX(0) } to{ transform:translateX(-100%) } }
    .top-bar-links a { color:var(--white); text-decoration:none; margin-left:18px }
    .top-bar-links a:hover { color:var(--accent) }

    /* ===== HEADER ===== */
    header { background:var(--white); box-shadow:0 2px 10px rgba(0,0,0,.08); position:sticky; top:0; z-index:1000 }
    .header-container { display:flex; justify-content:space-between; align-items:center; padding:0px 0 }
    .logo { display:flex; align-items:center; gap:10px; text-decoration:none }
    .logo-icon { font-size:2.2rem; color:var(--primary) }
    .logo-text { font-size:1.8rem; font-weight:700; color:var(--primary) }
    .search-bar { flex:1; max-width:520px; margin:0 30px; position:relative }
    .search-bar input { width:100%; padding:12px 20px; border:1px solid var(--border); border-radius:4px; font-size:1rem; outline:none }
    .search-bar input:focus { border-color:var(--primary) }
    .search-bar button { position:absolute; right:5px; top:5px; background:var(--primary); color:#fff; border:none; border-radius:4px; padding:7px 15px; cursor:pointer }
    .header-actions { display:flex; align-items:center }
    .header-action { position:relative; display:flex; flex-direction:column; align-items:center; margin-left:22px; text-decoration:none; color:var(--text) }
    .header-action:hover { color:var(--primary) }
    .header-action i { font-size:1.5rem; margin-bottom:5px }
    .header-action span { font-size:.85rem }
    #cartCount { position:absolute; top:-8px; right:-8px; background:var(--secondary); color:#fff; border-radius:50%; width:18px; height:18px; display:flex; align-items:center; justify-content:center; font-size:.7rem; font-weight:700 }
    .mobile-menu { display:none; font-size:1.5rem; cursor:pointer; color:var(--primary) }

    /* ===== NAV ===== */
    nav { background:var(--gray); padding:5px 0 }
    .nav-container { display:flex; justify-content:space-between; align-items:center }
    .nav-links { display:flex; list-style:none }
    .nav-links li { margin-right:25px; position:relative }
    .nav-links a { text-decoration:none; color:var(--text); font-weight:500; display:flex; align-items:center }
    .nav-links a:hover { color:var(--primary) }
    .dropdown { position:absolute; top:100%; left:0; background:lightgreen; box-shadow:0 5px 15px rgba(0,0,0,.1); border-radius:4px; min-width:200px; opacity:0; padding:10px;visibility:hidden; transform:translateY(10px); transition:.25s; color:#fff;}
    .nav-links li:hover .dropdown { opacity:1; visibility:visible; transform:translateY(0)}

    /* ===== HERO ===== */
    .hero { background:linear-gradient(rgba(0,0,0,.45), rgba(0,0,0,.45)), url('https://thumbs.dreamstime.com/b/asian-caucasian-female-florists-plant-store-owners-working-taking-care-plants-pot-doing-inventory-304941068.jpg') center/cover no-repeat; color:#fff; text-align:center; padding:100px 0 }
    .hero-content { max-width:800px; margin:0 auto }
    .hero h1 { font-size:3.2rem; margin-bottom:18px; text-shadow:2px 2px 5px rgba(0,0,0,.3) }
    .hero p { font-size:1.15rem; margin-bottom:24px }
    .hero .countdown { margin-bottom:14px; font-weight:700 }

    /* ===== CATEGORIES ===== */
    .categories-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(250px,1fr)); gap:25px }
    .category-card { background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 5px 15px rgba(0,0,0,.08); transition:.25s; text-align:center; padding:25px 15px; border:1px solid var(--border) }
    .category-card:hover { transform:translateY(-8px); box-shadow:0 15px 30px rgba(0,0,0,.12) }
    .category-icon { width:80px; height:80px; background:var(--light); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px }
    .category-icon i { font-size:2rem; color:var(--primary) }

    /* ===== PRODUCT GRID ===== */
    .featured-products { background:var(--gray) }
    .products-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(280px,1fr)); gap:30px }
    .product-card { background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 5px 15px rgba(0,0,0,.08); transition:.3s; position:relative; cursor:pointer }
    .product-card:hover { transform:translateY(-8px); box-shadow:0 15px 30px rgba(0,0,0,.15) }
    .product-badge { position:absolute; top:15px; left:15px; background:var(--secondary); color:#fff; padding:5px 10px; border-radius:3px; font-size:.8rem; font-weight:600; z-index:2 }
    .product-img { height:220px; overflow:hidden; position:relative }
    .product-img img, .product-img video { width:100%; height:100%; object-fit:cover; transition:transform .5s ease }
    .product-card:hover .product-img img { transform:scale(1.05) }
    .product-img video { position:absolute; inset:0; opacity:0; pointer-events:none }
    .product-card:hover .product-img video { opacity:1 }
    .product-info { padding:18px }
    .product-info h3 { font-size:1.1rem; margin-bottom:6px; color:var(--primary) }
    .product-info p.meta { font-size:.9rem; color:#777; margin:0 0 8px }
    .product-price { display:flex; align-items:center; justify-content:space-between; gap:10px }
    .price { font-weight:800; color:var(--primary); font-size:1.1rem }
    .qty-wrap { display:flex; align-items:center; gap:8px }
    .qty-wrap input { width:56px; height:36px; padding:6px 8px; border:1px solid var(--border); border-radius:8px; font-weight:600 }
    .add-btn { height:36px; padding:0 14px }

    /* ===== SERVICES / TESTIMONIALS / NEWSLETTER / FOOTER ===== */
    .services-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(300px,1fr)); gap:30px }
    .service-card { background:var(--light); padding:40px 30px; border-radius:8px; text-align:center; box-shadow:0 5px 15px rgba(0,0,0,.05); transition:.25s; border:1px solid var(--border) }
    .service-card:hover { transform:translateY(-8px); box-shadow:0 15px 30px rgba(0,0,0,.1); background:#fff }
    .service-icon { width:70px; height:70px; background:var(--primary); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 22px }
    .service-icon i { font-size:1.8rem; color:#fff }
    .testimonials { background:var(--gray) }
    .testimonials-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(300px,1fr)); gap:30px }
    .testimonial-card { background:#fff; padding:28px; border-radius:8px; box-shadow:0 5px 15px rgba(0,0,0,.05); position:relative }
    .testimonial-card::before { content:'"'; position:absolute; top:6px; left:16px; font-size:4rem; color:var(--primary); opacity:.18; font-family:Georgia, serif }
    .newsletter { background:linear-gradient(rgba(46,125,50,.9), rgba(46,125,50,.9)), url('https://images.unsplash.com/photo-1574269860304-9022657b43e7?auto=format&fit=crop&w=2070&q=80') center/cover no-repeat; color:#fff; text-align:center }
    .newsletter-form { max-width:500px; margin:0 auto; display:flex }
    .newsletter-form input { flex:1; padding:15px 20px; border:none; border-radius:4px 0 0 4px; font-size:1rem; outline:none }
    .newsletter-form button { padding:0 30px; background:var(--secondary); color:#fff; border:none; border-radius:0 4px 4px 0; font-weight:600; cursor:pointer }
    footer { background:var(--dark); color:#fff; padding:80px 0 30px }
    .footer-container { display:grid; grid-template-columns:repeat(auto-fit, minmax(200px,1fr)); gap:40px; margin-bottom:50px }
    .copyright { text-align:center; padding-top:30px; border-top:1px solid rgba(255,255,255,.1); font-size:.9rem; color:#bbb }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 992px){ .nav-links{ display:none } .mobile-menu{ display:block } .hero h1{ font-size:2.6rem } }
    @media (max-width: 768px){ .top-bar{ display:none } .header-container{ flex-wrap:wrap } .search-bar{ order:3; max-width:100%; margin:12px 0 0 } .hero h1{ font-size:2.2rem } .newsletter-form{ flex-direction:column } .newsletter-form input{ border-radius:4px; margin-bottom:10px } .newsletter-form button{ border-radius:4px; padding:15px } }
    @media (max-width: 576px){ .hero h1{ font-size:2rem } .section-title h2{ font-size:1.8rem } .header-action span{ display:none } }
    /* --- HERO SCROLLER --- */
.hero.hero-scroller { padding: 0; background: none; color: var(--white); }
.hero-scroller .hero-scroller-wrap { position: relative; }

.hero-track {
  display: grid;
  grid-auto-flow: column;
  grid-auto-columns: 100%;
  overflow-x: auto;
  scroll-snap-type: x mandatory;
  scrollbar-width: none;           /* Firefox */
  -ms-overflow-style: none;        /* IE/Edge */
  border-radius: 8px;
}
.hero-track::-webkit-scrollbar { display: none; } /* WebKit */

.hero-slide {
  position: relative;
  height: min(72vh, 640px);
  min-height: 360px;
  scroll-snap-align: start;
  outline: none;
}
.hero-slide img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  filter: brightness(0.9);
}

.hero-overlay {
  position: absolute;
  inset: 0;
  display: grid;
  place-items: center;
  text-align: center;
  padding: 24px;
  background: linear-gradient(to top, rgba(0,0,0,.45) 0%, rgba(0,0,0,.15) 50%, rgba(0,0,0,0) 100%);
}
.hero-overlay h1 { font-size: clamp(1.8rem, 3.6vw, 3.2rem); margin-bottom: 12px; text-shadow: 0 2px 8px rgba(0,0,0,.35); }
.hero-overlay p { font-size: clamp(1rem, 1.4vw, 1.2rem); margin-bottom: 14px; }
.hero-overlay .countdown { font-weight: 700; margin-bottom: 14px; }

.hero-caption {
  position: absolute;
  left: 16px; bottom: 16px;
  background: rgba(0,0,0,.5);
  padding: 8px 12px;
  border-radius: 6px;
  font-weight: 600;
  font-size: .95rem;
}

/* Controls */
.hero-nav {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 42px; height: 42px;
  border-radius: 50%;
  border: none;
  background: rgba(0,0,0,.45);
  color: #fff;
  font-size: 22px;
  cursor: pointer;
  display: grid; place-items: center;
  transition: background .2s ease, transform .2s ease;
}
.hero-nav:hover { background: rgba(0,0,0,.65); transform: translateY(-50%) scale(1.05); }
.hero-nav.prev { left: 16px; }
.hero-nav.next { right: 16px; }

/* Dots */
.hero-dots {
  position: absolute;
  left: 50%; bottom: 14px;
  transform: translateX(-50%);
  display: flex; gap: 8px;
}
.hero-dots button {
  width: 9px; height: 9px;
  border-radius: 50%;
  border: 0;
  background: rgba(255,255,255,.5);
  cursor: pointer;
  transition: transform .2s ease, background .2s ease;
}
.hero-dots button.active,
.hero-dots button[aria-selected="true"] { background: #fff; transform: scale(1.15); }

/* Responsive tweaks */
@media (max-width: 768px){
  .hero-slide { height: 58vh; }
  .hero-nav { width: 38px; height: 38px; font-size: 20px; }
}
/* ====== Motion System (vars + easings) ====== */
:root{
  --dur-fast: 180ms;
  --dur: 320ms;
  --dur-slow: 520ms;
  --ease-out: cubic-bezier(.17,.67,.29,1);
  --ease-soft: cubic-bezier(.22,.61,.36,1);
}

/* global micro-interactions */
a, .btn, .product-card, .service-card, .category-card, .testimonial-card { 
  transition: transform var(--dur) var(--ease-soft), box-shadow var(--dur) var(--ease-soft), background var(--dur-fast) var(--ease-soft), color var(--dur-fast) var(--ease-soft);
  will-change: transform;
}

/* subtle hover float for cards */
.category-card:hover,
.service-card:hover,
.testimonial-card:hover { transform: translateY(-10px); }

/* product card: layered depth + soft glow on hover */
.product-card { 
  transform: translateZ(0);
}
.product-card:hover {
  box-shadow: 0 18px 40px rgba(0,0,0,.16);
}
.product-card .product-img::after{
  content:"";
  position:absolute; inset:0;
  background: radial-gradient(120% 60% at 80% 100%, rgba(255,255,255,.15), transparent 60%);
  opacity:0; transition: opacity var(--dur) var(--ease-soft);
  pointer-events:none;
}
.product-card:hover .product-img::after{ opacity:1; }

/* Magnetic buttons (CSS side) */
.btn{
  position: relative; overflow: hidden;
  transform: translateZ(0);
}
.btn.magnet-move{
  transition: transform 80ms linear;
}

/* Click ripple */
.btn .ripple{
  position:absolute; border-radius:50%;
  transform: translate(-50%, -50%) scale(0);
  background: rgba(255,255,255,.35);
  animation: ripple .6s ease-out forwards;
  pointer-events:none;
}
@keyframes ripple{
  to { transform: translate(-50%, -50%) scale(10); opacity:0; }
}

/* Scroll reveal (progressive enhancement) */
.reveal{
  opacity:0; transform: translateY(24px) scale(.98);
  transition: opacity var(--dur-slow) var(--ease-out), transform var(--dur-slow) var(--ease-out);
  will-change: opacity, transform;
}
.reveal.reveal-in{
  opacity:1; transform: none;
}
.reveal-left{ transform: translateX(-24px); }
.reveal-right{ transform: translateX(24px); }

/* Header hide/show on scroll (headroom-style) */
header{ transition: transform var(--dur) var(--ease-soft), backdrop-filter var(--dur) var(--ease-soft); }
header.headroom--unpinned{ transform: translateY(-100%); }
header.headroom--pinned{ transform: translateY(0); }
@supports (backdrop-filter: blur(6px)){
  header{ backdrop-filter: blur(6px); background: rgba(255,255,255,.8); }
}

/* Hero scroller smoother feel already present — add subtle parallax on overlay text */
.hero-overlay{ 
  will-change: transform, opacity;
}
.hero-slide:hover .hero-overlay{
  transform: translateY(-4px);
}

/* Ticker soft edge mask (makes scrolling line fade at ends) */
.ticker-track{
  -webkit-mask-image: linear-gradient(to right, transparent 0, #000 40px, #000 calc(100% - 40px), transparent 100%);
          mask-image: linear-gradient(to right, transparent 0, #000 40px, #000 calc(100% - 40px), transparent 100%);
}

/* Buttons: subtle lift on hover + focus ring */
.btn:hover{ transform: translateY(-3px); }
.btn:focus-visible{
  outline: 2px solid var(--primary);
  outline-offset: 2px;
}

/* Product image smoothness hints */
.product-img img, .product-img video{ will-change: transform, opacity; }

/* Drag cursor for hero track (from earlier) */
.hero-track{ cursor: grab; }
.hero-track.grabbing{ cursor: grabbing; }

/* Turn down motion for users who prefer less motion */
@media (prefers-reduced-motion: reduce){
  *{ animation-duration: .01ms !important; animation-iteration-count: 1 !important; transition-duration: 0ms !important; scroll-behavior: auto !important; }
}
/* === Round Category Carousel === */
.categories-round { background: var(--white); }

.cat-scroller{
  position: relative;
  padding: 6px 36px; /* space for arrows */
}

.cat-track{
  display: grid;
  grid-auto-flow: column;
  grid-auto-columns: clamp(160px, 22vw, 220px);
  gap: clamp(14px, 2vw, 28px);
  overflow-x: auto;
  scroll-snap-type: x mandatory;
  padding: 8px 2px 18px;
  -ms-overflow-style: none; scrollbar-width: none;
  outline: none;
}
.cat-track::-webkit-scrollbar{ display:none; }

.cat-card{
  scroll-snap-align: start;
  text-decoration: none;
  text-align: center;
  color: var(--text);
}
.cat-card h4{
  margin-top: 12px;
  font-weight: 600;
  font-size: 0.98rem;
}
.cat-card:focus-visible h4{ outline: 2px solid var(--primary); outline-offset: 6px; }

/* circular image frame with soft ring + shine */
.cat-pic{
  width: clamp(120px, 18vw, 180px);
  aspect-ratio: 1/1;
  border-radius: 50%;
  overflow: hidden;
  position: relative;
  margin: 0 auto;
  background: radial-gradient(120% 90% at 80% 20%, rgba(255,255,255,.35), transparent 60%);
  box-shadow: 0 10px 26px rgba(0,0,0,.12);
  border: 6px solid #fff;
}
.cat-pic::before{
  content:"";
  position:absolute; inset:-2px;
  border-radius:50%;
  padding:2px;
  background: linear-gradient(135deg, var(--primary-light), var(--secondary));
  -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
          mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
  -webkit-mask-composite: xor; mask-composite: exclude;
  opacity:.25; transition: opacity .25s ease;
}
.cat-card:hover .cat-pic::before{ opacity:.6; }

.cat-pic img{
  width:100%; height:100%; object-fit: cover;
  transform: scale(1.03);
  transition: transform .45s cubic-bezier(.22,.61,.36,1);
  will-change: transform;
}
.cat-card:hover .cat-pic img{ transform: scale(1.08); }

/* nav arrows */
.cat-nav{
  position:absolute; top:50%; transform: translateY(-50%);
  width:40px; height:40px; border-radius:50%;
  border:none; cursor:pointer;
  background: rgba(0,0,0,.45); color:#fff; font-size:22px;
  display:grid; place-items:center;
  transition: background .2s ease, transform .2s ease;
  z-index:2;
}
.cat-nav:hover{ background: rgba(0,0,0,.65); transform: translateY(-50%) scale(1.05); }
.cat-nav.prev{ left: 4px; } .cat-nav.next{ right: 4px; }
.cat-nav[disabled]{ opacity:.35; cursor: default; }

/* edge fade for nicer scroll */
.cat-track{
  -webkit-mask-image: linear-gradient(to right, transparent 0, #000 30px, #000 calc(100% - 30px), transparent 100%);
          mask-image: linear-gradient(to right, transparent 0, #000 30px, #000 calc(100% - 30px), transparent 100%);
}

/* reduced motion */
@media (prefers-reduced-motion: reduce){
  .cat-pic img{ transition: none; }
}
/* ==== Featured card — premium look ==== */
.products-grid{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(260px,1fr));
  gap:28px;
}

.product-card{
  position:relative;
  border-radius:18px;
  overflow:hidden;
  cursor:pointer;
  background:
    radial-gradient(180% 120% at 80% 0%, rgba(255,255,255,.65) 0%, rgba(255,255,255,.35) 35%, rgba(255,255,255,.15) 60%, transparent 100%),
    linear-gradient(180deg, rgba(255,255,255,.55), rgba(255,255,255,.35));
  backdrop-filter:saturate(120%) blur(6px);
  border:1px solid rgba(0,0,0,.06);
  box-shadow:
    0 10px 25px rgba(0,0,0,.08),
    0 2px 6px rgba(0,0,0,.05) inset;
  transform:translateZ(0);
  transition:transform .35s cubic-bezier(.22,.61,.36,1), box-shadow .35s, filter .35s;
}

/* gradient ring edge */
.product-card::before{
  content:"";
  position:absolute; inset:0;
  border-radius:inherit;
  padding:1px;
  background:linear-gradient(135deg, var(--accent, #8bc34a), var(--secondary,#ff9800));
  -webkit-mask:linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
          mask:linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
  -webkit-mask-composite: xor; mask-composite: exclude;
  opacity:.35; pointer-events:none; transition:opacity .3s ease;
}
.product-card:hover::before{ opacity:.6; }

.product-card:hover{
  transform:translateY(-8px) scale(1.01);
  box-shadow: 0 22px 60px rgba(0,0,0,.18);
}

/* subtle “shine” sweep on hover */
.product-card::after{
  content:"";
  position:absolute; top:-130%; left:-160%;
  width:120%; height:360%;
  background:linear-gradient(75deg, transparent 40%, rgba(255,255,255,.35), transparent 60%);
  transform:rotate(12deg);
  opacity:0; pointer-events:none;
}
.product-card:hover::after{
  animation:cardShine 1.2s ease forwards;
  opacity:1;
}
@keyframes cardShine{ to{ left:150%; } }

/* media */
.product-img{
  height:230px;
  position:relative; overflow:hidden;
  border-bottom:1px solid rgba(0,0,0,.06);
}
.product-img img,
.product-img video{
  width:100%; height:100%; object-fit:cover; display:block;
  transition:transform .65s cubic-bezier(.22,.61,.36,1), opacity .35s;
}
.product-card:hover .product-img img{ transform:scale(1.07); }
.product-img video{
  position:absolute; inset:0; opacity:0; pointer-events:none;
  filter:saturate(1.05) contrast(1.02);
}
.product-card:hover .product-img video{ opacity:1; }

/* badge */
.product-badge{
  position:absolute; top:14px; left:14px; z-index:2;
  background:linear-gradient(45deg, var(--secondary,#ff9800), #ffb74d);
  color:#fff; font-weight:800; font-size:.78rem;
  padding:6px 10px; border-radius:10px;
  box-shadow:0 6px 14px rgba(255,152,0,.35);
}

/* text/meta */
.product-info{ padding:16px 16px 14px; }
.product-info h3{
  font-size:1.05rem; color:var(--primary,#2e7d32);
  margin:0 0 6px;
  display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;
}
.product-info .meta{ color:#6b7a6d; font-size:.9rem; margin-bottom:6px; }

/* price + actions */
.product-price{ display:flex; align-items:center; justify-content:space-between; gap:10px; }
.price{ font-weight:900; color:var(--primary-dark,#1b5e20); font-size:1.15rem; letter-spacing:.2px; }

.qty-wrap{
  display:flex; align-items:center; gap:8px;
  background: #ffffffc7;
  border-radius:12px;
  padding:6px;
  box-shadow: inset 0 0 0 1px var(--border,#e0e0e0);
}
.qty-wrap input{
  width:56px; height:36px;
  border:1px solid var(--border,#e0e0e0);
  border-radius:10px;
  text-align:center; font-weight:700;
  outline:none; transition:border .2s;
  background:#fff;
}
.qty-wrap input:focus{ border-color:var(--primary,#2e7d32); }

.add-btn{
  height:36px; padding:0 16px;
  border-radius:10px;
  background:linear-gradient(135deg, var(--primary,#2e7d32), var(--primary-light,#4caf50));
  color:#fff; border:none; font-weight:800; letter-spacing:.2px;
  box-shadow:0 10px 18px rgba(46,125,50,.25);
  transition:transform .15s ease, box-shadow .15s ease, filter .15s ease;
  position:relative; overflow:hidden; /* for ripple */
}
.add-btn:hover{ transform:translateY(-2px); box-shadow:0 14px 24px rgba(46,125,50,.28); }
.add-btn:active{ transform:translateY(0); box-shadow:0 8px 14px rgba(46,125,50,.22); }

/* ripple using CSS vars set from JS */
.add-btn::after{
  content:""; position:absolute; width:10px; height:10px; border-radius:50%;
  left:var(--rx,50%); top:var(--ry,50%);
  transform:translate(-50%,-50%) scale(0);
  background:rgba(255,255,255,.6);
  filter:blur(1px);
  transition:transform .45s ease, opacity .6s ease;
  opacity:0; pointer-events:none;
}
.add-btn.rippling::after{
  transform:translate(-50%,-50%) scale(18);
  opacity:0;
}

/* tilt hint on hover (works with JS) */
.product-card.tilted{
  transform:perspective(900px) rotateX(var(--rx,0deg)) rotateY(var(--ry,0deg)) translateY(-6px);
}

/* mobile tweaks */
@media (max-width: 480px){
  .product-img{ height:200px; }
  .qty-wrap input{ width:48px; }
}
/* ====== MOBILE POLISH PACK (drop-in) ====== */

/* Base spacing & typography that scales */
:root{
  --container-pad: 16px;
}
html{ -webkit-text-size-adjust:100%; }
body{ line-height:1.55; }
.container{ padding-left:var(--container-pad); padding-right:var(--container-pad); }

/* Buttons and tap targets */
.btn, .add-btn{
  min-height:44px;                /* a11y minimum */
  font-size:clamp(.95rem, 3.4vw, 1rem);
  border-radius:12px;
}

/* Header */
.header-container{ gap:12px; flex-wrap:wrap; }
.logo-text{ font-size:clamp(1.2rem, 5vw, 1.6rem); }
.search-bar{ order:3; width:100%; max-width:100%; margin:6px 0 0; }
.search-bar input{ height:44px; border-radius:12px; }
.search-bar button{ height:34px; border-radius:10px; top:5px; right:6px; }

/* Mobile nav drawer */
@media (max-width: 992px){
  .mobile-menu{ display:block; }
  .nav-links{
    display:none;
    position:fixed; inset:60px 0 0 0;
    background:#fff;
    padding:18px var(--container-pad) calc(18px + env(safe-area-inset-bottom));
    flex-direction:column; gap:12px;
    box-shadow:0 12px 30px rgba(0,0,0,.12);
    overflow:auto; z-index:1200;
  }
  .nav-links.show{ display:flex; }
  .nav-links a{ padding:10px 6px; font-size:1.05rem; }
  .dropdown{ position:static; opacity:1; visibility:visible; transform:none; box-shadow:none; border:1px solid #eee; border-radius:10px; }
}

/* Top bar ticker: smaller on phones */
@media (max-width: 768px){
  .top-bar{ font-size:.9rem; }
  .top-bar-links{ display:none; }  /* keep it clean */
}

/* HERO scroller */
.hero.hero-scroller{ padding:0; }
.hero-slide{ height: min(64vh, 520px); }
.hero-overlay{ padding: 18px var(--container-pad); }
.hero-overlay h1{ font-size: clamp(1.35rem, 6vw, 2.2rem); margin-bottom:10px; }
.hero-overlay p{ font-size: clamp(.95rem, 3.2vw, 1.05rem); margin-bottom:10px; }
.hero-dots{ bottom: 10px; gap:10px; }
.hero-dots button{ width:12px; height:12px; }
.hero-nav{ width:42px; height:42px; }
@media (max-width: 520px){
  .hero-slide{ height: 52vh; min-height: 320px; }
  .hero-nav{ width:40px; height:40px; }
}

/* Categories row with images (keeps circles centered) */
.categories .section-title h2{ font-size: clamp(1.25rem, 5.6vw, 1.8rem); }
.categories .section-title p{ font-size: .98rem; }
.category-scroller{ margin-top:14px; }
.cat-card{
  width: 150px; min-width: 150px;
}
.cat-card .cat-img{ width: 120px; height: 120px; }

/* Featured products grid */
.products-grid{
  display:grid;
  grid-template-columns:repeat(2, minmax(0, 1fr)); /* two-up on phones */
  gap:16px;
}
@media (max-width: 420px){
  .products-grid{ grid-template-columns: 1fr; }   /* single column on very small phones */
}

.product-card{ border-radius:16px; }
.product-img{ height: clamp(180px, 42vw, 230px); }
.product-info{ padding:14px; }
.product-info h3{ font-size:clamp(1rem, 3.6vw, 1.08rem); }

/* Price & controls stack neatly on mobile */
.product-price{
  flex-direction:column;
  align-items:stretch;
  gap:10px;
}
.price{ font-size:1.1rem; }
.qty-wrap{
  width:100%;
  justify-content:space-between;
  gap:10px; padding:8px;
  border-radius:12px;
}
.qty-wrap input{
  width:64px; height:42px; border-radius:10px; font-size:1rem;
}
.add-btn{ flex:1; height:42px; }

/* Services / testimonials / newsletter headings */
.section-title h2{ font-size: clamp(1.25rem, 5.4vw, 1.8rem); }
.section-title p{ font-size:.98rem; }

/* Footer: comfortable stacking and safe area */
footer .footer-container{ grid-template-columns: 1fr; gap:26px; }
footer{ padding-bottom: calc(30px + env(safe-area-inset-bottom)); }

/* Reduce motion for users who prefer it */
@media (prefers-reduced-motion: reduce){
  *{ animation-duration:0.01ms !important; animation-iteration-count:1 !important; transition-duration:0.01ms !important; scroll-behavior:auto !important; }
}
/* ========== SERVICES ========== */
.services{
  position: relative;
  background: radial-gradient(1200px 400px at -10% 0%, #eef9ef 0%, transparent 60%),
              radial-gradient(900px 300px at 110% 10%, #f2fbf2 0%, transparent 60%),
              var(--light);
}
.services-grid{
  display:grid; gap:22px;
  grid-template-columns:repeat(auto-fill, minmax(260px, 1fr));
}
.service-card{
  position:relative; overflow:hidden;
  background:#fff; border:1px solid var(--border); border-radius:18px;
  padding:26px 22px;
  box-shadow:0 6px 18px rgba(0,0,0,.06);
  transition:transform .25s ease, box-shadow .25s ease, border-color .25s ease;
  display:grid; grid-template-columns:64px 1fr; gap:16px; align-items:center;
}
.service-card:hover{
  transform:translateY(-6px);
  box-shadow:0 16px 36px rgba(0,0,0,.12);
  border-color:#e7eee7;
}
.service-card::after{ /* soft top glow */
  content:""; position:absolute; inset:-2px -2px auto -2px; height:6px; border-radius:18px 18px 0 0;
  background:linear-gradient(90deg, rgba(139,195,74,.25), rgba(46,125,50,.25), rgba(139,195,74,.25));
  opacity:.7;
}
.service-icon{
  width:64px; height:64px; border-radius:50%;
  display:grid; place-items:center; color:#fff; font-size:1.5rem;
  background:linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
  box-shadow:0 10px 18px rgba(46,125,50,.28), inset 0 0 0 6px rgba(255,255,255,.22);
}
.service-card h3{
  margin:0 0 6px; color:var(--primary);
  font-size:clamp(1rem, 2.8vw, 1.15rem);
}
.service-card p{ margin:0; color:#576160; font-size:.96rem }

/* ========== TESTIMONIALS ========== */
.testimonials{ background:var(--gray); }
.testimonials-grid{
  display:grid; gap:22px;
  grid-template-columns:repeat(auto-fill, minmax(280px, 1fr));
}
.testimonial-card{
  position:relative; background:#fff; border:1px solid var(--border);
  border-radius:16px; padding:22px 20px;
  box-shadow:0 6px 16px rgba(0,0,0,.06);
  transition:transform .2s ease, box-shadow .2s ease;
}
.testimonial-card:hover{ transform:translateY(-4px); box-shadow:0 14px 28px rgba(0,0,0,.12); }
.testimonial-card::before{
  content:"“"; position:absolute; left:14px; top:-8px;
  font-size:4rem; line-height:1; color:rgba(46,125,50,.15); font-family:Georgia, serif; pointer-events:none;
}
.testimonial-text{
  margin:6px 0 14px; color:#2e3432; line-height:1.7; font-size:1rem;
}
.testimonial-author{ display:flex; align-items:center; gap:12px; }
.testimonial-author .author-img{
  width:50px; height:50px; border-radius:50%; overflow:hidden; flex:0 0 50px;
  box-shadow:0 4px 10px rgba(0,0,0,.12);
}
.testimonial-author .author-info h4{
  margin:0; color:var(--primary); font-size:1rem;
}
.testimonial-author .author-info p{
  margin:2px 0 0; color:#7a8581; font-size:.9rem;
}

/* ========== NEWSLETTER ========== */
.newsletter{ background:transparent; padding-top:70px; padding-bottom:90px; }
.newsletter .wrap{
  background:linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
  color:#fff; border-radius:22px; padding:38px 26px;
  box-shadow:0 22px 44px rgba(46,125,50,.35);
  display:grid; gap:18px;
}
@media (min-width: 860px){
  .newsletter .wrap{ grid-template-columns:1.1fr .9fr; align-items:center; padding:46px 40px; }
}
.newsletter h2{ margin:0 0 8px; color:#fff; font-size:clamp(1.4rem, 3.4vw, 2.2rem); }
.newsletter p{ margin:0; color:#e7f7e7; }
.newsletter-form{
  width:100%; display:flex; gap:12px; align-items:center; margin-top:8px;
}
.newsletter-form input{
  flex:1; height:52px; border:none; outline:none; border-radius:12px;
  padding:0 16px; font-size:1rem; color:#194221;
  background:#f3fff3;
}
.newsletter-form input::placeholder{ color:#7aa77b; }
.newsletter-form button{
  height:52px; border:none; border-radius:12px; padding:0 26px;
  background:var(--secondary); color:#fff; font-weight:700; cursor:pointer;
  transition:transform .1s ease, filter .2s ease;
}
.newsletter-form button:hover{ filter:brightness(.95); }
.newsletter-form button:active{ transform:translateY(1px); }
@media (max-width: 640px){
  .newsletter-form{ flex-direction:column; align-items:stretch }
  .newsletter-form button{ width:100% }
}

/* ========== REVEAL ANIMATIONS (on scroll) ========== */
.reveal{ opacity:0; transform:translateY(18px); transition:opacity .6s ease, transform .6s ease; }
.reveal.in{ opacity:1; transform:none; }
@media (prefers-reduced-motion: reduce){
  .reveal{ opacity:1; transform:none }
  .service-card, .testimonial-card{ transition:none }
}
.wish{
  position:absolute; top:14px; right:14px; z-index:3;
  width:40px; height:40px; border:none; border-radius:50%;
  display:grid; place-items:center; cursor:pointer;
  background:rgba(255,255,255,.9); color:#999;
  box-shadow:0 8px 16px rgba(0,0,0,.12);
}
.wish i{ pointer-events:none }
.wish.active{ color:#e53935; background:#fff; }
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
/* ---------- Alignment polish for Services section ---------- */

/* keep the grid centered and at a comfy max width */
.services .container { max-width: 1100px; }

/* 3 tidy columns on desktop, stretch items so all cards get equal height */
.services-grid{
  grid-template-columns: repeat(3, minmax(280px, 1fr));
  align-items: stretch;          /* <- equal height */
  justify-items: stretch;
  gap: 24px;                     /* consistent gaps */
}

/* make each card fill its grid cell and keep inner content vertically centered */
.service-card{
  height: 100%;                  /* <- equal height */
  display: grid;                 /* keep your two-column card layout */
  grid-template-columns: 72px 1fr;
  align-items: center;           /* center icon + text vertically */
}

/* lock icon size/space so columns align pixel-perfect */
.service-icon{
  width: 64px; height: 64px;
  flex: 0 0 64px;
}

/* keep text block vertically centered and consistent */
.service-card > div:last-child{
  display: flex;
  flex-direction: column;
  justify-content: center;
  min-height: 64px;              /* matches icon height */
}

/* micro-typography so headings wrap the same way */
.service-card h3{
  line-height: 1.25;
  margin: 0 0 6px;
}
.service-card p{
  margin: 0;
}

/* tighter tablet/phone behavior */
@media (max-width: 920px){
  .services-grid{ grid-template-columns: repeat(2, minmax(260px, 1fr)); }
}
@media (max-width: 560px){
  .services-grid{ grid-template-columns: 1fr; }
  .service-card{ grid-template-columns: 60px 1fr; }
  .service-icon{ width: 60px; height: 60px; }
}
/* ===== Testimonials • alignment + premium styling ===== */

.testimonials{
  background: linear-gradient(180deg, #f8fff8 0%, #f3faf3 100%);
  padding-top: 70px;
  padding-bottom: 80px;
}

.testimonials .section-title h2{
  position: relative;
}
.testimonials .section-title h2::after{
  content:"";
  position:absolute;
  left:50%; bottom:-10px;
  transform:translateX(-50%);
  width:92px; height:4px; border-radius:4px;
  background: linear-gradient(135deg, var(--primary,#2e7d32), var(--accent,#8bc34a));
}

/* grid: equal-height cards, tidy gaps */
.testimonials-grid{
  display: grid;
  grid-template-columns: repeat(3, minmax(280px, 1fr));
  gap: 24px;
  align-items: stretch;    /* <- equal height */
  justify-items: stretch;
}

/* card */
.testimonial-card{
  height: 100%;
  position: relative;
  background: #fff;
  border: 1px solid var(--border,#e0e0e0);
  border-radius: 18px;
  padding: 22px 20px 18px;
  box-shadow: 0 8px 24px rgba(0,0,0,.06);
  transition: transform .25s cubic-bezier(.22,.61,.36,1), box-shadow .25s;
  overflow: hidden;
}
.testimonial-card:hover{
  transform: translateY(-6px);
  box-shadow: 0 18px 44px rgba(46,125,50,.14);
}

/* soft background glow */
.testimonial-card::after{
  content:"";
  position:absolute; inset:0 -40% -70% -40%;
  background: radial-gradient(60% 40% at 20% 0%, rgba(139,195,74,.18), transparent 60%);
  pointer-events:none; opacity:.25;
}

/* decorative big quote */
.testimonial-card::before{
  content:"“";
  position:absolute; left:14px; top:-8px;
  font-family: Georgia, serif;
  font-size: 5rem; line-height: 1;
  color: rgba(46,125,50,.12);
  pointer-events:none;
}

/* text fills available space so author row sits aligned at the bottom */
.testimonial-text{
  margin: 18px 0 16px;
  color:#2e3432;
  font-size: 1rem;
  line-height: 1.7;
  flex: 1 1 auto;
}

/* author row */
.testimonial-author{
  display:flex; align-items:center; gap:12px;
  margin-top: auto;                 /* anchors to bottom */
}

/* circular avatar with subtle ring */
.author-img{
  width: 52px; height: 52px; flex:0 0 52px;
  border-radius: 50%; overflow: hidden;
  position: relative;
  box-shadow: 0 4px 10px rgba(0,0,0,.12);
}
.author-img::before{
  content:""; position:absolute; inset:-2px; border-radius:50%;
  padding:2px;
  background: linear-gradient(135deg, var(--primary-light,#4caf50), var(--secondary,#ff9800));
  -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
          mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
  -webkit-mask-composite: xor; mask-composite: exclude;
  opacity:.5; pointer-events:none;
}

.author-info h4{
  margin:0; color: var(--primary,#2e7d32);
  font-size: 1rem; font-weight: 700;
}
.author-info p{
  margin:2px 0 0; color:#7a8581; font-size:.9rem;
}

/* responsive: stays perfectly aligned */
@media (max-width: 960px){
  .testimonials-grid{ grid-template-columns: repeat(2, minmax(0,1fr)); }
}
@media (max-width: 560px){
  .testimonials-grid{ grid-template-columns: 1fr; gap: 16px; }
  .testimonial-card{ padding:18px; }
}

  </style>
</head>
<body>
  <!-- ===== TOP BAR (Ticker) ===== -->
  <div class="top-bar">
    <div class="container top-bar-content">
      <div class="ticker-track" aria-label="Announcements">
        <div class="ticker-line">&nbsp;&nbsp;<?= $tickerText ?> &nbsp;&nbsp;&bull;&nbsp;&nbsp; <?= $tickerText ?></div>
      </div>
      <div class="top-bar-links">
  <a href="help_center.php"><i class="fas fa-question-circle"></i> Help Center</a>
  <a href="store_locator.php"><i class="fas fa-map-marker-alt"></i> Store Locator</a>
  <a href="track_order.php"><i class="fas fa-truck"></i> Track Order</a>
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
  <li>
    <a href="products.php?category=Plants">Plants <i class="fas fa-chevron-down"></i></a>
    <div class="dropdown">
      <a href="products.php?category=Plants&sub=Indoor Plants">Indoor Plants</a>
      <a href="products.php?category=Plants&sub=Outdoor Plants">Outdoor Plants</a>
      <a href="products.php?category=Plants&sub=Flowering Plants">Flowering Plants</a>
      <a href="products.php?category=Plants&sub=Succulents & Cacti">Succulents & Cacti</a>
      <a href="products.php?category=Plants&sub=Air Purifying Plants">Air Purifying Plants</a>
    </div>
  </li>

  <li>
    <a href="products.php?category=Seeds">Seeds <i class="fas fa-chevron-down"></i></a>
    <div class="dropdown">
      <a href="products.php?category=Seeds&sub=Vegetable Seeds">Vegetable Seeds</a>
      <a href="products.php?category=Seeds&sub=Flower Seeds">Flower Seeds</a>
      <a href="products.php?category=Seeds&sub=Herb Seeds">Herb Seeds</a>
      <a href="products.php?category=Seeds&sub=Fruit Seeds">Fruit Seeds</a>
    </div>
  </li>

  <li><a href="products.php?category=Pots and Planters">Pots & Planters</a></li>
  <li><a href="products.php?category=Gardening Tools">Gardening Tools</a></li>
  <li><a href="products.php?category=Soil and Fertilizers">Soil & Fertilizers</a></li>
  <li><a href="products.php?category=Offers">Offers <span class="offer-badge">Sale</span></a></li>
</ul>

        <div class="nav-offer"><a href="products.php" class="btn btn-outline">Special Offers</a></div>
      </div>
    </nav>
  </header>

  <!-- ===== HERO ===== -->
  <section class="hero hero-scroller" id="home">
  <div class="container">
    <div class="hero-scroller-wrap">
      <!-- Slides -->
      <div class="hero-track" id="heroTrack" tabindex="0" aria-label="Featured banners">
        <!-- Slide 1 (with your text / countdown) -->
    <article class="hero-slide" id="hero-slide-1" role="tabpanel" aria-roledescription="slide" aria-label="1 of 4">
  <img src="image.jpg" alt="Lush green indoor and outdoor plants displayed in a sunlit nursery">
  <div class="hero-overlay">
    <h1>Bring Nature’s Calm to Your Home & Garden</h1>
    <p>
      Transform your living spaces into a refreshing green paradise with our handpicked collection of indoor and outdoor plants. 
      From air-purifying houseplants to vibrant flowering beauties, each plant is nurtured with love to bring you closer to nature.
    </p>
    <p>
      Whether you’re starting your first balcony garden or expanding your backyard oasis, we’ve got everything you need — pots, planters, tools, and expert care tips to keep your greens thriving.
    </p>
    <div class="hero-btns">
      <a href="products.php" class="btn btn-outline">Explore Collection</a>
    </div>
  </div>
</article>


        <!-- Slide 2 -->
        <article class="hero-slide" id="hero-slide-2" role="tabpanel" aria-roledescription="slide" aria-label="2 of 4">
          <img src="image2.jpg" alt="Selection of succulents and cacti">
          
          <figcaption class="hero-caption">Succulents & Cacti • Low-maintenance beauties</figcaption>
          <div class="hero-btns">
      <a href="products.php" class="btn btn-outline">Explore Collection</a>
    </div>
        </article>

        <!-- Slide 3 -->
        <article class="hero-slide" id="hero-slide-3" role="tabpanel" aria-roledescription="slide" aria-label="3 of 4">
          <img src="image3.jpg" alt="Outdoor green nursery with potted plants">
          <figcaption class="hero-caption">Outdoor Plants • Make the balcony bloom</figcaption>
        </article>

        <!-- Slide 4 -->
        <article class="hero-slide" id="hero-slide-4" role="tabpanel" aria-roledescription="slide" aria-label="4 of 4">
          <img src="image5.jpg" alt="Gardening tools on a wooden table">
          <figcaption class="hero-caption">Tools & Soil • Everything you need</figcaption>
        </article>
      </div>

      <!-- Controls -->
      <button class="hero-nav prev" aria-label="Previous slide">‹</button>
      <button class="hero-nav next" aria-label="Next slide">›</button>

      <!-- Dots -->
      <div class="hero-dots" id="heroDots" role="tablist" aria-label="Select hero slide"></div>
    </div>
  </div>
</section>


  <!-- ===== CATEGORIES ===== -->
  <section class="categories categories-round" id="categories">
  <div class="container">
    <div class="section-title" style="display:flex;align-items:flex-end;justify-content:space-between;gap:12px;">
      <div>
        <h2>Explore Vanniddhi</h2>
        <p>Discover hand-picked collections for every vibe and space</p>
      </div>
      <a href="products.php" class="btn btn-outline" style="white-space:nowrap">View all</a>
    </div>

    <div class="cat-scroller">
      <button class="cat-nav prev" aria-label="Scroll categories left">‹</button>

      <div class="cat-track" id="catTrack" tabindex="0" role="region" aria-label="Shop by category">
        <!-- Use slugs that match products.php -->
        <a class="cat-card" href="products.php?category=preserved-nature-tabletops" aria-label="Preserved Nature Tabletops">
          <div class="cat-pic">
            <img src="image.jpg" alt="Preserved nature tabletop decor in glass jar" loading="lazy">
          </div>
          <h4>Preserved Nature Tabletops</h4>
        </a>

        <a class="cat-card" href="products.php?category=plants" aria-label="Plants">
          <div class="cat-pic">
            <img src="image2.jpg" alt="Assortment of lush indoor plants" loading="lazy">
          </div>
          <h4>Plants</h4>
        </a>

        <a class="cat-card" href="products.php?category=flower-bulbs" aria-label="Flower Bulbs">
          <div class="cat-pic">
            <img src="image3.jpg" alt="Pink lilies blooming" loading="lazy">
          </div>
          <h4>Flower Bulbs</h4>
        </a>

        <a class="cat-card" href="products.php?category=moss-flowers" aria-label="Moss Flowers">
          <div class="cat-pic">
            <img src="image5.jpg" alt="Artistic green moss decor" loading="lazy">
          </div>
          <h4>Moss Flowers</h4>
        </a>

        <a class="cat-card" href="products.php?category=succulents" aria-label="Succulents">
          <div class="cat-pic">
            <img src="succelants.jpg" alt="Assorted succulents" loading="lazy">
          </div>
          <h4>Succulents</h4>
        </a>

        <a class="cat-card" href="products.php?category=air-purifying" aria-label="Air Purifying">
          <div class="cat-pic">
            <img src="https://images.unsplash.com/photo-1501004318641-b39e6451bec6?q=80&w=1200&auto=format&fit=crop#air" alt="Air-purifying indoor plants" loading="lazy">
          </div>
          <h4>Air Purifying</h4>
        </a>

        <a class="cat-card" href="products.php?category=pots-and-planters" aria-label="Pots and Planters">
          <div class="cat-pic">
            <img src="pots.jpg" alt="Stylish ceramic planters" loading="lazy">
          </div>
          <h4>Pots &amp; Planters</h4>
        </a>

        <a class="cat-card" href="products.php?category=seeds" aria-label="Seeds">
          <div class="cat-pic">
            <img src="seeds.jpg" alt="Gardener sowing seeds" loading="lazy">
          </div>
          <h4>Seeds</h4>
        </a>
      </div>

      <button class="cat-nav next" aria-label="Scroll categories right">›</button>
    </div>
  </div>
</section>

  <!-- ===== FEATURED PRODUCTS (dynamic PHP) ===== -->
  <section class="featured-products" id="featured">
    <div class="container">
      <div class="section-title">
        <h2>Best Sellers</h2>
        <p>Hand‑picked bestsellers and easy keepers for every corner</p>
      </div>
      <div class="products-grid">
        <?php while($p = $featuredProducts->fetch_assoc()): ?>
          <div class="product-card" onclick="window.location='product.php?id=<?= $p['id'] ?>'">
            <div class="product-badge">Featured</div>
            <div class="product-img">
              <img src="<?= $p['thumbnail'] ?>" alt="<?= htmlspecialchars($p['name']) ?>">
              <?php if(!empty($p['video'])): ?>
                <video src="<?= $p['video'] ?>" muted loop></video>
              <?php endif; ?>
            </div>
            <div class="product-info">
              <h3><?= htmlspecialchars($p['name']) ?></h3>
              <p class="meta"><?= htmlspecialchars($p['category']) ?></p>
              <div class="product-price">
                <div class="price">₹<?= $p['price'] ?></div>
                <div class="qty-wrap" onclick="event.stopPropagation();">
                  <input type="number" value="1" min="1">
                  <button class="wish" 
        aria-label="Add to wishlist" 
        data-id="<?= (int)$p['id'] ?>" 
        onclick="toggleWishlist(event, <?= (int)$p['id'] ?>)">
  <i class="fas fa-heart"></i>
</button>

                  <button class="btn btn-primary add-btn" onclick="addToCart('<?= htmlspecialchars($p['name']) ?>', <?= (float)$p['price'] ?>, '<?= $p['thumbnail'] ?>'); event.stopPropagation();">Add</button>
                </div>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
  </section>

  <!-- ===== SERVICES ===== -->
 <!-- ===== SERVICES ===== -->
<section class="services">
  <div class="container">
    <div class="section-title reveal">
      <h2>Why Choose Vanniddhi?</h2>
      <p>We provide the best services to make your gardening experience wonderful</p>
    </div>

    <div class="services-grid">
      <div class="service-card reveal">
        <div class="service-icon"><i class="fas fa-shipping-fast"></i></div>
        <div>
          <h3>Free Shipping</h3>
          <p>Free delivery on orders above ₹999.</p>
        </div>
      </div>

      <div class="service-card reveal">
        <div class="service-icon"><i class="fas fa-seedling"></i></div>
        <div>
          <h3>Quality Plants</h3>
          <p>Carefully selected & nurtured.</p>
        </div>
      </div>

      <div class="service-card reveal">
        <div class="service-icon"><i class="fas fa-headset"></i></div>
        <div>
          <h3>Expert Support</h3>
          <p>Plant care help whenever you need.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== TESTIMONIALS ===== -->
<section class="testimonials">
  <div class="container">
    <div class="section-title reveal">
      <h2>What Our Customers Say</h2>
      <p>Read experiences from our happy plant lovers</p>
    </div>

    <div class="testimonials-grid">
      <div class="testimonial-card reveal">
        <div class="testimonial-text">“Beautiful healthy plants and top-notch packing. My balcony looks alive now!”</div>
        <div class="testimonial-author">
          <div class="author-img">
            <img src="https://images.unsplash.com/photo-1494790108755-2616b612b786?w=120&q=80" alt="Sarah Johnson" style="width:100%;height:100%;object-fit:cover">
          </div>
          <div class="author-info">
            <h4>Sarah Johnson</h4>
            <p>Interior Designer</p>
          </div>
        </div>
      </div>

      <div class="testimonial-card reveal">
        <div class="testimonial-text">“Great variety and quick delivery. The succulents were perfect.”</div>
        <div class="testimonial-author">
          <div class="author-img">
            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=120&q=80" alt="Michael Chen" style="width:100%;height:100%;object-fit:cover">
          </div>
          <div class="author-info">
            <h4>Michael Chen</h4>
            <p>Plant Enthusiast</p>
          </div>
        </div>
      </div>

      <div class="testimonial-card reveal">
        <div class="testimonial-text">“New to plants but Vanniddhi made it easy with care tips and support.”</div>
        <div class="testimonial-author">
          <div class="author-img">
            <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=120&q=80" alt="Emily Rodriguez" style="width:100%;height:100%;object-fit:cover">
          </div>
          <div class="author-info">
            <h4>Emily Rodriguez</h4>
            <p>Beginner</p>
          </div>
        </div>
      </div>
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
  <!-- ===== SCRIPTS: Keep your original functionality ===== -->
  <script>
    // Mobile menu toggle
    const mobileMenu = document.querySelector('.mobile-menu');
    const navLinks = document.querySelector('.nav-links');
    if (mobileMenu && navLinks){
      mobileMenu.addEventListener('click', () => {
        navLinks.style.display = (navLinks.style.display === 'flex') ? 'none' : 'flex';
      });
    }

    // Countdown (3 days from now)
    const countdownEl = document.getElementById('countdown');
    if (countdownEl){
      const end = new Date(Date.now() + 3*24*60*60*1000);
      const t = setInterval(()=>{
        const d = end - Date.now();
        if (d <= 0){ countdownEl.textContent = 'Offer Expired!'; clearInterval(t); return; }
        const days = Math.floor(d/86400000), hours = Math.floor((d%86400000)/3600000), minutes = Math.floor((d%3600000)/60000), seconds = Math.floor((d%60000)/1000);
        countdownEl.textContent = `⏳ ${days}d ${hours}h ${minutes}m ${seconds}s`;
      }, 1000);
    }

    // Hover video play/pause on product cards
    document.querySelectorAll('.product-card').forEach(card => {
      const video = card.querySelector('video');
      if(video){
        card.addEventListener('mouseenter', () => { video.currentTime = 0; video.play(); });
        card.addEventListener('mouseleave', () => { video.pause(); video.currentTime = 0; });
      }
    });

    // Cart: same localStorage logic as your original
    function addToCart(name, price, img){
      let cart = JSON.parse(localStorage.getItem('cart')) || [];
      price = parseFloat(price);
      const card = event.target.closest('.product-card');
      const qtyInput = card ? card.querySelector("input[type='number']") : null;
      const qty = qtyInput ? parseInt(qtyInput.value) || 1 : 1;
      const existing = cart.find(i => i.name === name);
      if (existing) existing.qty += qty; else cart.push({ name, price, qty, img });
      localStorage.setItem('cart', JSON.stringify(cart));
      updateCartCount();
      alert(`${name} added to cart!`);
    }

    function updateCartCount(){
      const cart = JSON.parse(localStorage.getItem('cart')) || [];
      const total = cart.reduce((s,i)=> s + (i.qty||0), 0);
      const badge = document.getElementById('cartCount');
      if (badge) badge.textContent = total > 0 ? total : 0;
    }

    window.addEventListener('storage', (e)=>{ if (e.key === 'cart') updateCartCount(); });
    document.addEventListener('DOMContentLoaded', updateCartCount);
  </script>
<script>
(function(){
  const track   = document.getElementById('heroTrack');
  if (!track) return;
  const slides  = Array.from(track.children);
  const dotsWrap= document.getElementById('heroDots');
  const prevBtn = document.querySelector('.hero-nav.prev');
  const nextBtn = document.querySelector('.hero-nav.next');

  // Build dots
  slides.forEach((slide, i) => {
    const dot = document.createElement('button');
    dot.type = 'button';
    dot.setAttribute('role', 'tab');
    dot.setAttribute('aria-controls', slide.id || `hero-slide-${i+1}`);
    dot.addEventListener('click', () => {
      track.scrollTo({ left: i * track.clientWidth, behavior: 'smooth' });
    });
    dotsWrap.appendChild(dot);
  });
  const dots = Array.from(dotsWrap.children);

  function setActive(i){
    dots.forEach((d, idx) => {
      const active = idx === i;
      d.classList.toggle('active', active);
      d.setAttribute('aria-selected', active ? 'true' : 'false');
    });
  }
  setActive(0);

  // Observe current slide for dot state
  const io = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if(entry.isIntersecting){
        const index = slides.indexOf(entry.target);
        if (index >= 0) setActive(index);
      }
    });
  }, { root: track, threshold: 0.6 });
  slides.forEach(s => io.observe(s));

  // Prev/Next click
  prevBtn.addEventListener('click', () => { stopAuto(); track.scrollBy({ left: -track.clientWidth, behavior: 'smooth' }); });
  nextBtn.addEventListener('click', () => { stopAuto(); track.scrollBy({ left:  track.clientWidth, behavior: 'smooth' }); });

  // Click on slide: left half = prev, right half = next (ignore buttons/links)
  track.addEventListener('click', (e) => {
    if (e.target.closest('a, button')) return;
    const rect = track.getBoundingClientRect();
    const x = e.clientX - rect.left;
    (x > rect.width / 2 ? nextBtn : prevBtn).click();
  });

  // Keyboard support
  track.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowRight') { stopAuto(); nextBtn.click(); }
    if (e.key === 'ArrowLeft')  { stopAuto(); prevBtn.click(); }
  });

  // Mouse/touch drag-to-scroll (desktop + mobile)
  let isDown = false, startX = 0, startScroll = 0, pointerId = null, dragged = false;
  track.addEventListener('pointerdown', (e) => {
    pointerId = e.pointerId;
    track.setPointerCapture(pointerId);
    isDown = true; dragged = false;
    startX = e.clientX;
    startScroll = track.scrollLeft;
    track.classList.add('grabbing');
    stopAuto();
  });
  track.addEventListener('pointermove', (e) => {
    if (!isDown) return;
    const dx = e.clientX - startX;
    if (Math.abs(dx) > 3) dragged = true;
    track.scrollLeft = startScroll - dx;
  });
  function endDrag(e){
    if (!isDown) return;
    isDown = false;
    track.classList.remove('grabbing');
    if (pointerId !== null) {
      try { track.releasePointerCapture(pointerId); } catch {}
      pointerId = null;
    }
    // snap assist: go to closest slide after drag
    const idx = Math.round(track.scrollLeft / track.clientWidth);
    track.scrollTo({ left: idx * track.clientWidth, behavior: 'smooth' });
    startAutoSoon();
  }
  track.addEventListener('pointerup', endDrag);
  track.addEventListener('pointerleave', endDrag);
  track.addEventListener('pointercancel', endDrag);

  // Map vertical wheel/trackpad to horizontal scroll
  track.addEventListener('wheel', (e) => {
    if (Math.abs(e.deltaY) > Math.abs(e.deltaX)) {
      e.preventDefault();
      track.scrollLeft += e.deltaY;
      stopAuto();
      startAutoSoon();
    }
  }, { passive: false });

  // ===== Autoplay (pauses on hover/interaction/hidden tab) =====
  const AUTOPLAY_MS = 4550;
  let auto = null, resumeTimer = null;

  function autoNext(){
    const nextLeft = Math.round((track.scrollLeft + track.clientWidth + 1) / track.clientWidth) * track.clientWidth;
    const maxLeft  = (slides.length - 1) * track.clientWidth;
    const left     = nextLeft > maxLeft ? 0 : nextLeft;
    track.scrollTo({ left, behavior: 'smooth' });
  }
  function startAuto(){
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    if (auto) return;
    auto = setInterval(autoNext, AUTOPLAY_MS);
  }
  function stopAuto(){
    clearInterval(auto); auto = null;
    clearTimeout(resumeTimer);
  }
  function startAutoSoon(){
    clearTimeout(resumeTimer);
    resumeTimer = setTimeout(startAuto, 2500);
  }

  track.addEventListener('mouseenter', stopAuto);
  track.addEventListener('mouseleave', startAutoSoon);
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) stopAuto(); else startAutoSoon();
  });

  startAuto();
})();
</script>

  <!-- Keep your external scripts if needed -->
  <script src="script.js" defer></script>
  <script src="cart.js" defer></script>
  <script>
/* ===== Scroll Reveal: fade/slide elements as they enter viewport ===== */
(function(){
  const els = document.querySelectorAll(`
    .section-title, 
    .category-card, 
    .product-card, 
    .service-card, 
    .testimonial-card, 
    .footer-col
  `);
  els.forEach((el, i) => {
    el.classList.add('reveal');
    if (el.classList.contains('category-card') || el.classList.contains('footer-col')){
      el.classList.add( (i % 2) ? 'reveal-right' : 'reveal-left' );
    }
  });

  const io = new IntersectionObserver((entries)=>{
    entries.forEach(e=>{
      if(e.isIntersecting){ e.target.classList.add('reveal-in'); io.unobserve(e.target); }
    });
  }, { threshold: .15 });
  els.forEach(el=> io.observe(el));
})();

/* ===== Magnetic Buttons + Ripple ===== */
(function(){
  const btns = document.querySelectorAll('.btn');
  btns.forEach(btn=>{
    // magnetic move
    btn.addEventListener('mousemove', (e)=>{
      const r = btn.getBoundingClientRect();
      const dx = e.clientX - (r.left + r.width/2);
      const dy = e.clientY - (r.top  + r.height/2);
      btn.classList.add('magnet-move');
      btn.style.transform = `translate(${dx*0.06}px, ${dy*0.06}px)`;
    });
    btn.addEventListener('mouseleave', ()=>{
      btn.style.transform = '';
      btn.classList.remove('magnet-move');
    });
    // ripple
    btn.addEventListener('click', (e)=>{
      const r = btn.getBoundingClientRect();
      const ripple = document.createElement('span');
      ripple.className = 'ripple';
      ripple.style.left = `${e.clientX - r.left}px`;
      ripple.style.top  = `${e.clientY - r.top }px`;
      ripple.style.width = ripple.style.height = `${Math.max(r.width, r.height)}px`;
      btn.appendChild(ripple);
      setTimeout(()=> ripple.remove(), 600);
    });
  });
})();

/* ===== Header hide on scroll down, show on scroll up ===== */
(function(){
  const header = document.querySelector('header');
  if (!header) return;
  let lastY = window.scrollY, state = 'pinned';
  window.addEventListener('scroll', ()=>{
    const y = window.scrollY;
    const goingDown = y > lastY;
    if (y > 120){
      if (goingDown && state !== 'unpinned'){ header.classList.remove('headroom--pinned'); header.classList.add('headroom--unpinned'); state='unpinned'; }
      if (!goingDown && state !== 'pinned'){ header.classList.remove('headroom--unpinned'); header.classList.add('headroom--pinned'); state='pinned'; }
    } else {
      header.classList.remove('headroom--unpinned'); header.classList.add('headroom--pinned'); state='pinned';
    }
    lastY = y;
  }, { passive:true });
})();

/* ===== Product card: gentle 3D tilt (mouse/parallax) ===== */
(function(){
  const cards = document.querySelectorAll('.product-card');
  cards.forEach(card=>{
    let raf = null;
    const onMove = (e)=>{
      const r = card.getBoundingClientRect();
      const x = (e.clientX - r.left)/r.width - .5;
      const y = (e.clientY - r.top )/r.height - .5;
      if (raf) cancelAnimationFrame(raf);
      raf = requestAnimationFrame(()=>{
        card.style.transform = `rotateX(${ -y*4 }deg) rotateY(${ x*6 }deg) translateY(-6px)`;
      });
    };
    const reset = ()=>{
      if (raf) cancelAnimationFrame(raf);
      card.style.transform = '';
    };
    card.addEventListener('mousemove', onMove);
    card.addEventListener('mouseleave', reset);
  });
})();

/* ===== Progressive perf: lazy-load images if not already ===== */
(function(){
  document.querySelectorAll('img:not([loading])').forEach(img => { img.loading = 'lazy'; });
})();
</script>
<script>
(function(){
  const track = document.getElementById('catTrack');
  if (!track) return;
  const prev  = document.querySelector('.cat-nav.prev');
  const next  = document.querySelector('.cat-nav.next');

  const CARD = () => track.firstElementChild?.getBoundingClientRect().width || 220;
  function updateArrows(){
    const max = track.scrollWidth - track.clientWidth - 2;
    prev.disabled = track.scrollLeft <= 2;
    next.disabled = track.scrollLeft >= max;
  }
  function scrollByCards(n){
    track.scrollBy({ left: n * (CARD()+parseFloat(getComputedStyle(track).columnGap||0)), behavior:'smooth' });
  }

  // click arrows
  prev.addEventListener('click', ()=> scrollByCards(-2));
  next.addEventListener('click', ()=> scrollByCards(+2));

  // wheel → horizontal
  track.addEventListener('wheel', (e)=>{
    if (Math.abs(e.deltaY) > Math.abs(e.deltaX)) {
      e.preventDefault();
      track.scrollLeft += e.deltaY;
      updateArrows();
    }
  }, { passive:false });

  // drag to scroll (mouse & touch)
  let down=false, startX=0, startLeft=0, pid=null;
  track.addEventListener('pointerdown', (e)=>{
    pid = e.pointerId; track.setPointerCapture(pid);
    down=true; startX=e.clientX; startLeft=track.scrollLeft; track.classList.add('grabbing');
  });
  const end=()=>{ if(!down) return; down=false; track.classList.remove('grabbing'); try{ track.releasePointerCapture(pid); }catch{} };
  track.addEventListener('pointermove', (e)=>{ if(!down) return; track.scrollLeft = startLeft - (e.clientX - startX); updateArrows(); });
  track.addEventListener('pointerup', end); track.addEventListener('pointerleave', end); track.addEventListener('pointercancel', end);

  // keyboard
  track.addEventListener('keydown', (e)=>{
    if (e.key === 'ArrowRight') { e.preventDefault(); scrollByCards(+1); }
    if (e.key === 'ArrowLeft')  { e.preventDefault(); scrollByCards(-1); }
  });

  // update on load/resize/scroll
  const ro = new ResizeObserver(updateArrows); ro.observe(track);
  track.addEventListener('scroll', ()=> requestAnimationFrame(updateArrows));
  updateArrows();
})();
</script>
<script>
/* Mobile menu drawer toggle */
(function(){
  const btn = document.querySelector('.mobile-menu');
  const links = document.querySelector('.nav-links');
  if(!btn || !links) return;
  btn.addEventListener('click', ()=> links.classList.toggle('show'));
  // close when a link is picked
  links.addEventListener('click', e=>{
    if(e.target.closest('a')) links.classList.remove('show');
  });
})();

/* Disable heavy 3D tilt effects on touch devices for smoother scrolling */
(function(){
  const coarse = window.matchMedia('(pointer:coarse)').matches;
  if(!coarse) return;
  document.querySelectorAll('.product-card').forEach(c=> c.classList.remove('tilted'));
})();
</script>
<script>
(function(){
  const items = document.querySelectorAll('.reveal');
  if(!('IntersectionObserver' in window) || !items.length) { items.forEach(i=>i.classList.add('in')); return; }
  const io = new IntersectionObserver((entries)=>{
    entries.forEach(e=>{
      if(e.isIntersecting){ e.target.classList.add('in'); io.unobserve(e.target); }
    });
  }, { threshold: .15 });
  items.forEach(el=> io.observe(el));
})();
</script>
<script>
/* === WISHLIST (localStorage: 'wishlist' = [ids]) === */
function getWishlist(){ try{ return JSON.parse(localStorage.getItem('wishlist'))||[] }catch{ return [] } }
function setWishlist(arr){ localStorage.setItem('wishlist', JSON.stringify(arr)); updateWishlistBadges(); }

function toggleWishlist(ev, id){
  ev.preventDefault(); ev.stopPropagation();
  let list = getWishlist();
  const i = list.indexOf(id);
  if(i===-1) list.push(id); else list.splice(i,1);
  setWishlist(list);
  const btn = ev.currentTarget;
  btn.classList.toggle('active', list.includes(id));
}

function updateWishlistBadges(){
  const list = getWishlist();
  document.querySelectorAll('.wish[data-id]').forEach(b=>{
    const id = +b.dataset.id; b.classList.toggle('active', list.includes(id));
  });
}
document.addEventListener('DOMContentLoaded', updateWishlistBadges);
</script>
<script>
  (function(){
    // Header search → go to products.php?q=...
    const searchWrap = document.querySelector('header .search-bar');
    if (searchWrap){
      const input = searchWrap.querySelector('input');
      const btn   = searchWrap.querySelector('button');
      const go = () => {
        const q = (input.value || '').trim();
        const url = new URL('products.php', location.href);
        if (q) url.searchParams.set('q', q);
        // keep category if already present in current URL
        const existingCat = new URLSearchParams(location.search).get('category');
        if (existingCat) url.searchParams.set('category', existingCat.toLowerCase());
        location.href = url.toString();
      };
      btn.addEventListener('click', go);
      input.addEventListener('keypress', e => { if (e.key === 'Enter') go(); });
    }

    // If user typed a query, append it to category-card clicks: products.php?category=...&q=...
    const headerInput = document.querySelector('header .search-bar input');
    document.querySelectorAll('.cat-card').forEach(a => {
      a.addEventListener('click', (e) => {
        const q = headerInput ? headerInput.value.trim() : '';
        if (!q) return; // normal navigation
        e.preventDefault();
        const u = new URL(a.href, location.href);
        u.searchParams.set('q', q);
        location.href = u.toString();
      });
    });
  })();
</script>
<script>
/* FORCE NAVIGATION from Explore Vanniddhi cards, even if drag script blocks clicks */
(function () {
  let down = null; // remember where pointer started on a cat-card

  // record press on a .cat-card (before other handlers)
  document.addEventListener('pointerdown', function (e) {
    const a = e.target.closest('a.cat-card');
    if (a) {
      down = { x: e.clientX, y: e.clientY, a: a };
    }
  }, true); // <-- capture

  // on release: if it wasn't a drag, go to link NOW (override any blockers)
  document.addEventListener('pointerup', function (e) {
    if (!down) return;
    const a = e.target.closest('a.cat-card') || down.a;
    const dist = Math.hypot(e.clientX - down.x, e.clientY - down.y);
    down = null;

    if (a && dist < 6) {               // small move => treat as click
      e.stopImmediatePropagation();     // stop any other handlers
      e.preventDefault();               // ignore blockers
      location.href = a.href;           // hard redirect
    }
  }, true); // <-- capture

  // keyboard support (Enter/Space on focused card)
  document.addEventListener('keydown', function (e) {
    if ((e.key === 'Enter' || e.key === ' ') && e.target.closest('a.cat-card')) {
      e.stopImmediatePropagation();
      e.preventDefault();
      location.href = e.target.closest('a.cat-card').href;
    }
  }, true);
})();
</script>
<script>
/* Make the whole HERO (scroller) clickable → products.php
   - Works even if other handlers consume the click
   - Ignores drags/swipes
   - Does NOT interfere with arrows, dots, or links/buttons inside
*/
(function () {
  const hero = document.querySelector('.hero.hero-scroller');
  if (!hero) return;

  let start = null;

  // remember where the press started (capture phase = before other handlers)
  hero.addEventListener('pointerdown', function (e) {
    // let built-in controls work as-is
    if (e.target.closest('a, button, .hero-nav, .hero-dots')) return;
    start = { x: e.clientX, y: e.clientY };
  }, true);

  // on release, if it wasn't a drag and not on a control → go to products.php
  hero.addEventListener('pointerup', function (e) {
    if (!start) return;
    const dist = Math.hypot(e.clientX - start.x, e.clientY - start.y);
    start = null;

    // ignore if user dragged/swiped or clicked a control/link
    if (dist > 10 || e.target.closest('a, button, .hero-nav, .hero-dots')) return;

    e.preventDefault();
    e.stopImmediatePropagation(); // beat any other click handlers
    window.location.href = 'products.php';
  }, true);

  // keyboard accessibility: Enter/Space inside hero (but not on controls)
  hero.addEventListener('keydown', function (e) {
    if ((e.key === 'Enter' || e.key === ' ') && !e.target.closest('a, button, .hero-nav, .hero-dots')) {
      e.preventDefault();
      window.location.href = 'products.php';
    }
  }, true);
})();
</script>

</body>
</html>