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

class PAffiliateWindowXMLFeed extends PXMLFeed
{

    function __construct()
    {

        parent::__construct();
        $this->providerName = 'AffiliateWindowXML';
        $this->providerNameL = 'AffiliateWindowXML';
        $this->productLevelElement = 'product';
        //$this->topLevelElement = 'merchant';
        $this->fileformat = 'xml'; //format/extension
        //$this->fieldDelimiter = ","; //comma delimiter
        $this->fields = array();

//Required fields (4)
        $this->addAttributeMapping('id', 'pid', true, true);
        $this->addAttributeMapping('title', 'name', true, true);
        $this->addAttributeMapping('price', 'price', true, true);
        $this->addAttributeMapping('link', 'purl', true, true); //product URL
//Highly Recommended
        $this->addAttributeMapping('description', 'desc', true, false);
        $this->addAttributeMapping('current_category', 'merchant_category', true, true);
        $this->addAttributeMapping('featured_imgurl', 'imgurl', true, false);
        $this->addAttributeMapping('localCategory', 'category', true, true);
//Optional	

        $this->addAttributeMapping('sku', 'sku', true, false);
        $this->addAttributeMapping('item_group_id', 'custom_1', true, false);
        $this->addAttributeMapping('parent_sku', 'custom_2', true, false);
        $this->addAttributeMapping('brand', 'brand', true, false);
        $this->addAttributeMapping('condition', 'condition', true, false);
        $this->addAttributeMapping('stock_status', 'instock', true, false);
        $this->addAttributeMapping('stock_quantity', 'stockquantity', true, false);
        $this->addAttributeMapping('size', 'size', true, false);
        $this->addAttributeMapping('color', 'colour', true, false);


        $this->addAttributeMapping('ean', 'ean', true, false);
        $this->addAttributeMapping('upc', 'upc', true, false);
        $this->addAttributeMapping('', 'isbn', true, false);
        $this->addAttributeMapping('', 'model_number', true, false);
        $this->addAttributeMapping('', 'basket_link', true, false); //Deeplink which automatically adds the product to the users basket
        $this->addAttributeMapping('', 'commission_group', true, false);


        $this->addAttributeMapping('', 'dimensions', true, false);
        $this->addAttributeMapping('', 'keywords', true, false);
        $this->addAttributeMapping('', 'language', true, false);
        $this->addAttributeMapping('', 'product_type', true, false);
        $this->addAttributeMapping('weight', 'product_weight', true, false);
        $this->addAttributeMapping('', 'promotional_text', true, false);
        $this->addAttributeMapping('', 'specifications', true, false);
        $this->addAttributeMapping('', 'warranty', true, false);
        $this->addAttributeMapping('currency', 'currency', true, false);
        $this->addAttributeMapping('delivery_cost', 'delivery_cost', true, false);
        $this->addAttributeMapping('', 'delivery_restrictions', true, false);
        $this->addAttributeMapping('delivery_time', 'delivery_time', true, false);
        $this->addAttributeMapping('has_sale_price', 'is_for_sale', true, false);
        $this->addAttributeMapping('', 'pre_order', true, false);
        $this->addAttributeMapping('', 'rrp_price', true, false); //Recommended retail price for the product
        $this->addAttributeMapping('', 'saving', true, false);
        $this->addAttributeMapping('', 'store_price', true, false);
        $this->addAttributeMapping('', 'valid_from', true, false);
        $this->addAttributeMapping('', 'valid_to', true, false);
        $this->addAttributeMapping('', 'web_offer', true, false);
        $this->addAttributeMapping('', 'merchant_image', true, false);
        $this->addAttributeMapping('', 'merchant_thumb', true, false);
        $this->addAttributeMapping('', 'thumb_url', true, false);
        $this->addAttributeMapping('', 'average_rating', true, false);
        $this->addAttributeMapping('', 'reviews', true, false);
        $this->addAttributeMapping('', 'Rating', true, false);

        //$this->addAttributeMapping('', 'custom_2',true,false);
        $this->addAttributeMapping('', 'custom_3', true, false);
        $this->addAttributeMapping('', 'custom_4', true, false);
        $this->addAttributeMapping('', 'custom_5', true, false);
        $this->addAttributeMapping('', 'last_updated', true, false);

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

    function getFeedFooter($file_name, $file_path)
    {
        $output = '
</merchant>';
        return $output;
    }

    function getFeedHeader($file_name, $file_path)
    {
        $output = '<?xml version="1.0" encoding="UTF-8" ?>
<!DOCTYPE merchant SYSTEM "http://www.affiliatewindow.com/DTD/merchant/datafeedupload.1.4.dtd">
<merchant>';
        return $output;
    }
}
