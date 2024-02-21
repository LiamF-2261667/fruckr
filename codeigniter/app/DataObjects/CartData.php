<?php

namespace App\DataObjects;

use App\Exceptions\CartException;
use App\Exceptions\NoDataException;

class CartData
{
    /* Attributes */
    public ?int $foodtruckId = null;
    public array $cartItems = [];

    /* Constructor */

    /* Methods */
    /**
     * Adds a food item to the cart
     * @param int $foodtruckId The id of the foodtruck
     * @param string $foodName The id of the food item
     * @param int $amount The amount of the food item
     * @throws CartException If the food item is from another foodtruck
     * @throws NoDataException If the food item doesn't exist
     * @post if the fooditem already is in the cart, it will be replaced (aka. updated)
     */
    public function addFoodItem(int $foodtruckId, string $foodName, int $amount) : void
    {
        // Set the foodtruck id if it wasn't set before
        if ($this->foodtruckId == null)
            $this->foodtruckId = $foodtruckId;

        // Check if the fooditem to add is from the same foodtruck
        if ($this->foodtruckId == $foodtruckId)
            $this->cartItems[$foodName] = new CartItemData($foodtruckId, $foodName, $amount);

        // Otherwise, give an exception
        else
            throw new CartException("You cannot add food items from multiple foodtruck in one order.");
    }

    /**
     * Removes a food item from the cart
     * @param string $foodName The id of the food item
     * @throws CartException If the food item isn't in the cart
     */
    public function removeFoodItem(string $foodName) : void
    {
        // Check if the food item is in the cart
        if (isset($this->cartItems[$foodName]))
            unset($this->cartItems[$foodName]);

        // Otherwise, give an exception
        else
            throw new CartException("The cart doesn't contain the food item.");

        // If the cart is empty, clear the foodtruck id
        if (count($this->cartItems) == 0)
            $this->foodtruckId = null;
    }

    /**
     * Updates the amount of a food item in the cart
     * @param int $foodName The id of the food item
     * @param int $amount The new amount of the food item
     * @throws CartException If the food item isn't in the cart
     */
    public function updateFoodItemAmount(int $foodName, int $amount) : void
    {
        // Check if the food item is in the cart
        if (isset($this->cartItems[$foodName]))
            $this->cartItems[$foodName]->setAmount($amount);

        // Otherwise, give an exception
        else
            throw new CartException("The cart doesn't contain the food item.");
    }

    /**
     * Clears the cart
     */
    public function clear() : void
    {
        $this->foodtruckId = null;
        $this->cartItems = [];
    }

    /**
     * Gets the id of the foodtruck that currently has items in the cart
     * @return int|null The id of the foodtruck
     */
    public function getFoodtruckId() : ?int
    {
        return $this->foodtruckId;
    }

    /**
     * Gets the fooditems inside a cart
     * @return array The food items as CartItemData objects
     */
    public function getItems() : array
    {
        return $this->cartItems;
    }

    /**
     * Gets the total price of all the food items in the cart
     * @return float The total price of all the food items in the cart
     */
    public function getTotalPriceSum() : float
    {
        $sum = 0;

        foreach ($this->cartItems as $cartItem)
            $sum += $cartItem->getTotalPrice();

        return $sum;
    }

    /**
     * Gets the total price of all the food items in the cart, formatted
     * @return string The total price of all the food items in the cart, formatted
     */
    public function getFormattedTotalPriceSum() : string
    {
        return number_format($this->getTotalPriceSum(), 2);
    }

    /**
     * Gets the total amount of food items in the cart
     * @return int The total amount of food items in the cart
     */
    public function getTotalItemCount() : int
    {
        $count = 0;

        foreach ($this->cartItems as $cartItem)
            $count += $cartItem->getAmount();

        return $count;
    }
}