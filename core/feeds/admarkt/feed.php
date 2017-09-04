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

class PAdmarktFeed extends PBasicFeed
{
    function __construct()
    {
        parent::__construct();
        $this->providerName = 'Admarkt';
        $this->providerNameL = 'admarkt';

        //Create some attributes (Mapping 3.0) in the form (title, Google-title, CData, isRequired)
        //Note that isRequired is just to direct the plugin on where on the dialog to display

        //Basic product information
        $this->addAttributeMapping('id', 'admarkt:id', true, true);
        $this->addAttributeMapping('sku', 'admarkt:externalId', true, true);
        $this->addAttributeMapping('current_category', 'admarkt:categoryId', false, true);
        $this->addAttributeMapping('title', 'admarkt:title', true, true);
        $this->addAttributeMapping('description', 'admarkt:description', true, true);
        $this->addAttributeMapping('regular_price', 'admarkt:price', true, true);
        $this->addAttributeMapping('price_type', 'admarkt:priceType', false, true);
        $this->addAttributeMapping('cpc', 'admarkt:cpc', false, false);
        $this->addAttributeMapping('link', 'admarkt:url', true, true);
        $this->addAttributeMapping('media', 'admarkt:media', false, true);
        $this->addAttributeMapping('attributes', 'admarkt:attributes');


        $this->google_exact_title = false;
        $this->google_combo_title = false;

        //automatic identifier_exists=false function.
        //set google_identifier to false to disable
        $this->google_identifier = true;

        $this->productLevelElement = 'admarkt:ad';

        $this->addRule('price_standard', 'pricestandard'); //append currency
        $this->addRule('status_standard', 'statusstandard'); //'in stock' or 'out of stock'
        $this->addRule('price_rounding', 'pricerounding'); //2 decimals
        //shipping
        $this->addRule('weight_unit', 'weightunit');
        $this->addRule('length_unit', 'dimensionunit', array('length'));
        $this->addRule('width_unit', 'dimensionunit', array('width'));
        $this->addRule('height_unit', 'dimensionunit', array('height'));

        $this->addRule('google_exact_title', 'googleexacttitle'); //true disables ucowrds
        $this->addRule('google_combo_title', 'googlecombotitle');
    }

    function formatProduct($product)
    {
        
        global $pfcore;

        $category = explode(":", $product->attributes['current_category']);
        if (isset($category[1]))
            $product->attributes['current_category'] = trim($category[0]);
        else
            $product->attributes['current_category'] = '';

        if (isset($category[1]))
            $product->attributes['category_id'] = trim($category[1]);
        //********************************************************************
        //Prepare the Product Attributes
        //********************************************************************

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
        #echo '<pre>';print_r($product);die;
        

        // echo $attr;die;
        
        $output = '
        <' . $this->productLevelElement . '>';

        //********************************************************************
        //Add attributes (Mapping 3.0)
        //********************************************************************

        foreach ($this->attributeMappings as $thisAttributeMapping){

            if ($thisAttributeMapping->attributeName == 'attributes') {
                $output .= "<admarkt:attributes>";
                foreach ($product->wc_attributes as $key => $value) {
                    if (in_array($key, $product->attributes) ) {
                        $output .= "<admarkt:attribute>";
                        // $output = $this->formatLine('attribute', $key, false);
                            // $output .= '<'.$this->productLevelElement.'attributeName>'.$key.'</'.$this->productLevelElement.'attribute>';
                            // $output .= '<'.$this->productLevelElement.'attributeValue>'.$product->attributes[$key].'</'.$this->productLevelElement.'attribute>';
                        $output .= $this->formatLine('admarkt:attributeName', $key, $thisAttributeMapping->usesCData);
                        $output .= $this->formatLine('admarkt:attributeValue', $product->attributes[$key], $thisAttributeMapping->usesCData);
                        $output .= "</admarkt:attribute>";
                    }
                }
                $output .= "</admarkt:attributes>";
            } elseif($thisAttributeMapping->attributeName == 'media'){
                if (count($product->imgurls) == 0) {
                    continue;
                }
                $output .= "<admarkt:media>";
                foreach ($product->imgurls as $k => $img) {
                    $output .= $this->formatLine('admarkt:image',$img, false);
                }
                $output .= "</admarkt:media>";

            } elseif ($thisAttributeMapping->enabled && !$thisAttributeMapping->deleted && isset($product->attributes[$thisAttributeMapping->attributeName])){
                $output .= $this->formatLine($thisAttributeMapping->mapTo, $product->attributes[$thisAttributeMapping->attributeName], $thisAttributeMapping->usesCData);
            }

        }

        //********************************************************************
        //Mapping 3.0 post processing
        //********************************************************************

        foreach ($this->attributeDefaults as $thisDefault){
            if ($thisDefault->stage == 3){
                $thisDefault->postProcess($product, $output);
            }
        }
                

        $output .= '
            </' . $this->productLevelElement . '>';

        return $output;

        #return parent::formatProduct($product);

    }

    function getFeedFooter($file_name, $file_path)
    {
            $output = '
        </admarkt:ads>';
        return $output;
    }

    function getFeedHeader($file_name, $file_path)
    {
        $output = '<?xml version="1.0" encoding="UTF-8" ?>
        <admarkt:ads xmlns:admarkt="http://admarkt.marktplaats.nl/schemas/1.0">';
        return $output;
    }

}