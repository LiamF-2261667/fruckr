<?php

namespace App\Models;

use App\Database\DatabaseHandler;
use App\DataObjects\BlobData;
use App\Exceptions\InvalidInputException;
use App\Exceptions\InvalidOrderException;
use App\Exceptions\NoDataException;
use App\Exceptions\NoOwnerException;
use App\Exceptions\NoUserException;
use App\Exceptions\NoWorkerException;
use App\Helpers\BlobConverter;

class FoodtruckModel
{
    /* Attributes */
    private ?int $id = null;
    private ?FoodtruckOwnerModel $owner = null;
    private ?array $workerIds = null;
    private ?string $name = null;
    private ?string $email = null;
    private ?string $phoneNumber = null;
    private ?AddressModel $currAddress = null;
    private array $tags = [];
    private array $openTimes = [];
    private array $futureLocations = [];
    private ?FoodtruckProfileModel $profile = null;
    private array $foodItems = [];
    private array $orders = [];
    private array $orderHistory = [];
    private ?int $rating = null;

    /* Initializers */
    /**
     * Create a new FoodtruckModel
     * @param int $id the id of the foodtruck
     * @return FoodtruckModel the created FoodtruckModel
     * @throws NoDataException if no foodtruck is found
     */
    public static function getFoodtruckById(int $id): FoodtruckModel
    {
        $foodtruck = new FoodtruckModel();
        $foodtruck->loadById($id);
        return $foodtruck;
    }

    /**
     * Get all the foodtrucks with a given worker
     * @param int $workerId the id of the worker
     * @return array all the FoodtruckModels with the given worker
     * @throws NoDataException if no foodtruck is found with the given worker
     */
    public static function getAllFoodtrucksWithWorker(int $workerId): array
    {
        return self::getFoodtrucksByQuery(self::$Q_GET_ALL_FOODTRUCKS_WITH_WORKER, [$workerId]);
    }

    /**
     * Get all the foodtrucks with a given name
     * @param string $name the name of the foodtruck
     * @return array all the FoodtruckModels with the given name
     * @throws NoDataException if no foodtruck is found with the given name
     */
    public static function getFoodtrucksByName(string $name): array
    {
        return self::getFoodtrucksByQuery(self::$Q_GET_FOODTRUCKS_BY_NAME, [$name]);
    }

    /**
     * Get all the foodtrucks with a given tag
     * @param string $tag the tag of the foodtruck
     * @return array all the FoodtruckModels with the given tag
     * @throws NoDataException if no foodtruck is found with the given tag
     */
    public static function getFoodtrucksByTag(string $tag): array
    {
        return self::getFoodtrucksByQuery(self::$Q_GET_FOODTRUCKS_BY_TAG, [$tag]);
    }

    /**
     * Get all the foodtrucks with a given city
     * @param string $city the city of the foodtruck
     * @return array all the FoodtruckModels with the given city
     * @throws NoDataException if no foodtruck is found with the given city
     */
    public static function getFoodtrucksByCity(string $city): array
    {
        return self::getFoodtrucksByQuery(self::$Q_GET_FOODTRUCKS_BY_CITY, [$city]);
    }

    /**
     * Get all the foodtrucks ordered by rating
     * @param int $limit the limit of foodtrucks to get
     * @return array all the FoodtruckModels with the highest rating until the limit is reached
     * @throws NoDataException if no foodtruck is found
     */
    public static function getFoodtruckByRating(int $limit): array
    {
        return self::getFoodtrucksByQuery(self::$Q_GET_ALL_FOODTRUCKS_SORTED_BY_RATING, [$limit]);
    }

    /**
     * Get all the foodtruckModels from a query with the given params
     * @param string $query the query to execute
     * @param array $params the params to bind to the query
     * @return array all the FoodtruckModels from the query with the given params
     * @throws NoDataException if no foodtruck is found
     */
    private static function getFoodtrucksByQuery(string $query, array $params): array
    {
        $result = DatabaseHandler::getInstance()->query($query, $params);
        $foodtrucks = [];
        foreach ($result as $foodtruck) {
            $foodtruckModel = new FoodtruckModel();
            $foodtruckModel->loadById($foodtruck->foodtruckId);
            $foodtrucks["id" . $foodtruck->foodtruckId] = $foodtruckModel;
        }
        return $foodtrucks;
    }

    /**
     * Get all the foodtrucks
     * @return array all the FoodtruckModels
     * @throws NoDataException if no foodtruck is found
     */
    public static function getAllFoodtrucks(): array
    {
        $result = DatabaseHandler::getInstance()->completeQuery(self::$Q_GET_ALL_FOODTRUCKS);
        $foodtrucks = [];
        foreach ($result as $foodtruck) {
            $foodtruckModel = new FoodtruckModel();
            $foodtruckModel->loadById($foodtruck->foodtruckId);
            $foodtrucks[] = $foodtruckModel;
        }
        return $foodtrucks;
    }

    /* Methods */
    /**
     * Loads the foodtruck with the given id
     * @param int $id The id of the foodtruck to load
     * @return void
     * @throws NoDataException
     */
    public function loadById(int $id)
    {
        // Loading the foodtruck data inside the model
        $this->loadFoodtruckData($id);

        // Getting the tags
        $this->loadTags($id);

        // Getting the open times
        $this->openTimes = OpenTimeModel::getOpenTimesByFoodtruckKey($id);

        // Getting the future locations
        $this->futureLocations = FutureLocationModel::getFutureLocationsByFoodtruckId($id);

        // Getting the profile
        $this->profile = FoodtruckProfileModel::getProfileByFoodtruckKey($id);
    }

    /**
     * Loads the fooditems of the foodtruck
     * @pre the foodtruck id is loaded
     */
    public function loadFoodItems() {
        try {
            $this->foodItems = FoodItemModel::getAllByFoodtruck($this->id);
        }
        catch (NoDataException $ignored) {
            $this->foodItems = [];
        }
    }

    /**
     * Loads the foodtruckdata with the given id
     * @param int $id The id of the foodtruck to load
     * @return void
     * @throws NoDataException if no valid foodtruck is found
     */
    private function loadFoodtruckData(int $id) {
        // Getting the foodtruck data from the db
        $result = DatabaseHandler::getInstance()->query(self::$Q_GET_FOODTRUCK_BY_ID, [$id]);
        if (count($result) == 0)
            throw new NoDataException("Foodtruck:id", "No foodtruck found with id " . $id);

        // Loading the foodtruck data inside the model
        $this->id = $id;
        try {
            $this->owner = FoodtruckOwnerModel::getOwnerByUserUid($result[0]->owner, false);
        } catch (NoOwnerException|NoUserException $e) {
            throw new NoDataException("Foodtruck:owner", "No owner found for foodtruck with id " . $id);
        }

        $this->name = $result[0]->name;
        $this->email = $result[0]->email;
        $this->phoneNumber = $result[0]->phoneNumber;
        $this->currAddress = AddressModel::getAddressById($result[0]->addressKey);
        $this->rating = $result[0]->rating;

        // Getting the workers
        foreach ($result as $row)
            $this->workerIds[] = $row->worker;
    }

    /**
     * Loads the foodtrucktags with the given id
     * @param int $id The id of the foodtruck to load
     */
    private function loadTags(int $id) {
        $result = DatabaseHandler::getInstance()->query(self::$Q_GET_FOODTRUCK_TAGS, [$id]);
        if (count($result) == 0)
            $this->tags = [];

        else {
            // Loading the tags inside the model
            $this->tags = [];
            foreach ($result as $tag)
                $this->tags[] = $tag->tag;
        }
    }

    /**
     * Create a new foodtruck
     * @param FoodtruckDataModel $foodtruckData the data of the foodtruck
     * @param FoodtruckOwnerModel $owner the owner
     * @throws InvalidInputException if an input is invalid
     */
    public static function create(FoodtruckDataModel $foodtruckData, FoodtruckOwnerModel $owner): ?FoodtruckModel
    {
        // Format and validate the inputs
        self::formatSaveInput($foodtruckData);
        self::validateSaveInput($foodtruckData);

        // Save the foodtruck ids currently owned by the owner
        $foodtruckIdsFromOwnerBefore = DatabaseHandler::getInstance()->query(self::$Q_GET_FOODTRUCKS_FROM_OWNER, [$owner->getUid()]);

        // Create a new address
        $address = null;
        if ($foodtruckData->bus === null)
            $address = AddressModel::createNewAddress($foodtruckData->postalCode, $foodtruckData->city, $foodtruckData->street, $foodtruckData->houseNr);
        else
            $address = AddressModel::createNewAddress($foodtruckData->postalCode, $foodtruckData->city, $foodtruckData->street, $foodtruckData->houseNr, $foodtruckData->bus);

        // Create the foodtruck
        DatabaseHandler::getInstance()->query(self::$Q_CREATE_FOODTRUCK, [$owner->getUid(), $foodtruckData->name, $foodtruckData->email, $foodtruckData->phoneNumber, $address->getAddressKey()], false);

        // Determine the new foodtruck id of the owner
        $foodtruckIdsFromOwnerAfter = DatabaseHandler::getInstance()->query(self::$Q_GET_FOODTRUCKS_FROM_OWNER, [$owner->getUid()]);

        $newFoodtruckId = null;
        foreach ($foodtruckIdsFromOwnerAfter as $foodtruckIdAfter) {
            if (!in_array($foodtruckIdAfter, $foodtruckIdsFromOwnerBefore)) {
                $newFoodtruckId = $foodtruckIdAfter->foodtruckId;
                break;
            }
        }

        if ($newFoodtruckId == null)
            throw new InvalidInputException("foodtruck", "Something went wrong while creating the foodtruck");

        // Create the profile
        FoodtruckProfileModel::createNewProfile($newFoodtruckId, $foodtruckData->extra, $foodtruckData->description, $foodtruckData->profileImageBase64, $foodtruckData->banners);

        // Load the foodtruck form the database
        try {
            $newFoodtruck = self::getFoodtruckById($newFoodtruckId);

            // Save all the other data depending on an already created foodtruck
            $newFoodtruck->save($foodtruckData);

            // Add the foodtruck to the owner
            $owner->addFoodtruck($newFoodtruck);

            // Load the data in again, in case something changed
            return self::getFoodtruckById($newFoodtruckId);
        }
        catch (NoDataException $ignored) {
            throw new InvalidInputException("", "Major error: FoodtruckModel::create() failed to load the foodtruck from the database");
        }
    }

    /**
     * Save the foodtruck
     * @param FoodtruckDataModel $foodtruckData the data of the foodtruck
     * @throws InvalidInputException if an input is invalid
     */
    public function save(FoodtruckDataModel $foodtruckData): void
    {
        // Format the inputs
        self::formatSaveInput($foodtruckData);

        // Validate the inputs
        self::validateSaveInput($foodtruckData);
        $this->validateOpenTimes($foodtruckData->openOn);
        $this->validateFutureLocations($foodtruckData->futureLocations);

        // Update the address if necessary
        if ($this->currAddress != null && !$this->currAddress->equals($foodtruckData->postalCode, $foodtruckData->city, $foodtruckData->street, $foodtruckData->houseNr, $foodtruckData->bus)) {
            $this->currAddress = AddressModel::createNewAddress($foodtruckData->postalCode, $foodtruckData->city, $foodtruckData->street, $foodtruckData->houseNr, $foodtruckData->bus);
            DatabaseHandler::getInstance()->query(self::$Q_UPDATE_FOODTRUCK, [$foodtruckData->name, $foodtruckData->email, $foodtruckData->phoneNumber, $this->currAddress->getAddressKey(), $this->id], false);
        }

        // Update the foodtruck if necessary
        else if (!$this->equals($foodtruckData->name, $foodtruckData->email, $foodtruckData->phoneNumber, $this->currAddress->getAddressKey()))
            DatabaseHandler::getInstance()->query(self::$Q_UPDATE_FOODTRUCK, [$foodtruckData->name, $foodtruckData->email, $foodtruckData->phoneNumber, $this->currAddress->getAddressKey(), $this->id], false);

        // Update the tags
        $this->updateTags($foodtruckData->tags);

        // Update the open times
        $this->updateOpenTimes($foodtruckData->openOn);

        // Update the future locations
        $this->updateFutureLocations($foodtruckData->futureLocations);

        // Update the profile
        $this->profile->save($foodtruckData->profileImageBase64, $foodtruckData->banners, $foodtruckData->description, $foodtruckData->extra);
    }

    /**
     * Update the tags of the foodtruck
     * @param array $tags the tags to update
     */
    private function updateTags(array $tags): void
    {
        // Get the current tags
        $currentTags = $this->tags;

        // Remove the tags that are not in the new tags
        foreach ($currentTags as $currentTag) {
            if (!in_array($currentTag, $tags))
                DatabaseHandler::getInstance()->query(self::$Q_DELETE_FOODTRUCK_TAG, [$this->id, $currentTag], false);
        }

        // Add the tags that are not in the current tags
        foreach ($tags as $tag) {
            if (!in_array($tag, $currentTags))
                DatabaseHandler::getInstance()->query(self::$Q_ADD_FOODTRUCK_TAG, [$this->id, $tag], false);
        }
    }

    /**
     * Update the open times of the foodtruck
     * @param array $openTimes the new open times
     * @throws InvalidInputException if an input is invalid
     */
    private function updateOpenTimes(array $openTimes): void
    {
        // Get the current open times
        $currentOpenTimes = $this->openTimes;

        // Create openTimeModels for the new open times
        $openTimeModels = [];
        foreach ($openTimes as $openTime) {
            $openTimeModel = new OpenTimeModel($this->id);
            $openTimeModel->setData($openTime);
            $openTimeModels[] = $openTimeModel;
        }

        // Check if there are changes
        $this->changeModelArrayToNewArray($currentOpenTimes, $openTimeModels);
    }

    /**
     * Update the future locations of the foodtruck
     * @param array $futureLocations the new future locations
     */
    private function updateFutureLocations(array $futureLocations): void
    {
        // Get the current future locations
        $currentFutureLocations = $this->futureLocations;

        // Create futureLocationModels for the new future locations
        $futureLocationModels = [];
        foreach ($futureLocations as $futureLocation) {
            $futureLocationModel = new FutureLocationModel($this->id);
            $futureLocationModel->setData($futureLocation);
            $futureLocationModels[] = $futureLocationModel;
        }

        // Check if there are changes
        $this->changeModelArrayToNewArray($currentFutureLocations, $futureLocationModels);
    }

    /**
     * Format the inputs for saving a foodtruck
     * @param FoodtruckDataModel $foodtruckData the data of the foodtruck
     * @return void the formatted data
     */
    private static function formatSaveInput(FoodtruckDataModel $foodtruckData): void
    {
        // General information
        $foodtruckData->email = strtolower($foodtruckData->email);

        if ($foodtruckData->phoneNumber != null && $foodtruckData->phoneNumber != "") {
            $foodtruckData->phoneNumber = str_replace(" ", "", $foodtruckData->phoneNumber);
            $foodtruckData->phoneNumber = str_replace("-", "", $foodtruckData->phoneNumber);
            $foodtruckData->phoneNumber = str_replace("/", "", $foodtruckData->phoneNumber);
            $foodtruckData->phoneNumber = str_replace(".", "", $foodtruckData->phoneNumber);
        }

        $foodtruckData->city = strtoupper($foodtruckData->city);
        $foodtruckData->street = ucwords(strtolower($foodtruckData->street));
        $foodtruckData->bus = strtoupper($foodtruckData->bus);

        // Tags
        for ($i = 0; $i < count($foodtruckData->tags); $i++)
            $foodtruckData->tags[$i] = ucwords(strtolower($foodtruckData->tags[$i]));
    }

    /**
     * Validate the input for saving a foodtruck
     * @throws InvalidInputException if an input is invalid
     */
    private static function validateSaveInput(FoodtruckDataModel $foodtruckData): void
    {
        // Check if the name is filled
        if ($foodtruckData->name == null || !isset($foodtruckData->name))
            throw new InvalidInputException("name", "Name: Please fill in the name");

        // Check if the email is filled
        if ($foodtruckData->email == null || !isset($foodtruckData->email))
            throw new InvalidInputException("email", "Information: Please fill in the email");

        // Check if the phone number is filled
        if ($foodtruckData->phoneNumber == null || !isset($foodtruckData->phoneNumber))
            throw new InvalidInputException("phoneNumber", "Information: Please fill in the phone number");

        // Check if the city is filled
        if ($foodtruckData->city == null || !isset($foodtruckData->city))
            throw new InvalidInputException("city", "Information: Please fill in the city");

        // Check if the street is filled
        if ($foodtruckData->street == null || !isset($foodtruckData->street))
            throw new InvalidInputException("street", "Information: Please fill in the street");

        // Check if the postal code is filled
        if ($foodtruckData->postalCode == null || !isset($foodtruckData->postalCode))
            throw new InvalidInputException("postalCode", "Information: Please fill in the postal code");

        // Check if the house number is filled
        if ($foodtruckData->houseNr == null || !isset($foodtruckData->houseNr))
            throw new InvalidInputException("houseNr", "Information: Please fill in the house number");

        // Check if the profile image is filled
        if ($foodtruckData->profileImageBase64 == null || !isset($foodtruckData->profileImageBase64))
            throw new InvalidInputException("profileImage", "Profile image: Please fill in the profile image");

        // the base64 has to be smaller than 3MB for the profile image
        if (strlen($foodtruckData->profileImageBase64) * (3/4) > 3000000) /* base64 * 3/4 = bytes */
            throw new InvalidInputException("profileImage", "Profile image: The profile image can't be bigger than 3MB");

        // Check if the banners are filled
        if ($foodtruckData->banners == null || !isset($foodtruckData->banners) || count($foodtruckData->banners) == 0)
            throw new InvalidInputException("banners", "Banners: Please fill in at least one banner");

        // Make sure no banners exceed 10MB
        $totalBannerSize = 0;
        foreach ($foodtruckData->banners as $banner) {
            $newBannerSize = strlen($banner->base64) * (3/4); /* base64 * 3/4 = bytes */
            $totalBannerSize += $newBannerSize;

            if ($newBannerSize > 10000000)
                throw new InvalidInputException("banners", "Banners: The banners may not be bigger than 10MB");
        }

        // Make sure there is max 10MB of banner data
        if ($totalBannerSize > 10000000)
            throw new InvalidInputException("banners", "Banners: The banners combined may not be bigger than 10MB");

        // Check if the description is filled
        if ($foodtruckData->description == null || !isset($foodtruckData->description))
            throw new InvalidInputException("description", "Description: Please fill in the description");

        // Check if the open times are filled
        if ($foodtruckData->openOn == null || !isset($foodtruckData->openOn) || count($foodtruckData->openOn) == 0)
            throw new InvalidInputException("openOn", "Open on: Please fill in at least opening time");

        // Check if the email is valid
        if (!filter_var($foodtruckData->email, FILTER_VALIDATE_EMAIL))
            throw new InvalidInputException("email", "Information: Please fill in a valid email (like example@gmail.com)");

        // Check if the address is valid
        try {
            AddressModel::validate($foodtruckData->postalCode, $foodtruckData->city, $foodtruckData->street, $foodtruckData->houseNr, $foodtruckData->bus);
        }
        catch (InvalidInputException $e) {
            throw new InvalidInputException("Information", "Information: " . $e->getMessage());
        }

        // Check if the name is valid
        if (!preg_match("/^[a-zA-Z'!,\- ]*$/", $foodtruckData->name))
            throw new InvalidInputException("name", "Information: a name can only contain letters, spaces, ', ! and -");

        // Check if the phone number is valid
        if (!preg_match("/^\+?[0-9]*$/", $foodtruckData->phoneNumber))
            throw new InvalidInputException("phoneNumber", "Information: a phone number can only contain numbers or a + in the beginning");

        if (strlen($foodtruckData->phoneNumber) < 9)
            throw new InvalidInputException("phoneNumber", "Information: a phone number must be at least 9 characters long");

        if (strlen($foodtruckData->phoneNumber) > 12)
            throw new InvalidInputException("phoneNumber", "Information: a phone number can't be longer than 12 characters");
    }

    /**
     * Validate the open times
     * @param array $openOn the open times to validate
     * @throws InvalidInputException if the open times are invalid
     */
    private function validateOpenTimes(array $openOn): void
    {
        // Check if the open times are valid
        $openTimeModels = [];
        foreach ($openOn as $openTime) {
            $openTimeModel = new OpenTimeModel($this->id);
            $openTimeModel->setData($openTime);
            $openTimeModel->validateModel();
            $openTimeModels[] = $openTimeModel;
        }

        // Make sure the open time models don't overlap
        for ($i = 0; $i < count($openTimeModels); $i++) {
            for ($j = $i + 1; $j < count($openTimeModels); $j++) {
                if ($openTimeModels[$i]->overlaps($openTimeModels[$j]))
                    throw new InvalidInputException("openOn", "The open times can't overlap");
            }
        }
    }

    /**
     * Validate the future locations
     * @param array $futureLocations the future locations to validate
     * @throws InvalidInputException if the future locations are invalid
     */
    private function validateFutureLocations(array $futureLocations): void
    {
        // Check if the future locations are valid
        $futureLocationModels = [];
        foreach ($futureLocations as $futureLocation) {
            $futureLocationModel = new FutureLocationModel($this->id);
            $futureLocationModel->setData($futureLocation);
            $futureLocationModel->validateModel();
            $futureLocationModels[] = $futureLocationModel;
        }

        // Make sure the future location models don't fall on the same day
        for ($i = 0; $i < count($futureLocationModels); $i++) {
            for ($j = $i + 1; $j < count($futureLocationModels); $j++) {
                if ($futureLocationModels[$i]->getStartDate() == $futureLocationModels[$j]->getStartDate())
                    throw new InvalidInputException("futureLocations", "The future locations can't fall on the same day");
            }
        }
    }

    /**
     * Change the current model array to the new model array, as well as in the database
     * @param array $currArray The current array of models
     * @param array $newArray The new array of models
     */
    private function changeModelArrayToNewArray(array $currArray, array $newArray): void
    {
        if (count($currArray) == count($newArray)) {
            $changes = false;
            for ($i = 0; $i < count($currArray); $i++) {
                if (!$currArray[$i]->equals($newArray[$i])) {
                    $changes = true;
                    break;
                }
            }
            if (!$changes)
                return;
        }

        // Delete the current models
        foreach ($currArray as $currentModel)
            $currentModel->delete();

        // Add the new model
        foreach ($newArray as $newModel)
            $newModel->save();
    }

    /**
     * Check if the foodtruck equals the given data
     * @param string $name the name of the foodtruck
     * @param string $email the email of the foodtruck
     * @param string $phoneNumber the phone number of the foodtruck
     * @param string $addressKey the address key of the foodtruck
     * @return bool true if the foodtruck equals the given data
     */
    public function equals(string $name, string $email, string $phoneNumber, string $addressKey): bool
    {
        return $this->name == $name && $this->email == $email && $this->phoneNumber == $phoneNumber && $this->currAddress->getAddressKey() == $addressKey;
    }

    /**
     * Add a food item to the foodtruck
     */
    public function addFoodItem(FoodItemModel $foodItem): void
    {
        $this->foodItems[] = $foodItem;
    }

    /**
     * Load all the open orders of the foodtruck
     * @throws InvalidOrderException if an order is invalid
     */
    public function loadOrders(): void
    {
        $this->orders = OrderModel::getAllOpenOrdersByFoodtruck($this->id);
    }

    /**
     * Unload all the open orders of the foodtruck
     */
    public function unloadOrders(): void
    {
        $this->orders = [];
    }

    /**
     * Load the order history of the foodtruck
     * @throws InvalidOrderException if an order is invalid
     */
    public function loadOrderHistory(): void
    {
        $this->orderHistory = OrderModel::getAllOrdersByFoodtruck($this->id);
    }

    /**
     * Unload the order history of the foodtruck
     */
    public function unloadOrderHistory(): void
    {
        $this->orderHistory = [];
    }

    /* Queries */
    private static string $Q_GET_FOODTRUCK_BY_ID = "SELECT * FROM Foodtruck LEFT JOIN WorksAt ON Foodtruck.foodtruckId = WorksAt.foodtruck WHERE foodtruckId = ?";
    private static string $Q_GET_FOODTRUCKS_FROM_OWNER = "SELECT foodtruckId FROM Foodtruck WHERE owner = ?";

    private static string $Q_CREATE_FOODTRUCK = "INSERT INTO Foodtruck (owner, name, email, phoneNumber, addressKey) VALUES (?, ?, ?, ?, ?)";

    private static string $Q_GET_FOODTRUCK_TAGS = "SELECT tag FROM FoodtruckTag WHERE foodtruck = ?";

    private static string $Q_GET_ALL_FOODTRUCKS = "SELECT foodtruckId FROM Foodtruck";
    private static string $Q_GET_ALL_FOODTRUCKS_WITH_WORKER =   "SELECT foodtruck as foodtruckId FROM WorksAt  WHERE worker = ?";
    private static string $Q_GET_ALL_FOODTRUCKS_SORTED_BY_RATING = "SELECT foodtruckId FROM Foodtruck ORDER BY rating DESC limit ?";

    private static string $Q_UPDATE_FOODTRUCK = "UPDATE Foodtruck SET name = ?, email = ?, phoneNumber = ?, addressKey = ? WHERE foodtruckId = ?";

    private static string $Q_DELETE_FOODTRUCK_TAG = "DELETE FROM FoodtruckTag WHERE foodtruck = ? AND tag = ?";
    private static string $Q_ADD_FOODTRUCK_TAG = "INSERT INTO FoodtruckTag (foodtruck, tag) VALUES (?, ?)";

    private static string $Q_GET_FOODTRUCKS_BY_NAME = "SELECT foodtruckId FROM Foodtruck WHERE name LIKE ?";
    private static string $Q_GET_FOODTRUCKS_BY_TAG = "SELECT foodtruck as foodtruckId FROM FoodtruckTag WHERE tag LIKE ?";
    private static string $Q_GET_FOODTRUCKS_BY_CITY = "SELECT foodtruckId FROM Foodtruck WHERE addressKey IN (SELECT addressKey FROM Address WHERE postalCode IN (SELECT postalCode FROM City WHERE name LIKE ?))";

    /* Getters */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?FoodtruckOwnerModel
    {
        return $this->owner;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }
    /**
     *
     * Get the phone number formatted to be more readable
     * @return string|null the phone number formatted to be more readable
     */
    public function getFormattedPhoneNumber(): ?string
    {
        $phoneNumber = $this->phoneNumber;

        if (str_contains($phoneNumber, "+")){
            $phoneNumber = substr_replace($phoneNumber, " ", 3, 0);
            $phoneNumber = substr_replace($phoneNumber, " ", 7, 0);
            $phoneNumber = substr_replace($phoneNumber, " ", 10, 0);
            $phoneNumber = substr_replace($phoneNumber, " ", 13, 0);
        }

        else {
            $phoneNumber = substr_replace($phoneNumber, " ", 4, 0);
            $phoneNumber = substr_replace($phoneNumber, " ", 7, 0);
            $phoneNumber = substr_replace($phoneNumber, " ", 10, 0);
        }

        return trim($phoneNumber);
    }

    public function getCurrAddress(): ?AddressModel
    {
        return $this->currAddress;
    }

    public function getTags(): ?array
    {
        return $this->tags;
    }

    public function getOpenTimes(): ?array
    {
        return $this->openTimes;
    }

    public function getFutureLocations(): ?array
    {
        return $this->futureLocations;
    }

    public function getRating(): ?int {
        return $this->rating;
    }

    public function getRatingString(): ?string
    {
        $rating = $this->getRating();
        $ratingString = str_repeat("★", $rating);

        for ($i = $rating; $i < 5; $i++)
            $ratingString .= "☆";

        return $ratingString;
    }

    public function getDescription(): ?string
    {
        return $this->profile->getDescription();
    }

    public function getExtraInfo(): ?string
    {
        return $this->profile->getExtraInfo();
    }

    public function getProfileImage(): ?BlobData
    {
        return $this->profile->getProfileImage();
    }

    public function getBannerImages(): ?array
    {
        return $this->profile->getBannerImages();
    }

    public function containsWorker(int $workerId): bool
    {
        return in_array($workerId, $this->workerIds);
    }

    public function getFoodItems(): ?array
    {
        return $this->foodItems;
    }

    public function getWorkers(): array
    {
        $workers = [];
        foreach ($this->workerIds as $workerId) {
            try {
                $workers[] = FoodtruckWorkerModel::getWorkerByUserUid($workerId);
            }
            catch (NoUserException | NoWorkerException | NoDataException $ignored) { }
        }
        return $workers;
    }

    /**
     * Get all the orders of the foodtruck (including closed orders)
     * @return array all the orders of the foodtruck (OrderModel[])
     * @pre orders are loaded
     */
    public function getOrderHistory(): array
    {
        return $this->orderHistory;
    }

    /**
     * Get all the open orders of the foodtruck
     * @return array all the open orders of the foodtruck (OrderModel[])
     * @pre orders are loaded
     */
    public function getOrders(): array
    {
        return $this->orders;
    }

    /**
     * Get an order by id
     * @param int $orderId the id of the order
     * @return OrderModel the order with the given id
     * @throws NoDataException if no order is found with the given id
     */
    public function getOrder(int $orderId): OrderModel
    {
        foreach ($this->orders as $order) {
            if ($order->getId() == $orderId)
                return $order;
        }

        throw new NoDataException("Order:orderId", "No order found with id " . $orderId);
    }

    /**
     * Get a food item by name
     * @throws NoDataException if no food item is found with the given name
     */
    public function getFoodItem(string $name): FoodItemModel
    {
        foreach ($this->foodItems as $foodItem) {
            if ($foodItem->getName() == $name)
                return $foodItem;
        }

        throw new NoDataException("FoodItem:name", "No fooditem found with name " . $name);
    }

    /**
     * Get the html string for the thumbnail of the foodtruck
     * @return string the html string for the thumbnail of the foodtruck
     */
    public function getThumbnailString(): string
    {
        return '<a href="foodtruck/' . $this->id . '">
                        <li>
                            <div class="title">
                                <h2>' . $this->name . '</h2>
                                <h2 class="rating">' . $this->getRatingString() . '</h2>
                            </div>
                            <div class="profileImageContainer">
                            ' . $this->profile->getProfileImage()->toHtml('loading="lazy" alt="Picture of the foodtruck"') . '
                            </div>
                            <h3>' . implode(" | ", $this->tags) . '</h3>
                            <p>' . $this->currAddress->toShortString() . '</p>
                        </li>
                    </a>';
    }
}