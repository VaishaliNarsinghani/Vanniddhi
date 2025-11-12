<?php
// store_locator.php
// Add your stores here (name, lat, lng, address, hours, phone)
$stores = [
  [
    "name" => "Vanniddhi — Main Nursery",
    "lat" => 22.7196,   // sample: Indore region (update!)
    "lng" => 75.8577,
    "address" => "Shop No. 04, Temp. Cracker Market, Chhota Bangarda, Indore",
    "hours" => "Mon–Sat: 9AM–6PM",
    "phone" => "+91 94250 46286"
  ],
  [
    "name" => "Vanniddhi — City Center",
    "lat" => 22.724, "lng" => 75.89,
    "address" => "City Center, Indore (Demo address)",
    "hours" => "Daily: 10AM–8PM",
    "phone" => "+91 99999 99999"
  ],
];
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Store Locator • Vanniddhi</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""
  />
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;background:#f6faf6;margin:0}
    .container{max-width:1100px;margin:0 auto;padding:20px}
    header{background:#fff;border-bottom:1px solid #eee;position:sticky;top:0;z-index:10}
    header .container{display:flex;align-items:center;gap:14px}
    .logo{color:#2e7d32;font-weight:800;text-decoration:none;font-size:20px}
    h1{margin:22px 0 8px;color:#2e7d32}
    .grid{display:grid;grid-template-columns:360px 1fr;gap:18px}
    @media(max-width:980px){.grid{grid-template-columns:1fr}}
    #map{width:100%;height:520px;background:#e8efe8;border-radius:12px;border:1px solid #e3e9e3}
    .card{background:#fff;border:1px solid #eaeaea;border-radius:12px;padding:16px;height:520px;overflow:auto}
    .store{border-bottom:1px dashed #e5eee5;padding:12px 6px}
    .store:last-child{border-bottom:none}
    .store h3{margin:0 0 6px}
    .store button{margin-top:8px;background:#2e7d32;color:#fff;border:none;padding:8px 12px;border-radius:10px;cursor:pointer}
    .muted{color:#6a7670;font-size:14px}
    .search{display:flex;gap:8px;margin:10px 0 12px}
    .search input{flex:1;padding:10px 12px;border:1px solid #dfe6df;border-radius:10px}
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
<header>
  <div class="container">
    <a class="logo" href="index.php"><i class="fa-solid fa-leaf"></i> Vanniddhi</a>
    <div style="margin-left:auto"><a href="index.php" style="text-decoration:none;color:#2e7d32;font-weight:600">← Back to Home</a></div>
  </div>
</header>

<main class="container">
  <h1>Store Locator</h1>
  <p class="muted">Find your nearest Vanniddhi location.</p>

  <div class="grid">
    <aside class="card">
      <div class="search">
        <input id="q" type="search" placeholder="Search by name or address">
      </div>
      <div id="list">
        <?php foreach($stores as $i => $s): ?>
          <div class="store" data-i="<?= $i ?>">
            <h3><?= htmlspecialchars($s['name']) ?></h3>
            <div class="muted"><?= htmlspecialchars($s['address']) ?></div>
            <div class="muted"><?= htmlspecialchars($s['hours']) ?> • <?= htmlspecialchars($s['phone']) ?></div>
            <button onclick="focusMarker(<?= $i ?>)">View on Map</button>
          </div>
        <?php endforeach; ?>
      </div>
    </aside>

    <section id="map"></section>
  </div>
</main>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
  integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
  const STORES = <?= json_encode($stores) ?>;
  const map = L.map('map');
  // Fit to all markers
  const bounds = [];
  const markers = [];

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution:'&copy; OpenStreetMap'
  }).addTo(map);

  STORES.forEach((s,i)=>{
    const m = L.marker([s.lat, s.lng]).addTo(map)
      .bindPopup(`<strong>${escapeHtml(s.name)}</strong><br>${escapeHtml(s.address)}<br><small>${escapeHtml(s.hours)} • ${escapeHtml(s.phone)}</small>`);
    markers.push(m);
    bounds.push([s.lat, s.lng]);
  });

  if (bounds.length) map.fitBounds(bounds, {padding:[30,30]}); else map.setView([20.59,78.96], 5);

  window.focusMarker = (i) => {
    const s = STORES[i], m = markers[i];
    if (!s || !m) return;
    map.setView([s.lat, s.lng], 15);
    m.openPopup();
  };

  // Search filter
  const q = document.getElementById('q');
  q.addEventListener('input', () => {
    const term = q.value.toLowerCase();
    document.querySelectorAll('.store').forEach(div => {
      const idx = +div.dataset.i;
      const s = STORES[idx];
      const hay = (s.name + ' ' + s.address).toLowerCase();
      div.style.display = hay.includes(term) ? '' : 'none';
    });
  });

  function escapeHtml(s){ return (s+'').replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])) }
</script>
</body>
</html>
