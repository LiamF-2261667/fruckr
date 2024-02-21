<?php

namespace App\Models;

use App\Database\DatabaseHandler;
use App\Exceptions\InvalidInputException;
use App\Exceptions\NoDataException;

class AddressModel
{
    /* Attributes */
    private ?int $postalCode = null;
    private ?string $city = null;
    private ?string $street = null;
    private ?int $houseNr = null;
    private ?string $bus = null;
    private ?int $addressKey = null;

    /* Methods */
    /**
     * Loads the address with the given key
     * @param int $addressKey The key of the address to load
     * @return AddressModel The loaded address
     */
    public static function getAddressById(int $addressKey): AddressModel
    {
        $address = new AddressModel();
        $address->loadByKey($addressKey);
        return $address;
    }

    /**
     * Loads the address with the given data
     * @param int $postalCode The postal code of the address to load
     * @param string $city The city of the address to load
     * @param string $street The street of the address to load
     * @param int $houseNr The house number of the address to load
     * @param string $bus The bus of the address to load
     * @return AddressModel The loaded address
     * @throws NoDataException When no address is found with the given data
     */
    public static function getAddressByData(int $postalCode, string $city, string $street, int $houseNr, string $bus = "/"): AddressModel
    {
        $address = new AddressModel();
        $address->loadByData($postalCode, $city, $street, $houseNr, $bus);
        return $address;
    }

    /**
     * Loads the address with the given key
     * @param int $addressKey The key of the address to load
     * @return void
     */
    public function loadByKey(int $addressKey)
    {
        $result = DatabaseHandler::getInstance()->query(self::$Q_GET_ADDRESS_BY_KEY, [$addressKey]);
        $this->load($result);
    }

    /**
     * Loads the address with the given data
     * @param int $postalCode The postal code of the address to load
     * @param string $city The city of the address to load
     * @param string $street The street of the address to load
     * @param int $houseNr The house number of the address to load
     * @param string $bus The bus of the address to load
     * @throws NoDataException When no address is found with the given data
     */
    public function loadByData(int $postalCode, string $city, string $street, int $houseNr, string $bus = "/")
    {
        $result = DatabaseHandler::getInstance()->query(self::$Q_GET_ADDRESS_BY_DATA, [$postalCode, $street, $houseNr, $bus]);
        if ($result == null)
            throw new NoDataException("Address", "No address found with the given data");
        else
            $this->load($result);
    }

    /**
     * Creates a new address with the given data
     * @param int $postalCode The postal code of the address to create
     * @param string $city The city of the address to create
     * @param string $street The street of the address to create
     * @param int $houseNr The house number of the address to create
     * @param string|null $bus The bus of the address to create
     * @return AddressModel
     * @throws InvalidInputException When the given data is invalid
     */
    public static function createNewAddress(int $postalCode, string $city, string $street, int $houseNr, string $bus = "/"): AddressModel
    {
        self::format($postalCode, $city, $street, $houseNr, $bus);
        self::validate($postalCode, $city, $street, $houseNr, $bus);

        if (DatabaseHandler::getInstance()->query(self::$Q_GET_CITY_BY_POSTAL_CODE, [$postalCode]) == null)
            DatabaseHandler::getInstance()->query(self::$Q_CREATE_CITY, [$postalCode, $city], false);

        if (DatabaseHandler::getInstance()->query(self::$Q_GET_ADDRESS_BY_DATA, [$postalCode, $street, $houseNr, $bus]) == null)
            DatabaseHandler::getInstance()->query(self::$Q_CREATE_ADDRESS, [$postalCode, $street, $houseNr, $bus], false);

        $address = new AddressModel();
        try {
            $address->loadByData($postalCode, $city, $street, $houseNr, $bus);
        } catch (NoDataException $ignored) {
            // This should never happen
        }
        return $address;
    }

    /**
     * Format the given data
     * @param int $postalCode The postal code to format
     * @param string $city The city to format
     * @param string $street The street to format
     * @param int $houseNr The house number to format
     * @param string|null $bus The bus to format
     * @return void
     */
    private static function format(int& $postalCode, string& $city, string& $street, int& $houseNr, ?string& $bus): void
    {
        $city = ucwords(strtolower($city));
        $street = ucwords(strtolower($street));
        if ($bus !== null) {
            if ($bus !== "")
                $bus = strtoupper($bus);
            else
                $bus = "/";
        }
    }

    /**
     * Validate the given data
     * @throws InvalidInputException When the data is invalid
     */
    public static function validate(int $postalCode, string $city, string $street, int $houseNr, string $bus = "/"): void
    {
        self::format($postalCode, $city, $street, $houseNr, $bus);

        if (!preg_match("/^[a-zA-Z\-]*$/", $city))
            throw new InvalidInputException("city", "a city can only contain letters and dashes");

        if (!preg_match("/^[a-zA-Z\-]*$/", $street))
            throw new InvalidInputException("street", "a street can only contain letters and dashes");
    }

    /**
     * Check if the address is equal to the given data
     * @param int $postalCode The postal code to check
     * @param string $city The city to check
     * @param string $street The street to check
     * @param int $houseNr The house number to check
     * @param string $bus The bus to check
     * @return bool
     */
    public function equals(int $postalCode, string $city, string $street, int $houseNr, string $bus = "/"): bool
    {
        return $this->postalCode == $postalCode &&
            $this->city == $city &&
            $this->street == $street &&
            $this->houseNr == $houseNr &&
            $this->bus == $bus;
    }

    /**
     * Load the data from an address query result
     * @param $result array The result of the query
     * @return void
     */
    private function load(array $result)
    {
        $this->postalCode = $result[0]->postalCode;
        $this->city = $result[0]->city;
        $this->street = $result[0]->street;
        $this->houseNr = $result[0]->houseNr;
        $this->bus = $result[0]->bus;
        $this->addressKey = $result[0]->addressKey;
    }

    /* QUERIES */
    private static string $Q_GET_CITY_BY_POSTAL_CODE = "SELECT postalCode, name as city FROM City WHERE postalCode = ?";
    private static string $Q_GET_ADDRESS_BY_KEY = "SELECT postalCode, street, houseNr, busNr as bus, addressKey, name as city FROM Address NATURAL JOIN City WHERE addressKey = ?";
    private static string $Q_GET_ADDRESS_BY_DATA = "SELECT postalCode, street, houseNr, busNr as bus, addressKey, name as city FROM Address NATURAL JOIN City WHERE postalCode = ? AND street = ? AND houseNr = ? AND busNr = ?";

    private static string $Q_CREATE_CITY = "INSERT INTO City (postalCode, name) VALUES (?, ?)";
    private static string $Q_CREATE_ADDRESS = "INSERT INTO Address (postalCode, street, houseNr, busNr) VALUES (?, ?, ?, ?);";

    /* Getters */
    public function getPostalCode(): ?int
    {
        return $this->postalCode;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function getHouseNr(): ?int
    {
        return $this->houseNr;
    }

    public function getBus(): ?string
    {
        return $this->bus;
    }

    public function getAddressKey(): ?int
    {
        return $this->addressKey;
    }

    public function toString(): string {
        if ($this->bus !== "/")
            return $this->street . " " . $this->houseNr . " " . $this->bus . ", " . $this->postalCode . " " . $this->city;
        else
            return $this->street . " " . $this->houseNr . ", " . $this->postalCode . " " . $this->city;
    }

    public function toShortString(): string {
        return $this->city . " " . $this->street;
    }

    public function toGoogleLink(): string {
        return "https://www.google.com/maps/place/" .
            $this->street . "+" .
            $this->houseNr . ",+" .
            $this->postalCode . "+" .
            ucwords(strtolower($this->city));
    }
}