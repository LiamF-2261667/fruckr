<?php

namespace App\Controllers;

use App\Exceptions\ChatException;
use App\Models\ChatModel;
use App\Models\FoodtruckModel;

class ChatController extends BaseController
{
    /* Attributes */
    public static string $INDEX_ROUTE = '/chat/(:num)';
    public static string $SEND_MESSAGE_ROUTE = '/chat/sendMessage';
    public static string $CREATE_CHAT_ROUTE = '/foodtruck/(:num)/chat';

    private ?ChatModel $chat = null;
    private ?bool $viewerIsClient = null;

    /* Methods */
    /**
     * Show the chat page
     */
    public function index($chatId = null)
    {
        // Clear unnecessary data
        $this->unloadUnnecessaryData();

        // Check if the user is logged in
        $currUser = $this->session->get('currUser');
        if ($currUser == null)
            return $this->defaultRedirect();

        // Check if the chat exists
        try {
            $this->chat = ChatModel::getById(intval($chatId));
        }
        catch (\Exception $e) {
            return $this->viewingPage("NoChatError");
        }

        // Check which person the user is
        if ($this->chat->getClient()->getUid() == $currUser->getUid())
            $this->viewerIsClient = true;
        else if ($this->chat->getFoodtruck()->containsWorker($currUser->getUid()))
            $this->viewerIsClient = false;

        // Else, the user is not allowed to see the chat
        else
            return $this->defaultRedirect();

        // Save the chat in the session
        $this->session->set('currChat', $this->chat);

        // Return the view
        return $this->viewingPage("Chat", $this->getViewData());
    }

    /**
     * Show the chat page && start a chat with the given foodtruck
     */
    public function startChat($foodtruckId = null)
    {
        // Clear unnecessary data
        $this->unloadUnnecessaryData();

        // Check if the user is logged in
        $currUser = $this->session->get('currUser');
        if ($currUser == null)
            return $this->redirect(LoginController::$INDEX_ROUTE);

        // Check if the foodtruck exists
        try {
            $foodtruck = FoodtruckModel::getFoodtruckById(intval($foodtruckId));
        }
        catch (\Exception $e) {
            return $this->viewingPage("NoFoodtruckError");
        }

        // Check if the doesn't work at the foodtruck
        if ($foodtruck->containsWorker($currUser->getUid()))
            return $this->defaultRedirect();

        // Create the chat
        try {
            $this->chat = ChatModel::createChat($foodtruck, $currUser);
        }
        catch (ChatException $e) {
            return $this->viewingPage("CouldntCreateChatError", ['error' => $e->getMessage()]);
        }

        // Save the chat in the session
        $this->session->set('currChat', $this->chat);

        // Viewer always is the client
        $this->viewerIsClient = true;

        // Return the view
        return $this->viewingPage("Chat", $this->getViewData());
    }

    /**
     * AJAX FUNCTION: Send a message to the chat
     */
    public function sendMessage()
    {
        // Getting the msg
        $content = $this->request->getJsonVar('content');

        // Check if the user is logged in
        $currUser = $this->session->get('currUser');
        if ($currUser == null) {
            $this->sendAjaxResponse(['success' => false, 'error' => 'You must be logged in to send a message']);
            return;
        }

        // Check if the chat is loaded
        $this->chat = $this->session->get('currChat');
        if ($this->chat == null) {
            $this->sendAjaxResponse(['success' => false, 'error' => 'The chat is not loaded']);
            return;
        }

        // Try to send the message
        try {
            $this->chat->sendMessage($content, $currUser->getUid());
        }
        catch (ChatException $e) {
            $this->sendAjaxResponse(['success' => false, 'error' => $e->getMessage()]);
            return;
        }

        // Return the success response
        $this->sendAjaxResponse(['success' => true]);
    }

    private function getViewData(): array
    {
        return [
            'chat' => $this->chat,
            'viewerIsClient' => $this->viewerIsClient,
            'recipientName' => $this->viewerIsClient ? $this->chat->getFoodtruck()->getName() : $this->chat->getClient()->getFullName(),
            'senderName' => $this->viewerIsClient ? $this->chat->getClient()->getFullName() : $this->chat->getFoodtruck()->getName()
        ];
    }
}