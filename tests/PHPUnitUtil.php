<?php


namespace Yeganehha\DigikalaSellerWebhook\Tests;


use ReflectionClass;
use ReflectionException;

class PHPUnitUtil
{
    public static $token = "Sample Token";
    public static $order = '[{"order_item_id":527232606,"order_id":171942704,"variant":{"id":33089218,"seller_id":948629,"site":"digikala","is_active":true,"is_archived":false,"title":"هندزفری بی سیم لنوو مدل HAJ XT90 POI | مشکی | گارانتی اصالت و سلامت فیزیکی کالا","product":{"id":7325682,"category_id":211,"title":"هندزفری بی سیم لنوو مدل HAJ XT90 POI","shipping_nature_id":1},"shipping_type":"digikala","supplier_code":"LENOVO-XT90-BLACK","dk_lead_time":1,"sbs_lead_time":0,"stock":{"in_the_way":0,"in_digikala_warehouse":0,"in_seller_warehouse":1,"reserved_stocks":{"seller":0,"digikala":1}},"price":{"id":1027394059,"selling_price":3590000,"rrp_price":3590000,"discount":0,"order_limit":1,"is_promotion_price":false,"tags":null}},"quantity":1,"shipping_type":"digikala","order_status":"warehouse","selling_price":3381000,"created_at":"2022-09-01 17:55:36","cart_closed_at":"2022-09-01 17:55:36","shipment_status":"pending","commitment_date":"2022-09-04 00:00:00"}]';
    public static function setToken($isCorrect){
        unset($_SERVER["HTTP_AUTHORIZATION"]);
        $_SERVER["HTTP_AUTHORIZATION"]= self::$token . ( ! $isCorrect ? " But Wrong" : "");
    }
    public static  function setCONTENT($justEmpty , $isCorrect){
        unset($_REQUEST["CONTENT"]);
        if ( ! $justEmpty )
            $_REQUEST["CONTENT"]=  ! $isCorrect ? '[{"order_item_id":527232606,"order_id":171942704}]' : self::$order;
    }

    /**
     * @throws ReflectionException
     */
    public static function callMethod($obj, $name, array $args) {
        $class = new ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }
}