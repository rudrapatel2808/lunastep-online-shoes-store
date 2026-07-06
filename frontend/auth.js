// Simulated Role-Based Access Control (RBAC)
function checkAuth(requiredRole) {
  const currentRole = localStorage.getItem('role');
  
  if (!currentRole) {
    window.location.href = 'login.html';
    return;
  }
  
  if (requiredRole && currentRole !== requiredRole) {
    // If they have a role but it's the wrong one, redirect them to their respective dashboard
    if (currentRole === 'admin') window.location.href = 'admin.html';
    else if (currentRole === 'manager') window.location.href = 'inventory.html';
    else window.location.href = 'login.html';
  }
}

function handleLogin(e) {
  e.preventDefault();
  const user = document.getElementById('username').value;
  const pass = document.getElementById('password').value;
  const errorMsg = document.getElementById('loginError');

  if (user === 'admin' && pass === 'admin123') {
    localStorage.setItem('role', 'admin');
    window.location.href = 'admin.html';
  } else if (user === 'manager' && pass === 'manager123') {
    localStorage.setItem('role', 'manager');
    window.location.href = 'inventory.html';
  } else {
    errorMsg.style.display = 'block';
  }
}

function logout() {
  localStorage.removeItem('role');
  window.location.href = 'login.html';
}

const loginForm = document.getElementById('loginForm');
if(loginForm) {
  loginForm.addEventListener('submit', handleLogin);
}

// Ensure logged-in users who visit login.html are redirected to their dashboard
if (window.location.pathname.endsWith('login.html')) {
  const currentRole = localStorage.getItem('role');
  if (currentRole === 'admin') window.location.href = 'admin.html';
  if (currentRole === 'manager') window.location.href = 'inventory.html';
}
