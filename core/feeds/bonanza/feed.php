<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/********************************************************************
 * Version 2.0
 * A GoDataFeed Feed
 * Copyright 2014 Purple Turtle Productions. All rights reserved.
 * license    GNU General Public License version 3 or later; see GPLv3.txt
 * By: Keneto 2014-09
 ********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PBonanzaFeed extends PCSVFeedEx
{

    function __construct()
    {

        parent::__construct();

        $this->providerName = 'Bonanza';
        $this->providerNameL = 'bonanza';
        $this->fileformat = 'csv';
        $this->fields = array();
        $this->fieldDelimiter = ",";

        //Create some attributes (Mapping 3.0)
//Required

        $this->addAttributeMapping('title', 'title', true, true); //product name (15-70 chars)
        $this->addAttributeMapping('description', 'description', true, true);
        $this->addAttributeMapping('regular_price', 'price', true, true);
        $this->addAttributeMapping('local_category', 'category', true, true);
        $this->addAttributeMapping('link', 'URL', true, true); //begin with http://
        $this->addAttributeMapping('feature_imgurl', 'ImageURL', true, true); //begin with http://
//$this->addAttributeMapping('', 'Manufacturer', true,true);

        //$this->addAttributeMapping('brand', 'Brand', true,true);
//Suggested
        $this->addAttributeMapping('', 'sku', true);
        $this->addAttributeMapping('', 'id', true);
        $this->addAttributeMapping('', 'booth_category', true);
        $this->addAttributeMapping('', 'shipping_price', true);
        $this->addAttributeMapping('', 'shipping_type', true);
        $this->addAttributeMapping('', 'shipping_lbs', true);
        $this->addAttributeMapping('', 'shipping_oz', true);
        $this->addAttributeMapping('', 'shipping_carrier', true);
        $this->addAttributeMapping('', 'shipping_package', true);
        $this->addAttributeMapping('', 'worldwide_shipping_price', true);
        $this->addAttributeMapping('', 'worldwide_shipping_type', true);
        $this->addAttributeMapping('', 'worldwide_shipping_carrier', true);
        $this->addAttributeMapping('', 'quantity', true);
        $this->addAttributeMapping('', 'trait', true);
        $this->addAttributeMapping('', 'force_update', true);

        $this->addAttributeDefault('price', 'none', 'PSalePriceIfDefined');
        $this->addAttributeDefault('local_category', 'none', 'PCategoryTree'); //store's local category tree
        $this->addRule('price_rounding', 'pricerounding'); //2 decimals

    }

    function formatProduct($product)
    {
        //Prepare input:

        $product->attributes['feature_imgurl'] = str_replace('https://', 'http://', $product->attributes['feature_imgurl']);

        if ($product->attributes['stock_status'] == 1)
            $product->attributes['stock_status'] = 'In Stock';
        else
            $product->attributes['stock_status'] = 'Out Of Stock';

        //Allowed condition values: New, Open Box, OEM, Refurbished, Pre-Owned, Like New, Good, Very Good, Acceptable
        return parent::formatProduct($product);
    }


}
