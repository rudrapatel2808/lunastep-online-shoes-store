// ================================================
// API Configuration — Frontend
// Change API_BASE to match your XAMPP/WAMP setup
// ================================================

const API_BASE = 'http://localhost/lunastep-online-shoes-store/backend/api';

/**
 * Helper to make API calls with consistent error handling.
 * Falls back gracefully if backend is unavailable.
 *
 * @param {string} endpoint - e.g. '/products.php' or '/auth.php?action=login'
 * @param {object} options - fetch options (method, body, etc.)
 * @returns {Promise<object|null>} - parsed JSON or null on failure
 */
async function apiFetch(endpoint, options = {}) {
  const url = API_BASE + endpoint;
  const defaultOptions = {
    headers: {
      'Content-Type': 'application/json',
    },
    credentials: 'include', // send cookies/session
  };

  const mergedOptions = {
    ...defaultOptions,
    ...options,
    headers: {
      ...defaultOptions.headers,
      ...(options.headers || {}),
    },
  };

  // If body is an object, stringify it
  if (mergedOptions.body && typeof mergedOptions.body === 'object') {
    mergedOptions.body = JSON.stringify(mergedOptions.body);
  }

  try {
    const response = await fetch(url, mergedOptions);
    const data = await response.json();

    if (!response.ok) {
      console.warn(`API Error [${response.status}]:`, data.message || data);
      return { error: true, status: response.status, message: data.message || 'Request failed', data };
    }

    return data;
  } catch (err) {
    console.warn('API unreachable:', endpoint, err.message);
    return null; // Backend unavailable — caller should use fallback
  }
}

/**
 * Check if user is currently logged in (customer).
 * Returns user object from localStorage or null.
 */
function getCurrentCustomer() {
  try {
    return JSON.parse(localStorage.getItem('shoestore_customer'));
  } catch {
    return null;
  }
}

/**
 * Log out current customer
 */
async function customerLogout() {
  await apiFetch('/auth.php?action=logout');
  localStorage.removeItem('shoestore_customer');
  window.location.href = 'customer-login.html';
}
