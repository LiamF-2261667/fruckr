<?php

namespace App\Controllers;

use App\Models\FoodtruckOwnerModel;
use App\Models\FoodtruckWorkerModel;
use App\Models\UserModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Session\Session;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = [];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    protected Session $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        $this->session = \Config\Services::session();
    }

    /**
     * View a page
     * @param string $pageName The name of the page to view
     * @param array $data The data to pass to the page
     * @return string The page
     */
    protected function viewingPage(string $pageName, array $data = []): string
    {
        // Add the currUser to the page data
        $data['currUser'] = $this->session->get('currUser');

        // Getting the page
        return viewPage($pageName, $data);
    }

    /**
     * Send an ajax response
     * @param $responseData
     * @return void
     */
    protected function sendAjaxResponse($responseData): void
    {
        $response = response();
        $response->setStatusCode(ResponseInterface::HTTP_OK);
        $response->setJSON($responseData);
        $response->send();
    }

    /**
     * Unload unnecessary data from the current user
     * @post the foodtrucks from the worker model of the currUser are unloaded
     * @post the currFoodtruck is unloaded
     * @post the currChat is unloaded
     * @post the currWorkers is unloaded
     */
    protected function unloadUnnecessaryData() {
        $this->unloadCurrUserFoodtrucks();
        $this->unloadCurrFoodtruck();
        $this->unloadCurrChat();
        $this->unloadCurrWorkers();
    }

    /**
     * Unload the foodtrucks from the current user
     */
    private function unloadCurrUserFoodtrucks() {
        $currUser = $this->session->get('currUser');

        if ($currUser !== null)
            if ($currUser->isFoodtruckWorker())
                $currUser->getWorker()->unloadFoodtrucks();

        $this->session->set('currUser', $currUser);
    }

    /**
     * Unload the current foodtruck
     */
    private function unloadCurrFoodtruck() {
        $this->session->remove('currFoodtruck');
    }

    /**
     * Unload the current chat
     */
    private function unloadCurrChat()
    {
        $this->session->remove('currChat');
    }

    /**
     * Unload the current workers
     */
    private function unloadCurrWorkers()
    {
        $this->session->remove('currWorkers');
    }

    /**
     * Get the redirect to another controller
     * @param string $controllerRoute The route of the place you want to reach (ex. '/homepage')
     * @return RedirectResponse The redirection
     */
    protected function redirect(string $controllerRoute): RedirectResponse
    {
        return redirect()->route(substr($controllerRoute, 1));
    }

    private static array $ILLEGAL_DEFAULT_REDIRECTS = ["logout", "login", "signup"];
    private static array $ILLEGAL_DEFAULT_REDIRECTS_IF_LOGGED_OUT = ["/orders", "chat", "profile", "my-foodtrucks"];

    /**
     * Get the default redirect to another controller
     * (in case the route you want to view is illegal)
     * @return RedirectResponse The redirection
     */
    protected function defaultRedirect(): RedirectResponse
    {
        $illegalRedirects = self::$ILLEGAL_DEFAULT_REDIRECTS;

        if ($this->session->get('currUser') === null)
            $illegalRedirects = array_merge($illegalRedirects, self::$ILLEGAL_DEFAULT_REDIRECTS_IF_LOGGED_OUT);

        // Never go back to the illegal redirects
        foreach ($illegalRedirects as $illegalRedirect)
            if (str_contains($this->session->get('_ci_previous_url'), $illegalRedirect))
                return $this->redirect(HomepageController::$INDEX_ROUTE);

        // Go to the previous page
        return redirect()->back();
    }
}
