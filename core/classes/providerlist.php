<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/***************************************************
 * Cartproductfeed: List of feed providers
 * Copyright 2014 Purple Turtle Productions. All rights reserved.
 * license    GNU General Public License version 3 or later; see GPLv3.txt
 * 2014-11 by Keneto
 ***************************************************/
class PProviderList
{

    public $items = array();

    public function __construct()
    {

        global $pfcore;

        //***************************************************
        //Targetted Feeds
        //***************************************************

        $this->addProvider('Admarkt', 'Admarkt', 'xml');

        $this->addProvider('Google', 'Google Merchant Feed');
        $this->addProvider('Amazon', 'Amazon Product Ads', 'txt');
        $np = $this->addProvider('AmazonPAUK', 'Amazon Product Ads (UK)', 'txt');
        $np = $this->addProvider('AmazonSC', 'Amazon Seller Central', 'txt');
        $np->prettyName = 'Amazon Seller';
        $this->addProvider('eBaySeller', 'eBay Seller', 'csv');
        $this->addProvider('Miinto', 'Miinto', 'tsv');
        $this->addProvider('MiintoBrand', 'Miinto Brand', 'tsv');
        $this->addProvider('', '------'); //A gap for dialogfeedpage
        $this->addProvider('ElevenMain', '11 Main', 'csv');
        $this->addProvider('AffiliateWindow', 'Affiliate Window', 'csv');
        $this->addProvider('AffiliateWindowXML', 'Affiliate Window XML', 'xml');
        $this->addProvider('AmmoSeek', 'AmmoSeek');
        $this->addProvider('Become', 'Become Europe', 'csv');
        $this->addProvider('Bonanza', 'Bonanza', 'csv');
        $this->addProvider('Beslist', 'Beslist');
        $this->addProvider('Bing', 'Bing Ads', 'txt');
        $this->addProvider('eBay', 'eBayCommerceNetwork'); //xml
        $this->addProvider('FacebookXML', 'Facebook Catalog');
        $this->addProvider('GoDataFeed', 'GoDataFeedXML');
        $this->addProvider('GoDataFeedCSV', 'GoDataFeedCSV', 'csv');
        $this->addProvider('GPAnalysis', 'GPAnalysis');
        $this->addProvider('GraziaShop', 'GraziaShop', 'csv');
        $this->addProvider('HardwareInfo', 'HardwareInfo', 'csv');
        $this->addProvider('Houzz', 'Houzz', 'csv');
        $this->addProvider('Kelkoo', 'Kelkoo');
        $this->addProvider('Newegg', 'Newegg', 'csv');
        $this->addProvider('Nextag', 'Nextag', 'csv');
        $this->addProvider('Polyvore', 'Polyvore');
        $this->addProvider('Pricefalls', 'Pricefalls Feed', 'txt');
        $this->addProvider('PriceGrabber', 'PriceGrabber', 'csv');
        $this->addProvider('PriceRunner', 'PriceRunner', 'xml');
        $this->addProvider('Pronto', 'Pronto', 'txt');
        $this->addProvider('Rakuten', 'Rakuten Inventory Feed', 'txt');
        $this->addProvider('RakutenNewSku', 'Rakuten New SKU Feed', 'txt');
        $this->addProvider('RakutenUK', 'Rakuten UK', 'csv');
        $this->addProvider('ShareASale', 'ShareASale', 'csv');
        $this->addProvider('Shopzilla', 'Shopzilla', 'txt');
        $this->addProvider('Slickguns', 'Slickguns');
        $this->addProvider('Webgains', 'Webgains', 'csv');
        $this->addProvider('Winesearcher', 'Winesearcher', 'txt');
        $this->addProvider('Atterley', 'Atterley Product Feed', 'xml');
        $this->addProvider('Avantlink', 'Avantlink', 'xml');
        $this->addProvider('TradeTracker', 'TradeTracker', 'xml');

        //***************************************************
        //Generic Export Feeds
        //***************************************************
        $this->addProvider('', '------');
        $np = $this->addProvider('Productlistxml', 'Product List XML Export');
        $np->prettyName = 'XML Export';
        $np = $this->addProvider('Productlistcsv', 'Product List CSV Export', 'csv');
        $np->prettyName = 'CSV Export';
        $np = $this->addProvider('Productlisttxt', 'Product List TXT Export', 'txt');
        $np->prettyName = 'TXT Export';
        $np = $this->addProvider('Productlistraw', 'Product List RAW Export', 'txt');
        $np->prettyName = 'Raw Export';

        //***************************************************
        //Aggregate Feeds
        //***************************************************
        $this->addProvider('', '------');
        $np = $this->addProvider('AggXmlGoogle', 'Google Aggregate Feed', 'xml');
        $np->prettyName = 'Google Aggregate'; //name shown on Manage Feeds page
        //$np = $this->addProvider('AggXmlSlickguns', 'Slickguns Aggregate Feed', 'xml');
        //$np->prettyName = 'Slickguns Aggregate'; //name shown on Manage Feeds page
        $np = $this->addProvider('AggCsv', 'CSV Aggregate Feed', 'csv');
        $np->prettyName = 'CSV Aggregate';
        $np = $this->addProvider('AggTsv', 'TSV Aggregate Feed', 'tsv');
        $np->prettyName = 'TSV Aggregate';
        $np = $this->addProvider('AggTxt', 'TXT Aggregate Feed', 'txt');
        $np->prettyName = 'TXT Aggregate';
        $np = $this->addProvider('AggXml', 'XML Aggregate Feed', 'xml');
        $np->prettyName = 'XML Aggregate';        //$pfcore->triggerFilter('cpf_init_provider', $this);

    }

    public function addProvider($name, $description, $fileformat = 'xml')
    {
        $np = new stdClass();
        $np->name = $name;
        $np->prettyName = $name; //Used by ManageFeeds Page
        $np->description = $description;
        $np->fileformat = $fileformat;
        $this->items[] = $np;
        return $np;
    }

    public function asOptionList()
    {
        $output = '';
        foreach ($this->items as $item)
            $output .= '
						<option value="' . $item->name . '">' . $item->description . '</option>';
        return $output;
    }

    public function getExtensionByType($type)
    {
        //Used by ManageFeeds to create a filename
        foreach ($this->items as $provider)
            if ($provider->name == $type)
                return $provider->fileformat;
        return '';
    }

    public function getFileFormatByType($type)
    {
        //Used by ManageFeeds to create a filename
        foreach ($this->items as $provider)
            if ($provider->name == $type)
                return $provider->fileformat;
        return '';
    }

    public function getPrettyNameByType($type)
    {
        //Used by ManageFeeds to create a filename
        foreach ($this->items as $provider)
            if ($provider->name == $type)
                return $provider->prettyName;
        return '';
    }

}

?>