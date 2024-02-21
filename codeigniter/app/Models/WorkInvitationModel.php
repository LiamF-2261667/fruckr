<?php

namespace App\Models;

use App\Database\DatabaseHandler;
use App\Exceptions\InvalidInputException;
use App\Exceptions\InvitationException;
use App\Exceptions\MailException;
use App\Exceptions\NoDataException;
use App\Exceptions\NoUserException;
use App\Factories\EmailFactory;
use App\Helpers\EmailSender;

class WorkInvitationModel
{
    /* Attributes */
    private ?FoodtruckModel $foodtruck = null;
    private ?string $workerEmail = null;

    /* Initializers */
    /**
     * Create a new WorkInvitationModel
     * @param FoodtruckModel $foodtruck the foodtruck
     * @param string $workerEmail the email of the worker
     * @return WorkInvitationModel the created WorkInvitationModel
     * @throws InvitationException if the invitation could not be created
     */
    public static function getWorkInvitation(FoodtruckModel $foodtruck, string $workerEmail): WorkInvitationModel
    {
        $invitation = new WorkInvitationModel();
        $invitation->loadByData($foodtruck, $workerEmail);
        return $invitation;
    }

    /**
     * Create a new WorkInvitationModel
     * @param FoodtruckModel $foodtruck the foodtruck
     * @param string $workerEmail the email of the worker
     * @throws InvitationException if the invitation could not be created
     * @throws InvalidInputException if the email is invalid
     */
    public static function createNewWorkInvitation(FoodtruckModel $foodtruck, string $workerEmail): WorkInvitationModel
    {
        // Make sure the email is valid
        if (!filter_var($workerEmail, FILTER_VALIDATE_EMAIL))
            throw new InvalidInputException("email", "Invalid email");

        // Make sure the worker is not already a worker at the foodtruck
        try {
            $user = UserModel::getUserByEmail($workerEmail);

            if ($foodtruck->containsWorker($user->getUid()))
                throw new InvalidInputException("", "User is already a worker at this foodtruck");
        }
        catch (NoUserException $ignored) { }

        // Make sure the invitation doesn't already exist
        try {
            $invitation = self::getWorkInvitation($foodtruck, $workerEmail);
            throw new InvalidInputException("invitation", "Invitation already exists");
        }
        catch (InvitationException $ignored) { }

        try {
            DatabaseHandler::getInstance()->query(self::$Q_CREATE_WORK_INVITATION, [$foodtruck->getId(), $workerEmail], false);
            return self::getWorkInvitation($foodtruck, $workerEmail);
        }
        catch (\Exception $e)
        {
            throw new InvitationException("Could not create invitation, please try again later.");
        }
    }

    /* Methods */
    /**
     * Load the invitation by the given foodtruck and worker email
     * @param FoodtruckModel $foodtruckModel the foodtruck
     * @param string $workerEmail the email of the worker
     * @throws InvitationException if the invitation could not be loaded
     */
    public function loadByData(FoodtruckModel $foodtruckModel, string $workerEmail): void
    {
        $result = DatabaseHandler::getInstance()->query(self::$Q_GET_WORK_INVITATION, [$foodtruckModel->getId(), $workerEmail]);

        if (count($result) === 0)
            throw new InvitationException("Invitation not found");

        $this->loadByDBResult($result[0]);
    }

    /**
     * Load the invitation by the given database result
     * @param $result mixed database result
     * @throws InvitationException if the invitation could not be loaded
     */
    private function loadByDBResult($result): void
    {
        try {
            $this->foodtruck = FoodtruckModel::getFoodtruckById($result->foodtruck);
            $this->workerEmail = $result->workerEmail;
        }
        catch (NoDataException $e)
        {
            throw new InvitationException("Trying to load invalid invitation data: " . $e->getMessage());
        }
    }

    /**
     * Accept the invitation
     * @throws InvitationException if the invitation could not be accepted
     */
    public function accept(): void
    {
        try {
            // Get the worker by the email
            $user = UserModel::getUserByEmail($this->workerEmail);

            // Add the user to the foodtruck workers if it doesn't exist already
            if (!FoodtruckWorkerModel::userIsWorker($user->getUid()))
                FoodtruckWorkerModel::createNewWorker($user->getUid());

            // Get the foodtruck worker
            $worker = FoodtruckWorkerModel::getWorkerByUserModel($user);

            // Add the foodtruck to the worker
            $worker->addFoodtruck($this->foodtruck);

            // Remove the invitation
            $this->remove();

            // Send a message to the foodtruck saying who joined
            EmailSender::sendMail(EmailFactory::workerJoined($this, $worker));
        }
        catch (MailException $ignored) { }
        catch (\Exception $e) {
            throw new InvitationException($e->getMessage());
        }
    }

    /**
     * Decline the invitation
     * @throws InvitationException if the invitation could not be declined
     */
    public function decline(): void
    {
        try {
            $this->remove();
        }
        catch (InvitationException $e)
        {
            throw new InvitationException("Could not decline invitation, please try again later.");
        }
    }

    /**
     * Remove the invitation
     * @throws InvitationException if the invitation could not be removed
     */
    public function remove(): void
    {
        try {
            DatabaseHandler::getInstance()->query(self::$Q_REMOVE_INVITATION, [$this->foodtruck->getId(), $this->workerEmail], false);
        }
        catch (\Exception $e)
        {
            throw new InvitationException("Could not remove invitation, please try again later.");
        }
    }

    /* Queries */
    private static string $Q_GET_WORK_INVITATION = 'SELECT * FROM OpenWorkInvitation WHERE foodtruck = ? AND workerEmail = ?';

    private static string $Q_CREATE_WORK_INVITATION = 'INSERT INTO OpenWorkInvitation (foodtruck, workerEmail) VALUES (?, ?)';

    private static string $Q_REMOVE_INVITATION = 'DELETE FROM OpenWorkInvitation WHERE foodtruck = ? AND workerEmail = ?';

    /* Getters */
    /**
     * Get the foodtruck
     * @return FoodtruckModel the foodtruck
     */
    public function getFoodtruck(): FoodtruckModel
    {
        return $this->foodtruck;
    }

    /**
     * Get the email of the worker
     * @return string the email of the worker
     */
    public function getWorkerEmail(): string
    {
        return $this->workerEmail;
    }

    public function getInvitationUrl(): string
    {
        return base_url() . 'foodtruck/' . $this->foodtruck->getId() . '/accept';
    }

    public function getDeclinationUrl(): string
    {
        return base_url() . 'foodtruck/' . $this->foodtruck->getId() . '/decline';
    }
}