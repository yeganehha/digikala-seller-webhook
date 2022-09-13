<?php

namespace Yeganehha\DigikalaSellerWebhook\Tests;

use Yeganehha\DigikalaSellerWebhook\Exceptions\UnauthorizedException;
use Yeganehha\DigikalaSellerWebhook\WebhookHandler;
use PHPUnit\Framework\TestCase;

class WebhookHandlerTest extends TestCase
{


    protected function setUp(): void
    {
        parent::setUp();
        PHPUnitUtil::setToken(true);
        PHPUnitUtil::setCONTENT(false , true);
    }

    public function testGetAuthenticateHeader(): void
    {
        $object = new WebhookHandler();
        $headersValue = PHPUnitUtil::callMethod($object,'requestHeaders',array());
        $this->assertEquals(
            $headersValue,[
                "Authorization" => PHPUnitUtil::$token
            ]);
    }


    public function testGetEmptyAuthenticateHeader(): void
    {
        PHPUnitUtil::setToken(false);
        $object = new WebhookHandler();
        $headersValue = PHPUnitUtil::callMethod($object,'requestHeaders',array());
        $this->assertNotEquals(
            $headersValue,[
                "Authorization" => PHPUnitUtil::$token
            ]);
    }

    public function testAuthenticateIgnoreEmptyTokenWithCorrectHeaderToken(){
        $object = new WebhookHandler();
        $headersValue = PHPUnitUtil::callMethod($object,'requestHeaders',array());
        $isAuthenticate = PHPUnitUtil::callMethod($object,'authorization',array($headersValue));
        $this->assertTrue($isAuthenticate);
    }

    public function testAuthenticateIgnoreEmptyTokenWithWrongHeaderToken(){
        PHPUnitUtil::setToken(false);
        $object = new WebhookHandler();
        $headersValue = PHPUnitUtil::callMethod($object,'requestHeaders',array());
        $isAuthenticate = PHPUnitUtil::callMethod($object,'authorization',array($headersValue));
        $this->assertTrue($isAuthenticate);
    }

    public function testAuthenticateCorrectHeaderToken(){
        $object = new WebhookHandler(PHPUnitUtil::$token);
        $headersValue = PHPUnitUtil::callMethod($object,'requestHeaders',array());
        $isAuthenticate = PHPUnitUtil::callMethod($object,'authorization',array($headersValue));
        $this->assertTrue($isAuthenticate);
    }

    public function testAuthenticateWrongHeaderToken(){
        PHPUnitUtil::setToken(false);
        $object = new WebhookHandler(PHPUnitUtil::$token);
        $headersValue = PHPUnitUtil::callMethod($object,'requestHeaders',array());
        $isAuthenticate = PHPUnitUtil::callMethod($object,'authorization',array($headersValue));
        $this->assertFalse($isAuthenticate);
    }


    public function testReceiveFromContactRequest(){
        $object = new WebhookHandler();
        $body = PHPUnitUtil::callMethod($object,'requestBody',array());
        $this->assertEquals($body , PHPUnitUtil::$order);
    }

    public function testReceiveEmptyContactRequestWithOutPost(){
        PHPUnitUtil::setCONTENT(true , true);
        $object = new WebhookHandler();
        $body = PHPUnitUtil::callMethod($object,'requestBody',array());
        $this->assertNull($body);
    }

    /**
     * @throws UnauthorizedException
     */
    public function testReceiveOrderObject(){
        $object = new WebhookHandler();
        $this->assertIsObject($object->getOrders()[0]);
    }

    /**
     * @throws UnauthorizedException
     */
    public function testReceiveAllAttributeOfOrderObject(){
        $object = new WebhookHandler();
        $this->assertObjectHasAttribute('price' ,$object->getOrders()[0]->variant);
    }

    /**
     * @throws UnauthorizedException
     */
    public function testReceiveAllAttributeOfOrderVariantObject(){
        $object = new WebhookHandler();
        $this->assertIsArray($object->getOrders()[0]->variant->price);
    }

}
