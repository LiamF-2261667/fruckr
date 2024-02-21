<?php

namespace App\Controllers;

class OrderConfirmationController extends BaseController
{
    /* Attributes */
    public static string $INDEX_ROUTE = '/order/confirmation';

    /* Constructor */

    /* Methods */
    public function index()
    {
        // Make sure the user has a completed order, otherwise redirect
        if ($this->session->get('orderPosted') === null)
            return $this->defaultRedirect();

        $this->unloadUnnecessaryData();

        // Clear cart for future purchases
        $this->session->remove('cart');

        // Let the orderPosted session variable expire on its own
        // So that page refreshes don't cause the user to be redirected

        // Get the page data
        $data = ["orderPosted" => $this->session->get('orderPosted')];

        // Send the view
        return $this->viewingPage('Order_Confirmation', $data);
    }
}