<head>
    <!-- Title -->
    <title><?php echo $foodtruck->getName() ?> - Fruckr</title>

    <!-- Stylesheets -->
    <link rel="stylesheet" type="text/css" href="/css/foodtruck.css">
    <link rel="stylesheet" type="text/css" href="/css/foodtruckOwnerPage.css">
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
        <button id="editButton" class="readonly-flex"><img src="../Icons/edit.png" alt="Edit Icon">Edit</button>
        <button id="cancelButton" class="editonly-flex"><img src="../Icons/cancel.png" alt="Cancel Icon">Cancel</button>
        <button id="saveButton" class="editonly-flex"><img src="../Icons/save.png" alt="Save Icon">Save</button>
        <a href="<?php echo current_url() . "/orders"; ?>" class="readonly-flex"><img src="../Icons/orders.png" alt="Orders Icon">Orders</a>
    </div>
</head>
<body>
<!-- Actual page-related content -->
<main>
    <!-- Hero Section containing a carousel of banner images from the foodtruck -->
    <div id="bannerCarousel" class="carousel slide">
        <!-- The foodtruck title-->
        <div class="hero-section">
            <div id="titleErrorBox" class="alert alert-danger" role="alert">
                Something went wrong!
            </div>
            <h1 class="readonly"><?php echo $foodtruck->getName() ?></h1>
            <input type="text" class="editonly" id="foodtruckName" name="foodtruckName" required
                   placeholder="Foodtruck Name..." value="<?php echo $foodtruck->getName() ?>">
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
            if (count($foodtruck->getBannerImages()) > 0) {
                echo '<div class="carousel-item active">';

                echo $foodtruck->getBannerImages()[0]->toHtml('fetchpriority="high" class="d-block" alt="First slide of foodtruck photos" autoplay muted loop');
                echo '</div>';
            }
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

    <div id="generalErrorBox" class="alert alert-danger" role="alert">
        Something went wrong!
    </div>

    <!-- The edit only section for the hero section & profile image -->
    <fieldset class="editonly">
        <legend>Upload Images & Videos</legend>

        <h2>Profile Image</h2>
        <div class="profileImage row">
            <label class="col-sm-2" for="profileImage">Replace</label>
            <input class="col-sm-4" type="file" id="profileImage" name="profileImage">
        </div>

        <div id="profileImageErrorBox" class="alert alert-danger" role="alert">
            Something went wrong!
        </div>

        <hr>

        <h2>Banners</h2>
        <div class="bannerImages">
            <?php
            for ($i = 0; $i < count($foodtruck->getBannerImages()); $i++) {
                $bannerImage = $foodtruck->getBannerImages()[$i];
                echo '<div class="bannerImage row">
                            ' . $bannerImage->toHtml('loading="lazy" class="col-sm-4 offset-sm-2" autoplay muted loop') . '

                            <label class="col-sm-2" for="bannerImageOrder">Order</label>
                            <input class="col-sm-1" type="number" min="0" id="bannerImageOrder" name="bannerImageOrder" value="' . $i . '">

                            <button class="logo-button offset-sm-1" id="removeBannerButton"><img src="../Icons/minus.png" alt="remove icon"></button>
                          </div>';
                echo '<hr class="mobile">';
            }
            ?>
        </div>

        <div class="addBanner row">
            <label class="col-sm-2" for="addBannerImage">Add</label>
            <input class="col-sm-4" type="file" id="addBannerImage" name="addBannerImage">

            <label class="col-sm-2" for="addBannerImageOrder">Order</label>
            <input class="col-sm-1" type="number" id="addBannerImageOrder" name="addBannerImageOrder">

            <button class="logo-button offset-sm-1" id="addBannerButton" disabled><img src="../Icons/add.png" alt="add icon">
            </button>
        </div>

        <div id="bannerErrorBox" class="alert alert-danger" role="alert">
            Something went wrong!
        </div>
    </fieldset>

    <!-- The general information about the foodtruck -->
    <div id="generalInformation">
        <div class="row description-n-open-times-n-information">
            <div class="description-n-open-times col-sm-6">
                <!-- The foodtruck description -->
                <div class="description w-100">
                    <h2>Description</h2>
                    <div id="descriptionErrorBox" class="alert alert-danger" role="alert">
                        Something went wrong!
                    </div>

                    <p id="descriptionRead" class="readonly"><?php echo $foodtruck->getDescription() ?></p>
                    <textarea id="descriptionEdit" type="text" class="editonly w-100"
                              placeholder="Description"><?php echo $foodtruck->getDescription(); ?></textarea>
                </div>
                <!-- The foodtruck OpenTimes -->
                <div class="open-times w-100">
                    <h2>Open on</h2>
                    <!-- The table with the open times -->
                    <table class="readonly">
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

                    <!-- The table with the open times to edit -->
                    <table class="editonly">
                        <tr>
                            <th>Day</th>
                            <th>From</th>
                            <th>To</th>
                            <th></th>
                        </tr>
                        <?php
                        foreach ($foodtruck->getOpenTimes() as $openTime) {
                            echo '<tr>
                                   <td><p>' . $openTime->getDayLong() . '</p></td>
                                   <td><p>' . $openTime->getFromTime()->toLocalizedString('HH:mm') . '</p></td>
                                   <td><p>' . $openTime->getToTime()->toLocalizedString('HH:mm') . '</p></td>
                                   <td><button class="logo-button"><img src="../Icons/minus.png" alt="remove icon"></td>
                                 </tr>';
                        }
                        ?>
                        <tr>
                            <td>
                                <select id="daySelect">
                                    <option value="0">Monday</option>
                                    <option value="1">Tuesday</option>
                                    <option value="2">Wednesday</option>
                                    <option value="3">Thursday</option>
                                    <option value="4">Friday</option>
                                    <option value="5">Saturday</option>
                                    <option value="6">Sunday</option>
                                </select>
                            </td>
                            <td><input id="fromTime" type="time" value="09:00"></td>
                            <td><input id="toTime" type="time" value="17:00"></td>
                            <td>
                                <button class="logo-button"><img src="../Icons/add.png" alt="add icon">
                            </td>
                        </tr>
                        <div id="openOnErrorBox" class="alert alert-danger" role="alert">
                            Something went wrong!
                        </div>
                    </table>
                </div>
            </div>

            <!-- The foodtruck location -->
            <div class="information col-sm-6">
                <h2>Information</h2>
                <div id="informationErrorBox" class="alert alert-danger" role="alert">
                    Something went wrong!
                </div>
                <ul class="readonly">
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
                <ul class="editonly">
                    <li>
                        <img src="../Icons/location.png" alt="address icon">
                        <div class="section">
                            <label for="city">City*</label>
                            <input type="text" id="city" required
                                   value="<?php echo $foodtruck->getCurrAddress()->getCity() ?>"
                                   pattern="[a-zA-Z\-]+" title="A city can only contain letters and dashes">

                            <label for="street">Street*</label>
                            <input type="text" id="street" required
                                   value="<?php echo $foodtruck->getCurrAddress()->getStreet() ?>"
                                   pattern="[a-zA-Z\-]+" title="A street can only contain letters and dashes">

                            <label for="postalCode">Postal Code*</label>
                            <input type="number" id="postalCode" required
                                   value="<?php echo $foodtruck->getCurrAddress()->getPostalCode() ?>">

                            <label for="houseNr">House Nr*</label>
                            <input type="number" id="houseNr" required
                                   value="<?php echo $foodtruck->getCurrAddress()->getHouseNr() ?>">

                            <label for="bus">Bus</label>
                            <input type="text" id="bus" value="<?php echo $foodtruck->getCurrAddress()->getBus() ?>">
                        </div>
                    </li>
                    <li>
                        <img src="../Icons/phone.png" alt="phone icon">
                        <input type="tel" id="phoneNumber" value="<?php echo $foodtruck->getFormattedPhoneNumber() ?>"
                               minlength="9" maxlength="16"
                               pattern="\+?([0-9]+(-|\/|\.| )?){4}[0-9]+"
                               title="A phone number may only contain numbers, +, ., -, / and spaces">
                    </li>
                    <li>
                        <img src="../Icons/mail.png" alt="mail icon">
                        <input type="email" id="email" value="<?php echo $foodtruck->getEmail() ?>">
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
                <div class="extra-info col-sm-6 readonly">
                    <h2>Extra</h2>
                    <p id="extraInfoRead"><?php echo $foodtruck->getExtraInfo() ?></p>
                </div>
            <?php } ?>

            <!-- The foodtruck extra information to edit -->
            <div class="extra-info col-sm-6 editonly">
                <h2>Extra</h2>
                <div id="extraErrorBox" class="alert alert-danger" role="alert">
                    Something went wrong!
                </div>

                <textarea id="extraInfoEdit" type="text"
                          placeholder="Extra Information"><?php echo $foodtruck->getExtraInfo(); ?></textarea>
            </div>

            <!-- Show the future locations if necessary -->
            <?php if (count($foodtruck->getFutureLocations()) > 0) { ?>
                <!-- The foodtruck future locations -->
                <div class="future-locations-readonly col-sm-6 readonly">
                    <h2>Future locations</h2>

                    <!-- The table with the future locations -->
                    <table class="readonly">
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

            <!-- The foodtruck future locations to edit -->
            <div class="future-locations col-sm-6 editonly">
                <h2>Future locations</h2>

                <!-- The table with the future locations to edit -->
                <table class="editonly">
                    <tr>
                        <th>Date</th>
                        <th>Address</th>
                        <th></th>
                    </tr>
                    <?php
                    foreach ($foodtruck->getFutureLocations() as $futureLocation) {
                        echo '<tr>
                                      <td><p>' . $futureLocation->getStartDate() . '</p></td>
                                      <td><p>' . $futureLocation->getAddress()->toString() . '</p></td>
                                      <td><button class="logo-button"><img src="../Icons/minus.png" alt="remove icon"></td>
                                    </tr>';
                    }
                    ?>
                    <tr>
                        <td><input id="startDate" type="date"></td>
                        <td>
                            <div class="section">
                                <label for="city">City*</label>
                                <input type="text" id="city" required
                                       pattern="[a-zA-Z\-]+" title="A city can only contain letters and dashes">

                                <label for="street">Street*</label>
                                <input type="text" id="street" required
                                       pattern="[a-zA-Z\-]+" title="A street can only contain letters and dashes">

                                <label for="postalCode">Postal Code*</label>
                                <input type="number" id="postalCode" required>

                                <label for="houseNr">House Nr*</label>
                                <input type="number" id="houseNr" required>

                                <label for="bus">Bus</label>
                                <input type="text" id="bus">
                            </div>
                        </td>
                        <td>
                            <button class="logo-button"><img src="../Icons/add.png" alt="add icon">
                        </td>
                    </tr>
                    <div id="futureLocationsErrorBox" class="alert alert-danger" role="alert">
                        Something went wrong!
                    </div>
                </table>
            </div>
        </div>

        <div class="tags-n-staff row">
            <div class="tags editonly col-sm-6">
                <h2>Tags</h2>

                <table class="editonly">
                    <tr>
                        <th>Tag</th>
                        <th></th>
                    </tr>
                    <?php
                    foreach ($foodtruck->getTags() as $tag) {
                        echo '<tr>
                          <td><p>' . $tag . '</p></td>
                          <td><button class="logo-button"><img src="../Icons/minus.png" alt="remove icon"></td>
                        </tr>';
                    }
                    ?>
                    <tr>
                        <td><input id="tag" type="text" placeholder="Enter new tag..."></td>
                        <td>
                            <button class="logo-button"><img src="../Icons/add.png" alt="add icon">
                        </td>
                    </tr>
                    <div id="tagsErrorBox" class="alert alert-danger" role="alert">
                        Something went wrong!
                    </div>
                </table>
            </div>

            <div class="staff editonly col-sm-6">
                <h2>Staff</h2>
                <a href="<?php echo current_url(); ?>/staff" class="a-btn">Manage staff</a>
            </div>
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

            <li id="add-food-item">
                <div class="food-item">
                    <div class="image-container">
                        <img loading="lazy" class="d-block" src="../Images/addBanner.png" alt="add image">
                    </div>
                    <div class="info">
                        <h2>Create new food item</h2>
                        <p>Click here to create a new food item</p>
                    </div>
                </div>
            </li>
        </ul>

        <!-- Extra info on a food item (info to be filled by js) -->
        <div class="extra-info-container">
            <!-- Modal -->
            <div class="custom-modal-wrapper">
                <form class="custom-modal" id="extraInfoModal">
                    <button class="logo-button close-button"><img src="../Icons/cancel.png" alt="Close Icon"></button>

                    <!-- Name -->
                    <input type="text" placeholder="Food item name" class="name" id="currFoodItemName" required
                           pattern="[a-zA-Z éçèà\-]+" title="A food item name may only contain letters, spaces and dashes">

                    <!-- Rating -->
                    <h1 class="rating">★★☆☆☆</h1>

                    <hr>

                    <!-- Description -->
                    <h2>Description</h2>
                    <textarea type="text" placeholder="Description text"  class="description" required></textarea>

                    <!-- Ingredients -->
                    <h2>Ingredients</h2>
                    <table class="ingredients">
                        <tbody>
                            <!-- Header -->
                            <tr class="table-header">
                                <th>Ingredient</th>
                                <th></th>
                            </tr>

                            <!-- actual ingredients come here -->

                            <!-- Add ingredient row -->
                            <tr class="add-row">
                                <td>
                                    <input type="text" placeholder="Ingredient" id="addIngredientField"
                                       pattern="[a-zA-Z éçèà\-]+" title="An ingredient may only contain letters, spaces and dashes">
                                </td>
                                <td>
                                    <button class="logo-button"><img src="../Icons/add.png" alt="add icon"></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="alert alert-danger" id="ingredientsError">
                        Something went wrong!
                    </div>

                    <!-- Price -->
                    <h2>Price</h2>
                    <div class="input-group">
                        <div class="input-group-append">
                            <span class="input-group-text">€</span>
                        </div>
                        <input type="number" required min="0" class="price" step="0.01">
                    </div>

                    <hr>

                    <!-- Media -->
                    <h2>Media</h2>

                    <div class="primary-image-container">

                    </div>
                    <label for="primary-image-change-input">Change primary image</label>
                    <input type="file" id="primary-image-change-input">

                    <div class="alert alert-danger" id="primaryImageError">
                        Something went wrong!
                    </div>

                    <table class="media">
                        <tbody>
                            <!-- Header -->
                            <tr class="table-header">
                                <th></th>
                                <th></th>
                            </tr>

                            <!-- actual media come here -->

                            <!-- Add media row -->
                            <tr class="add-row">
                                <td>
                                    <input type="file">
                                </td>
                                <td>
                                    <button class="logo-button"><img src="../Icons/add.png" alt="add icon"></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="alert alert-danger" id="mediaError">
                        Something went wrong!
                    </div>

                    <!-- Submit section -->
                    <button type="submit" id="extraInfoModalSaveButton">Save</button>
                    <img class="loadingCircle" src="../Gifs/loading.gif" alt="loading icon" id="foodItemSaveLoading">

                    <div class="alert alert-danger" id="foodItemSaveError">
                        Something went wrong!
                    </div>

                    <!-- Delete section -->
                    <div id="delete-section">
                        <div class="row">
                            <label for="delete-name">Confirm name to delete</label>
                            <input type="text" placeholder="Food item name" id="delete-name">
                        </div>

                        <button id="extraInfoModalDeleteButton">Delete</button>
                        <img class="loadingCircle" src="../Gifs/loading.gif" alt="loading icon" id="foodItemSaveLoading">
                    </div>

                    <!-- reviews title -->
                    <h2 class="reviews-title">Reviews</h2>
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
                </form>
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
<script src="/js/foodtruck.js"></script>
<script src="/js/customModals.js"></script>
<script src="/js/foodtruckOwnerPageLiveEditing.js"></script>
<script src="/js/foodtruckOwnerPage.js"></script>
<script src="/js/foodtruckOwnerPageMenuEditing.js"></script>
<script src="/js/reviews.js"></script>