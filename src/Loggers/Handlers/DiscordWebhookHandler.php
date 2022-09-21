<?php declare(strict_types=1);


namespace Yeganehha\DigikalaSellerWebhook\Loggers\Handlers;

/**
 * Handler send logs to Discord using Discord Webhook.
 *
 * How to use:
 *  1) Create a discord server.
 *  2) Create a text channel inside the created server from step 1.
 *  3) Click on edit channel icon in front of the created channel from step 2.
 *  4) Click on `Integration` tab.
 *  5) Click on `Create Webhook` button.
 *  6) Click on `Create Webhook` button.
 *  7) Click on `Copy Webhook URL` button.
 *
 * Use Discord webhook url from step 7 to create instance of DiscordWebhookHandler
 *
 *
 * @author Erfan Ebrahimi <me@erfanebrahimi.ir> [https://erfanebrahimi.ir]
 *
 * @phpstan-import-type Record from \Monolog\Logger
 */

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\MissingExtensionException;
class DiscordWebhookHandler extends AbstractProcessingHandler
{
    private const WEBHOOK_API = 'https://discord.com/api/webhooks/';
    private const COLORS = [
        'DEBUG' => "707070",
        'INFO' => "00fff7",
        'NOTICE' => "105deb",
        'WARNING' => "fffb94",
        'ERROR' => "ffb700",
        'CRITICAL' => "ff7700",
        'ALERT' => "ff4d00",
        'EMERGENCY' => "ebf7b0",
        'API' => "e06df7",
    ];

    public const Markdown = "Markdown";
    public const Embed = "Embed";

    /**
     * URL of Webhook.
     * @var string
     */
    private $WEBHOOK_URL;

    /**
     * The available values of parseMode according to the Discord webhook documentation
     */
    private const AVAILABLE_PARSE_MODES = [
        'Markdown',
        'Embed',
    ];


    /**
     * The kind of formatting that is used for the message.
     * in AVAILABLE_PARSE_MODES
     * @var ?string
     */
    private $parseMode;

    /**
     * Username of writer message (optional).
     * @var string
     */
    private $username;

    /**
     * Avatar of writer message (optional).
     * @var string
     */
    private $avatar_url;

    /**
     * true if this is a TTS message (optional).
     * @var bool
     */
    private $tts;

    /**
     * @param string $WEBHOOK_URL Discord webhook URL
     * @param string $parseMode The kind of formatting that is used for the message.
     * @param string $username Username of writer message.
     * @param string $avatar_url Avatar of writer message.
     * @param bool $tts true if this is a TTS message.
     * @throws MissingExtensionException
     */
    public function __construct(
        string $WEBHOOK_URL,
               $level = Logger::DEBUG,
        bool   $bubble = true,
        string $parseMode = 'Markdown',
        string $username = 'Digikala logger',
        string $avatar_url = 'data:image/png;base64,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',
        bool   $tts = false
    )
    {
        if (!extension_loaded('curl')) {
            throw new MissingExtensionException('The curl extension is needed to use the DiscordWebhookHandler');
        }
        if ( ! substr($WEBHOOK_URL, 0, strlen(self::WEBHOOK_API)) == self::WEBHOOK_API ) {
            throw new MissingExtensionException('Webhook URL is not correct!');
        }
        parent::__construct($level, $bubble);
        $this->WEBHOOK_URL = $WEBHOOK_URL;
        $this->setParseMode($parseMode);
        $this->setUsername($username);
        $this->setAvatarUrl($avatar_url);
        $this->setTTS($tts);
    }

    public function setParseMode(string $parseMode = null): self
    {
        $parseMode = ucfirst(strtolower($parseMode));
        if ($parseMode !== null && !in_array($parseMode, self::AVAILABLE_PARSE_MODES)) {
            throw new \InvalidArgumentException('Unknown parseMode, use one of these: ' . implode(', ', self::AVAILABLE_PARSE_MODES) . '.');
        }
        $this->parseMode = $parseMode;
        return $this;
    }

    public function setUsername(string $username = 'Log'): self
    {
        $this->username = $username;
        return $this;
    }

    public function setAvatarUrl(string $avatar_url = 'https://cdn.discordapp.com/attachments/667370472828043284/1020287124597133332/log.png'): self
    {
        $this->avatar_url = $avatar_url;
        return $this;
    }

    public function setTTS(bool $tts = false): self
    {
        $this->tts = $tts;
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function write(array $record): void
    {
        $this->sendCurl($record);
    }


    protected function sendCurl(array $record): void
    {
        $ch = curl_init();
        $url = $this->WEBHOOK_URL;
        $headers = [ 'Content-Type: application/json','charset=utf-8' ];
        $data = [
            'username' => $this->username,
            'avatar_url' => $this->avatar_url,
            'tts' => $this->tts
        ];
        if ( $this->parseMode == "Markdown")
            $data['content'] = $record['formatted'];
        else {
            $embed = [
                'description' => $record['message']."\n\n**Date:** ".$record['datetime'],
                "title" => $record['channel'] .' ('.$record['level_name'].')',
                'color' => $this->color($record['level_name']),
                "type" => "rich",
                "fields" => null
            ];
            $record['context'] = $this->array_flatten($record['context']);
            foreach ( $record['context'] as $name => $value )
                $embed['fields'][] = ["name" => $name,"value" => $value,"inline" => true];

            $data['embeds'] = [$embed];
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $result = Curl\Util::execute($ch);
        if (!is_string($result)) {
            throw new RuntimeException('Discord Webhook error. Description: No response');
        }
        $result = json_decode($result, true);

        if (isset($result['message'])) {
            throw new RuntimeException('Discord Webhook error. Description: ' . $result['message']);
        }
    }

    private function color(string $level_name)
    {
        return hexdec(self::COLORS[$level_name] ?? "FFFFFF");
    }

    private function array_flatten($array , $separator = '.') : array
    {

        $return = array();
        foreach ($array as $key => $value) {
            if( is_object($value) )
                $value =  (array) $value;
            if (is_array($value)){
                foreach ($value as $valueKey => $valueValue) {
                    $value[$key.$separator.$valueKey] = $valueValue;
                    unset($value[$valueKey]);
                }
                $return = array_merge($return, $this->array_flatten($value,$separator));
            }
            else {
                $return[$key] = $value;
            }
        }
        return $return;
    }
}
