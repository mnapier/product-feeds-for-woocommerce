<?php

	/********************************************************************
	Version 3.0
		An eBay Commerce Network (shopping.com) Feed
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-08
		2014-09 Moved to Attribute mapping v3.0
	********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PeBayFeed extends PBasicFeed {

	function __construct () {
		parent::__construct();
		$this->providerName = 'eBay';
		$this->providerNameL = 'ebay';
		//9 required fields
		//Required
		$this->addAttributeMapping('sku', 'Merchant_SKU', true,true);
		$this->addAttributeMapping('title', 'Product_Name', true,true);
		$this->addAttributeMapping('link', 'Product_URL', true,true);
		$this->addAttributeMapping('feature_imgurl', 'Image_URL', true,true);
		$this->addAttributeMapping('price', 'Current_Price', true,true);
		$this->addAttributeMapping('stock_status', 'Stock_Availability', true,true);
		$this->addAttributeMapping('condition', 'Condition', true,true);
		
		//MPN or ISBN (media only)
		$this->addAttributeMapping('', 'MPN', true); //can be blank
		$this->addAttributeMapping('', 'ISBN', true); //can be blank
		//UPC or EAN (media only)
		$this->addAttributeMapping('', 'UPC', true); //can be blank
		$this->addAttributeMapping('', 'EAN', true); //can be blank

		//Recommended Attributes
		$this->addAttributeMapping('', 'Shipping_Rate', true);
		//If your website displays a price drop and/or percent savings for this item, provide here the item’s original price.
		$this->addAttributeMapping('', 'Original_Price', true);
		$this->addAttributeMapping('', 'Coupon_Code', true);
		$this->addAttributeMapping('', 'Coupon_Code_Description', true);
		$this->addAttributeMapping('brand', 'Brand', true);

		$this->addAttributeMapping('description', 'Product_Description', true);
		$this->addAttributeMapping('', 'Product_Type', true); //local category
		$this->addAttributeMapping('current_category', 'Category', true);
		$this->addAttributeMapping('category_id', 'Category_ID');
		$this->addAttributeMapping('parent_sku', 'Parent_SKU',true); 
		$this->addAttributeMapping('', 'Parent_Name', true); //TODO		
		$this->addAttributeMapping('', 'Stock_description', true); //promotional text ie. Free shipping
		
		$this->addAttributeMapping('gender', 'Gender', true);
		$this->addAttributeMapping('color', 'Color', true);
		$this->addAttributeMapping('', 'Material', true);
		$this->addAttributeMapping('size', 'Size', true);
		$this->addAttributeMapping('', 'Size_Unit_Of_Measure', true);
		$this->addAttributeMapping('', 'Age_Range', true);		
		//Optional Attributes	
		for ($i = 0; $i < 10; $i++)
			$this->addAttributeMapping("alt_image_$i", "Alternative_Image_URL_$i", true);	
				
		// $this->addAttributeMapping('product_weight', 'Product_Weight', true);
		// $this->addAttributeMapping('shipping_weight', 'Shipping_Weight', true);
		// $this->addAttributeMapping('weight_unit', 'Weight_Unit_of_Measure', true);
		$this->addRule('status_standard', 'statusstandard'); //'in stock' or 'out of stock'

	}

  function formatProduct($product) {

	$category = explode(":", $product->attributes['current_category']);
	if (isset($category[1]))
		$product->attributes['current_category'] = trim($category[0]);
	else
		$product->attributes['current_category'] = '';
	
	if (isset($category[1]))
		$product->attributes['category_id'] = trim($category[1]);

	foreach($product->imgurls as $index => $imgurl)
		$product->attributes["alt_image_$index"] =  $imgurl;

	$product->attributes['weight_unit'] = $this->weight_unit;
	//if (!isset($product->attributes['shipping_rate']))
		//$product->attributes['shipping_rate'] = '0.00 ' . $this->currency;

		//********************************************************************
		//Mapping 3.0 pre processing
		//********************************************************************

		foreach ($this->attributeDefaults as $thisDefault)
			if ($thisDefault->stage == 2)
				$product->attributes[$thisDefault->attributeName] = $thisDefault->getValue($product);

		$output = '
      <Product>';

		//********************************************************************
		//Add attributes (Mapping 3.0)
		//********************************************************************

		foreach($this->attributeMappings as $thisAttributeMapping)
			if ($thisAttributeMapping->enabled && !$thisAttributeMapping->deleted && isset($product->attributes[$thisAttributeMapping->attributeName]) )
				$output .= $this->formatLine($thisAttributeMapping->mapTo, $product->attributes[$thisAttributeMapping->attributeName], $thisAttributeMapping->usesCData);

		//********************************************************************
		//Mapping 3.0 post processing
		//********************************************************************

		foreach ($this->attributeDefaults as $thisDefault)
			if ($thisDefault->stage == 3)
				$thisDefault->postProcess($product, $output);

		//********************************************************************
		//Validation checks & Error messages
		//********************************************************************

		//if (!isset($product->attributes['brand']) || (strlen($product->attributes['brand']) == 0))
			//$this->addErrorMessage(2000, 'Missing brand for ' . $product->attributes['title']);

    $output .= '
      </Product>';
    return $output;
  }

	function getFeedFooter($file_name, $file_path) {
    $output = '
  </Products>';
		return $output;
	}

	function getFeedHeader($file_name, $file_path) {
    $output = '<?xml version="1.0" encoding="UTF-8" ?>
  <Products>';
		return $output;
	}

}
?>