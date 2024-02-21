<?php

namespace App\Models;

use App\Database\DatabaseHandler;
use App\Exceptions\InvalidInputException;
use CodeIgniter\I18n\Time;
use CodeIgniter\Model;

class OpenTimeModel
{
    /* Attributes */
    private int $foodtruckKey;
    private ?string $day = null;
    private ?Time $fromTime = null;
    private ?Time $toTime = null;

    /* Initializers */
    public function __construct(int $foodtruckKey)
    {
        $this->foodtruckKey = $foodtruckKey;
    }

    /**
     * Get the open times by the given foodtruck key
     * @param int $foodtruckKey The key of the foodtruck
     * @return array The open times
     */
    public static function getOpenTimesByFoodtruckKey(int $foodtruckKey): array
    {
        $openTimes = [];
        $results = DatabaseHandler::getInstance()->query(self::$Q_GET_OPEN_TIMES_BY_FOODTRUCK_KEY, [$foodtruckKey]);
        foreach ($results as $row) {
            $openTime = new OpenTimeModel($foodtruckKey);
            $openTime->loadByQueryResult($row);
            $openTimes[] = $openTime;
        }
        return $openTimes;
    }

    /**
     * Load the open time by the given query result
     * @param $result
     */
    public function loadByQueryResult($result)
    {
        $this->day = $result->day;
        $this->fromTime = Time::parse($result->fromTime);
        $this->toTime = Time::parse($result->toTime);
    }

    /* Methods */
    /**
     * Set the data of the open time
     * @param OpenOnDataModel $openOnDataModel The data to set
     * @throws InvalidInputException When the time is invalid
     */
    public function setData(OpenOnDataModel $openOnDataModel): void
    {
        $this->day = $openOnDataModel->day;
        try {
            $this->fromTime = Time::parse($openOnDataModel->openTime);
            $this->toTime = Time::parse($openOnDataModel->closeTime);
        } catch (\Exception $e) {
            throw new InvalidInputException("Open On Time", "Open on: Invalid time");
        }
    }

    /**
     * Delete the open time from the database
     */
    public function delete(): void
    {
        DatabaseHandler::getInstance()->query(self::$Q_DELETE_OPEN_TIME, [$this->foodtruckKey, $this->day, $this->fromTime, $this->toTime], false);
    }

    /**
     * Save the open time to the database with the current data
     * @throws InvalidInputException When the model is invalid
     */
    public function save(): void
    {
        $this->validateModel();
        DatabaseHandler::getInstance()->query(self::$Q_CREATE_OPEN_TIME, [$this->foodtruckKey, $this->day, $this->fromTime, $this->toTime], false);
    }

    /**
     * Format all the data inside the model to the database standards
     * @return void
     */
    private function formatModel(): void
    {
        if ($this->day != null)
            $this->day = $this->getDayInFormat($this->day);
    }

    /**
     * Get the day in the correct format
     * @param string $rawDay The raw day string
     * @return string|null The day in the correct format | null if it cannot be formatted
     */
    private function getDayInFormat(string $rawDay): ?string
    {
        $rawDay = strtoupper($rawDay);

        if ($rawDay == "MONDAY" || $rawDay == "MON")
            return "MON";
        if ($rawDay == "TUESDAY" || $rawDay == "TUE")
            return "TUE";
        if ($rawDay == "WEDNESDAY" || $rawDay == "WED")
            return "WED";
        if ($rawDay == "THURSDAY" || $rawDay == "THU")
            return "THU";
        if ($rawDay == "FRIDAY" || $rawDay == "FRI")
            return "FRI";
        if ($rawDay == "SATURDAY" || $rawDay == "SAT")
            return "SAT";
        if ($rawDay == "SUNDAY" || $rawDay == "SUN")
            return "SUN";

        return null;
    }

    /**
     * Check if the open time is equal to the given open time
     * @param OpenTimeModel $openTime The open time to check
     * @return bool True if the open times are equal | False if the open times aren't equal
     */
    public function equals(OpenTimeModel $openTime): bool
    {
        return $this->day == $openTime->day &&
            $this->fromTime == $openTime->fromTime &&
            $this->toTime == $openTime->toTime;
    }

    /**
     * Validate the model
     * @throws InvalidInputException When the model is invalid
     */
    public function validateModel(): void
    {
        $this->formatModel();

        if ($this->day == null || $this->fromTime == null || $this->toTime == null)
            throw new InvalidInputException("Open On", "Open on: Invalid Open On information");

        if ($this->fromTime > $this->toTime)
            throw new InvalidInputException("Open On", "Open on: The from time must be before the to time");

        if ($this->day != "MON" && $this->day != "TUE" && $this->day != "WED" && $this->day != "THU" && $this->day != "FRI" && $this->day != "SAT" && $this->day != "SUN")
            throw new InvalidInputException("Open On", "Open on: Invalid day" );
    }

    /**
     * Check if the open time overlaps with the given open time
     * @param OpenTimeModel $openTime The open time to check
     * @return bool True if the open times overlap | False if the open times don't overlap
     */
    public function overlaps(OpenTimeModel $openTime): bool
    {
        if ($this->day != $openTime->day)
            return false;

        if ($this->fromTime < $openTime->fromTime && $this->toTime < $openTime->fromTime)
            return false;

        if ($this->fromTime > $openTime->toTime && $this->toTime > $openTime->toTime)
            return false;

        return true;
    }

    /* Queries */
    private static string $Q_GET_OPEN_TIMES_BY_FOODTRUCK_KEY = "SELECT * FROM OpenTime WHERE foodtruck = ?";
    private static string $Q_CREATE_OPEN_TIME = "INSERT INTO OpenTime (foodtruck, day, fromTime, toTime) VALUES (?, ?, ?, ?)";
    private static string $Q_DELETE_OPEN_TIME = "DELETE FROM OpenTime WHERE foodtruck = ? AND day = ? AND fromTime = ? AND toTime = ?";

    /* Getters */
    public function getDay(): ?string
    {
        return $this->day;
    }

    public function getDayLong(): ?string
    {
        switch ($this->day) {
            case "MON":
                return "Monday";
            case "TUE":
                return "Tuesday";
            case "WED":
                return "Wednesday";
            case "THU":
                return "Thursday";
            case "FRI":
                return "Friday";
            case "SAT":
                return "Saturday";
            case "SUN":
                return "Sunday";
            default:
                return null;
        }
    }

    public function getFromTime(): ?Time
    {
        return $this->fromTime;
    }

    public function getToTime(): ?Time
    {
        return $this->toTime;
    }

}