<?php

  /********************************************************************
  Version 1.0
		A TradeTracker Feed
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Sushma 2017-07-02

  ********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PTradeTrackerFeed extends PXMLFeed
{
	function __construct () 
	{
		parent::__construct();
		$this->providerName = 'TradeTracker';
		$this->providerNameL = 'tradetracker';
		$this->productLevelElement = 'product';
		//Create some attributes (Mapping 3.0)
	
		$this->addAttributeMapping('id', 'ID',true,true); //SKU, 5-100 chars
		//$this->addAttributeMapping('brand', 'g:brand',true,true); //grouping sku
		$this->addAttributeMapping('title', 'name',true);
		$this->addAttributeMapping('description', 'description',true); //reserved for future use
		$this->addAttributeMapping('link', 'productURL',true,true);
		$this->addAttributeMapping('feature_imgurl', 'imageURL',true,true);
		$this->addAttributeMapping('sale_price', 'price',true,true);	//5 - 200 chars
		$this->addAttributeMapping('regular_price', 'fromPrice',true,true);	

		$this->addAttributeMapping('' , 'discount' , true , true , false );
        $this->addAttributeMapping('localCategory', 'categorypath' , true , true , false);
        $this->addAttributeMapping('category' , 'categories' , true , true , false );
        $this->addAttributeMapping('subcategory' , 'subcategories' , true , true , false );
        $this->addAttributeMapping('subsubcategory' , 'subsubcategories' , true , true , false );
        $this->addAttributeMapping('color' , 'color' , true , true , false );
        //$this->addAttributeMapping('quantity' , 'stock' , true , true , false );
        $this->addAttributeMapping('material' , 'material' , true , true , false );	
		$this->addAttributeMapping('brand', 'brand',true); //100% cotton
		$this->addAttributeMapping('delivery_time', 'deilveryTime',true, true); 
		$this->addAttributeMapping('delivery_costs', 'deliveryCosts',true);
		$this->addAttributeMapping('ean', 'EAN',true);	
		$this->addAttributeMapping('size', 'size',true); 
		$this->addAttributeMapping('stock_quantity', 'stock',true); 
		$this->addAttributeMapping('gender', 'gender',true); 
		
		
//List the product price in US dollars, without a $ sign, commas, text, or quotation marks.
		$this->addRule('price_rounding','pricerounding');
		$this->addRule( 'description', 'description',array('max_length=6500','strict') ); 
		//$this->addRule( 'csv_standard', 'CSVStandard',array('description') ); 
		//$this->addRule( 'csv_standard', 'CSVStandard',array('title','200') ); //200 title char limit
		$this->addRule( 'substr','substr', array('title','0','200',true) ); //200 length
	
	}

function getFeedFooter($file_name, $file_path) 
	{   
    	$output = '
  </productfeed>';
		return $output;
	}

	function getFeedHeader( $file_name, $file_path ) 
	{
		$output = '<?xml version="1.0" encoding="UTF-8" ?>
  <productfeed>';
		return $output;
  }
  
	function formatProduct($product) {

		global $pfcore;
		
		$category = explode(":", $this->current_category);
		if (isset($category[1]))
			$product->attributes['current_category'] = trim($category[1]);
		else
			$product->attributes['current_category'] = 'no category selected';
		$variantUPC = '';
		$variantMfr = '';
		if ($product->attributes['isVariation']) {
			//Not used in original code
			//$variantUPC = rand();
			//$variantMfr = rand();
		}

/*
* This is an essential requirement from Grazia and gives the store owner the ability to simply 
* select 'Yes' or 'No' at the product level.
* GraziaShop Business rule: setAttributeDefualt business-attribute as none PGraziaBusinessRule
* where business-attribute is the custom field containing 'yes' or 'no' (if the custom attribute has spaces, enclose it in double quotes)
*/

//additional image links 
		if ( $this->allow_additional_images && (count($product->imgurls) > 0) )
	 		$product->attributes['additional_image_links'] = implode(',', $product->imgurls); 
//sale price from/to dates
	 	//if (($product->attributes['has_sale_price']) {
		if (isset($product->attributes['sale_price_dates_from']) && isset($product->attributes['sale_price_dates_to'])) 
		{
			$product->attributes['sale_price_dates_from'] = $pfcore->localizedDate( 'Y-m-d H:i:s', $product->attributes['sale_price_dates_from'] );
			$product->attributes['sale_price_dates_to'] = $pfcore->localizedDate( 'Y-m-d H:i:s', $product->attributes['sale_price_dates_to'] );

			if ( strlen($product->attributes['sale_price_dates_from']) > 0 && strlen($product->attributes['sale_price_dates_to']) > 0 )
				$product->attributes['sale_price_effective_date'] = $product->attributes['sale_price_dates_from'].' '.$product->attributes['sale_price_dates_to'];
		}
//currency
		// if (!isset($product->attributes['currency']) || (strlen($product->attributes['currency']) == 0))
		//$product->attributes['currency'] = $this->currency;
//stock
		// if ( !isset($product->attributes['quantity']) )
		// 	$product->attributes['quantity'] = $product->attributes['stock_quantity'];

//langauge: EN, FR, IT
		$language = get_locale();
		if (strpos($language,'_') !== false) {
			$language= substr($language, 0, strpos($language, '_'));
			$product->attributes['language'] = strtoupper($language);
		}

		if(count($product->taxonomy) > 0 ){
            $product->attributes['localCategory'] = implode('>' , $product->taxonomy);
				if(isset($product->taxonomy[0]))
			$product->attributes['category'] = $product->taxonomy[0];
		else
			$product->attributes['category'] = 'N.v.t.';
				
				if(isset($product->taxonomy[1]))
			$product->attributes['subcategory'] = $product->taxonomy[1];
		else
			$product->attributes['subcategory'] = 'N.v.t';
				
			
				if(isset($product->taxonomy[2]))
			$product->attributes['subsubcategory'] = $product->taxonomy[2];
		else
			$product->attributes['subsubcategory'] = 'N.v.t.';
				
}
		return parent::formatProduct($product);

	}//formatProduct	

}


?>