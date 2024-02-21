<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Setup for the webpage -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Stylesheets -->
        <!-- Bootstrap -->
        <link rel="stylesheet" type="text/css" href="/css/bootstrap/bootstrap.css">

        <!-- General Stylesheets -->
        <link rel="stylesheet" type="text/css" href="/css/variables.css">

            <!-- Colors -->
            <?php
            if ($currUser != null && $currUser->isFoodtruckWorker())
                echo '<link id="colors" rel="stylesheet" type="text/css" href="/css/foodtruckWorkerTheme.css">';
            else
                echo '<link id="colors" rel="stylesheet" type="text/css" href="/css/consumerTheme.css">';
            ?>

        <link rel="stylesheet" type="text/css" href="/css/basicStyling.css">

    <!-- Navigation bar -->
    <nav id="mainNav">
        <!-- Horizontal bar (for every screen, except for the "non-mobile" tags) -->
        <ul class="horizontal">
            <li class="left">
                <a class="smallLogo" href="/homepage">
                    <figure>Fruckr</figure>
                </a>
            </li>

            <li class="mobile"><img src="<?php echo base_url("images/hamburger.png") ?>" alt="hamburger Menu" id="mainNavHamburger"></li>

            <!-- Conditional login buttons -->
            <?php
            if ($currUser == null) {
                echo '<li class="non-mobile"><a href="/signup"><em>Signup</em></a></li>';
                echo '<li class="non-mobile"><a href="/login">Login</a></li>';
            } else {
                echo '<li class="non-mobile"><a href="/profile"><em>' . $currUser->getFullName() . '</em></a></li>';
                echo '<li class="non-mobile"><a href="/logout">Logout</a></li>';
                if ($currUser->isFoodtruckWorker())
                    echo '<li class="non-mobile"><a href="/my-foodtrucks">My Foodtrucks</a></li>';
                echo '<li class="non-mobile"><a href="/chats">Chats</a></li>';
            }
            ?>

            <li class="non-mobile"><a href="/cart">Cart</a></li>
        </ul>

        <!-- Vertical for mainly vertical screens (eg. phones, ...) -->
        <ul class="vertical" id="mainNavVerticalList">

            <!-- Conditional login buttons -->
            <?php
            if ($currUser == null) {
                echo '<li class="non-mobile"><a href="/signup"><em>Signup</em></a></li>';
                echo '<li class="non-mobile"><a href="/login">Login</a></li>';
            } else {
                echo '<li class="non-mobile"><a href="/profile"><em>' . $currUser->getFullName() . '</em></a></li>';
                if ($currUser->isFoodtruckWorker())
                    echo '<li class="non-mobile"><a href="/my-foodtrucks">My Foodtrucks</a></li>';
                echo '<li class="non-mobile"><a href="/logout">Logout</a></li>';
                echo '<li class="non-mobile"><a href="/chats">Chats</a></li>';
            }
            ?>

            <li class="non-mobile"><a href="/cart">Cart</a></li>
            <li class="non-mobile"><a href="/chats">Chats</a></li>
        </ul>
    </nav>

    <!-- JavaScript -->
    <script src="/js/bootstrap/bootstrap.js"></script>
    <script src="/js/basicJs.js"></script>

    <!-- A spacer for the navBar and page content -->
    <div class="navSpacer"></div>
</head>