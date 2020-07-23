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
//        add_action('woocommerce_update_product', array($this, 'test' ));

        //after the adding post hook
        add_action('added_post_meta', array($this, 'cbs_add_product'), 10, 4);

        //after the update product hook
        add_action('woocommerce_update_product', array($this, 'cbs_update_product'), 10, 4);

        //after the update product hook
        add_action('woocommerce_thankyou', array($this, 'test'), 10, 1);


        //after
    }

    /**
     * @param $meta_id //meta id
     * @param $meta_value // meta value
     * @param $meta_key //meta key
     * @param  $post_id // inserted product id
     * @return  void
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

            $newProductResult = callAPI("POST", $this->api_key, $this->api_url . "products", json_encode($newProduct));

        }

    }

    /**
     * @param  $post_id // inserted product id
     * @return  void
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

        //get the targeted product by its own ref
        $targetedProduct = callAPI("GET", $this->api_key, $this->api_url . "products/ref/" . $product->get_title());

        if (!$targetedProduct) {
            return;
        }

        //convert the api result from json to an object
        $targetedProduct = json_decode($targetedProduct);


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
        $newProductResult = callAPI("PUT", $this->api_key, $this->api_url . "products/" . $targetedProduct->id, json_encode($updatedProduct));
//

    }


//    /**
//     *
//     */

    public function test($order_id)
    {
        if (!$order_id)
            return;


        // Getting an instance of the order object

        $order = new WC_Order( $order_id );
        $items = $order->get_items();

        //Loop through them, you can get all the relevant data:

        foreach ( $items as $item ) {
            die(var_dump($item['product_id']));
            $product_name = $item['name'];
            $product_id = $item['product_id'];
            $product_variation_id = $item['variation_id'];
        }

    }


}


/**
 * initialize the creawebsync class
 * @var  $creawebSync
 */
$creawebSync = new CreawebSync();




