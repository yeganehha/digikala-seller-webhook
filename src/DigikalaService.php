<?php


namespace Yeganehha\DigikalaSellerWebhook;


use GuzzleHttp\Exception\GuzzleException as GuzzleExceptionAlias;
use Monolog\Handler\ProcessableHandlerInterface;
use Yeganehha\DigikalaSellerWebhook\Exceptions\ListOrdersShouldBeOrderNModelException;
use Yeganehha\DigikalaSellerWebhook\Exceptions\OrdersNotArrayException;
use Yeganehha\DigikalaSellerWebhook\Loggers\Logger;
use Yeganehha\DigikalaSellerWebhook\Model\Order;

class DigikalaService
{
    /**
     * in your panel at https://seller.digikala.com/api/webhook/
     * @var null
     */
    private $webhook_token;


    /**
     * in your panel at https://seller.digikala.com/api
     * @var null
     */
    private $api_token;


    /**
     * Reduce quantity of same product when new order received.
     * @var bool
     */
    private $update_quantity;


    /**
     * Send notification to telegram or discord or ... when new order received.
     * @var bool
     */
    private $send_notification = true;


    /**
     * list of all orders per each webhook call
     * @var array
     */
    private $orders = [];


    /**
     * execute custom code, when orders receive. Exp: update order or call api or etc.
     * @var null
     */
    public $function = null ;

    public $telegramWebHook = null ;
    public $telegramChannel = null;
    public $discordWebHook = null;
    public $customHandlers = null;

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
        $this->initLogger();
        APIHandler::setToken($this->api_token);
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
     * @param string|null $webhook_token
     * @return DigikalaService
     */
    public function setWebhookToken(?string $webhook_token): DigikalaService
    {
        $this->webhook_token = $webhook_token;
        return $this;
    }

    /**
     * Provide Digikala token for authorization. You can find the token
     * in your panel at https://seller.digikala.com/api/
     * @param string|null $api_token
     * @return DigikalaService
     */
    public function setApiToken(?string $api_token): DigikalaService
    {
        $this->api_token = $api_token;
        APIHandler::setToken($this->api_token);
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
        $this->initLogger();
        return $this;
    }

    /**
     * Disable send notification to telegram or discord or ... when new order received Automatically.
     * @return DigikalaService
     */
    public function sendNotificationManually(): DigikalaService
    {
        $this->send_notification = false;
        $this->initLogger();
        return $this;
    }

    /**
     * Get List of all order when new webhook received.
     * @return DigikalaService
     * @throws Exceptions\UnauthorizedException
     * @throws OrdersNotArrayException|ListOrdersShouldBeOrderNModelException
     * @throws GuzzleExceptionAlias
     */
    public function getListOrdersFromWebhook(): DigikalaService
    {
        $WebhookHandler = new WebhookHandler($this->webhook_token);
        $this->orders = $WebhookHandler->getOrders();

        // customize orders
        if ( $this->function != null )
            ($this->function)($this->orders);

        if ( ! is_array($this->orders) )
            throw new OrdersNotArrayException();
        foreach ( $this->orders as $order)
            if( ! $order instanceof  Order )
                throw new ListOrdersShouldBeOrderNModelException();

        if ( $this->update_quantity )
        {
            foreach ( $this->orders as $order)
                APIHandler::updateAllVariantSupplierCode($order->variant->supplier_code , ['seller_stock' => $order->variant->stock['in_seller_warehouse']]);
        }
        return $this;
    }

    /**
     * Get List of all order when new webhook received.
     * @return DigikalaService
     * @throws Exceptions\UnauthorizedException
     * @throws OrdersNotArrayException
     * @throws ListOrdersShouldBeOrderNModelException
     * @throws GuzzleExceptionAlias
     */
    public function orders(): DigikalaService
    {
        return $this->getListOrdersFromWebhook();
    }

    /**
     * execute custom code, when orders receive. Exp: update order or call api or etc.
     *
     * Example:
     * $digikala->onGetOrder(function(&orders) { $orders = [] ; } ); // change orders to empty
     * $digikala->onGetOrder(function(&orders) { $orders[0]->order_id = 1234 ; } ); // change order id of first order
     * $digikala->onGetOrder(function(orders) { var_dump($orders) } ); // show all orders
     *
     * @param $function
     * @return DigikalaService
     * @throws OrdersNotArrayException|ListOrdersShouldBeOrderNModelException
     */
    public function onGetOrder($function): DigikalaService
    {
        if ( ! empty($this->orders) )
        {
            $function($this->orders);
            if ( ! is_array($this->orders) )
                throw new OrdersNotArrayException();
            foreach ( $this->orders as $order)
                if( ! $order instanceof  Order )
                    throw new ListOrdersShouldBeOrderNModelException();
        }
        else
            $this->function = $function;
        return $this;
    }

    /**
     * get list of all orders per each webhook call
     * @param bool $isPassByReference
     * @return array
     */
    public function getOrders(bool $isPassByReference = false): array
    {
        if ( $isPassByReference )
            return $this->orders;
        $result = [];
        foreach ( $this->orders as $order)
            $result[] = clone $order;
        return $result;
    }

    private function initLogger(): void
    {
        if ( $this->send_notification ){
            Logger::$telegramWebhook = $this->telegramWebHook;
            Logger::$telegramChannel = $this->telegramChannel;
            Logger::$discordWebhook = $this->discordWebHook;
            Logger::$CustomHandlers = $this->customHandlers;
        } else {
            Logger::$telegramWebhook = null;
            Logger::$telegramChannel = null;
            Logger::$discordWebhook = null;
            Logger::$CustomHandlers = null;
        }
    }

    /**
     * @param string|ProcessableHandlerInterface $telegramWebHook
     * @param string|int|ProcessableHandlerInterface $telegramChannel
     * @return DigikalaService
     */
    public function setTelegram($telegramWebHook , $telegramChannel) : DigikalaService
    {
        $this->telegramWebHook = $telegramWebHook;
        $this->telegramChannel = $telegramChannel;
        $this->initLogger();
        return $this;
    }

    /**
     * @param string|ProcessableHandlerInterface $discordWebHook
     * @return DigikalaService
     */
    public function setDiscordWebHook($discordWebHook) : DigikalaService
    {
        $this->discordWebHook = $discordWebHook;
        $this->initLogger();
        return $this;
    }

    /**
     * @param ProcessableHandlerInterface $customHandlers
     * @return DigikalaService
     */
    public function setCustomHandlers(ProcessableHandlerInterface $customHandlers) : self
    {
        $this->customHandlers[] = $customHandlers;
        $this->initLogger();
        return $this;
    }

    /**
     * @return DigikalaService
     */
    public function clearCustomHandlers() : self
    {
        $this->customHandlers =[] ;
        $this->initLogger();
        return $this;
    }
}