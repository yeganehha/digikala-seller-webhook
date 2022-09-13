<?php

namespace Yeganehha\DigikalaSellerWebhook\Tests;

use PHPUnit\Framework\TestCase;
use Yeganehha\DigikalaSellerWebhook\DigikalaService;
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
     * @throws UnauthorizedException
     */
    public function testDigikalaGetOrdersFunction(){
        $this->assertIsArray(digikala_order()[0]->variant->price);
    }

    /**
     * @throws UnauthorizedException
     */
    public function testDigikalaGetOrdersFunctionWithToken(){
        $this->assertIsArray(digikala_order(PHPUnitUtil::$token)[0]->variant->price);
    }

    /**
     * @throws UnauthorizedException
     */
    public function testDigikalaGetOrdersFunctionWithWrongToken(){
        $this->expectException(UnauthorizedException::class);
        digikala_order("Wrong Token");
    }
}
