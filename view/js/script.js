currentEventId = null;

document.addEventListener("DOMContentLoaded", function () {
    const path = window.location.pathname;

    //checkAuth();

    updateCartCount();

    if (path.includes("cart.html")) {
        fetchAndInject('/WebTech/cart', 'main-content');

    } else if (path.includes("events.html")) {
        fetchAndInject('/WebTech/events', 'events-container');
    }


    document.addEventListener("click", function (event) {
        if (event.target.classList.contains("add-to-cart")) {
            const eventId = event.target.dataset.eventId;
            fetch(`/WebTech/events/add-to-cart?event_id=${eventId}`, {
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
            fetch('/WebTech/checkout', {method: 'POST'})
                .then(handleResponse)
                .then(() => window.location.href = "/order-confirmation");
        }

        if (event.target.classList.contains("remove-from-cart")) {
            const eventId = event.target.dataset.eventId;
            fetch(`/WebTech/cart/remove?event_id=${eventId}`, {method: 'POST'})
                .then(handleResponse)
                .then(() => {
                    updateCartCount();
                    fetchAndInject('/WebTech/cart', 'main-content');
                });
        }

        if (event.target.classList.contains("view-details")) {
            const eventId = event.target.dataset.eventId;
            fetchAndInject(`/WebTech/events/view?event_id=${eventId}`, 'event-details-container');
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
    fetch('/WebTech/cart/count')
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




async function login() {
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const rememberMe = document.getElementById('remember_me').checked;

    const clientHashedPassword = await sha256(password);

    const response = await fetch('/WebTech/auth/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            username,
            password: clientHashedPassword,
            remember_me: rememberMe,
        })
    });

    const result = await response.json();
    if (result.success) {
        window.location.href = '/WebTech/view/html/index.html';
    } else {
        alert(result.message);
    }
}

async function sha256(message) {
    const msgBuffer = new TextEncoder().encode(message);

    const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);

    const hashArray = Array.from(new Uint8Array(hashBuffer));
    return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
}





document.getElementById('show-register').addEventListener('click', function() {
    document.getElementById('login-form').classList.remove('active');
    document.getElementById('register-form').classList.add('active');
});

document.getElementById('show-login').addEventListener('click', function() {
    document.getElementById('register-form').classList.remove('active');
    document.getElementById('login-form').classList.add('active');
});

document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const form = e.target;
    const errorElement = document.getElementById('login-error');
    const username = form.elements['username'].value.trim();
    const password = form.elements['password'].value;
    const rememberMe = form.elements['remember_me'].checked;

    if (!username || !password) {
        showError(errorElement, 'Fill every field');
        return;
    }

    try {
        const formData = new FormData();
        formData.append('username', username);
        formData.append('password', password);
        formData.append('remember_me', rememberMe ? '1' : '0');

        const loginResponse = await fetch('/WebTech/auth/login', {
            method: 'POST',
            body: formData
        });

        const loginResult = await loginResponse.json();

        if (loginResult.success) {
            try {
                await fetch('/WebTech/auth/sendLoginMail', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ username })
                });
            } catch (notificationError) {
                console.error('Error sending message:', notificationError);
            }

            window.location.href = '/WebTech/view/html/index.html';
        } else {
            showError(errorElement, loginResult.message || 'Failed to login');
        }
    } catch (error) {
        showError(errorElement, 'Error connecting to server');
        console.error('Login error:', error);
    }
});


document.getElementById('registerForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const username = document.getElementById('register-username').value.trim();
    const email = document.getElementById('register-email').value.trim();
    const password = document.getElementById('register-password').value;
    const confirmPassword = document.getElementById('register-confirm-password').value;

    if (!username || !email || !password) {
        showError('register-error', 'All of the fields are necessary');
        return;
    }

    if (password !== confirmPassword) {
        showError('register-error', 'Passwords do not match');
        return;
    }

    try {
        const formData = new FormData();
        formData.append('username', username);
        formData.append('email', email);
        formData.append('password', password);

        const response = await fetch('/WebTech/auth/register', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();

        if (result.success) {
            try {
                await fetch('/WebTech/auth/verifyEmail', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ username, email })
                });
            } catch (emailError) {
                console.error('Error sending verification mail:', emailError);
            }

            showError('register-error', 'Successful registration! Check your email for verification.', 'success');
            document.getElementById('registerForm').reset();
            document.getElementById('register-form').classList.remove('active');
            document.getElementById('login-form').classList.add('active');
        } else {
            showError('register-error', result.message || 'Server error');
        }
    } catch (error) {
        showError('register-error', 'Network error');
        console.error('Registration error:', error);
    }
});

function showError(elementId, message, type = 'error') {
    const errorElement = document.getElementById(elementId);
    errorElement.textContent = message;
    errorElement.style.display = 'block';
    errorElement.style.color = type === 'error' ? '#f44336' : '#4CAF50';

    setTimeout(() => {
        errorElement.style.display = 'none';
    }, 5000);
}

async function checkAuth() {
    try {
        const response = await fetch('/WebTech/auth/check');
        const result = await response.json();

        /*if (result.authenticated) {
            window.location.href = '/WebTech/view/html/index.html';
        }*/
    } catch (error) {
        console.error('Auth check error:', error);
    }
}

document.addEventListener('DOMContentLoaded', checkAuth);