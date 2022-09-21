<?php


namespace Yeganehha\DigikalaSellerWebhook\Exceptions;


use Exception;

class UnauthorizedException extends Exception
{
    public function __construct() {
        parent::__construct('Token is not valid! when process '.$this->getFile()
            .' on line '.$this->getLine());
    }
}