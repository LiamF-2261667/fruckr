<?php

namespace App\DataObjects;

use App\Exceptions\NoDataException;
use App\Models\FoodItemModel;

class CartItemData
{
    /* Attributes */
    private FoodItemModel $foodItem;
    private int $amount;

    /* Constructor */
    /**
     * Creates a new CartItemData object
     * @param int $foodtruckId The id of the foodtruck
     * @param string $foodItemName The name of the food item
     * @param int $amount The amount of the food item
     * @throws NoDataException If the food item doesn't exist
     */
    public function __construct(int $foodtruckId, string $foodItemName, int $amount)
    {
        $this->foodItem = FoodItemModel::getByName($foodtruckId, $foodItemName);
        $this->amount = $amount;
    }

    /* Methods */
    public function getFoodItem() : FoodItemModel
    {
        return $this->foodItem;
    }

    public function getAmount() : int
    {
        return $this->amount;
    }

    public function setAmount(int $amount) : void
    {
        $this->amount = $amount;
    }

    public function getTotalPrice() : float
    {
        return $this->foodItem->getPrice() * $this->amount;
    }

    public function getFormattedTotalPrice() : string
    {
        return number_format($this->getTotalPrice(), 2);
    }

    /**
     * Convert the cart item data into a html string
     * @return string The HTML representation of this object
     */
    public function toHtml() : string
    {
        return '<div class="cart-item-object">
                    ' . $this->foodItem->toShortHtml() . '
                    <h3 class="amount">Amount: ' . $this->amount . '</h3>
                    <h3 class="total-price">€' . $this->getFormattedTotalPrice() . '</h3>
                    <button class="delete-button logo-button">
                        <img src="../Icons/delete.png" alt="Delete Icon" class="delete-icon">
                    </button>
                </div>';
    }
}