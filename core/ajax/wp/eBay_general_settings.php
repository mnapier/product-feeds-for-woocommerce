<?php
/**
 * Created by PhpStorm.
 * User: subash
 * Date: 5/9/16
 * Time: 4:55 PM
 */

define('XMLRPC_REQUEST', true);
if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!is_admin()) {
    die('Permission Denied!');
}
require_once dirname(__FILE__) . '/../../data/feedcore.php';
require_once dirname(__FILE__) . '/../../model/eBayAccount.php';

$default_account = CPF_eBayAccount::getDefaultAccount();
global $wpdb;
global $EC;

$table_accounts = $wpdb->prefix . 'ebay_accounts';
$table_currency = $wpdb->prefix . 'ebay_currency';
$site_info = $wpdb->get_row("SELECT acc.site_id,acc.site_code,cur.site_abbr ,cur.currency_code  FROM $table_accounts AS acc 
LEFT JOIN $table_currency as cur on acc.site_id = cur.site_id WHERE default_account = 1", ARRAY_A);
$table = $wpdb->prefix . 'ebay_shipping';
if (!$_POST['hiddenId']) {
    $wpdb->insert($table, array(
        'paypal_email' => $_POST['paypal_email'],
        'paypal_accept' => $_POST['ebayPaypalAccepted'],
        'shippingfee' => $_POST['flatShipping'],
        'ebayShippingType' => $_POST['ebayShippingType'],
        'dispatchTime' => $_POST['dispatchTime'],
        'default_account' => $default_account,
        'shipping_service' => $_POST['shippingService'],
        'listingDuration' => $_POST['listingDuration'],
        'listingType' => $_POST['listingType'],
        'refundOption' => $_POST['refundOption'],
        'refundDesc' => $_POST['refundDesc'],
        'returnwithin' => $_POST['returnwithin'],
        'postalcode' => $_POST['postalcode'],
        'additionalshippingservice' => $_POST['additionalshippingservice'],
        'conditionType' => $_POST['conditionType'],
        'quantity' => $_POST['quantity'],
        'site_id' => $site_info['site_id'],
        'site_code' => $site_info['site_code'],
        'currency_code' => $site_info['currency_code'],
        'site_abbr' => $site_info['site_abbr']
    ));
} else {
    $wpdb->update($table, array(
        'paypal_email' => $_POST['paypal_email'],
        'paypal_accept' => $_POST['ebayPaypalAccepted'],
        'shippingfee' => $_POST['flatShipping'],
        'ebayShippingType' => $_POST['ebayShippingType'],
        'dispatchTime' => $_POST['dispatchTime'],
        'default_account' => $default_account,
        'shipping_service' => $_POST['shippingService'],
        'listingDuration' => $_POST['listingDuration'],
        'listingType' => $_POST['listingType'],
        'refundOption' => $_POST['refundOption'],
        'refundDesc' => $_POST['refundDesc'],
        'returnwithin' => $_POST['returnwithin'],
        'postalcode' => $_POST['postalcode'],
        'additionalshippingservice' => $_POST['additionalshippingservice'],
        'conditionType' => $_POST['conditionType'],
        'quantity' => $_POST['quantity'],
        'site_id' => $site_info['site_id'],
        'site_code' => $site_info['site_code'],
        'currency_code' => $site_info['currency_code'],
        'site_abbr' => $site_info['site_abbr']
    ),
        array('ID' => $_POST['hiddenId'])
    );
}



