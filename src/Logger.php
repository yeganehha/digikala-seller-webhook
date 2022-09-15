<?php

namespace Yeganehha\DigikalaSellerWebhook;

use Monolog\Handler\StreamHandler;
use Monolog\Logger as Log;
class Logger
{
    public static $loggerObject = [];

    public static function make($name = 'default')
    {
        if ( isset(self::$loggerObject[$name])) {
            self::$loggerObject[$name]->reset();
            return self::$loggerObject[$name];
        }
        $logger = new Log($name);
        $logger->pushHandler(new StreamHandler(__DIR__.'/my_app.log', Log::INFO));
        self::$loggerObject[$name] = $logger ;
        return self::$loggerObject[$name];
    }
}