<?php


namespace Yeganehha\DigikalaSellerWebhook\Exceptions;


use Exception;
use Yeganehha\DigikalaSellerWebhook\Model\Order;

class ListOrdersShouldBeOrderNModelException extends Exception
{
    public function __construct() {
        parent::__construct('If personalization has been done on the order list, each element of list should be '. Order::class);
    }
}