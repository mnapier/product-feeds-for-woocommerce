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

class PMiintoBrandFeed extends PTSVFeedEx {

	function __construct () {
		parent::__construct();
		$this->providerName = 'MiintoBrand';
		$this->providerNameL = 'MiintoBrand';
		$this->fileformat = 'tsv';
		$this->fields = array();
		$this->fieldDelimiter = "\t";

		//Basic product information

		$this->addAttributeMapping('ean', 'gtin', true, false); //internal product id
		$this->addAttributeMapping('item_group_id', 'item_group_id', false, true); //Internal product group id - Must not be changed
		$this->addAttributeMapping('brand', 'brand', true, true); //Brand name
		$this->addAttributeMapping('title', 'title', true, true); //Title of the product
		$this->addAttributeMapping('current_category', 'product_type', true, true); //Category name - can be mapped to Miinto category in Feed Management. It could contains both Miinto Gender and Miinto Category splitted by google category delimiter, e.g. Mand > Slippe.
		$this->addAttributeMapping('gender', 'gender', true, false); //Sex value - Possible values: Male/Female/Unisex or M/F/U
		$this->addAttributeMapping('color', 'color', true, true); //Color name of the product
		$this->addAttributeMapping('size', 'size', true, true); //Product size name
		$this->addAttributeMapping('description', 'description', true, true); //Description of the product
		$this->addAttributeMapping('sale_price', 'c:discount_retail_price_NOK:integer', false, true); //Price after discount in cents
		$this->addAttributeMapping('regular_price', 'c:retail_price_NOK:integer', false, true); //Price in cents
		$this->addAttributeMapping('wholesale_price', 'sc:wholesale_price_NOK:integer', false, true); //Price after discount in cents
		$this->addAttributeMapping('sale_price', 'c:discount_retail_price_DKK:integer', false, true); //Price after discount in cents
		$this->addAttributeMapping('regular_price', 'c:retail_price_DKK:integer', false, true); //Price in cents
		$this->addAttributeMapping('wholesale_price', 'sc:wholesale_price_DKK:integer', false, true); //Price after discount in cents
		$this->addAttributeMapping('sale_price', 'c:discount_retail_price_SEK:integer', false, true); //Price after discount in cents
		$this->addAttributeMapping('regular_price', 'c:retail_price_SEK:integer', false, true); //Price in cents
		$this->addAttributeMapping('wholesale_price', 'sc:wholesale_price_SEK:integer', false, true); //Price after discount in cents		
		$this->addAttributeMapping('sale_price', 'c:discount_retail_price_EUR:integer', false, true); //Price after discount in cents
		$this->addAttributeMapping('regular_price', 'c:retail_price_EUR:integer', false, true); //Price in cents
		$this->addAttributeMapping('wholesale_price', 'sc:wholesale_price_EUR:integer', false, true); //Price after discount in cents
		$this->addAttributeMapping('feature_imgurl', 'image_link', true, true); //Link to a main product image - Prefered image size is 1000x1000 pixels or larger. Only JPEG is supported
		$this->addAttributeMapping('additional_image_link', 'additional_image_link', true, false); //Links to additional product images separated with comma (","). Only JPEG is supported
		//$this->addAttributeMapping('ean', 'gtin', true, true); //Global Trade Item Number or EAN number
		$this->addAttributeMapping('', 'c:season_tag:string', true, false); //Tag defining seasonability of a product
		$this->addAttributeMapping('quantity', 'c:stock_level:integer', true, true); //Current stock level of the product
		$this->addAttributeMapping('item_group_id', 'c:style_id:string', true, false); //Product style id
		$this->addAttributeMapping('', 'c:title_DA:string', false, false); //Title of the product
		$this->addAttributeMapping('', 'c:title_SV:string', false, false); //Title of the product
		$this->addAttributeMapping('', 'c:title_NO:string', false, false); //Title of the product
		$this->addAttributeMapping('', 'c:title_NL:string', false, false); //Title of the product
		$this->addAttributeMapping('', 'c:category_DA:string', false, false);
		$this->addAttributeMapping('', 'c:category_SV:string', false, false);
		$this->addAttributeMapping('', 'c:category_NO:string', false, false);
		$this->addAttributeMapping('', 'c:category_NL:string', false, false);
		$this->addAttributeMapping('', 'c:description_DA:string', false, false); //Danish Title of the product 
		$this->addAttributeMapping('', 'c:description_SV:string', false, false); //Swedish Title of the product
		$this->addAttributeMapping('', 'c:description_NO:string', false, false); //Norwaign Title of the product
		$this->addAttributeMapping('', 'c:description_NL:string', false, false); //Dutch Title of the product
		$this->addAttributeMapping('', 'c:color_DA:string', false, false);
		$this->addAttributeMapping('', 'c:color_SV:string', false, false);
		$this->addAttributeMapping('', 'c:color_NO:string', false, false);
		$this->addAttributeMapping('', 'c:color_NL:string', false, false);
}

  function formatProduct($product) {
	 $product->attributes['feature_imgurl'] = str_replace('https://','http://',$product->attributes['feature_imgurl']);

	 $image_count = 1;
		if ( $this->allow_additional_images && (count($product->imgurls) > 1) ) {
			//max 11 additional images
			foreach($product->imgurls as $imgurl) {
	 			$product->attributes['additional_image_link'] = implode(',', $product->imgurls);	 	
				$image_count++;
				if ($image_count >= 11)
					break;
			}
		}
	  if ($product->attributes['stock_status'] == 1)
		  $product->attributes['stock_status'] = 'In Stock';
	  else
		  $product->attributes['stock_status'] = 'Out Of Stock';

	  //Allowed condition values: New, Open Box, OEM, Refurbished, Pre-Owned, Like New, Good, Very Good, Acceptable
	  return parent::formatProduct($product);
  }


}
