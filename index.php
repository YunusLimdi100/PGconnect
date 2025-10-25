<?php
session_start();

include 'Backend/config.php';

// Initialize variables
$where = [];
$params = [];
$order = "ORDER BY created_at DESC";
$search_performed = false;

try {
    // Search filter
    if (!empty($_GET['q'])) {
        $search_performed = true;
        $q = mysqli_real_escape_string($conn, $_GET['q']);
        $where[] = "(title LIKE '%$q%' OR city LIKE '%$q%' OR amenities LIKE '%$q%')";
    }

    // Type filter
    if (!empty($_GET['type']) && $_GET['type'] != "all") {
        $search_performed = true;
        $type = mysqli_real_escape_string($conn, $_GET['type']);
        $where[] = "type = '$type'";
    }

    // AC filter
    if (isset($_GET['ac'])) {
        $search_performed = true;
        $where[] = "amenities LIKE '%AC%'";
    }

    // WiFi filter
    if (isset($_GET['wifi'])) {
        $search_performed = true;
        $where[] = "amenities LIKE '%WiFi%'";
    }

    // Attached Bathroom filter
    if (isset($_GET['attached'])) {
        $search_performed = true;
        $where[] = "amenities LIKE '%Attached Bath%'";
    }

    // Female-only
    if (isset($_GET['femaleOnly'])) {
        $search_performed = true;
        $where[] = "femaleOnly = 1";
    }

    // Sorting
    if (!empty($_GET['sort'])) {
        $search_performed = true;
        $allowed_sorts = ['price-asc', 'price-desc', 'rating', 'discount'];
        $sort = $_GET['sort'];
        
        if (in_array($sort, $allowed_sorts)) {
            switch ($sort) {
                case 'price-asc': $order = "ORDER BY price ASC"; break;
                case 'price-desc': $order = "ORDER BY price DESC"; break;
                case 'rating': $order = "ORDER BY rating DESC"; break;
                case 'discount': $order = "ORDER BY discount DESC"; break;
            }
        }
    }
    
    // Final SQL
    $sql = "SELECT * FROM pgs";
    if (count($where) > 0) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    $sql .= " $order";
    
    $pgs = mysqli_query($conn, $sql);
    if (!$pgs) {
        throw new Exception("SQL Error: " . mysqli_error($conn));
    }
    
    $pg_count = mysqli_num_rows($pgs);

} catch (Exception $e) {
    $error = $e->getMessage();
    $pg_count = 0;
    $pgs = [];
}
?>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>PG Connects — Find Your PG</title>
    <meta name="description"
        content="PG Connects - a frontend clone to find paying guest accommodations for students. Includes sorting, cart, coupons and mock payments." />
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
                <div style="display:flex; gap:8px; align-items:center">
                    <button id="cartBtn" class="icon-btn">🧾 <span id="cartCount"
                            style="font-weight:700; margin-left:6px">0</span></button>
                    <div class="nav-actions">
                        <?php if (isset($_SESSION['username'])): ?>
                        <span style="margin-right:10px; font-weight:600;">
                            👋 Welcome,
                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </span>
                        <a href="Backend/logout.php" class="btn">Logout</a>
                        <?php else: ?>
                        <a href="login.php" class="btn">Sign In</a>
                        <a href="signup.php" class="btn">Sign Up</a>
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
                        assistance) options and pay online or by cash on arrival.</p>

                    <div class="controls">
                        <form method="GET" action="" style="width: 100%;">
                            <div class="search">
                                <span>🔎</span>
                                <input name="q" id="searchInput" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>"
                                    placeholder="Search by city, college or amenities (eg: WiFi, AC, Attached Bathroom)" />
                                <select name="sort" id="sortSelect" title="Sort" onchange="this.form.submit()">
                                    <option value="popular" <?php echo ($_GET['sort'] ?? '') == 'popular' ? 'selected' : ''; ?>>Sort: Popular</option>
                                    <option value="price-asc" <?php echo ($_GET['sort'] ?? '') == 'price-asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                                    <option value="price-desc" <?php echo ($_GET['sort'] ?? '') == 'price-desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                                    <option value="rating" <?php echo ($_GET['sort'] ?? '') == 'rating' ? 'selected' : ''; ?>>Rating</option>
                                    <option value="discount" <?php echo ($_GET['sort'] ?? '') == 'discount' ? 'selected' : ''; ?>>Discount</option>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========== Main Layout ========== -->
        <main class="main">
            <aside class="sidebar">
                <div style="font-weight:800; margin-bottom:15px">Filters</div>
                
                <form method="GET" action="">
                    <!-- Preserve existing search -->
                    <?php if (!empty($_GET['q'])): ?>
                        <input type="hidden" name="q" value="<?php echo htmlspecialchars($_GET['q']); ?>">
                    <?php endif; ?>
                    
                    <!-- Room Type -->
                    <div style="margin-bottom:12px">
                        <div style="font-weight:700; margin-bottom:6px">Room Type</div>
                        <select name="type" onchange="this.form.submit()" style="width:100%; padding:8px; border-radius:6px;">
                            <option value="all" <?php echo ($_GET['type'] ?? '') == 'all' ? 'selected' : ''; ?>>All Types</option>
                            <option value="single" <?php echo ($_GET['type'] ?? '') == 'single' ? 'selected' : ''; ?>>Single Room</option>
                            <option value="sharing" <?php echo ($_GET['type'] ?? '') == 'sharing' ? 'selected' : ''; ?>>Sharing Room</option>
                        </select>
                    </div>

                    <!-- Amenities -->
                    <div style="margin-bottom:12px">
                        <div style="font-weight:700; margin-bottom:8px">Amenities</div>
                        <div style="margin-bottom:8px">
                            <label><input type="checkbox" name="ac" <?php echo isset($_GET['ac']) ? 'checked' : ''; ?> onchange="this.form.submit()" /> AC</label>
                        </div>
                        <div style="margin-bottom:8px">
                            <label><input type="checkbox" name="wifi" <?php echo isset($_GET['wifi']) ? 'checked' : ''; ?> onchange="this.form.submit()" /> WiFi</label>
                        </div>
                        <div style="margin-bottom:8px">
                            <label><input type="checkbox" name="attached" <?php echo isset($_GET['attached']) ? 'checked' : ''; ?> onchange="this.form.submit()" /> Attached Bathroom</label>
                        </div>
                    </div>

                    <!-- Other Filters -->
                    <div style="margin-bottom:12px">
                        <div style="font-weight:700; margin-bottom:8px">Other Filters</div>
                        <div style="margin-bottom:8px">
                            <label><input type="checkbox" name="femaleOnly" <?php echo isset($_GET['femaleOnly']) ? 'checked' : ''; ?> onchange="this.form.submit()" /> Female-only PG</label>
                        </div>
                    </div>

                    <!-- Clear Filters -->
                    <?php if ($search_performed): ?>
                    <div style="margin-top:15px">
                        <a href="?" class="btn" style="width:100%; text-align:center; display:block;">Clear All Filters</a>
                    </div>
                    <?php endif; ?>
                </form>

                <div style="margin-top:20px">
                    <div style="font-weight:700; margin-bottom:6px">Move-in Assistance</div>
                    <select id="deliveryOption" style="width:100%; padding:8px; border-radius:6px;">
                        <option value="standard">Standard - free</option>
                        <option value="assisted">Assisted - ₹499 (helper + shift)</option>
                        <option value="express">Express - ₹999 (priority)</option>
                    </select>
                </div>

                <div style="margin-top:15px">
                    <div style="font-weight:700; margin-bottom:6px">Apply Coupon</div>
                    <input id="couponInput" placeholder="Enter coupon code"
                        style="width:100%; padding:8px; border-radius:6px;" />
                    <div style="margin-top:8px; display:flex; gap:8px;">
                        <button id="applyCoupon" class="btn">Apply</button>
                        <div id="couponMsg" class="muted" style="align-self:center"></div>
                    </div>
                </div>
            </aside>

            <section>
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px">
                    <div style="font-weight:800">Available PGs</div>
                    <div class="muted">Showing <?php echo $pg_count; ?> results</div>
                </div>

                <?php if (isset($error)): ?>
                    <div class="error-message">Error loading PGs: <?php echo htmlspecialchars($error); ?></div>
                <?php elseif ($pg_count == 0): ?>
                    <div class="no-results">
                        <?php if ($search_performed): ?>
                            No PGs found matching your criteria. Try adjusting your filters.
                        <?php else: ?>
                            No PGs available at the moment. Please check back later.
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="listings">
                        <?php while ($pg = mysqli_fetch_assoc($pgs)): ?>
                            <div class="card">
                                <img src="<?php echo htmlspecialchars($pg['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($pg['title']); ?>" 
                                     onerror="this.src='https://via.placeholder.com/300x200?text=PG+Image'" />
                                <div class="meta">
                                    <div style="display:flex; justify-content:space-between; align-items:center">
                                        <div style="font-weight:800"><?php echo htmlspecialchars($pg['title']); ?></div>
                                        <div class="price">₹<?php echo number_format($pg['price']); ?></div>
                                    </div>
                                    <div class="muted">
                                        <?php echo htmlspecialchars($pg['city']); ?> · <?php echo $pg['rating']; ?> ★
                                        <?php echo $pg['femaleOnly'] ? ' · Female-only' : ''; ?>
                                    </div>
                                    <div class="tags">
                                        <?php 
                                        $tags = explode(",", $pg['amenities']);
                                        foreach ($tags as $t) {
                                            echo "<div class='tag'>" . htmlspecialchars(trim($t)) . "</div>";
                                        }
                                        ?>
                                        <?php if ($pg['discount'] > 0) echo "<div class='tag'>{$pg['discount']}% off</div>"; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
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
                <div style="color:gray; margin-bottom:15px;">
                    PG Connects &mdash; frontend clone demo. Customize data, integrate backend or payment SDK to make
                    this production-ready.
                </div>
            </div>
        </footer>

    </div>

    <script src="app.js"></script>
</body>
</html>