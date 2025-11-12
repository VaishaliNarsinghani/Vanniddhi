let cart = JSON.parse(localStorage.getItem("cart")) || [];

// ✅ Update cart count in navbar
function updateCartCount() {
  const cartCount = document.getElementById("cartCount");
  if (!cartCount) return; // only update if element exists
  let totalItems = cart.reduce((sum, item) => sum + item.qty, 0);
  cartCount.textContent = totalItems;
}

// ✅ Render Cart
function renderCart() {
  const cartItemsDiv = document.getElementById("cartItems");
  cartItemsDiv.innerHTML = "";

  let subtotal = 0;

  if (cart.length === 0) {
    cartItemsDiv.innerHTML = `
      <div class="empty-cart">
        <h3>Your Cart is Empty 🛒</h3>
        <a href="products.php" class="browse-btn">Browse Products</a>
      </div>
    `;
    document.getElementById("subtotal").innerText = "0.00";
    document.getElementById("total").innerText = "0.00";
    updateCartCount();
    return;
  }

  cart.forEach((item, index) => {
    subtotal += item.price * item.qty;

    const div = document.createElement("div");
    div.className = "cart-item";
    div.innerHTML = `
      <img src="${item.img}" alt="${item.name}">
      <div>
        <h4>${item.name}</h4>
        <p class="price">$${item.price}</p>
      </div>
      <div class="qty-controls">
        <button onclick="decreaseQty(${index})">-</button>
        <input type="number" id="qty-${index}" value="${item.qty}" readonly>
        <button onclick="increaseQty(${index})">+</button>
        <button onclick="removeItem(${index})" style="margin-left:10px;background:red;color:#fff;">x</button>
      </div>
    `;
    cartItemsDiv.appendChild(div);
  });

  document.getElementById("subtotal").innerText = subtotal.toFixed(2);
  document.getElementById("total").innerText = subtotal.toFixed(2);
  updateCartCount();
}

// ✅ Quantity functions
function increaseQty(i) {
  cart[i].qty++;
  localStorage.setItem("cart", JSON.stringify(cart));
  renderCart();
}

function decreaseQty(i) {
  if (cart[i].qty > 1) {
    cart[i].qty--;
  } else {
    cart.splice(i, 1); // remove if qty reaches 0
  }
  localStorage.setItem("cart", JSON.stringify(cart));
  renderCart();
}

function removeItem(i) {
  cart.splice(i, 1);
  localStorage.setItem("cart", JSON.stringify(cart));
  renderCart();
}

// ✅ Generate Invoice PDF
document.getElementById("checkoutForm").addEventListener("submit", function(e) {
  e.preventDefault();
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF();

  // Customer Info
  const name = document.getElementById("name").value;
  const email = document.getElementById("email").value;
  const phone = document.getElementById("phone").value;
  const address = document.getElementById("address").value;

  doc.setFontSize(18);
  doc.text("Invoice - Safal Sales", 20, 20);
  doc.setFontSize(12);
  doc.text(`Name: ${name}`, 20, 40);
  doc.text(`Email: ${email}`, 20, 48);
  doc.text(`Phone: ${phone}`, 20, 56);
  doc.text(`Address: ${address}`, 20, 64);

  doc.text("Order Details:", 20, 80);

  let y = 90;
  let total = 0;
  cart.forEach(item => {
    doc.text(`${item.name} (x${item.qty}) - $${(item.price*item.qty).toFixed(2)}`, 20, y);
    y += 10;
    total += item.price * item.qty;
  });

  doc.text(`Total: $${total.toFixed(2)}`, 20, y + 10);

  doc.save("invoice.pdf");
});

// ✅ Initialize
renderCart();
updateCartCount();

document.getElementById("downloadInvoice").addEventListener("click", function () {
    const name = document.getElementById("name").value;
    const email = document.getElementById("email").value;
    const phone = document.getElementById("phone").value;
    const address = document.getElementById("address").value;

    const cart = JSON.parse(localStorage.getItem("cart") || "[]");

    fetch("save_invoice.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ name, email, phone, address, cart })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Redirect to invoice.php with invoice id
            window.location.href = "invoice.php?id=" + data.invoice_id;
        } else {
            alert("Error generating invoice");
        }
    });
});
