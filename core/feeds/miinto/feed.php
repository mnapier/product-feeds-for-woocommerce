<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/********************************************************************
 * Version 3.0
 * An eBay Commerce Network (shopping.com) Feed
 * Copyright 2014 Purple Turtle Productions. All rights reserved.
 * license    GNU General Public License version 3 or later; see GPLv3.txt
 * By: Keneto 2014-05-08
 * 2014-09 Moved to Attribute mapping v3.0
 ********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PMiintoFeed extends PTSVFeedEx
{

    function __construct()
    {
        parent::__construct();
        $this->providerName = 'Miinto';
        $this->providerNameL = 'Miinto';

        $this->fileformat = 'tsv';
        $this->fields = array();
        $this->fieldDelimiter = "\t";

        //Basic product information

        $this->addAttributeMapping('id', 'id', true, false); //internal product id
        $this->addAttributeMapping('item_group_id', 'item_group_id', false, true); //Internal product group id - Must not be changed
        $this->addAttributeMapping('title', 'title', true, true); //Title of the product
        $this->addAttributeMapping('description', 'description', true, true); //Description of the product

        $this->addAttributeMapping('regular_price', 'price', false, true); //Price in cents
        $this->addAttributeMapping('sale_price', 'sale_price', false, true); //Price after discount in cents
        $this->addAttributeMapping('brand', 'brand', true, true); //Brand name
        $this->addAttributeMapping('product_type', 'product_type', true, true); //Category name - can be mapped to Miinto category in Feed Management. It could contains both Miinto Gender and Miinto Category splitted by google category delimiter, e.g. Mand > Slippe.
        $this->addAttributeMapping('gender', 'gender', true, false); //Sex value - Possible values: Male/Female/Unisex or M/F/U
        $this->addAttributeMapping('ean', 'gtin', true, true); //Global Trade Item Number or EAN number
        $this->addAttributeMapping('color', 'color', true, true); //Color name of the product
        $this->addAttributeMapping('size', 'size', true, true); //Product size name

        $this->addAttributeMapping('quantity', 'c:stock_level:integer', true, true); //Current stock level of the product

        $this->addAttributeMapping('stock_status', 'availability', false, false); //Is product "in stock" or is "out of stock"
        $this->addAttributeMapping('feature_imgurl', 'image_link', true, true); //Link to a main product image - Prefered image size is 1000x1000 pixels or larger. Only JPEG is supported
         for ($i = 1; $i < 4; $i++)
		    $this->addAttributeMapping("additional_image_link$i", "additional_image_link$i"); //Links to additional product images separated with comma (","). Only JPEG is supported
        $this->addAttributeMapping('', 'c:season_tag:string', true, false); //Tag defining seasonability of a product
        $this->addAttributeMapping('', 'c:style_id:string', true, false); //Product style id
        $this->addRule('price_rounding', 'pricerounding');

    }

    function formatProduct($product)
    {
        $product->attributes['feature_imgurl'] = str_replace('https://', 'http://', $product->attributes['feature_imgurl']);

        $product->attributes['description'] = trim(html_entity_decode( $product->attributes['description']));
	    $product->attributes['description'] = str_replace("&nbsp;"," ",$product->attributes['description']);
        if ($product->attributes['stock_status'] == 1)
            $product->attributes['stock_status'] = 'In Stock';
        else
            $product->attributes['stock_status'] = 'Out Of Stock';

        //Manage Additional image link
	    $image_count = 1;
	    foreach($product->imgurls as $imgurl) {
		    $image_index = "additional_image_link$image_count";
		    $product->attributes[$image_index] = $imgurl;
		    $image_count++;
		    if ($image_count >= 4)
			    break;
	    }

        /*Check sale_price has value or not .If not save regular price as sale price*/
        if($product->attributes['has_sale_price'] == ''){
            $product->attributes['has_sale_price'] = 1;
            $product->attributes['sale_price'] = $product->attributes['regular_price'];
        }
        //Allowed condition values: New, Open Box, OEM, Refurbished, Pre-Owned, Like New, Good, Very Good, Acceptable
        return parent::formatProduct($product);
    }


}
