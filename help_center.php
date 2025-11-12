<?php
// help_center.php
include("db.php");

// Create help_tickets table if it doesn't exist (safe noop if exists)
$conn->query("
  CREATE TABLE IF NOT EXISTS help_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// Handle form submit
$flash = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name    = trim($_POST['name'] ?? '');
  $email   = trim($_POST['email'] ?? '');
  $subject = trim($_POST['subject'] ?? '');
  $message = trim($_POST['message'] ?? '');
  if ($name && filter_var($email, FILTER_VALIDATE_EMAIL) && $subject && $message) {
    $stmt = $conn->prepare("INSERT INTO help_tickets (name, email, subject, message) VALUES (?,?,?,?)");
    $stmt->bind_param("ssss", $name, $email, $subject, $message);
    if ($stmt->execute()) $flash = "Thanks! We’ve received your message and will reply soon.";
    else $flash = "Sorry, something went wrong. Please try again.";
  } else {
    $flash = "Please fill all fields with a valid email.";
  }
}

// FAQ data (edit/add freely)
$faqs = [
  ["How do I care for indoor plants?", "Most indoor plants prefer bright, indirect light and watering only when the top inch of soil is dry. Avoid over-watering."],
  ["Do you ship plants?", "Yes! We pack them carefully. Standard delivery 2–5 days depending on location."],
  ["Can I return a damaged plant?", "Absolutely. Contact us within 24 hours of delivery with photos and we’ll replace or refund."],
  ["Do you offer bulk/office décor?", "Yes, we do corporate/bulk orders. Reach us from the contact form below."],
];
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Help Center • Vanniddhi</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;background:#f6faf6;margin:0}
    .container{max-width:1100px;margin:0 auto;padding:20px}
    header{background:#fff;border-bottom:1px solid #eee;position:sticky;top:0;z-index:10}
    header .container{display:flex;align-items:center;gap:14px}
    .logo{color:#2e7d32;font-weight:800;text-decoration:none;font-size:20px}
    h1{margin:22px 0 8px;color:#2e7d32}
    .search{margin:10px 0 24px;display:flex;gap:8px}
    .search input{flex:1;padding:12px 14px;border:1px solid #dfe6df;border-radius:10px}
    .faq{background:#fff;border:1px solid #eaeaea;border-radius:12px;overflow:hidden}
    .faq-item{border-top:1px solid #eee}
    .faq-item:first-child{border-top:none}
    .faq-q{padding:14px 16px;font-weight:600;cursor:pointer;display:flex;justify-content:space-between;align-items:center}
    .faq-a{padding:0 16px 16px;display:none;color:#444}
    .faq-item.open .faq-a{display:block}
    .faq-item.open .faq-q{background:#f9fff9}
    .grid{display:grid;grid-template-columns:1.1fr .9fr;gap:24px;margin-top:26px}
    @media(max-width:900px){.grid{grid-template-columns:1fr}}
    .card{background:#fff;border:1px solid #eaeaea;border-radius:12px;padding:18px}
    .card h2{margin:4px 0 12px;color:#2e7d32;font-size:20px}
    form .row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    @media(max-width:700px){form .row{grid-template-columns:1fr}}
    input,textarea{width:100%;padding:12px;border:1px solid #dfe6df;border-radius:10px}
    button{background:#2e7d32;color:#fff;border:none;padding:12px 16px;border-radius:10px;font-weight:700;cursor:pointer}
    .flash{margin:12px 0;padding:10px 12px;border-radius:8px;background:#e8f5e9;color:#1b5e20;border:1px solid #c8e6c9}
    .muted{color:#6a7670;font-size:14px}
  </style>
</head>
<body>
<header>
  <div class="container">
    <a class="logo" href="index.php"><i class="fa-solid fa-leaf"></i> Vanniddhi</a>
    <div style="margin-left:auto"><a href="index.php" style="text-decoration:none;color:#2e7d32;font-weight:600">← Back to Home</a></div>
  </div>
</header>

<main class="container">
  <h1>Help Center</h1>
  <p class="muted">Search FAQs or drop us a message—happy to help 🌿</p>

  <div class="search">
    <input id="faqSearch" type="search" placeholder="Search help topics (e.g. shipping, returns, care)">
  </div>

  <section class="faq" id="faqList" aria-label="FAQs">
    <?php foreach($faqs as $i => $row): ?>
    <article class="faq-item">
      <div class="faq-q" role="button" aria-expanded="false">
        <span><?= htmlspecialchars($row[0]) ?></span>
        <i class="fa-solid fa-chevron-down"></i>
      </div>
      <div class="faq-a">
        <p><?= htmlspecialchars($row[1]) ?></p>
      </div>
    </article>
    <?php endforeach; ?>
  </section>

  <div class="grid">
    <div class="card">
      <h2>Contact us</h2>
      <?php if($flash): ?><div class="flash"><?= htmlspecialchars($flash) ?></div><?php endif; ?>
      <form method="post">
        <div class="row">
          <div><input name="name" placeholder="Your name" required></div>
          <div><input type="email" name="email" placeholder="Your email" required></div>
        </div>
        <div class="row">
          <div><input name="subject" placeholder="Subject" required></div>
          <div><input name="order_id" placeholder="Order ID (optional)"></div>
        </div>
        <div style="margin:12px 0">
          <textarea name="message" rows="5" placeholder="How can we help?" required></textarea>
        </div>
        <button type="submit">Send Message</button>
      </form>
      <p class="muted" style="margin-top:10px"><i class="fa-solid fa-clock"></i> Usual reply within 24 hours.</p>
    </div>

    <div class="card">
      <h2>Quick links</h2>
      <ul style="line-height:2">
        <li><a href="track_order.php">Track an order</a></li>
        <li><a href="store_locator.php">Find a store</a></li>
        <li><a href="products.php">Browse products</a></li>
      </ul>
      <p class="muted">Email: hello@vanniddhi.com<br>Phone: +91 94250 46286</p>
    </div>
  </div>
</main>

<script>
  // FAQ toggle
  document.querySelectorAll('.faq-q').forEach(q => {
    q.addEventListener('click', () => q.parentElement.classList.toggle('open'));
  });

  // FAQ search
  const input = document.getElementById('faqSearch');
  const items = [...document.querySelectorAll('.faq-item')];
  input.addEventListener('input', () => {
    const q = input.value.toLowerCase();
    items.forEach(it => {
      const text = it.innerText.toLowerCase();
      it.style.display = text.includes(q) ? '' : 'none';
    });
  });
</script>
</body>
</html>
