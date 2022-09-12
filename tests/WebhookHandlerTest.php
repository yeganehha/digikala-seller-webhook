<?php

namespace Yeganehha\DigikalaSellerWebhook\Tests;

use Yeganehha\DigikalaSellerWebhook\WebhookHandler;
use PHPUnit\Framework\TestCase;

class WebhookHandlerTest extends TestCase
{
    private $token = "Sample Token";
    private $order = '[{"order_item_id":527232606,"order_id":171942704,"variant":{"id":33089218,"seller_id":948629,"site":"digikala","is_active":true,"is_archived":false,"title":"هندزفری بی سیم لنوو مدل HAJ XT90 POI | مشکی | گارانتی اصالت و سلامت فیزیکی کالا","product":{"id":7325682,"category_id":211,"title":"هندزفری بی سیم لنوو مدل HAJ XT90 POI","shipping_nature_id":1},"shipping_type":"digikala","supplier_code":"LENOVO-XT90-BLACK","dk_lead_time":1,"sbs_lead_time":0,"stock":{"in_the_way":0,"in_digikala_warehouse":0,"in_seller_warehouse":1,"reserved_stocks":{"seller":0,"digikala":1}},"price":{"id":1027394059,"selling_price":3590000,"rrp_price":3590000,"discount":0,"order_limit":1,"is_promotion_price":false,"tags":null}},"quantity":1,"shipping_type":"digikala","order_status":"warehouse","selling_price":3381000,"created_at":"2022-09-01 17:55:36","cart_closed_at":"2022-09-01 17:55:36","shipment_status":"pending","commitment_date":"2022-09-04 00:00:00"}]';
    private function setToken($isCorrect){
        unset($_SERVER["HTTP_AUTHORIZATION"]);
        $_SERVER["HTTP_AUTHORIZATION"]= $this->token . ( ! $isCorrect ? " But Wrong" : "");
    }
    private function setCONTENT($justEmpty , $isCorrect){
        unset($_REQUEST["CONTENT"]);
        if ( ! $justEmpty )
            $_REQUEST["CONTENT"]=  ! $isCorrect ? '[{"order_item_id":527232606,"order_id":171942704}]' : $this->order;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->setToken(true);
        $this->setCONTENT(false , true);
    }

    public function testGetAuthenticateHeader(): void
    {
        $object = new WebhookHandler();
        $headersValue = PHPUnitUtil::callMethod($object,'requestHeaders',array());
        $this->assertEquals(
            $headersValue,[
                "Authorization" => $this->token
            ]);
    }


    public function testGetEmptyAuthenticateHeader(): void
    {
        $this->setToken(false);
        $object = new WebhookHandler();
        $headersValue = PHPUnitUtil::callMethod($object,'requestHeaders',array());
        $this->assertNotEquals(
            $headersValue,[
                "Authorization" => $this->token
            ]);
    }

    public function testAuthenticateIgnoreEmptyTokenWithCorrectHeaderToken(){
        $object = new WebhookHandler();
        $headersValue = PHPUnitUtil::callMethod($object,'requestHeaders',array());
        $isAuthenticate = PHPUnitUtil::callMethod($object,'authorization',array($headersValue));
        $this->assertTrue($isAuthenticate);
    }

    public function testAuthenticateIgnoreEmptyTokenWithWrongHeaderToken(){
        $this->setToken(false);
        $object = new WebhookHandler();
        $headersValue = PHPUnitUtil::callMethod($object,'requestHeaders',array());
        $isAuthenticate = PHPUnitUtil::callMethod($object,'authorization',array($headersValue));
        $this->assertTrue($isAuthenticate);
    }

    public function testAuthenticateCorrectHeaderToken(){
        $object = new WebhookHandler($this->token);
        $headersValue = PHPUnitUtil::callMethod($object,'requestHeaders',array());
        $isAuthenticate = PHPUnitUtil::callMethod($object,'authorization',array($headersValue));
        $this->assertTrue($isAuthenticate);
    }

    public function testAuthenticateWrongHeaderToken(){
        $this->setToken(false);
        $object = new WebhookHandler($this->token);
        $headersValue = PHPUnitUtil::callMethod($object,'requestHeaders',array());
        $isAuthenticate = PHPUnitUtil::callMethod($object,'authorization',array($headersValue));
        $this->assertFalse($isAuthenticate);
    }


    public function testReceiveFromContactRequest(){
        $object = new WebhookHandler();
        $body = PHPUnitUtil::callMethod($object,'requestBody',array());
        $this->assertEquals($body , $this->order);
    }

    public function testReceiveEmptyContactRequestWithOutPost(){
        $this->setCONTENT(true , true);
        $object = new WebhookHandler();
        $body = PHPUnitUtil::callMethod($object,'requestBody',array());
        $this->assertNull($body);
    }

    public function testReceiveOrderObject(){
        $object = new WebhookHandler();
        $this->assertIsObject($object->getOrders()[0]);
    }

    public function testReceiveAllAttributeOfOrderObject(){
        $object = new WebhookHandler();
        $this->assertObjectHasAttribute('price' ,$object->getOrders()[0]->variant);
    }

    public function testReceiveAllAttributeOfOrderVariantObject(){
        $object = new WebhookHandler();
        $this->assertIsArray($object->getOrders()[0]->variant->price);
    }

}
