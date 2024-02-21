<?php

namespace App\Models;

use App\Database\DatabaseHandler;
use App\Exceptions\InvalidInputException;
use App\Exceptions\NoDataException;
use App\Exceptions\NoUserException;
use App\Exceptions\NoWorkerException;
use Exception;

class FoodtruckWorkerModel
{
    /* Attributes */
    private UserModel $user;
    private ?array $foodtrucks = [];

    /* Initializers */
    /**
     * Create a new FoodtruckWorkerModel
     * @param int $userKey the key of the user
     * @return FoodtruckWorkerModel the created FoodtruckWorkerModel
     * @throws NoUserException if no user is found
     * @throws NoWorkerException if the user is not a worker
     */
    public static function getWorkerByUserUid(int $userKey): FoodtruckWorkerModel
    {
        $worker = new FoodtruckWorkerModel();
        $worker->loadWorkerByUserUid($userKey);
        return $worker;
    }

    /**
     * Create a new FoodtruckWorkerModel
     * @param UserModel $user the user model
     * @return FoodtruckWorkerModel the created FoodtruckWorkerModel
     * @throws NoWorkerException if the user is not a worker
     */
    public static function getWorkerByUserModel(UserModel $user): FoodtruckWorkerModel
    {
        $worker = new FoodtruckWorkerModel();
        $worker->loadWorkerByUserModel($user);
        return $worker;
    }

    /* Methods */
    /**
     * Create a new worker
     * @param int $userKey the uid of the user
     * @throws InvalidInputException if the user is already a worker
     */
    public static function createNewWorker(int $userKey)
    {
        if (self::userIsWorker($userKey))
            throw new InvalidInputException("userKey", "User is already a worker");

        DatabaseHandler::getInstance()->query(self::$Q_CREATE_WORKER, [$userKey], false);
    }

    /**
     * Check if the given user is a worker
     * @param int $userKey the key of the user
     * @return bool true if the user is a worker
     */
    public static function userIsWorker(int $userKey): bool
    {
        return DatabaseHandler::getInstance()->query(self::$Q_IS_WORKER, [$userKey]) != null;
    }

    /**
     * Load a new FoodtruckWorkerModel by the given user key
     * @param int $userKey the key of the user
     * @throws NoUserException if no user is found
     * @throws NoWorkerException if the user is not a worker
     */
    public function loadWorkerByUserUid(int $userKey)
    {
        if (!self::userIsWorker($userKey))
            throw new NoWorkerException();

        $this->user = UserModel::getUserById($userKey);
    }

    /**
     * Load a new FoodtruckWorkerModel by the given user model
     * @param UserModel $user the user model
     * @throws NoWorkerException if the user is not a worker
     */
    public function loadWorkerByUserModel(UserModel $user)
    {
        if (!self::userIsWorker($user->getUid()))
            throw new NoWorkerException();

        $this->user = $user;
    }

    /**
     * Load the foodtrucks of the worker
     * @return void
     * @pre The user is loaded
     * @throws NoDataException if no foodtruck is found with the given worker
     */
    public function loadFoodtrucks()
    {
        $this->foodtrucks = FoodtruckModel::getAllFoodtrucksWithWorker($this->getUid());
    }

    public function unloadFoodtrucks() {
        $this->foodtrucks = [];
    }

    /**
     * Add a foodtruck to the worker
     * @param FoodtruckModel $foodtruckModel the foodtruck to add
     * @return void
     */
    public function addFoodtruck(FoodtruckModel $foodtruckModel)
    {
        try {
            DatabaseHandler::getInstance()->query(self::$Q_ADD_FOODTRUCK, [$this->user->getUid(), $foodtruckModel->getId()], false);
            $this->foodtrucks[$foodtruckModel->getId()] = $foodtruckModel;
        }
        catch (Exception $ignored) { }
    }

    /**
     * Remove a foodtruck from the worker
     * @param FoodtruckModel $foodtruckModel the foodtruck to remove
     * @throws Exception if the foodtruck could not be removed
     */
    public function removeFoodtruck(FoodtruckModel $foodtruckModel)
    {
        DatabaseHandler::getInstance()->query(self::$Q_REMOVE_FOODTRUCK, [$this->user->getUid(), $foodtruckModel->getId()], false);

        unset($this->foodtrucks[$foodtruckModel->getId()]);

        // Check if there remain any foodtrucks, otherwise remove the worker from the workers entirely
        if (count($this->foodtrucks) === 0)
            DatabaseHandler::getInstance()->query(self::$Q_REMOVE_WORKER, [$this->user->getUid()], false);
    }

    /* QUERIES */
    private static string $Q_IS_WORKER = "SELECT * FROM FoodtruckWorker WHERE uid = ?";

    private static string $Q_CREATE_WORKER = "INSERT INTO FoodtruckWorker (uid) VALUES (?)";

    private static string $Q_REMOVE_WORKER = "DELETE FROM FoodtruckWorker WHERE uid = ?";

    private static string $Q_ADD_FOODTRUCK = "INSERT INTO WorksAt (worker, foodtruck) VALUES (?, ?)";
    private static string $Q_REMOVE_FOODTRUCK = "DELETE FROM WorksAt WHERE worker = ? AND foodtruck = ?";

    /* Getters */
    /**
     * Get the foodtrucks of the worker
     * @return array the foodtrucks of the worker
     */
    public function getFoodtrucks(): array
    {
        return $this->foodtrucks;
    }

    public function getUid(): ?int
    {
        return $this->user->getUid();
    }

    public function getEmail(): ?string
    {
        return $this->user->getEmail();
    }

    public function getAddress(): ?AddressModel
    {
        return $this->user->getAddress();
    }

    public function getLastName(): ?string
    {
        return $this->user->getLastName();
    }

    public function getFirstName(): ?string
    {
        return $this->user->getFirstName();
    }

    /**
     * Get the full name of the user
     * @return string|null the full name of the user
     */
    public function getFullName(): ?string
    {
        return $this->user->getFullName();
    }

    public function getPhoneNumber(): ?string
    {
        return $this->user->getPhoneNumber();
    }

    /**
     * Get the phone number formatted to be more readable
     * @return string|null the phone number formatted to be more readable
     */
    public function getFormattedPhoneNumber(): ?string
    {
        return $this->user->getFormattedPhoneNumber();
    }

    public function getPassword(): ?string
    {
        return $this->user->getPassword();
    }
}