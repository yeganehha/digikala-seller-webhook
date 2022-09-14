<?php

namespace Yeganehha\DigikalaSellerWebhook\Tests;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
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

    private function listVariantMockHandler(){
        $mock = new MockHandler([
            new Response(200,[], PHPUnitUtil::$listVariants),
        ]);
        $handlerStack = HandlerStack::create($mock);
        APIHandler::$handler = $handlerStack;
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

    public function testGetVariantsWithWrongToken()
    {
        $this->expectException(ClientException::class);
        APIHandler::setToken("Wrong Token");
        APIHandler::getVariants();
    }

    public function testGetVariantsWithSearchOption()
    {
        $this->listVariantMockHandler();
        $variants = APIHandler::getVariants(['supplier_code'=>'LENOVO-XT90-BLACK']);
        $this->assertIsArray($variants);
    }

    public function testGetAllVariants()
    {
        $this->listVariantMockHandler();
        $variants = APIHandler::getVariants(['supplier_code'=>'LENOVO-XT90-BLACK']);
        $this->assertEquals(4,count($variants));
    }

    public function testSearchVariantsCorrect()
    {
        $this->listVariantMockHandler();
        $variants = APIHandler::getVariants(['supplier_code'=>'LENOVO-XT90-BLACK']);
        foreach ($variants as $variant)
            $this->assertEquals('LENOVO-XT90-BLACK' ,$variant->supplier_code );
    }

}
