<?php

namespace App\Controllers;

use App\Exceptions\InvalidInputException;
use App\Exceptions\NoUserException;
use App\Models\UserModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

class ProfileController extends BaseController
{
    /* Routes */
    public static string $INDEX_ROUTE = "/profile";
    public static string $UPDATE_ROUTE = "/profile/update";

    /* Codes */
    private static string $UPDATE_SUCCESS_CODE = "success";

    /* Methods */
    /**
     * The index page of a user profile
     */
    public function index()
    {
        if ($this->session->get('currUser') === null)
            return $this->defaultRedirect();

        $this->unloadUnnecessaryData();

        return $this->viewingPage('Profile');
    }

    /**
     * AJAX POST FUNCTION: Update the data of the current user
     * @return void
     */
    public function update()
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
        $this->tryUpdate($firstName, $lastName, $email, $phoneNumber, $city, $street, $postalCode, $houseNr, $bus, $password);
    }

    /**
     * Try to update the data of the current user
     * @param string $firstName the new first name
     * @param string $lastName the new last name
     * @param string $email the new email
     * @param string|null $phoneNumber the new phone number
     * @param string $city the new city
     * @param string $street the new street
     * @param string $postalCode the new postal code
     * @param string $houseNr the new house number
     * @param string $bus the new bus
     * @param string $password the current password of the user
     * @return void
     */
    private function tryUpdate(string $firstName, string $lastName, string $email, ?string $phoneNumber, string $city, string $street, string $postalCode, string $houseNr, string $bus, string $password): void
    {
        try {
            // Get the user by email
            $user = $this->session->get("currUser");

            // Check if the password is correct
            if (!$user->isCorrectPassword($password)) {
                $this->sendAjaxResponse("The password is incorrect");
                return;
            }

            // Update the user
            $this->session->set("currUser", $user->updateData($firstName, $lastName, $phoneNumber, $postalCode, $city, $street, $houseNr, $bus));

            // Send the response
            $this->sendAjaxResponse(self::$UPDATE_SUCCESS_CODE);
        }
        catch (NoUserException $e) {
            $this->sendAjaxResponse("The email is not registered");
        }
        catch (InvalidInputException $e) {
            $this->sendAjaxResponse($e->getMessage());
        }
        catch (DatabaseException $e) {
            $this->sendAjaxResponse($e->getMessage());
        }
    }
}