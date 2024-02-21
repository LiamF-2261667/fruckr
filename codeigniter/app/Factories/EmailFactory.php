<?php

namespace App\Factories;

use App\DataObjects\EmailData;
use App\Models\FoodtruckWorkerModel;
use App\Models\OrderModel;
use App\Models\UserModel;
use App\Models\WorkInvitationModel;
use CodeIgniter\Email\Email;

class EmailFactory
{
    public static function orderReady(OrderModel $order): EmailData
    {
        $subject = 'Order ' . $order->getId() . ' is ready for pickup!';
        $message = '
            <h2>Order ' . $order->getId() . ' is ready for pickup!</h2>
            <p>Dear ' . $order->getClient()->getFullName() . ',</p>
            <p>Your order is ready for pickup at ' . $order->getFoodtruck()->getCurrAddress()->toString() . '.</p>
        ';

        return new EmailData($order->getClient()->getEmail(), $subject, $message);
    }

    public static function orderClaimed(OrderModel $order): EmailData
    {
        $subject = 'Would you like to review your order ' . $order->getId() . '?';
        $message = '
            <h2>Would you like to review your order ' . $order->getId() . '?</h2>
            <p>Dear ' . $order->getClient()->getFullName() . ',</p>
            <p>Your order has been picked up.</p>
            <a style="color: IndianRed" href="' . base_url() . '/review/' . $order->getId() . '">Click here to leave a review.</a>
        ';

        return new EmailData($order->getClient()->getEmail(), $subject, $message);
    }

    public static function workInvitation(WorkInvitationModel $invitation): EmailData
    {
        $subject = 'You have been invited to work at ' . $invitation->getFoodtruck()->getName() . '!';
        $message = '
            <h2>You have been invited to work at ' . $invitation->getFoodtruck()->getName() . '!</h2>
            <a style="color: IndianRed" href="' . $invitation->getInvitationUrl() . '">Click here to accept the invitation.</a><br />
            <br />
            <a style="color: IndianRed" href="' . $invitation->getDeclinationUrl() . '">Or here to decline.</a>
        ';

        return new EmailData($invitation->getWorkerEmail(), $subject, $message);
    }

    public static function workerJoined(WorkInvitationModel $invitation, FoodtruckWorkerModel $worker): EmailData
    {
        $subject = 'A new worker has joined ' . $invitation->getFoodtruck()->getName() . '!';
        $message = '
            <h2>' . $worker->getFullName() . ' joined the foodtruck!</h2>
            <p>He will from now be able to see and handle the incoming orders for ' . $invitation->getFoodtruck()->getName() . '.</p>
        ';

        return new EmailData($invitation->getFoodtruck()->getEmail(), $subject, $message);
    }
}