<head>
    <!-- Title -->
    <title>Signup - Fruckr</title>

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/signup.css">
</head>
<body>
<!-- Actual page-related content -->
<main>
    <h1 class="title"> Register </h1>
    <hr>

    <!-- signup form -->
    <form id="signupForm" action="/">
        <h2>Account Type</h2>
        <!-- Account Type -->
        <div id="typeSection" class="section">
            <div class="typeSectionRadio">
                <input type="radio" id="customer" name="type" checked>
                <label class="typeRadioLabel" for="customer">Customer</label>
            </div>

            <div" class="typeSectionRadio">
                <input type="radio" id="foodtruckOwner" name="type">
                <label class="typeRadioLabel" for="foodtruckOwner">Foodtruck Owner</label>
            </div>
        </div>

        <h2>Name</h2>
        <!-- Name -->
        <div class="section">
            <label class="col-sm-2" for="firstName">Firstname*</label>
            <input class="col-sm-3" type="text" id="firstName" required
                   pattern="[a-zA-Zéçèà]+" title="A firstname can only contain letters">

            <label class="col-sm-2" for="lastName">Lastname*</label>
            <input class="col-sm-3" type="text" id="lastName" required
                   pattern="[a-zA-Z éçèà]+" title="A lastname can only contain letters and spaces">
        </div>

        <h2>Contact</h2>
        <!-- Email -->
        <div class="section">
            <label class="col-sm-2" for="email">E-mail*</label>
            <input class="col-sm-8" type="email" id="email">
        </div>

        <!-- PhoneNumber -->
        <div class="section">
            <label class="col-sm-2" for="phoneNumber">Phone Number</label>
            <input class="col-sm-3" type="tel" id="phoneNumber" minlength="9" maxlength="16"
                   pattern="\+?([0-9]+(-|\/|\.| )?){4}[0-9]+" title="A phone number may only contain numbers, +, ., -, / and spaces">
        </div>

        <h2>Address</h2>
        <!-- Address -->
        <div class="section">
            <label class="col-sm-2" for="city">City*</label>
            <input class="col-sm-3" type="text" id="city" required
                   pattern="[a-zA-Z\-]+" title="A city can only contain letters and dashes">

            <label class="col-sm-2" for="street">Street*</label>
            <input class="col-sm-3" type="text" id="street" required
                   pattern="[a-zA-Z\-]+" title="A street can only contain letters and dashes">

            <label class="col-sm-2" for="postalCode">Postal Code*</label>
            <input class="col-sm-1" type="number" id="postalCode" required>

            <label class="col-sm-1" for="houseNr">House Nr*</label>
            <input class="col-sm-1" type="number" id="houseNr" required>

            <label class="col-sm-2" for="bus">Bus</label>
            <input class="col-sm-1" type="text" id="bus">
        </div>

        <h2>Password</h2>
        <!-- Password -->
        <div class="section">
            <label class="col-sm-2" for="password">Password*</label>
            <input class="col-sm-3" type="password" id="password" required>

            <label class="col-sm-2" for="confirmPassword">Confirm Password*</label>
            <input class="col-sm-3" type="password" id="confirmPassword" required>
        </div>

        <!-- Loading icon -->
        <img class="loadingCircle" id="signupLoading" src="../Gifs/loading.gif" alt="Loading icon">

        <!-- Error -->
        <div id="errorBox" class="alert alert-danger" role="alert">
            Something went wrong!
        </div>

        <!-- Success -->
        <div id="successBox" class="alert alert-success" role="alert">
            Your profile has created!
        </div>

        <!-- Submit -->
        <button type="submit">Register</button>

        <!-- Link (encase the user doesn't have an account) -->
        <a id="AlreadyAccountLink" href="login">Already have an account? Login here.</a>
    </form>
</main>

</body>
</html>

<!-- JavaScript -->
<script src="/js/signup.js"></script>