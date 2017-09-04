<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/********************************************************************
 * Version 2.0
 * A Pricefalls Feed
 * Copyright 2014 Purple Turtle Productions. All rights reserved.
 * license    GNU General Public License version 3 or later; see GPLv3.txt
 * By: Sushma 2016-12-20
 * 2014-08 Moved to Attribute Mapping v3
 ********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PPricefallsFeed extends PCSVFeedEx
{

    //public $bingForceGoogleCategory = false;
    //public $bingForcePriceDiscount = false;

    function __construct()
    {
        parent::__construct();
        $this->providerName = 'Pricefalls';
        $this->providerNameL = 'pricefalls';
        $this->fileformat = 'txt';
        $this->fields = array();
        $this->fieldDelimiter = "\t";
        $this->stripHTML = true;
        //Create some attributes (Mapping 3.0)

        //required
        //$this->addAttributeMapping('id', 'MPID',false,true);
        $this->addAttributeMapping('title', 'Item Title', true, true);
        $this->addAttributeMapping('brand', 'Brand Name', true, true);
        $this->addAttributeMapping('current_category', 'Product Category', true, true);
        $this->addAttributeMapping('regular_price', 'Fixed Product Price', true, true); //base price
        $this->addAttributeMapping('description', 'Product Description', true, true);
        $this->addAttributeMapping('feature_imgurl', 'Image 1 URL', true, true);
        $this->addAttributeMapping('quantity', 'Available Product Quantity', true, true);
        $this->addAttributeMapping('condition', 'Product Condition', true, true);

        //optional
        $this->addAttributeMapping('availability', 'Stock Availability', true, false);
        $this->addAttributeMapping('current_category', 'Merchant Category Trail', true, false);
        $this->addAttributeMapping('merchant_id', 'Merchant ID', true, false);
        $this->addAttributeMapping('tags', 'Search Term Suggestions', true, false);
        $this->addAttributeMapping('', 'Dutch Auction Price Ceiling', true, false);
        $this->addAttributeMapping('', 'Dutch Auction Price Floor', true, true);
        $this->addAttributeMapping('', 'MSRP', true, false);
        $this->addAttributeMapping('', 'MAP', true, false);
        $this->addAttributeMapping('', 'Dead Cost Price', true, false); //desired bing category
        $this->addAttributeMapping('sku', 'SKU', true, false);
        $this->addAttributeMapping('upc', 'UPC', true, false);
        $this->addAttributeMapping('mpn', 'MPN', true, false);
        $this->addAttributeMapping('isbn', 'ISBN', true, false);
        $this->addAttributeMapping('asin', 'ASIN', true, false);

        $this->addAttributeMapping('brand', 'Supplier Name'); //valid values: Newborn, Infant, Toddler, Kid, Adult
        $this->addAttributeMapping('', 'Dutch Auction Catchit Duration', false, false);
        $this->addAttributeMapping('product_listing_duration', 'Product Listing Duration', false, false);
        //product variants
        $this->addAttributeMapping('color', 'Product Color', false, false);
        $this->addAttributeMapping('', 'Product Material', false, false);
        $this->addAttributeMapping('', 'Product Pattern', false, false);
        $this->addAttributeMapping('gender', 'Product Gender', false, false);
        $this->addAttributeMapping('size', 'Product Size', false, false);
        $this->addAttributeMapping('age_group', 'Product Age Group', false, false);
        //additonal image links
        $this->addAttributeMapping('additional_imag_link0', 'Image 2 URL', true, false);
        $this->addAttributeMapping('additional_imag_link1', 'Image 3 URL', true, false);
        $this->addAttributeMapping('additional_imag_link2', 'Image 4 URL', true, false);
        $this->addAttributeMapping('additional_imag_link3', 'Image 5 Url', true, false);
        $this->addAttributeMapping('video_url', 'Video URL', true, false);

        //sgipping attributes
        $this->addAttributeMapping('', 'Shipping Cost', true, false);
        $this->addAttributeMapping('', 'Expedited Shipping Cost', true, false);
        $this->addAttributeMapping('', 'Two-Day Shipping Cost', true, false);
        $this->addAttributeMapping('', 'One-Day Shipping Cost', true, false);
        $this->addAttributeMapping('', 'Handling Fee', true, false);
        $this->addAttributeMapping('weight', 'Shipping Weight', true, false);
        $this->addAttributeMapping('', 'Shipping Dimensions', true, false);
        $this->addAttributeMapping('', 'Override for Returns Policy Type', true, false);
        $this->addAttributeMapping('', 'Override for Warranty Length', true, false);
        $this->addAttributeMapping('', 'Override for Lead Time', true, false);
        $this->addAttributeMapping('', 'Override for Shipping Type', true, false);
        $this->addAttributeMapping('', 'Override for Shipping Source', true, false);
        //$this->addAttributeMapping('', 'Handling Fee', true, false);

        //optional - sales and promotions
        //if ($this->bingForcePriceDiscount)
        //$this->addAttributeMapping('sale_price', 'sale_price');
        //$this->addAttributeMapping('sale_price_effective_date', 'sale_price_effective_date');

        $this->addRule('description', 'description', array('max_length=5000', 'strict'));
        // $this->addRule( 'csv_standard', 'CSVStandard',array('title','150') );
        // $this->addRule( 'csv_standard', 'CSVStandard',array('description') );
        $this->addRule('substr', 'substr', array('title', '0', '150', true)); //150 length
    }

    function formatProduct($product)
    {

        global $pfcore; //required to call localizedDate (sale_price_dates_from/to)

        //********************************************************************
        //Prepare the Product Attributes
        //********************************************************************

        //if ($product->attributes['isVariation'])
        //'Item Group ID' => $product->item_group_id;
        if (strlen($product->attributes['regular_price']) == 0)
            $product->attributes['regular_price'] = '0.00';

        $product->attributes['regular_price'] = sprintf($this->currency_format, $product->attributes['regular_price']);
        if ($product->attributes['has_sale_price'])
            $product->attributes['sale_price'] = sprintf($this->currency_format, $product->attributes['sale_price']);


        //Note: Only In stock && New products will publish on Bing
        if ($product->attributes['stock_status'] >= 1)
            $product->attributes['availability'] = 'in stock';
        else
            $product->attributes['availability'] = 'Out of stock';

        //********************************************************************
        //Google date, ISO 8601 format.
        //Timezone Bug in WordPress: a manual offset, for example UTC+5:00 will show offset of 0
        //Fix: Select specific region, example: Toronto
        //********************************************************************
        if (isset($product->attributes['sale_price_dates_from']) && isset($product->attributes['sale_price_dates_to'])) {
            $product->attributes['sale_price_dates_from'] = $pfcore->localizedDate('Y-m-d\TH:iO', $product->attributes['sale_price_dates_from']);
            $product->attributes['sale_price_dates_to'] = $pfcore->localizedDate('Y-m-d\TH:iO', $product->attributes['sale_price_dates_to']);

            if (strlen($product->attributes['sale_price_dates_from']) > 0 && strlen($product->attributes['sale_price_dates_to']) > 0)
                $product->attributes['sale_price_effective_date'] = $product->attributes['sale_price_dates_from'] . '/' . $product->attributes['sale_price_dates_to'];
        }

        //if ($this->bingForceGoogleCategory) {
        //For this to work, we need to enable a Google taxonomy dialog box.
        //}

        //********************************************************************
        //Validation checks & Error messages
        //********************************************************************
        /*
        title, brand, (MPN), Sku, b_category = 255
        URL, ImageURL = 2000, UPC12 ISBN13
        Description 5000
        if (strlen($product->attributes['title']) > 255) {
            $product->attributes['title'] = substr($product->attributes['title'], 0, 254);
            $this->addErrorMessage(000, 'Title truncated for ' . $product->attributes['title'], true);
        }*/

        return parent::formatProduct($product);
    }

}
