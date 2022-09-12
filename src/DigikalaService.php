<?php


namespace Yeganehha\DigikalaSellerWebhook;


use Yeganehha\DigikalaSellerWebhook\Model\Order;

class DigikalaService
{
    /**
     * in your panel at https://seller.digikala.com/api/webhook/
     * @var null
     */
    private $webhook_token = null;


    /**
     * in your panel at https://seller.digikala.com/api
     * @var null
     */
    private $api_token = null;


    /**
     * Reduce quantity of same product when new order received.
     * @var bool
     */
    private $update_quantity = true;


    /**
     * Send notification to telegram or discord or ... when new order received.
     * @var bool
     */
    private $send_notification = true;


    private $Orders = [];
    /**
     * Provide Digikala webhook token for authorization. You can find the token
     * in your panel at https://seller.digikala.com/api/webhook/
     * @param null $webhook_token
     *
     * Provide Digikala token for authorization. You can find the token
     * in your panel at https://seller.digikala.com/api/
     * @param null $api_token
     *
     * Reduce quantity of same product when new order received.
     * @param bool $update_quantity
     */
    public function __construct($webhook_token = null, $api_token = null , bool $update_quantity = true)
    {
        $this->webhook_token = $webhook_token;
        $this->api_token = $api_token;
        $this->update_quantity = $update_quantity;
    }

    /**
     * @param null $webhook_token
     * @param null $api_token
     * @param bool $update_quantity
     * @return DigikalaService
     */
    public static function get($webhook_token = null, $api_token = null , bool $update_quantity = true) : DigikalaService
    {
        return new DigikalaService($webhook_token, $api_token ,  $update_quantity);
    }

    /**
     * Provide Digikala webhook token for authorization. You can find the token
     * in your panel at https://seller.digikala.com/api/webhook/
     * @param null $webhook_token
     * @return DigikalaService
     */
    public function setWebhookToken($webhook_token): DigikalaService
    {
        $this->webhook_token = $webhook_token;
        return $this;
    }

    /**
     * Provide Digikala token for authorization. You can find the token
     * in your panel at https://seller.digikala.com/api/
     * @param null $api_token
     * @return DigikalaService
     */
    public function setApiToken($api_token): DigikalaService
    {
        $this->api_token = $api_token;
        return $this;
    }

    /**
     * Reduce quantity of same product when new order received Automatically.
     * @return DigikalaService
     */
    public function UpdateQuantityAutomatically(): DigikalaService
    {
        $this->update_quantity = true;
        return $this;
    }

    /**
     * Disable reduce quantity of same product when new order received Automatically.
     * @return DigikalaService
     */
    public function UpdateQuantityManually(): DigikalaService
    {
        $this->update_quantity = false;
        return $this;
    }

    /**
     * Send notification to telegram or discord or ... when new order received Automatically.
     * @return DigikalaService
     */
    public function sendNotificationAutomatically(): DigikalaService
    {
        $this->send_notification = true;
        return $this;
    }

    /**
     * Disable send notification to telegram or discord or ... when new order received Automatically.
     * @return DigikalaService
     */
    public function sendNotificationManually(): DigikalaService
    {
        $this->send_notification = false;
        return $this;
    }

    /**
     * Get List of all order when new webhook received.
     * @return array
     * @throws Exceptions\UnauthorizedException
     */
    public function getListOrdersFromWebhook(): array
    {
        $WebhookHandler = new WebhookHandler($this->webhook_token);
        $this->orders = $WebhookHandler->getOrders();
        if ( $this->update_quantity )
        {
            //Todo : add auto update quantity
        }
        if ( $this->send_notification )
        {
            //Todo : add send order notification
        }
        return $this->orders;
    }

    /**
     * Get List of all order when new webhook received.
     * @return array
     * @throws Exceptions\UnauthorizedException
     */
    public function orders(): array
    {
        return $this->getListOrdersFromWebhook();
    }
}