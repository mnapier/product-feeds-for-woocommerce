<?php
	//********************************************************************
	//UK Amazon Seller Template
	//2015-02
	//********************************************************************

/*** Basic ***/
	$this->addAttributeMapping('feed_product_type', 'feed_product_type',true,true)->localized_name = 'Product type';
	
/** Offer **/
	//Indicates how many items are in the package
	$this->addAttributeMapping('', 'item_package_quantity',true,false)->localized_name = 'Package Quantity'; 
	$this->addAttributeMapping('regular_price', 'standard_price',true,false)->localized_name = 'Standard Price';
	$this->addAttributeMapping('currency', 'currency',true,false)->localized_name = 'Currency';  //GBP
	$this->addAttributeMapping('quantity', 'quantity',true,false)->localized_name = 'Quantity';
	$this->addAttributeMapping('condition', 'condition_type',true,false)->localized_name = 'Condition Type'; 

/* Dimenstions*/

/*** Discovery ***/
/*** Some more preferred/optional attributes ***/
	$this->addAttributeMapping('', 'vintage',true,false)->localized_name = 'Vintage';  


?>