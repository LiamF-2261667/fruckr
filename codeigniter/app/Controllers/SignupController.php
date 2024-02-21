<?php

namespace App\Controllers;

use App\Exceptions\InvalidInputException;
use App\Exceptions\NoUserException;
use App\Models\UserModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

class SignupController extends BaseController
{
    /* Routes */
    public static string $INDEX_ROUTE = "/signup";
    public static string $REGISTER_ROUTE = "/signup/register";

    /* Codes */
    private static string $REGISTER_SUCCESS_CODE = "success";

    /* Methods */
    /**
     * Signup a new user
     */
    public function index()
    {
        if ($this->session->get('currUser') !== null)
            return $this->defaultRedirect();

        $this->unloadUnnecessaryData();

        // Redirect to the login page
        return $this->viewingPage('Signup');
    }

    /**
     * AJAX POST FUNCTION: create a new user and set it as the current user
     * @return void
     */
    public function register()
    {
        // Get the inputs
        $firstName = request()->getJsonVar('firstName');
        $lastName = request()->getJsonVar('lastName');
        $email = request()->getJsonVar('email');
        $phoneNumber = request()->getJsonVar('phoneNumber');
        $city = request()->getJsonVar('city');
        $street = request()->getJsonVar('street');
        $postalCode = request()->getJsonVar('postalCode');
        $houseNr = request()->getJsonVar('houseNr');
        $bus = request()->getJsonVar('bus');
        $password = request()->getJsonVar('password');
        $isOwner = request()->getJsonVar('isOwner');

        // Make sure they are filled
        if ($firstName == null      || !isset($firstName)      ||
            $lastName == null       || !isset($lastName)       ||
            $email == null          || !isset($email)          ||
            $city == null           || !isset($city)           ||
            $street == null         || !isset($street)         ||
            $postalCode == null     || !isset($postalCode)     ||
            $houseNr == null        || !isset($houseNr)        ||
            $password == null       || !isset($password))       {
            $this->sendAjaxResponse("Please fill in all the required* fields");
            return;
        }

        // Login into the user
        $this->tryRegister($firstName, $lastName, $email, $phoneNumber, $city, $street, $postalCode, $houseNr, $bus, $password, $isOwner);
    }

    /**
     * Try to register a new user
     * @param string $firstName the new first name
     * @param string $lastName the new last name
     * @param string $email the new email
     * @param string|null $phoneNumber the new phone number
     * @param string $city the new city
     * @param string $street the new street
     * @param string $postalCode the new postal code
     * @param string $houseNr the new house number
     * @param string $bus the new bus
     * @param string $password the new password
     * @param bool $isOwner whether the new user is an owner
     * @return void
     */
    private function tryRegister(string $firstName, string $lastName, string $email, ?string $phoneNumber, string $city, string $street, string $postalCode, string $houseNr, string $bus, string $password, bool $isOwner): void
    {
        try {
            // Check if the email is already in use
            try {
                if (UserModel::getUserByEmail($email) != null)
                    throw new InvalidInputException("email", "This email is already in use");
            } catch (NoUserException $e) { }

            // Create the user
            $this->session->set("currUser", UserModel::createNewUser($firstName, $lastName, $email, $phoneNumber, $password, $postalCode, $city, $street, $houseNr, $bus, $isOwner));

            // Send the response
            $this->sendAjaxResponse(self::$REGISTER_SUCCESS_CODE);
        }
        catch (InvalidInputException $e) {
            $this->sendAjaxResponse($e->getMessage());
        }
        catch (DatabaseException $e) {
            $this->sendAjaxResponse($e->getMessage());
        }
    }
}
