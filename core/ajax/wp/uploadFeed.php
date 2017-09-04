<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!is_admin()) {
    die('Permission Denied!');
}
/**
 * Created by PhpStorm.
 * User: subash
 * Date: 6/6/16
 * Time: 11:16 AM
 */
//require_once dirname(__FILE__) . '/../../classes/CPF_Page.php';

class uploadFeed
{
    public $token;
    public $filePath;
    public $fileName;
    public $sellerConfig;
    public $head = array();
    public $listing = array();

    function __construct()
    {
        $this->filePath = (content_url() . '/uploads/cart_product_feeds/eBaySeller/');
    }


    public function uploadToEbay($feed_id)
    {
        global $EC;
        $flag = true;
        /// $this->publish2e();
        //$this->additem();
        $this->getFileName($feed_id);
        $this->getListing();
        $this->sellerConfig = $this->getSellerAdditionalConfig();
        $this->getTokenForDefaultAccount();
        $EC->initEC();
        $EC->postListing($this->listing, $this->sellerConfig, $this->token);
        $EC->closeEbay();
    }

    public function getFileName($id)
    {
        global $wpdb;
        $tableName = $wpdb->prefix . 'cp_feeds';
        $this->fileName = $wpdb->get_var("SELECT url FROM {$tableName} WHERE id = {$id} ");
    }

    public function getTokenForDefaultAccount()
    {
        global $wpdb;
        $tableName = $wpdb->prefix . 'ebay_accounts';
        $this->token = $wpdb->get_var("SELECT token FROM  {$tableName} WHERE default_account = 1 ");
    }

    private function getListing()
    {
        $fileName = $this->fileName;
        $csv = array_map('str_getcsv', file($fileName));
        $csv = array_merge($csv);
        $this->formatCSV($csv);
    }

    private function formatCSV($csv)
    {
        $headers = [];
        $data = array();
        foreach ($csv[0] as $key => $value) {
            $headers[] = $value;
        }
        unset($csv[0]);
        $data = [];
        $listing = [];
        foreach ($csv as $key => $rec) {
            foreach ($rec as $key => $value) {
                $newkey = $headers[$key];
                $data[$newkey] = $rec[$key];
            }
            $listing[] = $data;
        }
        $this->listing = $listing;
    }

    private function getSellerAdditionalConfig()
    {
        global $wpdb;
        $tableName = $wpdb->prefix . 'ebay_shipping';
        $sellerConfig = $wpdb->get_row("SELECT paypal_email,paypal_accept,shippingfee, site_id ,dispatchTime,ebayShippingType, default_account,shipping_service, listingDuration,listingType,refundOption,refundDesc,returnwithin,postalcode,additionalshippingservice,conditionType,quantity,site_code,currency_code,site_abbr FROM {$tableName}", ARRAY_A);
        return $sellerConfig;
    }
}