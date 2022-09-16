<?php

namespace Yeganehha\DigikalaSellerWebhook;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
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

    /**
     * Return all Variants
     * @param array $search
     * @return array
     * @throws GuzzleException
     */
    public static function getVariants(array $search = []):array
    {
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
     * @param int $variantID
     * @param array $update
     * @return bool
     * @throws GuzzleException
     */
    public static function updateVariant(int $variantID , array $update) :bool
    {
        $allowSearchParameter = ['site','shipping_type','seller_stock','max_per_order','digikala_lead_time','ship_by_seller_lead_time','is_archived','is_active','price','gold_wage','non_gold_parts_cost','non_gold_parts_wage','gold_profit'];
        $update = array_intersect_key($update, array_flip($allowSearchParameter));
        $response = self::call('variants/'.$variantID.'/' , [] , $update , "put" );
        if(self::$callStatus and $response->status == "ok" ) {
            $logger = Logger::make('update_variant_id_'.$variantID);
            $logger->info('Update Variant ID: '.$variantID , $update);
            return true;
        }
        return false;
    }

    /**
     * @param string $supplier_code
     * @param array $update
     * @return bool
     * @throws GuzzleException
     */
    public static function updateAllVariantSupplierCode(string $supplier_code , array $update) :bool
    {
        $result = true ;
        $logger = Logger::make('update_all_variant_of_'.$supplier_code);
        $logger->info('Update Variant Code: '.$supplier_code);
        $variants = self::getVariants(['supplier_code' => $supplier_code]);
        foreach ($variants as $variant){
            $temp = self::updateVariant($variant->id , $update);
            $result = ($temp and $result) ;
            $logger->info('ID: '.$supplier_code , ['original' =>$variant , 'update' => $update]);
        }
        return $result;
    }

    /**
     * @param $URI
     * @param array $query
     * @param array $body
     * @param string $method
     * @return stdClass
     * @throws GuzzleException
     */
    private static function call($URI , array $query = [] , array $body = [], string $method = "GET"): stdClass
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