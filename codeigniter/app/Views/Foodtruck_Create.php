<head>
    <!-- Title -->
    <title>Create a foodtruck - Fruckr</title>

    <!-- Stylesheets -->
    <link rel="stylesheet" type="text/css" href="/css/foodtruck.css">
    <link rel="stylesheet" type="text/css" href="/css/foodtruckOwnerPage.css">
    <link rel="stylesheet" type="text/css" href="/css/foodtruckCreatePage.css">

    <!-- Profile Image -->
    <img class="profile-image" alt="Profile image of foodtruck" src="../Images/missingImage.png">

    <!-- Toolbar -->
    <div class="toolbar">
        <button id="createButton" class="editonly-flex"><img src="../Icons/save.png" alt="Create Icon">Create</button>
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
            <input type="text" class="editonly" id="foodtruckName" name="foodtruckName" required placeholder="Foodtruck Name...">
        </div>
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

                    <textarea id="descriptionEdit" type="text" class="editonly w-100" placeholder="Description"></textarea>
                </div>
                <!-- The foodtruck OpenTimes -->
                <div class="open-times w-100">
                    <h2>Open on</h2>

                    <!-- The table with the open times to edit -->
                    <table class="editonly">
                        <tr>
                            <th>Day</th>
                            <th>From</th>
                            <th>To</th>
                            <th></th>
                        </tr>
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
                <ul class="editonly">
                    <li>
                        <img src="../Icons/location.png" alt="address icon">
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
                    </li>
                    <li>
                        <img src="../Icons/phone.png" alt="phone icon">
                        <input type="tel" id="phoneNumber"
                               minlength="9" maxlength="16"
                               pattern="\+?([0-9]+(-|\/|\.| )?){4}[0-9]+"
                               title="A phone number may only contain numbers, +, ., -, / and spaces">
                    </li>
                    <li>
                        <img src="../Icons/mail.png" alt="mail icon">
                        <input type="email" id="email">
                    </li>
                    <li>
                        <img src="../Icons/user.png" alt="owner icon">
                        <?php echo $ownerName ?>
                    </li>
                </ul>
            </div>
        </div>

        <div class="extra-info-n-future-locations row">
            <!-- The foodtruck extra information to edit -->
            <div class="extra-info col-sm-6 editonly">
                <h2>Extra</h2>
                <div id="extraErrorBox" class="alert alert-danger" role="alert">
                    Something went wrong!
                </div>

                <textarea id="extraInfoEdit" type="text" placeholder="Extra Information"></textarea>
            </div>

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

        <div class="tags editonly col-sm-6">
            <h2>Tags</h2>

            <table class="editonly">
                <tr>
                    <th>Tag</th>
                    <th></th>
                </tr>
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
    </div>
</main>

</body>
</html>

<!-- JavaScript -->
<script src="/js/foodtruck.js"></script>
<script src="/js/foodtruckOwnerPageLiveEditing.js"></script>
<script src="/js/foodtruckOwnerPage.js"></script>
<script src="/js/foodtruckCreatePage.js"></script>