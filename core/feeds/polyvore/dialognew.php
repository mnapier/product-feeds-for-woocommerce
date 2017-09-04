<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
  /********************************************************************
  Version 2.0
    Front Page Dialog for Polyvore
	  Copyright 2015 Shopping Cart Product Feed / Export Feed. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Calvin 2015-09-09

  ********************************************************************/

class PolyvoreDlg extends PBaseFeedDialog {

	function __construct() {
		parent::__construct();
		$this->service_name = 'Polyvore';
		$this->service_name_long = 'Polyvore Feed (via Google Feed specs)';
	}

	function convert_option($option) {
		return strtolower(str_replace(" ", "_", $option));
	}

}
