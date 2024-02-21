<?php

namespace App\Controllers;

use App\Exceptions\InvalidOrderException;
use App\Exceptions\NoDataException;
use App\Exceptions\ReviewException;
use App\Models\FoodItemModel;
use App\Models\FoodtruckModel;
use App\Models\OrderModel;
use App\Models\ReviewModel;
use App\Models\UserModel;

class CreateReviewController extends BaseController
{
    /* Attributes */
    public static string $INDEX_ROUTE = '/review/(:num)';
    public static string $CREATE_REVIEW_ROUTE = '/review/create';

    private ?UserModel $currUser = null;
    private ?OrderModel $order = null;
    private ?array $orderedItems = null;

    /* Methods */
    public function index($orderId = null)
    {
        $this->unloadUnnecessaryData();

        // Check if the user is logged in
        $currUser = $this->session->get('currUser');
        if ($currUser == null)
            return $this->redirect(LoginController::$INDEX_ROUTE);

        // Check if the order exists
        try {
            $this->order = OrderModel::getByID($orderId);
        }
        catch (InvalidOrderException $e) {
            return $this->defaultRedirect();
        }

        // Check if the current user is the owner of the order
        if ($this->order->getClient()->getUid() != $currUser->getUid())
            return $this->defaultRedirect();

        // Check if the order is completed
        if (!$this->order->isCollected())
            return $this->defaultRedirect();

        // Store necessary data in the session
        $this->session->set('currOrder', $this->order);

        // Return the view
        return $this->viewingPage("Create_Review", $this->getViewData());
    }

    /**
     * AJAX FUNCTION: Create a review for the current session data foodtruck and food item
     */
    public function createReviewForCurrentFoodtruck() {
        $this->loadData();

        // Make sure the user is logged in && the current order is set
        if ($this->order == null) {
            $this->sendAjaxResponse(['success' => false, 'error' => 'No order selected']);
            return;
        }
        if ($this->currUser == null) {
            $this->sendAjaxResponse(['success' => false, 'error' => 'You need to be logged in to create a review']);
            return;
        }

        // get the food item
        $foodName = $this->request->getJsonVar('foodName');

        // get the food item
        $selectedFoodItem = null;
        foreach ($this->orderedItems as $foodItem) {
            if (trim(strtolower($foodItem->getName())) === trim(strtolower($foodName))) {
                $selectedFoodItem = $foodItem;
                break;
            }
        }

        // get the review data
        $rating = $this->request->getJsonVar('rating');
        $title = $this->request->getJsonVar('title');
        $content = $this->request->getJsonVar('content');

        // create the review
        try {
            $review = ReviewModel::createReview($this->currUser, $this->order->getFoodtruck(), $rating, $title, $content, $selectedFoodItem);
        }
        catch (ReviewException $e) {
            $this->sendAjaxResponse(['success' => false, 'error' => $e->getMessage()]);
            return;
        }

        // Give the review as response
        $this->sendAjaxResponse(['success' => true, 'review' => $review->toHtml(), 'item' => $foodName]);
    }

    private function loadData(): void
    {
        // Get the current user
        $this->currUser = $this->session->get('currUser');

        // Get the current order
        $this->order = $this->session->get('currOrder');

        // Get the ordered items
        $this->orderedItems = [];
        foreach ($this->order->getCartItems() as $cartItem)
            $this->orderedItems[] = $cartItem->getFoodItem();
    }

    private function getViewData(): array
    {
        $this->loadData();

        return [
            'foodtruck' => $this->order->getFoodtruck(),
            'foodItems' => $this->orderedItems,
            'order' => $this->order
        ];
    }
}