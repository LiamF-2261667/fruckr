<?php

namespace App\Models;

use App\Database\DatabaseHandler;
use App\DataObjects\BlobData;
use App\DataObjects\FoodItemData;
use App\Exceptions\InvalidInputException;
use App\Exceptions\NoDataException;
use Exception;

class FoodItemModel
{
    /* Attributes */
    private int $foodtruckId;

    private ?string $name = null;
    private ?string $description = null;
    private ?float $price = null;
    private ?array $ingredients = null;
    private ?BlobData $image = null;
    private ?int $rating = null;
    private array $media = [];

    /* Constructor */
    /**
     * Create a new instance of the food item model
     * @param int $foodtruckId The id of the foodtruck
     */
    public function __construct(int $foodtruckId)
    {
        $this->foodtruckId = $foodtruckId;
    }

    /* Initializers */
    /**
     * Get all the food items from the database of a foodtruck
     * @param int $foodtruckId The id of the foodtruck
     * @return array The food items
     * @throws NoDataException When there are no food items for the foodtruck
     */
    public static function getAllByFoodtruck(int $foodtruckId): array
    {
        $foodItems = [];

        // Get all the food items
        $foodItemsData = DatabaseHandler::getInstance()->query(self::$Q_GET_ALL_FOODITEMS_BY_FOODTRUCK, [$foodtruckId]);

        // Convert the data to models
        foreach ($foodItemsData as $foodItemData) {
            $foodItem = new FoodItemModel($foodtruckId);

            // Load the general data
            $foodItem->loadGeneralData($foodItemData);

            // Load the ingredients
            $ingredients = DatabaseHandler::getInstance()->query(self::$Q_GET_INGREDIENTS_BY_NAME, [$foodtruckId, $foodItem->getName()]);
            $foodItem->loadIngredientsData($ingredients);

            // Add the food item to the array
            $foodItems[] = $foodItem;
        }

        if (count($foodItems) == 0)
            throw new NoDataException("No food items found for foodtruck with id '$foodtruckId'");

        return $foodItems;
    }

    /**
     * Get a food item model from the database
     * @param int $foodtruckId The id of the foodtruck
     * @param string $name The name of the food item
     * @return FoodItemModel The food item model
     * @throws NoDataException When there is no food item with the name in the foodtruck
     */
    public static function getByName(int $foodtruckId, string $name): FoodItemModel
    {
        $foodItem = new FoodItemModel($foodtruckId);
        $foodItem->loadByName($foodtruckId, $name);
        return $foodItem;
    }

    /**
     * Load the data from the database
     * @param int $foodtruckId The id of the foodtruck
     * @param string $name The name of the food item
     * @throws NoDataException When there is no food item with the name in the foodtruck
     */
    private function loadByName(int $foodtruckId, string $name): void
    {
        // Get the result from the database
        $generalResult = DatabaseHandler::getInstance()->query(self::$Q_GET_BY_NAME, [$foodtruckId, $name]);
        $ingredients = DatabaseHandler::getInstance()->query(self::$Q_GET_INGREDIENTS_BY_NAME, [$foodtruckId, $name]);

        // Check if there is a result
        if (count($generalResult) == 0)
            throw new NoDataException("No food item found with name '$name'");

        // Load the data
        $this->loadGeneralData($generalResult[0]);
        $this->loadIngredientsData($ingredients);
    }

    /* Methods */
    /**
     * Load the general data from the database
     */
    private function loadGeneralData($data): void
    {
        $this->name = $data->name;
        $this->description = $data->description;
        $this->price = $data->price;
        $this->image = new BlobData($data->image, BlobData::$IMG);
        $this->rating = $data->rating;
    }

    /**
     * Load the ingredients data from the database
     * @param array $data The data from the database
     */
    private function loadIngredientsData(array $data): void
    {
        $this->ingredients = [];
        foreach ($data as $ingredient)
            $this->ingredients[] = $ingredient->ingredient;
    }

    /**
     * Load the extra data from the database
     */
    public function loadExtraData(): void
    {
        // Unload the extra data if it is already loaded
        $this->unloadExtraData();

        // Get the extra media
        $mediaResult = DatabaseHandler::getInstance()->query(self::$Q_GET_MEDIA_BY_NAME, [$this->foodtruckId, $this->name]);

        // Load the media
        foreach ($mediaResult as $media)
            $this->media[] = new BlobData($media->data, $media->type);
    }

    /**
     * Unload the extra data from the model
     */
    public function unloadExtraData(): void
    {
        $this->media = [];
    }

    /**
     * Save the data to the database
     * @param FoodItemData $data The data to save
     * @throws InvalidInputException When the data is invalid
     * @throws Exception When something went wrong while changing the name
     */
    public function save(FoodItemData $data): void
    {
        // Format & validate the inputs
        $data = self::formatSaveInputs($data);
        self::validateSaveInputs($data);

        // Save the data
        $this->saveGeneralData($data);
        $this->saveIngredientsData($data->ingredients);
        $this->saveMediaData($data->media);

        // Change the name if necessary
        if ($this->name !== $data->name)
            $this->changeName($data->name);
    }

    /**
     * Create a new food item
     * @param int $foodtruckId The id of the foodtruck
     * @param FoodItemData $data The data of the food item
     * @return FoodItemModel The created food item
     * @throws InvalidInputException When the data is invalid
     * @throws Exception When something went wrong while creating the food item
     */
    public static function create(int $foodtruckId, FoodItemData $data): FoodItemModel
    {
        // Format & validate the inputs
        $data = self::formatSaveInputs($data);
        self::validateSaveInputs($data);

        // Check if the food item already exists
        try {
            self::getByName($foodtruckId, $data->name);
            throw new InvalidInputException("name", "A food item with this name already exists");
        }
        catch (NoDataException $ignored) { }

        // Save the data
        DatabaseHandler::getInstance()->query(self::$Q_INSERT_FOODITEM, [
            $foodtruckId,
            $data->name,
            $data->description,
            $data->price,
            $data->image->getBlobData()
        ], false);

        // Save the ingredients
        foreach ($data->ingredients as $ingredient)
            DatabaseHandler::getInstance()->query(self::$Q_INSERT_FOODITEM_INGREDIENT, [
                $foodtruckId,
                $data->name,
                $ingredient
            ], false);

        // Save the media
        foreach ($data->media as $media)
            DatabaseHandler::getInstance()->query(self::$Q_INSERT_FOODITEM_MEDIA, [
                $foodtruckId,
                $data->name,
                $media->getBlobData(),
                $media->getTypeStr()
            ], false);

        // Return the created food item
        try {
            return self::getByName($foodtruckId, $data->name);
        }
        catch (NoDataException $e) {
            throw new Exception("Something went wrong while creating the food item");
        }
    }

    /**
     * Format the inputs for saving
     * @param FoodItemData $data The data to format
     * @return FoodItemData The formatted data
     */
    private static function formatSaveInputs(FoodItemData $data): FoodItemData
    {
        $data->name = ucwords(strtolower(trim($data->name)));
        $data->description = trim($data->description);
        $data->price = floatval($data->price);
        $data->ingredients = array_map(function ($ingredient) {
            return strtolower(trim($ingredient));
        }, $data->ingredients);

        return $data;
    }

    /**
     * Validate the inputs for saving
     * @param FoodItemData $data The data to validate
     * @throws InvalidInputException When the data is invalid
     */
    private static function validateSaveInputs(FoodItemData $data)
    {
        // The name is required
        if (empty($data->name))
            throw new InvalidInputException("name", "The name is required");

        // The name may only contain letters, numbers, spaces, dashes
        if (!preg_match("/^[a-zA-Z0-9 \-]*$/", $data->name))
            throw new InvalidInputException("name", "The name may only contain letters, numbers, spaces and dashes");

        // The name may not be longer than 50 characters
        if (strlen($data->name) > 50)
            throw new InvalidInputException("name", "The name may not be longer than 50 characters");

        // The description is required
        if (empty($data->description))
            throw new InvalidInputException("description", "The description is required");

        // The description may not be longer than 500 characters
        if (strlen($data->description) > 500)
            throw new InvalidInputException("description", "The description may not be longer than 500 characters");

        // The price is required
        if (empty($data->price))
            throw new InvalidInputException("price", "The price is required");

        // The price must positive
        if ($data->price <= 0)
            throw new InvalidInputException("price", "The price must be positive");

        // Ingredients may not be empty strings
        if (in_array("", $data->ingredients))
            throw new InvalidInputException("Ingredients", "Ingredients may not be empty");

        // Ingredients may not be longer than 50 characters
        if (count(array_filter($data->ingredients, function ($ingredient) {
            return strlen($ingredient) > 50;
        })) > 0)
            throw new InvalidInputException("Ingredients", "Ingredients may not be longer than 50 characters");

        // Ingredients may only contain letters, spaces, dashes
        if (count(array_filter($data->ingredients, function ($ingredient) {
            return !preg_match("/^[a-zA-Z \-]*$/", $ingredient);
        })) > 0)
            throw new InvalidInputException("Ingredients", "Ingredients may only contain letters, spaces and dashes");

        // Ingredients may not contain duplicate values
        if (count(array_unique($data->ingredients)) != count($data->ingredients))
            throw new InvalidInputException("Ingredients", "Ingredients may not contain duplicate values");

        // base image is required
        if ($data->image->getBase64Data() === null || $data->image->getBase64Data() === "")
            throw new InvalidInputException("image", "The base image is required");

        // base image may be at most 3 MB
        if (strlen($data->image->getBase64Data()) * (3/4) > 3 * 1024 * 1024)
            throw new InvalidInputException("image", "The base image may be at most 3 MB");

        // total of media may be at most 10 MB
        if (array_reduce($data->media, function ($acc, $media) {
            return $acc + strlen($media->getBase64Data()) * (3/4);
        }, 0) > 10 * 1024 * 1024)
            throw new InvalidInputException("media", "The total of media may be at most 10 MB");
    }

    /**
     * Save the general data to the database
     * @param FoodItemData $data The data to save
     */
    private function saveGeneralData(FoodItemData $data)
    {
        // Save the data to the database
        DatabaseHandler::getInstance()->query(self::$Q_UPDATE_FOODITEM, [
            $data->description,
            $data->price,
            $data->image->getBlobData(),
            $this->foodtruckId,
            $this->name
        ], false);

        // Update the model
        $this->description = $data->description;
        $this->price = $data->price;
        $this->image = $data->image;
    }

    /**
     * Save the ingredients data to the database
     * @param array $descriptionStrings The data to save
     */
    private function saveIngredientsData(array $descriptionStrings)
    {
        // Delete the current ingredients
        DatabaseHandler::getInstance()->query(self::$Q_DELETE_ALL_FOODITEM_INGREDIENT, [$this->foodtruckId, $this->name], false);

        // Try to save the data to the database
        // Catch the exception when the ingredient already exists
        foreach ($descriptionStrings as $descriptionString) {
            try {
                DatabaseHandler::getInstance()->query(self::$Q_INSERT_FOODITEM_INGREDIENT, [
                    $this->foodtruckId,
                    $this->name,
                    $descriptionString
                ], false);
            }
            catch (Exception $e) {}
        }

        // Update the model
        $this->ingredients = $descriptionStrings;
    }

    /**
     * Save the media data to the database
     * @param array $mediaBlobs The data to save
     */
    private function saveMediaData(array $mediaBlobs)
    {
        // Delete the current media
        DatabaseHandler::getInstance()->query(self::$Q_DELETE_ALL_FOODITEM_MEDIA, [$this->foodtruckId, $this->name], false);

        // Save the media to the database
        foreach ($mediaBlobs as $mediaBlob)
            DatabaseHandler::getInstance()->query(self::$Q_INSERT_FOODITEM_MEDIA, [
                $this->foodtruckId,
                $this->name,
                $mediaBlob->getBlobData(),
                $mediaBlob->getTypeStr()
            ], false);

        // Update the model
        $this->media = $mediaBlobs;
    }

    /**
     * Change the name of the food item
     * @param string $newName The new name of the food item
     * @throws Exception When something went wrong while changing the name
     * @post The name of the food item is changed
     */
    private function changeName(string $newName)
    {
        // Load the extra information
        $extraDataIsLoaded = $this->extraDataIsLoaded();
        $this->loadExtraData();

        // Insert a new food item with the new name
        DatabaseHandler::getInstance()->query(self::$Q_INSERT_FOODITEM, [
            $this->foodtruckId,
            $newName,
            $this->description,
            $this->price,
            $this->image->getBlobData()
        ], false);

        // Add the ingredients to the new food item
        foreach ($this->ingredients as $ingredient)
            DatabaseHandler::getInstance()->query(self::$Q_INSERT_FOODITEM_INGREDIENT, [
                $this->foodtruckId,
                $newName,
                $ingredient
            ], false);

        // Add the media to the new food item
        foreach ($this->media as $media)
            DatabaseHandler::getInstance()->query(self::$Q_INSERT_FOODITEM_MEDIA, [
                $this->foodtruckId,
                $newName,
                $media->getBlobData(),
                $media->getTypeStr()
            ], false);

        // Delete the old ingredients
        DatabaseHandler::getInstance()->query(self::$Q_DELETE_ALL_FOODITEM_INGREDIENT, [$this->foodtruckId, $this->name], false);

        // Delete the old media
        DatabaseHandler::getInstance()->query(self::$Q_DELETE_ALL_FOODITEM_MEDIA, [$this->foodtruckId, $this->name], false);

        // Delete the old food item
        DatabaseHandler::getInstance()->query(self::$Q_DELETE_FOODITEM, [$this->foodtruckId, $this->name], false);

        // Load the new food item
        try {
            $this->loadByName($this->foodtruckId, $newName);
        }
        catch (NoDataException $e) {
            throw new Exception("Something went wrong while changing the name of the food item");
        }

        // Load the extra data again if it was loaded before
        if ($extraDataIsLoaded)
            $this->loadExtraData();
    }

    /**
     * Delete the food item
     * @post The food item is deleted
     */
    public function delete(): void
    {
        // Delete the ingredients
        DatabaseHandler::getInstance()->query(self::$Q_DELETE_ALL_FOODITEM_INGREDIENT, [$this->foodtruckId, $this->name], false);

        // Delete the media
        DatabaseHandler::getInstance()->query(self::$Q_DELETE_ALL_FOODITEM_MEDIA, [$this->foodtruckId, $this->name], false);

        // Delete the food item
        DatabaseHandler::getInstance()->query(self::$Q_DELETE_FOODITEM, [$this->foodtruckId, $this->name], false);
    }

    /* Queries */
    private static string $Q_GET_BY_NAME = "SELECT * FROM FoodItem WHERE foodtruck = ? AND name = ?";
    private static string $Q_GET_INGREDIENTS_BY_NAME = "SELECT * FROM FoodItemIngredient WHERE foodtruck = ? AND foodName = ?";
    private static string $Q_GET_MEDIA_BY_NAME = "SELECT * FROM FoodItemMedia WHERE foodtruck = ? AND foodName = ?";

    private static string $Q_GET_ALL_FOODITEMS_BY_FOODTRUCK = "SELECT * FROM FoodItem WHERE foodtruck = ?";

    private static string $Q_INSERT_FOODITEM = "INSERT INTO FoodItem (foodtruck, name, description, price, image) VALUES (?, ?, ?, ?, ?)";
    private static string $Q_INSERT_FOODITEM_INGREDIENT = "INSERT INTO FoodItemIngredient (foodtruck, foodName, ingredient) VALUES (?, ?, ?)";
    private static string $Q_INSERT_FOODITEM_MEDIA = "INSERT INTO FoodItemMedia (foodtruck, foodName, data, type) VALUES (?, ?, ?, ?)";

    private static string $Q_UPDATE_FOODITEM = "UPDATE FoodItem SET description = ?, price = ?, image = ? WHERE foodtruck = ? AND name = ?";

    private static string $Q_DELETE_ALL_FOODITEM_INGREDIENT = "DELETE FROM FoodItemIngredient WHERE foodtruck = ? AND foodName = ?";
    private static string $Q_DELETE_ALL_FOODITEM_MEDIA = "DELETE FROM FoodItemMedia WHERE foodtruck = ? AND foodName = ?";
    private static string $Q_DELETE_FOODITEM = "DELETE FROM FoodItem WHERE foodtruck = ? AND name = ?";

    /* Getters */
    public function getName(): ?string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function getFormattedPrice(): ?string
    {
        return number_format($this->price, 2);
    }

    public function getIngredients(): ?array
    {
        return $this->ingredients;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function getMedia(): array
    {
        return $this->media;
    }

    public function extraDataIsLoaded(): bool
    {
        return count($this->media) > 0 || count($this->ingredients) > 0;
    }

    /**
     * Convert the model to a html string
     * @return string The html string
     */
    public function toHtml(): string
    {
        return '<li class="food-item">
                    <div class="image-container">
                        ' . $this->image->toHtml('loading="lazy" class="d-block" alt="Image of food item"') . '
                    </div>
                    <div class="info">
                        <h2>' . $this->name . '</h2>
                        <p>' . $this->description . '</p>
                        <h3>Ingredients</h3>
                        <p>' . implode(', ', $this->ingredients) . '</p>
                    </div>
                    <div class="price">
                        <div class="input-group">
                            <input type="number" value="1" min="1">
                            <div class="input-group-append">
                                <span class="input-group-text">€' . $this->getFormattedPrice() . '</span>
                            </div>
                            <div class="input-group-append">
                                <button class="input-group-text logo-button add-food-item-button"><img src="../Icons/add.png" alt="add Icon"></button>
                            </div>
                        </div>
                    </div>
                    <button class="extraInfo logo-button">
                        <img src="../Icons/info.png" alt="info Icon" class="extra-info-icon">
                    </button>
                </li>';
    }

    /**
     * Convert the model to a short html string
     * @return string The short html string with less information
     */
    public function toShortHtml(): string
    {
        return '
        <div class="food-item">
            <div class="image-container">
                ' . $this->image->toHtml('class="d-block" alt="Image of food item"') . '
            </div>
            <div class="info">
                <h2>' . $this->name . '</h2>
                <p>' . $this->description . '</p>
                <h3>Ingredients</h3>
                <p>' . implode(', ', $this->ingredients) . '</p>
            </div>
        </div>
        ';
    }
}