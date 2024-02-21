<head>
    <!-- Title -->
    <title><?php echo $foodtruck->getName() ?> - Orders Fruckr</title>

    <!-- Stylesheets -->
    <link rel="stylesheet" type="text/css" href="/css/foodtruckOrders.css">

    <!-- Profile Image -->
    <?php
    echo $foodtruck->getProfileImage()->toHtml('class="profile-image" alt="Profile image of foodtruck"');
    ?>
</head>


<body>
<main>
    <!-- Page title -->
    <h1 class="title">Orders - <?php echo $foodtruck->getName(); ?></h1>

    <!-- Loading circle -->
    <img class="loadingCircle" id="loadingIcon" src="../../Gifs/loading.gif" alt="Loading icon">

    <!-- Error box -->
    <div id="errorBox" class="alert alert-danger">Something went wrong!</div>
    
    <hr>

    <!-- Waiting for receiving orders -->
    <section id="toBeReceived">
        <h2> Orders waiting for pickup </h2>
        <ul class="orders">
            <?php
            foreach ($foodtruck->getOrders() as $order) {
                if ($order->isReady())
                    echo '<li class="order">
                            ' . $order->toShortHtml() . '
                            <button class="received-button">Has been picked up</button>
                          </li>';
            }
            ?>
        </ul>
    </section>

    <hr>

    <!-- Unhandled orders -->
    <section id="toBeReady">
        <h2> Orders to be made </h2>
        <ul class="orders">
            <?php
            foreach ($foodtruck->getOrders() as $order) {
                if (!$order->isReady())
                    echo '<li class="order">
                            ' . $order->toShortHtml() . '
                            <button class="ready-button">Set ready</button>
                          </li>';
            }
            ?>
        </ul>
    </section>
</main>
</body>
</html>

<!-- JavaScript -->
<script src="/js/foodtruckOrders.js"></script>