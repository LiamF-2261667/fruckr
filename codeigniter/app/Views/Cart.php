<head>
    <!-- Title -->
    <title>Cart - Fruckr</title>

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/cart.css">
</head>
<body>

<!-- Actual page-related content -->
<main>
    <!-- The page title -->
    <h1>Shopping Cart</h1>

    <!-- The cart order section -->
    <section class="order">
        <h1>Order</h1>
        <h3>Amount of items: <span id="total-count"><?php echo $cart->getTotalItemCount(); ?></span></h3>
        <h3>Total Price: €<span id="total-price"><?php echo $cart->getFormattedTotalPriceSum(); ?></span></h3>
        <a href="/order" class="a-btn">Place Order</a>
    </section>

    <hr>

    <!-- Actual cart items -->
    <ul class="cart-items">
        <?php
        // If the cart is empty, show a message
        if ($cart->getItems() == null || count($cart->getItems()) == 0)
            echo '<li class="cart-item">Your cart is empty</li>';

        // Load all the cart items
        foreach ($cart->getItems() as $cartItem)
            echo '<li class="cart-item">' . $cartItem->toHtml() . "</li>";
        ?>
    </ul>
</main>

</body>
</html>

<!-- JavaScript -->
<script src="/js/cart.js"></script>