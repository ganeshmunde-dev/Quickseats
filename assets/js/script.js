// ============================================================
// QuickSeats Classic Logic (OTP Authentication)
// ============================================================

const API_BASE = 'api';

// ============================================================
// UI Handlers (Mobile Nav)
// ============================================================
const hamburger = document.getElementById('hamburger-btn');
const mobileMenu = document.getElementById('mobile-menu');

if (hamburger && mobileMenu) {
  hamburger.addEventListener('click', () => {
    mobileMenu.classList.toggle('open');
  });
}

// ============================================================
// User Session Management
// ============================================================
function updateUserUI() {
  const user = localStorage.getItem('qs_user');
  const desktopArea = document.getElementById('nav-user-area');
  const mobileArea = document.getElementById('mobile-user-area');

  const loggedInHTML = `
    <span style="color: white; margin-right: 1rem; font-size: 0.85rem;">👤 ${user}</span>
    <button class="btn-login" onclick="logoutUser()">Logout</button>
  `;
  
  const loggedOutHTML = `
    <button class="btn-login" onclick="openAuthModal()">Log In / Sign Up</button>
  `;

  if (desktopArea) desktopArea.innerHTML = user ? loggedInHTML : loggedOutHTML;
  if (mobileArea) {
    mobileArea.innerHTML = user 
      ? `<a href="#" onclick="logoutUser()">Logout (${user})</a>` 
      : `<a href="#" onclick="openAuthModal()">Log In / Sign Up</a>`;
  }
}

window.logoutUser = function() {
  localStorage.removeItem('qs_user');
  updateUserUI();
  if (typeof updateCheckoutState === 'function') updateCheckoutState();
};

// ============================================================
// OTP Auth Modal Logic
// ============================================================
const modal = document.getElementById('auth-modal');
const stepEmail = document.getElementById('auth-step-email');
const stepOtp = document.getElementById('auth-step-otp');

window.openAuthModal = function() {
  if (modal) {
    modal.classList.add('active');
    stepEmail.classList.remove('hidden');
    stepOtp.classList.add('hidden');
    document.getElementById('email-error').classList.add('hidden');
    document.getElementById('otp-error').classList.add('hidden');
  }
};

document.getElementById('close-modal')?.addEventListener('click', () => {
  modal.classList.remove('active');
});

document.getElementById('trigger-login')?.addEventListener('click', (e) => {
  e.preventDefault();
  openAuthModal();
});

// Step 1: Send OTP
document.getElementById('email-form')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const email = document.getElementById('auth-email').value;
  const errorBox = document.getElementById('email-error');
  const btn = e.target.querySelector('button');

  errorBox.classList.add('hidden');
  btn.textContent = 'Sending...';
  btn.disabled = true;

  try {
    const res = await fetch(`${API_BASE}/send_otp.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email })
    });
    
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Failed to send OTP.');
    
    // Move to step 2
    stepEmail.classList.add('hidden');
    stepOtp.classList.remove('hidden');

    if (data.otp) {
      const otpInput = document.getElementById('auth-otp');
      if (otpInput) otpInput.value = data.otp;
    }
    
  } catch (err) {
    errorBox.textContent = err.message;
    errorBox.classList.remove('hidden');
  } finally {
    btn.textContent = 'Send OTP';
    btn.disabled = false;
  }
});

// Step 2: Verify OTP
document.getElementById('otp-form')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const email = document.getElementById('auth-email').value;
  const otp = document.getElementById('auth-otp').value;
  const errorBox = document.getElementById('otp-error');
  const btn = e.target.querySelector('button');

  errorBox.classList.add('hidden');
  btn.textContent = 'Verifying...';
  btn.disabled = true;

  try {
    const res = await fetch(`${API_BASE}/verify_otp.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, otp })
    });
    
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Invalid OTP.');
    
    // Login Success
    localStorage.setItem('qs_user', email);
    modal.classList.remove('active');
    updateUserUI();
    if (typeof updateCheckoutState === 'function') updateCheckoutState();
    
  } catch (err) {
    errorBox.textContent = err.message;
    errorBox.classList.remove('hidden');
  } finally {
    btn.textContent = 'Verify & Continue';
    btn.disabled = false;
  }
});

// ============================================================
// Home Page - Search Flow
// ============================================================
if (document.getElementById('btn-search')) {
  // Load routes into dropdowns
  fetch(`${API_BASE}/routes.php`)
    .then(r => r.json())
    .then(routes => {
      const fromSel = document.getElementById('search-from');
      const toSel = document.getElementById('search-to');
      const fromSet = new Set(), toSet = new Set();
      
      routes.forEach(r => { fromSet.add(r.route_from); toSet.add(r.route_to); });
      fromSet.forEach(c => fromSel.appendChild(new Option(c, c)));
      toSet.forEach(c => toSel.appendChild(new Option(c, c)));
    }).catch(console.error);

  document.getElementById('btn-search').addEventListener('click', () => {
    const from = document.getElementById('search-from').value;
    const to = document.getElementById('search-to').value;
    const date = document.getElementById('search-date').value;
    
    if (!from || !to || !date) { alert("Please fill all fields."); return; }
    
    sessionStorage.setItem('search_from', from);
    sessionStorage.setItem('search_to', to);
    sessionStorage.setItem('search_date', date);
    window.location.href = 'booking.html';
  });
}

// ============================================================
// Booking Page - Logic
// ============================================================
let selectedBus = null;
let selectedSeats = [];

if (document.getElementById('bus-list')) {
  loadBuses();
}

async function loadBuses() {
  const busList = document.getElementById('bus-list');
  const from = sessionStorage.getItem('search_from');
  const to = sessionStorage.getItem('search_to');
  const date = sessionStorage.getItem('search_date');

  try {
    const res = await fetch(`${API_BASE}/routes.php`);
    let routes = await res.json();
    
    if (from && to) {
      routes = routes.filter(r => r.route_from === from && r.route_to === to);
    }
    
    busList.innerHTML = '';
    if (routes.length === 0) {
      busList.innerHTML = '<p>No buses found for this route.</p>';
      return;
    }

    routes.forEach(route => {
      const card = document.createElement('div');
      card.className = 'bus-card';
      card.innerHTML = `
        <div>
          <h3>QuickSeats Travels - ${route.bus_type}</h3>
          <p>${route.route_from} to ${route.route_to} | ${route.travel_time}</p>
        </div>
        <div style="text-align:right;">
          <div id="login-warning" class="modal-error hidden" style="margin-top:1rem;">
            Please <a href="#" id="trigger-login" style="text-decoration:underline; font-weight:bold; color:inherit;">Log In</a> to book a ticket.
          </div>
          <div class="bus-price">₹${route.price}</div>
          <button class="btn-login" style="background:var(--primary); border:none; margin-top:0.5rem;" onclick='openSeats(${JSON.stringify(route)})'>Select Seats</button>
        </div>
      `;
      busList.appendChild(card);
    });
  } catch(e) {
    console.error(e);
  }
}

window.openSeats = async function(route) {
  selectedBus = route;
  selectedSeats = [];
  updateCheckoutState();
  
  const section = document.getElementById('seat-selection');
  const grid = document.getElementById('seat-grid-container');
  section.classList.remove('hidden');
  
  let booked = [];
  try {
    const res = await fetch(`${API_BASE}/get_booked_seats.php?route_id=${route.id}&travel_date=${sessionStorage.getItem('search_date') || route.travel_date}`);
    const data = await res.json();
    if(data.success) booked = data.booked_seats;
  } catch(e) {}

  grid.innerHTML = '';
  ['A','B','C','D','E'].forEach(row => {
    const rowEl = document.createElement('div');
    rowEl.className = 'seat-row';
    rowEl.appendChild(createSeat(`${row}1`, booked));
    rowEl.appendChild(createSeat(`${row}2`, booked));
    const space = document.createElement('div'); space.className = 'seat-space'; rowEl.appendChild(space);
    rowEl.appendChild(createSeat(`${row}3`, booked));
    rowEl.appendChild(createSeat(`${row}4`, booked));
    grid.appendChild(rowEl);
  });
};

function createSeat(id, booked) {
  const el = document.createElement('div');
  el.className = 'seat';
  el.textContent = id;
  if(booked.includes(id)) {
    el.classList.add('booked');
  } else {
    el.addEventListener('click', () => {
      if(el.classList.contains('selected')) {
        el.classList.remove('selected');
        selectedSeats = selectedSeats.filter(s => s !== id);
      } else {
        el.classList.add('selected');
        selectedSeats.push(id);
      }
      updateCheckoutState();
    });
  }
  return el;
}

window.updateCheckoutState = function() {
  const empty = document.getElementById('checkout-empty');
  const form = document.getElementById('checkout-form');
  if(!empty || !form) return;

  if(!selectedBus || selectedSeats.length === 0) {
    empty.classList.remove('hidden');
    form.classList.add('hidden');
    return;
  }

  empty.classList.add('hidden');
  form.classList.remove('hidden');
  
  document.getElementById('summary-route').textContent = `${selectedBus.route_from} → ${selectedBus.route_to}`;
  document.getElementById('summary-seats').textContent = selectedSeats.join(', ');
  
  const total = parseFloat(selectedBus.price) * selectedSeats.length;
  document.getElementById('summary-total').textContent = `₹${total}`;

  const user = localStorage.getItem('qs_user');
  document.getElementById('login-warning').classList.toggle('hidden', !!user);
  document.getElementById('btn-book').disabled = !user;
};

document.getElementById('checkout-form')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const btn = document.getElementById('btn-book');
  let errorDiv = document.getElementById('checkout-error');
  if (!errorDiv) {
    errorDiv = document.createElement('div');
    errorDiv.id = 'checkout-error';
    errorDiv.className = 'modal-error hidden';
    errorDiv.style.marginTop = '1rem';
    btn.parentNode.insertBefore(errorDiv, btn);
  }
  
  errorDiv.classList.add('hidden');
  
  const paymentMethod = document.getElementById('payment-method').value;
  const payload = {
    customer_email: localStorage.getItem('qs_user'),
    passenger_name: document.getElementById('passenger-name').value,
    route_id: selectedBus.id,
    route_from: selectedBus.route_from,
    route_to: selectedBus.route_to,
    travel_date: sessionStorage.getItem('search_date') || selectedBus.travel_date,
    travel_time: selectedBus.travel_time,
    bus_type: selectedBus.bus_type,
    seats_booked: selectedSeats.length,
    selected_seats: selectedSeats.join(','),
    price_per_seat: selectedBus.price,
    total_price: parseFloat(selectedBus.price) * selectedSeats.length
  };
  
  const checkoutCard = document.querySelector('.checkout-card');

  if (paymentMethod === 'UPI') {
    const upiUrl = encodeURIComponent(`upi://pay?pa=quickseats@bank&pn=QuickSeats&am=${payload.total_price}`);
    const qrSrc = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${upiUrl}`;
    
    checkoutCard.innerHTML = `
      <div style="text-align:center; padding: 1rem;">
        <h3 style="margin-bottom: 0.5rem;">Complete UPI Payment</h3>
        <p style="margin-bottom: 1.5rem; color: #4b5563;">Scan the QR code below to pay <strong>₹${payload.total_price}</strong></p>
        <img src="${qrSrc}" alt="UPI QR Code" style="border:1px solid #e5e7eb; border-radius:12px; margin-bottom: 1.5rem; width:200px; height:200px; padding:0.5rem; background:white;" />
        <br>
        <p style="font-size: 0.85rem; color: #6b7280; margin-bottom: 1.5rem;">Or use UPI ID: quickseats@bank</p>
        <button id="btn-complete-payment" class="btn-primary-full" style="margin-bottom: 0.75rem;">I have paid</button>
        <button id="btn-cancel-payment" class="btn-secondary" style="width: 100%; border: 1px solid #cbd5e1; background: #f1f5f9; padding: 0.875rem; border-radius: 8px; font-weight: 600; cursor: pointer;">Cancel</button>
      </div>
    `;
    
    document.getElementById('btn-cancel-payment').addEventListener('click', () => {
       window.location.reload();
    });
    
    document.getElementById('btn-complete-payment').addEventListener('click', async (btnEv) => {
       const confirmBtn = btnEv.target;
       confirmBtn.textContent = 'Processing...'; confirmBtn.disabled = true;
       await processBookingInline(payload, checkoutCard);
    });
  } else {
    btn.textContent = 'Processing...'; btn.disabled = true;
    await processBookingInline(payload, checkoutCard);
  }
});

async function processBookingInline(payload, container) {
  try {
    const res = await fetch(`${API_BASE}/book_ticket.php`, {
      method: 'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload)
    });
    const data = await res.json();
    if(!res.ok) throw new Error(data.error || 'Failed to book ticket.');
    
    const userEmail = encodeURIComponent(localStorage.getItem('qs_user') || '');
    const recRes = await fetch(`${API_BASE}/get_single_booking.php?id=${data.insert_id}&email=${userEmail}`);
    const recData = await recRes.json();
    if(!recRes.ok || !recData.success) throw new Error(recData.error || 'Failed to load receipt.');
    
    const b = recData.booking;
    const pnr = `QS${String(b.id).padStart(6,'0')}`;
    const travelDate = new Date(b.travel_date).toLocaleDateString('en-IN', {day:'2-digit', month:'short', year:'numeric'});

    container.innerHTML = `
      <div class="receipt-ticket" id="receipt-ticket">
        <div class="receipt-header">
          <div>
            <div class="logo-text">🚌 QuickSeats</div>
            <div class="booking-confirmed">Booking Confirmed — E-Ticket</div>
          </div>
          <div class="receipt-success-badge">✓</div>
        </div>

        <div class="receipt-divider">
          <hr class="receipt-divider-line">
        </div>

        <div class="receipt-body">
          <div class="receipt-pnr">
            <div class="pnr-label">PNR / Booking Reference</div>
            <div class="pnr-number">${pnr}</div>
            <span class="receipt-status">✔ CONFIRMED &amp; PAID</span>
          </div>

          <div class="receipt-route">
            <div class="receipt-city">
              <div class="city-name">${b.route_from}</div>
              <div class="city-label">Origin</div>
            </div>
            <div class="receipt-arrow">→</div>
            <div class="receipt-city">
              <div class="city-name">${b.route_to}</div>
              <div class="city-label">Destination</div>
            </div>
          </div>

          <div class="receipt-grid">
            <div class="receipt-field">
              <div class="field-label">Passenger</div>
              <div class="field-value">${b.passenger_name}</div>
            </div>
            <div class="receipt-field">
              <div class="field-label">Travel Date</div>
              <div class="field-value">${travelDate}</div>
            </div>
            <div class="receipt-field">
              <div class="field-label">Departure</div>
              <div class="field-value">${b.travel_time}</div>
            </div>
            <div class="receipt-field">
              <div class="field-label">Bus Type</div>
              <div class="field-value">${b.bus_type}</div>
            </div>
            <div class="receipt-field">
              <div class="field-label">Seats</div>
              <div class="field-value">${b.selected_seats || b.seats_booked + ' seat(s)'}</div>
            </div>
            <div class="receipt-field">
              <div class="field-label">Booked By</div>
              <div class="field-value" style="word-break:break-all; font-size:0.8rem;">${b.customer_email || b.customer_mobile}</div>
            </div>
          </div>

          <div class="receipt-total-bar">
            <span class="total-label">💳 Total Amount Paid</span>
            <span class="total-amount">₹${b.total_price}</span>
          </div>

          <div class="receipt-actions">
            <button class="btn-receipt-primary" onclick="window.open('success.html?id=${b.id}&email=' + encodeURIComponent(localStorage.getItem('qs_user') || ''), '_blank')">👁 View Receipt</button>
            <button class="btn-receipt-secondary" onclick="window.print()">⬇ Download / Print</button>
          </div>
        </div>
      </div>
    `;
  } catch(err) {
    container.innerHTML = `
      <div style="text-align:center;">
        <h3 style="color:red; margin-bottom: 1rem;">Booking Failed</h3>
        <p style="margin-bottom: 1.5rem;">${err.message}</p>
        <button class="btn-primary-full" onclick="window.location.reload()">Try Again</button>
      </div>
    `;
  }
}

// Initialize Nav
updateUserUI();
