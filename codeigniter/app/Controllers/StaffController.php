<?php

namespace App\Controllers;

use App\Exceptions\InvitationException;
use App\Exceptions\MailException;
use App\Exceptions\NoDataException;
use App\Exceptions\NoUserException;
use App\Factories\EmailFactory;
use App\Helpers\EmailSender;
use App\Models\FoodtruckModel;
use App\Models\FoodtruckWorkerModel;
use App\Models\UserModel;
use App\Models\WorkInvitationModel;

class StaffController extends BaseController
{
    /* Attributes */
    public static string $INDEX_ROUTE = '/foodtruck/(:num)/staff';
    public static string $ADD_WORKER_ROUTE = '/foodtruck/addWorker';
    public static string $REMOVE_WORKER_ROUTE = '/foodtruck/removeWorker';

    public static string $ACCEPT_ROUTE = '/foodtruck/(:num)/accept';
    public static string $DECLINE_ROUTE = '/foodtruck/(:num)/decline';

    private ?FoodtruckModel $foodtruck;
    private array $workers;

    /* Methods */
    public function index($foodtruckId = null)
    {
        // Check if an id was given, otherwise redirect
        if ($foodtruckId === null)
            return $this->defaultRedirect();

        // Load the current user
        $currUser = $this->session->get('currUser');
        if ($currUser === null)
            return $this->redirect(LoginController::$INDEX_ROUTE);

        $this->unloadUnnecessaryData();

        // Load the foodtruck
        $this->loadFoodtruck(intval($foodtruckId));

        if ($this->foodtruck === null)
            return $this->viewingPage('errors/html/NoFoodtruckError');

        // Make sure the user is the owner of the foodtruck
        if ($this->foodtruck->getOwner()->getUid() !== $currUser->getUid())
            return $this->defaultRedirect();

        // Show the correct page
        return $this->viewingPage('Staff', $this->getData());
    }

    public function removeWorker()
    {
        $this->loadCurrData();

        // Get the uid to delete
        $uid = $this->request->getJsonVar('uid');

        // Make sure the uid is not the owner
        if ($uid === $this->foodtruck->getOwner()->getUid()) {
            $this->sendAjaxResponse(["success" => false, "error" => "You cannot remove the owner."]);
            return;
        }

        try {
            // Get the worker
            $worker = FoodtruckWorkerModel::getWorkerByUserUid(intval($uid));

            // Remove the worker
            $worker->removeFoodtruck($this->foodtruck);
        }
        catch (\Exception $e) {
            if (getenv('CI_ENVIRONMENT') !== 'production')
                $this->sendAjaxResponse(["success" => false, "error" => $e]);
            else
                $this->sendAjaxResponse(["success" => false, "error" => "Failed to remove the worker, please try again later."]);
            return;
        }

        // Send a response
        $this->sendAjaxResponse(["success" => true, "uid" => $uid]);
    }

    public function addWorker()
    {
        $this->loadCurrData();

        // Get the email to add
        $email = $this->request->getJsonVar('email');

        try {
            // Create a work invitation
            $invitation = WorkInvitationModel::createNewWorkInvitation($this->foodtruck, $email);

            // Send mail to the user
            EmailSender::sendMail(EmailFactory::workInvitation($invitation));
        }
        catch (InvitationException $e) {
            if (getenv('CI_ENVIRONMENT') !== 'production')
                $this->sendAjaxResponse(["success" => false, "error" => "Failed to send email: " . $e->getMessage()]);
            else
                $this->sendAjaxResponse(["success" => false,"error" => "Failed to add the user, please try again later."]);
            return;
        }
        catch (MailException $e) {
            if (getenv('CI_ENVIRONMENT') !== 'production')
                $this->sendAjaxResponse(["success" => false, "error" => "Failed to send email: " . $e->getMessage()]);
            else
                $this->sendAjaxResponse([
                    "success" => false,
                    "error" => "Failed to send email, you can manually try to add the user by giving him this link: " . $invitation->getInvitationUrl()
                ]);
            return;
        }
        catch (\Exception $e) {
            $this->sendAjaxResponse(["success" => false, "error" => $e->getMessage()]);
            return;
        }

        // Send a response
        $this->sendAjaxResponse(["success" => true, "email" => $email]);
    }

    public function accept($foodtruckId = null)
    {
        // Check if an id was given, otherwise redirect
        if ($foodtruckId === null)
            return $this->defaultRedirect();

        // Load the current user
        $currUser = $this->session->get('currUser');
        if ($currUser === null)
            return $this->redirect(LoginController::$INDEX_ROUTE);

        $this->unloadUnnecessaryData();

        // Load the foodtruck
        $this->loadFoodtruck(intval($foodtruckId));

        if ($this->foodtruck === null)
            return $this->viewingPage('errors/html/NoFoodtruckError');

        // Get the invitation
        try {
            $invitation = WorkInvitationModel::getWorkInvitation($this->foodtruck, $currUser->getEmail());
        }
        catch (InvitationException $e) {
            return $this->viewingPage('errors/html/NoInvitationError');
        }

        // Accept the invitation
        try {
            $invitation->accept();

            // Refresh the currUser
            $this->session->set('currUser', UserModel::getUserById($currUser->getUid()));
        }
        catch (InvitationException | NoUserException $e) {
            return $this->viewingPage('errors/html/InvitationError');
        }

        // Go to the foodtruck page
        return $this->defaultRedirect();
    }

    public function decline($foodtruckId = null) {
        // Check if an id was given, otherwise redirect
        if ($foodtruckId === null)
            return $this->defaultRedirect();

        // Load the current user
        $currUser = $this->session->get('currUser');
        if ($currUser === null)
            return $this->redirect(LoginController::$INDEX_ROUTE);

        $this->unloadUnnecessaryData();

        // Load the foodtruck
        $this->loadFoodtruck(intval($foodtruckId));

        if ($this->foodtruck === null)
            return $this->viewingPage('errors/html/NoFoodtruckError');

        // Get the invitation
        try {
            $invitation = WorkInvitationModel::getWorkInvitation($this->foodtruck, $currUser->getEmail());
        }
        catch (InvitationException $e) {
            return $this->viewingPage('errors/html/NoInvitationError');
        }

        // Decline the invitation
        try {
            $invitation->decline();
        }
        catch (InvitationException $e) {
            return $this->viewingPage('errors/html/InvitationError');
        }

        return $this->defaultRedirect();
    }

    /**
     * Load the foodtruck
     * @param int $foodtruckId the id of the foodtruck
     * @post $this->foodtruck is loaded from the database, or null if nothing is found
     */
    private function loadFoodtruck(int $foodtruckId)
    {
        // Try to load the foodtruck
        try {
            $this->foodtruck = FoodtruckModel::getFoodtruckById($foodtruckId);
            $this->workers = $this->foodtruck->getWorkers();

            $this->session->set("currFoodtruck", $this->foodtruck);
            $this->session->set("currWorkers", $this->workers);
        }

            // If nothing is found, set it to null
        catch (NoDataException $e) {
            $this->foodtruck = null;
        }
    }

    private function loadCurrData(): void
    {
        $this->foodtruck = $this->session->get("currFoodtruck");
        $this->workers = $this->session->get("currWorkers");
    }

    private function getData(): array
    {
        return [
            'foodtruck' => $this->foodtruck,
            'workers' => $this->workers
        ];
    }
}