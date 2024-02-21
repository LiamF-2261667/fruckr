<?php

namespace App\Controllers;

use App\Exceptions\InvalidOrderException;
use App\Exceptions\NoDataException;
use App\Models\FoodtruckModel;

class FoodtruckOrdersController extends BaseController
{
    /* Attributes */
    public static string $INDEX_ROUTE = "/foodtruck/(:num)/orders";
    public static string $SET_READY_ROUTE = "/foodtruck/orders/ready";
    public static string $SET_RECEIVED_ROUTE = "/foodtruck/orders/received";

    private ?FoodtruckModel $foodtruck = null;

    /* Methods */

    /**
     * View the orders of a foodtruck
     */
    public function index($foodtruckId = null)
    {
        // Check if an id was given, otherwise redirect
        if ($foodtruckId === null)
            return $this->defaultRedirect();

        $this->unloadUnnecessaryData();

        // Load the foodtruck
        $this->loadFoodtruck(intval($foodtruckId));
        $this->foodtruck->loadFoodItems();
        $this->session->set("currFoodtruck", $this->foodtruck);

        if ($this->foodtruck === null)
            return $this->viewingPage("OrdersLoadingError");

        // Check if the user is logged in & is a worker of the foodtruck
        $currUser = $this->session->get("currUser");
        if ($currUser === null || !$this->foodtruck->containsWorker($currUser->getUid()))
            return $this->defaultRedirect();

        // Load the orders
        try {
            $this->foodtruck->loadOrders();
        }
        catch (InvalidOrderException $e) {
            return $this->viewingPage("OrdersLoadingError");
        }

        // Show the correct page
        return $this->viewingPage("Foodtruck_Orders", $this->getData());
    }

    /**
     * Set an order as ready
     */
    public function setReady()
    {
        // Check if the foodtruck is loaded
        $this->foodtruck = $this->session->get("currFoodtruck");
        if ($this->foodtruck === null) {
            $this->sendAjaxResponse(json_encode(array("success" => false, "error" => "Foodtruck not loaded")));
            return;
        }

        // Check if an id was given
        $orderId = $this->request->getJsonVar("orderId");
        if ($orderId === null) {
            $this->sendAjaxResponse(json_encode(array("success" => false, "error" => "No order id given")));
            return;
        }

        // Get the current user
        $currUser = $this->session->get("currUser");

        // Try to ready the order
        try {
            $this->foodtruck->getOrder(intval($orderId))->setReady($currUser->getUid());
        }
        catch (InvalidOrderException | NoDataException $e) {
            $this->sendAjaxResponse(json_encode(array("success" => false, "error" => $e->getMessage())));
            return;
        }

        $this->sendAjaxResponse(json_encode(array("success" => true, "orderId" => $orderId, "ready" => true)));
    }

    /**
     * Set an order as received
     */
    public function setReceived()
    {
        // Check if the foodtruck is loaded
        $this->foodtruck = $this->session->get("currFoodtruck");
        if ($this->foodtruck === null) {
            $this->sendAjaxResponse(json_encode(array("success" => false, "error" => "Foodtruck not loaded")));
            return;
        }

        // Check if an id was given
        $orderId = $this->request->getJsonVar("orderId");
        if ($orderId === null) {
            $this->sendAjaxResponse(json_encode(array("success" => false, "error" => "No order id given")));
            return;
        }

        // Get the current user
        $currUser = $this->session->get("currUser");

        // Try to receive the order
        try {
            $this->foodtruck->getOrder(intval($orderId))->confirmOrder($currUser->getUid());
        }
        catch (InvalidOrderException | NoDataException $e) {
            $this->sendAjaxResponse(json_encode(array("success" => false, "error" => $e->getMessage())));
            return;
        }

        $this->sendAjaxResponse(json_encode(array("success" => true, "orderId" => $orderId, "received" => true)));
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
     * Get the data required for each page
     * @return array the data
     */
    private function getData() : array
    {
        return [
            'foodtruck' => $this->foodtruck,
            'orders' => $this->foodtruck->getOrders()
        ];
    }
}