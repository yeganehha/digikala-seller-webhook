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

    private function listVariantMockHandler(){
        $mock = new MockHandler([
            new Response(200,[], PHPUnitUtil::$listVariants),
        ]);
        $handlerStack = HandlerStack::create($mock);
        APIHandler::$handler = $handlerStack;
    }
    private function updateAllVariantMockHandler(){
        $data = json_decode(PHPUnitUtil::$listVariants);
        $responses = [new Response(200,[], PHPUnitUtil::$listVariants)];
        foreach ($data->data->items as $item){
            $item->supplier_code = 'LENOVO-LP3 PRO-BLACK';
            $responses[] = new Response(200,[], json_encode([
                'status' => 'ok',
                'data' => $item
            ]));
        }
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        APIHandler::$handler = $handlerStack;
    }

    private function updateVariantMockHandler($status){
        $data = json_decode(PHPUnitUtil::$updateVariants);
        $data->status = $status;
        $mock = new MockHandler([
            new Response($status == "ok" ? 200 : $status,[], json_encode($data)),
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
        $this->listVariantMockHandler();
        $variants = APIHandler::getVariants(['supplier_code'=>'LENOVO-XT90-BLACK']);
        $this->assertIsArray($variants);
    }

    /**
     * @throws GuzzleException
     */
    public function testGetAllVariants()
    {
        $this->listVariantMockHandler();
        $variants = APIHandler::getVariants(['supplier_code'=>'LENOVO-XT90-BLACK']);
        $this->assertCount(4, $variants);
    }

    /**
     * @throws GuzzleException
     */
    public function testSearchVariantsCorrect()
    {
        $this->listVariantMockHandler();
        $variants = APIHandler::getVariants(['supplier_code'=>'LENOVO-XT90-BLACK']);
        foreach ($variants as $variant)
            $this->assertEquals('LENOVO-XT90-BLACK' ,$variant->supplier_code );
    }

    /**
     * @throws GuzzleException
     */
    public function testUpdateVariant()
    {
        $this->updateVariantMockHandler("ok");
        $result = APIHandler::updateVariant(33088959 , ['supplier_code'=>'LENOVO-LP3 PRO-BLACK']);
        $this->assertTrue($result);
    }

    /**
     * @throws GuzzleException
     */
    public function testUpdateUnknownVariant()
    {
        $this->expectException(ClientException::class);
        $this->updateVariantMockHandler(404);
        APIHandler::updateVariant(1 , ['supplier_code'=>'LENOVO-LP3 PRO-BLACK']);
    }

    /**
     * @throws GuzzleException
     */
    public function testUpdateAllVariantBySupplierCode()
    {
        $this->updateAllVariantMockHandler();
        $result = APIHandler::updateAllVariantSupplierCode('LENOVO-XT90-BLACK' , ['supplier_code'=>'LENOVO-LP3 PRO-BLACK']);
        $this->assertTrue($result);
    }

}
