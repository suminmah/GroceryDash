<footer class="site-footer">
  <div class="footer-inner">
    
    <div class="footer-brand-col">
      <a href="<?= APP_URL ?>/" class="logo">
        <span class="logo-text">Grocery<strong>Dash</strong></span>
      </a>
      <p class="footer-bio">
        Your neighborhood grocery store, delivered fresh to your door in 30 minutes or less. Clean, fresh, guaranteed.
      </p>
      <div class="footer-socials">
        <a href="#" class="social-icon-btn" aria-label="Facebook">FB</a>
        <a href="#" class="social-icon-btn" aria-label="Instagram">IG</a>
        <a href="#" class="social-icon-btn" aria-label="Twitter">TW</a>
      </div>
    </div>

    <div class="footer-links-col">
      <h3 class="footer-heading">Shop</h3>
      <ul class="footer-links-list">
        <li><a href="<?= APP_URL ?>/shop" class="footer-link">All Products</a></li>
        <li><a href="<?= APP_URL ?>/offers" class="footer-link">Today's Offers</a></li>
        <li><a href="<?= APP_URL ?>/shop?category=vegetables" class="footer-link">Vegetables</a></li>
        <li><a href="<?= APP_URL ?>/shop?category=fruits" class="footer-link">Fruits</a></li>
        <li><a href="<?= APP_URL ?>/shop?category=dairy-eggs" class="footer-link">Dairy & Eggs</a></li>
      </ul>
    </div>

    <div class="footer-links-col">
      <h3 class="footer-heading">Support</h3>
      <ul class="footer-links-list">
        <li><a href="<?= APP_URL ?>/help" class="footer-link">Help Centre</a></li>
        <li><a href="<?= APP_URL ?>/delivery" class="footer-link">Delivery Info</a></li>
        <li><a href="<?= APP_URL ?>/about" class="footer-link">About Us</a></li>
        <li><a href="<?= APP_URL ?>/account/orders" class="footer-link">Track Order</a></li>
      </ul>
    </div>

    <div class="footer-links-col" style="flex: 1.5 !important; max-width: 320px !important;">
      <h3 class="footer-heading">Newsletter</h3>
      <p style="font-size: 0.9rem; line-height: 1.5; margin-bottom: 1rem; color: #94a3b8;">
        Subscribe to get updates on special offers, fresh arrivals, and recipes.
      </p>
      <form action="#" method="POST" onsubmit="event.preventDefault(); alert('Thank you for subscribing!');" style="display: flex; gap: 0.5rem; width: 100%;">
        <input type="email" placeholder="Your email address" required 
               style="flex: 1; padding: 0.6rem 0.85rem; border-radius: 8px; border: 1px solid #333; background: #222; color: #fff; font-size: 0.875rem; outline: none; transition: border-color 0.2s;">
        <button type="submit" 
                style="background: var(--green, #198754); color: #fff; border: none; padding: 0.6rem 1rem; border-radius: 8px; font-weight: 600; cursor: pointer; transition: background 0.2s;">
          Join
        </button>
      </form>
    </div>

  </div>

  <div class="footer-bottom">
    <div class="footer-bottom-inner">
      <p class="copyright-text">
        &copy; <?= date('Y') ?> GroceryDash. All rights reserved.
      </p>
      <div class="footer-legal-links">
        <a href="<?= APP_URL ?>/privacy" class="footer-legal-link">Privacy Policy</a>
        <a href="<?= APP_URL ?>/terms" class="footer-legal-link">Terms of Use</a>
      </div>
    </div>
  </div>
</footer>

<button id="scrollToTopBtn" class="scroll-btn-top" aria-label="Scroll back to top horizontal boundary" title="Go to top">
  ▲
</button>


<script>
document.addEventListener("DOMContentLoaded", function () {
  const scrollBtn = document.getElementById("scrollToTopBtn");

  if (scrollBtn) {
    // Reveal Scroll Trigger Button gracefully based on screen matrix coordinate depth
    window.addEventListener("scroll", () => {
      if (window.scrollY > 400) {
        scrollBtn.classList.add("show");
      } else {
        scrollBtn.classList.remove("show");
      }
    });

    // Handle automated smooth scrolling execution
    scrollBtn.addEventListener("click", () => {
      window.scrollTo({
        top: 0,
        behavior: "smooth"
      });
    });
  }
});

document.addEventListener('DOMContentLoaded', () => {
    // Event delegation: Intercept clicks on any element with the .wishlist-btn class
    document.body.addEventListener('click', function(e) {
        const btn = e.target.closest('.wishlist-btn');
        if (!btn) return; // Exit if a wishlist button wasn't clicked

        e.preventDefault();

        // 1. Extract data structures embedded natively in the element
        const productId = btn.getAttribute('data-product-id');
        const csrfToken = btn.getAttribute('data-csrf');

        // 2. Format request body to automatically populate PHP's $_POST array
        const payload = new URLSearchParams();
        payload.append('product_id', productId);
        payload.append('csrf_token', csrfToken); // Matches your verifyCsrf() utility lookups

        // 3. Send async dispatch stream to your route controller endpoint
        fetch('/grocery-shop/public/wishlist/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: payload
        })
        .then(async response => {
            const data = await response.json();
            
            // Check for explicit structural HTTP status issues
            if (!response.ok) {
                // Handle unauthenticated state redirections natively (401 Unauthorized)
                if (response.status === 401 && data.redirect) {
                    window.location.href = data.redirect;
                    return;
                }
                throw new Error(data.message || 'Server error occurred.');
            }
            return data;
        })
        .then(data => {
            if (data && data.success) {
                // 4. Update element UI states smoothly based on controller responses
                if (data.wishlisted) {
                    btn.classList.add('wishlisted');
                    btn.innerHTML = '❤️';
                } else {
                    btn.classList.remove('wishlisted');
                    btn.innerHTML = '🤍';
                }

                // Handle item card removal animation if on the wishlist page
                const removeCardId = btn.getAttribute('data-remove-card');
                if (removeCardId && !data.wishlisted) {
                    const card = document.getElementById(removeCardId);
                    if (card) {
                        card.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.9)';
                        
                        setTimeout(() => {
                            card.remove();
                            // If wishlist is now empty, reload to show empty state view
                            const grid = document.querySelector('.products-grid');
                            if (grid && !grid.querySelector('.product-card')) {
                                window.location.reload();
                            }
                        }, 300);
                    }
                }
            }
        })
        .catch(err => {
            console.error('Wishlist Action Failure:', err);
            alert(err.message || 'Failed to update wishlist. Please try again.');
        });
    });

    // ─────────────────────────────────────────────────────────
    // GLOBAL AJAX HANDLER: ADD TO CART
    // ─────────────────────────────────────────────────────────
    document.body.addEventListener('submit', function(e) {
        const form = e.target;
        if (form.getAttribute('action') && form.getAttribute('action').includes('/cart/add')) {
            e.preventDefault();
            
            const submitBtn = form.querySelector('button[type="submit"]');
            let originalText = '';
            
            if (submitBtn) {
                originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Adding...';
            }
            
            fetch(form.getAttribute('action'), {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                // If backend redirects despite our headers (should not happen anymore)
                if (response.redirected) {
                    window.location.href = response.url;
                    return null;
                }
                return response.json();
            })
            .then(data => {
                if (!data) return; // Handled by redirect above
                
                if (data.success) {
                    // Update global cart counter pills
                    const cartCounters = document.querySelectorAll('.cart-count, .cart-counter, .cart-badge');
                    cartCounters.forEach(counter => {
                        counter.innerText = data.cart_count;
                        counter.style.display = 'inline-block';
                    });
                    
                    if (submitBtn) {
                        submitBtn.innerHTML = 'Added! ✓';
                        setTimeout(() => {
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                        }, 2000);
                    }
                } else {
                    alert(data.message || 'Failed to add item to cart.');
                    if (submitBtn) {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }
                }
            })
            .catch(err => {
                console.error('Cart Add Error:', err);
                if (submitBtn) {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            });
        }
    }); // Closes the submit listener

    // ─────────────────────────────────────────────────────────
    // GLOBAL SMOOTH PAGE TRANSITIONS
    // ─────────────────────────────────────────────────────────
    // Fade in when the page is fully parsed (hides unstyled flashes instantly)
    document.body.classList.add('page-loaded');

    // Safeguard for Safari/iOS Back-Forward Cache (bfcache)
    // If the user clicks the browser 'Back' button, ensure the page is visible
    window.addEventListener('pageshow', function (event) {
        if (event.persisted) {
            document.body.classList.remove('page-exiting');
            document.body.classList.add('page-loaded');
        }
    });
});
</script>