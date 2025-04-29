currentEventId = null;

document.addEventListener("DOMContentLoaded", function () {
    const path = window.location.pathname;

    updateCartCount();

    if (path.includes("cart.html")) {
        fetchAndInject('/cart', 'main-content');

    } else if (path.includes("events.html")) {
        fetchAndInject('/events', 'events-container');
    }


    document.addEventListener("click", function (event) {
        if (event.target.classList.contains("add-to-cart")) {
            const eventId = event.target.dataset.eventId;
            fetch(`/events/add-to-cart?event_id=${eventId}`, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateCartCount();
                        alert('Added to cart!');
                    }
                });
        }

        if (event.target.id === "checkout-button") {
            fetch('/checkout', {method: 'POST'})
                .then(handleResponse)
                .then(() => window.location.href = "/order-confirmation");
        }

        if (event.target.classList.contains("remove-from-cart")) {
            const eventId = event.target.dataset.eventId;
            fetch(`/cart/remove?event_id=${eventId}`, {method: 'POST'})
                .then(handleResponse)
                .then(() => {
                    updateCartCount();
                    fetchAndInject('/cart', 'main-content');
                });
        }

        if (event.target.classList.contains("view-details")) {
            const eventId = event.target.dataset.eventId;
            fetchAndInject(`/events/view?event_id=${eventId}`, 'event-details-container');
            const element = document.getElementById('event-details-container');
            element.style.display = 'block';
        }
    });

});

function fetchAndInject(contentUrl, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;

    fetch(contentUrl)
        .then(response => response.text())
        .then(html => {
            container.innerHTML = html;
        })
        .catch(error => console.error(`Error loading ${contentUrl}:`, error));
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
