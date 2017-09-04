<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
  /********************************************************************
  Version 3.0
    Export an PriceRunner XML data feed
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Calv 2014-11-12
  ********************************************************************/

class PriceRunnerDlg extends PBaseFeedDialog {

	function __construct() {
		parent::__construct();
		$this->service_name = 'PriceRunner';
		$this->service_name_long = 'PriceRunner XML Feed';
		$this->blockCategoryList = false;
		$this->options = array(	);
	}

}
