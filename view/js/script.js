// Mock data for events
const events = [
    {
        id: 1,
        name: "Concert: Rock Night",
        date: "2023-12-15",
        price: 50
    },
    {
        id: 2,
        name: "Theater: Hamlet",
        date: "2023-11-20",
        price: 30
    },
    {
        id: 3,
        name: "Comedy Show: Laugh Out Loud",
        date: "2023-10-25",
        price: 20
    }
];

// Cart array to store grouped items
let cart = [];

// Function to display events on the Events page
function displayEvents() {
    const eventList = document.querySelector('.event-list');
    if (eventList) {
        eventList.innerHTML = ''; // Clear existing content
        events.forEach(event => {
            const eventDiv = document.createElement('div');
            eventDiv.className = 'event';
            eventDiv.innerHTML = `
                <h3>${event.name}</h3>
                <p>Date: ${event.date}</p>
                <p>Price: $${event.price}</p>
                <button onclick="addToCart(${event.id})">Add to Cart</button>
            `;
            eventList.appendChild(eventDiv);
        });
    }
}

// Function to add an event to the cart
function addToCart(eventId) {
    const event = events.find(e => e.id === eventId);
    if (event) {
        // Check if the event is already in the cart
        const cartItem = cart.find(item => item.id === eventId);
        if (cartItem) {
            // If it exists, increment the quantity
            cartItem.quantity += 1;
        } else {
            // If it doesn't exist, add it to the cart with a quantity of 1
            cart.push({ ...event, quantity: 1 });
        }
        localStorage.setItem('cart', JSON.stringify(cart)); // Save cart to localStorage
        updateCart();
        alert(`${event.name} added to cart!`);
    }
}

// Function to update the cart display
function updateCart() {
    const cartItems = document.querySelector('.cart-items');
    const cartTotal = document.querySelector('.cart-total h3');
    if (cartItems && cartTotal) {
        cartItems.innerHTML = ''; // Clear existing content
        let total = 0;
        cart.forEach((item, index) => {
            const cartItemDiv = document.createElement('div');
            cartItemDiv.className = 'cart-item';
            cartItemDiv.innerHTML = `
                <h3>${item.name} (${item.quantity}x)</h3>
                <p>Date: ${item.date}</p>
                <p>Price: $${item.price} each</p>
                <p>Total: $${item.price * item.quantity}</p>
                <button onclick="removeFromCart(${index})">Remove</button>
            `;
            cartItems.appendChild(cartItemDiv);
            total += item.price * item.quantity;
        });
        cartTotal.textContent = `Total: $${total}`;
    }
}

// Function to remove an item from the cart
function removeFromCart(index) {
    cart.splice(index, 1);
    localStorage.setItem('cart', JSON.stringify(cart)); // Update localStorage
    updateCart();
}

// Function to handle checkout
function checkout() {
    if (cart.length === 0) {
        alert('Your cart is empty!');
        return;
    }
    alert('Thank you for your purchase!');
    cart = []; // Clear the cart
    localStorage.removeItem('cart'); // Remove cart data from localStorage
    updateCart(); // Update the cart display
}

// Initialize the site
function init() {
    cart = JSON.parse(localStorage.getItem('cart')) || []; // Load cart from localStorage
    displayEvents();
    updateCart();
}

// Run the init function when the page loads
window.onload = init;