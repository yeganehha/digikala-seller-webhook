<?php


namespace Yeganehha\DigikalaSellerWebhook\Model;


class Variant
{
    public $id ;
    public $seller_id ;
    public $site ;
    public $is_active ;
    public $is_archived ;
    public $title ;
    public $shipping_type ;
    public $supplier_code ;
    public $dk_lead_time ;
    public $sbs_lead_time ;
    public $stock ;
    public $price ;
    public $product ;

    public static function get() : Variant
    {
        return new Variant();
    }
    public function setFromArray(array $arrayItem) : Variant
    {
        foreach ($arrayItem as $key => $value)
            if( property_exists($this, $key) )
                $this->$key = $value;
        return $this;
    }
}