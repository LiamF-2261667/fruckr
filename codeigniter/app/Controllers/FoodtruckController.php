<?php

namespace App\Controllers;

use App\DataObjects\FoodItemData;
use App\Exceptions\InvalidInputException;
use App\Exceptions\NoDataException;
use App\Models\BannerDataModel;
use App\Models\FoodItemModel;
use App\Models\FoodtruckDataModel;
use App\Models\FoodtruckModel;
use App\Models\FutureLocationDataModel;
use App\Models\OpenOnDataModel;

class FoodtruckController extends BaseController
{
    /* Attributes */
    private ?FoodtruckModel $foodtruck = null;

    /* Routes */
    public static string $INDEX_ROUTE = "/foodtruck";
    public static string $INDEX_WITH_ID_ROUTE = "/foodtruck/(:num)";
    public static string $SAVE_FOODTRUCK_ROUTE = "/foodtruck/save";
    public static string $CREATE_FOODTRUCK_PAGE_ROUTE = "/foodtruck/create";
    public static string $CREATE_FOODTRUCK_ROUTE = "/foodtruck/create/create";
    public static string $GET_EXTRA_FOOD_ITEM_INFO_ROUTE = "/foodtruck/getExtraFoodItemInfo";
    public static string $SAVE_FOOD_ITEM_ROUTE = "/foodtruck/saveFoodItem";
    public static string $CREATE_FOOD_ITEM_ROUTE = "/foodtruck/createFoodItem";
    public static string $DELETE_FOOD_ITEM_ROUTE = "/foodtruck/deleteFoodItem";

    /* Methods */
    public function index($foodtruckId = null)
    {
        // Check if an id was given, otherwise redirect
        if ($foodtruckId === null)
            return $this->defaultRedirect();

        $this->unloadUnnecessaryData();

        // Load the foodtruck
        $this->loadFoodtruck(intval($foodtruckId));

        if ($this->foodtruck === null)
            return $this->viewingPage('errors/html/NoFoodtruckError');

        $this->foodtruck->loadFoodItems();
        $this->session->set("currFoodtruck", $this->foodtruck);

        // Show the correct page
        return $this->viewingPage($this->getPage(), $this->getData());
    }

    /**
     * The create foodtruck page
     */
    public function createFoodtruckPage()
    {
        $this->unloadUnnecessaryData();

        // Check if the user is an owner
        $currUser = $this->session->get("currUser");
        if ($currUser === null || $currUser->isFoodtruckOwner() === false)
            return $this->defaultRedirect();

        // Show the correct page
        $data = ["ownerName" => $currUser->getFullName()];
        return $this->viewingPage('Foodtruck_Create', $data);
    }

    /**
     * AJAX POST FUNCTION: Get extra food item info
     * @pre the foodtruck is loaded
     */
    public function getExtraFoodItemInfo()
    {
        // Check if the foodtruck is loaded
        $this->foodtruck = $this->session->get("currFoodtruck");
        if ($this->foodtruck === null) {
            $this->sendAjaxResponse(json_encode(array("success" => false, "error" => "The foodtruck isn't loaded")));
            return;
        }

        // Get the inputs
        $foodItemName = request()->getJsonVar('foodItemName');
        if ($foodItemName === null) {
            $this->sendAjaxResponse(json_encode(array("success" => false, "error" => "No food item name was given")));
            return;
        }

        try {
            // Get the food item
            $foodItem = $this->foodtruck->getFoodItem($foodItemName);

            // Load the extra data
            $foodItem->loadExtraData();

            // Get the media
            $mediaHtmls = [];
            foreach ($foodItem->getMedia() as $media)
                $mediaHtmls[] = $media->toHtml("controls");

            // Send the response
            $this->sendAjaxResponse(json_encode(array(
                "success" => true,
                "media" => $mediaHtmls,
                "rating" => $foodItem->getRating(),
                "reviews" => []
            )));

            // Unload the extra data
            $foodItem->unloadExtraData();
        } catch (NoDataException $e) {
            $this->sendAjaxResponse(json_encode(array("success" => false, "error" => "The food item doesn't exist")));
            return;
        } catch (\Exception $e) {
            if (getenv('CI_ENVIRONMENT') == "development")
                $this->sendAjaxResponse(json_encode(array("success" => false, "error" => $e->getMessage())));
            else
                $this->sendAjaxResponse(json_encode(array("success" => false, "error" => "Something went wrong getting the food item data, please try again later!")));
        }
    }

    /**
     * AJAX POST FUNCTION: Get the food item info
     * @pre the foodtruck is loaded
     */
    public function saveFoodItem()
    {
        // Check if the foodtruck is loaded
        $this->foodtruck = $this->session->get("currFoodtruck");
        if ($this->foodtruck === null) {
            $this->sendAjaxResponse(json_encode(array("success" => false, "error" => "The foodtruck isn't loaded")));
            return;
        }

        // Get the inputs
        $oldFoodItemName = request()->getJsonVar('oldFoodItemName');
        if ($oldFoodItemName === null) {
            $this->sendAjaxResponse(json_encode(array("success" => false, "error" => "No old food item name was given")));
            return;
        }

        try {
            // Get the food item
            $foodItem = $this->foodtruck->getFoodItem($oldFoodItemName);

            // Get the data
            $foodItemData = FoodItemData::parse(request()->getJsonVar('foodItemData'));

            // Save the food item
            $foodItem->save($foodItemData);

            // Send the response
            $this->sendAjaxResponse(json_encode(array("success" => true, "foodItemHtml" => $foodItem->toHtml())));
        } catch (NoDataException $e) {
            $this->sendAjaxResponse(json_encode(array("success" => false, "error" => "The food item doesn't exist")));
            return;
        } catch (InvalidInputException $e) {
            $this->sendAjaxResponse(json_encode(array("success" => false, "error" => $e->getMessage())));
            return;
        } catch (\Exception $e) {
            if (getenv('CI_ENVIRONMENT') == "development")
                $this->sendAjaxResponse(json_encode(array("success" => false, "error" => $e->getMessage())));
            else
                $this->sendAjaxResponse(json_encode(array("success" => false, "error" => "Something went wrong saving the food item data, please try again later!")));
        }
    }

    /**
     * AJAX POST FUNCTION: Create a new food item
     */
    public function createFoodItem()
    {
        // Check if the foodtruck is loaded
        $this->foodtruck = $this->session->get("currFoodtruck");
        if ($this->foodtruck === null) {
            $this->sendAjaxResponse(json_encode(array("success" => false, "error" => "The foodtruck isn't loaded")));
            return;
        }

        try {
            // Get the data
            $foodItemData = FoodItemData::parse(request()->getJsonVar('foodItemData'));

            // Create the food item
            $newFoodItem = FoodItemModel::create($this->foodtruck->getId(), $foodItemData);
            $this->foodtruck->addFoodItem($newFoodItem);

            // Send the response
            $this->sendAjaxResponse(json_encode(array("success" => true, "foodItemHtml" => $newFoodItem->toHtml())));
        } catch (InvalidInputException $e) {
            $this->sendAjaxResponse(json_encode(array("success" => false, "error" => $e->getMessage())));
            return;
        } catch (\Exception $e) {
            if (getenv('CI_ENVIRONMENT') == "development")
                $this->sendAjaxResponse(json_encode(array("success" => false, "error" => $e->getMessage())));
            else
                $this->sendAjaxResponse(json_encode(array("success" => false, "error" => "Something went wrong creating the food item, please try again later!")));
        }
    }

    /**
     * AJAX POST FUNCTION: Delete a food item
     */
    public function deleteFoodItem()
    {
        // Check if the foodtruck is loaded
        $this->foodtruck = $this->session->get("currFoodtruck");
        if ($this->foodtruck === null) {
            $this->sendAjaxResponse(json_encode(array("success" => false, "error" => "The foodtruck isn't loaded")));
            return;
        }

        // Get the inputs
        $foodItemName = request()->getJsonVar('foodItemName');

        try {
            // Delete the food item
            $foodItem = $this->foodtruck->getFoodItem($foodItemName);
            $foodItem->delete();

            // Send the response
            $this->sendAjaxResponse(json_encode(array("success" => true)));
        }
        catch (NoDataException $e) {
            $this->sendAjaxResponse(json_encode(array("success" => false, "error" => "The food item doesn't exist")));
            return;
        }
        catch (\Exception $e) {
            if (getenv('CI_ENVIRONMENT') == "development")
                $this->sendAjaxResponse(json_encode(array("success" => false, "error" => $e->getMessage())));
            else
                $this->sendAjaxResponse(json_encode(array("success" => false, "error" => "Something went wrong deleting the food item, please try again later!")));
        }
    }

    /**
     * AJAX POST FUNCTION: Create a new foodtruck
     */
    public function createFoodtruck()
    {
        // Get the inputs
        try {
            $foodtruckDataModel = $this->getFoodtruckDataModelFromJsonVar(request()->getJsonVar('foodtruckDataModel'));

            // Check if the user is an owner
            $currUser = $this->session->get("currUser");
            if ($currUser === null || $currUser->isFoodtruckOwner() === false) {
                $this->sendAjaxResponse(json_encode(array("success" => false, "error" => "Only an owner can create foodtrucks")));
                return;
            }

            // Create the foodtruck
            $foodtruck = FoodtruckModel::create($foodtruckDataModel, $currUser->getOwner());

            // Store the foodtruck in the session
            $this->foodtruck = $foodtruck;

            // Send the response
            $this->sendAjaxResponse(json_encode(array("success" => true, "foodtruckId" => $foodtruck->getId())));
        }
        catch (\Exception $e) {
            if (getenv('CI_ENVIRONMENT') == "development")
                $this->sendAjaxResponse(json_encode(array("success" => false, "error" => $e->getMessage())));
            else
                $this->sendAjaxResponse(json_encode(array("success" => false, "error" => "Something went wrong saving the foodtruck data, please try again later!")));
            return;
        }
    }

    /**
     * AJAX POST FUNCTION: Save the foodtruck
     */
    public function saveFoodtruck()
    {
        // Check if the foodtruck is loaded
        $this->foodtruck = $this->session->get("currFoodtruck");
        if ($this->foodtruck === null) {
            $this->sendAjaxResponse(json_encode(array("success" => false, "error" => "The foodtruck isn't loaded")));
            return;
        }

        // Check if the user is the owner
        $currUser = $this->session->get("currUser");
        if ($currUser === null || $currUser->getUid() !== $this->foodtruck->getOwner()->getUid()) {
            $this->sendAjaxResponse(json_encode(array("success" => false, "error" => "Only the owner can save the foodtruck")));
            return;
        }

        // Get the inputs
        try {
            $foodtruckDataModel = $this->getFoodtruckDataModelFromJsonVar(request()->getJsonVar('foodtruckDataModel'));
        }
        catch (\Exception $e) {
            if (getenv('CI_ENVIRONMENT') == "development")
                $this->sendAjaxResponse(json_encode(array("success" => false, "error" => $e->getMessage())));
            else
                $this->sendAjaxResponse(json_encode(array("success" => false, "error" => "Something went wrong saving the foodtruck data, please try again later!")));
            return;
        }

        // Save the foodtruck
        $this->trySave($foodtruckDataModel);
    }

    /**
     * Try to save the foodtruck
     * @param FoodtruckDataModel $foodtruckData the data of the foodtruck
     * @post the foodtruck is saved
     */
    private function trySave(FoodtruckDataModel $foodtruckData)
    {
        try {
            // Save the foodtruck
            $this->foodtruck->save($foodtruckData);

            // Send the response
            $this->sendAjaxResponse(json_encode(array("success" => true)));
        }
        catch (\Exception $e) {
            $this->sendAjaxResponse(json_encode(array("success" => false, "error" => $e->getMessage())));
        }
    }

    /**
     * Read a jsonVar and try to return a FoodtruckDataModel
     * @param $jsonVar
     * @return FoodtruckDataModel the foodtruck data
     */
    private function getFoodtruckDataModelFromJsonVar($jsonVar): FoodtruckDataModel
    {
        // Create the foodtruck data
        $foodtruckData = new FoodtruckDataModel();
        $foodtruckData->profileImageBase64 = $jsonVar->profileImageBase64;
        $foodtruckData->name = $jsonVar->name;
        $foodtruckData->email = $jsonVar->information->email;
        $foodtruckData->phoneNumber = $jsonVar->information->phoneNumber;
        $foodtruckData->city = $jsonVar->information->city;
        $foodtruckData->street = $jsonVar->information->street;
        $foodtruckData->postalCode = $jsonVar->information->postalCode;
        $foodtruckData->houseNr = $jsonVar->information->houseNr;
        $foodtruckData->bus = $jsonVar->information->bus;
        $foodtruckData->extra = $jsonVar->extra;
        $foodtruckData->description = $jsonVar->description;
        $foodtruckData->tags = $jsonVar->tags;

        // Create the banners
        $foodtruckData->banners = [];
        foreach ($jsonVar->banners as $banner) {
            $bannerData = new BannerDataModel();
            $bannerData->base64 = $banner->base64;
            $bannerData->type = $banner->type;
            $bannerData->order = $banner->order;
            $foodtruckData->banners[] = $bannerData;
        }

        // Create the openOn
        $foodtruckData->openOn = [];
        foreach ($jsonVar->openOn as $openOn) {
            $openOnData = new OpenOnDataModel();
            $openOnData->day = $openOn->day;
            $openOnData->openTime = $openOn->from;
            $openOnData->closeTime = $openOn->to;
            $foodtruckData->openOn[] = $openOnData;
        }

        // Create the futureLocations
        $foodtruckData->futureLocations = [];
        foreach ($jsonVar->futureLocations as $futureLocation) {
            $futureLocationData = new FutureLocationDataModel();
            $futureLocationData->city = $futureLocation->city;
            $futureLocationData->street = $futureLocation->street;
            $futureLocationData->postalCode = $futureLocation->postalCode;
            $futureLocationData->houseNr = $futureLocation->houseNr;
            $futureLocationData->bus = $futureLocation->bus;
            $futureLocationData->date = $futureLocation->date;
            $foodtruckData->futureLocations[] = $futureLocationData;
        }

        return $foodtruckData;
    }

    /**
     * Load the foodtruck
     * @param int $foodtruckId the id of the foodtruck
     * @post $this->foodtruck is loaded from the database, or null if nothing is found
     */
    private function loadFoodtruck(int $foodtruckId)
    {
        // Try to load the foodtruck
        try {
            $this->foodtruck = FoodtruckModel::getFoodtruckById($foodtruckId);
        }

        // If nothing is found, set it to null
        catch (NoDataException $e) {
            $this->foodtruck = null;
        }
    }

    /**
     * Get the page to return
     * @return string the page name
     */
    private function getPage() : string
    {
        // Checking if there is a foodtruck
        if ($this->foodtruck === null)
            return 'errors/html/NoFoodtruckError';

        // Checking which page to return
        $currUser = $this->session->get("currUser");
        if ($currUser !== null) {
            // If the currUser is the owner, return the owner page
            if ($currUser->getUid() === $this->foodtruck->getOwner()->getUid())
                return 'Foodtruck_Owner';

            // If the currUser is a worker, return the worker page
            if ($this->foodtruck->containsWorker($currUser->getUid()))
                return 'Foodtruck_Worker';
        }

        // Else the currUser is a customer, return the customer page
        return 'Foodtruck_Customer';
    }

    /**
     * Get the data required for each page
     * @return array the data
     */
    private function getData() : array
    {
        // Each page requires the foodtruck
        $data = [];
        $data['foodtruck'] = $this->foodtruck;

        return $data;
    }
}