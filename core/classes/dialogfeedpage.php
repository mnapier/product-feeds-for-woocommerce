<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/********************************************************************
 * Version 2.1
 * For the main feed page
 * Copyright 2014 Purple Turtle Productions. All rights reserved.
 * license    GNU General Public License version 3 or later; see GPLv3.txt
 * By: Keneto 2014-05-05
 ********************************************************************/
class PFeedPageDialogs
{

    public static function pageHeader()
    {

        global $pfcore;

        $gap = '
			<div style="float:left; width: 50px;">
				&nbsp;
			</div>';
        $style_lic_text = '';
        if ($pfcore->cmsName == 'WordPress') {
            $lic = '<div style="position:absolute; left:300px; top:60px">
                 <a class="button-primary" type="submit" value="" id="submit" name="submit" href="http://www.exportfeed.com/support/" target="_blank">Thank You For Supporting The Project</a>
                    </div>';
            $style_lic_text = "display:none";
        } else
            $lic = '';
        $providers = new PProviderList();
        if ($_GET['page'] == 'eBay_settings_tabs') {
            $style = 'display : none';
        } else {
            $style = 'display : block';
        }
        $output = '
			<div class="postbox" style="width:100%;">
				<div class="inside-export-target">
					<div style = "' . $style . '">
						<h4>Select Merchant Type</h4>
						<select id="selectFeedType" onchange="doSelectFeed();">
						<option></option>' .
            $providers->asOptionList() . '
						</select>
						<br>
						<ul class="subsubsub" >
						<li><a href= "http://www.exportfeed.com/supported-merchants/" class="support-channel-list">List of our support channels</a></li>
					</div>				
					' . $lic . '
					<ul class="subsubsub license-key-text" style="' . $style_lic_text . '">
						<li><span class="license-key-info">Include all text and generated numeric value </span><a href="http://www.exportfeed.com/woocommerce-product-feed/">Get License Key</a>
					</ul>
				</div>
				
			</ul>
			</div>
			
			<div class="clear"></div>';

        return $output;

    }

    public static function pageBody()
    {
        $output = '

	  <div id="feedPageBody" class="postbox" style="width: 100%;float: left;">
	    <div class="inside export-target">
	      <h4>Select a merchant type.</h4>
		  <hr />
		</div>
	  </div>
	  ';
        return $output;
    }

}

?>