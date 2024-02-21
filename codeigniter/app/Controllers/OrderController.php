<?php

namespace App\Controllers;

use App\Exceptions\InvalidCreditCardException;
use App\Exceptions\InvalidInputException;
use App\Exceptions\InvalidOrderException;
use App\Helpers\CreditCardValidator;
use App\Models\AddressModel;
use App\Models\OrderModel;

class OrderController extends BaseController
{
    /* Attributes */
    public static string $INDEX_ROUTE = '/order';
    public static string $POST_ORDER_ROUTE = '/order/post';

    /* Constructor */

    /* Methods */
    public function index()
    {
        // Redirect the user to the login page, if they aren't logged in
        if (!$this->session->get('currUser'))
            return $this->redirect(LoginController::$INDEX_ROUTE);

        // Make sure the user is logged in && the cart isn't empty
        if ($this->session->get('cart') === null || $this->session->get('cart')->getTotalItemCount() == 0)
            return $this->redirect(LoginController::$INDEX_ROUTE);

        $this->unloadUnnecessaryData();

        // Send the view
        return $this->viewingPage('Order', $this->getViewData());
    }

    /**
     * AJAX FUNCTION: Place an order
     */
    public function postOrder()
    {
        // Get the order parameters
        $cardNumber = request()->getJsonVar('cardNumber');
        $expirationDate = request()->getJsonVar('expirationDate');
        $cardHolder = request()->getJsonVar('cardHolder');
        $city = request()->getJsonVar('city');
        $street = request()->getJsonVar('street');
        $postal = request()->getJsonVar('postal');
        $houseNr = request()->getJsonVar('houseNr');
        $bus = request()->getJsonVar('bus');

        // Try to post the order
        try {
            // Validate the credit card
            CreditCardValidator::validateCardNumber($cardNumber, $expirationDate, $cardHolder);

            // Create the address (if it already exists, it will be loaded)
            $address = AddressModel::createNewAddress($postal, $city, $street, $houseNr, $bus);

            // Get the cart to order
            $cart = $this->session->get('cart');
            if ($cart === null || $cart->getTotalItemCount() == 0)
                throw new InvalidInputException('The cart is empty');

            // Get the currUser to order for
            $currUser = $this->session->get('currUser');
            if ($currUser === null)
                throw new InvalidInputException('The user is not logged in');

            /////////////////////////////////////////////////////////////
            // Pretend the payment is done here (no api is being used) //
            /////////////////////////////////////////////////////////////

            // Place the order
            $order = OrderModel::createOrder($cart->getFoodtruckId(), $currUser->getUid(), $address, $cart->getItems());

            // Set the order as posted
            $this->session->set('orderPosted', $order);
        }
        catch (InvalidCreditCardException | InvalidInputException | InvalidOrderException $e) {
            $this->sendAjaxResponse(json_encode(array("success" => false, "error" => $e->getMessage())));
            return;
        }
        catch (\Exception $e) {
            // If in development, send full error
            if (getenv('CI_ENVIRONMENT') == "development")
                $this->sendAjaxResponse(json_encode(array("success" => false, "error" => "Major error: " . $e->getMessage())));

            // Otherwise, send a generic error
            else
                $this->sendAjaxResponse(json_encode(array("success" => false, "error" => "An unknown error occurred")));

            return;
        }

        $this->sendAjaxResponse(json_encode(array("success" => true)));
    }

    private function getViewData(): array
    {
        return [
            'cart' => $this->session->get('cart')
        ];
    }
}