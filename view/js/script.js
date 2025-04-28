
let currentEventId = null;

document.addEventListener("DOMContentLoaded", function() {

    fetchAndInject('/events', 'events-container');

    updateCartCount();

    document.addEventListener("click", function(event) {
        // Event clicks
        if (event.target.closest(".event-card")) {
            const eventId = event.target.closest(".event-card").dataset.eventId;
            fetchAndInject(`/events/view?id=${eventId}`, 'event-details-container');
        }

        if (event.target.classList.contains("add-to-cart")) {
            const eventId = event.target.dataset.eventId;
            fetch(`/events/add-to-cart?event_id=${eventId}`, { method: 'POST' })
                .then(handleResponse)
                .then(() => updateCartCount());
        }

        if (event.target.classList.contains("remove-from-cart")) {
            const eventId = event.target.dataset.eventId;
            fetch(`/cart/remove?event_id=${eventId}`, { method: 'POST' })
                .then(handleResponse)
                .then(() => {
                    updateCartCount();
                    if (window.location.pathname === '/cart') {
                        fetchAndInject('/cart', 'main-content');
                    }
                });
        }

        if (event.target.closest('a[href="/cart"]')) {
            event.preventDefault();
            fetchAndInject('/cart', 'main-content');
        }

        // Checkout button
        if (event.target.id === "checkout-button") {
            fetch('/checkout', { method: 'POST' })
                .then(handleResponse)
                .then(() => window.location.href = "/order-confirmation");
        }
    });
});

function fetchAndInject(url, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;

    fetch(url)
        .then(response => response.text())
        .then(html => container.innerHTML = html)
        .catch(error => console.error(`Error loading ${url}:`, error));
}

function updateCartCount() {
    fetch('/cart/count')
        .then(response => response.json())
        .then(data => {
            document.querySelectorAll('.cart-count').forEach(el => {
                el.textContent = data.count;
            });
        })
        .catch(error => console.error("Error updating cart count:", error));
}
function handleResponse(response) {
    if (!response.ok) throw new Error('Request failed');
    return response.json();
}

document.addEventListener("DOMContentLoaded", function() {
    fetchAndInjectEvents();

    updateCartCount();


    document.addEventListener("click", function(event) {
        if (event.target.closest(".event-card")) {
            const eventId = event.target.closest(".event-card").dataset.eventId;
            fetchAndInject(`/events/view?id=${eventId}`, 'event-details-container');
        }

        if (event.target.classList.contains("add-to-cart")) {
            const eventId = event.target.dataset.eventId;
            fetch(`/events/add-to-cart?event_id=${eventId}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateCartCount();
                        alert('Added to cart!');
                    }
                });
        }
    });
});

function fetchAndInjectEvents() {
    const container = document.getElementById("events-container");
    if (!container) return;

    fetch('/events')
        .then(response => response.text())
        .then(html => {
            container.innerHTML = html;
            document.querySelectorAll(".event-card").forEach(card => {
                card.addEventListener("click", function() {
                    const eventId = this.dataset.eventId;
                    fetchAndInject(`/events/view?id=${eventId}`, 'event-details-container');
                });
            });
        })
        .catch(error => console.error("Error loading events:", error));
}

