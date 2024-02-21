<head>
    <!-- Title -->
    <title><?php echo $currUser->getFirstName(); ?> - Fruckr</title>

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/profile.css">
</head>
<body>
<!-- Actual page-related content -->
<main>
    <h1 class="title"> Profile </h1>
    <hr>

    <!-- save form -->
    <form id="saveForm" action="/">
        <h2>Name</h2>
        <!-- Name -->
        <div class="section">
            <label class="col-sm-2" for="firstName">Firstname*</label>
            <input class="col-sm-3" type="text" id="firstName" required value="<?php echo $currUser->getFirstName(); ?>"
                   pattern="[a-zA-Zéçèà]+" title="A firstname can only contain letters">

            <label class="col-sm-2" for="lastName">Lastname*</label>
            <input class="col-sm-3" type="text" id="lastName" required value="<?php echo $currUser->getLastName(); ?>"
                   pattern="[a-zA-Z éçèà]+" title="A lastname can only contain letters and spaces">
        </div>

        <h2>Contact</h2>
        <!-- Email -->
        <div class="section">
            <label class="col-sm-2" for="email">E-mail*</label>
            <input class="col-sm-8" type="email" id="email" readonly value="<?php echo $currUser->getEmail(); ?>">
        </div>

        <!-- PhoneNumber -->
        <div class="section">
            <label class="col-sm-2" for="phoneNumber">Phone Number</label>
            <input class="col-sm-3" type="tel" id="phoneNumber" value="<?php echo $currUser->getFormattedPhoneNumber(); ?>" minlength="9" maxlength="16"
                   pattern="\+?([0-9]+(-|\/|\.| )?){4}[0-9]+" title="A phone number may only contain numbers, +, ., -, / and spaces">
        </div>

        <h2>Address</h2>
        <!-- Address -->
        <div class="section">
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

        <h2>Password</h2>
        <!-- Password -->
        <div class="section">
            <label class="col-sm-2" for="password">Password*</label>
            <input class="col-sm-8" type="password" id="password" required>
        </div>

        <!-- Loading icon -->
        <img class="loadingCircle" id="saveLoading" src="../Gifs/loading.gif" alt="Loading icon">

        <!-- Error -->
        <div id="errorBox" class="alert alert-danger" role="alert">
            Something went wrong!
        </div>

        <!-- Success -->
        <div id="successBox" class="alert alert-success" role="alert">
            Your profile has been updated!
        </div>

        <!-- Submit -->
        <button type="submit">Save</button>
    </form>
</main>

</body>
</html>

<!-- JavaScript -->
<script src="/js/profile.js"></script>