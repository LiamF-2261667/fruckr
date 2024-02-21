<?php

namespace App\Models;

use App\Database\DatabaseHandler;
use App\Exceptions\InvalidInputException;
use App\Exceptions\NoDataException;
use App\Exceptions\NoOwnerException;
use App\Exceptions\NoUserException;
use App\Exceptions\NoWorkerException;
use CodeIgniter\Database\Exceptions\DatabaseException;
use mysql_xdevapi\Exception;

/**
 * A model that represents a user in the database
 * @author Liam Froyen
 */
class UserModel
{
    /* Attributes */
    private ?int $uid = null;
    private ?string $email = null;
    private ?AddressModel $address = null;
    private ?string $lastName = null;
    private ?string $firstName = null;
    private ?string $phoneNumber = null;
    private ?string $password = null;
    private ?FoodtruckWorkerModel $worker = null;
    private ?FoodtruckOwnerModel  $owner = null;

    /* Methods */
    /**
     * load a user from the database
     * @param $uid int the uid of the user to load
     * @return void
     * @throws NoUserException when no user is found
     */
    public function loadByUid(int $uid)
    {
        // Get the user from the database
        $resultUser = DatabaseHandler::getInstance()->query(self::$Q_GET_USER_BY_UID, [$uid]);

        // Check if the database got a result
        if ($resultUser == null || count($resultUser) == 0)
            throw new NoUserException();

        // Get if it is a worker
        $resultWorker = DatabaseHandler::getInstance()->query(self::$Q_GET_WORKERS_BY_UID, [$uid]);

        // Load the user
        $this->loadUser($resultUser);
    }

    /**
     * load a user from the database
     * @param $email string the email of the user to load
     * @return void
     * @throws NoUserException when no user is found
     */
    public function loadByEmail(string $email)
    {
        // Get the user from the database
        $resultUser = DatabaseHandler::getInstance()->query(self::$Q_GET_USER_BY_EMAIL, [$email]);

        // Check if the database got a result
        if ($resultUser == null || count($resultUser) == 0)
            throw new NoUserException();

        // Get if it is a worker
        $resultWorker = DatabaseHandler::getInstance()->query(self::$Q_GET_WORKERS_BY_UID, [$resultUser[0]->uid]);

        // Load the user
        $this->loadUser($resultUser, $resultWorker != null && count($resultWorker) > 0);
    }

    /**
     * Load a UserModel from the database by email
     * @param string $email the email of the user to get
     * @return UserModel the user with the given email
     * @throws NoUserException when no user is found
     */
    public static function getUserByEmail(string $email): UserModel
    {
        $user = new UserModel();
        $user->loadByEmail($email);
        return $user;
    }

    /**
     * Load a UserModel from the database by uid
     * @param int $id the uid of the user to get
     * @return UserModel the user with the given uid
     * @throws NoUserException when no user is found
     */
    public static function getUserById(int $id): UserModel
    {
        $user = new UserModel();
        $user->loadByUid($id);
        return $user;
    }

    /**
     * Create a new user in the database, if it doesn't already exist
     * @param string $firstName the firstname of the user
     * @param string $lastName the lastname of the user
     * @param string $email the email of the user
     * @param string|null $phoneNumber the phone number of the user
     * @param string $password the password of the user
     * @param int $postalCode the postal code of the user
     * @param string $city the city of the user
     * @param string $street the street of the user
     * @param int $houseNr the house number of the user
     * @param string|null $bus the bus of the user
     * @param bool $isOwner whether the user is an owner
     * @return UserModel the created user
     * @throws InvalidInputException when an input is invalid
     */
    public static function createNewUser(string $firstName, string $lastName, string $email, ?string $phoneNumber, string $password,
                                         int    $postalCode, string $city, string $street, int $houseNr, ?string $bus, bool $isOwner = false): UserModel
    {
        // Formatting and validating the user input
        if ($bus == null || !isset($bus) || $bus == "") $bus = "/";
        self::formatUserInput($firstName, $lastName, $email, $phoneNumber, $password, $postalCode, $city, $street, $houseNr, $bus);
        self::validateUserInput($firstName, $lastName, $email, $phoneNumber, $password, $postalCode, $city, $street, $houseNr, $bus);
        if ($phoneNumber == "") $phoneNumber = null;
        $password = self::hashPassword($password);

        // Adding the address to the database
        $address = AddressModel::createNewAddress($postalCode, $city, $street, $houseNr, $bus);

        // Adding the user to the database
        if (DatabaseHandler::getInstance()->query(self::$Q_GET_USER_BY_EMAIL, [$email]) == null)
            DatabaseHandler::getInstance()->query(self::$Q_CREATE_USER, [$email, $lastName, $firstName, $phoneNumber, $password, $address->getAddressKey()], false);

        // Loading the user from the database
        $user = new UserModel();

        try {
            // Load the user
            $user->loadByEmail($email);

            // Create the owner if needed
            if ($isOwner) {
                FoodtruckWorkerModel::createNewWorker($user->getUid());
                $user->worker = FoodtruckWorkerModel::getWorkerByUserModel($user);

                FoodtruckOwnerModel::createNewOwner($user->getUid());
                $user->owner = FoodtruckOwnerModel::getOwnerByUserModel($user);
            }
        } catch (NoUserException $e) {
            // This should never happen because we just created the user
            throw new Exception("User was not created");
        } catch (NoWorkerException $e) {
            // This should never happen because we just created the worker
            throw new Exception("Worker was not created");
        } catch (NoOwnerException $e) {
            // This should never happen because we just created the owner
            throw new Exception("Owner was not created");
        }

        return $user;
    }

    /**
     * Update the data of the user in the database
     * @param string $firstName the firstname of the user
     * @param string $lastName the lastname of the user
     * @param string|null $phoneNumber the phone number of the user
     * @param int $postalCode the postal code of the user
     * @param string $city the city of the user
     * @param string $street the street of the user
     * @param int $houseNr the house number of the user
     * @param string|null $bus the bus of the user
     * @return $this UserModel the updated user
     * @throws InvalidInputException when an input is invalid
     */
    public function updateData(string $firstName, string $lastName, ?string $phoneNumber,
                               int    $postalCode, string $city, string $street, int $houseNr, ?string $bus): UserModel
    {
        // Formatting and validating the user input
        if ($bus == null || !isset($bus) || $bus == "") $bus = "/";
        self::formatUserInput($firstName, $lastName, $this->email, $phoneNumber, $this->password, $postalCode, $city, $street, $houseNr, $bus);
        self::validateUserInput($firstName, $lastName, $this->email, $phoneNumber, $this->password, $postalCode, $city, $street, $houseNr, $bus);
        if ($phoneNumber == "") $phoneNumber = null;

        // If nothing changed, just return the current user
        if ($this->equals($firstName, $lastName, $phoneNumber, $postalCode, $city, $street, $houseNr, $bus, $this->password, $this->email))
            return $this;

        // Else create the address if it changed
        if (!$this->address->equals($postalCode, $city, $street, $houseNr, $bus))
            $this->address = AddressModel::createNewAddress($postalCode, $city, $street, $houseNr, $bus);

        // Then update the user
        DatabaseHandler::getInstance()->query(self::$Q_UPDATE_USER, [$lastName, $firstName, $phoneNumber, $this->password, $this->address->getAddressKey(), $this->uid], false);

        // Then reload the user
        try {
            $this->loadByUid($this->uid);
        } catch (NoUserException $e) {
            // This should never happen because we just updated the user
            throw new Exception("User was not updated");
        }
        return $this;
    }

    /**
     * Check if the user is equal to the given data
     * @param string $firstName the firstname of the user
     * @param string $lastName the lastname of the user
     * @param string|null $phoneNumber the phone number of the user
     * @param int $postalCode the postal code of the user
     * @param string $city the city of the user
     * @param string $street the street of the user
     * @param int $houseNr the house number of the user
     * @param string $bus the bus of the user
     * @param string $password the password of the user
     * @param string $email the email of the user
     * @return bool
     */
    public function equals(string $firstName, string $lastName, ?string $phoneNumber, int $postalCode, string $city,
                           string $street, int $houseNr, string $bus, string $password, string $email): bool
    {
        return $this->firstName == $firstName &&
            $this->lastName == $lastName &&
            $this->phoneNumber == $phoneNumber &&
            $this->address->equals($postalCode, $city, $street, $houseNr, $bus) &&
            $this->password == $password &&
            $this->email == $email;
    }

    /**
     * Format the user input to be consistent across the database
     * @param string $firstName the firstname of the user
     * @param string $lastName the lastname of the user
     * @param string $email the email of the user
     * @param string|null $phoneNumber the phone number of the user
     * @param string $password the password of the user
     * @param int $postalCode the postal code of the user
     * @param string $city the city of the user
     * @param string $street the street of the user
     * @param int $houseNr the house number of the user
     * @param string|null $bus the bus of the user
     * @return void
     * @post the input variables are formatted according to the database standards
     */
    private static function formatUserInput(string &$firstName, string &$lastName, string &$email, ?string &$phoneNumber, string &$password,
                                            int    &$postalCode, string &$city, string &$street, int &$houseNr, ?string &$bus)
    {
        $firstName = ucwords(strtolower($firstName));
        $lastName = ucwords(strtolower($lastName));
        $email = strtolower($email);

        if ($phoneNumber != null && $phoneNumber != "") {
            $phoneNumber = str_replace(" ", "", $phoneNumber);
            $phoneNumber = str_replace("-", "", $phoneNumber);
            $phoneNumber = str_replace("/", "", $phoneNumber);
            $phoneNumber = str_replace(".", "", $phoneNumber);
        }

        $city = strtoupper($city);
        $street = ucwords(strtolower($street));
        $bus = strtoupper($bus);
    }

    /**
     * Validate the user input
     * @param string $firstName the firstname of the user | only letters
     * @param string $lastName the lastname of the user | only letters and spaces
     * @param string $email the email of the user | must be something like example@gmail.com
     * @param string|null $phoneNumber the phone number of the user | only numbers or a + in the beginning
     * @param string $password the password of the user
     * @param int $postalCode the postal code of the user
     * @param string $city the city of the user | only letters
     * @param string $street the street of the user | only letters and dashes
     * @param int $houseNr
     * @param string $bus the bus of the user
     * @return void
     * @throws InvalidInputException when the input is invalid
     */
    private static function validateUserInput(string $firstName, string $lastName, string $email, ?string $phoneNumber, string $password,
                                              int    $postalCode, string $city, string $street, int $houseNr, string $bus)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            throw new InvalidInputException("email", "email must be something like example@gmail.com");

        if (!preg_match("/^[a-zA-Zéçèà]*$/", $firstName))
            throw new InvalidInputException("firstName", "a firstname can only contain letters");

        if (!preg_match("/^[a-zA-Z éçèà]*$/", $lastName))
            throw new InvalidInputException("lastName", "a lastname can only contain letters and spaces");

        if ($phoneNumber != null) {
            if (!preg_match("/^\+?[0-9]*$/", $phoneNumber))
                throw new InvalidInputException("phoneNumber", "a phone number can only contain numbers or a + in the beginning");
            if (strlen($phoneNumber) < 9)
                throw new InvalidInputException("phoneNumber", "a phone number must be at least 9 characters long");
            if (strlen($phoneNumber) > 12)
                throw new InvalidInputException("phoneNumber", "a phone number can't be longer than 12 characters");
        }

        if (!preg_match("/^[a-zA-Z\-]*$/", $city))
            throw new InvalidInputException("city", "a city can only contain letters and dashes");

        if (!preg_match("/^[a-zA-Z\-]*$/", $street))
            throw new InvalidInputException("street", "a street can only contain letters and dashes");
    }

    /**
     * Hash a password
     * @param string $password the password to hash
     * @return string the hashed password
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Load the user from the database
     * @param $result array the result from the database query
     * @return void
     */
    private function loadUser(array $result)
    {
        // Loading the user data from the result
        $this->uid = $result[0]->uid;
        $this->email = $result[0]->email;
        $addressKey = $result[0]->addressKey;
        $this->lastName = $result[0]->lastName;
        $this->firstName = $result[0]->firstName;
        $this->phoneNumber = $result[0]->phoneNumber;
        $this->password = $result[0]->password;

        $this->address = new AddressModel();
        $this->address->loadByKey($addressKey);

        // Load the worker and owner if they exist
        try {
            $this->worker = FoodtruckWorkerModel::getWorkerByUserModel($this);
        } catch (NoWorkerException|NoDataException $ignored) { }

        try {
            $this->owner = FoodtruckOwnerModel::getOwnerByUserModel($this);
        } catch (NoOwnerException $ignored) { }
    }

    /**
     * Check if the password is correct
     * @param string $password the password to check
     * @return bool whether the password is correct
     * @pre a user is loaded in the model
     */
    public function isCorrectPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    /* Queries */
    private static string $Q_GET_USER_BY_UID = "SELECT * FROM User WHERE uid = ?";
    private static string $Q_GET_USER_BY_EMAIL = "SELECT * FROM User WHERE email = ?";
    private static string $Q_GET_WORKERS_BY_UID = "SELECT * FROM FoodtruckWorker WHERE uid = ?";

    private static string $Q_CREATE_USER = "INSERT INTO User (email, lastName, firstName, phoneNumber, password, addressKey) VALUES (?, ?, ?, ?, ?, ?)";

    private static string $Q_UPDATE_USER = "UPDATE User SET lastName = ?, firstName = ?, phoneNumber = ?, password = ?, addressKey = ? WHERE uid = ?";

    /* Getters */
    public function getUid(): ?int
    {
        return $this->uid;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getAddress(): ?AddressModel
    {
        return $this->address;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * Get the full name of the user
     * @return string|null the full name of the user
     */
    public function getFullName(): ?string
    {
        return $this->firstName . " " . $this->lastName;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    /**
     * Get the phone number formatted to be more readable
     * @return string|null the phone number formatted to be more readable
     */
    public function getFormattedPhoneNumber(): ?string
    {
        $phoneNumber = $this->phoneNumber;
        $phoneNumber = substr_replace($phoneNumber, " ", 4, 0);
        $phoneNumber = substr_replace($phoneNumber, " ", 7, 0);
        $phoneNumber = substr_replace($phoneNumber, " ", 10, 0);
        return trim($phoneNumber);
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function isFoodtruckWorker(): ?bool
    {
        return $this->worker != null;
    }

    public function getWorker(): ?FoodtruckWorkerModel
    {
        return $this->worker;
    }

    public function isFoodtruckOwner(): ?bool
    {
        return $this->owner != null;
    }

    public function getOwner(): ?FoodtruckOwnerModel
    {
        return $this->owner;
    }

    /* Setters */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function setPhoneNumber(?string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    public function setWorker(?FoodtruckWorkerModel $worker): void
    {
        $this->worker = $worker;
    }

    public function setOwner(?FoodtruckOwnerModel $owner): void
    {
        $this->owner = $owner;
    }

    public function setAddress(?AddressModel $address): void
    {
        $this->address = $address;
    }
}