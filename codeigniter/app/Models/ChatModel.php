<?php

namespace App\Models;

use App\Database\DatabaseHandler;
use App\DataObjects\ChatMessage;
use App\Exceptions\ChatException;
use App\Exceptions\NoDataException;
use App\Exceptions\NoUserException;
use DateTime;

class ChatModel
{
    /* Attributes */
    private ?int $id = null;
    private ?FoodtruckModel $foodtruck = null;
    private ?UserModel $client = null;
    private ?array $messages = null;

    /* Initializers */
    /**
     * Get a chat from the database with the given id
     * @param int $id The id of the chat to get
     * @return ChatModel The chat with the given id
     * @throws ChatException If the chat data is invalid or no chat could be found
     */
    public static function getById(int $id): ChatModel
    {
        $chat = new ChatModel();
        $chat->loadById($id);
        return $chat;
    }

    /**
     * Get a chat from the database with the given client and foodtruck
     * @param UserModel $client The client of the chat
     * @param FoodtruckModel $foodtruck The foodtruck of the chat
     * @return ChatModel The chat with the given client and foodtruck
     * @throws ChatException If the chat data is invalid or no chat could be found
     */
    public static function getByClientAndFoodtruck(UserModel $client, FoodtruckModel $foodtruck): ChatModel
    {
        $chat = new ChatModel();
        $chat->loadByClientAndFoodtruck($client, $foodtruck);
        return $chat;
    }

    /**
     * Get all chats from the given client
     * @param UserModel $client The client to get the chats from
     * @return array An array with all chats from the given client
     */
    public static function getAllFromClient(UserModel $client): array
    {
        $results = DatabaseHandler::getInstance()->query(self::$Q_GET_ALL_CHATS_FROM_CLIENT, [$client->getUid()]);

        $chats = [];
        foreach ($results as $result) {
            try {
                $chat = new ChatModel();
                $chat->loadByDBResult($result);
                $chats[] = $chat;
            }
            catch (ChatException $e) {
                // Ignore invalid chats
            }
        }

        return self::orderByLatestMessageDate($chats);
    }

    /**
     * Get all chats from the given foodtruck
     * @param FoodtruckModel $foodtruck The foodtruck to get the chats from
     * @return array An array with all chats from the given foodtruck
     */
    public static function getAllFromFoodtruck(FoodtruckModel $foodtruck): array
    {
        $results = DatabaseHandler::getInstance()->query(self::$Q_GET_ALL_CHATS_FROM_FOODTRUCK, [$foodtruck->getId()]);

        $chats = [];
        foreach ($results as $result) {
            try {
                $chat = new ChatModel();
                $chat->loadByDBResult($result);
                $chats[] = $chat;
            }
            catch (ChatException $e) {
                // Ignore invalid chats
            }
        }

        return self::orderByLatestMessageDate($chats);
    }

    /**
     * Create a chat between the given foodtruck and client
     * @param FoodtruckModel $foodtruck The foodtruck
     * @param UserModel $client The client
     * @return ChatModel The created chat
     * @throws ChatException If the chat could not be created
     */
    public static function createChat(FoodtruckModel $foodtruck, UserModel $client): ChatModel
    {
        // If a chat already exists, return it
        try {
            return self::getByClientAndFoodtruck($client, $foodtruck);
        }
        catch (ChatException $e) {
            // Ignore
        }

        // Create the chat
        DatabaseHandler::getInstance()->query(self::$Q_CREATE_CHAT, [$foodtruck->getId(), $client->getUid()], false);

        // Load the chat
        try {
            $chat = self::getByClientAndFoodtruck($client, $foodtruck);
        }
        catch (ChatException $e) {
            throw new ChatException("The chat could not be created: " . $e->getMessage());
        }

        return $chat;
    }

    /* Methods */
    /**
     * Order the given chats by latest message date
     * @param array $chats The chats to order
     * @return array The chats ordered by latest message date
     */
    private static function orderByLatestMessageDate(array $chats): array
    {
        usort($chats, function ($a, $b) {
            $aLatestMsg = $a->getLatestMessage();
            $bLatestMsg = $b->getLatestMessage();

            if ($aLatestMsg == null)
                return 1;
            if ($bLatestMsg == null)
                return -1;

            return $aLatestMsg->getTimestamp() < $bLatestMsg->getTimestamp();
        });

        return $chats;
    }

    /**
     * Load a chat from the database with the given id
     * @param int $id The id of the chat to load
     * @throws ChatException If the chat data is invalid or no chat could be found
     */
    public function loadById(int $id): void
    {
        $this->loadByQuery(self::$Q_GET_BY_ID, [$id]);
    }

    /**
     * Load a chat from the database with the given client and foodtruck
     * @param UserModel $client The client of the chat
     * @param FoodtruckModel $foodtruck The foodtruck of the chat
     * @throws ChatException If the chat data is invalid or no chat could be found
     */
    public function loadByClientAndFoodtruck(UserModel $client, FoodtruckModel $foodtruck): void
    {
        $this->loadByQuery(self::$Q_GET_FROM_CLIENT_AND_FOODTRUCK, [$client->getUid(), $foodtruck->getId()]);
    }

    /**
     * Loads the chat from a db query
     * @param string $query The query to execute
     * @param array $params The parameters for the query
     * @throws ChatException If the chat data is invalid
     */
    private function loadByQuery(string $query, array $params): void
    {
        $results = DatabaseHandler::getInstance()->query($query, $params);

        if (count($results) == 0)
            throw new ChatException("No chat with the given parameters");

        $this->loadByDBResult($results[0]);
    }

    /**
     * Loads the chat from the database result
     * @param mixed $result The result from the database
     * @throws ChatException If the chat data is invalid
     */
    private function loadByDBResult($result): void
    {
        try {
            $this->id = $result->chatId;
            $this->foodtruck = FoodtruckModel::getFoodtruckById($result->foodtruck);
            $this->client = UserModel::getUserById($result->client);
        }
        catch (NoDataException | NoUserException $e) {
            throw new ChatException("Invalid chat data");
        }
    }

    /**
     * Loads the messages from the database
     * @pre The chat must be loaded
     */
    public function loadMessages(): void
    {
        $results = DatabaseHandler::getInstance()->query(self::$Q_GET_ALL_MESSAGES, [$this->id]);

        $this->messages = [];
        foreach ($results as $result) {
            $this->messages[] = new ChatMessage(new DateTime($result->timestamp), $result->content, $result->sendByClient);
        }
    }

    /**
     * Unload the messages from the chat (to save memory)
     */
    public function unloadMessages(): void
    {
        $this->messages = null;
    }

    /**
     * Send a message to the chat
     * @param string $content The content of the message
     * @param int $senderId The id of the sender
     * @throws ChatException If the sender is not allowed to send a message to this chat
     * @pre The chat must be loaded
     */
    public function sendMessage(string $content, int $senderId): void
    {
        // Validate if the sender is allowed to send a message
        if ($this->client->getUid() != $senderId && !$this->foodtruck->containsWorker($senderId))
            throw new ChatException("The sender is not allowed to send a message to this chat");

        // Check that the content is not empty
        if (empty(trim($content)))
            throw new ChatException("The message content cannot be empty");

        // Check that the content doesn't exceed max length
        if (strlen($content) > 500)
            throw new ChatException("The message content cannot exceed 500 characters");

        // Send the message
        DatabaseHandler::getInstance()->query(self::$Q_SEND_MESSAGE, [
            $this->id,
            date("Y-m-d H:i:s"),
            $content,
            $this->client->getUid() == $senderId
        ], false);
    }

    /* Queries */
    private static string $Q_GET_BY_ID = 'SELECT * FROM Chat WHERE chatId = ?';
    private static string $Q_GET_ALL_CHATS_FROM_CLIENT = 'SELECT * FROM Chat WHERE client = ?';
    private static string $Q_GET_ALL_CHATS_FROM_FOODTRUCK = 'SELECT * FROM Chat WHERE foodtruck = ?';
    private static string $Q_GET_FROM_CLIENT_AND_FOODTRUCK = 'SELECT * FROM Chat WHERE client = ? AND foodtruck = ?';

    private static string $Q_GET_LATEST_MESSAGE = 'SELECT * FROM Message WHERE chatId = ? ORDER BY timestamp DESC LIMIT 1';
    private static string $Q_GET_ALL_MESSAGES = 'SELECT * FROM Message WHERE chatId = ? ORDER BY timestamp ASC';

    private static string $Q_SEND_MESSAGE = 'INSERT INTO Message (chatId, timestamp, content, sendByClient) VALUES (?, ?, ?, ?)';

    private static string $Q_CREATE_CHAT = 'INSERT INTO Chat (foodtruck, client) VALUES (?, ?)';

    /* Getters */
    /**
     * Get the latest message send in the chat
     * @return ChatMessage|null The latest message send in the chat, null if no message was sent
     * @pre The chat must be loaded
     */
    public function getLatestMessage(): ?ChatMessage
    {
        $result = DatabaseHandler::getInstance()->query(self::$Q_GET_LATEST_MESSAGE, [$this->id]);

        if (count($result) == 0)
            return null;

        return new ChatMessage(new DateTime($result[0]->timestamp), $result[0]->content, $result[0]->sendByClient);
    }

    /**
     * Get the id of the chat
     * @return int The id of the chat
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the foodtruck of the chat
     * @return FoodtruckModel The foodtruck of the chat
     */
    public function getFoodtruck(): FoodtruckModel
    {
        return $this->foodtruck;
    }

    /**
     * Get the client of the chat
     * @return UserModel The client of the chat
     */
    public function getClient(): UserModel
    {
        return $this->client;
    }

    /**
     * Get the messages of the chat
     * @return array The messages of the chat
     * @pre The chat must be loaded
     * @post The messages will be loaded
     */
    public function getMessages(): array
    {
        if ($this->messages == null)
            $this->loadMessages();

        return $this->messages;
    }

    /**
     * Get a short html representation of the chat
     * @param bool $fromClientView Whether the chat is viewed from the client's perspective
     * @return string A short html representation of the chat
     */
    public function toShortHtml(bool $fromClientView): string
    {
        // Get the recipient
        $recipient = $fromClientView ? $this->foodtruck->getName() : $this->client->getFullName();

        // Get the latest msg text
        $latestMsg = $this->getLatestMessage();
        $content = '';

        // Add sender to the content
        if ($latestMsg != null) {
            if ($latestMsg->isSendByClient() == $fromClientView)
                $content = 'You: ';
            else
                $content = $recipient . ': ';

            $content .= $latestMsg->getContent();
        }

        // Get the latest text time
        $timestamp = ($latestMsg == null) ? '' : $latestMsg->getFormattedTimestamp();

        return '
            <a class="chat-object" href="/chat/' . $this->id . '">
                <h2 class="recipient">' . $recipient . '</h2>
                <p class="latest-message">' . $content . '</p>
                <p class="timestamp">' . $timestamp . '</p>
            </a>
        ';
    }
}