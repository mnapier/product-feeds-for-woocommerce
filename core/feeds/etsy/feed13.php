<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/********************************************************************
 * Version 2.1
 * A Google Feed
 * Copyright 2014 Purple Turtle Productions. All rights reserved.
 * license GNU General Public License version 3 or later; see GPLv3.txt
 * By: Keneto 2014-05-08
 * 2014-09 Retired Attribute Mapping v2.0 (Keneto)
 * 2014-11 All required & optional parameters now show
 ********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PEtsyFeed extends PXMLFeed
{
    function __construct()
    {
        parent::__construct();
        $this->providerName = 'Etsy';
        $this->providerNameL = 'Etsy';


        //Create some attributes (Mapping 3.0) in the form (title, Google-title, CData, isRequired)
        //Note that isRequired is just to direct the plugin on where on the dialog to display

        //Basic product information
        $this->addAttributeMapping('stock_quantity', 'g:quantity', true, true);
        $this->addAttributeMapping('title', 'g:title', true, true);
        $this->addAttributeMapping('description', 'g:description', false, true);

        //Unique Product Identifiers
        $this->addAttributeMapping('sku', 'g:mpn', false, false);
        $this->addAttributeMapping('upc', 'g:gtin', false, false);
        $this->addAttributeMapping('identifier_exists', 'g:identifier_exists', false, false);

        $this->addAttributeMapping('brand', 'g:brand', false, false);
        $this->addAttributeMapping('brand', 'g:who_made', false, true);
        $this->addAttributeMapping('feature_imgurl', 'g:image', false, false);

        $this->addAttributeMapping('regular_price', 'g:price', false, true);

        $this->addAttributeMapping('materials', 'g:materials', false, false);

        $this->addAttributeMapping('', 'shipping_template_id', false, false);
        $this->addAttributeMapping('', 'shop_section_id', false);
        $this->addAttributeMapping('', 'image_ids', false);
        $this->addAttributeMapping('', 'is_customizable', false);
        $this->addAttributeMapping('', 'non_taxable', false);


        $this->addAttributeMapping('', 'state', false, false);
        $this->addAttributeMapping('', 'processing_min', false, false);
        $this->addAttributeMapping('', 'processing_max', false, false);

        $this->addAttributeMapping('', 'category_id', false, false);
        $this->addAttributeMapping('', 'taxonomy_id', false, false);
        $this->addAttributeMapping('', 'tags', false, false);

        $this->addAttributeMapping('recipient', 'recipient', false, false);
        $this->addAttributeMapping('when_made', 'when_made', false, false);
        $this->addAttributeMapping('', 'occasion', false, false);
        $this->addAttributeMapping('', 'style', false, false);
        $this->addAttributeMapping('link', 'link', true, true);


        $this->google_exact_title = false;
        $this->google_combo_title = false;


        //automatic identifier_exists=false function. 
        //set google_identifier to false to disable  
        $this->google_identifier = false;

        $this->productLevelElement = 'item';

        $this->addAttributeDefault('additional_images', 'none', 'PGoogleAdditionalImages');
        $this->addAttributeDefault('tax_country', 'US');
        $this->addAttributeDefault('local_category', 'none', 'PCategoryTree'); //store's local category tree

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
        //********************************************************************
        //Prepare the Product Attributes
        //********************************************************************

        //********************************************************************
        //Google date, ISO 8601 format. 
        //Timezone Bug in WordPress: a manual offset, for example UTC+5:00 will show offset of 0
        //Fix: Select specific region, examplg: Toronto
        //********************************************************************
        if (isset($product->attributes['sale_price_dates_from']) && isset($product->attributes['sale_price_dates_to'])) {
            $product->attributes['sale_price_dates_from'] = $pfcore->localizedDate('Y-m-d\TH:iO', $product->attributes['sale_price_dates_from']);
            $product->attributes['sale_price_dates_to'] = $pfcore->localizedDate('Y-m-d\TH:iO', $product->attributes['sale_price_dates_to']);

            if (strlen($product->attributes['sale_price_dates_from']) > 0 && strlen($product->attributes['sale_price_dates_to']) > 0)
                $product->attributes['sale_price_effective_date'] = $product->attributes['sale_price_dates_from'] . '/' . $product->attributes['sale_price_dates_to'];
        }

        //********************************************************************
        //Validation checks & Error messages
        //********************************************************************
        $id_exists_count = 0; //number of identifiers that are set

        //loop through attributes to find the mapped-to attributes for g:brand, g:mpn and g:gtin
        //check if the attribute has a value. If so, increase the count variable.
        foreach ($this->attributeMappings as $thisAttributeMapping) {
            if ($thisAttributeMapping->mapTo == 'g:brand' || $thisAttributeMapping->mapTo == 'g:mpn' || $thisAttributeMapping->mapTo == 'g:gtin') {
                if (isset($product->attributes[$thisAttributeMapping->attributeName]))
                    $id_exists_count++;
            }
        }

        //automatically sets identifier_exists to FALSE if less than 2 product identifiers are detected
        //set google_identifier to false to disable 
        if ($id_exists_count < 2 && $this->google_identifier) {
            $product->attributes['identifier_exists'] = 'FALSE';
            $this->addErrorMessage(2000, 'Missing unique identifiers for ' . $product->attributes['title']);
        }

        //debug id_exists
        //notg: if mapping from multiple attributes, id_count will count these
        //if ( isset($id_exists_count) )
        //  $product->attributes['id_count'] = $id_exists_count;

        // the following is now handled above (~line 149)
        // if (!isset($product->attributes['brand']) || (strlen($product->attributes['brand']) == 0))
        //  if (($this->getMappingByMapto('g:identifier_exists') == null))
        //      $this->addErrorMessage(2000, 'Missing brand for ' . $product->attributes['title']);

        return parent::formatProduct($product);

    }

    function getFeedFooter($file_name, $file_path)
    {
        $output = '
  </channel>
</rss>';
        return $output;
    }

    function getFeedHeader($file_name, $file_path)
    {
        $output = '<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0" xmlns:c="http://base.google.com/cns/1.0">

  <channel>
    <title>' . $file_name . '</title>
    <link><![CDATA[' . $file_path . ']]></link>
    <description>' . $file_name . '</description>';
        return $output;
    }

}