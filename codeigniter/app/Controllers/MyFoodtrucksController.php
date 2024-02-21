<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

class MyFoodtrucksController extends BaseController
{
    public static string $INDEX_ROUTE = '/my-foodtrucks';

    public function index()
    {
        // Check if the user is allowed to view this page
        $currUser = $this->session->get('currUser');
        if (!$this->mayView($currUser))
            return $this->getRedirect($currUser);

        // Unload all unnecessary data to speed up the page loading
        $this->unloadUnnecessaryData();

        // Load the foodtrucks before loading the page
        $this->loadFoodtrucks();

        return $this->viewingPage('MyFoodtrucks');
    }

    /**
     * Load the foodtrucks of the current user
     */
    private function loadFoodtrucks()
    {
        $currUser = $this->session->get("currUser");

        // Check if the currUser is a worker
        if ($currUser !== null) {
            if ($currUser->isFoodtruckWorker()) {
                // Load the foodtrucks of the worker
                $currUser->getWorker()->loadFoodtrucks();
                $this->session->set("currUser", $currUser);
            }
        }
    }

    /**
     * Check whether a user may view this page
     * @param UserModel|null $user the user to check on
     * @return bool true if the user is allowed on the page, false otherwise
     */
    private function mayView(?UserModel $user) : bool
    {
        return $user !== null && $user->isFoodtruckWorker();
    }

    /**
     * @param UserModel|null $user The user to check where it should get redirected towards
     * @return RedirectResponse The redirection
     */
    private function getRedirect(?UserModel $user): RedirectResponse
    {
        // Go to log in if not logged in
        if ($user === null)
            return $this->redirect(LoginController::$INDEX_ROUTE);

        return $this->defaultRedirect();
    }
}