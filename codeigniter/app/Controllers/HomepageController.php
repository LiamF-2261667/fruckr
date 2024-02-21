<?php

namespace App\Controllers;

use App\Exceptions\InvalidInputException;
use App\Exceptions\NoDataException;
use App\Models\SearchModel;
use App\Models\SearchType;
use App\Models\UserModel;

class HomepageController extends BaseController
{
    /* Routes */
    public static string $INDEX_ROUTE = "/homepage";

    private static int $MAX_FOODTRUCKS_PER_CATEGORY = 5;

    private SearchModel $searchModel;

    /* Constructor */
    public function __construct()
    {
        // Create the search model
        $this->searchModel = new SearchModel();
    }

    /* Methods */
    public function index(): string
    {
        $this->unloadUnnecessaryData();
        return $this->viewingPage('Homepage', $this->getPageData());
    }

    private function getPageData(): array
    {
        $currUser = $this->session->get('currUser');

        $data = [];

        // Add recommended & near you foodtrucks
        $data["recommended"] = $this->recommendedFoodtrucks();
        if ($currUser !== null) $data["nearYou"] = $this->foodtrucksNearUser($currUser);

        return $data;
    }

    private function formatFoodtruckArray(array $foodtrucks): array
    {
        return array_slice($foodtrucks, 0, self::$MAX_FOODTRUCKS_PER_CATEGORY);
    }

    private function recommendedFoodtrucks(): array
    {
        // Get the foodtrucks
        try {
            return $this->formatFoodtruckArray($this->searchModel->getRecommendedFoodtrucks(self::$MAX_FOODTRUCKS_PER_CATEGORY));
        }
        catch (NoDataException $e) {
            return $this->formatFoodtruckArray([]);
        }
    }

    private function foodtrucksNearUser($user): array
    {
        // Get the foodtrucks
        try {
            return $this->formatFoodtruckArray($this->searchModel->getFoodtrucksBySearchType(SearchType::$CITY, $user->getAddress()->getCity()));
        }
        catch (NoDataException | InvalidInputException $e) {
            return $this->formatFoodtruckArray([]);
        }
    }
}
