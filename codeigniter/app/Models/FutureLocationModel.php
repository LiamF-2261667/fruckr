<?php

namespace App\Models;

use App\Database\DatabaseHandler;
use App\Exceptions\InvalidInputException;
use App\Exceptions\NoDataException;

class FutureLocationModel
{
    /* Attributes */
    private int $foodtruckId;
    private ?array $startDate = null;
    private ?AddressModel $address = null;

    /* Initializers */
    public function __construct(int $foodtruckId)
    {
        $this->foodtruckId = $foodtruckId;
    }

    public static function getFutureLocationsByFoodtruckId(int $foodtruckId): array
    {
        $result = DatabaseHandler::getInstance()->query(self::$Q_GET_FOODTRUCK_FUTURE_LOCATIONS, [$foodtruckId]);
        $futureLocations = [];
        foreach ($result as $futureLocation) {
            $futureLocationModel = new FutureLocationModel($foodtruckId);
            $futureLocationModel->loadByQueryResult($futureLocation);
            $futureLocations[] = $futureLocationModel;
        }
        return $futureLocations;
    }

    public function loadByQueryResult($result)
    {
        $this->startDate = date_parse($result->startDate);
        $this->address = AddressModel::getAddressById($result->addressKey);
    }

    /* Methods */
    /**
     * Set the data of the future location
     * @param FutureLocationDataModel $futureLocationDataModel The data to set
     */
    public function setData(FutureLocationDataModel $futureLocationDataModel): void
    {
        $this->startDate = date_parse($futureLocationDataModel->date);

        // Create New Address will automatically return the existing address if it already exists
        if ($futureLocationDataModel->bus === null)
            $this->address = AddressModel::createNewAddress($futureLocationDataModel->postalCode, $futureLocationDataModel->city, $futureLocationDataModel->street, $futureLocationDataModel->houseNr);
        else
            $this->address = AddressModel::createNewAddress($futureLocationDataModel->postalCode, $futureLocationDataModel->city, $futureLocationDataModel->street, $futureLocationDataModel->houseNr, $futureLocationDataModel->bus);
    }

    /**
     * Save the future location
     * @return void
     * @throws InvalidInputException When the model is invalid
     */
    public function save(): void
    {
        $this->validateModel();
        $addressKey = $this->address->getAddressKey();
        $startDate = $this->startDate["year"] . "-" . $this->startDate["month"] . "-" . $this->startDate["day"];
        DatabaseHandler::getInstance()->query(self::$Q_SAVE_FUTURE_LOCATION, [$this->foodtruckId, $addressKey, $startDate], false);
    }

    /**
     * Delete the future location
     * @return void
     */
    public function delete(): void
    {
        $addressKey = $this->address->getAddressKey();
        $startDate = $this->startDate["year"] . "-" . $this->startDate["month"] . "-" . $this->startDate["day"];
        DatabaseHandler::getInstance()->query(self::$Q_DELETE_FUTURE_LOCATION, [$this->foodtruckId, $addressKey, $startDate], false);
    }

    /**
     * Validate the model
     * @throws InvalidInputException When the model is invalid
     */
    public function validateModel(): void
    {
        if ($this->startDate == null || count($this->startDate) == 0 || !checkdate($this->startDate["month"], $this->startDate["day"], $this->startDate["year"]))
            throw new InvalidInputException("Future location", "Future location: Invalid start date");
        if ($this->address == null)
            throw new InvalidInputException("Future Location", "Future location: Invalid address");

        try {
            AddressModel::validate($this->address->getPostalCode(), $this->address->getCity(), $this->address->getStreet(), $this->address->getHouseNr(), $this->address->getBus());
        }
        catch (InvalidInputException $e) {
            throw new InvalidInputException("Future location", "Future location: " . $e->getMessage());
        }
    }

    /**
     * Check if the future location is equal to the given future location
     * @param FutureLocationModel $futureLocationModel The future location to check
     * @return bool True if the future locations are equal, false otherwise
     */
    public function equals(FutureLocationModel $futureLocationModel): bool
    {
        return $this->startDate == $futureLocationModel->startDate && $this->address->equals(
            $futureLocationModel->address->getPostalCode(),
            $futureLocationModel->address->getCity(),
            $futureLocationModel->address->getStreet(),
            $futureLocationModel->address->getHouseNr(),
            $futureLocationModel->address->getBus()
            );
    }

    /* Queries */
    private static string $Q_GET_FOODTRUCK_FUTURE_LOCATIONS = "SELECT * FROM FutureLocation WHERE foodtruck = ?";
    private static string $Q_SAVE_FUTURE_LOCATION = "INSERT INTO FutureLocation (foodtruck, addressKey, startDate) VALUES (?, ?, ?)";
    private static string $Q_DELETE_FUTURE_LOCATION = "DELETE FROM FutureLocation WHERE foodtruck = ? AND addressKey = ? AND startDate = ?";

    /* Getters */
    public function getStartDate(): ?string
    {
        return $this->startDate["year"] . "-" . $this->startDate["month"] . "-" . $this->startDate["day"];
    }

    public function getAddress(): ?AddressModel
    {
        return $this->address;
    }
}