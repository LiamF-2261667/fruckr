<?php

namespace App\Controllers;

class AccessibilityController extends BaseController
{
    /* Attributes */
    public static string $INDEX_ROUTE = '/accessibility';

    /* Methods */
    public function index()
    {
        return $this->viewingPage('Accessibility');
    }
}