<?php global $pfcore; ?>
<div class="attributes-mapping">
	<div id="poststuff">
		<div class="postbox" style="width: 98%;">

			<!-- *************** 
					Page Header 
					****************** -->
			<div class= "service_name_long hndle">
				<h2><?php echo $this->service_name_long; ?></h2>
				<a target="blank" title="Generate Merchant Feed" href="http://www.exportfeed.com/documentation/generate-google-merchant-feed-woocommerce/">Generate your first feed</a> |
				<a target=\'_blank\' href=\'http://www.exportfeed.com/tos/\' >View guides</a>
			</div>
			<div class="inside export-target">

				<!-- *************** 
						LEFT SIDE 
						****************** -->

				<!-- Attribute Mapping DropDowns -->
				<div class="feed-left">
					<?php echo $this->attributeMappings(); ?>
				</div>

				<!-- *************** 
						RIGHT SIDE 
						****************** -->

				<div class="feed-right">

					<!-- ROW 1: Local Categories -->
					<div class="feed-right-row">
						<span class="label"><?php echo $pfcore->cmsPluginName; ?> Category : </span>
						<?php echo $this->localCategoryList; ?>
					</div>

					<!-- ROW 2: Remote Categories -->
					<?php echo $this->line2(); ?>
					<div class="feed-right-row">
						<?php $this->service_name; echo $this->categoryList($initial_remote_category); ?>
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
						UTM Tracking Codes
						****************** 
				<h2>UTM Tracking</h2>
				<div>
					<span class="label">Visitor Tracking Method : </span>
					<span >
						<select class="attribute_select">
							<option></option>
							<option>Visitor Tracking Method #1</option>
						</select>
					</span>
				</div>
				<div>
					<span class="label">Session ID Identification : </span>
					<span ><input name="edtPassword" id="edtPassword" class="text_big" /></span>
				</div>
				<div>
					<span class="label">Tracking Field : </span>
					<span ><input class="text_big" /></span>
				</div>
				-->

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