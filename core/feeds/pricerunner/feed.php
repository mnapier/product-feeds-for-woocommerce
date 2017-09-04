<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/********************************************************************
 * Version 3.0
 * A Pronto Feed
 * Copyright 2014 Purple Turtle Productions. All rights reserved.
 * license    GNU General Public License version 3 or later; see GPLv3.txt
 * By: Keneto 2014-05-08
 * 2014-09 Moved to Attribute mapping v3
 ********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PPriceRunnerFeed extends PXMLFeed
{

    function __construct()
    {

        parent::__construct();
        $this->providerName = 'PriceRunner';
        $this->providerNameL = 'PriceRunner';
        $this->productLevelElement = 'product';
        $this->topLevelElement = 'products';
        $this->fileformat = 'xml'; //format/extension
        //$this->fieldDelimiter = ","; //comma delimiter
        $this->fields = array();

//Required fields (4)
        $this->addAttributeMapping('id', 'SKU', true, true);
        $this->addAttributeMapping('price', 'PRICE', true, true);
        $this->addAttributeMapping('brand', 'MANUFACTURER', true, true);
        $this->addAttributeMapping('title', 'PRODUCT_NAME', true, true); //product URL
//Highly Recommended

        $this->addAttributeMapping('localCategory', 'CATEGORY', true, false);
        $this->addAttributeMapping('link', 'PRODUCT_URL', true, false);
        $this->addAttributeMapping('feature_imgurl', 'GRAPHIC_URL', true, false);
        $this->addAttributeMapping('sku', 'MANUFACTURER_SKU', true, false);

//Optional	

        $this->addAttributeMapping('', 'DELIVERY_COST', true, false);
        $this->addAttributeMapping('stock_status', 'AVAILABILITY', true, false);
        $this->addAttributeMapping('', 'DELIVERY_TIME', true, false);
        $this->addAttributeMapping('description', 'DESCRIPTION', true, false);
        $this->addAttributeMapping('ean', 'EAN/UPC', true, false);
        //$this->addAttributeMapping('upc', 'UPC',true,false);
        $this->addAttributeMapping('', 'RETAILER_MESSAGE', true, false);
        $this->addAttributeMapping('', 'PRID', true, false);


        $this->addAttributeDefault('local_category', 'none', 'PCategoryTree'); //store's local category tree
        $this->addAttributeDefault('price', 'none', 'PSalePriceIfDefined');
        $this->addRule('price_rounding', 'pricerounding'); //2 decimals

    }

    function formatProduct($product)
    {
        //is_for_sale
        if (isset($product->attributes['has_sale_price']))
            if ($product->attributes['has_sale_price'] == 1)
                $product->attributes['has_sale_price'] = 1;
            else
                $product->attributes['has_sale_price'] = 0;

        return parent::formatProduct($product);
    }

    function getFeedHeader($file_name, $file_path)
    {
        $output = '<?xml version="1.0" encoding="UTF-8" ?>
 <products>';
        return $output;
    }


}
