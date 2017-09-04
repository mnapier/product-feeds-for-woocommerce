<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
  /********************************************************************
  Version 3.0
    Export a 11Main CSV data feed
	  Copyright 2015 Export Feed. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Calv 2015-20-08
  ********************************************************************/

class ElevenMainDlg extends PBaseFeedDialog {

	function __construct() {
		parent::__construct();
		$this->service_name = 'ElevenMain'; //must match folder and class name
		$this->service_name_long = '11 Main CSV Feed'; //shown on front end
		$this->blockCategoryList = true;
		$this->options = array(	);
	}

}
