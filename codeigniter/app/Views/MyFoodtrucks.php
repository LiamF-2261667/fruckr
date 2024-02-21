<!-- Top of page content -->
<head>
    <!-- Title -->
    <title>My Foodtrucks - Fruckr</title>
</head>

<body>
<!-- Actual page-related content -->
<main>
    <!-- A carousel of all owner foodtrucks -->
    <?php
        if ($currUser->isFoodtruckOwner()) {
            ?>
            <div class="foodtruckCarousel">
                <h1>Owned foodtrucks</h1>
                <ul>
                    <!-- List containing all the foodtrucks and it's relevant information -->
                    <?php
                        foreach ($currUser->getOwner()->getOwnedFoodtrucks() as $foodtruck) {
                            echo $foodtruck->getThumbnailString();
                        }
                    ?>
                    <!-- An option to create new foodtrucks -->
                    <a href="foodtruck/create">
                        <li>
                            <!-- Example foodtruck -->
                            <div class="title">
                                <h2>Create new foodtruck</h2>
                            </div>
                            <div class="profileImageContainer">
                                <img alt="Plus image" src="../Images/addBanner.png">
                            </div>
                        </li>
                    </a>
                </ul>
            </div>
        <?php }
    ?>

    <!-- A carousel of all working foodtrucks -->
    <?php
    if ($currUser->isFoodtruckWorker() && count($currUser->getWorker()->getFoodtrucks()) > 0) {
        ?>
        <div class="foodtruckCarousel">
            <h1>Working in foodtrucks</h1>
            <ul>
                <!-- List containing all the foodtrucks and it's relevant information -->
                <?php
                foreach ($currUser->getWorker()->getFoodtrucks() as $foodtruck) {
                    echo $foodtruck->getThumbnailString();
                }
                ?>
            </ul>
        </div>
    <?php }
    ?>

</main>
</body>
</html>