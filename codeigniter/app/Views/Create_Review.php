<head>
    <!-- Title -->
    <title>Create Review - Fruckr</title>

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/createReview.css">
    <link rel="stylesheet" href="/css/reviews.css">
</head>
<body>

<!-- Actual page-related content -->
<main>
    <!-- Title -->
    <h1>Create a review</h1>

    <!-- Foodtruck name -->
    <h2>For: <?php echo $foodtruck->getName(); ?></h2>

    <!-- Success message -->
    <div class="alert alert-success" id="successBox">
        Review created successfully!
    </div>

    <!-- Create review form -->
    <form class="create-review" id="createFoodtruckReview">
        <!-- food item selection -->
        <div class="create-review-food-item-selection">
            <label for="reviewItem">Select what you want to review</label>
            <select name="reviewItem" class="create-review-food-item" required>
                <option value="foodtruck" selected>Foodtruck</option>
                <?php
                foreach ($foodItems as $foodItem) {
                    echo '<option value="' . $foodItem->getName() . '">' . $foodItem->getName() . '</option>';
                }
                ?>
            </select>
        </div>

        <!-- rating -->
        <select class="create-review-rating" required>
            <option value="" disabled selected>Rating...</option>
            <option value="1">★☆☆☆☆</option>
            <option value="2">★★☆☆☆</option>
            <option value="3">★★★☆☆</option>
            <option value="4">★★★★☆</option>
            <option value="5">★★★★★</option>
        </select>

        <!-- title -->
        <input class="create-review-title" type="text" placeholder="Title..."
            pattern="[a-zA-Z '.?:!\-0-9]+" title="A title may only contains letters, numbers, ', ., ?, !, : and dashes">

        <!-- content -->
        <textarea class="create-review-content" placeholder="Type your review here..."></textarea>

        <!-- submit -->
        <button class="create-review-submit" type="submit">Leave Review</button>
    </form>

    <!-- Loading -->
    <img class="loadingCircle" id="loadingIcon" src="../Gifs/loading.gif" alt="Loading icon">

    <!-- Error message -->
    <div class="alert alert-danger" id="errorBox">
        Something went wrong!
    </div>
</main>

</body>
</html>

<!-- JavaScript -->
<script src="/js/createReview.js"></script>