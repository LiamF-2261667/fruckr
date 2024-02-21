<head>
    <!-- Title -->
    <title><?php echo $foodtruck->getName() ?> - Fruckr</title>

    <!-- Stylesheets -->
    <link rel="stylesheet" type="text/css" href="/css/foodtruck.css">
    <link rel="stylesheet" type="text/css" href="/css/reviews.css">

    <!-- Profile Image -->
    <?php
    echo $foodtruck->getProfileImage()->toHtml('class="profile-image" alt="Profile image of foodtruck"');
    ?>

    <!-- Open Chats -->
    <div class="chat-container">
        <a href="<?php echo current_url(); ?>/chats" class="logo-button"><img src="../Icons/chat.png" alt="Chat Icon"></a>
    </div>

    <!-- Toolbar -->
    <div class="toolbar">
        <a href="<?php echo current_url() . "/orders"; ?>"><img src="../Icons/orders.png" alt="Orders Icon">Orders</a>
    </div>
</head>
<body>
<!-- Actual page-related content -->
<main>
    <!-- Hero Section containing a carousel of banner images from the foodtruck -->
    <div id="bannerCarousel" class="carousel slide">
        <!-- The foodtruck title-->
        <div class="hero-section">
            <h1><?php echo $foodtruck->getName() ?></h1>
            <h2><?php echo $foodtruck->getRatingString() ?></h2>
        </div>

        <!-- The indicators for the carousel, only show if is more than 1 picture -->
        <div class="carousel-indicators">
            <?php
            if (count($foodtruck->getBannerImages()) > 1) {
                echo '<button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>';
                for ($i = 1; $i < count($foodtruck->getBannerImages()); $i++)
                    echo '<button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="' . $i . '" aria-label="Slide ' . $i . '"></button>';
            }
            ?>
        </div>

        <!-- The pictures inside the carousel-->
        <div class="carousel-inner">
            <?php
            echo '<div class="carousel-item active">';

            echo $foodtruck->getBannerImages()[0]->toHtml('fetchpriority="high" class="d-block" alt="First slide of foodtruck photos" autoplay muted loop');
            echo '</div>';
            for ($i = 1; $i < count($foodtruck->getBannerImages()); $i++)
                echo '<div class="carousel-item">'
                    . $foodtruck->getBannerImages()[$i]->toHtml('loading="lazy" class="d-block" alt="' . ($i + 1) . 'th slide of foodtruck photos" autoplay muted loop') .
                    '</div>';
            ?>
        </div>

        <!-- Carousel navigation buttons if there is more than 1 picture -->
        <?php if (count($foodtruck->getBannerImages()) > 1) { ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        <?php } ?>
    </div>

    <!-- The general information about the foodtruck -->
    <div id="generalInformation">
        <div class="row">
            <div class="description-n-open-times col-sm-6">
                <!-- The foodtruck description -->
                <div class="description w-100">
                    <h2>Description</h2>
                    <p><?php echo $foodtruck->getDescription() ?></p>
                </div>
                <!-- The foodtruck OpenTimes -->
                <div class="open-times w-100">
                    <h2>Open on</h2>
                    <table>
                        <tr>
                            <th>Day</th>
                            <th>From</th>
                            <th>To</th>
                        </tr>
                        <?php
                        foreach ($foodtruck->getOpenTimes() as $openTime) {
                            echo '<tr>
                               <td>' . $openTime->getDayLong() . '</td>
                               <td>' . $openTime->getFromTime()->toLocalizedString('HH:mm') . '</td>
                               <td>' . $openTime->getToTime()->toLocalizedString('HH:mm') . '</td>
                             </tr>';
                        }
                        ?>
                    </table>
                </div>
            </div>

            <!-- The foodtruck location -->
            <div class="information col-sm-6">
                <h2>Information</h2>
                <ul>
                    <li>
                        <img src="../Icons/location.png" alt="address icon">
                        <a href="<?php echo $foodtruck->getCurrAddress()->toGoogleLink() ?>" target="_blank">
                            <?php echo $foodtruck->getCurrAddress()->toString() ?>
                        </a>
                    </li>
                    <li>
                        <img src="../Icons/phone.png" alt="phone icon">
                        <a href="tel:<?php echo $foodtruck->getPhoneNumber() ?>">
                            <?php echo $foodtruck->getFormattedPhoneNumber() ?>
                        </a>
                    </li>
                    <li>
                        <img src="../Icons/mail.png" alt="mail icon">
                        <a href="mailto:<?php echo $foodtruck->getEmail() ?>">
                            <?php echo $foodtruck->getEmail() ?>
                        </a>
                    </li>
                    <li>
                        <img src="../Icons/user.png" alt="owner icon">
                        <?php echo $foodtruck->getOwner()->getFullName() ?>
                    </li>
                </ul>
            </div>
        </div>

        <div class="extra-info-n-future-locations row">
            <!-- Show the foodtruck extra information if necessary -->
            <?php if ($foodtruck->getExtraInfo() != null) { ?>
                <!-- The foodtruck extra information -->
                <div class="extra-info col-sm-6">
                    <h2>Extra</h2>
                    <p><?php echo $foodtruck->getExtraInfo() ?></p>
                </div>
            <?php } ?>

            <!-- Show the future locations if necessary -->
            <?php if (count($foodtruck->getFutureLocations()) > 0) { ?>
                <!-- The foodtruck future locations -->
                <div class="future-locations col-sm-6">
                    <h2>Future locations</h2>
                    <table>
                        <tr>
                            <th>Date</th>
                            <th>Address</th>
                        </tr>
                        <?php
                        foreach ($foodtruck->getFutureLocations() as $futureLocation) {
                            echo '<tr>
                                  <td>' . $futureLocation->getStartDate() . '</td>
                                  <td>' . $futureLocation->getAddress()->toString() . '</td>
                                </tr>';
                        }
                        ?>
                    </table>
                </div>
            <?php } ?>
        </div>
    </div>

    <hr>

    <!-- The foodtruck menu -->
    <div id="menu">
        <h2>Menu</h2>
        <ul class="menu-items">
            <?php
            foreach ($foodtruck->getFoodItems() as $foodItem)
                echo $foodItem->toHtml();
            ?>
        </ul>

        <!-- Extra info on a food item (info to be filled by js) -->
        <div class="extra-info-container">
            <!-- Modal -->
            <div class="custom-modal-wrapper">
                <div class="custom-modal" id="extraInfoModal">
                    <button class="logo-button close-button"><img src="../Icons/cancel.png" alt="Close Icon"></button>

                    <h1 class="name" id="currFoodItemName">Food Item Name</h1>
                    <h1 class="rating">★★☆☆☆</h1>

                    <hr>

                    <h2>Description</h2>
                    <p class="description">description</p>

                    <h2>Ingredients</h2>
                    <p class="ingredients">ingredients</p>

                    <hr>

                    <h2>Media</h2>

                    <div class="primary-image-container">

                    </div>

                    <div class="media">

                    </div>

                    <!-- reviews title -->
                    <h2>Reviews</h2>
                    <button id="loadFoodItemReviews">Load Reviews</button>

                    <!-- reviews list -->
                    <ul class="review-list" id="foodItemReviewsList">

                    </ul>

                    <!-- reviews loading icon -->
                    <img class="loadingCircle" id="fooditemReviewsLoading" src="../Gifs/loading.gif" alt="Loading icon">

                    <!-- reviews error box -->
                    <div class="alert alert-danger" id="fooditemReviewsErrorBox">
                        Something went wrong!
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr>

    <!-- The foodtruck reviews -->
    <div id="foodtruckReviews">
        <!-- reviews title -->
        <h2>Reviews</h2>

        <!-- reviews list -->
        <ul class="review-list">

        </ul>

        <!-- reviews loading icon -->
        <img class="loadingCircle" id="foodtruckReviewsLoading" src="../Gifs/loading.gif" alt="Loading icon">

        <!-- reviews error box -->
        <div class="alert alert-danger" id="foodtruckReviewsErrorBox">
            Something went wrong!
        </div>
    </div>
</main>

</body>
</html>

<!-- JavaScript -->
<script src="/js/customModals.js"></script>
<script src="/js/foodtruck.js"></script>
<script src="/js/reviews.js"></script>