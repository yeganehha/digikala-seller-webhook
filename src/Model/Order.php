<?php


namespace Yeganehha\DigikalaSellerWebhook\Model;


class Order
{
    public $order_item_id;
    public $order_id;
    public $quantity;
    public $shipping_type;
    public $order_status;
    public $selling_price;
    public $created_at;
    public $cart_closed_at;
    public $shipment_status;
    public $commitment_date;
    /**
     * @var Variant $variant
     */
    public $variant;

    public static function get() : Order
    {
        return new Order();
    }
    public function setFromArray(array $arrayItem) : Order
    {
        if ( isset($arrayItem['variant'])) {
            $this->variant = Variant::get()->setFromArray($arrayItem['variant']) ;

            unset($arrayItem['variant']);
        }
        foreach ($arrayItem as $key => $value)
            if( property_exists($this, $key) )
                $this->$key = $value;
        return $this;
    }
}