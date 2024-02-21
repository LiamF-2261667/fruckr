<?php

namespace App\Models;

use App\Database\DatabaseHandler;
use App\Exceptions\InvalidInputException;
use App\Exceptions\NoOwnerException;
use App\Exceptions\NoUserException;
use CodeIgniter\Database\Exceptions\DatabaseException;

class FoodtruckOwnerModel
{
    /* Attributes */
    private ?UserModel $user = null;

    /* Initializers */
    /**
     * Create a new FoodtruckOwnerModel
     * @param int $userKey the key of the user
     * @return FoodtruckOwnerModel the created FoodtruckOwnerModel
     * @throws NoUserException if no user is found
     * @throws NoOwnerException if the user is not a owner
     */
    public static function getOwnerByUserUid(int $userKey): FoodtruckOwnerModel
    {
        $owner = new FoodtruckOwnerModel();
        $owner->loadOwnerByUserUid($userKey);
        return $owner;
    }

    /**
     * Create a new FoodtruckOwnerModel
     * @param UserModel $user the user model
     * @return FoodtruckOwnerModel the created FoodtruckOwnerModel
     * @throws NoOwnerException if the user is not a owner
     */
    public static function getOwnerByUserModel(UserModel $user): FoodtruckOwnerModel
    {
        $owner = new FoodtruckOwnerModel();
        $owner->loadOwnerByUserModel($user);
        return $owner;
    }

    /* Methods */
    /**
     * Create a new owner
     * @param int $userKey the uid of the user
     * @throws InvalidInputException if the user is already a owner
     */
    public static function createNewOwner(int $userKey)
    {
        if (self::userIsOwner($userKey))
            throw new InvalidInputException("userKey", "User is already a owner");

        DatabaseHandler::getInstance()->query(self::$Q_CREATE_OWNER, [$userKey], false);
    }

    /**
     * Check if the given user is an owner
     * @param int $userKey the key of the user
     * @return bool true if the user is an owner
     */
    public static function userIsOwner(int $userKey): bool
    {
        return DatabaseHandler::getInstance()->query(self::$Q_IS_OWNER, [$userKey]) != null;
    }

    /**
     * Load a new FoodtruckOwnerModel by the given user key
     * @param int $userKey the key of the user
     * @throws NoUserException if no user is found
     * @throws NoOwnerException if the user is not a owner
     */
    public function loadOwnerByUserUid(int $userKey)
    {
        if (!self::userIsOwner($userKey))
            throw new NoOwnerException();

        $this->user = UserModel::getUserById($userKey);
    }

    /**
     * Load a new FoodtruckOwnerModel by the given user model
     * @param UserModel $user the user model
     * @throws NoOwnerException if the user is not a owner
     */
    public function loadOwnerByUserModel(UserModel $user)
    {
        if (!self::userIsOwner($user->getUid()))
            throw new NoOwnerException();

        $this->user = $user;
    }

    /**
     * Add a foodtruck to the owner
     * @param FoodtruckModel $foodtruck the foodtruck to add
     */
    public function addFoodtruck(FoodtruckModel $foodtruck): void
    {
        $this->user->getWorker()->addFoodtruck($foodtruck);
    }

    /* Queries */
    private static string $Q_IS_OWNER = "SELECT * FROM FoodtruckOwner WHERE uid = ?";

    private static string $Q_CREATE_OWNER = "INSERT INTO FoodtruckOwner (uid) VALUES (?)";

    /* Getters */
    /**
     * Get the foodtrucks this owner is associated with.
     * @return array|null The foodtrucks this owner is associated with.
     */
    public function getFoodtrucks(): ?array
    {
        return $this->user->getWorker()->getFoodtrucks();
    }

    /**
     * Get the foodtrucks owned by this owner.
     * @return array|null The foodtrucks owned by this owner.
     */
    public function getOwnedFoodtrucks(): ?array
    {
        $ownedFoodtrucks = [];

        foreach ($this->user->getWorker()->getFoodtrucks() as $foodtruck) {
            if ($foodtruck->getOwner()->getUid() === $this->user->getUid()) {
                $ownedFoodtrucks[$foodtruck->getId()] = $foodtruck;
            }
        }

        return $ownedFoodtrucks;
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