<?php

namespace App\Controllers;

use App\Exceptions\NoDataException;
use App\Exceptions\ReviewException;
use App\Models\FoodItemModel;
use App\Models\ReviewModel;

class ReviewController extends BaseController
{
    /* Attributes */
    public static string $GET_CURRENT_FOODTRUCK_REVIEWS_ROUTE = '/reviews/foodtruck';
    public static string $GET_CURRENT_FOODTRUCK_ITEM_REVIEWS_ROUTE = 'reviews/foodtruck/foodItem';

    /* Methods */
    /**
     * AJAX FUNCTION: Get the reviews for the current session data foodtruck
     */
    public function getCurrentFoodtruckReviews()
    {
        // get the current foodtruck
        $foodtruck = $this->session->get('currFoodtruck');
        if ($foodtruck == null) {
            $this->sendAjaxResponse(['success' => false, 'error' => 'No foodtruck selected']);
            return;
        }

        // get the reviews for the current foodtruck
        try {
            $reviews = ReviewModel::getAllFromFoodtruck($foodtruck);
        }
        catch (ReviewException $e) {
            if (getenv('CI_ENVIRONMENT') != 'production')
                $this->sendAjaxResponse(['success' => false, 'error' => 'An invalid review is preventing the reviews from loading: ' . $e->getMessage()]);
            else
                $this->sendAjaxResponse(['success' => false, 'error' => 'Cannot load reviews, please try again later!']);
            return;
        }

        // Give the reviews as response
        $this->sendAjaxResponse(['success' => true, 'reviews' => $this->reviewsToHtml($reviews)]);
    }

    /**
     * AJAX FUNCTION: Get the reviews for the current session data foodtruck and food item
     */
    public function getCurrentFoodtruckItemReviews()
    {
        // get the current foodtruck
        $foodtruck = $this->session->get('currFoodtruck');
        if ($foodtruck == null) {
            $this->sendAjaxResponse(['success' => false, 'error' => 'No foodtruck selected']);
            return;
        }

        // get the food item
        $foodName = $this->request->getJsonVar('foodName');
        if ($foodName == null) {
            $this->sendAjaxResponse(['success' => false, 'error' => 'No food name given']);
            return;
        }
        try {
            $foodItem = FoodItemModel::getByName($foodtruck->getId(), $foodName);
        }
        catch (NoDataException $e) {
            $this->sendAjaxResponse(['success' => false, 'error' => 'No food item found with the given name']);
            return;
        }

        // get the reviews for the current foodtruck and food item
        try {
            $reviews = ReviewModel::getAllForFoodItem($foodtruck, $foodItem);
        }
        catch (ReviewException $e) {
            if (getenv('CI_ENVIRONMENT') != 'production')
                $this->sendAjaxResponse(['success' => false, 'error' => 'An invalid review is preventing the reviews from loading: ' . $e->getMessage()]);
            else
                $this->sendAjaxResponse(['success' => false, 'error' => 'Cannot load reviews, please try again later!']);
            return;
        }

        // Give the reviews as response
        $this->sendAjaxResponse(json_encode(['success' => true, 'reviews' => $this->reviewsToHtml($reviews)]));
    }

    private function reviewsToHtml(array $reviews): array
    {
        $html = [];
        foreach ($reviews as $review) {
            $html[] = $review->toHtml();
        }
        return $html;
    }
}