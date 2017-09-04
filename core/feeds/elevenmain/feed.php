<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/********************************************************************
 * Version 3.0
 * 11 Main Feed
 * Copyright 2014 Shopping Cart Product Feed. All rights reserved.
 * license    GNU General Public License version 3 or later; see GPLv3.txt
 * By: Keneto 2015-20-08
 ********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PElevenMainFeed extends PCSVFeedEx
{

    function __construct()
    {

        parent::__construct();
        $this->providerName = 'ElevenMain'; //feed folder name (must match everything else)
        $this->providerNameL = 'Eleven Main'; //does not appear to be used
        $this->fileformat = 'csv';
        $this->fieldDelimiter = ",";
        $this->fields = array();

//Required fields (5)
        $this->addAttributeMapping('sku', 'ItemID', true, true); //40 chars
        $this->addAttributeMapping('title', 'Title', true, true); //80 chars
        $this->addAttributeMapping('description', 'Description', true, true);
        $this->addAttributeMapping('quantity', 'Quantity', true, true);
        $this->addAttributeMapping('regular_price', 'Price', true, true);
        $this->addAttributeMapping('feature_imgurl', 'Image1', true, true);
        for ($i = 2; $i <= 5; $i++)
            $this->addAttributeMapping('additional_images' . $i, 'Image' . $i, true, true);
//Fields to Control the Item State
        $this->addAttributeMapping('', 'Delete', true, false);
        $this->addAttributeMapping('', 'Visibility', true, false); //Accepted values are: Visible, Hidden (case insensitive)
        $this->addAttributeMapping('', 'ReturnsAccepted', true, false); //Accepted values are: Yes, No, Shop
        $this->addAttributeMapping('', 'Taxable', true, false); //Accepted values are: Yes, No, Shop
//Optional
        $this->addAttributeMapping('sale_price', 'SalePrice', true, false);
        $this->addAttributeMapping('sale_price_dates_from', 'SalePriceStartDate', true, false);
        $this->addAttributeMapping('sale_price_dates_to', 'SalePriceEndDate', true, false);
        $this->addAttributeMapping('brand', 'Brand', true, false);
        $this->addAttributeMapping('condition', 'Condition', true, false);
        $this->addAttributeMapping('gender', 'Gender', true, false);
        $this->addAttributeMapping('age_group', 'AgeGroup', true, false);
//Important Variation Fields
        $this->addAttributeMapping('color', 'Color', true, false);
        $this->addAttributeMapping('size', 'Size', true, false);
        $this->addAttributeMapping('', 'Material', true, false);
        $this->addAttributeMapping('', 'Pattern', true, false);
        //for ($i = 1; $i <= 5; $i++)
        //	$this->addAttributeMapping('', 'Variation',$i,true,false);
        $this->addAttributeMapping('item_group_id', 'GroupItemID', true, false);
//Category Fields
        $this->addAttributeMapping('', 'ShopCategories', true, false); //Shop categories must be defined in the 11 Main seller portal first
        $this->addAttributeMapping('local_category', 'MerchantCategory', true, false);
        $this->addAttributeMapping('', 'GoogleCategory', true, false); //Item category value in Google Shopping taxonomy
//Additional Optional Fields
        $this->addAttributeMapping('', 'ConditionNotes', true, false); //Any additional text pertaining to item condition
        $this->addAttributeMapping('', 'Featured', true, false); //yes|no
        $this->addAttributeMapping('weight', 'Weight', true, false); //The weight of the item in pounds
        $this->addAttributeMapping('height', 'Height', true, false); //The item height in inches
        $this->addAttributeMapping('length', 'Length', true, false); //The item length in inches
        $this->addAttributeMapping('width', 'Width', true, false); //The item width in inches

        $this->addAttributeMapping('', 'ISBN', true, false);
        $this->addAttributeMapping('upc', 'UPC', true, false);
        $this->addAttributeMapping('ean', 'EAN', true, false);
        $this->addAttributeMapping('mpn', 'MPN', true, false);
        $this->addAttributeMapping('', 'JAN', true, false); //Japanese Article Number. 8 or 13 digits long.
        $this->addAttributeMapping('', 'GTIN', true, false);

        $this->addAttributeDefault('local_category', 'none', 'PCategoryTree'); //store's local category tree
        $this->addRule('substr', 'substr', array('title', '0', '80', true)); //80 length
    }

    function formatProduct($product)
    {

        //Format Products

//*** Images ***/
        $product->attributes['feature_imgurl'] = str_replace('https://', 'http://', $product->attributes['feature_imgurl']);
        if ($this->allow_additional_images && (count($product->imgurls) > 0)) {
            $image_count = 2;
            foreach ($product->imgurls as $imgurl) {
                $product->attributes['additional_images' . $image_count] = $imgurl;
                $image_count++;
                if ($image_count >= 20)
                    break;
            }
        }

        /*** sale price start/end date. Format: MM/DD/YYYY. Remember that excel may 'display' the format differently ***/
        global $pfcore;

        if (isset($product->attributes['sale_price_dates_from']))
            $product->attributes['sale_price_dates_from'] = $pfcore->localizedDate('m/d/Y', $product->attributes['sale_price_dates_from']);
        if (isset($product->attributes['sale_price_dates_to']))
            $product->attributes['sale_price_dates_to'] = $pfcore->localizedDate('m/d/Y', $product->attributes['sale_price_dates_to']);

        /*** weight in lbs ***/
        $weight_multiplier = 1;
        switch ($this->weight_unit) {
            case 'kg':
                $weight_multiplier = 2.20462;
                break;
            case 'g':
                $weight_multiplier = 0.00220462;
                break;
            case 'lbs':
                $weight_multiplier = 1;
                break;
            case 'oz':
                $weight_multiplier = 0.0625;
                break;
            default:
                $weight_multiplier = 1;
                break;
        }
        $product->attributes['weight'] = round($product->attributes['weight'] * $weight_multiplier, 3);

        /*** convert item height, length and width to inches ***/
        $dimension_multiplier = 1;
        switch ($product->attributes['dimension_unit']) {
            case 'm':
                $dimension_multiplier = 39.3701;
                break;
            case 'cm':
                $dimension_multiplier = 0.393701;
                break;
            case 'mm':
                $dimension_multiplier = 0.0393701;
                break;
            case 'in':
                $dimension_multiplier = 1;
                break;
            case 'yd':
                $dimension_multiplier = 36;
                break;
            default:
                $dimension_multiplier = 1;
                break;
        }
        //convert to cm
        $product->attributes['height'] = round($product->attributes['height'] * $dimension_multiplier, 2);
        $product->attributes['width'] = round($product->attributes['width'] * $dimension_multiplier, 2);
        $product->attributes['length'] = round($product->attributes['length'] * $dimension_multiplier, 2);

        return parent::formatProduct($product);
    }

}
