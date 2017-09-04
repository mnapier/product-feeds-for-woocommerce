<?php

/********************************************************************
 * Version 2.0
 * Get a feed
 * Copyright 2014 Purple Turtle Productions. All rights reserved.
 * license    GNU General Public License version 3 or later; see GPLv3.txt
 * By: Keneto 2014-07-01
 ********************************************************************/
if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!is_admin()) {
    die('Permission Denied!');
}
define('XMLRPC_REQUEST', true);
//ob_start(null, 0, PHP_OUTPUT_HANDLER_FLUSHABLE | PHP_OUTPUT_HANDLER_CLEANABLE);
ob_start(null);

function safeGetPostData($index)
{
    if (isset($_POST[$index]))
        return $_POST[$index];
    else
        return '';
}

function doOutput($output)
{
    ob_clean();
    echo json_encode($output);
}

require_once dirname(__FILE__) . '/../../../cart-product-wpincludes.php';

do_action('load_cpf_modifiers');
global $pfcore;
$pfcore->trigger('cpf_init_feeds');

add_action('get_feed_main_hook', 'get_feed_main');
do_action('get_feed_main_hook');

function cpf_upload_links($provider){

    $upload_links              = array(
            'google'                => 'http://www.exportfeed.com/documentation/google-merchant-shopping-product-upload/',
            'amazonsc'              => 'http://www.exportfeed.com/documentation/amazon-feed-installation-feed-creation-manual/',
            'ebayseller'            => 'http://www.exportfeed.com/documentation/ebay-seller-guide-2/',
            'facebookxml'           => 'http://www.exportfeed.com/documentation/facebook-dynamic-product-ads/',
            'bing'                  => 'http://www.exportfeed.com/documentation/bing-product-ads-guide/',
            'miinto'                => 'http://www.exportfeed.com/documentation/miinto-guide/',
            'pricerunner'           => 'http://www.exportfeed.com/documentation/price-runner-guide/',
            'bonanza'               => 'http://www.exportfeed.com/documentation/bonanza/',
            'become'                => 'http://www.exportfeed.com/documentation/become-integration-guide/',
            'ebaycommercenetwork'   => 'http://www.exportfeed.com/documentation/ebay-commerce-network-integration-guide/',
            'houzz'                 => 'http://www.exportfeed.com/documentation/houzz-export-guide/',
            'newegg'                => 'http://www.exportfeed.com/documentation/newegg-integration-guide/',
            'nextag'                => 'http://www.exportfeed.com/documentation/nextag-integration-guide/',
            'pronto'                => 'http://www.exportfeed.com/documentation/pronto-integration-guide/',
            'rakuten'               => 'http://www.exportfeed.com/documentation/rakuten/',
            'kelkoo'                => 'http://www.exportfeed.com/documentation/kelkoo-guide/',
            'shopping.com'          => 'http://www.exportfeed.com/documentation/shopping-com-integration-guide/',
            'pricegrabber'          => 'http://www.exportfeed.com/documentation/pricegrabber-com-integration-guide/',
            'shopzilla'             => 'http://www.exportfeed.com/documentation/shopzilla-guide/'
            );
    return isset($upload_links[$provider]) ? $upload_links[$provider] : ""; 
}

function get_feed_main()
{
    $requestCode = safeGetPostData('provider');
    $local_category = safeGetPostData('local_category');
    $remote_category = safeGetPostData('remote_category');
    $file_name = safeGetPostData('file_name');
    $feedIdentifier = safeGetPostData('feed_identifier');
    $saved_feed_id = safeGetPostData('feed_id');
    $miinto_country_code = safeGetPostData('country_code'); //For Miinto save country code for further use in edit feed section
    if ($miinto_country_code == '')
        $miinto_country_code = NULL;
    $feed_list = safeGetPostData('feed_ids'); //For Aggregate Feed Provider

    $output = new stdClass();
    $output->url = '';

    if (strlen($requestCode) * strlen($local_category) == 0) {
        $output->errors = 'Error: error in AJAX request. Insufficient data or categories supplied.';
        doOutput($output);
        return;
    }

    if (strlen($remote_category) == 0) {
        $output->errors = 'Error: Insufficient data. Please fill in "' . $requestCode . ' category"';
        doOutput($output);
        return;
    }

    // Check if form was posted and select task accordingly
    $dir = PFeedFolder::uploadRoot();
    if (!is_writable($dir)) {
        $output->errors = "Error: $dir should be writeable";
        doOutput($output);
        return;
    }
    $dir = PFeedFolder::uploadFolder();
    if (!is_dir($dir)) {
        mkdir($dir);
    }
    if (!is_writable($dir)) {
        $output->errors = "Error: $dir should be writeable";
        doOutput($output);
        return;
    }

    $providerFile = 'feeds/' . strtolower($requestCode) . '/feed.php';

    if (!file_exists(dirname(__FILE__) . '/../../' . $providerFile))
        if (!class_exists('P' . $requestCode . 'Feed')) {
            $output->errors = 'Error: Provider file not found.';
            doOutput($output);
            return;
        }

    $providerFileFull = dirname(__FILE__) . '/../../' . $providerFile;
    if (file_exists($providerFileFull))
        require_once $providerFileFull;

    //Load form data
    $file_name = sanitize_title_with_dashes($file_name);
    if ($file_name == '')
        $file_name = 'feed' . rand(10, 1000);

    $saved_feed = null;
    if ((strlen($saved_feed_id) > 0) && ($saved_feed_id > -1)) {
        require_once dirname(__FILE__) . '/../../data/savedfeed.php';
        $saved_feed = new PSavedFeed($saved_feed_id);
    }

    $providerClass = 'P' . $requestCode . 'Feed';
    $x = new $providerClass;
    $x->feed_list = $feed_list; //For Aggregate Provider only
    if (strlen($feedIdentifier) > 0)
        $x->activityLogger = new PFeedActivityLog($feedIdentifier);
    $x->getFeedData($local_category, $remote_category, $file_name, $saved_feed, $miinto_country_code);

    if ($x->success){
        $upload_path = cpf_upload_links(strtolower($requestCode));
        $output->upload_path = $upload_path;
        $output->url = PFeedFolder::uploadURL() . $x->providerName . '/' . $file_name . '.' . $x->fileformat;
    }

    $output->feed_is_deleted = false;
    /* // may be required for future development .. it will delete the feed if the products inside it contains no specific values like mpn, sku gtin etc.
    if (strlen($x->getErrorMessages()) > 0) {
        cpf_delete_the_feed_with_url($x->file_path);
        $output->feed_is_deleted = true;
    }*/

    $output->errors = $x->getErrorMessages();

    doOutput($output);
}
function cpf_delete_the_feed_with_url($url){
    global $wpdb;
    $wpdb->delete($wpdb->prefix."cp_feeds",['url'=>$url]);
    @unlink($url);

}
?>