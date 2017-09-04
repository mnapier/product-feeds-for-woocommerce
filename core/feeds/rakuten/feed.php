<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/********************************************************************
 * Version 3.0
 * A Rakuten Feed
 * Copyright 2014 Purple Turtle Productions. All rights reserved.
 * license    GNU General Public License version 3 or later; see GPLv3.txt
 * By: Keneto 2014-07-24
 * 2014-09 Moved to 100% Mapping v3 compliance
 ********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PRakutenFeed extends PCSVFeedEx
{

    function __construct()
    {
        parent::__construct();
        $this->providerName = 'Rakuten';
        $this->providerNameL = 'rakuten';
        $this->fileformat = 'txt';
        $this->fieldDelimiter = "\t";
        $this->fields = array();
        //$this->fields = array('Seller-id', 'gtin', 'mfg-name', 'mfg-part-number', 'Seller-sku', 'title', 'description', 'main-image', 'additional-images', 'weight', 'category-id', 'product-set-id', 'listing-price');

        $this->addAttributeMapping('ListingId', 'ListingId'); //assigned by Rakuten.com Shopping
        $this->addAttributeMapping('', 'ProductId', true, true);
        $this->addAttributeMapping('ProductIdType', 'ProductIdType', true, true); //0,1,2,3
        $this->addAttributeMapping('condition', 'ItemCondition', true, true);
        $this->addAttributeMapping('price', 'Price', true, true); //Seller-sku must be unique in the feed
        $this->addAttributeMapping('', 'MAP');
        $this->addAttributeMapping('', 'MAPType');
        $this->addAttributeMapping('stock_quantity', 'Quantity', true, true);
        $this->addAttributeMapping('OfferExpeditedShipping', 'OfferExpeditedShipping', true, true);
        $this->addAttributeMapping('description_short', 'Description', true); //describes the CONDITION of the item
        $this->addAttributeMapping('', 'ShippingRateStandard');
        $this->addAttributeMapping('', 'ShippingRateExpedited');
        $this->addAttributeMapping('', 'ShippingLeadTime');
        $this->addAttributeMapping('', 'OfferTwoDayShipping');
        $this->addAttributeMapping('', 'ShippingRateTwoDay');
        $this->addAttributeMapping('', 'OfferOneDayShipping');
        $this->addAttributeMapping('', 'ShippingRateOneDay');
        $this->addAttributeMapping('', 'OfferLocalDeliveryShippingRates');
        $this->addAttributeMapping('sku', 'ReferenceId', true, true); //unique product id assigned to the product by you

        $this->addAttributeDefault('price', 'none', 'PSalePriceIfDefined');
        $this->addRule('price_rounding', 'pricerounding'); //2 decimals
        $this->addAttributeDefault('local_category', 'none', 'PCategoryTree'); //store's local category tree
        //escape any quotes
        //$this->addRule( 'csv_standard', 'CSVStandard',array('description_short','250') );
    }

    function getFeedHeader($file_name, $file_path)
    {
        //Rakuten header line 1
        $output = implode(
                $this->fieldDelimiter,
                array('##Type=Inventory;Version=5.0')
            ) . "\r\n";

        //Rakuten header line 2
        foreach ($this->attributeMappings as $thisMapping) {
            if ($thisMapping->enabled && !$thisMapping->deleted) {
                $output .= $thisMapping->mapTo . $this->fieldDelimiter;
            }
        }
        return substr($output, 0, -1) . "\r\n";
    }

    function getFeedFooter($file_name, $file_path)
    {
        //Override parent and do nothing
    }

    function formatProduct($product)
    {

        //cheat: Remap these
        if ($product->attributes['condition'] == 'New')
            $product->attributes['condition'] = '1';

        //$product->attributes['OfferExpeditedShipping'] = 1;
        //result code notifications
        $error_count = 0;
        //loop through attributes
        foreach ($this->attributeMappings as $thisAttributeMapping) {
            //look for required Rakuten Attributes that may need user config
            if ($thisAttributeMapping->isRequired &&
                ($thisAttributeMapping->mapTo == 'ProductId' ||
                    $thisAttributeMapping->mapTo == 'ProductIdType' ||
                    $thisAttributeMapping->mapTo == 'OfferExpeditedShipping')
            ) {
                if (!isset($product->attributes[$thisAttributeMapping->attributeName]) || strlen($product->attributes[$thisAttributeMapping->attributeName]) == 0) {
                    if ($thisAttributeMapping->mapTo == 'ProductId') {
                        $this->addErrorMessage(14000, 'Missing required: ' . $thisAttributeMapping->mapTo,false,$product->attributes['title']);
                        $error_count++;
                    } elseif ($thisAttributeMapping->mapTo == 'ProductIdType') {
                        $this->addErrorMessage(14001, 'Missing required: ' . $thisAttributeMapping->mapTo,false,$product->attributes['title']);
                        $error_count++;
                    } elseif ($thisAttributeMapping->mapTo == 'OfferExpeditedShipping') {
                        $this->addErrorMessage(14002, 'Missing required: ' . $thisAttributeMapping->mapTo,false,$product->attributes['title']);
                        $error_count++;
                    } else
                        continue;
                }
            }
        }
        if ($error_count > 0) $this->productCount--;
        return parent::formatProduct($product);
    }

}
