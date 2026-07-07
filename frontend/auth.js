// ================================================
// Auth.js — Admin/Manager Authentication
// Uses PHP backend via apiFetch (from api-config.js)
// ================================================

/**
 * Check if user has the required role.
 * Redirects to login if not authenticated or wrong role.
 */
async function checkAuth(requiredRole) {
  const result = await apiFetch('/auth.php?action=me');

  // If backend unreachable, fall back to localStorage check
  if (result === null) {
    const currentRole = localStorage.getItem('role');
    if (!currentRole) {
      window.location.href = 'login.html';
      return;
    }
    if (requiredRole && currentRole !== requiredRole) {
      if (currentRole === 'admin') window.location.href = 'admin.html';
      else if (currentRole === 'manager') window.location.href = 'inventory.html';
      else window.location.href = 'login.html';
    }
    return;
  }

  // Backend responded
  if (!result.loggedIn || !result.user) {
    localStorage.removeItem('role');
    window.location.href = 'login.html';
    return;
  }

  const userRole = result.user.role;
  localStorage.setItem('role', userRole); // Keep in sync

  if (requiredRole && userRole !== requiredRole) {
    if (userRole === 'admin') window.location.href = 'admin.html';
    else if (userRole === 'manager') window.location.href = 'inventory.html';
    else window.location.href = 'login.html';
  }
}

/**
 * Handle admin/manager login form submit
 */
async function handleLogin(e) {
  e.preventDefault();
  const email = document.getElementById('username').value;
  const pass = document.getElementById('password').value;
  const errorMsg = document.getElementById('loginError');

  // Try backend login first
  const result = await apiFetch('/auth.php?action=admin-login', {
    method: 'POST',
    body: { email: email, password: pass }
  });

  if (result && !result.error && result.user) {
    localStorage.setItem('role', result.user.role);
    if (result.user.role === 'admin') {
      window.location.href = 'admin.html';
    } else if (result.user.role === 'manager') {
      window.location.href = 'inventory.html';
    }
    return;
  }

  // Fallback: try hardcoded demo accounts if backend is down
  if (result === null) {
    if (email === 'admin' && pass === 'admin123') {
      localStorage.setItem('role', 'admin');
      window.location.href = 'admin.html';
      return;
    } else if (email === 'manager' && pass === 'manager123') {
      localStorage.setItem('role', 'manager');
      window.location.href = 'inventory.html';
      return;
    }
  }

  // Show error
  errorMsg.style.display = 'block';
  if (result && result.message) {
    errorMsg.textContent = result.message;
  }
}

/**
 * Logout — clear session on both server and client
 */
async function logout() {
  await apiFetch('/auth.php?action=logout', { method: 'POST' });
  localStorage.removeItem('role');
  window.location.href = 'login.html';
}

// Attach login form handler
const loginForm = document.getElementById('loginForm');
if (loginForm) {
  loginForm.addEventListener('submit', handleLogin);
}

// Redirect logged-in users away from login page
if (window.location.pathname.endsWith('login.html')) {
  const currentRole = localStorage.getItem('role');
  if (currentRole === 'admin') window.location.href = 'admin.html';
  if (currentRole === 'manager') window.location.href = 'inventory.html';
}
