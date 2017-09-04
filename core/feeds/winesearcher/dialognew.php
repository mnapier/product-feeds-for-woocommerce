<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
  /********************************************************************
  Version 2.0
    Front Page Dialog for GoDataFeed
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2015-12

  ********************************************************************/

class WinesearcherDlg extends PBaseFeedDialog {

	function __construct() {
		parent::__construct();
		$this->service_name = 'Winesearcher';
		$this->service_name_long = 'Winesearcher XML Feed';
		$this->blockCategoryList = true;
	}

}
