<?php

namespace Yeganehha\DigikalaSellerWebhook\Tests;

use Yeganehha\DigikalaSellerWebhook\DigikalaService;
use PHPUnit\Framework\TestCase;
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
     * @throws UnauthorizedException|OrdersNotArrayException
     */
    public function testGetOrdersWithOutToken(){
        $digikala = new DigikalaService();
        $digikala->orders();
        $this->assertIsArray($digikala->orders[0]->variant->price);
    }

    /**
     * @throws UnauthorizedException|OrdersNotArrayException
     */
    public function testGetOrdersWithToken(){
        $digikala = new DigikalaService(PHPUnitUtil::$token);
        $digikala->orders();
        $this->assertIsArray($digikala->orders[0]->variant->price);
    }

    /**
     * @throws UnauthorizedException|OrdersNotArrayException
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
        $digikala = new DigikalaService();
        $digikala->onGetOrder(function (&$orders){
            $orders = ["Orders update to string and order id is:". $orders[0]->order_id];
        })->orders();
        $this->assertEquals(["Orders update to string and order id is:171942704"], $digikala->orders);
    }

    /**
     * @throws UnauthorizedException
     */
    public function testCustomizeOrderSyntaxError(){
        $this->expectException(OrdersNotArrayException::class);
        $digikala = new DigikalaService();
        $digikala->onGetOrder(function (&$orders){
            $orders = null;
        })->orders();
    }

    /**
     * @throws UnauthorizedException
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
     * @throws UnauthorizedException|OrdersNotArrayException
     */
    public function testCustomizeOrderChangeVariables(){
        $digikala = new DigikalaService();
        $digikala->onGetOrder(function ($orders){
            $orders[0]->order_id = 1234;
        })->orders();
        $this->assertEquals(1234, $digikala->orders[0]->order_id);
    }

    /**
     * @throws UnauthorizedException|OrdersNotArrayException
     */
    public function testCustomizeOrderAfterGetOrders(){
        $digikala = new DigikalaService();
        $previousOrders = $digikala->orders();
        $digikala->onGetOrder(function ($ordersItems){
            $ordersItems[0]->order_id = 1234;
        });
        $this->assertEquals([171942704 , 1234], [$previousOrders[0]->order_id,$digikala->orders[0]->order_id]);
    }
}
