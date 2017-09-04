<?php
/**
 * Created by PhpStorm.
 * User: subash
 * Date: 10/4/16
 * Time: 9:51 AM
 */

$miinto_country_array = array (
	array('' , 'Select Country'),
	array('DK' , 'Denmark'),
	array('SE' , 'Sweden'),
	array('NO' ,'Norway'),
	//array('ES' , 'Espain'), category tree not found
	array('NL' , 'Netherlands')
);

$country_arr = '';
//echo ("Code : " .$this->miinto_country_code);
$country_arr .= '<select name="cpf_miinto_feed_country_list" id="cpf_miinto_feed_country_list"  onchange="cpf_fetch_miinto_category(this)">';
//$country_arr .= '<option value="0"> Select Country </option>';
foreach ($miinto_country_array as $key => $value){
	if(isset($this->miinto_country_code) && $value[0] == $this->miinto_country_code)
		$selected = 'selected';
	else
		$selected = '';
	$country_arr .= '<option value="'.$value[0].'" '.$selected.'>'.$value[1].'</option>';
}
$country_arr .= '</select>';
$feed_id = isset($_GET['id']) ? isset($_GET['id']) : '';
if($feed_id){
	$style_1 = ' style = display:block ;';
}else{
	$style_1 = ' style = display:none ;';
}


?>

<div class="attributes-mapping">
	<div id="poststuff">
		<div class="postbox" style="width: 98%;">

			<!-- ***************
					Page Header
					****************** -->

			<div class="service_name_long hndle">
				<h2><?php echo $this->service_name_long; ?></h2>
				<a target="blank" title="Generate Merchant Feed"
				   href="http://www.exportfeed.com/documentation/generate-google-merchant-feed-woocommerce/">Generate
					your first feed</a> |
				<a target=\'_blank\' href=\'http://www.exportfeed.com/tos/\'>View guides</a>
			</div

				<!-- ***************
						LEFT SIDE
						****************** -->
				<!-- Attribute Mapping DropDowns -->
			<table>
				<tr>
					<td><h2>Please select country</h2></td>
					<td><?php echo $country_arr ;?></td>
				</tr>
			</table>

			<div class="feed-left" <?php echo $style_1; ?> id="cpf_feed_left_miinto">
				<?php echo $this->attributeMappings(); ?>
			</div>
			<!-- ***************
						RIGHT SIDE
						****************** -->

			<div class="feed-right" style="display : none; " id="cpf_feed_right_miinto">

				<!-- ROW 1: Local Categories -->
				<div class="feed-right-row">
					<span class="label"><?php echo $pfcore->cmsPluginName; ?> Category : </span>
					<?php echo $this->localCategoryList; ?>
				</div>

				<!-- ROW 2: Remote Categories -->
				<?php echo $this->line2(); ?>
				<div class="feed-right-row">
					<?php $this->service_name ; echo $this->categoryList($initial_remote_category); ?>
				</div>

				<!-- ROW 3: Filename -->
				<div class="feed-right-row">
					<span class="label">File name for feed : </span>
					<span ><input type="text" name="feed_filename" id="feed_filename_default" class="text_big" value="<?php echo $this->initial_filename; ?>" /></span>
				</div>
				<div class="feed-right-row">
					<label>* If you use an existing file name, the file will be overwritten.</label>
				</div>

				<!-- ROW 4: Get Feed Button -->
				<div class="feed-right-row">
					<input class="button-primary" type="button" onclick="doGetFeed('<?php echo $this->service_name; ?>' , this)" value="Get Feed" style="width:45%;" />
					<br/><br/>
					<div id="feed-message-display">&nbsp;</div>
					<div id="cpf_feed_view"></div>
					<div id="feed-error-display">&nbsp;</div>
					<div id="feed-status-display">&nbsp;</div>
				</div>
			</div>

			<!-- ***************
					Termination DIV
					****************** -->

			<div style="clear: both;">&nbsp;</div>
			<div id="cpf_advance_command_default" style="padding-left: 19px; display: none;">
				<span id="cpf_advance_command_settings">
						<a href="#cpf_advance_command_desc"><input class="button-primary" title="This will open advance command information." type="button" id="cpf_feed_config_link_default" value=" Show Advance Command Section" onclick="toggleAdvanceCommandSectionDefault(this);"></a>
				</span>
					<div id="cpf_advance_section_default" style="display: none;">
					<div class="advanced-section-description" id="advanced_section_description_default" style="padding-left: 17px;">
						<p>Advanced Commands grant you more control over your feeds. They provide a way to create your own attribute, map from non-standard ones or modify and delete feed data.</p>
						<ul style="list-style: inherit;">
							<li><a href="http://www.exportfeed.com/documentation/creating-attributes/#3_Creating_Defaults_using_Advanced_Commands">Creating Default Attributes with Advanced Commands</a></li>
							<li><a href="http://www.exportfeed.com/documentation/mapping-attributes/#3_Mapping_from_8216setAttributeDefault8217_Advanced_Commands">Mapping/Remapping with Advanced Commands</a></li>
							<li>Comprehensive Advanced Commands can be found here: <a title="mapping attributes - advanced commands" href="http://docs.shoppingcartproductfeed.com/AttributeMappingv3.1.pdf" target="_blank">More Advanced Commands</a> – *PDF</li>
						</ul>
					</div>
					<div>
						<label class="un_collapse_label" title="Click to open advance command field to customize your feed" ><input class="button-primary" type="button" id="toggleAdvancedSettingsButtonDefault" onclick="toggleAdvancedDialogDeafult();" value="Open Advanced Commands"/></label>
						<label class="un_collapse_label" title="This will erase your attribute mappings from the feed." id="erase_mappings_default" onclick="doEraseMappings('<?php echo $this->service_name; ?>')"><input class="button-primary" type="button" value="Reset Attribute Mappings"  /></label>
					</div>
				</div>
				<div class="feed-advanced" id="feed-advanced-default" >
					<textarea class="feed-advanced-text" id="feed-advanced-text-default"><?php echo $this->advancedSettings;?></textarea>
					<?php echo $this->cbUnique; ?>
					<input class="button-primary" type="button" id="bUpdateSettingDefault" name="bUpdateSettingDefault" title="Update Setting will update your feed data according to the advance command enter in advance command section." value="Update Settings" onclick="doUpdateSetting('feed-advanced-text-default', 'cp_advancedFeedSetting-<?php echo $this->service_name; ?>'); return false;"  />
					<div id="updateSettingMsg">&nbsp;</div>
				</div>
			</div>


			<div class="feed-advanced" id="feed-advanced" style="display : none; ">
				<textarea class="feed-advanced-text" id="feed-advanced-text"><?php echo $this->advancedSettings; ?></textarea>
				<?php echo $this->cbUnique; ?>
				<button class="navy_blue_button" id="bUpdateSetting" name="bUpdateSetting" onclick="doUpdateSetting('feed-advanced-text', 'cp_advancedFeedSetting-<?php echo $this->service_name; ?>'); return false;" >Update</button>
				<div id="updateSettingMessage">&nbsp;</div>
			</div>
		</div>
		</div>

</div>


<script>
	function cpf_fetch_miinto_category(selector){
		s = selector;
		cpf_miinto_country = jQuery(selector).val();
		//console.log(provider);
		var provider = jQuery('#selectFeedType').val();
		var cmdFetchMiintoCategory = "core/ajax/wp/fetch_miinto_category.php";
		var thisDate = new Date();
		feedIdentifier = thisDate.getTime();
		jQuery.ajax({
			type : 'POST',
			url  : ajaxhost + cmdFetchMiintoCategory ,
			data : {
					country_code : cpf_miinto_country ,
					feed_identifier: feedIdentifier ,
					provider : provider
			} ,
			success : function (res) {
				jQuery("#cpf_feed_left_miinto").show();
				//jQuery("#cpf_advance_command_default").show();
				jQuery("#cpf_feed_right_miinto").find("#categoryDisplayText").val('');
				jQuery("#cpf_feed_right_miinto").find("#categoryList").hide();
			}
		});
	}
	

</script>


