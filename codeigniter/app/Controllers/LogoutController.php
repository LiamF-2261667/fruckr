<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;

class LogoutController extends BaseController
{
    /* Routes */
    public static string $INDEX_ROUTE = "/logout";

    /* Methods */
    /**
     * Logout the current user
     * @return RedirectResponse the redirection
     */
    public function index(): RedirectResponse
    {
        $this->unloadUnnecessaryData();

        // Remove the current user
        $this->session->remove("currUser");

        // Redirect the user
        return $this->defaultRedirect();
    }
}
