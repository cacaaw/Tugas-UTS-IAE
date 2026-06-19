const API_USER = 'http://127.0.0.1:8001/api';
const API_FOOD = 'http://127.0.0.1:8002/api';
const API_ORDER = 'http://127.0.0.1:8003/api';

// State
let currentUser = JSON.parse(localStorage.getItem('opsFoodUser')) || null;
let foods = [];
let currentOrderFood = null;
let currentTheme = localStorage.getItem('opsFoodTheme') || 'dark';

// Theme Logic
const applyTheme = (theme) => {
    if (theme === 'light') {
        document.body.classList.add('light-mode');
        document.querySelectorAll('.theme-icon').forEach(el => el.textContent = '☀️');
    } else {
        document.body.classList.remove('light-mode');
        document.querySelectorAll('.theme-icon').forEach(el => el.textContent = '🌙');
    }
    localStorage.setItem('opsFoodTheme', theme);
};

const toggleTheme = () => {
    currentTheme = currentTheme === 'dark' ? 'light' : 'dark';
    applyTheme(currentTheme);
};

// Initialize Theme
applyTheme(currentTheme);

// DOM Elements
const authView = document.getElementById('auth-view');
const mainView = document.getElementById('main-view');
const menuSection = document.getElementById('menu-section');
const historySection = document.getElementById('history-section');
const foodGrid = document.getElementById('food-grid');
const historyTable = document.getElementById('history-table-body');
const userGreeting = document.getElementById('user-greeting');
const loginForm = document.getElementById('login-form');
const loginError = document.getElementById('login-error');
const loginBtn = document.getElementById('login-btn');
const toastEl = document.getElementById('toast');
const themeToggleBtn = document.getElementById('theme-toggle');
const themeToggleAuthBtn = document.getElementById('theme-toggle-auth');

// Theme Event Listeners
themeToggleBtn.addEventListener('click', toggleTheme);
themeToggleAuthBtn.addEventListener('click', toggleTheme);

const registerForm = document.getElementById('register-form');
const registerError = document.getElementById('register-error');
const registerBtn = document.getElementById('register-btn');
const showRegisterBtn = document.getElementById('show-register');
const showLoginBtn = document.getElementById('show-login');
const authSubtitle = document.getElementById('auth-subtitle');

// Modal Elements
const orderModal = document.getElementById('order-modal');
const orderForm = document.getElementById('order-form');
const orderImg = document.getElementById('order-img');
const orderFoodName = document.getElementById('order-food-name');
const orderFoodPrice = document.getElementById('order-food-price');
const orderQty = document.getElementById('order-qty');
const orderTotalPrice = document.getElementById('order-total-price');

// Utils
const formatMoney = (amount) => `Rp ${Number(amount).toLocaleString('id-ID')}`;

const showToast = (message, isError = false) => {
    toastEl.textContent = message;
    toastEl.className = `toast show ${isError ? 'error' : ''}`;
    setTimeout(() => toastEl.classList.remove('show'), 3000);
};

// Navigation & Auth Logic
const checkAuth = () => {
    if (currentUser) {
        authView.classList.remove('active');
        mainView.classList.add('active');
        userGreeting.textContent = `Hi, ${currentUser.name}`;
        loadFoods();
    } else {
        authView.classList.add('active');
        mainView.classList.remove('active');
    }
};

const switchTab = (tab) => {
    document.querySelectorAll('.btn-nav').forEach(btn => btn.classList.remove('active'));
    document.getElementById(`btn-${tab}`).classList.add('active');
    
    if (tab === 'menu') {
        menuSection.classList.add('active');
        historySection.classList.remove('active');
        loadFoods();
    } else if (tab === 'history') {
        historySection.classList.add('active');
        menuSection.classList.remove('active');
        loadHistory();
    }
};

// API Interactions
loginForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    loginError.textContent = '';
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    loginBtn.disabled = true;
    loginBtn.textContent = 'Signing in...';

    try {
        const response = await fetch(`${API_USER}/login`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ email, password })
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Login failed');
        }

        currentUser = data;
        localStorage.setItem('opsFoodUser', JSON.stringify(currentUser));
        showToast('Login successful!');
        checkAuth();
    } catch (err) {
        loginError.textContent = err.message;
    } finally {
        loginBtn.disabled = false;
        loginBtn.textContent = 'Sign In';
    }
});

showRegisterBtn.addEventListener('click', (e) => {
    e.preventDefault();
    loginForm.style.display = 'none';
    registerForm.style.display = 'block';
    authSubtitle.textContent = 'Create an account to order.';
});

showLoginBtn.addEventListener('click', (e) => {
    e.preventDefault();
    registerForm.style.display = 'none';
    loginForm.style.display = 'block';
    authSubtitle.textContent = 'Enter your details to order.';
});

registerForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    registerError.textContent = '';
    
    const name = document.getElementById('reg-name').value;
    const email = document.getElementById('reg-email').value;
    const password = document.getElementById('reg-password').value;
    
    registerBtn.disabled = true;
    registerBtn.textContent = 'Registering...';

    try {
        const response = await fetch(`${API_USER}/users`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ name, email, password })
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Registration failed');
        }

        currentUser = data;
        localStorage.setItem('opsFoodUser', JSON.stringify(currentUser));
        showToast('Registration successful!');
        checkAuth();
    } catch (err) {
        registerError.textContent = err.message;
    } finally {
        registerBtn.disabled = false;
        registerBtn.textContent = 'Register';
    }
});

document.getElementById('btn-logout').addEventListener('click', () => {
    currentUser = null;
    localStorage.removeItem('opsFoodUser');
    checkAuth();
});

document.getElementById('btn-menu').addEventListener('click', () => switchTab('menu'));
document.getElementById('btn-history').addEventListener('click', () => switchTab('history'));

// Load Foods
const loadFoods = async () => {
    foodGrid.innerHTML = '<p>Loading menu...</p>';
    try {
        const response = await fetch(`${API_FOOD}/foods`);
        foods = await response.json();
        
        if (foods.length === 0) {
            foodGrid.innerHTML = '<p>No food available at the moment.</p>';
            return;
        }

        foodGrid.innerHTML = foods.map(food => `
            <div class="food-card">
                <img class="food-img" src="${food.image || 'https://via.placeholder.com/300'}" alt="${food.name}">
                <div class="food-info">
                    <h3>${food.name}</h3>
                    <p>${food.description || 'Delicious food.'}</p>
                    <div class="food-meta">
                        <span class="food-price">${formatMoney(food.price)}</span>
                        <span class="food-qty">${food.qty > 0 ? `Stock: ${food.qty}` : '<span style="color:var(--danger)">Out of stock</span>'}</span>
                    </div>
                    <button class="btn btn-primary" onclick="openOrderModal(${food.id})" ${food.qty <= 0 ? 'disabled' : ''}>Order Now</button>
                </div>
            </div>
        `).join('');
    } catch (err) {
        foodGrid.innerHTML = '<p class="error-msg">Failed to load foods.</p>';
    }
};

// Order Logic
window.openOrderModal = (foodId) => {
    currentOrderFood = foods.find(f => f.id === foodId);
    if (!currentOrderFood) return;

    orderImg.src = currentOrderFood.image || 'https://via.placeholder.com/300';
    orderFoodName.textContent = currentOrderFood.name;
    orderFoodPrice.textContent = formatMoney(currentOrderFood.price);
    orderQty.value = 1;
    orderQty.max = currentOrderFood.qty;
    updateTotalPrice();
    
    orderModal.classList.add('active');
};

const updateTotalPrice = () => {
    if (currentOrderFood) {
        const total = currentOrderFood.price * parseInt(orderQty.value || 0);
        orderTotalPrice.textContent = formatMoney(total);
    }
};

orderQty.addEventListener('input', updateTotalPrice);

document.getElementById('close-modal').addEventListener('click', () => {
    orderModal.classList.remove('active');
});

orderForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const submitBtn = document.getElementById('submit-order-btn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Processing...';

    const payload = {
        user_id: currentUser.id,
        food_id: currentOrderFood.id,
        quantity: parseInt(orderQty.value)
    };

    try {
        const response = await fetch(`${API_ORDER}/orders`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        if (!response.ok) {
            const data = await response.json();
            throw new Error(data.message || 'Failed to place order');
        }

        showToast('Order placed successfully!');
        orderModal.classList.remove('active');
        loadFoods(); // refresh stock
        switchTab('history');
    } catch (err) {
        showToast(err.message, true);
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Confirm Order';
    }
});

// Load History
const loadHistory = async () => {
    historyTable.innerHTML = '<tr><td colspan="6">Loading history...</td></tr>';
    try {
        const response = await fetch(`${API_USER}/users/${currentUser.id}/orders`);
        if (!response.ok) throw new Error('Failed to load history');
        
        const data = await response.json();
        const orders = data.orders || [];

        if (orders.length === 0) {
            historyTable.innerHTML = '<tr><td colspan="6">No orders found.</td></tr>';
            return;
        }

        historyTable.innerHTML = orders.map(order => `
            <tr>
                <td>#${order.id}</td>
                <td>${order.food_name}</td>
                <td>${order.quantity}</td>
                <td>${formatMoney(order.total_price)}</td>
                <td><span class="status-badge status-${order.status}">${order.status}</span></td>
                <td>${new Date(order.created_at).toLocaleDateString()}</td>
            </tr>
        `).join('');
    } catch (err) {
        historyTable.innerHTML = `<tr><td colspan="6" class="error-msg">${err.message}</td></tr>`;
    }
};

// Init
checkAuth();
