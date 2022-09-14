<?php

namespace Yeganehha\DigikalaSellerWebhook\Tests;

use Yeganehha\DigikalaSellerWebhook\APIHandler;
use Yeganehha\DigikalaSellerWebhook\DigikalaService;
use PHPUnit\Framework\TestCase;
use Yeganehha\DigikalaSellerWebhook\Exceptions\ListOrdersShouldBeOrderNModelException;
use Yeganehha\DigikalaSellerWebhook\Exceptions\OrdersNotArrayException;
use Yeganehha\DigikalaSellerWebhook\Exceptions\UnauthorizedException;

class DigikalaServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        PHPUnitUtil::setToken(true);
        PHPUnitUtil::setCONTENT(false , true);
    }


    public function testGetObjectStatic(){
        $this->assertInstanceOf(DigikalaService::class,DigikalaService::get());
    }

    /**
     * @throws UnauthorizedException|OrdersNotArrayException|ListOrdersShouldBeOrderNModelException
     */
    public function testGetOrdersWithOutToken(){
        $digikala = new DigikalaService();
        $digikala->orders();
        $this->assertIsArray($digikala->getOrders()[0]->variant->price);
    }

    /**
     * @throws UnauthorizedException|OrdersNotArrayException|ListOrdersShouldBeOrderNModelException
     */
    public function testGetOrdersWithToken(){
        $digikala = new DigikalaService(PHPUnitUtil::$token);
        $digikala->orders();
        $this->assertIsArray($digikala->getOrders()[0]->variant->price);
    }

    /**
     * @throws UnauthorizedException|OrdersNotArrayException|ListOrdersShouldBeOrderNModelException
     */
    public function testGetOrdersWithWongToken(){
        $this->expectException(UnauthorizedException::class);
        $digikala = new DigikalaService("wrong token");
        $digikala->orders();
    }

    /**
     * @throws UnauthorizedException|OrdersNotArrayException
     */
    public function testCustomizeOrderBeforeReceive(){
        $this->expectException(ListOrdersShouldBeOrderNModelException::class);
        $digikala = new DigikalaService();
        $digikala->onGetOrder(function (&$orders){
            $orders = ["Orders update to string and order id is:". $orders[0]->order_id];
        })->orders();
    }

    /**
     * @throws UnauthorizedException|ListOrdersShouldBeOrderNModelException
     */
    public function testCustomizeOrderSyntaxError(){
        $this->expectException(OrdersNotArrayException::class);
        $digikala = new DigikalaService();
        $digikala->onGetOrder(function (&$orders){
            $orders = null;
        })->orders();
    }

    /**
     * @throws UnauthorizedException|ListOrdersShouldBeOrderNModelException
     */
    public function testCustomizeOrderSyntaxErrorAfter(){
        $this->expectException(OrdersNotArrayException::class);
        $digikala = new DigikalaService();
        $digikala->orders();
        $digikala->onGetOrder(function (&$orders){
            $orders = null;
        });
    }

    /**
     * @throws UnauthorizedException|OrdersNotArrayException|ListOrdersShouldBeOrderNModelException
     */
    public function testCustomizeOrderChangeVariables(){
        $digikala = new DigikalaService();
        $digikala->onGetOrder(function ($orders){
            $orders[0]->order_id = 1234;
        })->orders();
        $this->assertEquals(1234, $digikala->getOrders()[0]->order_id);
    }

    /**
     * @throws UnauthorizedException|OrdersNotArrayException
     * @throws ListOrdersShouldBeOrderNModelException
     */
    public function testCustomizeOrderAfterGetOrdersFetchByValue(){
        $digikala = new DigikalaService();
        $previousOrders = $digikala->orders()->getOrders();
        $digikala->getOrders(true);
        $digikala->onGetOrder(function ($ordersItems){
            $ordersItems[0]->order_id = 1234;
        });
        $this->assertEquals(171942704 , $previousOrders[0]->order_id);
    }

    /**
     * @throws UnauthorizedException|OrdersNotArrayException
     * @throws ListOrdersShouldBeOrderNModelException
     */
    public function testCustomizeOrderAfterGetOrdersFetchByReference(){
        $digikala = new DigikalaService();
        $referenceOrders = $digikala->orders()->getOrders(true);
        $digikala->onGetOrder(function ($ordersItems){
            $ordersItems[0]->order_id = 1234;
        });
        $this->assertEquals(1234 , $referenceOrders[0]->order_id);
    }

    /**
     * @throws UnauthorizedException|OrdersNotArrayException
     * @throws ListOrdersShouldBeOrderNModelException
     */
    public function testCustomizeOrderAfterGetOrders(){
        $digikala = new DigikalaService();
        $digikala->orders();
        $digikala->onGetOrder(function ($ordersItems){
            $ordersItems[0]->order_id = 1234;
        });
        $this->assertEquals(1234, $digikala->getOrders()[0]->order_id);
    }

    public function testSetAPITokenByCreatNew(){
        new DigikalaService(null,"Api Token");
        $this->assertEquals("Api Token", APIHandler::$token);
    }

    public function testSetAPITokenByStatic(){
        DigikalaService::get(null,"Api Token");
        $this->assertEquals("Api Token", APIHandler::$token);
    }

    public function testSetAPITokenByCallMethod(){
        DigikalaService::get()->setApiToken("Api Token");
        $this->assertEquals("Api Token", APIHandler::$token);
    }
}
