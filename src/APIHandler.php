<?php

namespace Yeganehha\DigikalaSellerWebhook;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use phpDocumentor\Reflection\Types\Mixed_;
use stdClass;

class APIHandler
{
    public static $token ;
    public static $baseUri = 'https://seller.digikala.com/api/v1/';
    private static $callStatus;
    public static $handler = null;

    /**
     * @param mixed $token
     * @return APIHandler
     */
    public static function setToken($token): APIHandler
    {
        self::$token = $token;
        return new static();
    }

    /**
     * @param string $baseUri
     * @return APIHandler
     */
    public static function setBaseUri(string $baseUri): APIHandler
    {
        self::$baseUri = $baseUri;
        return new static();
    }

    public static function  getVariants($search = []){
        $allowSearchParameter = ['id','product_id','category_ids','brand_ids','has_warehouse_stock','shipping_type','is_active','is_archived','is_buy_box_winner','is_in_promotion','is_in_competition','supplier_code','active_b2b'];
        $search['search'] = array_intersect_key($search, array_flip($allowSearchParameter));
        $pageNumber = 1 ;
        $allVariants = [];
        while ( true ){
            $search['page'] = $pageNumber;
            $variants = self::call('variants/' , $search );

            if(self::$callStatus and $variants->status == "ok" ) {
                if ( is_array($variants->data->items) )
                    foreach ($variants->data->items as $item){
                        $allVariants[] = $item;
                    }
                if ($variants->data->pager->total_page == $pageNumber)
                    break;
                $pageNumber++;
            }
        }
        return $allVariants;
    }

    /**
     * @param $URI
     * @param array $query
     * @param array $body
     * @param string $method
     * @return stdClass
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private static function call($URI , $query = [] , $body = [], $method = "GET"): stdClass
    {
        self::$callStatus = false;
        $config['base_uri']  = self::$baseUri;
        if ( self::$handler != null )
            $config['handler']  = self::$handler;
        $client = new Client($config);
        $headers = ['authorization' => self::$token];
        $options['Accept'] = 'application/json';
        $options['headers'] = $headers;
//        $options['http_errors'] = false;
        if ( ! in_array(strtolower($method) ,["get" , "head", "delete"]) )
            $options['form_params'] = $body;
        $options['query'] = $query;
        $request = $client->request($method,$URI,$options);
        $body = $request->getBody()->getContents();
        self::$callStatus = $request->getStatusCode() == 200 ;
        return json_decode($body);
    }

}