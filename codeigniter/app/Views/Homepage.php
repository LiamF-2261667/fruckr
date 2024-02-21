<!-- Top of page content -->
<head>
    <!-- Title -->
    <title>Fruckr</title>

    <!-- Big logo -->
    <figure class="logo">
        <em>Fruckr</em>
        The Foodtruck Finder
    </figure>

    <!-- Searchbar -->
    <form action="/search">
        <div class="searchBar">
            <input class="search" type="search" name="searchBar" autocomplete="on" autofocus
                   placeholder="Search something..." required
                   pattern="[a-zA-Z éçèà\-0-9]+" title="A search can only contain letters, spaces, numbers and dashes">
            <button class="search" type="submit"><img src="../Icons/search.png" alt="Searchbutton"></button>
        </div>

    </form>
</head>

<body>
<!-- Actual page-related content -->
<main>
    <!-- A carousel of all nearby foodtrucks -->
    <?php if (isset($nearYou) && count($nearYou) > 0) { ?>
        <div class="foodtruckCarousel">
            <h1>Foodtrucks near you</h1>
            <ul>
                <!-- List containing all the foodtrucks and it's relevant information -->
                <?php
                    foreach ($nearYou as $foodtruck) {
                        echo $foodtruck;
                    }

                    echo '<a href="search?searchBar=' . $currUser->getAddress()->getCity() . '">
                        <li>
                            <div class="title">
                                <h2>View all</h2>
                            </div>
                            <div class="profileImageContainer">
                                <img src="../Images/nextBanner.png" alt="View all image">
                            </div>
                        </li>
                    </a>'
                ?>
            </ul>
        </div>
    <?php } ?>

    <!-- A carousel of all recommended foodtrucks -->
    <div class="foodtruckCarousel">
        <h1>Recommended foodtrucks</h1>
        <ul>
            <!-- List containing all the foodtrucks and it's relevant information -->
            <?php foreach ($recommended as $foodtruck) {
                echo $foodtruck;
            } ?>
        </ul>
    </div>
</main>
</body>