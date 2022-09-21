<?php

namespace Yeganehha\DigikalaSellerWebhook\Loggers;

use Monolog\Handler\MissingExtensionException;
use Monolog\Handler\TelegramBotHandler;
use Monolog\Logger as Log;
use Yeganehha\DigikalaSellerWebhook\Loggers\Handlers\DiscordWebhookHandler;

class Logger
{
    public static $loggerObject = [];

    public static $telegramWebhook = null;
    public static $telegramChannel = null;
    public static $discordWebhook = null;
    public static $CustomHandlers = [];

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

        if ( self::$discordWebhook )
            $logger->pushHandler(new DiscordWebhookHandler(self::$discordWebhook,Log::INFO , true,DiscordWebhookHandler::Embed));

        if ( self::$CustomHandlers )
            foreach ( self::$CustomHandlers as $handler)
                $logger->pushHandler($handler);

        self::$loggerObject[$name] = $logger ;
        return self::$loggerObject[$name];
    }
}