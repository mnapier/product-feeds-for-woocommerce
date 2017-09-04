<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/********************************************************************
 * Version 2.0
 * Core functionality of a basic feed.
 * Copyright 2014 Purple Turtle Productions. All rights reserved.
 * license    GNU General Public License version 3 or later; see GPLv3.txt
 * By: Keneto 2014-05-05
 * 2014-10 Moved to template (.tpl) format for simplicity (hopefully) -Keneto
 ********************************************************************/

require_once dirname(__FILE__) . '/../data/productcategories.php';
require_once dirname(__FILE__) . '/../data/attributesfound.php';
require_once dirname(__FILE__) . '/../data/feedfolders.php';

//echo admin_url().'includes/class-wp-list-table.php';


class PBaseFeedDialog
{

    public $blockCategoryList = false;
    public $options; //Array to be filled by constructor of descendant
    public $service_name = 'Google'; //Example only
    public $service_name_long = 'Google Products XML Export'; //Example only

    function __construct()
    {
        $this->options = array();
    }

    function createDropdown($thisAttribute, $index)
    {
        $found_options = new FoundOptions($this->service_name, $thisAttribute);
        $output = '
	<select class="attribute_select" id="attribute_select' . $index . '" onchange="setAttributeOption(\'' . $this->service_name . '\', \'' . $thisAttribute . '\', ' . $index . ')">
	  <option value=""></option>';
        foreach ($this->options as $option) {
            if ($option == $found_options->option_value)
                $selected = 'selected="selected"';
            else
                $selected = '';
            $output .= '<option value="' . $this->convert_option($option) . '"' . $selected . '>' . $option . '</option>';
        }
        $output .= '
	</select>';
        return $output;
    }

    function createDropdownAttr($FoundAttributes, $defaultValue = '', $mapTo)
    {
        $output = '
	<select class="attribute_select" service_name="' . $this->service_name . '"
		mapto="' . $mapTo . '"
		onchange="setAttributeOptionV2(this)" >
	  <option value=""></option>
	  <option value="(Reset)">(Reset)</option>';
        foreach ($FoundAttributes->attributes as $attr) {
            if ($defaultValue == $attr->attribute_name)
                $selected = ' selected="true"';
            else
                $selected = '';
            $output .= '<option value="' . $attr->attribute_name . '"' . $selected . '>' . $attr->attribute_name . '</option>';
        }
        $output .= '
		<option value="">--Common attributes--</option>
		<option value="brand">brand</option>
		<option value="description_short">description_short</option>
		<option value="id">id</option>
		<option value="regular_price">regular_price</option>
		<option value="sale_price">sale_price</option>
		<option value="sku">sku</option>
		<option value="tag">tag</option>
		<option value="title">title</option>		
		<option value="">--CPF Additional Fields--</option>
		<option value="brand">brand</option>
		<option value="ean">ean</option>
		<option value="mpn">mpn</option>
		<option value="upc">upc</option>
		<option value="description">description</option>
		<option value="">--Dummy attributes--</option>
		<option value="default1">default1</option>
		<option value="default2">default2</option>
		<option value="default3">default3</option>	
	</select>';
        return $output;
    }

    function attributeMappings()
    {

        global $pfcore;
        $FoundAttributes = new FoundAttribute();
        $savedAttributes = $FoundAttributes->attributes;
        $FoundAttributes->attributes = array();
        foreach ($savedAttributes as $attr)
            $FoundAttributes->attributes[] = $attr;

        foreach ($this->provider->attributeMappings as $thisAttributeMapping) {
            //if empty mapping, don't add to drop down list
            if (strlen(trim($thisAttributeMapping->attributeName)) > 0) {
                $attr = new stdClass();
                $attr->attribute_name = $thisAttributeMapping->attributeName;
                $FoundAttributes->attributes[] = $attr;
            }
        }

        /*
        //patch: for google feed, ram the brand in
        if ($this->service_name == 'Google') {
            $has_brand = false;
            foreach($FoundAttributes->attributes as $attr)
                if (strtolower($attr->attribute_name) == 'brand') {
                    $has_brand = true;
                    break;
                }
            if (!$has_brand) {
                $thisAttribute = new stdClass();
                $thisAttribute->attribute_name = 'brand';
                $FoundAttributes->attributes[] = $thisAttribute;
            }
        }
        */
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : "feed_by_cats";
        ?>
        <div class="nav-wrapper">
            <nav class="nav-tab-wrapper">
                <span id="cpf-feeds_by_cats"
                      class="nav-tab <?php echo ($active_tab == 'feed_by_cats') ? 'nav-tab-active' : ''; ?>"> Feed By Category </span>
                <span id="cpf-custom-feed"
                      class="nav-tab <?php echo ($active_tab == 'customfeed') ? 'nav-tab-active' : ''; ?> "> Custom Product Feeed </span>
            </nav>
        </div>
        <div class="clear"></div>
        <style>
            #filters_results {
                float: left;
                list-style: none;
                margin: 0;
                padding: 0;
                width: 190px;
            }

            #filters_results li {
                padding: 8px;
                background: #FAFAFA;
                border-bottom: #F0F0F0 1px solid;
            }

            #filters_results li:hover {
                background: #F0F0F0;
            }

            #cpf_keywords-filter {
                border: #F0F0F0 1px solid;
            }
            tr#selected_product_rows td:nth-child(4) {
                display: none;
            }
            tr#selected_product_rows td.cpf-selected-parent {
                display: none;
            }
            tr#selected_product_rows input[type='checkbox'] {
                display: none;
            }


        </style>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                console.log(jQuery(".chosen-select"));
                jQuery("#cpf_feeds_by_category").hide();
                jQuery(".feed-right").hide();
                jQuery("#cpf-feeds_by_cats").click(function () {
                    jQuery("#cpf_feeds_by_category").show();
                    jQuery(".feed-right").show();
                    jQuery("#cpf-custom_feed_generation").hide();
                });

                jQuery("#cpf-custom-feed").click(function () {
                    jQuery("#cpf_feeds_by_category").hide();
                    jQuery(".feed-right").hide();
                    jQuery("#cpf-custom_feed_generation").show();
                });
            });
            jQuery(document).ready(function () {


                jQuery('#cpf_keywords-filter,#cpf_brand-filter,#cpf_sku_filter').keyup(function (e) {
                    e.preventDefault();
                    window.suggestion_box = (jQuery(this).parent().find(".cpf-suggestion-box"));
                    window.$_this = jQuery(this);
                    var searchterm = jQuery($_this).val();
                    var searchfilters = '';

                    if (jQuery($_this).attr('id') == 'cpf_keywords-filter') {
                        searchfilters = "all";
                    }
                    if (jQuery($_this).attr('id') == 'cpf_brand-filter') {
                        searchfilters = 'brand';
                    }

                    if (jQuery($_this).attr('id') == 'cpf_sku_filter') {
                        searchfilters = 'sku';
                    }

                    if (searchterm.length >= 3) {
                        jQuery.ajax({
                            type: "POST",
                            url: "<?php echo CPF_URL . "core/ajax/wp/fetch_product_ajax.php?q=ajax" ?>",
                            data: {keyword: searchterm, searchfilters: searchfilters},
                            /*beforeSend: function(){
                             jQuery("#cpf_keywords-filter").css("background","#FFF url(images/loading.gif) no-repeat 165px");
                             },*/
                            success: function (data) {
                                jQuery(suggestion_box).show();
                                jQuery(suggestion_box).html(data);
                            }
                        });
                    }
                });
                jQuery("#cpf_keywords-filter,#cpf_brand-filter,#cpf_sku_filter").on('search', function (e) {
                    e.preventDefault();
                    if (jQuery(this).val() == '') {
                        jQuery(suggestion_box).hide();
                    }
                });

                jQuery("#categoryDisplayText").on('search', function (e) {
                    e.preventDefault();
                    jQuery("#categoryList").hide();
                });
            });

            jQuery("#cpf-custom-feed-form").submit(function (event) {
                 event.preventDefault();
                var keywords = jQuery("#cpf_keywords-filter").val();
                var category = jQuery("#cpf_locacategories_filter").val();
                var brand = jQuery("#cpf_brand-filter").val();
                var sku = jQuery("#cpf_sku_filter").val();
                var price_range = jQuery("#cpf_price_filter").val();
                var merchat_type = jQuery("#selectFeedType").val();
                jQuery("#cpf-no-products-search").remove();
                jQuery.ajax({
                    type: "POST",
                    url: "<?php echo CPF_URL . "core/ajax/wp/fetch_product_ajax.php?q=search" ?>",
                    data: {
                        keywords: keywords,
                        category: category,
                        brand: brand,
                        sku: sku,
                        price_range: price_range,
                        merchat_type: merchat_type
                    },
                     success: function (data) {
                          var $_tr = jQuery("#cpf-the-list");
                        jQuery("#cpf-no-results").remove();
                        var html_1 = $_tr.html();
                        $_tr.html(html_1 + data );
                        jQuery("#cpf-generate-table").show();
                    }
                });
            });

            //To select results
            function selectFilters(val) {
                jQuery($_this).val(val);
                jQuery(suggestion_box).hide();
            }

            /* jQuery(".cpf-table-sortable").sortable({
             connectWith: ".cpf-table-sortable",
             placeholder: "ui-state-highlight",
             helper: function (e, tr) {
             var $originals = tr.children();
             var $helper = tr.clone();
             $helper.children().each(function (index) {
             //set helper cell sizes to match the original size
             jQuery(this).width($originals.eq(index).width());
             });
             $helper.css("background-color", "rgb(223,240,249)");
             return $helper;
             }
             });
             jQuery("#the-list_1").disableSelection();*/


            jQuery("#cpf_move_selected").click(function (e) {
                e.preventDefault();
                var b = false;
                //var delele_feed = '<td><span class="dashicons dashicons-trash" onclick="cpf_remove_feed(this);"></span></td>';
                jQuery('#cpf-sort').find('input:checkbox').each(function (i, data) {
                    if (this.checked) {
                        b = true;
                        jQuery("#cpf-no-products").remove();
                      //  var t = [];
                        var remote_category = jQuery(this).parent().parent().find('.text_big').val();
                        var tr_row = jQuery(this).closest('tr').find('.cpf_selected_product_hidden_attr');
                        t= this;
                        var cpf_remote_category = jQuery(".cpf_remote_category_selected span");
                        var cpf_selected_local_cat_ids ;
                        var cpf_selected_product_title;
                        var cpf_selected_product_id;
                        var cpf_selected_product_cat_names ;
                        var cpf_selected_product_type;
                        var cpf_selected_product_attributes_details ;
                        var cpf_selected_product_variation_ids;
                        jQuery(tr_row ).find(".cpf_selected_local_cat_ids").each( function (i , data) {
                            cpf_selected_local_cat_ids = (jQuery(data).html());
                        });
                        jQuery(tr_row).find(".cpf_selected_local_cat_ids").each( function (i , data) {
                            cpf_selected_local_cat_ids = (jQuery(data).html());
                        });
                        jQuery(tr_row).find(".cpf_selected_product_title").each( function (i , data) {
                            cpf_selected_product_title = (jQuery(data).html());
                        });
                        jQuery(tr_row).find(".cpf_selected_product_cat_names").each( function (i , data) {
                            cpf_selected_product_cat_names = (jQuery(data).html());
                        });
                        jQuery(tr_row).find(".cpf_selected_product_type").each( function (i , data) {
                            cpf_selected_product_type = (jQuery(data).html());
                        });
                        jQuery(tr_row).find(".cpf_selected_product_attributes_details").each( function (i , data) {
                            cpf_selected_product_attributes_details = (jQuery(data).html());
                        });
                        jQuery(tr_row).find(".cpf_selected_product_variation_ids").each( function (i , data) {
                            cpf_selected_product_variation_ids = (jQuery(data).html());
                        });
                        jQuery(tr_row).find(".cpf_selected_product_id").each( function (i , data) {
                            cpf_selected_product_id = (jQuery(data).html());
                        });
                        var remote_category_arr;
                        jQuery(cpf_remote_category).each(function (i , data) {
                            //console.log(jQuery(data).html());
                            remote_category_arr = (jQuery(data).html());
                        });
                        jQuery.ajax({
                            type        : "POST" ,
                            url         : "<?php echo CPF_URL . "core/ajax/wp/fetch_product_ajax.php?q=savep" ?>",
                            data        : {
                                local_cat_ids           : cpf_selected_local_cat_ids,
                                product_id              : cpf_selected_product_id,
                                product_title           : cpf_selected_product_title,
                                category_name           : cpf_selected_product_cat_names,
                                product_type            : cpf_selected_product_type,
                                product_attributes      : cpf_selected_product_attributes_details,
                                product_variation_ids   : cpf_selected_product_variation_ids,
                                remote_category         : remote_category

                            },
                            success:function(res){
                                console.log("saved to database");

                            }
                        });
                    }
                    showSelectedProductTables();
                });

            });
            showSelectedProductTables();
            function showSelectedProductTables(){
                jQuery.ajax({
                    type        : 'POST',
                    url         : "<?php echo CPF_URL . "core/ajax/wp/fetch_product_ajax.php?q=showT" ?>",
                    data        : {params : 'listTables'} , 
                    success     : function (res) {
                        jQuery("#cpf-the-list_1").html((res));
                        console.log(res);
                    }
                });
            }

            function cpf_remove_feed(row){
               // console.log(row);
                t = row ;
                var product_id = jQuery(t).parent().parent().find(".cpf_product_id_hidden").html();
                jQuery.ajax({
                    type        : 'POST',
                    url         : "<?php echo CPF_URL . "core/ajax/wp/fetch_product_ajax.php?q=delR" ?>",
                    data        :{ id : product_id } , 
                    success     :function(res){
                        console.log("Deleted successsfully");
                        showSelectedProductTables();
                    }
                });
               /* var rows_number = jQuery("#cpf-the-list_1 tr").length;
                console.log(rows_number);
                var parent = jQuery(row).parent().parent();
                jQuery(parent).remove();
                if(rows_number == 1 ){
                    jQuery("#cpf-the-list_1").append('<tr id="cpf-no-products"><td colspan="5">No product selected.</td></tr>');
                }*/

            }

            function cpf_remove_feed_parent(row){
                var rows_number = jQuery("#cpf-the-list tr").length;
                var parent = jQuery(row).parent().parent();
                jQuery(parent).remove();
                if(rows_number == 1 ){
                    jQuery("#cpf-the-list").append('<tr id="cpf-no-products-search"><td colspan="5">No product search.</td></tr>');
                }

            }
             </script>
        <style>
            #categoryList {
                width: 100%;
                float: left;
               /* position: absolute;*/
                background: #fff;
                left: 0;
            }

            #categoryList .categoryItem {
                border-bottom: 1px solid #ccc;
                padding: 7px;
            }


        </style>
        <?php
        $output = '';
        $output .= '<div class="feeds_by_category" id="cpf_feeds_by_category"><div class="cpf_feed_by_category_left">';
        $output .= '				
				<p>Use the drop downs below to re-map ' . $pfcore->cmsPluginName . ' attributes to ' . $this->service_name . '\'s required attributes.<br>
				Additional attributes can also be found below by clicking [Show] Additional Attributes.</p>
				<label class="attributes-label" title="Required Attributes" id="toggleRequiredAttributes" onclick="toggleRequiredAttributes()">Required Attributes</label>
				<div class="required-attributes" id=\'required-attributes\'>
				<table>
				<tr><td>Attribute</td><td width="20"></td><td>' . $this->service_name . ' Attribute</td></tr>';

        foreach ($this->provider->attributeMappings as $thisAttributeMapping)
            if ($thisAttributeMapping->isRequired)
                $output .= '<tr><td>' . $this->createDropdownAttr($FoundAttributes, $thisAttributeMapping->attributeName, $thisAttributeMapping->mapTo) . '</td><td></td><td>' . $thisAttributeMapping->mapTo . '</td></tr>';
        $output .= '
			  </table>
			  </div>
			  <label class="attributes-label" title="Optional Attributes" id="toggleOptionalAttributes" onclick="toggleOptionalAttributes()">[Show] Additional Attributes</label>
			  <div class="optional-attributes" id=\'optional-attributes\'>
			  <table>
			  <tr><td>Attribute</td><td width="20"></td><td>' . $this->service_name . ' Attribute</td></tr>';

        foreach ($this->provider->attributeMappings as $thisAttributeMapping)
            if (!$thisAttributeMapping->isRequired)
                $output .= '<tr><td>' . $this->createDropdownAttr($FoundAttributes, $thisAttributeMapping->attributeName, $thisAttributeMapping->mapTo) . '</td><td></td><td>' . $thisAttributeMapping->mapTo . '</td></tr>';
        $output .= '
			  </table>
			  </div></div></div>';

        $output .= '<div class="cpf-custom_feed_generation" id="cpf-custom_feed_generation" style="padding-left: 7px;">
					<form name = "cpf-custom_feed" id="cpf-custom-feed-form" method="POST">';
        require_once dirname(__FILE__) . '/../../views/cpf-table-list.php';
        ?>
        <?php
        $output .= '<h3 class="heading">Product Search</h3>
							<table class="form-table">
								<tbody>
									<tr valign="top">
										<th scope="row" class="titledesc"><label for="cpf_keywords-filter">Filter by Keywords:</label><span class="cpf-help-tip"></span></th>
										<td class="forminp"><input type="search" id="cpf_keywords-filter" name="keywords_filter" placeholder="Type any Keywords" style="width:100%">
											<div class="cpf-suggestion-box"></div>
										</td>	
									</tr>
									<tr valign="top">
										<th scope="row" class="titledesc"><label for="cpf_category_filter">Search by Category:</label><span class="cpf-help-tip"></span></th>
										<td class="forminp" id="cpf_localcategory_list"></td>
									</tr>
									<tr valign="top">	
										<th scope="row" class="titledesc"><label for="cpf_brand-filter">Search by Brand:</label><span class="cpf-help-tip"></span></th>
										<td class="forminp">
											<input type="search" id="cpf_brand-filter" name="cpf_brand-filter" placeholder="Type any Brand" style="width:100%">
											<div class="cpf-suggestion-box"></div>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row" class="titledesc"><label for="cpf_sku_filter">Search by SKU:</label><span class="cpf-help-tip"></span></th>
										<td class="forminp">
											<input type="search" id="cpf_sku_filter" name="cpf_sku_filter" placeholder="Type any SKU" style="width:100%">
											<div class="cpf-suggestion-box"></div>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row" class="titledesc"><label for="cpf_price_filter">Search by Price:</label><span class="cpf-help-tip"></span></th>
										<td class="forminp">
											<select name = "cpf_price_filter" id= "cpf_price_filter" style="width:100%">
												<option value="">Select Price Range</option>
												<option value="1-5">1-5</option>
												<option value="6-25">6-25</option>
												<option value="26-100">26-100</option>
											</select>
										</td>
									</tr>
									</tbody>
							</table>
								<p class="submit"><input class="button-primary" type="submit" value="Search Product" id="submit" name="submit" style="float:right"/></p>
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
                                         <th scope="col" id="product_name"
                                            class="manage-column column-details column-primary">Product Name
                                        </th>
                                        <th scope="col" id="local_category_heading" class="manage-column column-user_name">Local
                                            Category
                                        </th>
                                        <th scope="col" id="remote_category_heading" class="manage-column column-site">Remote Category</th>
                                        <th style="width: 8%;"></th>
                                    </tr>
                                    </thead>
                                    <tbody id="cpf-the-list_1" data-cpf-lists="list:cpf-search-list"
                                           class="cpf-table-sortable ">
                                      <!--  <tr id="cpf-no-products">
                                            <td colspan="4">No product Selected.</td>
                                        </tr>-->
                                     </tbody>
                                    </table>
                            </div>
                        </div>
                    </div>';
                     $output .= '<table  id="cpf-generate-table">
                        <tbody>
                            <tr valign="top">
                                <th scope="row" class="titledesc"><label for="cpf_sku_filter">File name for feed :</label><span class="cpf-help-tip"></span></th>
                                <td class="forminp">
                                    <input type="text" name="feed_filename" id="feed_filename" class="text_big" value="" autocomplete="off">
                                </td>
                            </tr>
                            <tr>
                                <td>    
                                    <div class="feed-right-row">
                                        <input class="cupid-green" type="button" onclick="doGetCustomFeed()" value="Get Feed">
                                        <div id="feed-error-display">&nbsp;</div>
                                        <div id="feed-status-display">&nbsp;</div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                       </table>
                </div>
            </div> <!-- #postbox-container-1 -->

            <!-- #postbox-container-2 -->
            <div id="postbox-container-2" class="postbox-container" style="width: 555px;">
                <div class="meta-box-sortables ui-sortable">
                    <div class="postbox">
                        <h3><span>Search Results: </span></h3>
					    <span class="move-search-products"><input class="button-primary" type="button"
                                                                  id="cpf_move_selected" value="Move Selected"
                                                                 />
                     </div>
                    <table class="cpf-list-table widefat fixed striped cpf-results" id="cpf-sort" style="margin-top: -22px;">
                        <thead>
                        <tr>
                            <th style="width: 5%"></th>
                            <th scope="col" id="details" class="manage-column column-details column-primary">Product
                                Name
                            </th>
                            <th scope="col" id="user_name" class="manage-column column-user_name">Local Category</th>
                            <th scope="col" id="site" class="manage-column column-site" rowspan="2">Remote Category</th>
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
                </div> <!-- .meta-box-sortables -->
            </div> <!-- #postbox-container-1 -->
        </div> <!-- #post-body -->
        <br class="clear">
    </div> <!-- #poststuff -->
</div>
</div>';

        return $output;
    }

    function categoryList($initial_remote_category)
    {
        if ($this->service_name == 'eBaySeller') {
            $functionOn = 'onclick = "showeBayCategories(\'' . $this->service_name . '\')" ';
        } else {
            $functionOn = 'onkeyup = "doFetchCategory_timed(\'' . $this->service_name . '\' , this.value)"';
        }

        if ($this->blockCategoryList)
            return ' < input type = "hidden" id = "remote_category" name = "remote_category" value = "undefined" > ';
        else {
            if ($this->service_name == 'eBaySeller') {
                return '
    < span class="label" > ' . $this->service_name . ' Category : </span >
				  <span ><input type = "text" name = "categoryDisplayText" class="text_big" id = "categoryDisplayText"  onclick = "showeBayCategories(\'' . $this->service_name . '\')" value = "' . $initial_remote_category . '" autocomplete = "off" placeholder = "Click here for a category name" /></span >
				  <div id = "categoryList" class="categoryList" ></div >
				  <input type = "hidden" id = "remote_category" name = "remote_category" value = "' . $initial_remote_category . '" >
				  <input type = "hidden" id = "remote_category_id" name = "remote_category" value = "" >
        ';
            } else {
                return '<span class="label" > ' . $this->service_name . ' Category : </span >
				  <span ><input type = "text" name = "categoryDisplayText" class="text_big" id = "categoryDisplayText"  onkeyup = "doFetchCategory_timed(\'' . $this->service_name . '\',  this.value)" value = "' . $initial_remote_category . '" autocomplete = "off" placeholder = "Start typing for a category name" /></span >
				  <div id = "categoryList" class="categoryList" ></div >
				  <input type = "hidden" id = "remote_category" name = "remote_category" value = "' . $initial_remote_category . '" > ';
            }

        }
    }

    public function getTemplateFile()
    {
        $filename = dirname(__FILE__) . ' /../feeds/' . strtolower($this->service_name) . '/dialog.tpl.php';
        if (!file_exists($filename))
            $filename = dirname(__FILE__) . '/dialogbasefeed.tpl.php';
        return $filename;
    }

    public function initializeProvider()
    {
        //Load the feed provider
        require_once dirname(__FILE__) . '/md5.php';
        require_once dirname(__FILE__) . '/../feeds/' . strtolower($this->service_name) . '/feed.php';
        $providerName = 'P' . $this->service_name . 'Feed';
        $this->provider = new $providerName;
        $this->provider->loadAttributeUserMap();
    }

    function line2()
    {
        global $pfcore;
        if ($pfcore->cmsPluginName != 'RapidCart')
            return '';
        $listOfShops = $pfcore->listOfRapidCartShops();
        $output = ' < select class="text_big" id = "edtRapidCartShop" onchange = "doFetchLocalCategories()" > ';
        foreach ($listOfShops as $shop) {
            if ($shop->id == $pfcore->shopID)
                $selected = ' selected = "selected"';
            else
                $selected = '';
            $output .= ' < option value = "' . $shop->id . '"' . $selected . ' > ' . $shop->name . '</option > ';
        }
        $output .= '</select > ';
        return '
				<div class="feed-right-row" >
				  <span class="label" > Shop : </span >
    ' . $output . '
				</div > ';
    }

    public function mainDialog($source_feed = null)
    {

        global $pfcore;

        $this->advancedSettings = $pfcore->settingGet($this->service_name . ' - cart - product - settings');
        if ($source_feed == null) {
            $initial_local_category = '';
            $this->initial_local_category_id = '';
            $initial_remote_category = '';
            $this->initial_filename = '';
            $this->script = '';
            $this->cbUnique = '';
        } else {
            $initial_local_category = $source_feed->local_category;
            $this->initial_local_category_id = $source_feed->category_id;
            $initial_remote_category = $source_feed->remote_category;
            $this->initial_filename = $source_feed->filename;
            if ($source_feed->own_overrides == 1) {
                $strChecked = 'checked = "checked" ';
                $this->advancedSettings = $source_feed->feed_overrides;
            } else
                $strChecked = '';
            $this->cbUnique = ' < div><label ><input type = "checkbox" id = "cbUniqueOverride" ' . $strChecked . ' />Advanced commands unique to this feed </label ></div > ';
            /*if ($source_feed->own_overrides == 1) {
                $this->advancedSettings = $source_feed->feed_overrides;
                $this->script = '
                    <script type = "text/javascript" >
        jQuery(document) . ready(function () {
            jQuery("#cbUniqueOverride") . prop("checked", true);
        });
                    </script > ';
            }*/
        }

        $this->servName = strtolower($this->service_name);

        $this->initializeProvider();

        $attrVal = array();
        $this->folders = new PFeedFolder();
        $this->product_categories = new PProductCategories(); //used?

        $this->localCategoryList = '
			<input type = "text" name = "local_category_display" class="text_big" id = "local_category_display"  onclick = "showLocalCategories(\'' . $this->service_name . '\')" value = "' . $initial_local_category . '" autocomplete = "off" readonly = "true" placeholder = "Click here to select your categories" />
			<input type = "hidden" name = "local_category" id = "local_category" value = "' . $this->initial_local_category_id . '" />';
        $this->source_feed = $source_feed;


        //Pass this to the template for processing

        include $this->getTemplateFile();

    }

    //Strip special characters out of an option so it can safely go in a <select /> in the dialog
    function convert_option($option)
    {
        //Some Feeds (like Google & eBay) need to modify this
        return $option;
    }

}