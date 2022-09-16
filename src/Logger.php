<?php

namespace Yeganehha\DigikalaSellerWebhook;

use Monolog\Handler\MissingExtensionException;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\TelegramBotHandler;
use Monolog\Logger as Log;
class Logger
{
    public static $loggerObject = [];

    public static $telegramWebhook = null;
    public static $telegramChannel = null;
    public static $discordWebhook = null;
    public static $slackWebhook = null;

    /**
     * @throws MissingExtensionException
     */
    public static function make($name = 'default') : Log
    {
        if ( isset(self::$loggerObject[$name])) {
            self::$loggerObject[$name]->reset();
            return self::$loggerObject[$name];
        }
        $logger = new Log($name);
        if ( self::$telegramWebhook and self::$telegramChannel )
            $logger->pushHandler(new TelegramBotHandler(self::$telegramWebhook, self::$telegramChannel ,Log::INFO , true,'MarkDown'));


        self::$loggerObject[$name] = $logger ;
        return self::$loggerObject[$name];
    }
}