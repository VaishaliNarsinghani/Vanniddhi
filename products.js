// Sample product data (later from PHP+MySQL)
const products = [
  {id:1, name:"Sparkle Fountain", price:12.99, category:"fountains", img:"images/fireworks1.jpg"},
  {id:2, name:"Thunder Boom", price:8.49, category:"sound", img:"images/fireworks2.jpg"},
  {id:3, name:"Color Burst Rockets", price:19.99, category:"rockets", img:"images/fireworks3.jpg"},
  {id:4, name:"Sparklers Pack", price:5.99, category:"sparklers", img:"images/fireworks4.jpg"},
  {id:5, name:"Ground Spinner", price:7.49, category:"ground", img:"images/fireworks5.jpg"},
  {id:6, name:"Celebration Pack", price:34.99, category:"assortment", img:"images/fireworks6.jpg"},
  {id:7, name:"Rainbow Wheel", price:11.99, category:"wheels", img:"images/fireworks7.jpg"},
  {id:8, name:"Sky Lantern", price:6.99, category:"lanterns", img:"images/fireworks8.jpg"}
];

let cart = JSON.parse(localStorage.getItem("cart")) || [];

// Render products
function renderProducts(list) {
  const container = document.getElementById("productList");
  container.innerHTML = "";
  list.forEach(p => {
    const card = document.createElement("div");
    card.className = "product-card";
    card.innerHTML = `
      <img src="${p.img}" alt="${p.name}">
      <h3>${p.name}</h3>
      <p class="price">$${p.price}</p>
      <div class="qty-controls">
        <button onclick="decreaseQty(${p.id})">-</button>
        <input type="number" id="qty-${p.id}" value="1" min="1">
        <button onclick="increaseQty(${p.id})">+</button>
      </div>
      <button class="add" onclick="addToCart(${p.id})">Add to Cart</button>
    `;
    container.appendChild(card);
  });
}
renderProducts(products);

// Search filter
document.getElementById("search").addEventListener("input", (e) => {
  const val = e.target.value.toLowerCase();
  const filtered = products.filter(p => p.name.toLowerCase().includes(val));
  renderProducts(filtered);
});

// Category filter
document.getElementById("categoryFilter").addEventListener("change", (e) => {
  const val = e.target.value;
  if(val === "all") renderProducts(products);
  else renderProducts(products.filter(p => p.category === val));
});

// Quantity functions
function increaseQty(id) {
  const qtyInput = document.getElementById(`qty-${id}`);
  qtyInput.value = parseInt(qtyInput.value) + 1;
}
function decreaseQty(id) {
  const qtyInput = document.getElementById(`qty-${id}`);
  if(qtyInput.value > 1) qtyInput.value = parseInt(qtyInput.value) - 1;
}

// Add to cart
function addToCart(id) {
  const product = products.find(p => p.id === id);
  const qty = parseInt(document.getElementById(`qty-${id}`).value);
  cart.push({...product, qty});
  localStorage.setItem("cart", JSON.stringify(cart));
  alert(`${product.name} (x${qty}) added to cart!`);
}
