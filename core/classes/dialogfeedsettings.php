<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/********************************************************************
 * Version 2.0
 * Settings for feeds
 * Copyright 2014 Purple Turtle Productions. All rights reserved.
 * license    GNU General Public License version 3 or later; see GPLv3.txt
 * By: Keneto 2014-05-05
 * Note: Joomla version does not use this part of the core... it defines its own component screens
 ********************************************************************/
class PFeedSettingsDialogs
{

    static function formatIntervalOption($value, $descriptor, $current_delay)
    {
        $selected = '';
        if ($value == $current_delay) {
            $selected = ' selected="selected"';
        }
        return '<option value="' . $value . '"' . $selected . '>' . $descriptor . '</option>';
    }

    static function fetchRefreshIntervalSelect()
    {
        $current_delay = get_option('cp_feed_delay');
        return '
					<select name="delay" class="select_medium" id="selectDelay" style="width: 81%">' . "\r\n" .
        PFeedSettingsDialogs::formatIntervalOption(604800, '1 Week', $current_delay) . "\r\n" .
        PFeedSettingsDialogs::formatIntervalOption(86400, '24 Hours', $current_delay) . "\r\n" .
        PFeedSettingsDialogs::formatIntervalOption(43200, '12 Hours', $current_delay) . "\r\n" .
        PFeedSettingsDialogs::formatIntervalOption(21600, '6 Hours', $current_delay) . "\r\n" .
        PFeedSettingsDialogs::formatIntervalOption(3600, '1 Hour', $current_delay) . "\r\n" .
        PFeedSettingsDialogs::formatIntervalOption(900, '15 Minutes', $current_delay) . "\r\n" .
        PFeedSettingsDialogs::formatIntervalOption(300, '5 Minutes', $current_delay) . "\r\n" . '
					</select>';
    }

    public static function refreshTimeOutDialog()
    {
        global $wpdb;
        return '
		  <div id="poststuff">
			  <div id="post-body" class="metabox-holder columns-2">
			  	<div id="postbox-container-1" class="postbox-container">
			  		<div class="postbox">
			  		<h3 class="hndle"></h3>
						<div class="inside export-target">
							<span class="dashicons dashicons-arrow-right"></span><b>Cart product feed will automatically update your feed after the interval set here.</b>
							<br/><br/>
							<span class="dashicons dashicons-arrow-right"></span><b>Your feed will be update automatically after <span id="set_interval_time"></span>.</b>
						</div>
					</div>
			  	</div>
			  	<div id="postbox-container-2" class="postbox-container">
					<div class="postbox">
						<h3 class="hndle">Interval at which feed auto-refreshes</h3>
						<div class="inside export-target">
							<table class="form-table">
								<tbody>
									<tr>
										<th style="width:5%"><label>Interval:</label></th>
										<td style="width:59%;">' . PFeedSettingsDialogs::fetchRefreshIntervalSelect() . '</td>
										<td><input class="button-primary" style="float:right;" type="submit" value="Update Interval" id="submit" name="submit" onclick="doUpdateSetting(\'selectDelay\', \'cp_feed_delay\')"><div id="updateSettingMessage"></div></td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>	
			   </div>	
		  </div> <div class="clear"></div>';

    }

    public static function filterProductDialog()
    {
        global $wpdb;
        return '
      <div id="cpf_filter_poststuff">
	    <div class="postbox">
		  <h3 class="hndle" style="font-size: 14px;padding-left: 13px;">Select Feed type you want to display.</h3>
		  <div class="inside export-target" style="padding-left: 9px;">
		  <label for="cpf_filter_product_feed"><b>Feed Type:</b></label>
		  <select name="cpf_filter_product_feed" id="cpf_filter_product_feed" style="width: 22%;margin-left: 9px;">
		                <option value="0">Select Feed Type</option>
		                <option value="1">Custom product feed</option>
		                <option value="2">Feed by Category</option>
                </select>
                <span class="spinner" style="float: none;"></span>
		  </div>
		 </div>
	  </div>';
    }
}

?>
<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery("#cpf_filter_product_feed").change(function () {
            jQuery('#cpf_filter_poststuff').find('.spinner').css('visibility', 'visible');
            var feed_type = jQuery("#cpf_filter_product_feed").val();
            jQuery.ajax({
                type: "POST",
                url: "<?php echo CPF_URL . "core/ajax/wp/fetch_feed_table.php" ?>",
                data: {feed_type: feed_type},
                success: function (res) {
                    jQuery('#cpf_filter_poststuff').find('.spinner').css('visibility', 'hidden');
                    jQuery("#cpf_manage_table_originals").html(res);
                }
            });
        });

        jQuery("#set_interval_time").html(jQuery("#selectDelay option:selected").html());

    });

</script>