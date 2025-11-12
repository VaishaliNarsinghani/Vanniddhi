let cart = [];

function addToCart(name, price) {
  alert(`${name} added to cart!`);
  cart.push({name, price});
  localStorage.setItem("cart", JSON.stringify(cart));
}

function addToCart(name, price, img) {
  let cart = JSON.parse(localStorage.getItem("cart")) || [];

  // ✅ Ensure price is number
  price = parseFloat(price);

  // Check if already in cart
  let existing = cart.find(item => item.name === name);
  if (existing) {
    existing.qty++;
  } else {
    cart.push({ name, price, qty: 1, img });
  }

  localStorage.setItem("cart", JSON.stringify(cart));

  // Update badge immediately
  updateCartCount();
  alert(name + " added to cart!");
}
function toggleTheme() {
      document.body.classList.toggle("dark");
      const btn = document.querySelector(".toggle-btn");
      if (document.body.classList.contains("dark")) {
        btn.textContent = "☀️ Light";
      } else {
        btn.textContent = "🌙 Dark";
      }
    }

    