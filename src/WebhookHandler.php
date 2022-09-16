<?php
namespace Yeganehha\DigikalaSellerWebhook;

use Yeganehha\DigikalaSellerWebhook\Exceptions\UnauthorizedException;
use Yeganehha\DigikalaSellerWebhook\Model\Order;

class WebhookHandler
{
    private $token;

    /**
     * Provide Digikala webhook token for authorization. You can find the token
     * in your panel at https://seller.digikala.com/api/webhook/
     * @param string|null $token
     */
    public function __construct(string $token = null)
    {
        $this->token = $token;
    }

    /**
     * Calling to function will analyze the request coming from Digikala and if
     * it is a valid data, it will return an array containing order items.
     * @throws UnauthorizedException
     */
    public function getOrders() : array
    {
        $headers = $this->requestHeaders();

        if (!$this->authorization($headers)) {
            throw new UnauthorizedException();
        }

        $body = $this->requestBody();
        $ordersObject = [];
        $orders = json_decode($body, true) ?? [];

        $logger = Logger::make('new_order');
        foreach ($orders as $order) {
            $temporary = Order::get()->setFromArray($order);
            $ordersObject[] = $temporary;
            $logger->info('New Order received' , ['order' => $temporary]);
        }

        return $ordersObject;
    }

    private function authorization($headers): bool
    {
        return $this->token == null || !empty($headers['Authorization']) && $headers['Authorization'] == $this->token;
    }

    private function requestHeaders()
    {
        // If running on apache server
        if (function_exists('apache_request_headers')) {
            // Return the much faster method
            return apache_request_headers();
        } elseif (extension_loaded('http')) {
            // Return the much faster method
            return http_get_request_headers();
        }
        // Setup the output
        $headers = array();
        // Parse the content type
        if (!empty($_SERVER['CONTENT_TYPE'])) {
            $headers['content-type'] = $_SERVER['CONTENT_TYPE'];
        }
        // Parse the content length
        if (!empty($_SERVER['CONTENT_LENGTH'])) {
            $headers['content-length'] = $_SERVER['CONTENT_LENGTH'];
        }
        foreach ($_SERVER as $key => $value) {
            // If there is no HTTP header here, skip
            if (strpos($key, 'HTTP_') !== 0) {
                continue;
            }
            // This is a dirty hack to ensure HTTP_X_FOO_BAR becomes x-foo-bar
            $headers[ucfirst(strtolower(str_replace(array('HTTP_', '_'), array('', '-'), $key)))] = $value;
        }
        return $headers;
    }

    private function requestBody()
    {
        if (!empty($_REQUEST['CONTENT'])) {
            return $_REQUEST['CONTENT'];
        }

        if (! isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
            return null;
        }

        if (extension_loaded('http')) {
            // Return the much faster method
            return http_get_request_body();
        }

        return file_get_contents('php://input');
    }
}