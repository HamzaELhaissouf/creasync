<?php


// Copyright (C) 2020-2021 Hamza Elhaissouf <>


/**
 * CreawebSync plugin.
 *
 * Dolibarr WooCommerce integration.
 *
 * @package CreawebSync
 */


/**
 * calling the dolibarr apis
 * using the curl library this method interact with the dolibarr apis for the crud operations
 * @param string $method //request method type
 * @param string $api_key //dolibarr api key
 * @param string $api_url //dolibarr api url
 * @param bool $data //request method data
 * @return bool
 */
function callAPI($method, $api_key, $api_url, $data = false)
{
    $curl = curl_init();
    /**
     * @var  $httpheader
     */
    $httpheader = ['DOLAPIKEY: ' . $api_key];

    switch ($method) {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);
            $httpheader[] = "Content-Type:application/json";

            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

            break;
        case "PUT":

            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
            $httpheader[] = "Content-Type:application/json";

            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

            break;
        case "GET":
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');

            break;
        default:
            if ($data)
                $api_url = sprintf("%s?%s", $api_url, http_build_query($data));
    }

    // Optional Authentication:
    //    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    //    curl_setopt($curl, CURLOPT_USERPWD, "username:password");

    curl_setopt($curl, CURLOPT_URL, $api_url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $httpheader);

    $result = curl_exec($curl);

    curl_close($curl);

    return $result;
}
/**
 * get the product id by its ref
 *  this method just check if a product already exit if so it returns its id if not it returns 0
 * @param string $ref //product_ref to get
 * @param string $api_key //dolibarr api key
 * @param string $api_url //dolibarr api url
 * @return int // product id
 */
function product_id_by_ref($ref , $api_key, $api_url){

    $product = $targetedProduct = callAPI("GET"
        , $api_key,
        $api_url . "products/ref/" . $ref);

    $product = json_decode($product);

    return $product->id ?? 0;
}

/**
 * get the thirdparty id by  email
 * this method check if a customer already exit & returns id || 0
 * @param string $email //product_ref to get
 * @param string $api_key //dolibarr api key
 * @param string $api_url //dolibarr api url
 * @return int // thirdparty id
 */
function thirdParty_id_by_email($email , $api_key, $api_url){

    $thirdParty = $targetedProduct = callAPI("GET"
        , $api_key,
        $api_url . "thirdparties/byEmail/" . $email);

    $thirdParty = json_decode($thirdParty);

    return $thirdParty->id  ?? 0;

}
