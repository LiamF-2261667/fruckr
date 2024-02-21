<?php

namespace App\Controllers;

use App\DataObjects\CartData;
use App\Exceptions\CartException;
use App\Exceptions\NoDataException;

class CartController extends BaseController
{
    /* Attributes */
    public static string $INDEX_ROUTE = "/cart";
    public static string $ADD_TO_CART_ROUTE = "/cart/add";
    public static string $REMOVE_FROM_CART_ROUTE = "/cart/remove";

    private ?CartData $currCart = null;

    /* Constructor */

    /* Methods */
    public function index()
    {
        $this->unloadUnnecessaryData();

        // Load the cart from the user
        $this->loadCart();

        return $this->viewingPage('Cart', $this->getViewData());
    }

    /**
     * AJAX FUNCTION: Adds a food item to the cart
     */
    public function addToCart()
    {
        // Get the foodtruck id, food item id and amount
        $foodtruckId = request()->getJsonVar('foodtruckId');
        $foodName = request()->getJsonVar('foodName');
        $amount = request()->getJsonVar('amount');

        // Check if all the fields are filled
        if ($foodtruckId == null || !isset($foodtruckId) ||
            $foodName == null    || !isset($foodName)    ||
            $amount == null      || !isset($amount)      || $amount <= 0) {
            $this->sendAjaxResponse(json_encode(array("success" => false, "error" => "No foodtruck id, food name or amount was given")));
            return;
        }

        // Load the cart from the user
        $this->loadCart();

        // Add the food item to the cart
        try {
            $this->currCart->addFoodItem($foodtruckId, $foodName, $amount);
        }
        catch (CartException|NoDataException $e) {
            $this->sendAjaxResponse(json_encode(array("success" => false, "error" => $e->getMessage())));
            return;
        }

        // Save the cart to the user
        $this->saveCart($this->currCart);

        // Return the new cart
        $this->sendAjaxResponse(json_encode(array("success" => true, "foodItemName" => $foodName, "amount" => $amount)));
    }

    /**
     * AJAX FUNCTION: Removes a food item from the cart
     */
    public function removeFromCart()
    {
        // Get the food item id
        $foodName = request()->getJsonVar('cartItemName');

        // Check if all the fields are filled
        if ($foodName == null || !isset($foodName)) {
            $this->sendAjaxResponse(json_encode(array("success" => false, "error" => "No food name was given")));
            return;
        }

        // Load the cart from the user
        $this->loadCart();

        // Remove the food item from the cart
        try {
            $this->currCart->removeFoodItem($foodName);
        }
        catch (CartException $e) {
            $this->sendAjaxResponse(json_encode(array("success" => false, "error" => $e->getMessage())));
            return;
        }

        // Save the cart to the user
        $this->saveCart($this->currCart);

        // Return the new cart
        $this->sendAjaxResponse(json_encode(array(
            "success" => true,
            "cartItemName" => $foodName,
            "totalPrice" => $this->currCart->getFormattedTotalPriceSum(),
            "totalItemCount" => $this->currCart->getTotalItemCount()
        )));
    }

    /**
     * Get the data to view the cart page
     * @return array The data to view the cart page
     */
    private function getViewData(): array
    {
        $data = [];

        // Add the cart
        $data["cart"] = $this->currCart;

        return $data;
    }

    /**
     * Load the cart from the user into the controller
     * @post $this->currCart is set
     */
    private function loadCart(): void
    {
        $this->currCart = $this->session->get('cart');

        // Create an empty cart if the user doesn't have one yet
        if ($this->currCart == null) {
            $this->currCart = new CartData();
            $this->saveCart($this->currCart);
        }
    }

    /**
     * Save the cart in the session data
     * @param CartData|null $cart The cart to save
     */
    private function saveCart(?CartData $cart): void
    {
        if ($cart == null)
            $this->session->remove('cart');
        else
            $this->session->set('cart', $cart);
    }
}