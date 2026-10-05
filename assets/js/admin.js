// ============================================================
// QuickSeats Admin Dashboard Logic
// ============================================================
(function () {
  'use strict';

  const API = 'api';

  // ── Element references ──────────────────────────────────
  const loginSection    = document.getElementById('admin-login-section');
  const dashboardSection= document.getElementById('admin-dashboard-section');
  const loginForm       = document.getElementById('admin-login-form');
  const loginError      = document.getElementById('admin-login-error');
  const logoutBtn       = document.getElementById('admin-logout-btn');
  const addRouteForm    = document.getElementById('add-route-form');
  const addRouteError   = document.getElementById('add-route-error');
  const addRouteSuccess = document.getElementById('add-route-success');
  const routeList       = document.getElementById('admin-route-list');
  const bookingList     = document.getElementById('admin-booking-list');

  // ── Utility helpers ─────────────────────────────────────
  function showEl(el)  { if (el) el.classList.remove('hidden'); }
  function hideEl(el)  { if (el) el.classList.add('hidden'); }
  function setMsg(el, msg, type) {
    if (!el) return;
    el.textContent = msg;
    el.className = type === 'error' ? 'admin-error-box' : 'admin-success-box';
    showEl(el);
  }

  // ── Session check ────────────────────────────────────────
  async function checkAdminStatus() {
    try {
      const res  = await fetch(`${API}/admin_auth.php?action=status`);
      const data = await res.json();
      if (data.success && data.loggedIn) {
        showDashboard();
      } else {
        showLogin();
      }
    } catch (e) {
      showLogin();
    }
  }

  function showDashboard() {
    hideEl(loginSection);
    showEl(dashboardSection);
    showEl(logoutBtn);
    loadOverview();
  }

  function showLogin() {
    showEl(loginSection);
    hideEl(dashboardSection);
    hideEl(logoutBtn);
  }

  // ── Login ─────────────────────────────────────────────────
  if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      hideEl(loginError);
      const btn = loginForm.querySelector('button[type=submit]');
      const username = document.getElementById('admin-username').value.trim();
      const password = document.getElementById('admin-password').value;

      btn.textContent = 'Logging in…';
      btn.disabled = true;

      try {
        const res  = await fetch(`${API}/admin_auth.php?action=login`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ username, password })
        });
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.error || 'Invalid credentials.');
        showDashboard();
      } catch (err) {
        setMsg(loginError, err.message, 'error');
      } finally {
        btn.textContent = 'Secure Login';
        btn.disabled = false;
      }
    });
  }

  // ── Logout ────────────────────────────────────────────────
  if (logoutBtn) {
    logoutBtn.addEventListener('click', async (e) => {
      e.preventDefault();
      await fetch(`${API}/admin_auth.php?action=logout`);
      showLogin();
    });
  }

  // ── Tab navigation ───────────────────────────────────────
  document.querySelectorAll('.admin-tab-link').forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      const tab = link.dataset.tab;

      document.querySelectorAll('.admin-tab-link').forEach(l => l.classList.remove('active'));
      link.classList.add('active');

      document.querySelectorAll('.admin-tab-panel').forEach(p => hideEl(p));
      const panel = document.getElementById(`tab-${tab}`);
      if (panel) showEl(panel);

      // Load tab-specific data on first visit
      if (tab === 'routes')   loadRoutes();
      if (tab === 'bookings') loadBookings();
    });
  });

  // ── Overview ─────────────────────────────────────────────
  async function loadOverview() {
    try {
      const [bRes, rRes] = await Promise.all([
        fetch(`${API}/get_bookings.php`),
        fetch(`${API}/routes.php`)
      ]);

      if (bRes.ok) {
        const bookings = await bRes.json();
        if (Array.isArray(bookings)) {
          const rev = bookings.reduce((s, b) => s + parseFloat(b.total_price || 0), 0);
          const tix = bookings.reduce((s, b) => s + parseInt(b.seats_booked || 0), 0);
          document.getElementById('stat-revenue').textContent = `₹${rev.toLocaleString('en-IN')}`;
          document.getElementById('stat-tickets').textContent = tix;
        }
      }

      if (rRes.ok) {
        const routes = await rRes.json();
        if (Array.isArray(routes)) {
          document.getElementById('stat-routes').textContent = routes.length;
        }
      }
    } catch (e) {
      console.error('Overview load error:', e);
    }
  }

  // ── Load Routes ──────────────────────────────────────────
  async function loadRoutes() {
    if (!routeList) return;
    routeList.innerHTML = '<p class="admin-placeholder">Loading routes…</p>';
    try {
      const res    = await fetch(`${API}/routes.php`);
      const routes = await res.json();

      if (!Array.isArray(routes) || routes.length === 0) {
        routeList.innerHTML = '<p class="admin-placeholder">No routes found.</p>';
        return;
      }

      routeList.innerHTML = '';
      routes.forEach(r => {
        const item = document.createElement('div');
        item.className = 'admin-route-item';
        item.innerHTML = `
          <div class="admin-route-info">
            <div class="admin-route-name">🚌 ${r.route_from} → ${r.route_to}</div>
            <div class="admin-route-meta">
              ${r.travel_date} &nbsp;·&nbsp; ${r.travel_time} &nbsp;·&nbsp;
              ${r.bus_type} &nbsp;·&nbsp; ₹${parseFloat(r.price).toLocaleString('en-IN')} &nbsp;·&nbsp;
              ${r.seats} seats
            </div>
          </div>
          <button class="admin-btn-danger" data-id="${r.id}">Delete</button>
        `;
        item.querySelector('.admin-btn-danger').addEventListener('click', async () => {
          if (!confirm('Delete this route?')) return;
          await fetch(`${API}/routes.php?id=${r.id}`, { method: 'DELETE' });
          loadRoutes();
          loadOverview();
        });
        routeList.appendChild(item);
      });
    } catch (e) {
      routeList.innerHTML = '<p class="admin-placeholder">Failed to load routes.</p>';
    }
  }

  // ── Add Route Form ────────────────────────────────────────
  if (addRouteForm) {
    addRouteForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      hideEl(addRouteError);
      hideEl(addRouteSuccess);

      const btn = addRouteForm.querySelector('button[type=submit]');
      btn.textContent = 'Saving…';
      btn.disabled = true;

      const payload = {
        from_location: document.getElementById('route-from').value.trim(),
        to_location:   document.getElementById('route-to').value.trim(),
        travel_date:   document.getElementById('route-date').value,
        travel_time:   document.getElementById('route-time').value,
        bus_type:      document.getElementById('route-type').value,
        price:         parseFloat(document.getElementById('route-price').value),
        seats:         parseInt(document.getElementById('route-seats').value)
      };

      try {
        const res  = await fetch(`${API}/routes.php`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.error || 'Failed to add route.');

        setMsg(addRouteSuccess, '✔ Route added successfully!', 'success');
        addRouteForm.reset();
        document.getElementById('route-seats').value = '30';
        loadRoutes();
        loadOverview();
      } catch (err) {
        setMsg(addRouteError, err.message, 'error');
      } finally {
        btn.textContent = '➕ Add Route';
        btn.disabled = false;
      }
    });
  }

  // ── Load Bookings ─────────────────────────────────────────
  async function loadBookings() {
    if (!bookingList) return;
    bookingList.innerHTML = '<p class="admin-placeholder">Loading bookings…</p>';
    try {
      const res  = await fetch(`${API}/get_bookings.php`);
      const data = await res.json();

      if (!Array.isArray(data) || data.length === 0) {
        bookingList.innerHTML = '<p class="admin-placeholder">No bookings found.</p>';
        return;
      }

      bookingList.innerHTML = '';
      data.forEach(b => {
        const pnr = `QS${String(b.id).padStart(6, '0')}`;
        const item = document.createElement('div');
        item.className = 'admin-booking-item';
        item.innerHTML = `
          <div class="admin-booking-info">
            <div class="admin-booking-name">
              ${b.passenger_name || 'Unknown'} &nbsp;·&nbsp; <span style="color:#6b7280; font-weight:500;">${pnr}</span>
            </div>
            <div class="admin-booking-meta">
              ${b.route_from} → ${b.route_to} &nbsp;·&nbsp;
              ${b.travel_date} &nbsp;·&nbsp;
              ${b.seats_booked} seat(s) &nbsp;·&nbsp;
              ${b.customer_email || b.customer_mobile || ''}
            </div>
          </div>
          <div class="admin-booking-amount">₹${parseFloat(b.total_price).toLocaleString('en-IN')}</div>
        `;
        bookingList.appendChild(item);
      });
    } catch (e) {
      bookingList.innerHTML = '<p class="admin-placeholder">Failed to load bookings.</p>';
    }
  }

  // ── Initialise ───────────────────────────────────────────
  checkAdminStatus();

})();
