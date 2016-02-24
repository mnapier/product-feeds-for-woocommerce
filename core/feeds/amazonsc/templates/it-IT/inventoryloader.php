<?php
	//********************************************************************
	//Amazon Seller InventoryLoader
	//2015-03
	//********************************************************************

	$this->addAttributeMapping('sku', 'sku', false, true);
	$this->addAttributeMapping('', 'product-id', false, false);
	//$this->addAttributeMapping('product-id-type', 'product-id-type'); //added automatically
	$this->addAttributeMapping('price', 'price', false, false);
	$this->addAttributeMapping('', 'minimum-seller-allowed-price', false, false);
	$this->addAttributeMapping('', 'maximum-seller-allowed-price', false, false);
	$this->addAttributeMapping('condition', 'item-condition', false, false);
	$this->addAttributeMapping('quantity', 'quantity', false, false);
	$this->addAttributeMapping('add_delete', 'add-delete', false, false);
	$this->addAttributeMapping('', 'item-note', false, false);
	$this->addAttributeMapping('', 'expedited-shipping', false, false);
	$this->addAttributeMapping('', 'will-ship-internationally', false, false);
	$this->addAttributeMapping('', 'fulfillment-center-id', false, false);
	//$this->addAttributeMapping('standard-plus', 'standard-plus', false, false);
	//$this->addAttributeMapping('product-tax-code', 'product-tax-code', false, false);
	//$this->addAttributeMapping('leadtime-to-ship', 'leadtime-to-ship', false, false);
?>