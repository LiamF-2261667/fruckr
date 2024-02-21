<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Exceptions\InvalidInputException;
use App\Exceptions\NoDataException;
use App\Models\FoodtruckModel;
use App\Models\SearchModel;
use CodeIgniter\HTTP\RedirectResponse;

class SearchController extends BaseController
{
    /* Attributes */
    private SearchModel $searchModel;

    /* Routes */
    public static string $INDEX_ROUTE = "/search";
    public static string $SEARCH_ROUTE = "/search/search";

    /* Constructor */
    public function __construct()
    {
        // Create the search model
        $this->searchModel = new SearchModel();
    }

    /* Methods */
    public function index()
    {
        $this->unloadUnnecessaryData();

        $data = [];

        // Get search data if given directly
        $searchData = request()->getGet('searchBar');
        $data["searchData"] = $searchData;

        try {
            // Get the search results
            $data["searchResults"] = $this->searchModel->getSearchResults($searchData);
        }
        catch (NoDataException|InvalidInputException $e) {
            $data["searchResults"] = [];
            $data["error"] = $e->getMessage();
        }

        // Redirect the user
        return $this->viewingPage('Search', $data);
    }

    /**
     * AJAX POST FUNCTION: Search for a foodtruck
     */
    public function search()
    {
        try {
            // Get the searchData
            $searchData = request()->getJsonVar('searchData');

            // Get the search results
            $searchResults = $this->searchModel->getSearchResults($searchData);

            // Return the response
            $this->sendAjaxResponse(json_encode(array("success" => true, "results" => $searchResults)));
        }
        catch (\Exception $ex) {
            $this->sendAjaxResponse(json_encode(array("success" => false, "error" => $ex->getMessage())));
        }
    }
}