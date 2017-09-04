<?php global $pfcore; ?>
<script type="text/javascript">
jQuery( document ).ready(function() {		
		var shopID = jQuery("#edtRapidCartShop").val();
		if (shopID == null)
			shopID = "";
		var template = jQuery("#remote_category").val();
		if (template != null && template.length > 0) {
			jQuery.ajax({
				type: "post",
				url: ajaxhost + cmdFetchTemplateDetails,
				data: {shop_id: shopID, template: template, provider: "amazonsc"},
				success: function(res){
					jQuery("#attributeMappings").html(res);
				}
			});
		}
	});
</script>
<div class="attributes-mapping">
	<div id="poststuff">
		<div class="postbox">

			<!-- *************** 
					Page Header 
					****************** -->

			<h3 class="hndle"><?php echo $this->service_name_long; ?></h3>
			<div class="inside export-target">
				<!-- *************** 
						LEFT SIDE 
						****************** -->

				<!-- Attribute Mapping DropDowns -->
				<div class="feed-left" id="attributeMappings">
					<label for="categoryDisplayText">
					<br>Templates differ by region, please refer to the table below. <br>
					Type the template name in the field on the right.</label>
					<br/>
					<div class='amazon-template-table'>
					<table>
					<tr>
						<th>Region</th>
						<th>Template prefix</th>
						<th>Example</th>
					</tr>
					<tr>
						<td>US</td>
						<td>Type template as is</td>
						<td>Clothing</td>
					</tr>
					<tr>
						<td>UK / GB</td>
						<td>UK/</td>
						<td>UK/ Home</td>
					</tr>
					<tr>
						<td>Spain</td>
						<td>ES/</td>
						<td>ES/ Books</td>
					</tr>
					<tr>
						<td>France</td>
						<td>FR/</td>
						<td>FR/ Lighting</td>
					</tr>
					<tr>					
						<td>Germany</td>
						<td>DE/</td>
						<td>DE/ Sports</td>
					</tr>
					<tr>						
						<td>Italy</td>
						<td>IT/</td>
						<td>IT/ Luggage</td>
					</tr>
					</table>
					</div>
					<p>Example: To target the "Amazon UK Home template", type: UK/ Home<br>Template names are in English</p>
					<?php //echo $this->attributeMappings(); ?>
				</div>

				<!-- *************** 
						RIGHT SIDE 
						****************** -->

				<div class="feed-right" style="float:right">

					<!-- ROW 1: Local Categories -->
					<div class="feed-right-row">
						<span class="label"><?php echo $pfcore->cmsPluginName; ?> Category : </span>
						<?php echo $this->localCategoryList; ?>
					</div>

					<!-- ROW 2: Remote Categories -->
					<?php echo $this->line2(); ?>
					<div class="feed-right-row">
						<?php echo $this->categoryList($initial_remote_category); ?>
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

				<!-- *************** 
						FOOTER
						****************** -->

				<div id="cpf_advance_command_default" style="padding-left: 19px;">
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


				<div class="feed-advanced" id="feed-advanced">
					<textarea class="feed-advanced-text" id="feed-advanced-text"><?php echo $this->advancedSettings; ?></textarea>
					<?php echo $this->cbUnique; ?>
					<button class="navy_blue_button" id="bUpdateSetting" name="bUpdateSetting" onclick="doUpdateSetting('feed-advanced-text', 'cp_advancedFeedSetting-<?php echo $this->service_name; ?>'); return false;" >Update</button>
					<div id="updateSettingMessage">&nbsp;</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
	function toggleAdvanceCommandSection(event){
		var feed_config =jQuery("#cpf_custom_feed_config").css('display');
		var feed_config_button = jQuery("#cpf_feed_config_link");

		//First slideUp feed config section if displayed
		if(feed_config == "block"){
			jQuery("#cpf_custom_feed_config").slideUp();
			jQuery("#cpf_feed_config_desc").slideUp();
			jQuery(feed_config_button).attr('title' , 'This will open feed config section below.You can provide suffix and prefix for the attribute to be included in feed.');
			jQuery(feed_config_button).val('Show Feed Config');
		}

		var display =jQuery("#cpf_advance_section").css('display');
		if(display == 'none'){
			jQuery("#cpf_advance_section").slideDown();
			jQuery(event).val('Hide Advance Section');
			jQuery(event).attr('title' , 'Hide Feed config section');
			/* var divPosition = jQuery("#cpf_custom_feed_config").offset();
			 jQuery('#custom_feed_settingd').animate({scrollBottom: divPosition.top}, "slow");*/
		}
		if(display == 'block'){
			jQuery("#cpf_advance_section").slideUp();
			jQuery("#feed-advanced").slideUp();
			// jQuery("#bUpdateSetting").slideUp();
			jQuery(event).attr('title' , 'This will open feed advance command section where you can customize your feed using advanced command.');
			jQuery(event).val('Show advance Command section');
		}
	}

	function toggleAdvanceCommandSectionDefault(event){
		var display =jQuery("#cpf_advance_section_default").css('display');
		if(display == 'none'){
			jQuery("#cpf_advance_section_default").slideDown();
			jQuery(event).val('Hide Advance Section');
			jQuery(event).attr('title' , 'Hide Feed config section');
			/* var divPosition = jQuery("#cpf_custom_feed_config").offset();
			 jQuery('#custom_feed_settingd').animate({scrollBottom: divPosition.top}, "slow");*/
		}
		if(display == 'block'){
			jQuery("#cpf_advance_section_default").slideUp();
			jQuery("#feed-advanced-default").slideUp();
			// jQuery("#bUpdateSetting").slideUp();
			jQuery(event).attr('title' , 'This will open feed advance command section where you can customize your feed using advanced command.');
			jQuery(event).val('Show advance Command section');
		}
	}
</script>