<head>
    <!-- Title -->
    <title>Cart - Fruckr</title>

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/order.css">
</head>
<body>

<!-- Actual page-related content -->
<main>
    <!-- The page title -->
    <h1>Order Success!</h1>

    <!-- Order info section -->
    <section class="order-info">
        <h2>Info</h2>
        <h3>Amount of items: <span id="total-count"><?php echo $orderPosted->getTotalItemCount(); ?></span></h3>
        <h3>Total Price: €<span id="total-price"><?php echo $orderPosted->getFormattedTotalPrice(); ?></span></h3>
    </section>

    <!-- Success message -->
    <div class="alert alert-success">
        <h3>You will get an email at: <?php echo $currUser->getEmail(); ?> once the food is ready!</h3>
        <h3>Thank you for ordering at Fruckr!</h3>
    </div>

    <!-- General information section  -->
    <section class="general-info">
        <h2>Detailed Order Information</h2>
        <p>Order ID: <span id="order-id"><?php echo $orderPosted->getId(); ?></span></p>
        <p>Order Date: <span id="order-date"><?php echo $orderPosted->getFormattedOrderDate(); ?></span></p>
    </section>
</main>

</body>
</html>

<!-- JavaScript -->
<script src="/js/order.js"></script>