<?php

namespace App\Models;

use App\Exceptions\InvalidInputException;
use App\Exceptions\NoDataException;

class SearchType {
    public static int $NAME = 0;
    public static int $TAG = 1;
    public static int $CITY = 2;
}

class SearchModel
{
    /**
     * Validate the search data
     * @throws InvalidInputException The search data is invalid
     */
    private function validateSearchData(?string $searchData): void
    {
        if ($searchData === null || trim($searchData) === "")
            throw new InvalidInputException("searchbar", "Please fill in the search bar");

        $regex = "/^[a-zA-Z éçèà\-0-9]+$/";
        if (preg_match($regex, $searchData) !== 1)
            throw new InvalidInputException("searchbar", "The search can only contain letters, spaces, numbers and dashes");
    }

    /**
     * Get the search results
     * @param string|null $searchData The search data
     * @return array The search results
     * @throws NoDataException The search results are empty
     * @throws InvalidInputException The search data is invalid
     */
    public function getSearchResults(?string $searchData): array
    {
        // Validate the search data
        $this->validateSearchData($searchData);

        // Get the foodtrucks
        $foodtrucks = array_merge(
            $this->getFoodtrucksByName($searchData),
            $this->getFoodtrucksByTag($searchData),
            $this->getFoodtrucksByCity($searchData)
        );

        // Check if the foodtrucks are set
        if (count($foodtrucks) == 0)
            throw new NoDataException("No foodtrucks found");

        // Return the search results
        return $this->foodtrucksToSearchResults($foodtrucks);
    }

    /**
     * Get the recommended foodtrucks
     * @param int $limit The maximum amount of foodtrucks to return
     * @return array The recommended foodtrucks
     * @throws NoDataException No foodtrucks found
     */
    public function getRecommendedFoodtrucks(int $limit): array
    {
        $foodtrucks = $this->getFoodtruckByRating($limit);

        // Check if the foodtrucks are set
        if (count($foodtrucks) == 0)
            throw new NoDataException("No foodtrucks found");

        // Return the search results
        return $this->foodtrucksToSearchResults($foodtrucks);
    }

    /**
     * Get the foodtrucks by search type
     * @param int $type The search type
     * @param string $searchData The search data
     * @return array The foodtrucks
     * @throws NoDataException No foodtrucks found
     * @throws InvalidInputException The search data is invalid
     */
    public function getFoodtrucksBySearchType(int $type, string $searchData): array
    {
        // Validate the search data
        $this->validateSearchData($searchData);

        $foodtrucks = [];

        switch ($type) {
            case SearchType::$NAME:
                $foodtrucks = $this->getFoodtrucksByName($searchData);
                break;
            case SearchType::$TAG:
                $foodtrucks = $this->getFoodtrucksByTag($searchData);
                break;
            case SearchType::$CITY:
                $foodtrucks = $this->getFoodtrucksByCity($searchData);
        }

        // Check if the foodtrucks are set
        if (count($foodtrucks) == 0)
            throw new NoDataException("No foodtrucks found");

        // Return the search results
        return $this->foodtrucksToSearchResults($foodtrucks);
    }

    private function getFoodtrucksByName(string $searchData): array
    {
        try {
            return FoodtruckModel::getFoodtrucksByName('%' . $searchData . '%');
        }
        catch (NoDataException $e) {
            return [];
        }
    }

    private function getFoodtruckByRating(int $limit): array
    {
        try {
            return FoodtruckModel::getFoodtruckByRating($limit);
        }
        catch (NoDataException $e) {
            return [];
        }
    }

    private function getFoodtrucksByTag(string $searchData): array
    {
        try {
            return FoodtruckModel::getFoodtrucksByTag('%' . $searchData . '%');
        }
        catch (NoDataException $e) {
            return [];
        }
    }

    private function getFoodtrucksByCity(string $searchData): array
    {
        try {
            return FoodtruckModel::getFoodtrucksByCity('%' . $searchData . '%');
        }
        catch (NoDataException $e) {
            return [];
        }
    }

    /**
     * Convert the foodtrucks to search results
     * @param array $foodtrucks The foodtrucks
     * @return array The search results
     */
    private function foodtrucksToSearchResults(array $foodtrucks): array
    {
        // Get the search results
        $searchResults = [];
        foreach ($foodtrucks as $foodtruck) {
            // Add the foodtruck to the search results
            $searchResults[] = $foodtruck->getThumbnailString();
        }

        // Return the search results
        return $searchResults;
    }
}