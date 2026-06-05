// public/assets/js/main.js — GroceryDash client-side logic

const APP_URL = document.querySelector('meta[name="app-url"]')?.content
  || window.location.origin + '/grocery-shop/public';

/* ── Cart badge updater ───────────────────────────────── */
function updateCartBadges(count) {
  document.querySelectorAll('#cartBadge, #cartBadgeMob').forEach(el => {
    el.textContent = count;
    el.style.display = count > 0 ? '' : 'none';
  });
}

/* ── Toast notification ───────────────────────────────── */
function showToast(message, type = 'success') {
  const existing = document.getElementById('fc-toast');
  if (existing) existing.remove();

  const toast = document.createElement('div');
  toast.id = 'fc-toast';
  toast.textContent = message;
  Object.assign(toast.style, {
    position:     'fixed',
    bottom:       '80px',
    left:         '50%',
    transform:    'translateX(-50%)',
    background:   type === 'success' ? '#2D8C4E' : '#E63946',
    color:        '#fff',
    padding:      '.7rem 1.4rem',
    borderRadius: '8px',
    fontSize:     '.875rem',
    fontWeight:   '500',
    zIndex:       '9999',
    boxShadow:    '0 4px 20px rgba(0,0,0,.2)',
    transition:   'opacity .3s ease',
  });
  document.body.appendChild(toast);
  setTimeout(() => { toast.style.opacity = '0'; }, 2500);
  setTimeout(() => toast.remove(), 2800);
}

/* ── Add to Cart ──────────────────────────────────────── */
document.addEventListener('click', async (e) => {
  // 1. ABSOLUTE GUARD CLAUSE: If clicking the heart button or its inner text, kill EVERYTHING else instantly
  if (e.target.closest('.wishlist-btn')) {
    e.stopImmediatePropagation();
    return;
  }
  
  // 2. Safely support both potential class names you used across scripts (.js-add-to-cart and .btn-add-cart)
  const btn = e.target.closest('.js-add-to-cart') || e.target.closest('.btn-add-cart');
  if (!btn) return;

  // 3. Ensure we didn't just target a disabled native placeholder button
  if (btn.hasAttribute('disabled')) return;

  // Prevent default submit behaviors if it happens to be wrapped inside an native HTML form tag
  e.preventDefault();

  const productId = btn.dataset.productId;
  const csrf      = btn.dataset.csrf;

  let quantity = 1;
  if (btn.dataset.qtyInput) {
    quantity = parseInt(document.getElementById(btn.dataset.qtyInput)?.value) || 1;
  }

  btn.disabled = true;
  const originalText = btn.textContent;
  btn.textContent = 'Adding…';

  try {
    const res  = await fetch(`${APP_URL}/cart/add`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ product_id: productId, quantity, csrf_token: csrf }),
    });
    const data = await res.json();

    if (data.success) {
      if (typeof updateCartBadges === 'function') updateCartBadges(data.cart_count);
      if (typeof showToast === 'function') showToast(data.message || 'Added to cart!');
      btn.textContent = '✓ Added!';
      setTimeout(() => { btn.textContent = originalText; btn.disabled = false; }, 1500);
    } else {
      if (typeof showToast === 'function') showToast(data.message || 'Could not add to cart.', 'error');
      btn.textContent = originalText;
      btn.disabled = false;
    }
  } catch {
    if (typeof showToast === 'function') showToast('Network error. Please try again.', 'error');
    btn.textContent = originalText;
    btn.disabled = false;
  }
});

/* ── Cart quantity update ─────────────────────────────── */
document.addEventListener('click', async (e) => {
  const btn = e.target.closest('.js-update-qty');
  if (!btn) return;

  const productId  = btn.dataset.id;
  const csrf       = btn.dataset.csrf;
  const itemEl     = document.getElementById(`cart-item-${productId}`);
  const qtyDisplay = itemEl?.querySelector('.qty-display');
  if (!qtyDisplay) return;

  let qty = parseInt(qtyDisplay.textContent);
  qty     = btn.dataset.action === 'inc' ? qty + 1 : qty - 1;
  if (qty < 0) return;

  try {
    const res  = await fetch(`${APP_URL}/cart/update`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ product_id: productId, quantity: qty, csrf_token: csrf }),
    });
    const data = await res.json();
    if (data.success) {
      updateCartBadges(data.cart_count);
      if (qty === 0) {
        itemEl?.remove();
      } else {
        qtyDisplay.textContent = qty;
      }
      // Update summary
      const summaryUpdate = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
      summaryUpdate('summarySubtotal', data.subtotal);
      summaryUpdate('summaryDelivery', data.delivery_fee);
      summaryUpdate('summaryTotal',    data.total);
    }
  } catch { showToast('Could not update cart.', 'error'); }
});

/* ── Cart item remove ─────────────────────────────────── */
document.addEventListener('click', async (e) => {
  const btn = e.target.closest('.js-remove-item');
  if (!btn) return;

  const productId = btn.dataset.id;
  const csrf      = btn.dataset.csrf;

  try {
    const res  = await fetch(`${APP_URL}/cart/remove`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ product_id: productId, csrf_token: csrf }),
    });
    const data = await res.json();
    if (data.success) {
      document.getElementById(`cart-item-${productId}`)?.remove();
      updateCartBadges(data.cart_count);
      showToast('Item removed.');
      const totalEl = document.getElementById('summaryTotal');
      if (totalEl) totalEl.textContent = data.total;
    }
  } catch { showToast('Could not remove item.', 'error'); }
});

/* ── Product detail qty controls ─────────────────────── */
const qtyMinus = document.getElementById('qtyMinus');
const qtyPlus  = document.getElementById('qtyPlus');
const qtyInput = document.getElementById('qtyInput');
if (qtyMinus && qtyPlus && qtyInput) {
  qtyMinus.addEventListener('click', () => {
    const v = parseInt(qtyInput.value);
    if (v > 1) qtyInput.value = v - 1;
  });
  qtyPlus.addEventListener('click', () => {
    const max = parseInt(qtyInput.max) || 99;
    const v   = parseInt(qtyInput.value);
    if (v < max) qtyInput.value = v + 1;
  });
}

/* ── Mobile nav toggle ────────────────────────────────── */
const navToggle = document.getElementById('navToggle');
const navLinks  = document.getElementById('navLinks');
if (navToggle && navLinks) {
  navLinks.classList.add('closed');
  navToggle.addEventListener('click', () => {
    navLinks.classList.toggle('closed');
  });
}

/* ── Auto-dismiss flash messages ─────────────────────── */
document.querySelectorAll('.flash').forEach(el => {
  setTimeout(() => el.remove(), 5000);
});

/* ── Live search autocomplete (lightweight) ───────────── */
const searchInput = document.querySelector('.search-input');
if (searchInput) {
  let debounceTimer;
  searchInput.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    const q = searchInput.value.trim();
    if (q.length < 2) {
      document.getElementById('search-autocomplete')?.remove();
      return;
    }
    debounceTimer = setTimeout(async () => {
      try {
        const res  = await fetch(`${APP_URL}/search?q=${encodeURIComponent(q)}&ajax=1`);
        // For now just submit the form on enter — full autocomplete can be wired to an API endpoint
      } catch { /* silent */ }
    }, 280);
  });
}

document.addEventListener('DOMContentLoaded', () => {
    // Globally register an efficient, single Event Delegation structural interceptor pattern listener
    document.body.addEventListener('click', async function (event) {
        const targetButton = event.target.closest('.wishlist-btn');
        if (!targetButton) return;

        event.preventDefault();
        event.stopPropagation();

        const productId = targetButton.dataset.productId;
        const tokenValue = targetButton.dataset.csrf;

        if (!productId || !tokenValue) {
            console.error('Critical operational failure: Missing element context dataset definitions.');
            return;
        }

        // Apply visual throttling interface locks to isolate atomic mutations 
        targetButton.style.opacity = '0.4';
        targetButton.style.pointerEvents = 'none';

        try {
            const endpointResponse = await fetch('/grocery-shop/public/wishlist/toggle', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `product_id=${encodeURIComponent(productId)}&csrf_token=${encodeURIComponent(tokenValue)}`
            });

            // Handle unauthenticated user pathing interception triggers
            if (endpointResponse.status === 401) {
                const unauthorizedPayload = await endpointResponse.json();
                window.location.href = unauthorizedPayload.redirect || '/login';
                return;
            }

            // Capture functional bad requests or token validations blocks
            if (!endpointResponse.ok) {
                const errorPayload = await endpointResponse.status === 419 
                    ? { message: "Session expired. Please reload your document view windows." } 
                    : await endpointResponse.json();
                throw new Error(errorPayload.message || `System communication status error exception: ${endpointResponse.status}`);
            }

            const operationDataResult = await endpointResponse.json();

            if (operationDataResult.success) {
                // Mutate target state values visually
                targetButton.innerHTML = operationDataResult.wishlisted ? '❤️' : '🤍';
                targetButton.classList.toggle('wishlisted', operationDataResult.wishlisted);

                // Handle item card destruction animation rules if evaluating removal directly inside Wishlist page grid
                const evaluationCardNodeKeyId = targetButton.dataset.removeCard;
                if (evaluationCardNodeKeyId && !operationDataResult.wishlisted) {
                    const targetDOMCardElement = document.getElementById(evaluationCardNodeKeyId);
                    if (targetDOMCardElement) {
                        targetDOMCardElement.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
                        targetDOMCardElement.style.opacity = '0';
                        targetDOMCardElement.style.transform = 'scale(0.9)';
                        
                        setTimeout(() => {
                            targetDOMCardElement.remove();
                            // If grid becomes empty, force layout refresh view state loop
                            if (!document.querySelector('.product-card')) {
                                window.location.reload();
                            }
                        }, 300);
                    }
                }
            }
        } catch (runtimeNetworkExceptionError) {
            console.error('Asynchronous pipeline execution termination trace failure:', runtimeNetworkExceptionError);
            alert(runtimeNetworkExceptionError.message || 'An error occurred. Please try again.');
        } finally {
            // Restore interactive responsiveness state loops
            targetButton.style.opacity = '1';
            targetButton.style.pointerEvents = 'auto';
        }
    });
});

