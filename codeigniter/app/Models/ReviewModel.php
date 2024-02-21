<?php

namespace App\Models;

use App\Database\DatabaseHandler;
use App\Exceptions\NoDataException;
use App\Exceptions\ReviewException;
use DateTime;

class ReviewModel
{
    /* Attributes */
    private ?int $id = null;
    private ?UserModel $user = null;
    private ?FoodtruckModel $foodtruck = null;
    private ?int $rating = null;
    private ?DateTime $date = null;

    private ?string $title = null;
    private ?string $content = null;
    private ?FoodItemModel $foodItem = null;

    /* Initializers */
    /**
     * Get a review by the given id
     * @param int $id The id of the review
     * @return ReviewModel The review with the given id
     * @throws NoDataException If no review was found with the given id
     * @throws ReviewException If the review failed to load
     */
    public static function getById(int $id): ReviewModel
    {
        $review = new ReviewModel();
        $review->loadById($id);
        return $review;
    }

    /**
     * Get all the reviews for a given foodtruck
     * @param FoodtruckModel $foodtruck The foodtruck to get the reviews for
     * @return array The reviews for the given foodtruck
     * @throws ReviewException If a review failed to load
     */
    public static function getAllFromFoodtruck(FoodtruckModel $foodtruck): array
    {
        $results = DatabaseHandler::getInstance()->query(self::$Q_GET_ALL_FOR_FOODTRUCK, [$foodtruck->getId()]);
        $reviews = [];
        foreach ($results as $result) {
            $review = new ReviewModel();
            $review->loadByDBResult($result);
            $reviews[] = $review;
        }
        return $reviews;
    }

    /**
     * Get all the reviews for a given food item
     * @param FoodtruckModel $foodtruck The foodtruck to get the reviews for
     * @param FoodItemModel $foodItem The food item to get the reviews for
     * @return array The reviews for the given food item
     * @throws ReviewException If a review failed to load
     */
    public static function getAllForFoodItem(FoodtruckModel $foodtruck, FoodItemModel $foodItem): array
    {
        $results = DatabaseHandler::getInstance()->query(self::$Q_GET_ALL_FOR_FOOD_ITEM, [$foodtruck->getId(), $foodItem->getName()]);
        $reviews = [];
        foreach ($results as $result) {
            $review = new ReviewModel();
            $review->loadByDBResult($result);
            $reviews[] = $review;
        }
        return $reviews;
    }

    /**
     * Get a review by the given parameters
     * @param UserModel $user The user that made the review
     * @param FoodtruckModel $foodtruck The foodtruck that the review is for
     * @param DateTime $date The date of the review
     * @return ReviewModel The review with the given parameters
     * @throws NoDataException If no review was found with the given parameters
     * @throws ReviewException If the review failed to load
     */
    public static function getByData(UserModel $user, FoodtruckModel $foodtruck, DateTime $date): ReviewModel
    {
        $review = new ReviewModel();
        $review->loadByData($user, $foodtruck, $date);
        return $review;
    }

    /**
     * Create a review
     * @param UserModel $user The user that made the review
     * @param FoodtruckModel $foodtruck The foodtruck that the review is for
     * @param int $rating The rating of the review
     * @param string|null $title The title of the review (optional)
     * @param string|null $content The content of the review (optional)
     * @param FoodItemModel|null $foodItem The food item that the review is for (null if the review is for the foodtruck)
     * @return ReviewModel The created review
     * @throws ReviewException If the review failed to create
     */
    public static function createReview(UserModel $user, FoodtruckModel $foodtruck, int $rating, ?string $title, ?string $content, ?FoodItemModel $foodItem): ReviewModel
    {
        // Validate the inputs
        self::validateReviewInput($user, $foodtruck, $rating, $title, $content, $foodItem);

        // Format the inputs
        if (trim($title) === "")
            $title = null;
        if (trim($content) === "")
            $content = null;

        $date = new DateTime();

        // Create the review
        DatabaseHandler::getInstance()->query(self::$Q_CREATE_REVIEW, [
            $user->getUid(),
            $foodtruck->getId(),
            $rating,
            $date->format('Y-m-d H:i:s'),
            $title,
            $content,
            $foodItem == null ? null : $foodItem->getName()
        ], false);

        // Get the created review
        try {
            return self::getByData($user, $foodtruck, $date);
        }
        catch (NoDataException $e) {
            throw new ReviewException("Failed to create review: " . $e->getMessage());
        }
    }

    /* Methods */
    /**
     * Validate the given review input
     * @param UserModel $user The user that made the review
     * @param FoodtruckModel $foodtruck The foodtruck that the review is for
     * @param int $rating The rating of the review
     * @param string $title The title of the review
     * @param string $content The content of the review
     * @param FoodItemModel|null $foodItem The food item that the review is for (null if the review is for the foodtruck)
     * @throws ReviewException If the review input is invalid
     */
    private static function validateReviewInput(UserModel $user, FoodtruckModel $foodtruck, int $rating, string $title, string $content, ?FoodItemModel $foodItem): void
    {
        if ($rating < 1 || $rating > 5)
            throw new ReviewException("The rating must be between 1 and 5");

        if (($title == null && $content != null) || ($title != null && $content == null))
            throw new ReviewException("A review must contain both a title and a message or none of them");

        // Validate the title if != null
        if ($title != null) {
            // Title may only contain letters, numbers, ', ., ?, !, :, - and spaces
            if (!preg_match('/^[a-zA-Z0-9 \'.,?!:-]+$/', $title))
                throw new ReviewException("The title may only contain letters, numbers, ', ., ?, !, :, - and spaces");
        }

        // The user may not work at the foodtruck
        if ($foodtruck->containsWorker($user->getUid()))
            throw new ReviewException("A worker may not review the foodtruck they work at");

        // Max title length == 255
        if (strlen($title) > 255)
            throw new ReviewException("The title may not be longer than 255 characters");

        // Max content length == 1000
        if (strlen($content) > 1000)
            throw new ReviewException("The content may not be longer than 1000 characters");
    }

    /**
     * Load the review by the given id
     * @param int $id The id of the review
     * @throws NoDataException If no review was found with the given id
     * @throws ReviewException If the review failed to load
     * @post The review is loaded from the database
     */
    public function loadById(int $id): void
    {
        $this->loadByQuery(self::$Q_GET_BY_ID, [$id]);
    }

    /**
     * Load the review by the given parameters
     * @param UserModel $user The user that made the review
     * @param FoodtruckModel $foodtruck The foodtruck that the review is for
     * @param DateTime $date The date of the review
     * @throws NoDataException If no review was found with the given parameters
     * @throws ReviewException If the review failed to load
     * @post The review is loaded from the database
     */
    public function loadByData(UserModel $user, FoodtruckModel $foodtruck, DateTime $date): void
    {
        $this->loadByQuery(self::$Q_GET_BY_DATA, [$user->getUid(), $foodtruck->getId(), $date->format('Y-m-d H:i:s')]);
    }

    /**
     * Load the review by the given parameters
     * @param string $query The query to execute
     * @param array $params The parameters to use in the query
     * @throws NoDataException If no review was found with the given parameters
     * @throws ReviewException If the review failed to load
     * @post The review is loaded from the database
     */
    private function loadByQuery(string $query, array $params): void
    {
        $results = DatabaseHandler::getInstance()->query($query, $params);
        if (count($results) == 0)
            throw new NoDataException("No review found with the given parameters");

        $this->loadByDBResult($results[0]);
    }

    /**
     * Load the review from the database result
     * @param $result mixed The database result
     * @throws ReviewException If the review failed to load
     * @post The review is loaded from the database result
     */
    private function loadByDBResult($result): void
    {
        try {
            $this->id = intval($result->reviewId);
            $this->user = UserModel::getUserById(intval($result->user));
            $this->foodtruck = FoodtruckModel::getFoodtruckById(intval($result->foodtruck));
            $this->rating = intval($result->rating);
            $this->date = new DateTime($result->timestamp);

            if (isset($result->title))
                $this->title = $result->title;
            else
                $this->title = null;

            if (isset($result->content))
                $this->content = $result->content;
            else
                $this->content = null;

            if (isset($result->foodName))
                $this->foodItem = FoodItemModel::getByName($this->foodtruck->getId(), $result->foodName);
            else
                $this->foodItem = null;
        }
        catch (\Exception $e) {
            throw new ReviewException("Failed to load review from database result: " . $e->getMessage());
        }
    }

    /* QUERIES */
    private static string $Q_GET_BY_ID = 'SELECT * FROM Review WHERE reviewId = ?';
    private static string $Q_GET_BY_DATA = 'SELECT * FROM Review WHERE user = ? AND foodtruck = ? AND timestamp = ?';
    private static string $Q_GET_ALL_FOR_FOODTRUCK = 'SELECT * FROM Review WHERE foodtruck = ? AND foodName IS NULL ORDER BY timestamp ASC';
    private static string $Q_GET_ALL_FOR_FOOD_ITEM = 'SELECT * FROM Review WHERE foodtruck = ? AND foodName = ? ORDER BY timestamp ASC';

    private static string $Q_CREATE_REVIEW = 'INSERT INTO Review (user, foodtruck, rating, timestamp, title, content, foodName) VALUES (?, ?, ?, ?, ?, ?, ?)';

    /* GETTERS */
    /**
     * Get the id of the review
     * @return int The id of the review
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the user that made the review
     * @return UserModel The user that made the review
     */
    public function getUser(): UserModel
    {
        return $this->user;
    }

    /**
     * Get the foodtruck that the review is for
     * @return FoodtruckModel The foodtruck that the review is for
     */
    public function getFoodtruck(): FoodtruckModel
    {
        return $this->foodtruck;
    }

    /**
     * Get the rating of the review
     * @return int The rating of the review
     */
    public function getRating(): int
    {
        return $this->rating;
    }

    /**
     * Get the rating of the review in a formatted string
     * @return string The rating of the review in a formatted string
     */
    public function getFormattedRating(): string
    {
        $res = str_repeat("★", $this->rating);
        for ($i = $this->rating; $i < 5; $i++)
            $res .= "☆";

        return $res;
    }

    /**
     * Get the date of the review
     * @return DateTime The date of the review
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * Get the date of the review in a formatted string
     * @return string The date of the review in a formatted string
     */
    public function getFormattedDate(): string
    {
        return $this->date->format('d/m/Y');
    }

    /**
     * Get the title of the review
     * @return string The title of the review
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Get the content of the review
     * @return string The content of the review
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Check if the review contains a message
     * @return bool True if the review contains a message, false otherwise
     */
    public function containsMessage(): bool
    {
        return $this->title != null || $this->content != null;
    }

    /**
     * Get the food item that the review is for
     * @return FoodItemModel The food item that the review is for
     */
    public function getFoodItem(): ?FoodItemModel
    {
        return $this->foodItem;
    }

    /**
     * Get the HTML representation of the review
     * @return string The HTML representation of the review
     */
    public function toHtml(): string
    {
        // Show a different html if the review contains a message
        if ($this->containsMessage())
            return '
                <div class="review-object">
                    <div class="general-info">
                        <h2 class="user">' . $this->user->getFirstName() . '</h2>
                        <h2 class="rating">' . $this->getFormattedRating() . '</h2>
                        <p class="date">' . $this->getFormattedDate() . '</p>
                    </div>
                    <div class="message">
                        <h1 class="title">' . $this->title . '</h1>
                        <p class="content">' . $this->content . '</p>
                    </div>
                </div>
            ';

        // Else show the normal html
        else
            return '
                <div class="review-object">
                    <div class="general-info">
                        <h2 class="user">' . $this->user->getFirstName() . '</h2>
                        <h2 class="rating">' . $this->getFormattedRating() . '</h2>
                        <p class="date">' . $this->getFormattedDate() . '</p>
                    </div>
                </div>
            ';
    }
}