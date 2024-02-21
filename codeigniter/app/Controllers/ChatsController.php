<?php

namespace App\Controllers;

use App\Models\ChatModel;
use App\Models\FoodtruckModel;
use App\Models\UserModel;

class ChatsController extends BaseController
{
    /* Attributes */
    public static string $CLIENT_ROUTE = '/chats';
    public static string $FOODTRUCK_ROUTE = '/foodtruck/(:num)/chats';

    private bool $isFoodtruckChats = false;
    private ?FoodtruckModel $foodtruck = null;
    private ?UserModel $client = null;

    /* Methods */
    /**
     * Show the chat list page
     */
    public function index($foodtruck = null)
    {
        // Check if the user is logged in
        $currUser = $this->session->get('currUser');
        if ($currUser == null)
            return $this->defaultRedirect();

        // Check if they want to see the foodtruck chats
        if ($foodtruck != null) {
            $this->isFoodtruckChats = true;

            // Check if the user works at the foodtruck
            try {
                $this->foodtruck = FoodtruckModel::getFoodtruckById(intval($foodtruck));
            }
            catch (\Exception $e) {
                return $this->viewingPage("NoFoodtruckError");
            }

            if (!$this->foodtruck->containsWorker($currUser->getUid()))
                return $this->defaultRedirect();

            // Else, the user is allowed to see the foodtruck chats
        }

        // Else set up the client chats
        else {
            $this->isFoodtruckChats = false;
            $this->client = $currUser;
        }

        // Return the view
        return $this->viewingPage("Chats", $this->getViewData());
    }

    private function getViewData(): array
    {
        if ($this->isFoodtruckChats)
            return [
                'viewPointName' => $this->foodtruck->getName(),
                'fromClientView' => false,
                'chats' => ChatModel::getAllFromFoodtruck($this->foodtruck)
            ];

        else
            return [
                'viewPointName' => $this->client->getFullName(),
                'fromClientView' => true,
                'chats' => ChatModel::getAllFromClient($this->client)
            ];
    }
}