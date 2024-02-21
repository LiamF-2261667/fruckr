<?php

namespace App\Controllers;

use App\Exceptions\NoUserException;
use App\Models\UserModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

class LoginController extends BaseController
{
    /* Routes */
    public static string $INDEX_ROUTE = "/login";
    public static string $LOGIN_ROUTE = "/login/login";

    /* Methods */
    /**
     * The index page of the login controller
     */
    public function index()
    {
        // Make sure only not logged in used can see this route
        if ($this->session->get('currUser') !== null)
            return $this->defaultRedirect();

        // Get the redirectionRoute for redirecting the user after they log in
        $this->setRedirection();

        $this->unloadUnnecessaryData();

        return $this->viewingPage('Login');
    }

    /**
     * Set the redirection route for the user
     */
    private function setRedirection()
    {
        $redirectionRoute = $this->session->get('_ci_previous_url');
        $prevRedirectionRoute = $this->session->get('redirection');
        if (str_contains($redirectionRoute, "logout") || str_contains($redirectionRoute, "login"))
            if ($redirectionRoute !== null)
                $redirectionRoute = $prevRedirectionRoute;
            else
                $redirectionRoute = "/";

        $this->session->set('redirection', $redirectionRoute);
    }

    /**
     * AJAX POST FUNCTION: Login a user
     * @return void
     */
    public function login()
    {
        // Get the inputs
        $email = request()->getJsonVar('email');
        $password = request()->getJsonVar('password');

        // Make sure they are filled
        if ($email == null      || !isset($email)       ||
            $password == null   || !isset($password))   {
            $this->sendAjaxResponse(json_encode(array("success" => false, "error" => "Please fill in the email and password")));
            return;
        }

        // Login into the user
        $this->tryLogin($email, $password);
    }

    /**
     * Try to log in a user
     * @param string $email The email of the user
     * @param string $password The password of the user
     * @return void
     */
    private function tryLogin(string $email, string $password): void
    {
        try {
            // Get the user by email
            $user = UserModel::getUserByEmail($email);

            // Check if the password is correct
            if (!$user->isCorrectPassword($password)) {
                $this->sendAjaxResponse(json_encode(array("success" => false, "error" => "Incorrect password")));
                return;
            }

            // Set the current user
            $this->session->set("currUser", $user);

            // Get the redirection and send the response
            $this->sendAjaxResponse(json_encode(array("success" => true, "redirect" => $this->session->get('redirection'))));
            $this->session->remove('redirection');
        }
        catch (NoUserException $e) {
            $this->sendAjaxResponse(json_encode(array("success" => false, "error" => "No user with that email exists")));
        }
        catch (\Exception $e) {
            $this->sendAjaxResponse(json_encode(array("success" => false, "error" => $e->getMessage())));
        }
    }
}
