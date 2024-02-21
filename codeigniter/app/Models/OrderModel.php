<?php

namespace App\Models;

use App\Database\DatabaseHandler;
use App\DataObjects\CartItemData;
use App\Exceptions\InvalidOrderException;
use App\Exceptions\MailException;
use App\Exceptions\NoDataException;
use App\Exceptions\NoUserException;
use App\Exceptions\NoWorkerException;
use App\Helpers\EmailSender;
use DateTime;
use App\Factories\EmailFactory;
use Exception;

class OrderModel
{
    /* Attributes */
    private ?int $id = null;
    private ?FoodtruckModel $foodtruck = null;
    private ?UserModel $client = null;
    private ?AddressModel $address = null;
    private ?FoodtruckWorkerModel $confirmer = null;
    private ?DateTime $timestamp = null;
    private ?bool $isCollected = null;
    private ?bool $isReady = null;
    private ?array $cartItems = null; /* array of CartItemData */

    /* Initializers */
    /**
     * Get an order from the db by id
     * @param int $id The id of the order
     * @return OrderModel The order
     * @throws InvalidOrderException If the order is not found or isn't valid
     */
    public static function getByID(int $id): OrderModel
    {
        $order = new OrderModel();
        $order->loadById($id);
        return $order;
    }

    /**
     * Get an order from the db by data
     * @param int $foodtruckId The id of the foodtruck
     * @param int $clientId The id of the client
     * @param string $timestamp The timestamp of the order
     * @return OrderModel The order
     * @throws InvalidOrderException If the order is not found or isn't valid
     * @pre the timestamp must be in the format "Y-m-d H:i:s"
     */
    public static function getByData(int $foodtruckId, int $clientId, string $timestamp): OrderModel
    {
        $order = new OrderModel();
        $order->loadByData($foodtruckId, $clientId, $timestamp);
        return $order;
    }

    /**
     * Get all orders from the db by foodtruck
     * @param int $foodtruckId The id of the foodtruck
     * @return array The orders (array of OrderModel)
     * @throws InvalidOrderException If an order isn't valid
     */
    public static function getAllOrdersByFoodtruck(int $foodtruckId): array
    {
        $orders = [];

        // Get the orders from the db
        $result = DatabaseHandler::getInstance()->query(self::$Q_GET_ALL_ORDERS_BY_FOODTRUCK, [$foodtruckId]);
        foreach ($result as $order)
            $orders[] = self::getByID($order->orderId);

        return $orders;
    }

    /**
     * Get all open orders from the db by foodtruck
     * @param int $foodtruckId The id of the foodtruck
     * @return array The orders (array of OrderModel)
     * @throws InvalidOrderException If an order isn't valid
     */
    public static function getAllOpenOrdersByFoodtruck(int $foodtruckId): array
    {
        $orders = [];

        // Get the orders from the db
        $result = DatabaseHandler::getInstance()->query(self::$Q_GET_ALL_OPEN_ORDERS_BY_FOODTRUCK, [$foodtruckId]);
        foreach ($result as $order)
            $orders[] = self::getByID($order->orderId);

        return $orders;
    }

    /* Methods */
    /**
     * Load an order from the db by id
     * @throws InvalidOrderException If the order is not found or isn't valid
     */
    public function loadById(int $id): void
    {
        $this->loadByQuery(self::$Q_GET_BY_ID, [$id]);
    }

    /**
     * Load an order from the db by data
     * @param int $foodtruckId The id of the foodtruck
     * @param int $clientId The id of the client
     * @param string $timestamp The timestamp of the order
     * @throws InvalidOrderException If the order is not found or isn't valid
     * @pre the timestamp must be in the format "Y-m-d H:i:s"
     */
    public function loadByData(int $foodtruckId, int $clientId, string $timestamp): void
    {
        // Load the order
        $this->loadByQuery(self::$Q_GET_BY_DATA, [$foodtruckId, $clientId, $timestamp]);
    }

    /**
     * Load an order from the db by query
     * @param string $query The query
     * @param array $params The query parameters
     * @throws InvalidOrderException If the order is not found or isn't valid
     */
    private function loadByQuery(string $query, array $params): void
    {
        try {
            // Getting the order data from the db
            $result = DatabaseHandler::getInstance()->query($query, $params);
            if (count($result) == 0)
                throw new NoDataException("Order", "No order found");

            // Loading the order data & cart items
            $this->loadByDBResult($result[0]);
            $this->loadCartItems();
        }
        catch (NoDataException | NoUserException | NoWorkerException | Exception $e) {
            throw new InvalidOrderException("Cannot load order: " . $e->getMessage());
        }
    }

    /**
     * Load the order data from the database result
     * @throws NoDataException If the order data is not found
     * @throws NoUserException If the user data is not found
     * @throws NoWorkerException If the worker data is not found
     * @throws Exception If the timestamp is not valid
     */
    private function loadByDBResult($result): void
    {
        $this->id = $result->orderId;
        $this->foodtruck = FoodtruckModel::getFoodtruckById($result->foodtruck);
        $this->client = UserModel::getUserById($result->client);
        $this->address = AddressModel::getAddressById($result->addressKey);

        if ($result->confirmer != null)
            $this->confirmer = FoodtruckWorkerModel::getWorkerByUserUid($result->confirmer);

        $this->timestamp = new DateTime($result->timestamp);

        $this->isCollected = $result->isCollected;
        $this->isReady = $result->isReady;
    }

    /**
     * Load the cart items from the database
     * @throws NoDataException If the cart items are not found
     * @pre the foodtruck and the order id must be set
     */
    private function loadCartItems(): void
    {
        $this->cartItems = [];
        $result = DatabaseHandler::getInstance()->query(self::$Q_GET_CART_ITEMS, [$this->id]);
        foreach ($result as $item)
            $this->cartItems[] = new CartItemData($this->foodtruck->getId(), $item->foodName, $item->amount);
    }

    /**
     * Create an order
     * @param int $foodtruckId The id of the foodtruck
     * @param int $clientId The id of the client
     * @param AddressModel $deliveryAddress The delivery address
     * @param array $cartItems The cart items (array of CartItemData)
     * @return OrderModel The created order
     * @throws InvalidOrderException If the order is not valid
     * @throws Exception If there is a major error whilst creating the order
     */
    public static function createOrder(int $foodtruckId, int $clientId, AddressModel $deliveryAddress, array $cartItems): OrderModel
    {
        // Validate the inputs
        self::validateOrderInput($foodtruckId, $clientId, $deliveryAddress, $cartItems);

        // Create order variables
        $timestamp = date("Y-m-d H:i:s");

        // Create the order
        DatabaseHandler::getInstance()->query(self::$Q_CREATE_ORDER,
            [
                $foodtruckId,
                $clientId,
                $deliveryAddress->getAddressKey(),
                null, $timestamp,
                false
            ], false);

        // Get the order
        $newOrder = null;
        try {
            $newOrder = self::getByData($foodtruckId, $clientId, $timestamp);
        }
        catch (InvalidOrderException $e) {
            // This shouldn't happen
            throw new Exception("Major error whilst retrieving recently created order id: " . $e->getMessage());
        }

        // Create the cart items
        foreach ($cartItems as $item)
            DatabaseHandler::getInstance()->query(self::$Q_CREATE_CART_ITEM,
                [
                    $foodtruckId,
                    $item->getFoodItem()->getName(),
                    $newOrder->getId(),
                    $item->getAmount()
                ], false);

        // Load the cart items
        try {
            $newOrder->loadCartItems();
        }
        catch (NoDataException $e) {
            // This shouldn't happen
            throw new Exception("Major error whilst retrieving recently created cart items: " . $e->getMessage());
        }

        // Return the order
        return $newOrder;
    }

    /**
     * Validate the order input
     * @param int $foodtruckId The id of the foodtruck
     * @param int $clientId The id of the client
     * @param AddressModel $deliveryAddress The delivery address
     * @param array $cartItems The cart items (array of CartItemData)
     * @throws InvalidOrderException If the order is not valid
     */
    private static function validateOrderInput(int $foodtruckId, int $clientId, AddressModel $deliveryAddress, array $cartItems): void
    {
        // Validate the foodtruck id: it cannot be null
        if ($foodtruckId == null)
            throw new InvalidOrderException("Foodtruck id cannot be null");

        // Validate the client id: it cannot be null
        if ($clientId == null)
            throw new InvalidOrderException("Client id cannot be null");

        // Validate the delivery address: it cannot be null
        if ($deliveryAddress->getAddressKey() == null)
            throw new InvalidOrderException("Delivery address key cannot be null");

        // Validate the cart items: they cannot be null
        if ($cartItems == null)
            throw new InvalidOrderException("Cart items cannot be null");

        // Validate the cart items: there must be at least 1 item
        if (count($cartItems) == 0)
            throw new InvalidOrderException("Cart items cannot be empty");

        // Validate the cart items: each item must have more than 1 amount
        foreach ($cartItems as $item)
            if ($item->getAmount() <= 0)
                throw new InvalidOrderException("Cart item amount cannot be less than or equal to 0");
    }

    /**
     * Confirm an order
     * @param int $workerUid The id of the worker who confirms the order
     * @pre the order is loaded
     * @throws InvalidOrderException If the worker doesn't work for the foodtruck ordered at
     */
    public function confirmOrder(int $workerUid): void
    {
        // Check if the worker works at the ordered foodtruck
        if (!$this->foodtruck->containsWorker($workerUid))
            throw new InvalidOrderException("The worker doesn't work at the ordered foodtruck");

        // Confirm the order
        DatabaseHandler::getInstance()->query(self::$Q_CONFIRM_ORDER,
            [
                $workerUid,
                true,
                $this->id
            ], false);

        // Send a mail to the client that the order has been set to picked up
        try {
            EmailSender::sendMail(EmailFactory::orderClaimed($this));
        } catch (MailException $e) { }
    }

    /**
     * Set an order as ready
     * @param int $workerUid The id of the worker who confirms the order
     * @pre the order is loaded
     * @throws InvalidOrderException If the worker doesn't work for the foodtruck ordered at
     */
    public function setReady(int $workerUid): void
    {
        // Check if the worker works at the ordered foodtruck
        if (!$this->foodtruck->containsWorker($workerUid))
            throw new InvalidOrderException("The worker doesn't work at the ordered foodtruck");

        // Confirm the order
        DatabaseHandler::getInstance()->query(self::$Q_READY_ORDER,
            [
                true,
                $this->id
            ], false);

        // Send a mail to the client waiting for the order to be ready
        try {
            EmailSender::sendMail(EmailFactory::orderReady($this));
        } catch (MailException $e) { }
    }

    /* Queries */
    private static string $Q_GET_BY_ID = 'SELECT * FROM ClientOrder WHERE orderId = ?';
    private static string $Q_GET_BY_DATA = 'SELECT * FROM ClientOrder WHERE foodtruck = ? AND client = ? AND timestamp = ?';
    private static string $Q_GET_CART_ITEMS = 'SELECT * FROM isOrdered WHERE orderId = ?';
    private static string $Q_GET_ALL_ORDERS_BY_FOODTRUCK = 'SELECT * FROM ClientOrder WHERE foodtruck = ? ORDER BY timestamp DESC';
    private static string $Q_GET_ALL_OPEN_ORDERS_BY_FOODTRUCK = 'SELECT * FROM ClientOrder WHERE foodtruck = ? AND isCollected = 0 ORDER BY timestamp ASC';

    private static string $Q_CREATE_ORDER = 'INSERT INTO ClientOrder (foodtruck, client, addressKey, confirmer, timestamp, isCollected) VALUES (?, ?, ?, ?, ?, ?)';
    private static string $Q_CREATE_CART_ITEM = 'INSERT INTO isOrdered (foodtruck, foodName, orderId, amount) VALUES (?, ?, ?, ?)';

    private static string $Q_CONFIRM_ORDER = 'UPDATE ClientOrder SET confirmer = ?, isCollected = ? WHERE orderId = ?';
    private static string $Q_READY_ORDER = 'UPDATE ClientOrder SET isReady = ? WHERE orderId = ?';

    /* Getters */
    public function getId(): int
    {
        return $this->id;
    }

    public function getFoodtruck(): FoodtruckModel
    {
        return $this->foodtruck;
    }

    public function getClient(): UserModel
    {
        return $this->client;
    }

    public function getAddress(): AddressModel
    {
        return $this->address;
    }

    public function getConfirmer(): ?FoodtruckWorkerModel
    {
        return $this->confirmer;
    }

    public function getTimestamp(): DateTime
    {
        return $this->timestamp;
    }

    public function getFormattedOrderDate(): string
    {
        return $this->timestamp->format("d/m/Y H:i");
    }

    public function isCollected(): bool
    {
        return $this->isCollected;
    }

    public function isReady(): bool
    {
        return $this->isReady;
    }

    public function getCartItems(): array
    {
        return $this->cartItems;
    }

    public function getTotalPrice(): float
    {
        $total = 0;
        foreach ($this->cartItems as $item)
            $total += $item->getTotalPrice();
        return $total;
    }

    public function getFormattedTotalPrice(): string
    {
        return number_format($this->getTotalPrice(), 2);
    }

    public function getTotalItemCount(): int
    {
        $total = 0;
        foreach ($this->cartItems as $item)
            $total += $item->getAmount();
        return $total;
    }

    public function toShortHtml(): string
    {
        $orderItemsStr = "";
        foreach ($this->cartItems as $item)
            $orderItemsStr .= '<li class="order-object-item">' . $item->getAmount() . "x " . $item->getFoodItem()->getName() . '</li>';

        return '
        <div class="order-object">
            <div class="order-object-header">
                <h2 class="order-object-title">Order #' . $this->getId() . '</h2>
                <p class="order-object-date">' . $this->getFormattedOrderDate() . '</p>
            </div>
            <div class="order-object-body">
                <div class="order-object-items">
                    <h3>Items: (' . $this->getTotalItemCount() . ')</h3>
                    <ul>
                        ' . $orderItemsStr . '
                    </ul>
                </div>
                <h3 class="order-object-price">Total: €' . $this->getFormattedTotalPrice() . '</h3>
            </div>
        </div>
        ';
    }
}