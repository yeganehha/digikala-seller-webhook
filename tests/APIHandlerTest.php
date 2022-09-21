<?php

namespace Yeganehha\DigikalaSellerWebhook\Tests;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Yeganehha\DigikalaSellerWebhook\APIHandler;
use PHPUnit\Framework\TestCase;

class APIHandlerTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        PHPUnitUtil::setToken(true);
        PHPUnitUtil::setCONTENT(false , true);
        APIHandler::setToken(PHPUnitUtil::$APIToken);
    }

    public function testSetToken()
    {
        $this->assertEquals(PHPUnitUtil::$APIToken , APIHandler::$token);
    }

    public function testSetBaseUri()
    {
        $baseURI = APIHandler::$baseUri;
        APIHandler::setBaseUri("https://google.com");
        $this->assertEquals("https://google.com" , APIHandler::$baseUri);
        APIHandler::setBaseUri($baseURI);
    }

    /**
     * @throws GuzzleException
     */
    public function testGetVariantsWithWrongToken()
    {
        $this->expectException(ClientException::class);
        APIHandler::setToken("Wrong Token");
        APIHandler::getVariants();
    }

    /**
     * @throws GuzzleException
     */
    public function testGetVariantsWithSearchOption()
    {
        PHPUnitUtil::listVariantMockHandler();
        $variants = APIHandler::getVariants(['supplier_code'=>'LENOVO-XT90-BLACK']);
        $this->assertIsArray($variants);
    }

    /**
     * @throws GuzzleException
     */
    public function testGetAllVariants()
    {
        PHPUnitUtil::listVariantMockHandler();
        $variants = APIHandler::getVariants(['supplier_code'=>'LENOVO-XT90-BLACK']);
        $this->assertCount(4, $variants);
    }

    /**
     * @throws GuzzleException
     */
    public function testSearchVariantsCorrect()
    {
        PHPUnitUtil::listVariantMockHandler();
        $variants = APIHandler::getVariants(['supplier_code'=>'LENOVO-XT90-BLACK']);
        foreach ($variants as $variant)
            $this->assertEquals('LENOVO-XT90-BLACK' ,$variant->supplier_code );
    }

    /**
     * @throws GuzzleException
     */
    public function testUpdateVariant()
    {
        PHPUnitUtil::updateVariantMockHandler("ok");
        $result = APIHandler::updateVariant(33088959 , ['supplier_code'=>'LENOVO-LP3 PRO-BLACK']);
        $this->assertTrue($result);
    }

    /**
     * @throws GuzzleException
     */
    public function testUpdateUnknownVariant()
    {
        $this->expectException(ClientException::class);
        PHPUnitUtil::updateVariantMockHandler(404);
        APIHandler::updateVariant(1 , ['supplier_code'=>'LENOVO-LP3 PRO-BLACK']);
    }

    /**
     * @throws GuzzleException
     */
    public function testUpdateAllVariantBySupplierCode()
    {
        PHPUnitUtil::updateAllVariantMockHandler();
        $result = APIHandler::updateAllVariantSupplierCode('LENOVO-XT90-BLACK' , ['supplier_code'=>'LENOVO-LP3 PRO-BLACK']);
        $this->assertTrue($result);
    }

}
