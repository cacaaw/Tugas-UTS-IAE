/**
 * Dashboard JavaScript
 * Mengelola interaksi UI untuk dashboard order service
 */

// ===== THEME TOGGLE =====
const themeToggle = document.getElementById('themeToggle');
const body = document.body;

// Load saved theme
const savedTheme = localStorage.getItem('theme') || 'light';
if (savedTheme === 'dark') {
    body.setAttribute('data-theme', 'dark');
    themeToggle.textContent = 'Mode Terang';
}

// Toggle theme
themeToggle.addEventListener('click', () => {
    const currentTheme = body.getAttribute('data-theme');
    if (currentTheme === 'dark') {
        body.removeAttribute('data-theme');
        themeToggle.textContent = 'Mode Gelap';
        localStorage.setItem('theme', 'light');
    } else {
        body.setAttribute('data-theme', 'dark');
        themeToggle.textContent = 'Mode Terang';
        localStorage.setItem('theme', 'dark');
    }
});

// ===== TAB NAVIGATION =====
const menuButtons = document.querySelectorAll('#menuTabs button');
const panelViews = document.querySelectorAll('.panel-view');

menuButtons.forEach(button => {
    button.addEventListener('click', () => {
        const targetId = button.getAttribute('data-target');
        
        // Remove active class from all buttons and panels
        menuButtons.forEach(btn => btn.classList.remove('active'));
        panelViews.forEach(panel => panel.classList.remove('active'));
        
        // Add active class to clicked button and target panel
        button.classList.add('active');
        const targetPanel = document.getElementById(targetId);
        if (targetPanel) {
            targetPanel.classList.add('active');
        }
    });
});

// ===== TOAST AUTO-HIDE =====
const toastStack = document.getElementById('toastStack');
if (toastStack) {
    setTimeout(() => {
        toastStack.style.opacity = '0';
        toastStack.style.transition = 'opacity 0.3s ease';
        setTimeout(() => {
            toastStack.remove();
        }, 300);
    }, 4000);
}

// ===== LOADING OVERLAY =====
const loadingOverlay = document.createElement('div');
loadingOverlay.className = 'loading-overlay';
loadingOverlay.innerHTML = `
    <div class="skeleton-panel">
        <div class="skeleton-pill"></div>
        <div class="skeleton-row"></div>
        <div class="skeleton-row short"></div>
        <div class="skeleton-row"></div>
        <div class="skeleton-row short"></div>
    </div>
`;
document.body.appendChild(loadingOverlay);

// Show loading on navigation
document.querySelectorAll('[data-loading-trigger="true"]').forEach(element => {
    element.addEventListener('click', (e) => {
        loadingOverlay.classList.add('active');
    });
});

// Show loading on form submit
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', (e) => {
        // Don't show loading for forms with data-no-loading attribute
        if (!form.hasAttribute('data-no-loading')) {
            loadingOverlay.classList.add('active');
        }
    });
});

// ===== FORM VALIDATION =====
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', (e) => {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.style.borderColor = 'var(--danger)';
                
                // Reset border color after 2 seconds
                setTimeout(() => {
                    field.style.borderColor = '';
                }, 2000);
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            loadingOverlay.classList.remove('active');
        }
    });
});

// ===== UTILITY FUNCTIONS =====

/**
 * Format number as Indonesian Rupiah
 */
function formatRupiah(number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(number);
}

/**
 * Show toast notification
 */
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    
    let stack = document.getElementById('toastStack');
    if (!stack) {
        stack = document.createElement('div');
        stack.id = 'toastStack';
        stack.className = 'toast-stack';
        document.body.appendChild(stack);
    }
    
    stack.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// ===== KEYBOARD SHORTCUTS =====
document.addEventListener('keydown', (e) => {
    // Ctrl/Cmd + K: Focus search
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.querySelector('input[name="q"]');
        if (searchInput) {
            searchInput.focus();
            searchInput.select();
        }
    }
    
    // Ctrl/Cmd + D: Toggle dark mode
    if ((e.ctrlKey || e.metaKey) && e.key === 'd') {
        e.preventDefault();
        themeToggle.click();
    }
});

console.log('✅ Dashboard initialized successfully');
