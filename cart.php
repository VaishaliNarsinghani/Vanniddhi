<?php
// cart.php — Vanniddhi-themed + Payment Mode (Hosted PG Ready)
$news = [
  "💠 Welcome to Vanniddhi Plant Nursery — premium plants & decor 💠",
  "💠 Free delivery on orders above ₹999 (Indore city limits) 💠",
  "💠 Same-day dispatch on prepaid orders placed before 4 PM 💠",
  "💠 Festive Season: Extended hours — store open till 11 PM, all week 💠",
  "💠 Pick-up: Shop No. 04, Temp. Cracker Market, Chhota Bangarda 💠",
  "💠 Need help choosing? Expert support & plant care guidance 💠",
  "💠 Preserved Nature Tabletops & Moss Frames now in stock 💠",
  "💠 Combo packs & bulk orders available — limited stock 💠",
  "💠 Secure payments: UPI / Card / NetBanking 💠",
  "💠 Helpline: 94250 46286 💠"
];
$tickerText = implode(' • ', array_map('htmlspecialchars', $news));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>Your Cart • Vanniddhi</title>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">

<style>
  :root{ --primary:#2e7d32; --primary-light:#4caf50; --primary-dark:#1b5e20; --secondary:#ff9800; --accent:#8bc34a;
    --light:#f8fdf8; --dark:#1a331c; --text:#333; --white:#fff; --gray:#f5f5f5; --border:#e0e0e0;
    --container-pad:16px; --mobile-bar-h:68px; }
  *{ box-sizing:border-box; margin:0; padding:0 }
  body{ font-family:'Open Sans',sans-serif; color:var(--text); background:var(--light); line-height:1.6; overflow-x:hidden }
  h1,h2,h3,h4{ font-family:'Poppins',sans-serif; font-weight:600; line-height:1.3 }
  .container{ width:100%; max-width:1400px; margin:0 auto; padding:0 var(--container-pad) }

  .top-bar{ background:var(--primary-dark); color:#fff; font-size:.92rem }
  .top-bar-content{ min-height:40px; display:flex; align-items:center; justify-content:space-between; gap:16px }
  .ticker-track{ overflow:hidden; white-space:nowrap; flex:1 }
  .ticker-line{ display:inline-block; padding-left:100%; animation:ticker 28s linear infinite }
  @keyframes ticker{ from{transform:translateX(0)} to{transform:translateX(-100%)} }
  .top-bar-links a{ color:#fff; text-decoration:none; margin-left:18px }
  .top-bar-links a:hover{ color:var(--accent) }
  @media (max-width:768px){ .top-bar{ display:none } }

  header{ background:#fff; box-shadow:0 2px 10px rgba(0,0,0,.08); position:sticky; top:0; z-index:1000 }
  .header-container{ display:flex; justify-content:space-between; align-items:center; padding:0; gap:12px; flex-wrap:wrap }
  .logo{ display:flex; align-items:center; gap:10px; text-decoration:none }
  .logo-icon{ width:100px; height:100px }
  .logo-icon img{ width:100%; height:100%; object-fit:contain; display:block }
  .logo-wordmark{ display:flex; flex-direction:column; line-height:1 }
  .logo-name{
    font-family:'Poppins',sans-serif; font-weight:800; letter-spacing:.2px;
    font-size: clamp(1.15rem, 2.2vw, 1.4rem);
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    -webkit-background-clip:text; background-clip:text; color:transparent;
  }
  .logo-tag{ margin-top:4px; font-weight:700; font-size:.72rem; letter-spacing:.12em; text-transform:uppercase; color:#5c7a60 }
  .search-bar{ flex:1; max-width:1020px; position:relative; order:3; width:100% }
  .search-bar input{ width:100%; height:44px; padding:0 16px; border:1px solid var(--border); border-radius:12px; outline:none }
  .search-bar input:focus{ border-color:var(--primary) }
  .search-bar button{ position:absolute; right:6px; top:5px; height:34px; padding:0 14px; border:none; border-radius:10px; cursor:pointer; background:var(--primary); color:#fff }

  .header-actions{ display:flex; align-items:center }
  .header-action{ position:relative; margin-left:18px; display:flex; flex-direction:column; align-items:center; color:var(--text); text-decoration:none }
  .header-action i{ font-size:1.3rem; margin-bottom:4px }
  .header-action:hover{ color:var(--primary) }
  #cartCount{ position:absolute; top:-8px; right:-8px; width:18px; height:18px; border-radius:50%; background:var(--secondary); color:#fff; display:flex; align-items:center; justify-content:center; font-size:.7rem; font-weight:800 }
  @media (max-width:576px){ .header-action span{ display:none } }

  nav{ background:var(--gray); padding:12px 0 }
  .nav-container{ display:flex; justify-content:space-between; align-items:center }
  .nav-links{ list-style:none; display:flex; gap:18px }
  .nav-links a{ color:var(--text); text-decoration:none; font-weight:600 }
  .nav-links a:hover{ color:var(--primary) }
  .mobile-menu{ display:none; font-size:1.5rem; color:var(--primary); cursor:pointer }
  @media (max-width:992px){
    .mobile-menu{ display:block }
    .nav-links{ display:none; position:fixed; inset:60px 0 0 0; background:#fff; padding:18px var(--container-pad) calc(18px + env(safe-area-inset-bottom)); flex-direction:column; gap:12px; overflow:auto; z-index:1200; box-shadow:0 12px 30px rgba(0,0,0,.12) }
    .nav-links.show{ display:flex }
  }

  .page-hero{ background:linear-gradient(180deg,#ffffff, #f4fbf4); padding:18px 0 }
  .page-hero .intro{ text-align:center }
  .page-hero h1{ color:var(--primary); font-size:clamp(1.25rem, 4.5vw, 1.8rem) }

  section.cart-page{ padding:20px 0 40px }
  .cart-container{ display:grid; grid-template-columns:2fr 1fr; gap:22px }
  @media (max-width:900px){ .cart-container{ grid-template-columns:1fr } }

  .card{ background:#fff; border:1px solid var(--border); border-radius:16px; box-shadow:0 8px 18px rgba(0,0,0,.06) }
  .card-pad{ padding:16px }
  .cart-items{ display:flex; flex-direction:column; gap:12px }
  .cart-item{ display:grid; grid-template-columns:92px 1fr auto; gap:12px; align-items:center;
    border:1px solid var(--border); border-radius:14px; padding:10px; background:#fbfdfb; }
  .cart-item img{ width:92px; height:92px; object-fit:cover; border-radius:10px; background:#f2f5f2 }
  .ci-name{ font-weight:700; color:var(--primary); font-size:1rem }
  .ci-price{ font-weight:800; color:var(--primary-dark); margin-top:6px; font-size:1.05rem }
  .qty-controls{ display:flex; align-items:center; gap:8px; justify-self:end; }
  .qty-controls button{ width:40px; height:40px; border:0; border-radius:12px; cursor:pointer;
    background:linear-gradient(135deg,var(--primary),var(--primary-light)); color:#fff; font-weight:900; font-size:1rem; }
  .qty-controls input{ width:56px; height:40px; text-align:center; border:1px solid var(--border); border-radius:12px; font-weight:800; font-size:1rem; }
  .remove-btn{ border:0; background:#dc3545; color:#fff; border-radius:12px; padding:10px 12px; cursor:pointer; font-weight:800; margin-left:6px }
  @media (max-width:600px){
    .cart-item{ grid-template-columns:72px 1fr; grid-template-rows:auto auto }
    .cart-item img{ width:72px; height:72px }
    .qty-controls{ grid-column:1 / -1; justify-self:start }
  }

  .empty-cart{ text-align:center; padding:26px; border:1px dashed #cfe2cf; border-radius:14px }
  .browse-btn{ display:inline-block; margin-top:10px; padding:12px 16px; border-radius:12px; background:linear-gradient(135deg,var(--primary),var(--primary-light)); color:#fff; text-decoration:none; font-weight:800 }

  .order-summary{ position:sticky; top:80px; height:max-content }
  .order-summary h3{ color:var(--primary); margin-bottom:10px; font-size:1.05rem }
  .summary-row{ display:flex; justify-content:space-between; margin:6px 0; color:#334; font-size:1rem }
  .summary-row.total{ font-weight:900; font-size:1.15rem; color:var(--primary-dark); margin-top:6px }

  form#checkoutForm{ margin-top:12px; display:grid; gap:10px }
  #checkoutForm input, #checkoutForm textarea{
    width:100%; padding:14px 12px; border:1px solid var(--border); border-radius:12px; font-size:1rem; outline:none; background:#fff; }
  #checkoutForm textarea{ min-height:90px; resize:vertical }
  .checkout-btn{ height:48px; border:none; border-radius:12px; background:linear-gradient(135deg,var(--primary),var(--primary-light)); color:#fff;
    font-weight:900; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; box-shadow:0 10px 18px rgba(46,125,50,.25); }
  .checkout-btn:active{ transform:translateY(1px) }

  .pay-options{ display:flex; gap:12px; align-items:center; flex-wrap:wrap; margin:4px 0 4px; }
  .pay-options label{ display:flex; align-items:center; gap:8px; padding:8px 10px; border:1px solid var(--border);
    border-radius:12px; cursor:pointer; font-weight:700; color:#2f3b31; background:#fff; }

  .mobile-checkout{ position:fixed; left:0; right:0; bottom:0; display:none; align-items:center; justify-content:space-between; gap:12px;
    background:#fff; padding:10px 14px calc(10px + env(safe-area-inset-bottom)); border-top:1px solid var(--border); box-shadow:0 -10px 24px rgba(0,0,0,.08); z-index:1201; }
  .mobile-checkout .total{ font-weight:900; font-size:1.1rem; color:var(--primary-dark) }
  .mobile-checkout button{ height:44px; padding:0 18px; border:0; border-radius:12px; background:linear-gradient(135deg,var(--primary),var(--primary-light)); color:#fff; font-weight:900; cursor:pointer; }
  @media (max-width:900px){ .mobile-checkout{ display:flex } body{ padding-bottom: calc(var(--mobile-bar-h) + env(safe-area-inset-bottom)); } }

  footer{ background:var(--dark); color:#fff; padding:60px 0 28px; margin-top:30px }
  .footer-container{ display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:26px; margin-bottom:32px }
  .copyright{ text-align:center; padding-top:18px; border-top:1px solid rgba(255,255,255,.12); color:#bbb; font-size:.92rem }

  .modal{ position: fixed; inset: 0; display: none; align-items: center; justify-content: center; background: rgba(0,0,0,.55); z-index: 3000 }
  .modal.show{ display:flex }
  .modal-content{ width: min(480px, 92vw); background:#fff; border-radius:16px; padding:22px; text-align:center; box-shadow:0 18px 70px rgba(0,0,0,.25) }
  .modal-content h2{ margin:0 0 8px; color:#2e7d32 }
  .modal-content p{ margin:0 0 14px; color:#333 }
  .modal-buttons{ display:flex; gap:12px; justify-content:center; margin-top:10px }
  .modal-buttons button{ border:0; border-radius:12px; padding:10px 16px; font-weight:800; cursor:pointer }
  #confirmYes{ background:#2e7d32; color:#fff }
  #confirmNo{ background:#dc3545; color:#fff }

  #redirectOverlay{
    position:fixed; inset:0; display:none; align-items:center; justify-content:center;
    background:rgba(255,255,255,.85); z-index:4000; backdrop-filter:blur(2px);
    font-weight:800; color:#1b5e20; font-size:1.1rem;
  }
  #redirectOverlay.show{ display:flex }
  @keyframes ticker{ from{transform:translateX(0)} to{transform:translateX(-100%)} }
</style>
</head>
<body>

  <!-- TOP BAR -->
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

  <!-- HEADER -->
  <header>
    <div class="container header-container">
      <a href="index.php" class="logo" aria-label="Vanniddhi – home">
        <div class="logo-icon"><img src="vanniddhi.png" alt="Vanniddhi logo"></div>
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
          <li><a href="index.php">Home</a></li>
          <li><a href="products.php">Products</a></li>
          <li><a href="#" style="color:var(--primary);font-weight:700">My Cart</a></li>
        </ul>
        <div class="nav-offer"><a href="products.php" class="btn btn-primary" style="padding:10px 18px">Special Offers</a></div>
      </div>
    </nav>
  </header>

  <!-- PAGE INTRO -->
  <section class="page-hero">
    <div class="container intro">
      <h1>Your Cart</h1>
      <p>Review items, choose payment mode, and finish checkout.</p>
    </div>
  </section>

  <!-- CART -->
  <section class="cart-page">
    <div class="container cart-container">
      <!-- Items -->
      <div class="card card-pad">
        <h3 style="color:var(--primary);margin-bottom:8px">Items</h3>
        <div class="cart-items" id="cartItems"></div>
      </div>

      <!-- Summary -->
      <aside class="order-summary card card-pad">
        <h3>Order Summary</h3>
        <div class="summary-row">
          <span>Subtotal</span>
          <span>₹<span id="subtotal">0.00</span></span>
        </div>
        <div class="summary-row total">
          <span>Total</span>
          <span>₹<span id="total">0.00</span></span>
        </div>

        <h3 style="margin-top:10px">Enter Your Information</h3>
        <form id="checkoutForm" action="save_invoice.php" method="post">
          <input type="hidden" name="cart" id="cartInput">
          <input type="hidden" name="total" id="totalInput">
          <input type="hidden" name="invoice_number" id="invoiceInput">
          <input type="hidden" name="paymode" id="paymodeInput" value="offline"><!-- set in JS -->

          <input type="text" name="name" id="name" placeholder="Your Name" required>
          <input type="tel" name="phone" id="phone" placeholder="Your Mobile Number" required>
          <textarea name="referred_person" id="referred_person" placeholder="Referred Person Name or Mobile Number (WhatsApp)" required></textarea>
          <textarea name="address" id="address" placeholder="Your Delivery Address" required></textarea>

          <!-- Payment Mode -->
          <div class="pay-options">
            <label><input type="radio" name="paymode_radio" value="online" checked> Pay Online (UPI/Card)</label>
            <label><input type="radio" name="paymode_radio" value="offline"> Download Order Sheet</label>
          </div>

          <button type="submit" class="checkout-btn" id="desktopCheckoutBtn">Continue</button>
        </form>
      </aside>
    </div>
  </section>

  <!-- Sticky mobile checkout bar -->
  <div class="mobile-checkout" id="mobileCheckout" role="region" aria-label="Checkout">
    <div class="total">Total: ₹<span id="mTotal">0.00</span></div>
    <button id="mobileCheckoutBtn" aria-label="Proceed">Checkout</button>
  </div>

  <!-- Modal -->
  <div id="confirmModal" class="modal" aria-hidden="true">
    <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
      <h2 id="confirmTitle">Proceed?</h2>
      <p id="confirmMsg">You will be redirected to secure payment page.</p>
      <div class="modal-buttons">
        <button id="confirmYes">✅ Yes</button>
        <button id="confirmNo">❌ Cancel</button>
      </div>
    </div>
  </div>

  <!-- Redirect overlay -->
  <div id="redirectOverlay"><div>Redirecting to secure payment…</div></div>

  <!-- FOOTER -->
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
            <li><i class="fas fa-clock"></i> Mon–Sat: 9AM – 6PM</li>
          </ul>
        </div>
      </div>
      <div class="copyright">&copy; <?= date('Y') ?> Vanniddhi Plant Nursery. All rights reserved.</div>
    </div>
  </footer>

  <!-- SCRIPTS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script>
    // Mobile nav
    const mobileMenu = document.querySelector('.mobile-menu');
    const navLinks = document.querySelector('.nav-links');
    if (mobileMenu && navLinks){
      mobileMenu.addEventListener('click', ()=> navLinks.classList.toggle('show'));
      navLinks.addEventListener('click', e=>{ if(e.target.closest('a')) navLinks.classList.remove('show'); });
    }

    // Cart state
    let cart = JSON.parse(localStorage.getItem("cart")) || [];

    function updateCartCount() {
      const totalItems = cart.reduce((s, i) => s + (i.qty||0), 0);
      const badge = document.getElementById('cartCount');
      if (badge) badge.textContent = totalItems > 0 ? totalItems : 0;
    }

    function renderCart() {
      const wrap = document.getElementById("cartItems");
      wrap.innerHTML = "";

      if (!cart.length) {
        wrap.innerHTML = `
          <div class="empty-cart">
            <h2>🛒 Your cart is empty</h2>
            <p>Looks like you haven’t added anything yet.</p>
            <a href="products.php" class="browse-btn">Browse Products</a>
          </div>`;
        setTotals(0);
        updateCartCount();
        return;
      }

      let subtotal = 0;
      cart.forEach((item, i) => {
        subtotal += item.price * item.qty;
        const div = document.createElement("div");
        div.className = "cart-item";
        div.innerHTML = `
          <img src="${item.img}" alt="${item.name}">
          <div>
            <div class="ci-name">${item.name}</div>
            <div class="ci-price">₹${Number(item.price).toFixed(2)}</div>
          </div>
          <div class="qty-controls">
            <button onclick="decreaseQty(${i})" aria-label="Decrease">−</button>
            <input type="number" id="qty-${i}" value="${item.qty}" readonly>
            <button onclick="increaseQty(${i})" aria-label="Increase">+</button>
            <button class="remove-btn" onclick="removeItem(${i})" aria-label="Remove">×</button>
          </div>`;
        wrap.appendChild(div);
      });

      setTotals(subtotal);
      updateCartCount();
    }

    function setTotals(amount){
      const total = Number(amount||0).toFixed(2);
      document.getElementById("subtotal").textContent = total;
      document.getElementById("total").textContent = total;
      document.getElementById("mTotal").textContent = total;
    }

    function increaseQty(i){ cart[i].qty++; localStorage.setItem("cart", JSON.stringify(cart)); renderCart(); }
    function decreaseQty(i){ if (cart[i].qty>1) cart[i].qty--; else cart.splice(i,1); localStorage.setItem("cart", JSON.stringify(cart)); renderCart(); }
    function removeItem(i){ cart.splice(i,1); localStorage.setItem("cart", JSON.stringify(cart)); renderCart(); }

    window.addEventListener('storage', (e)=>{ if (e.key === 'cart'){ cart = JSON.parse(localStorage.getItem('cart')) || []; renderCart(); } });
    document.addEventListener('DOMContentLoaded', ()=>{ renderCart(); updateCartCount(); });

    // ====== Checkout Modal + Mode branching ======
    const form   = document.getElementById("checkoutForm");
    const modal  = document.getElementById("confirmModal");
    const yesBtn = document.getElementById("confirmYes");
    const noBtn  = document.getElementById("confirmNo");
    const desktopBtn = document.getElementById("desktopCheckoutBtn");
    const mobileBtn  = document.getElementById("mobileCheckoutBtn");
    const titleEl = document.getElementById("confirmTitle");
    const msgEl   = document.getElementById("confirmMsg");
    const overlay = document.getElementById("redirectOverlay");

    function openModal(mode){
      if(mode === 'online'){
        titleEl.textContent = "Proceed to Payment?";
        msgEl.textContent   = "You will be redirected to a secure payment page.";
      }else{
        titleEl.textContent = "Confirm Download?";
        msgEl.textContent   = "We will generate and download your order sheet (PDF).";
      }
      modal.classList.add("show");
      modal.setAttribute("aria-hidden","false");
    }
    function closeModal(){
      modal.classList.remove("show");
      modal.setAttribute("aria-hidden","true");
    }

    // Intercept ALL submits
    form.addEventListener("submit", (e) => {
      if (form.dataset.skipConfirm === "1"){ form.dataset.skipConfirm = ""; return; }
      e.preventDefault();
      const mode = (document.querySelector('input[name="paymode_radio"]:checked')?.value) || 'online';
      const tot = Number((document.getElementById('total').textContent||'0').trim());
      if (tot <= 0) { alert('Your cart total must be greater than 0.'); return; }
      openModal(mode);
    });

    // Mobile sticky button → same submit
    mobileBtn?.addEventListener("click", () => desktopBtn?.click());

    // Close behaviors
    noBtn.addEventListener("click", closeModal);
    modal.addEventListener("click", (e) => { if (e.target === modal) closeModal(); });
    document.addEventListener("keydown", (e) => { if (e.key === "Escape") closeModal(); });

    // Proceed based on mode
    yesBtn.addEventListener("click", async () => {
      closeModal();
      const mode = (document.querySelector('input[name="paymode_radio"]:checked')?.value) || 'online';
      if(mode === 'online'){
        document.getElementById('paymodeInput').value = 'online';
        await startOnlinePayment(); // redirect to bank/PG
      }else{
        document.getElementById('paymodeInput').value = 'offline';
        generateInvoiceAndSubmitOffline();
      }
    });

    // ====== OFFLINE FLOW (PDF + save_invoice.php) ======
    function generateInvoiceAndSubmitOffline(){
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF();

      const cols = [{x:20,w:80},{x:100,w:30},{x:130,w:20},{x:150,w:40}];
      const headerH=10, pad=3, font=11, lineH=1.15, bottom=280;

      const invoiceNo = "INV-" + Math.floor(Math.random()*900000 + 100000);
      const today = new Date().toLocaleDateString();

      doc.setFont("helvetica","bold"); doc.setFontSize(18);
      doc.text("Vanniddhi Order Sheet", 105, 15, { align: "center" });
      doc.setFontSize(11); doc.setFont("helvetica","normal");
      doc.text("Vanniddhi Plant Nursery", 20, 30);
      doc.text("Phone: +91 94250 46286", 20, 36);
      doc.text(`Invoice #: ${invoiceNo}`, 150, 30);
      doc.text(`Date: ${today}`, 150, 36);

      const name = document.getElementById("name").value;
      const refp = document.getElementById("referred_person").value;
      const phone= document.getElementById("phone").value;
      const addr = document.getElementById("address").value;

      doc.setFont("helvetica","bold"); doc.text("Bill To:", 20, 50);
      doc.setFont("helvetica","normal");
      doc.text(name, 20, 56);
      doc.text(refp, 20, 62);
      doc.text(phone, 20, 68);
      doc.text(doc.splitTextToSize(addr, 80), 20, 74);

      // Table header
      let y = 96;
      doc.setFillColor(46,125,50); doc.setTextColor(255,255,255);
      doc.setFont("helvetica","bold"); doc.setFontSize(font);
      cols.forEach(c => doc.rect(c.x, y, c.w, headerH, "F"));
      doc.text("Item", cols[0].x+pad, y+headerH-3);
      doc.text("Price", cols[1].x+cols[1].w/2, y+headerH-3, {align:"center"});
      doc.text("Qty", cols[2].x+cols[2].w/2, y+headerH-3, {align:"center"});
      doc.text("Total", cols[3].x+cols[3].w-pad, y+headerH-3, {align:"right"});

      doc.setTextColor(0,0,0); doc.setFont("helvetica","normal");

      y += headerH + 4;
      let grand = 0;

      function maybeBreak(h){ if (y + h > bottom){ doc.addPage(); y = 20; } }

      const cartNow = JSON.parse(localStorage.getItem("cart") || "[]");
      cartNow.forEach(it => {
        const nameLines = doc.splitTextToSize(String(it.name), cols[0].w - 2*pad);
        const rowH = Math.max(headerH, (nameLines.length * font * lineH) + 2*pad);
        maybeBreak(rowH);
        cols.forEach(c => doc.rect(c.x, y, c.w, rowH));
        const ty = y + pad + font - 1;
        nameLines.forEach((ln, idx) => doc.text(ln, cols[0].x+pad, ty + idx*font*lineH));
        const yNum = y + (rowH/2) + (font/2) - 2;
        doc.text(`${Number(it.price).toFixed(2)}`, cols[1].x+cols[1].w-pad, yNum, {align:"right"});
        doc.text(String(it.qty), cols[2].x+cols[2].w/2, yNum, {align:"center"});
        doc.text(`${(it.price*it.qty).toFixed(2)}`, cols[3].x+cols[3].w-pad, yNum, {align:"right"});
        grand += it.price * it.qty;
        y += rowH + 4;
      });

      doc.rect(cols[0].x, y, cols[0].w+cols[1].w+cols[2].w, headerH);
      doc.rect(cols[3].x, y, cols[3].w, headerH);
      doc.setFont("helvetica","bold");
      doc.text("Total Amount", cols[0].x+pad, y+headerH-3);
      doc.text(`${grand.toFixed(2)}`, cols[3].x+cols[3].w-pad, y+headerH-3, {align:"right"});
      doc.setFontSize(10); doc.setFont("helvetica","italic");
      doc.text("(Thank you for your business!)", 105, 290, { align: "center" });

      doc.save(`invoice_${invoiceNo}.pdf`);

      // Post to save_invoice.php
      document.getElementById("invoiceInput").value = invoiceNo;
      document.getElementById("cartInput").value    = JSON.stringify(cartNow);
      document.getElementById("totalInput").value   = grand.toFixed(2);

      form.dataset.skipConfirm = "1";
      form.submit();
    }

    // ====== ONLINE FLOW (Redirect/Hosted Checkout) ======
    async function startOnlinePayment(){
      const name  = document.getElementById("name").value.trim();
      const phone = document.getElementById("phone").value.trim();
      const addr  = document.getElementById("address").value.trim();
      const refp  = document.getElementById("referred_person").value.trim();
      const amount= Number((document.getElementById("total").textContent||"0").trim());
      const cartNow = JSON.parse(localStorage.getItem("cart")||"[]");

      if(!cartNow.length){ alert("Cart empty hai."); return; }
      if(!name || !phone || !addr){ alert("Please fill your details (name/phone/address)."); return; }

      try{
        overlay.classList.add('show');
        const res = await fetch('/pay/create-order.php', {
          method:'POST',
          headers:{ 'Content-Type':'application/json' },
          body: JSON.stringify({
            customer: { name, phone, address: addr, ref: refp },
            cart: cartNow,
            amount: amount,       // in rupees
            currency: 'INR',
            client_ref: 'VN-' + Date.now()
          })
        });

        const html = await res.text();
        if(!res.ok || html.startsWith('PG_NOT_CONFIGURED')){
          throw new Error(html || 'Payment gateway not configured');
        }

        // inject form & submit
        const holder = document.createElement('div');
        holder.style.display = 'none';
        holder.innerHTML = html; // contains <form ...> auto-post to bank
        document.body.appendChild(holder);
        const pgForm = holder.querySelector('form');
        if(pgForm){ pgForm.submit(); }
        else{
          const w = window.open('about:blank','_blank');
          w.document.open(); w.document.write(html); w.document.close();
        }
      }catch(err){
        console.error(err);
        overlay.classList.remove('show');
        alert("Unable to start online payment. Please try again or choose Download Order Sheet.");
      }
    }
  </script>

  <!-- Optional Google Translate -->
  <div id="google_translate_element" style="display:none"></div>
  <script>
    function googleTranslateElementInit(){
      if (window.google?.translate?.TranslateElement){
        new google.translate.TranslateElement({pageLanguage:'en', includedLanguages:'en,hi'}, 'google_translate_element');
      }
    }
  </script>
  <script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</body>
</html>
