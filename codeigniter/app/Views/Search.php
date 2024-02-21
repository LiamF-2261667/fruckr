<!-- Top of page content -->
<head>
    <!-- Title -->
    <title><?php echo $searchData; ?> - Search Fruckr</title>

    <link rel="stylesheet" type="text/css" href="/css/search.css">

    <!-- Searchbar -->
    <form action="" id="searchForm">
        <!-- Actual search bar -->
        <div class="searchBar">
            <input class="search" type="search" name="searchBar" id="searchBar" autocomplete="on" autofocus
                   placeholder="Search something..." required value="<?php echo $searchData; ?>"
                   pattern="[a-zA-Z éçèà\-0-9]+" title="A search can only contain letters, spaces, numbers and dashes">
            <button class="search" type="submit"><img src="../Icons/search.png" alt="Searchbutton"></button>
        </div>

        <!-- Error -->
        <div id="errorBox" class="alert alert-danger" role="alert" <?php if (isset($error)) echo 'style="display: block;"'; ?>>
            <?php
            if (isset($error))
                echo $error;
            else
                echo 'Something went wrong!';
            ?>
        </div>

        <!-- Loading Icon -->
        <img class="loadingCircle" id="searchLoading" src="../Gifs/loading.gif" alt="Loading icon">
    </form>
</head>

<body>
<!-- Actual page-related content -->
<main>
    <!-- Filters -->
    <div class="filters">
        <h1>Filters</h1>

        <div class="city-filter-wrapper filter-wrapper">
            <h2>City</h2>

            <div class="city-filter">
            </div>

            <button id="city-filter-show-all">Show all ⇩</button>
        </div>

        <div class="tag-filter-wrapper filter-wrapper">
            <h2>Tags</h2>

            <div class="tag-filter">
            </div>

            <button id="tag-filter-show-all">Show all ⇩</button>
        </div>

        <div class="rating-filter-wrapper filter-wrapper">
            <h2>Rating</h2>

            <div class="rating-filter">
            </div>

            <button id="rating-filter-show-all">Show all ⇩</button>
        </div>
    </div>

    <!-- Search results -->
    <div class="searchResults">
        <h1>Search Results</h1>

        <div class="foodtruckCarousel">
            <ul>
                <?php
                if ($searchResults === null || count($searchResults) == 0) {
                    echo '<li><p>No results found</p></li>';
                }
                else {
                    foreach ($searchResults as $result) {
                        echo $result;
                    }
                }
                ?>
            </ul>
        </div>
    </div>
</main>
</body>

<script src="/js/search.js"></script>
