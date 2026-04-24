/**
 * Market Intelligence Platform - Main JavaScript
 */
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
        });

        // Close sidebar on outside click (mobile)
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 1024 && 
                sidebar.classList.contains('open') && 
                !sidebar.contains(e.target) && 
                !sidebarToggle.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        });
    }

    // Auto-dismiss toasts after 5 seconds
    document.querySelectorAll('.toast').forEach(function(toast) {
        setTimeout(function() {
            toast.style.transition = 'opacity 0.3s ease';
            toast.style.opacity = '0';
            setTimeout(function() { toast.remove(); }, 300);
        }, 5000);
    });

    // Confirm delete actions
    document.querySelectorAll('[data-confirm]').forEach(function(el) {
        el.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    // Active nav item highlight based on URL
    const currentPath = window.location.pathname;
    document.querySelectorAll('.sidebar-nav .nav-item').forEach(function(item) {
        const href = item.getAttribute('href');
        if (href && currentPath.startsWith(href) && href !== '/') {
            item.classList.add('active');
        }
    });
});

/**
 * Format number for display
 */
function formatNumber(num) {
    if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
    if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
    return num.toString();
}

/**
 * Show a toast notification
 */
function showToast(message, type) {
    type = type || 'success';
    var toast = document.createElement('div');
    toast.className = 'toast toast-' + type;
    toast.innerHTML = '<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + '"></i>' +
                      '<span>' + message + '</span>' +
                      '<button class="toast-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>';
    
    var content = document.querySelector('.content');
    if (content) {
        content.insertBefore(toast, content.firstChild);
        setTimeout(function() {
            toast.style.transition = 'opacity 0.3s ease';
            toast.style.opacity = '0';
            setTimeout(function() { toast.remove(); }, 300);
        }, 5000);
    }
}

/**
 * Make AJAX request
 */
function apiRequest(url, method, data, callback) {
    var xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    
    var csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        xhr.setRequestHeader('X-CSRF-Token', csrfToken.getAttribute('content'));
    }

    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
            try {
                var response = JSON.parse(xhr.responseText);
                if (callback) callback(null, response);
            } catch (e) {
                if (callback) callback(null, xhr.responseText);
            }
        } else {
            if (callback) callback(xhr.statusText);
        }
    };

    xhr.onerror = function() {
        if (callback) callback('Network error');
    };

    if (data && method !== 'GET') {
        xhr.send(JSON.stringify(data));
    } else {
        xhr.send();
    }
}
