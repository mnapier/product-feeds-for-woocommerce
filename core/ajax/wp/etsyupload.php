<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!is_admin()) {
    die('Permission Denied!');
}
define('XMLRPC_REQUEST', true);

require_once dirname(__FILE__) . '/../../../../../../wp-config.php';

require_once dirname(__FILE__) . '/../../classes/etsyclient.php';
global $wpdb;

$etsy = new Etsy();

$config['max_upload'] = $etsy->getConfig('max_upload')->configuration_value;
$config['request_per_minute'] = $etsy->getConfig('request_per_minute')->configuration_value;

$table = $wpdb->prefix . "tmp_etsy_listing";
$sql = "SELECT id,shop_id,quantity,title,description,price,who_made,when_made,state,is_supply,shipping_template_id,category_id, listing_id, image FROM " . $table . " WHERE uploaded = 0  LIMIT 0," . $config['max_upload'];

$data = $wpdb->get_results($sql, ARRAY_A);

if (!empty($data)) {

    foreach ($data as $listing) {

        $image = $listing['image'];
        $shop_id = $listing['shop_id'];
        $id = $listing['id'];
        unset($listing['shop_id']);
        unset($listing['id']);
        unset($listing['image']);


        $response = $etsy->doUploading($listing);
        $res = json_decode($response);
        $listing_id = $res->results[0]->listing_id;

        if ($listing_id > 0) {
            $wpdb->update($table, ['listing_id' => $listing_id, 'uploaded' => 1], ['id' => $id]);
            $imgRequest = $etsy->uploadImage($listing_id, $image);

        }
        sleep($config['request_per_minute']);
    }
}
