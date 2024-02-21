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
    <h1>Order</h1>

    <!-- Order info section -->
    <section class="order-info">
        <h2>Info</h2>
        <h3>Amount of items: <span id="total-count"><?php echo $cart->getTotalItemCount(); ?></span></h3>
        <h3>Total Price: €<span id="total-price"><?php echo $cart->getFormattedTotalPriceSum(); ?></span></h3>
    </section>

    <!-- Order params section  -->
    <form id="orderForm" class="order-params">
        <h2>Payment</h2>
        <div class="section payment">
            <label class="col-sm-2" for="cardNumber">Card Number*</label>
            <input class="col-sm-3" type="text" id="cardNumber" required placeholder="xxxx xxxx xxxx xxxx"
                   pattern="[a-zA-Z]{0,2}[0-9\- ]+[a-zA-Z]?\d?" minlength="16" maxlength="24"
                   title="A card number can only numbers, spaces and dashes and some letters">

            <label class="col-sm-2" for="expirationDate">Expiration Date*</label>
            <select class="col-sm-2" name='expireMM' id='expireMM' required>
                <option value=''>Month</option>
                <option value='01'>01 - January</option>
                <option value='02'>02 - February</option>
                <option value='03'>03 - March</option>
                <option value='04'>04 - April</option>
                <option value='05'>05 - May</option>
                <option value='06'>06 - June</option>
                <option value='07'>07 - July</option>
                <option value='08'>08 - August</option>
                <option value='09'>09 - September</option>
                <option value='10'>10 - October</option>
                <option value='11'>11 - November</option>
                <option value='12'>12 - December</option>
            </select>
            <select class="col-sm-1" name='expireYY' id='expireYY' required>
                <option value=''>Year</option>
                <?php
                for ($i = 0; $i < 5; $i++) {
                    $year = date('Y') + $i;
                    $yearIndex = date('y') + $i;
                    echo "<option value='$yearIndex'>$year</option>";
                }
                ?>
            </select>

            <label class="col-sm-2" for="cardHolder">Card Holder*</label>
            <input class="col-sm-3" type="text" id="cardHolder" required placeholder="John Doe"
                   pattern="[a-zA-Z\- ]+" title="A card holder can only contain letters, spaces and dashes">
        </div>

        <h2>Address</h2>
        <!-- Address -->
        <div class="section address">
            <label class="col-sm-2" for="city">City*</label>
            <input class="col-sm-3" type="text" id="city" required value="<?php echo $currUser->getAddress()->getCity(); ?>"
                   pattern="[a-zA-Z\-]+" title="A city can only contain letters and dashes">

            <label class="col-sm-2" for="street">Street*</label>
            <input class="col-sm-3" type="text" id="street" required value="<?php echo $currUser->getAddress()->getStreet(); ?>"
                   pattern="[a-zA-Z\-]+" title="A street can only contain letters and dashes">

            <label class="col-sm-2" for="postalCode">Postal Code*</label>
            <input class="col-sm-1" type="number" id="postalCode" required value="<?php echo $currUser->getAddress()->getPostalCode(); ?>">

            <label class="col-sm-1" for="houseNr">House Nr*</label>
            <input class="col-sm-1" type="number" id="houseNr" required value="<?php echo $currUser->getAddress()->getHouseNr(); ?>">

            <label class="col-sm-2" for="bus">Bus</label>
            <input class="col-sm-1" type="text" id="bus" value="<?php echo $currUser->getAddress()->getBus(); ?>">
        </div>

        <!-- Error box -->
        <div class="alert alert-danger" id="orderErrorBox">Something went wrong!</div>

        <!-- Loading circle -->
        <img class="loadingCircle" id="orderLoading" src="../Gifs/loading.gif" alt="Loading icon">

        <!-- Pay button -->
        <button type="submit" id="pay-button">Pay</button>
    </form>
</main>

</body>
</html>

<!-- JavaScript -->
<script src="/js/order.js"></script>