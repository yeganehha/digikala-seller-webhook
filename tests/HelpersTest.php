<?php

namespace Yeganehha\DigikalaSellerWebhook\Tests;

use PHPUnit\Framework\TestCase;
use Yeganehha\DigikalaSellerWebhook\DigikalaService;
use Yeganehha\DigikalaSellerWebhook\Exceptions\ListOrdersShouldBeOrderNModelException;
use Yeganehha\DigikalaSellerWebhook\Exceptions\OrdersNotArrayException;
use Yeganehha\DigikalaSellerWebhook\Exceptions\UnauthorizedException;

class HelpersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        PHPUnitUtil::setToken(true);
        PHPUnitUtil::setCONTENT(false , true);
    }

    public function testDigikalaFunction(): void
    {
        $this->assertInstanceOf(DigikalaService::class,digikala());
    }

    /**
     * @throws UnauthorizedException|OrdersNotArrayException|ListOrdersShouldBeOrderNModelException
     */
    public function testDigikalaGetOrdersFunction(){
        $this->assertIsArray(digikala_order(null,null,false)[0]->variant->price);
    }

    /**
     * @throws UnauthorizedException|OrdersNotArrayException|ListOrdersShouldBeOrderNModelException
     */
    public function testDigikalaGetOrdersFunctionWithToken(){
        $this->assertIsArray(digikala_order(PHPUnitUtil::$token,null,false)[0]->variant->price);
    }

    /**
     * @throws UnauthorizedException|OrdersNotArrayException|ListOrdersShouldBeOrderNModelException
     */
    public function testDigikalaGetOrdersFunctionWithWrongToken(){
        $this->expectException(UnauthorizedException::class);
        digikala_order("Wrong Token");
    }
}
