<?php

use Yeganehha\DigikalaSellerWebhook\DigikalaService;
use Yeganehha\DigikalaSellerWebhook\Exceptions\OrdersNotArrayException;
use Yeganehha\DigikalaSellerWebhook\Exceptions\UnauthorizedException;

if ( ! function_exists('digikala') ){
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
     * @return DigikalaService
     */
    function digikala($webhook_token = null, $api_token = null , bool $update_quantity = true): DigikalaService
    {
        return new DigikalaService($webhook_token, $api_token ,  $update_quantity);
    }
}

if ( ! function_exists('digikala_order') ){
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
     * @return array
     * @throws UnauthorizedException
     * @throws OrdersNotArrayException
     */
    function digikala_order($webhook_token = null, $api_token = null , bool $update_quantity = true):array
    {
        return digikala($webhook_token, $api_token ,  $update_quantity)->orders()->getOrders();
    }
}