<style>
/* navbar.css */
/* Electrical Billing System Theme - Navbar - Clean, Professional, Minimalist */

:root {
    --primary: #0C6D9E;
    --primary-dark: #084d6f;
    --primary-light: #eef5f9;
    --secondary: #2c3e50;
    --white: #ffffff;
    --gray-light: #f8f9fa;
    --gray-border: #e0e4e8;
    --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
    --shadow-md: 0 2px 8px rgba(0, 0, 0, 0.08);
    --transition: all 0.2s ease;
}

.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 2rem;
    background: var(--white);
    box-shadow: var(--shadow-md);
    border-bottom: 1px solid var(--gray-border);
    position: sticky;
    top: 0;
    z-index: 100;
    backdrop-filter: blur(0px);
}

/* Brand Section */
.nav-brand {
    display: flex;
    align-items: center;
}

.brand-text {
    font-size: 1.25rem;
    font-weight: 600;
    letter-spacing: -0.2px;
    color: var(--secondary);
    background: transparent;
    padding: 0.4rem 0;
}

.brand-text::before {
    content: "";
    margin-right: 0;
}

/* The lightning emoji is already in HTML, so no need to duplicate */
.nav-brand .brand-text {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

/* Navigation Buttons Container */
.nav-buttons {
    display: flex;
    gap: 0.75rem;
    align-items: center;
}

.server-status {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 600;
    color: #ffffff;
    background: #6c757d;
    transition: background 0.2s ease;
}

.server-status.online {
    background: #28a745;
}

.server-status.offline {
    background: #dc3545;
}

/* Button Base Styling */
.btn-primary {
    padding: 0.5rem 1.1rem;
    font-size: 0.85rem;
    font-weight: 500;
    border-radius: 40px;
    cursor: pointer;
    transition: var(--transition);
    border: 1px solid transparent;
    background: transparent;
    font-family: inherit;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

/* Homepage Button (Outline Style) */
.homepage-btn {
    background: transparent;
    border: 1.5px solid var(--primary);
    color: var(--primary);
}

.homepage-btn:hover {
    background: var(--primary-light);
    border-color: var(--primary-dark);
    color: var(--primary-dark);
    transform: translateY(-1px);
}

/* Logout Button (Primary Style) */
.logout-btn {
    background: var(--primary);
    border: 1.5px solid var(--primary);
    color: white;
}

.logout-btn:hover {
    background: var(--primary-dark);
    border-color: var(--primary-dark);
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(12, 109, 158, 0.2);
}

/* Active / Focus States */
.btn-primary:active {
    transform: translateY(0);
}

.btn-primary:focus-visible {
    outline: 2px solid var(--primary);
    outline-offset: 2px;
}

/* Navigation Link Wrapper */
.nav-link {
    text-decoration: none;
    display: inline-flex;
}

/* Responsive Design */
@media (max-width: 640px) {
    .navbar {
        padding: 0.6rem 1.2rem;
    }

    .brand-text {
        font-size: 1rem;
    }

    .btn-primary {
        padding: 0.4rem 0.9rem;
        font-size: 0.75rem;
    }

    .nav-buttons {
        gap: 0.5rem;
    }
}

@media (max-width: 480px) {
    .navbar {
        padding: 0.5rem 1rem;
    }

    .brand-text {
        font-size: 0.9rem;
    }

    .btn-primary {
        padding: 0.35rem 0.75rem;
        font-size: 0.7rem;
    }
}

/* Optional: subtle border bottom indicator for current page */
.navbar:after {
    display: none;
}
</style>
<nav class="navbar">
    <div class="nav-brand">
        <span class="brand-text">⚡ NEA Billing System</span>
        <span id="server-status" class="server-status">Checking API...</span>
    </div>

    <?php if ($currentPage === 'home.php') { ?>
        <div class="nav-buttons">
            <button class="btn-primary logout-btn" onclick="submit">Logout</button>
        </div>
    <?php } elseif ($currentPage === 'login.php' || $currentPage === 'register.php') { ?>
        <!-- No buttons shown on login/register pages -->
    <?php } elseif ($currentPage === 'index.php') { ?>
        <div class="nav-buttons">
            <button class="btn-primary logout-btn" onclick="submit">Logout</button>
        </div>
    <?php } else { ?>
        <div class="nav-buttons">
            
            <a href="../login.php" class="nav-link">
                <button class="btn-primary logout-btn">Logout</button>
            </a>
        </div>
    <?php } ?>
</nav>
<script>
(function() {
    const statusEl = document.getElementById('server-status');
    if (!statusEl) return;

    fetch('http://localhost:3000/health', { mode: 'cors' })
        .then(response => response.json())
        .then(data => {
            if (data && data.success) {
                statusEl.textContent = 'API Online';
                statusEl.classList.add('online');
                statusEl.classList.remove('offline');
            } else {
                throw new Error(data?.message || 'Offline');
            }
        })
        .catch(() => {
            statusEl.textContent = 'API Offline';
            statusEl.classList.add('offline');
            statusEl.classList.remove('online');
        });
})();
</script>