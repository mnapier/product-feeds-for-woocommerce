<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/********************************************************************
 * Version 2.1
 * A Google Feed
 * Copyright 2014 Purple Turtle Productions. All rights reserved.
 * license    GNU General Public License version 3 or later; see GPLv3.txt
 * By: Keneto 2014-05-08
 * 2014-09 Retired Attribute Mapping v2.0 (Keneto)
 * 2014-11 All required & optional parameters now show
 ********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PSlickgunsFeed extends PXMLFeed
{
    function __construct()
    {
        parent::__construct();
        $this->providerName = 'Slickguns';
        $this->providerNameL = 'slickguns';

        //Create some attributes (Mapping 3.0) in the form (title, Google-title, CData, isRequired)
        //Note that isRequired is just to direct the plugin on where on the dialog to display

        //Basic product information
        $this->addAttributeMapping('title', 'title', true, true);
        $this->addAttributeMapping('id', 'id', false, true);
        $this->addAttributeMapping('price', 'price', false, true);

        $this->addAttributeMapping('product_category', 'product_category', true, false);
        $this->addAttributeMapping('feature_imgurl', 'image_link', false, false);
        $this->addAttributeMapping('link', 'link', true, true);

        $this->addAttributeMapping('stock_status', 'availability', false, true);
        $this->addAttributeMapping('sku', 'mpn', true, true);
        $this->addAttributeMapping('', 'upc', false, true);
        $this->addAttributeMapping('brand', 'brand', true, false);

        $this->addAttributeMapping('', 'shipping_price', false, false);
        $this->addAttributeMapping('weight', 'shipping_weight', false, false);
        $this->addAttributeMapping('is_firearm', 'is_firearm', false, false);

        $this->google_exact_title = false;
        $this->google_combo_title = false;
        $this->productLevelElement = 'item';

        $this->addAttributeDefault('price', 'none', 'PSalePriceIfDefined');
        $this->addAttributeDefault('local_category', 'none', 'PCategoryTree'); //store's local category tree

        $this->addRule('weight_unit', 'weightunit'); //apend weight unit to 'weight' attribute
        //$this->addRule('price_standard', 'pricestandard'); //append currency
        $this->addRule('status_standard', 'statusstandard'); //'in stock' or 'out of stock'
        $this->addRule('price_rounding', 'pricerounding'); //2 decimals

        $this->addRule('google_exact_title', 'googleexacttitle'); //true disables ucowrds
        $this->addRule('google_combo_title', 'googlecombotitle');

    }

    function formatProduct($product)
    {
        global $pfcore;
        //********************************************************************
        //Prepare the Product Attributes
        //********************************************************************

        return parent::formatProduct($product);

    }

    function getFeedFooter($file_name, $file_path)
    {
        $output = '
  </channel>';
        return $output;
    }

    function getFeedHeader($file_name, $file_path)
    {
        $output = '<?xml version="1.0" encoding="UTF-8" ?>
  <channel>
    <title>' . $file_name . '</title>
    <link><![CDATA[' . $file_path . ']]></link>
    <description>' . $file_name . '</description>';
        return $output;
    }

}