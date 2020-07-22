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

        //after the adding post hook
        add_action('added_post_meta', array($this, 'cbs_add_product'), 10, 4);

        //after the update product hook
        add_action('updated_post_meta', array($this, 'cbs_update_product'), 10, 4);

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


        //killing the process if the targeted product is empty
        if (!$product) {
            return;
        }

        $newProduct = [
            "ref" => $product->get_title(),
            "label" => $product->get_title(),
            "price" => (float)$product->get_regular_price(),
            "price_base_type" => "HT",
            "tva_tx" => "20.000",
            "id" => $product->get_id(),
            "status_buy" => "1",
            "status" => "1",
            "description" => $product->get_description(),

        ];

        callAPI("POST", $this->api_key, $this->api_url . "products", json_encode($newProduct));


    }

//    /**
//     * @param $meta_id //meta id
//     * @param $meta_value // meta value
//     * @param $meta_key //meta key
//     * @param  $post_id // inserted product id
//     * @return  void*
//     */
//    public function cbs_update_product($meta_id, $post_id, $meta_key, $meta_value)
//    {
//        // get the targeted product
//        $product = $product = wc_get_product($post_id);
//        var_dump($product);
//        die();
//
//        if ($meta_key == '_edit_lock') { // we've been editing the post
//            //killing the process if the targeted product is empty
//            if (!$product) {
//                return;
//            }
//
//
//
//            callAPI("PUT", $this->api_key, $this->api_url . "products/" . $product_id, json_encode($newProduct));
//
//        }
//    }


}


/**
 * initialize the creawebsync class
 * @var  $creawebSync
 */
$creawebSync = new CreawebSync();




