<?php
session_start();
?>
<?php
include 'Backend/config.php';
$pgResult = mysqli_query($conn, "SELECT * FROM pgs ORDER BY created_at DESC");
?>

<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>PG Connects — Find Your PG</title>
    <meta name="description"
        content="PG Connects - a frontend clone to find paying guest accommodations for students. Includes dark mode, animations, sorting, cart, coupons and mock payments." />
    <link rel="stylesheet" href="style.css" />
</head>

<body>
    <div id="app">
        <!-- ========== Header ========== -->
        <header>
            
            <div class="brand">
                <div class="logo">PG</div>
                <div>
                    <div style="font-weight:800">PG Connects</div>
                    <div class="muted" style="font-size:12px">Find student PG around you</div>
                </div>
            </div>

            <div class="nav-actions">
                <button id="themeToggle" class="icon-btn" title="Toggle dark mode">🌙</button>
                <div style="display:flex; gap:8px; align-items:center">
                    <button id="cartBtn" class="icon-btn">🧾 <span id="cartCount"
                            style="font-weight:700; margin-left:6px">0</span></button>
                    <div class="nav-actions">
  <?php if (isset($_SESSION['username'])): ?>
      <span style="margin-right:10px; font-weight:600;">
        👋 Welcome, <?php echo $_SESSION['username']; ?>
      </span>
      <a href="Backend/logout.php" class="btn">Logout</a>
  <?php else: ?>
      <a href="Backend/login.php" class="btn">Sign In</a>
      <a href="Backend/signup.php" class="btn">Sign Up</a>
  <?php endif; ?>
</div>

                </div>
            </div>
            
        </header>
        
        

        <!-- ========== Hero ========== -->
        <section class="hero">
            <div class="bg-blobs">
                <div class="blob a"></div>
                <div class="blob b"></div>
            </div>
            <div class="hero-inner">
                <div class="left">
                    <h1>Find the perfect PG near your college — fast</h1>
                    <p class="lead">Search, filter, sort and book PGs. Apply coupons, choose delivery (move-in
                        assistance) options and pay online or by cash on arrival. (This is a frontend demo / clone.)</p>

                    <div class="controls">
                        <div class="search">
                            <span>🔎</span>
                            <input id="searchInput"
                                placeholder="Search by city, college or amenities (eg: WiFi, Attached Bathroom)" />
                            <select id="sortSelect" title="Sort">
                                <option value="popular">Sort: Popular</option>
                                <option value="price-asc">Price: Low to High</option>
                                <option value="price-desc">Price: High to Low</option>
                                <option value="rating">Rating</option>
                                <option value="discount">Discount</option>
                            </select>
                        </div>
                        <div class="filters">
                            <select id="typeFilter">
                                <option value="all">All Types</option>
                                <option value="single">Single Room</option>
                                <option value="sharing">Sharing</option>
                            </select>
                            <select id="amenityFilter">
                                <option value="any">Any Amenity</option>
                                <option value="wifi">WiFi</option>
                                <option value="ac">AC</option>
                                <option value="attached">Attached Bathroom</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="right" style="width:340px;">
                    <div style="background:var(--card); padding:16px; border-radius:12px; box-shadow:var(--shadow);">
                        <div style="font-weight:800">Special Offer</div>
                        <div class="muted" style="margin:8px 0">Flat discounts on early bookings. Example coupon: <span
                                class="coupon-badge">STUDENT10</span></div>
                        <button id="bookNow" class="btn" style="width:100%">Quick Book</button>
                    </div>
                </div>
            </div>
        </section>
        

        <!-- ========== Main Layout ========== -->
        <main class="main">
            <aside class="sidebar">
                <div style="font-weight:800; margin-bottom:10px">Filters & Delivery</div>
                <div style="margin-bottom:8px"><label><input type="checkbox" id="filterFemale" /> Female-only PG</label>
                </div>
                <div style="margin-bottom:8px"><label><input type="checkbox" id="filterVeg" /> Pure Vegetarian</label>
                </div>

                <div style="margin-top:10px">
                    <div style="font-weight:700; margin-bottom:6px">Move-in Assistance (Delivery)</div>
                    <select id="deliveryOption">
                        <option value="standard">Standard - free</option>
                        <option value="assisted">Assisted - ₹499 (helper + shift)</option>
                        <option value="express">Express - ₹999 (priority)</option>
                    </select>
                </div>

                <div style="margin-top:12px">
                    <div style="font-weight:700; margin-bottom:6px">Coupons</div>
                    <input id="couponInput" placeholder="Enter coupon code"
                        style="width:100%; padding:8px; border-radius:8px;" />
                    <div style="margin-top:8px; display:flex; gap:8px;"><button id="applyCoupon"
                            class="btn">Apply</button>
                        <div id="couponMsg" class="muted" style="align-self:center"></div>
                    </div>
                </div>

                <div style="margin-top:12px">
                    <div style="font-weight:700; margin-bottom:6px">Quick Filters</div>
                    <div style="display:flex; gap:8px; flex-wrap:wrap">
                        <button class="pill" data-filter="wifi">WiFi</button>
                        <button class="pill" data-filter="attached">Attached Bath</button>
                        <button class="pill" data-filter="ac">AC</button>
                        <button class="pill" data-filter="security">24/7 Security</button>
                    </div>
                </div>
            </aside>

            <section>
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px">
                    <div style="font-weight:800">Available PGs</div>
                    <div class="muted">Showing <span id="resultsCount">0</span> results</div>
                </div>

                <div class="listings" id="listings"></div>
            </section>
 <!-- ========== USER ENTERED PGS ========== -->
  <section style="margin-top:40px;">
  <div style="font-weight:800; margin-bottom:12px; text-align:center;">User Submitted PGs</div>
  
  <div class="listings">
    <?php if (mysqli_num_rows($pgResult) > 0): ?>
      <?php while ($pg = mysqli_fetch_assoc($pgResult)): ?>
        <div class="card" style="display:flex; gap:12px; align-items:center;">
          <img src="<?php echo $pg['image']; ?>" 
               alt="<?php echo htmlspecialchars($pg['title']); ?>" 
               style="width:110px; height:80px; object-fit:cover; border-radius:8px;" />
          <div class="meta">
            <div style="display:flex; justify-content:space-between; align-items:center">
              <div style="font-weight:800"><?php echo htmlspecialchars($pg['title']); ?></div>
              <div class="price">₹<?php echo $pg['price']; ?></div>
            </div>
            <div class="muted"><?php echo htmlspecialchars($pg['city']); ?> · <?php echo $pg['type']; ?></div>
            <div class="tags">
              <?php 
                $tags = explode(",", $pg['amenities']);
                foreach ($tags as $t) {
                  echo "<div class='tag'>" . htmlspecialchars(trim($t)) . "</div>";
                }
              ?>
            </div>
            <div style="margin-top:6px; font-size:12px; color:gray;">
              Posted by: <?php echo $pg['created_by']; ?>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="muted" style="text-align:center;">No PGs added yet. Be the first to post!</div>
    <?php endif; ?>
  </div>
</section>

        

        </main>

        <!-- ========== Cart Panel ========== -->
        <div id="cartPanel" class="cart-list hide" aria-hidden="true">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px">
                <div style="font-weight:800">Cart / Shortlist</div><button id="closeCart" class="icon-btn">✖</button>
            </div>
            <div id="cartItems"></div>
            <div style="margin-top:10px; display:flex; justify-content:space-between; align-items:center">
                <div>
                    <div class="muted">Subtotal</div>
                    <div id="subtotal">₹0</div>
                </div>
                <div><button id="checkoutBtn" class="btn">Checkout</button></div>
            </div>
        </div>

        <!-- ========== Checkout Modal ========== -->
        <div id="modalBackdrop" class="modal-backdrop">
            <div class="modal" role="dialog" aria-modal="true">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px">
                    <div style="font-weight:800">Checkout</div><button id="closeModal" class="icon-btn">✖</button>
                </div>
                <div style="display:flex; gap:12px;">
                    <div style="flex:1">
                        <div style="font-weight:700; margin-bottom:6px">Guest Details</div>
                        <input id="guestName" placeholder="Full name"
                            style="width:100%; padding:10px; border-radius:8px; margin-bottom:8px;" />
                        <input id="guestPhone" placeholder="Phone"
                            style="width:100%; padding:10px; border-radius:8px; margin-bottom:8px;" />
                        <div style="font-weight:700; margin:6px 0">Payment Method</div>
                        <div class="payment-method">
                            <div class="payment-card" data-method="upi">UPI</div>
                            <div class="payment-card" data-method="card">Credit / Debit Card</div>
                            <div class="payment-card" data-method="netbanking">Netbanking</div>
                            <div class="payment-card" data-method="cod">Cash on Delivery</div>
                        </div>
                    </div>

                    <div style="width:260px;">
                        <div style="font-weight:700; margin-bottom:6px">Order Summary</div>
                        <div id="summaryList"></div>
                        <div style="margin-top:8px; display:flex; justify-content:space-between">
                            <div class="muted">Delivery</div>
                            <div id="summaryDelivery">₹0</div>
                        </div>
                        <div style="margin-top:8px; display:flex; justify-content:space-between">
                            <div class="muted">Discount</div>
                            <div id="summaryDiscount">-₹0</div>
                        </div>
                        <div style="margin-top:12px; display:flex; justify-content:space-between; align-items:center">
                            <div style="font-weight:900">Total</div>
                            <div id="summaryTotal">₹0</div>
                        </div>
                        <div style="margin-top:12px"><button id="payNow" class="btn" style="width:100%">Pay /
                                Confirm</button></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== Footer ========== -->
      <footer>
  <div style="max-width:1200px; margin:0 auto; text-align:center; padding:20px;">
      
      <?php if (isset($_SESSION['username'])): ?>
        <a href="Backend/add_pg.php" 
        style="background:#0066ff; color:white; padding:10px 16px; border-radius:8px; 
                text-decoration:none; font-weight:600; display:inline-block; box-shadow:0 4px 10px rgba(0,0,0,0.15);">
          Add Your PG
        </a>
        <?php else: ?>
            <span style="color:gray; font-size:13px">Login to post your PG</span>
            <?php endif; ?>
            <div style="color:gray; margin-bottom:15px;">
              PG Connects &mdash; frontend clone demo. Customize data, integrate backend or payment SDK to make this production-ready.
            </div>
  </div>
</footer>

    </div>
    

    <script src="app.js"></script>

</body>

</html>
<?php
include 'Backend/config.php';
$result = mysqli_query($conn, "SELECT * FROM pgs ORDER BY created_at DESC");
?>

