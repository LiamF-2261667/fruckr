<head>
    <!-- Title -->
    <title>Login - Fruckr</title>

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/login.css">
</head>
<body>
<!-- Actual page-related content -->
<main>
    <div id="contentSection">
        <!-- Big logo -->
        <figure class="logo">
            <em>Fruckr</em>
            The Foodtruck Finder
        </figure>

        <!-- Login form -->
        <img class="loadingCircle" id="loginLoading" src="../Gifs/loading.gif" alt="Loading icon">
        
        <form id="loginForm" action="<?php echo \Config\Services::session()->get("prevPage"); ?>">
            <!-- Error -->
            <div id="errorBox" class="alert alert-danger" role="alert">
                Something went wrong!
            </div>

            <!-- Email -->
            <div id="emailSection">
                <h1>E-mail</h1>
                <input type="email" id="email" placeholder="example@gmail.com" required>
            </div>

            <!-- Password -->
            <div id="passwordSection">
                <h1>Password</h1>
                <input type="password" id="password" placeholder="" required>
            </div>

            <!-- Submit -->
            <button type="submit">Login</button>

            <!-- Link (encase the user doesn't have an account) -->
            <a id="NoAccountLink" href="signup">Don't have an account? Create one here.</a>
        </form>
    </div>
</main>

</body>
</html>

<!-- JavaScript -->
<script src="/js/login.js"></script>