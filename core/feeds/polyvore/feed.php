<?php

	/********************************************************************
	Version 2.1
		A Google Feed
	Copyright 2014 Purple Turtle Productions. All rights reserved.
	license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-08
		2014-09 Retired Attribute Mapping v2.0 (Keneto)
		2014-11 All required & optional parameters now show
	********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

//Feed should include only Women’s Fashion and Beauty products and/or Home items—Men’s and Kids’ items, etc., should not be included

class PPolyvoreFeed extends PXMLFeed
{
	function __construct () 
	{
		parent::__construct();
		$this->providerName = 'Polyvore';
		$this->providerNameL = 'polyvore';
		//$this->fileformat = 'tsv';

		//Create some attributes (Mapping 3.0) in the form (title, Google-title, CData, isRequired)
		//Note that isRequired is just to direct the plugin on where on the dialog to display

		//Basic product information
		$this->addAttributeMapping('brand', 'g:brand', true, true);
		$this->addAttributeMapping('id', 'g:id', false, true);
		$this->addAttributeMapping('item_group_id', 'g:item_group_id', false, true);
		$this->addAttributeMapping('title', 'title', true, true);
		$this->addAttributeMapping('description', 'description', true, true);
		$this->addAttributeMapping('link', 'link', true, true);
		$this->addAttributeMapping('product_type', 'g:product_type', true, true);	
		$this->addAttributeMapping('current_category', 'g:google_product_category', true, true);
		$this->addAttributeMapping('feature_imgurl', 'g:image_link', true, true);
		$this->addAttributeMapping('condition', 'g:condition', false, true);

		//Polyvore
		$this->addAttributeMapping('size_system', 'g:size_system', true, false); //US, UK, IT, FR, etc...
		$this->addAttributeMapping('size_type', 'g:size_type', true, false);
		$this->addAttributeMapping('cpc_tracking_url', 'cpc_tracking_url', true, false);

		//Availability & Price
		$this->addAttributeMapping('stock_status', 'g:availability', false, true);		
		$this->addAttributeMapping('regular_price', 'g:price', false, true);
		$this->addAttributeMapping('sale_price', 'g:sale_price', false, false);
		$this->addAttributeMapping('sale_price_effective_date', 'g:sale_price_effective_date', true, false);

		//Unique Product Identifiers
		$this->addAttributeMapping('sku', 'g:mpn', true, true);
		$this->addAttributeMapping('upc', 'g:gtin', true, false);
		$this->addAttributeMapping('', 'g:identifier_exists', true, false);		
		
		//Detailed Product Attributes		
		$this->addAttributeMapping('gender', 'g:gender', true, false);
		$this->addAttributeMapping('age_group', 'g:age_group', false, false);
		$this->addAttributeMapping('color', 'g:color', true, false);
		$this->addAttributeMapping('size', 'g:size', true, false);
		//$this->addAttributeMapping('', 'g:size_type', true, false);
		//$this->addAttributeMapping('', 'g:size_system', true, false);		
		$this->addAttributeMapping('', 'g:material', true, false);
		$this->addAttributeMapping('', 'g:pattern', true, false);	

		//Tax & Shipping
		$this->addAttributeMapping('', 'g:tax', true, false);
		$this->addAttributeMapping('weight', 'g:shipping_weight', false, false);
		$this->addAttributeMapping('length', 'g:shipping_length', false, false); //req. measurment unit
		$this->addAttributeMapping('width', 'g:shipping_width', false, false);
		$this->addAttributeMapping('height', 'g:shipping_height', false, false);
		$this->addAttributeMapping('', 'g:shipping_label', false, false);

		$this->addAttributeMapping('', 'g:multipack', true, false); //integer
		$this->addAttributeMapping('', 'g:is_bundle', true, false); //TRUE or FALSE
		$this->addAttributeMapping('', 'g:adult', true, false); //TRUE or FALSE

		// $this->addAttributeMapping('', 'g:adwords_grouping', true, false); //deprecated
		// $this->addAttributeMapping('', 'g:adwords_labels', true, false); //deprecated
		$this->addAttributeMapping('', 'g:adwords_redirect', true, false);

		$this->addAttributeMapping('', 'g:unit_pricing_measure', true, false);
		$this->addAttributeMapping('', 'g:unit_pricing_base_measure', true, false);
		$this->addAttributeMapping('', 'g:energy_efficiency_class', true, false);
		$this->addAttributeMapping('', 'g:excluded_destination', true, false);
		$this->addAttributeMapping('', 'g:expiration_date', true, false);

		//Custom Label Attributes for Shopping Campaigns
		$this->addAttributeMapping('', 'g:custom_label_0', true, false);
		$this->addAttributeMapping('', 'g:custom_label_1', true, false);
		$this->addAttributeMapping('', 'g:custom_label_2', true, false);
		$this->addAttributeMapping('', 'g:custom_label_3', true, false);
		$this->addAttributeMapping('', 'g:custom_label_4', true, false);

		$this->google_exact_title = false;
		$this->google_combo_title = false;
		$this->productLevelElement = 'item';

		$this->addAttributeDefault('additional_images', 'none', 'PGoogleAdditionalImages');
		$this->addAttributeDefault('tax_country', 'US');
		$this->addAttributeDefault('local_category', 'none','PCategoryTree'); //store's local category tree

		$this->addRule('price_standard', 'pricestandard'); //append currency
		$this->addRule('status_standard', 'statusstandard'); //'in stock' or 'out of stock'
		$this->addRule('price_rounding','pricerounding'); //2 decimals
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
 		//Fix: Select specific region, example: Toronto
 		//********************************************************************
		if (isset($product->attributes['sale_price_dates_from']) && isset($product->attributes['sale_price_dates_to'])) 
		{	
			$product->attributes['sale_price_dates_from'] = $pfcore->localizedDate( 'Y-m-d\TH:i:sO', $product->attributes['sale_price_dates_from'] );
			$product->attributes['sale_price_dates_to'] = $pfcore->localizedDate( 'Y-m-d\TH:i:sO', $product->attributes['sale_price_dates_to'] );

			if ( strlen($product->attributes['sale_price_dates_from']) > 0 && strlen($product->attributes['sale_price_dates_to']) > 0 )
				$product->attributes['sale_price_effective_date'] = $product->attributes['sale_price_dates_from'].'/'.$product->attributes['sale_price_dates_to'];
		}

		//********************************************************************
		//Validation checks & Error messages
		//********************************************************************

		if (!isset($product->attributes['brand']) || (strlen($product->attributes['brand']) == 0))
			if (($this->getMappingByMapto('g:identifier_exists') == null))
				$this->addErrorMessage(2000, 'Missing brand for ' . $product->attributes['title']);

		return parent::formatProduct($product);

	}

	function getFeedFooter($file_name, $file_path) 
	{   
    	$output = '
  </channel>
</rss>';
		return $output;
	}

	function getFeedHeader( $file_name, $file_path ) 
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