<?php
/*
Plugin Name: creawebSync
Plugin URI:
Description: Dolibarr WooCommerce integration
Version: 1.0.2
Author: Hamza EL haissouf
Author URI: https://gpcsolutions.fr
License: GPL-3.0+
Text Domain: doliwoo
Domain Path: /languages
*/


/**
 * CreawebSync Integration for WooCommerce
 */
class CreawebSync
{
    /**
     * @var string //dolibarr api key
     */
    public $api_key;

    /**
     * @var string //dolibarr api url
     */
    public $api_url;

//    /**
//     * @var int // all Guest invoices will be attached to.
//     */
//    public $default_customer_id;

    /**
     * @var int // all Guest products will be attached to.
     */
    public $default_thirdParty_id;

    /**
     * @var float //default tx tva for guest products
     */
    public $default_tva_tx;

    /**
     * @var //default price base type
     */
    public $price_base_type;

    /**
     * creawebSync plugin
     */
    public function __construct()
    {

        require 'includes/helpers.php';

        //initialize the api_key , this can be customizable after
        $this->api_key = 'bM1o3lLvaui4k5oHOs2J657nF7PAD3GO';
        //initialize the api_key , this can be customizable after
        $this->api_url = 'http://192.168.100.30:8050/dolipar/htdocs/api/index.php/';

        //after the adding post hook
        add_action('added_post_meta', array($this, 'cbs_add_product'), 10, 4);

        //after the update product hook
        add_action('woocommerce_update_product', array($this, 'cbs_update_product'), 10, 4);

        //after the order validation hook
        add_action('woocommerce_thankyou', array($this, 'cbs_add_order'), 10, 1);

        //after the order update
        add_action('woocommerce_process_shop_order_meta' , array($this  , 'cbs_update_order'));



    }

    /**
     * insert the wordpress created product in dolibarr
     * @param $meta_id //meta id
     * @param $meta_value // meta value
     * @param $meta_key //meta key
     * @param  $post_id // inserted product id
     * @method
     */

    public function cbs_add_product($meta_id, $post_id, $meta_key, $meta_value)
    {
        // get the targeted product
        $product = $product = wc_get_product($post_id);
        if ($meta_key == '_regular_price') {


            //killing the process if the targeted product is empty
            if (!$product) {
                return;
            }

            $newProduct = [
                "ref" => $product->get_title(),
                "label" => $product->get_title(),
                "price_base_type" => "HT",
                "tva_tx" => "20.000",
                "status_buy" => "1",
                "status" => "1",
                "weight" => $product->get_weight(),
                "height" => $product->get_height(),
                'url' => $product->get_permalink(),
                'description' => $product->get_description(),
                'note_public' => $product->get_purchase_note(),
//                $product->get_image()
                "price" => (float)$product->get_regular_price(),

            ];

            $newProductResult = callAPI(
                "POST",
                $this->api_key,
                $this->api_url . "products",
                json_encode($newProduct
             ));

        }



    }

    /**
     * set the product modfication in dolibarr
     * @param  $post_id // inserted product id
     * @method
     */
    public function cbs_update_product($post_id)
    {
        // get the targeted product

        $product = $product = wc_get_product($post_id);
//        $image_id  = $product->get_image_id();
////        $image_url = wp_get_attachment_image_url( $image_id, 'full' );

        //killing the process if the targeted product is empty
        if (!$product) {
            return;
        }


        $updatedProduct = [
            "ref" => $product->get_title(),
            "label" => $product->get_title(),
            "price_base_type" => "HT",
            "tva_tx" => "20.000",
            "status_buy" => "1",
            "status" => "1",
            "weight" => $product->get_weight(),
            "height" => $product->get_height(),
            'url' => $product->get_permalink(),
            'description' => $product->get_description(),
            'note_public' => $product->get_purchase_note(),
//                $product->get_image()
            "price" => (float)$product->get_regular_price(),

        ];
        $newProductResult = callAPI(
            "PUT", $this->api_key,
            $this->api_url . "products/" .
            product_id_by_ref($product->get_title(),
                $this->api_key, $this->api_url),
            json_encode($updatedProduct
            ));
//

    }


    /**
     * insert a new order in dolibarr after it's been validated by the client
     * @param  $order_id // inserted product id
     * @method
     */

    public function cbs_add_order($order_id)
    {
        if (!$order_id)
            return;


        // Getting an instance of the order object

        $order = new WC_Order($order_id);

        $items = $order->get_items();


        //Getting the customer id after inseting it or get it from dolibarr
        $customer_id = $this->cbs_add_thirdParty(
            $customer = [
                'firstname' => $order->get_billing_first_name(),
                'lastname' => $order->get_billing_last_name(),
                'name' => $order->get_billing_first_name(),
                'email' => $order->get_billing_email(),
                'phone' => $order->get_billing_phone(),
                'adresse' => $order->get_billing_address_1(),
                "country" => $order->get_billing_country(),
                "city" => $order->get_billing_city(),
                "company" => $order->get_billing_company(),
                'client' => '1',
                'code_client' => '-1'
            ]
        );
        // The array where there will be all the products lines of my order.
        $newCommandeLine = [];


        //Loop through the order items, then get the products/lines data
        foreach ($items as $item) {

            $product = wc_get_product($item['product_id']);
            $newCommandeLine[] = [
                "desc" => $product->get_title(),
                "subprice" => $order->get_total(),
                "qty" => $item['quantity'],
                "tva_tx" => "20.000",
                "fk_product" => product_id_by_ref($product->get_title(), $this->api_key, $this->api_url)
            ];
        }

        if (count($newCommandeLine) > 0) {


            $newCommande = [
                "socid" => $customer_id,
                "type" => "0",
                "lines" => $newCommandeLine,
                "note_private" => "order created automatically with API",
                "date_commande"=>$order->get_date_created()->date_i18n(),
                ''
            ];

            $newCommandeResult = CallAPI(
                "POST",
                $this->api_key,
                $this->api_url . "orders",
                json_encode($newCommande
              ));
            $newCommandeResult = json_decode($newCommandeResult, true);


        }



    }

    /**
     * create a thirdparty  (customer) in dolibarr to add a new order to it
     * @return  int //customer id
     * @param array $thirdParty //tirdParty data
     */

    public function cbs_add_thirdParty($thirdParty)
    {
        //getting the thirdparty id from the method helper
        $thirdparty_id = thirdParty_id_by_email($thirdParty['email'], $this->api_key, $this->api_url);

        if ($thirdparty_id != 0) return $thirdparty_id;

        //insert the thirdparty in  dolibarr
        $newProductResult = callAPI("POST",
            $this->api_key,
            $this->api_url . "thirdparties",
            json_encode($thirdParty
         ));

        //get the inserted third party id
        $thirdparty_id = json_decode($newProductResult);
        //returning the result
        return $thirdparty_id;
    }

    /**
     *  set the order modfications in dolibarr
     * @param  $order_id // inserted product id
     * @method
     */
    public  function  cbs_update_order($order_id){
   //  TODO: 'update the order'
        die(var_dump($order_id));
    }
}


/**
 * initialize the creawebsync class
 * @var  $creawebSync
 */
$creawebSync = new CreawebSync();




