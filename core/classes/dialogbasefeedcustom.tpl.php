<?php
/**
 * Created by PhpStorm.
 * User: subash
 * Date: 9/16/16
 * Time: 2:29 PM
 */
?>
<div class="nav-wrapper">
            <nav class="nav-tab-wrapper">
                <span id="cpf-feeds_by_cats" class="nav-tab"> Feed By Category </span>
                <span id="cpf-custom-feed" class="nav-tab"> Custom Product Feed </span>
            </nav>
        </div>
        <div class="clear"></div>
<?php
$output = '';
$output .= '<div class="cpf-custom_feed_generation" id="cpf-custom_feed_generation" style="padding-left: 7px;">
					<form name = "cpf-custom_feed" id="cpf-custom-feed-form" method="POST">';
$output .= '<h3 class="heading">Search Product(s):</h3>
							<table class="form-table">
								<tbody>
									<tr valign="top">
										<th scope="row" class="titledesc"><label for="cpf_keywords-filter">Filter by Keywords:</label></th>
										<td class="forminp"><input type="search" id="cpf_keywords-filter" name="keywords_filter" placeholder="Type any Keywords" style="width:100%">
											<div class="cpf-suggestion-box"></div>
										</td>	
									</tr>
									<tr valign="top">
										<th scope="row" class="titledesc"><label for="cpf_category_filter">Search by Category:</label></th>
										<td class="forminp" id="cpf_localcategory_list"></td>
									</tr>
									<tr valign="top">	
										<th scope="row" class="titledesc"><label for="cpf_brand-filter">Search by Brand:</label></th>
										<td class="forminp">
											<input type="search" id="cpf_brand-filter" name="cpf_brand-filter" placeholder="Type any Brand" style="width:100%">
											<div class="cpf-suggestion-box"></div>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row" class="titledesc"><label for="cpf_sku_filter">Search by SKU:</label></th>
										<td class="forminp">
											<input type="search" id="cpf_sku_filter" name="cpf_sku_filter" placeholder="Type any SKU" style="width:100%">
											<div class="cpf-suggestion-box"></div>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row" class="titledesc"><label for="cpf_price_filter_option">Search by Price:</label></th>
										<td class="forminp">
										<select name = "cpf_price_filter_option" id= "cpf_price_filter_option" style="width:100%">
												<option value="">Select Price Range</option>
												<option value="less_than">Less than or equals to</option>
												<option value="more_than">Greater than or equals to</option>
												<option value="in_between">In Between</option>
										</select>
										<div id="cpf_price_selection_list_option" style="margin-top: 10px;">
										 <input type="search" name="cpf_price_filter_less_than" placeholder="Enter Amount" id="cpf_price_filter_less_than" style="display: none;"/>
										   <input type="search" name="cpf_price_filter_more_than" placeholder="Enter Amount" id="cpf_price_filter_more_than" style="display: none;" />
										    <input type="search" name="cpf_price_filter_in_between_first" placeholder="Enter First Amount" id="cpf_price_filter_in_between_first" style="display: none;"/>
										    <input type="search" name="cpf_price_filter_in_between_second" placeholder="Enter Second Amount" id="cpf_price_filter_in_between_second"style="display: none;" />
										</div>
									</tr>
									
									</tbody>
							</table>
								<p class="submit"><span class="spinner"></span> <input class="button-primary" title="This will search product list from above information you give and generate the result on search result section below." type="button" value="Search Product" id="submit_data" name="submit_data" onclick="submitForm(this,0);"style="float:right"/></p>
								<span class="cpf_search_info" style="float: right ;display: none;">Searching Product<span class="dot" style="font-weight: bold;padding-right: 25px;" > ....</span></span>
								<br/><br/>
						</form>
					<div class="clear"></div> ';
$output .= '<div id ="cpf-product-search-results">
   
<div class="wrap cpf-page">
    <div id="wrapperproductsearch">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="postbox-container-1" class="postbox-container" style="margin-right: -381px; ">
                <div id="side-sortables" class="meta-box" style="width: 558px;">
                    <!-- first sidebox -->
                    <div class="postbox" id="submitdiv">
                        <!--<div title="Click to toggle" class="handlediv"><br></div>-->
                        <h3><span>Selected Products: </span></h3>
                        <div class="inside">
                            <div id="submitpost" class="submitbox">
                                <table class="cp-list-table widefat fixed striped cpf-results" id="sort_1">
                                    <thead>
                                    <tr>
                                         <th style="width: 5%;padding-left: 2px;"><input type="checkbox" id="cpf_select_all_checkbox_1" onclick="selectAllProducts_1(this);"/></th>
                                         <th scope="col" id="product_name"
                                            class="manage-column column-details column-primary">Product Name
                                        </th>
                                        <th scope="col" id="local_category_heading" class="manage-column column-user_name">Local
                                            Category
                                        </th>
                                        <th scope="col" id="remote_category_heading" class="manage-column column-site">Merchant Category</th>
                                        <th style="width: 8%;"></th>
                                    </tr>
                                    </thead>
                                    <tbody id="cpf-the-list_1" data-cpf-lists="list:cpf-search-list"
                                           class="cpf-table-sortable ">
                                      <!--  <tr id="cpf-no-products">
                                            <td colspan="4">No product Selected.</td>
                                        </tr>-->
                                     </tbody>
                                     <tfoot>
                                        <tr>
                                            <td colspan="5" style="text-align: right;"><span class="delete-selected-products"><input class="button-primary" type="button"
                                                                  id="cpf_deleted_selected" value="Delete Selected" title="This will delete the product list from the feed."
                                                                onclick ="deletedSelected(this) ;" /></span>
                                                <span class="spinner"></span>
                                            <br/>
                                             <span id="cpf_deleted_selected_from_list" style="display: none;">Deleting product list....</span>
                                            </td>
                                           
                                        </tr>
                                    </tfoot>
                                    </table>
                            </div>
                        </div>
                    </div>';
$output .= '
                </div>
            </div> <!-- #postbox-container-1 -->
            <!-- #postbox-container-2 -->
            <div id="postbox-container-2" class="postbox-container" style="width: 555px;margin-bottom: 35px;">
                <div class="meta-box-sortables ui-sortable">
                    <div class="postbox">
                        <span class="cpf-text-info" style="display: none;">
					        <span class="dashicons dashicons-arrow-right"></span><span class="text_desc">Select the merchant product category.</span><br/>
                             <span class="dashicons dashicons-arrow-right"></span><span class="text_desc">Select the products to include in your feed and click on "move selected".</span><br/>
                        </span> 
                        <h3><span>Search Results: </span></h3>
					    <span class="move-search-products"><input class="button-primary" type="button" title="Click here to move the selected product list to SELECTED PRODUCTS section.SELECTED PRODUCT section prepare your search result to be included in your feed."
                                                                  id="cpf_move_selected" value="Move Selected"
                                                                style = "float:right;" onclick ="moveSelected(this) ;" />
                        <span class="spinner"></span>
                         </span>
                         
                     </div>
                    <table class="cpf-list-table widefat fixed striped cpf-results" id="cpf-sort" style="margin-top: -22px;">
                        <thead>
                        <tr>
                            <th style="width: 5%;padding-left: 2px;"><input type="checkbox" id="cpf_select_all_checkbox" onclick="selectAllProducts(this);" /></th>
                            <th scope="col" id="details" class="manage-column column-details column-primary">Product
                                Name
                            </th>
                            <th scope="col" id="user_name" class="manage-column column-user_name">Local Category</th>
                            <th scope="col" id="site" class="manage-column column-site" rowspan="2">Merchant Category</th>
                             <th style="width: 7%"></th>
                        </tr>
                        </thead>
                        <tbody id="cpf-the-list" data-cpf-lists="list:cpf-search-list" class="cpf-table-sortable">
                            <tr id="cpf-no-results">
                                <td colspan="5" >No Products Search.</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th style="width: 5%"></th>
                                <th scope="col" id="details" class="manage-column column-details column-primary">Product
                                    Name
                                </th>
                                <th scope="col" id="user_name" class="manage-column column-user_name">Local Category</th>
                                <th scope="col" id="site" class="manage-column column-site">Remote Category</th>
                                <th style="width: 7%"></th>
                            </tr>
                          
                        </tfoot>
                    </table>
                    <div class="tablenav bottom" style="padding-bottom: 42px; display:none;">
                         <div class="alignleft actions bulkactions">
                         <div id="cpf_error_message_action"></div>
                            <table>
                                <tbody>
                                   <tr>
                                        <td style="width: 15%;">
                                            <label for="bulk-action-selector-bottom" class="screen-reader-text">Select bulk action</label>
                                            <select name="action2" id="bulk-action-selector-bottom" onchange="bulk_action_selector(this);">
                                                <option value="-1">Bulk Actions</option>
                                                <option value="assignCategory" class="hide-if-no-js">Assign Category</option>
                                                <option value="trash">Delete</option>
                                            </select>
                                        </td>
                                        <td id="cpf_bulk_action_list" style="display:none;">
                                         <span>
                                            <input type="search" name="categoryDisplayText" class="text_big" id="categoryDisplayText" onkeyup="doFetchCategory_timed_custom('."'{$this->service_name}'".',this)" value="" autocomplete="off" placeholder="Start typing merchant category..." style="width: 100%;"></span>
                                        </span>   
                                            <div class="categoryList"></div>
                                        </td>
                                        <td class="apply_btn">
                                            <input type="submit" id="doaction2" class="button-primary action" value="Apply" onclick="cpf_apply_action(this)">
                                        </td>
                                   </tr>
                                   <tr>
                                        <td colspan="3"></td>
                                   </tr>
                                   <tr>
                                        <td colspan="3">*You can now assign merchant category at once to different products.Please select the <b>"Assign Category"</b> from above select list ,choose merchant category and click apply.</td>
                                   </tr>
                                 </tbody>
                            </table> 
                         </div>
                    </div>
                 </div> <!-- .meta-box-sortables -->
            </div> <!-- #postbox-container-1 -->
        </div> <!-- #post-body -->
        <br class="clear">
    </div> <!-- #poststuff -->
    </div>
</div>
<div class="clear"></div>
        <!-- #Feed Config !-->
        ';

$this->loadFeedConfig();
$output .='<span id="custom_feed_settingd"><a href="#cpf_custom_feed_config"><input class="button-primary" title="This will open feed config section below.You can provide suffix and prefix for the attribute to be included in feed." type="button" id="cpf_feed_config_link" value="Show Feed Config" onclick="toggleFeedSettings();"></a></span>
                            <span id="custom_feed_advance_section">
						<a href="#cpf_advance_command_desc"><input class="button-primary" title="This will open advance command information." type="button" id="cpf_advance_section_link" value=" Show Advance Command Section" onclick="toggleAdvanceCommandSection(this);"></a>
					</span>
                            <div class="postbox" id="cpf_feed_config_desc" style="display:none;">
                                <ul style="list-style-type:disc;padding-left: 24px;">
                                    <b> Now you can set up feed with default merchant attributes or with your custom value.</b></br>
                                   <li> Select merchant attributes.</li> 
                                        <b> For eg: g:brand </b><br/><br/>
                                   <li> Map attributes with merchant attributes or your custom value.</li>
                                        <b> For eg: Choose custom value </b> </br/><br/>
                                   <li> Enter your custom brand title.</li>
                                         <b> For eg: TestBrand </b><br/> <br/>
                                   <li> Add prefix to your custom brand name.</li>
                                         <b> For eg: p_</b> <br/><br/>
                                   <li> Add Suffix to your custom brand name.</li>
                                          <b> For eg: _s </b> <br/><br/>
                                   <li> Output limit helps to limit your product list on feed.</li>
                                         <b> For eg: 0-100 This will display 100 product list on your feed.</b> <br/>
                                 </ul>   
                            </div>                       
                       ';

$output.='<div id="cpf_custom_feed_config" class="cpf_custom_feed_config" style="display: none;">
            <div class="postbox" style="overflow: hidden;box-sizing: border-box;">
                <h3 style="float: left;"><span>Feed Config: </span></h3>
                <span class="update_cpf_config" style="float: right;padding-top: 16px;">
                <button class="button-primary" id="bFeedSetting" name="bFeedSetting" onclick="doCustomFeedSetting(this);" title="This will update your feed config with the data that you enter in feed config section."> Update</button>
                <input type=hidden name="cpf_custom_merchant_type" value="cp_advancedFeedSetting-'.$this->service_name.'" />
					<div id="updateCustomSettingMessage">&nbsp;</div><span class="spinner"></span>
                </span>
		    </div>
            <table class="cpf-list-table widefat fixed striped cpf-results" id="cpf-sort_config" style="margin-top: -22px;">
                <thead>
                <tr>
                    <th scope="col" style="text-align: center">'.($this->provider->providerName).' Attributes</pre></th>
                    <th scope="col" style="text-align: center">Type</th>
                    <th scope="col" style="text-align: center">Value</th>
                     <th scope="col" style="text-align: center">Prefix</th>
                    <th scope="col" style="text-align: center">Suffix</th>
                    <th scope="col" style="text-align: center">Output limit</th>
                    <th scope="col" style="width: 5%;text-align: center"></th>
                    <th scope="col" style="width: 5%;text-align: center"></th>
                </tr>
                </thead>
                <tbody id="cpf_custom_feed_config_body">';

$output .= '  <tr>
                    <td style="text-align: center;width:100%">' . $this->merchantAttr($defaultValue=''). '</td>
                    <td style="text-align: center">';

$output .= '<select name="cpf_type " class="cpf_change_type" onchange="cpf_changeType(this);"><option value="0" >Attributes</option><option value="1" >Custom Value</option></select></td>
                    <td style="text-align: center" id="cpf_attrdropdownlist" class="cpf_value_td">
                        <span class="cpf_default_attributes" >' /*. $this->createDropdownAttrCustom($FoundAttributes, $defaultValue, $thisAttributeMapping->mapTo)*/ . '</span>
                        <span class="cpf_custom_value_span" ><input type="text" style="display:none;" class="cpf_custom_value_attr" value= "" name="cpf_custom_value" style="width:100%" placeholder="Enter Custom Value"/></span>
                    </td>
                     <td style="text-align: center"><input type="text" class="cpf_prefix" name="cpf_prefix" style="width:100%" value= "" placeholder="Enter Prefix"/></td>
                    <td style="text-align: center"><input type="text" class="cpf_suffix" name="cpf_suffix" style="width:100%" value= "" placeholder="Enter Suffix"/></td>
                    <td style="text-align: center"><input type="text" id="cpf_feed_output_limit" name="cpf_feed_output_limit" style="width:100%" placeholder="Limit your feed list"/></td>
                    <td style="width: 5%;text-align: center"><span class="dashicons dashicons-plus" onclick="addRows(this);" title="Add rows."></span></td>
                    <td style="width: 5%;text-align: center"><span class="dashicons dashicons-trash" onclick="removeRows(this); title="Delete this rows."></span></td>
                </tr>';


$output .= '</tbody>
            </table>
         </div>  <!--#cpf_custom_feed_config -->
         <div id="cpf_advance_command">
			<div id="cpf_advance_section" style="display: none;">
                        <div class="advanced-section-description" id="advanced_section_description" style="padding-left: 17px;">
                        <p>Advanced Commands grant you more control over your feeds. They provide a way to create your own attribute, map from non-standard ones or modify and delete feed data.</p>
                        <ul style="list-style: inherit;">
                            <li><a href="http://www.exportfeed.com/documentation/creating-attributes/#3_Creating_Defaults_using_Advanced_Commands" target="_blank">Creating Default Attributes with Advanced Commands</a></li>
                            <li><a href="http://www.exportfeed.com/documentation/mapping-attributes/#3_Mapping_from_8216setAttributeDefault8217_Advanced_Commands" target="_blank">Mapping/Remapping with Advanced Commands</a></li>
                            <li>Comprehensive Advanced Commands can be found here: <a title="mapping attributes - advanced commands" href="http://docs.shoppingcartproductfeed.com/AttributeMappingv3.1.pdf" target="_blank">More Advanced Commands</a> – *PDF</li>
                        </ul>
                    </div>
                        <div>
					    <label class="un_collapse_label" title="Click to open advance command field to customize your feed" ><input class="button-primary" type="button" id="toggleAdvancedSettingsButton" onclick="toggleAdvancedDialog()" value="Open Advanced Commands"/></label>
					    <label class="un_collapse_label" title="This will erase your attribute mappings from the feed." id="erase_mappings" onclick="doEraseMappings('."'{$this->service_name}'".')"><input class="button-primary" type="button" value="Reset Attribute Mappings"  /></label>
				    </div>
                    </div>    
                    <div class="feed-advanced" id="feed-advanced" >
					    <textarea class="feed-advanced-text" id="feed-advanced-text">'."{$this->advancedSettings}".'</textarea>
					    '."{$this->cbUnique}".' 
					    <input class="button-primary" type="button" id="bUpdateSetting" name="bUpdateSetting" title="Update Setting will update your feed data according to the advance command enter in advance command section." value="Update Settings" onclick="doUpdateSetting(\'feed-advanced-text\', \'cp_advancedFeedSetting-'."{$this->service_name}".'\'); return false;"  />
                        <div id="updateSettingMessage">&nbsp;</div>
				    </div>
                </div>
         <table  id="cpf-generate-table" style="float: right;padding-top: 70px;">
            <tbody>
                <tr valign="top">
                    <th style="line-height: 2em;" scope="row" class="titledesc"><label for="feed_filename">File name for feed :</label><span style="padding : 8px 0 0 3px" class="cpf-help-tip" title="Enter the file name for the feed."></span></th>
                    <td class="forminp">
                        <input type="search" style="width:100%" name="feed_filename" id="feed_filename" class="text_big" value="'."$this->initial_filename".'" autocomplete="off" placeholder="Enter file name for feed you want to create">
                    </td>
                </tr>
                <tr>
                <th></th>
                    <td><b>*Feed will open in new window. If not disable the popup blocker.</b></td>
                </tr>
                 <tr>
                <th></th>
                    <td><b>*If you use an existing file name, the file will be overwritten.</b></td>
                </tr>
                <tr>
                <th></th>
                    <td style="text-align:right;">    
                        <div class="feed-right-row">
                            <span class="spinner" style="float: left;margin-left: 109px;""></span>
                            <input class="button-primary" type="button" id="cpf_get_custom_feed" onclick="doGetCustomFeed('."'{$this->service_name}'".' , this)" value="Get Feed" style="width:65%">
                             <br/><br/>
                             <div id="feed-message-display">&nbsp;</div>
                             <div id="cpf_feed_view"></div>
                             <div id="feed-error-display">&nbsp;</div>
                             <div id="feed-status-display">&nbsp;</div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

</div>';
echo  $output;