<?php


namespace Yeganehha\DigikalaSellerWebhook\Exceptions;


use Exception;

class OrdersNotArrayException extends Exception
{
    public function __construct() {
        parent::__construct('If personalization has been done on the order list, it must remain a list and not be changed to a string, null or...');
    }
}