<?php
/********************************************************************
 * Version 2.0
 * Core functionality of a basic feed.
 * Copyright 2014 Purple Turtle Productions. All rights reserved.
 * license    GNU General Public License version 3 or later; see GPLv3.txt
 * By: Keneto 2014-05-05
 * 2014-10 Moved to template (.tpl) format for simplicity (hopefully) -Keneto
 ********************************************************************/
if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!is_admin()) {
    die('Permission Denied!');
}
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
        //$active_tab = isset($_GET['tab']) ? $_GET['tab'] : "customfeed";
        ?>
        <div class="nav-wrapper">
            <nav class="nav-tab-wrapper">
                <span id="cpf-feeds_by_cats" class="nav-tab"> Feed By Category </span>
                <span id="cpf-custom-feed" class="nav-tab"> Custom Product Feed </span>
            </nav>
        </div>
        <div class="clear"></div>
        <style>

            nav.nav-tab-wrapper span {
                cursor: pointer;
            }

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
                /* display: inline*/
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

            .nav-tab-active {
                background: #F1F1FB;
            }
        </style>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                var feed_type = "<?php echo $this->feed_type; ?>";
                console.log(feed_type);
                if (feed_type == 1) {
                    jQuery("#cpf_feeds_by_category").hide();
                    jQuery(".feed-right").hide();
                    jQuery("#cpf-custom_feed_generation").show();
                }

                if (feed_type == 0) {
                    jQuery("#cpf_feeds_by_category").show();
                    jQuery(".feed-right").show();
                    jQuery("#cpf-custom_feed_generation").hide();
                }
                if ((feed_type == '')) {
                    jQuery("#cpf_feeds_by_category").hide();
                    jQuery(".feed-right").hide();
                    jQuery("#cpf-custom_feed_generation").show();
                }

                jQuery("#cpf-feeds_by_cats").click(function () {

                    jQuery("#cpf_feeds_by_category").show();
                    jQuery(".feed-right").show();
                    if (jQuery("#cpf_feeds_by_category").css('display') == 'block') {
                        jQuery("#cpf-feeds_by_cats").addClass('nav-tab-active');
                        jQuery("#cpf-custom-feed").removeClass('nav-tab-active');
                    }
                    jQuery("#cpf-custom_feed_generation").hide();
                });

                jQuery("#cpf-custom-feed").click(function () {

                    jQuery("#cpf_feeds_by_category").hide();
                    jQuery(".feed-right").hide();
                    jQuery("#cpf-custom_feed_generation").show();
                    if (jQuery("#cpf-custom_feed_generation").css('display') == 'block') {
                        jQuery("#cpf-custom-feed").addClass('nav-tab-active');
                        jQuery("#cpf-feeds_by_cats").removeClass('nav-tab-active');
                    }
                });
                if (jQuery("#cpf-custom_feed_generation").css('display') == 'block') {
                    jQuery("#cpf-custom-feed").addClass('nav-tab-active');
                    jQuery("#cpf-feeds_by_cats").removeClass('nav-tab-active');
                }


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

            function submitForm(event) {
                jQuery("#cpf-custom-feed-form").find('.spinner').css('visibility', 'visible');
                jQuery("#cpf-custom-feed-form").find('.cpf_search_info').css('display', 'block');
                var keywords = jQuery("#cpf_keywords-filter").val();
                var category = jQuery("#cpf_locacategories_filter").val();
                var brand = jQuery("#cpf_brand-filter").val();
                var sku = jQuery("#cpf_sku_filter").val();
                var merchat_type = jQuery("#selectFeedType").val();
                var price_option = jQuery("#cpf_price_filter_option").val();
                var service_name = "<?php echo $this->service_name; ?>";
                var cpf_price_range;
                if (price_option == 'less_than') {
                    if (jQuery("#cpf_price_filter_less_than").val() == '') {
                        alert("Enter amount");
                        return;
                    }
                    cpf_price_range = "<=" + jQuery("#cpf_price_filter_less_than").val();
                }
                if (price_option == 'more_than') {
                    if (jQuery("#cpf_price_filter_more_than").val() == '') {
                        alert("Enter amount");
                        return;
                    }
                    cpf_price_range = ">=" + jQuery("#cpf_price_filter_more_than").val();
                }
                if (price_option == 'in_between') {
                    if (jQuery("#cpf_price_filter_in_between_first").val() == '') {
                        alert("Enter first amount");
                        return;
                    }
                    if (jQuery("#cpf_price_filter_in_between_second").val() == '') {
                        alert("Enter first amount");
                        return;
                    }
                    var cpf_price_range_first = jQuery("#cpf_price_filter_in_between_first").val();
                    var cpf_price_range_second = jQuery("#cpf_price_filter_in_between_second").val();
                    if (cpf_price_range_first > cpf_price_range_second) {
                        alert("Price range is not valid.First amount should be less than second amount");
                        return;
                    }
                    cpf_price_range = cpf_price_range_first + '-' + cpf_price_range_second;
                }
                jQuery("#cpf-no-products-search").remove();
                jQuery.ajax({
                    type: "POST",
                    url: "<?php echo CPF_URL . "core/ajax/wp/fetch_product_ajax.php?q=search" ?>",
                    data: {
                        keywords: keywords,
                        category: category,
                        brand: brand,
                        sku: sku,
                        price_range: cpf_price_range,
                        merchat_type: merchat_type,
                        service_name: service_name
                    },
                    success: function (data) {
                        jQuery("#cpf-custom-feed-form").find('.spinner').css('visibility', 'hidden');
                        jQuery("#cpf-custom-feed-form").find('.cpf_search_info').css('display', 'none');
                        jQuery("#postbox-container-2 .cpf-text-info").css('display', 'block');
                        var $_tr = jQuery("#cpf-the-list");
                        jQuery("#cpf-no-results").remove();
                        var html_1 = $_tr.html();
                        $_tr.html(html_1 + data);
                        jQuery("#cpf-generate-table").show();
                        jQuery($_tr).parent().parent().find(".tablenav").show();
                        var divPosition = jQuery("#postbox-container-2").offset();
                        jQuery('html, body').animate({scrollTop: divPosition.top}, "slow");
                    }
                });
            }
            ;

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


            function moveSelected(e) {
                var b = false;
                if ((jQuery("#cpf-the-list").find("input:checkbox:checked").length) == 0) {
                    alert("Please select atleast one product from product search list.");
                    return false;
                }

                jQuery('#cpf-the-list').find('input:checkbox').each(function (i, data) {
                    if (this.checked) {
                        t = this;
                        var remote_category = jQuery(this).parent().parent().find('.text_big').val();
                        if (remote_category == '') {
                            if (jQuery(data).is(":checked")) {
                                jQuery(t).parent().parent().find('.no_remote_category').html("Select remote category.");
                                jQuery(t).parent().parent().find('.no_remote_category').fadeIn('slow');
                                return;
                            }
                        } else {
                            if (jQuery(data).is(":checked")) {
                                jQuery(t).parent().parent().find('.no_remote_category').hide();
                            }
                        }
                        jQuery(".move-search-products").find(".spinner").css('visibility', 'visible');
                        b = true;
                        jQuery("#cpf-no-products").remove();
                        //  var t = [];
                        var tr_row = jQuery(this).closest('tr').find('.cpf_selected_product_hidden_attr');
                        t = this;
                        var cpf_remote_category = jQuery(".cpf_remote_category_selected span");
                        var cpf_selected_local_cat_ids;
                        var cpf_selected_product_title;
                        var cpf_selected_product_id;
                        var cpf_selected_product_cat_names;
                        var cpf_selected_product_type;
                        var cpf_selected_product_attributes_details;
                        var cpf_selected_product_variation_ids;
                        jQuery(tr_row).find(".cpf_selected_local_cat_ids").each(function (i, data) {
                            cpf_selected_local_cat_ids = (jQuery(data).html());
                        });
                        jQuery(tr_row).find(".cpf_selected_local_cat_ids").each(function (i, data) {
                            cpf_selected_local_cat_ids = (jQuery(data).html());
                        });
                        jQuery(tr_row).find(".cpf_selected_product_title").each(function (i, data) {
                            cpf_selected_product_title = (jQuery(data).html());
                        });
                        jQuery(tr_row).find(".cpf_selected_product_cat_names").each(function (i, data) {
                            cpf_selected_product_cat_names = (jQuery(data).html());
                        });
                        jQuery(tr_row).find(".cpf_selected_product_type").each(function (i, data) {
                            cpf_selected_product_type = (jQuery(data).html());
                        });
                        jQuery(tr_row).find(".cpf_selected_product_attributes_details").each(function (i, data) {
                            cpf_selected_product_attributes_details = (jQuery(data).html());
                        });
                        jQuery(tr_row).find(".cpf_selected_product_variation_ids").each(function (i, data) {
                            cpf_selected_product_variation_ids = (jQuery(data).html());
                        });
                        jQuery(tr_row).find(".cpf_selected_product_id").each(function (i, data) {
                            cpf_selected_product_id = (jQuery(data).html());
                        });
                        var remote_category_arr;
                        jQuery(cpf_remote_category).each(function (i, data) {
                            //console.log(jQuery(data).html());
                            remote_category_arr = (jQuery(data).html());
                        });

                        jQuery.ajax({
                            type: "POST",
                            url: "<?php echo CPF_URL . "core/ajax/wp/fetch_product_ajax.php?q=savep" ?>",
                            data: {
                                local_cat_ids: cpf_selected_local_cat_ids,
                                product_id: cpf_selected_product_id,
                                product_title: cpf_selected_product_title,
                                category_name: cpf_selected_product_cat_names,
                                product_type: cpf_selected_product_type,
                                product_attributes: cpf_selected_product_attributes_details,
                                product_variation_ids: cpf_selected_product_variation_ids,
                                remote_category: remote_category,
                            },
                            success: function (res) {
                                jQuery(".move-search-products").find(".spinner").css('visibility', 'hidden');
                                console.log("saved to database");

                            }
                        });
                    }
                    showSelectedProductTables();
                });

            }
            ;
            showSelectedProductTables();
            function showSelectedProductTables() {
                jQuery.ajax({
                    type: 'POST',
                    url: "<?php echo CPF_URL . "core/ajax/wp/fetch_product_ajax.php?q=showT" ?>",
                    data: {params: 'listTables'},
                    success: function (res) {
                        jQuery("#cpf-the-list_1").html((res));
                    }
                });
            }

            jQuery("#cpf_price_filter_option").on('change', function () {
                var price_option = jQuery("#cpf_price_filter_option").val();
                if (price_option == '') {
                    jQuery("#cpf_price_filter_less_than").hide();
                    jQuery("#cpf_price_filter_more_than").hide();
                    jQuery("#cpf_price_filter_in_between_first").hide();
                    jQuery("#cpf_price_filter_in_between_second").hide();
                    jQuery("#cpf_price_filter_less_than").val();
                    jQuery("#cpf_price_filter_more_than").val();
                    jQuery("#cpf_price_filter_in_between_first").val();
                    jQuery("#cpf_price_filter_in_between_second").val();
                }
                if (price_option == 'less_than') {
                    jQuery("#cpf_price_filter_more_than").hide();
                    jQuery("#cpf_price_filter_in_between_first").hide();
                    jQuery("#cpf_price_filter_in_between_second").hide();
                    jQuery("#cpf_price_filter_less_than").show();
                }
                if (price_option == 'more_than') {
                    jQuery("#cpf_price_filter_less_than").hide();
                    jQuery("#cpf_price_filter_in_between_first").hide();
                    jQuery("#cpf_price_filter_in_between_second").hide();
                    jQuery("#cpf_price_filter_more_than").show();
                }
                if (price_option == 'in_between') {
                    jQuery("#cpf_price_filter_less_than").hide();
                    jQuery("#cpf_price_filter_more_than").hide();
                    jQuery("#cpf_price_filter_in_between_first").show();
                    jQuery("#cpf_price_filter_in_between_second").show();

                }
            });
            function cpf_remove_feed(row) {
                // console.log(row);
                if (confirm("Are you sure you want to deleted this feed?")) {
                    t = row;
                    jQuery(t).parent().find('.spinner').css('visibility', 'visible');
                    var product_id = jQuery(t).parent().parent().find(".cpf_feed_id_hidden").html();
                    jQuery.ajax({
                        type: 'POST',
                        url: "<?php echo CPF_URL . "core/ajax/wp/fetch_product_ajax.php?q=delR" ?>",
                        data: {id: product_id},
                        success: function (res) {
                            console.log("Deleted successsfully");
                            jQuery(t).parent().find('.spinner').css('visibility', 'hidden');
                            showSelectedProductTables();
                        }
                    });
                } else {
                    return;
                }
            }

            function cpf_remove_feed_parent(row) {
                if (confirm("Are you sure you want to deleted this feed?")) {
                    t = row;
                    jQuery(row).parent().find('.spinner').css('visibility', 'visible');
                    var rows_number = jQuery("#cpf-the-list tr").length;
                    var parent = jQuery(row).parent().parent();
                    jQuery(parent).remove();
                    if (rows_number == 1) {
                        jQuery("#cpf-the-list").append('<tr id="cpf-no-products-search"><td colspan="5">No product search.</td></tr>');
                    }
                    else {
                        return;
                    }
                }

            }

            jQuery("#cpf_select_all_checkbox").click(function () {
                var checked = jQuery("#cpf_select_all_checkbox").attr('checked');
                if (checked == 'checked') {
                    jQuery("#cpf-the-list").find("input[type=checkbox]").attr('checked', true);
                } else {
                    jQuery("#cpf-the-list").find("input[type=checkbox]").removeAttr('checked');
                }

            });

            jQuery("#cpf_select_all_checkbox_1").click(function () {
                var checked = jQuery("#cpf_select_all_checkbox_1").attr('checked');
                if (checked == 'checked') {
                    jQuery("#cpf-the-list_1").find("input[type=checkbox]").attr('checked', true);
                } else {
                    jQuery("#cpf-the-list_1").find("input[type=checkbox]").removeAttr('checked');
                }

            });

            function addRows(selector) {
                var tr_html = '';
                var categoryList = jQuery("#cpf_attrdropdownlist .cpf_default_attributes").html();
                var merchantList = jQuery("#cpf_merchantAttributes").html();
                jQuery(categoryList).find('.cpf_custom_value_span').remove();
                tr_html += '<tr>';
                tr_html += '<td style="text-align: center">' + merchantList + '</td>';
                tr_html += '<td style="text-align: center" ><select name="cpf_type " id="cpf_change_type" class="cpf_change_type" onchange="cpf_changeType(this);"><option value="0">Attributes</option><option value="1">Custom Value</option></select></td>';
                tr_html += '<td style="text-align: center" class="cpf_value_td">' + categoryList;
                tr_html += '<span class="cpf_custom_value_span" style="display:none;"><input type="text"  class="cpf_custom_value_attr" name="cpf_custom_value" style="width:100%"/></span></td>';
                tr_html += '<td style="text-align: center"><input type="text" class="cpf_prefix" name="cpf_prefix" style="width:100%"/></td>';
                tr_html += '<td style="text-align: center"><input type="text" class="cpf_suffix" name="cpf_suffix" style="width:100%" /></td>';
                tr_html += '<td style="text-align: center"></td>';
                tr_html += '<td style="width: 5%;text-align: center"><span class="dashicons dashicons-plus" onclick="addRows(this);" title="Add Rows"></span></td>';
                tr_html += '<td style="width: 5%;text-align: center"><span class="dashicons dashicons-trash" onclick="removeRows(this);" title="Delete Rows"></span></td>';
                tr_html += '</tr>';
                jQuery("#cpf_custom_feed_config_body").append(tr_html);
            }
            function removeRows(selector) {
                var tr_length = jQuery("#cpf_custom_feed_config_body tr").length;
                if (tr_length == 1) {
                    jQuery("#cpf_custom_feed_config_body tr").find("span.dashicons-trash").removeAttr('onclick');
                }
                var parent = jQuery(selector).parent().parent();
                jQuery(parent).remove();
            }

            function cpf_changeType(selector) {
                t = selector;
                if (selector.value == 1) {
                    //jQuery(t).parent().parent().find(".attribute_select").removeAttr('selected');
                    jQuery(t).parent().parent().find(".cpf_custom_value_span").show();
                    jQuery(t).parent().parent().find(".cpf_custom_value_attr").focus();
                    jQuery(t).parent().parent().find(".cpf_default_attributes").hide();
                    jQuery(t).parent().parent().find(".attribute_select").hide();
                }
                if (selector.value == 0) {
                    jQuery(t).parent().parent().find(".cpf_custom_value_span").hide();
                    jQuery(t).parent().parent().find(".cpf_custom_value_attr").hide();
                    jQuery(t).parent().parent().find(".cpf_default_attributes").show();
                    jQuery(t).parent().parent().find(".attribute_select").show();
                    jQuery(t).parent().parent().find(".attribute_select").focus();
                }
            }

            function toggleFeedSettings(event) {
                var display = jQuery("#cpf_custom_feed_config").css('display');
                if (display == 'none') {
                    jQuery("#cpf_feed_config_desc").slideDown();
                    jQuery("#cpf_custom_feed_config").slideDown();
                    jQuery(event).val('Hide Feed Config');
                    jQuery(event).attr('title', 'Hide Feed config section');
                    /* var divPosition = jQuery("#cpf_custom_feed_config").offset();
                     jQuery('#custom_feed_settingd').animate({scrollBottom: divPosition.top}, "slow");*/
                }
                if (display == 'block') {
                    jQuery("#cpf_custom_feed_config").slideUp();
                    jQuery("#cpf_feed_config_desc").slideUp();
                    jQuery(event).attr('title', 'This will open feed config section below.You can provide suffix and prefix for the attribute to be included in feed.');
                    jQuery(event).val('Show Feed Config');
                }
            }

            function cpf_apply_action(selector) {
                //console.log(selector);
                t = selector;
                var action = jQuery(selector).parent().parent().find("#bulk-action-selector-bottom").val();
                var error_div = jQuery(t).parent().parent().parent().parent().parent().parent().find("#cpf_error_message_action");
                jQuery(error_div).html();
                var msg = '';
                if (action == -1) {
                    msg = "Error: Please select bulk options.";
                    jQuery(error_div).html(msg);
                    // jQuery(error_div).fadeOut('slow');
                    return;
                }
                if (action == 'assignCategory') {
                    var category = jQuery(t).parent().parent().find(".text_big").val();
                    var checked_option_length = jQuery(selector).parent().parent().parent().parent().parent().parent().parent().find("#cpf-the-list td input:checkbox:checked").length;
                    if (category == '') {
                        msg = "Error: Please select merchant Category";
                        jQuery(error_div).html(msg);
                        jQuery(error_div).fadeIn('slow');
                        jQuery(t).parent().parent().find(".text_big").focus();
                        return;
                    }
                    if (checked_option_length == 0) {
                        msg = "Error: Please select product list.";
                        jQuery(error_div).html(msg);
                        jQuery(error_div).fadeIn('slow');
                        jQuery(error_div).fadeOut('slow');
                        return;
                    }

                    var checked_option = jQuery(selector).parent().parent().parent().parent().parent().parent().parent().find("#cpf-the-list td input:checkbox:checked");
                    jQuery(checked_option).parent().parent().find('.text_big').val(category);
                }

                if (action == 'trash') {
                    var checked_option_length_t = jQuery(selector).parent().parent().parent().parent().parent().parent().parent().find("#cpf-the-list td input:checkbox:checked").length;
                    if (checked_option_length_t == 0) {
                        msg = "Error: Please select product list.";
                        jQuery(error_div).html(msg);
                        jQuery(error_div).fadeIn('slow');
                        jQuery(error_div).fadeOut('slow');
                        return;
                    }
                    var table_body = jQuery(selector).parent().parent().parent().parent().parent().parent().parent().find("#cpf-the-list");
                    var checked_option_t = jQuery(selector).parent().parent().parent().parent().parent().parent().parent().find("#cpf-the-list td input:checkbox:checked");
                    if (confirm("Are you sure you want to deleted this feed?")) {
                        jQuery(checked_option_t).parent().parent().remove();
                        console.log(checked_option_length_t);
                        jQuery(selector).parent().parent().parent().parent().parent().parent().parent().find("#cpf_select_all_checkbox").removeAttr('checked');
                    }
                    var table_body_length = jQuery(selector).parent().parent().parent().parent().parent().parent().parent().find("#cpf-the-list tr ").length;
                    if (table_body_length == 0) {
                        jQuery(table_body).append('<tr id="cpf-no-products-search"><td colspan="5">No product search.</td></tr>');
                    }
                }
            }

            function deletedSelected(selector) {
                s = selector;
                var checked_box_length = jQuery(selector).parent().parent().parent().parent().parent().find("#cpf-the-list_1 input:checkbox:checked").length;
                if (checked_box_length == 0) {
                    alert("Please select product that you want to delete.");
                    return;
                }
                if (confirm("Are you sure you want to delete this feed ? ")) {
                    jQuery(selector).parent().parent().find(".spinner").css('visibility', 'visible');
                    jQuery(selector).parent().parent().find("#cpf_deleted_selected_from_list").show();
                    var table_html = jQuery(selector).parent().parent().parent().parent().parent().find("#cpf-the-list_1 input:checkbox:checked");
                    var feed_html = jQuery(table_html).parent().siblings().find(".cpf_feed_id_hidden");
                    var feed_id = [];
                    jQuery(feed_html).each(function (i, data) {
                        feed_id.push(jQuery(data).html());
                    });
                    jQuery.ajax({
                        type: 'POST',
                        url: "<?php echo CPF_URL . "core/ajax/wp/fetch_product_ajax.php?q=delR" ?>",
                        data: {id: feed_id},
                        success: function (res) {
                            jQuery(selector).parent().parent().find(".spinner").css('visibility', 'hidden');
                            jQuery(selector).parent().parent().find("#cpf_deleted_selected_from_list").html(checked_box_length + " Product(s) deleted successfully.");
                            jQuery(selector).parent().parent().find("#cpf_deleted_selected_from_list").fadeOut(3000);
                            jQuery(selector).parent().parent().parent().parent().parent().find("#cpf_select_all_checkbox_1").removeAttr('checked');
                            showSelectedProductTables();
                        }
                    });
                }
            }


            function bulk_action_selector(selector) {
                var option = jQuery(selector).val();
                if (option == 'assignCategory') {
                    jQuery(selector).parent().parent().find("#cpf_bulk_action_list").show();
                }

                if (option != 'assignCategory') {
                    jQuery(selector).parent().parent().find("#cpf_bulk_action_list").hide();
                }
            }

            jQuery(function () {
                jQuery(document).tooltip({
                    track: true,
                    items: "[data-title]",
                    content: function () {
                        return jQuery(this).data('title').replace('|', '<br />');

                    },
                    position: {
                        my: 'center-top',
                        at: 'center-bottom+10'

                    },
                    tooltipClass: "cpf_tool-tip"
                });
            });

            jQuery(document).ready(function () {
                jQuery('[data-toggle="tooltip"]').tooltip();
            });

            function toggleAdvanceCommandSection(event) {
                var display = jQuery("#cpf_advance_section").css('display');
                if (display == 'none') {
                    jQuery("#cpf_advance_section").slideDown();
                    jQuery(event).val('Hide Advance Section');
                    jQuery(event).attr('title', 'Hide Feed config section');
                    /* var divPosition = jQuery("#cpf_custom_feed_config").offset();
                     jQuery('#custom_feed_settingd').animate({scrollBottom: divPosition.top}, "slow");*/
                }
                if (display == 'block') {
                    jQuery("#cpf_advance_section").slideUp();
                    jQuery("#feed-advanced").slideUp();
                    // jQuery("#bUpdateSetting").slideUp();
                    jQuery(event).attr('title', 'This will open feed advance command section where you can customize your feed using advanced command.');
                    jQuery(event).val('Show advance Command section');
                }
            }


        </script>
        <style>
            #cboxClose {
                right: 0;
                top: 0;
                background-position: -100px -25px;
                margin-right: 11px;
                margin-top: 11px;
                width: 44px;
                text-indent: 0;
            }

            .ui-tooltip {
                background: #666;
                color: white;
                border: none;
                padding: 0;
                opacity: 1;
            }

            .ui-tooltip-content {
                position: relative;
                padding: 1em;
            }

            .ui-tooltip-content::after {
                content: '';
                position: absolute;
                border-style: solid;
                display: block;
                width: 0;
            }

            .right .ui-tooltip-content::after {
                top: 18px;
                left: -10px;
                border-color: transparent #666;
                border-width: 10px 10px 10px 0;
            }

            .left .ui-tooltip-content::after {
                top: 18px;
                right: -10px;
                border-color: transparent #666;
                border-width: 10px 0 10px 10px;
            }

            .top .ui-tooltip-content::after {
                bottom: -10px;
                left: 72px;
                border-color: #666 transparent;
                border-width: 10px 10px 0;
            }

            .bottom .ui-tooltip-content::after {
                top: -10px;
                left: 72px;
                border-color: #666 transparent;
                border-width: 0 10px 10px;
            }

            #cpf_error_message_action {
                color: #ff0000;
            }

            #cpf_custom_feed_config_body select.attribute_select {
                width: 100% !important;
            }

            .categoryList {
                width: 100%;
                float: left;
                background: #fff;
                left: 0;
                max-height: 167px;
                overflow-x: hidden;
                overflow-y: auto;
            }

            .categoryItem {
                border-bottom: 1px solid #ccc;
                padding: 7px;
            }

            span.text_desc {
                display: inline-block;
                line-height: 20px;
            }

            #cpf-the-list_1 tr:first {
                display: none;
            }

            .cpf-selected-parent .dashicons-trash {
                cursor: pointer;
            }

            .no_remote_category {
                color: #ff0000;
                width: 180px;
            }

            #cpf_custom_feed_config_body span.dashicons {
                cursor: pointer;
            }

            /*  .ui-tooltip-content::after, .ui-tooltip-content::before {
                  content: "";
                  position: absolute;
                  border-style: solid;
                  display: block;
                  left: 90px;
              }
              .ui-tooltip-content::before {
                  bottom: -10px;
                  border-color: #AAA transparent;
                  border-width: 10px 10px 0;
              }
              .ui-tooltip-content::after {
                  bottom: -7px;
                  border-color: white transparent;
                  border-width: 10px 10px 0;
              }*/

            #cpf_bulk_action_list {
                position: absolute;
                z-index: 9999;
                width: 15%;
            }

            td.apply_btn {
                position: relative;
                left: 183px;
            }

            #cpf_feed_setting_advance_commands .dashicons-arrow-right {
                line-height: 14px;
            }

            #cpf_advance_command {
                padding-left: 19px;
            }

        </style>
        </
        style
        >
        <?php
        $output = '';
        $output .= '
                    <div class="feeds_by_category" id="cpf_feeds_by_category"><div class="cpf_feed_by_category_left">
                        <p>Use the drop downs below to re-map ' . $pfcore->cmsPluginName . ' attributes to ' . $this->service_name . '\'s required attributes.<br>
                    Additional attributes can also be found below by clicking [Show] Additional Attributes.</p>
                        <label class="attributes-label"><input class="button-primary" type="button" title="Required Attributes" id="toggleRequiredAttributes" onclick="toggleRequiredAttributes(this);" value="Show Required Attributes"/></label>
                        <div class="required-attributes" id=\'required-attributes\' style="display:none;">
                        <table>
                            <tr><td>Attribute</td><td width="20"></td><td>' . $this->service_name . ' Attribute</td></tr>';

        foreach ($this->provider->attributeMappings as $thisAttributeMapping)
            if ($thisAttributeMapping->isRequired)
                $output .= '<tr><td>' . $this->createDropdownAttr($FoundAttributes, $thisAttributeMapping->attributeName, $thisAttributeMapping->mapTo) . '</td><td></td><td>' . $thisAttributeMapping->mapTo . '</td></tr>';
        $output .= '</table>
			        </div>
			  <label class="attributes-label"><input class="button-primary"  type="button" id="toggleOptionalAttributes" onclick="toggleOptionalAttributes(this);" value="Show Additional Attributes" title="Optional Attributes"/></label>
			  <div class="optional-attributes" id=\'optional-attributes\' style="display: none;">
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
								<p class="submit"><span class="spinner"></span> <input class="button-primary" title="This will search product list from above information you give and generate the result on search result section below." type="button" value="Search Product" id="submit_data" name="submit_data" onclick="submitForm('."'{$this->service_name}'".',0);" style="float:right"/></p>
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
                                         <th style="width: 5%;padding-left: 2px;"><input type="checkbox" id="cpf_select_all_checkbox_1" /></th>
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
                            <th style="width: 5%;padding-left: 2px;"><input type="checkbox" id="cpf_select_all_checkbox" /></th>
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
                                            <input type="search" name="categoryDisplayText" class="text_big" id="categoryDisplayText" onkeyup="doFetchCategory_timed_custom(' . "'{$this->service_name}'" . ',this)" value="" autocomplete="off" placeholder="Start typing merchant category..." style="width: 100%;"></span>
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
        $merchantAttributes = '';
        $merchantName = $this->service_name;
        $merchantAttributes .= '<div id="cpf_merchantAttributes"><select name="cpf_merchantAttributes" class="cpf_merchantAttributes" style="width:100%;">';
        $merchantAttributes .= '<option value="">Select Category</option>';
        foreach ($this->provider->attributeMappings as $key => $mappingData) {
            $merchantAttributes .= '<option value="' . $mappingData->mapTo . '">' . $mappingData->mapTo . '</option>';
        }

        $output .= '<!--<div class="postbox" id="cpf_feed_setting_advance_commands">
                        <div class="inside-export-target" style="height: auto !important;">-->
                            <!-- <span class="dashicons dashicons-arrow-right"></span>Advanced Commands grant you more control over your feeds. They provide a way to create your own attribute, map from non-standard ones or modify and delete feed data.<br/>
                             <span class="dashicons dashicons-arrow-right"></span>After selecting a Merchant Type, navigate to the bottom of the page and click "Open Feed Settings".<br/>
                             <span class="dashicons dashicons-arrow-right"></span>Feed Config setting part  will be displayed where you can enter commands to apply rules and customize your feed.<br/>
                            <span class="dashicons dashicons-arrow-right"></span>The advanced command can be used to customize your feed:<br/></br><br/>-->
                            <span id="custom_feed_settingd"><a href="#cpf_custom_feed_config"><input class="button-primary" title="This will open feed config section below.You can provide suffix and prefix for the attribute to be included in feed." type="button" id="cpf_feed_config_link" value="Show Feed Config" onclick="toggleFeedSettings(this);"></a></span>
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
        $merchantAttributes .= '</select></div>';
        $output .= '<div id="cpf_custom_feed_config" class="cpf_custom_feed_config" style="display: none;">
            <div class="postbox" style="overflow: hidden;box-sizing: border-box;">
                <h3 style="float: left;"><span>Feed Config: </span></h3>
                <span class="update_cpf_config" style="float: right;padding-top: 16px;">
                <button class="button-primary" id="bFeedSetting" name="bFeedSetting" onclick="doCustomFeedSetting(this);" title="This will update your feed config with the data that you enter in feed config section."> Update</button>
                <input type=hidden name="cpf_custom_merchant_type" value="cp_advancedFeedSetting-' . $this->service_name . '" />
					<div id="updateCustomSettingMessage">&nbsp;</div><span class="spinner"></span>
                </span>
		    </div>
            <table class="cpf-list-table widefat fixed striped cpf-results" id="cpf-sort_config" style="margin-top: -22px;">
                <thead>
                <tr>
                    <th scope="col" style="text-align: center">' . ($this->provider->providerName) . ' Attributes</pre></th>
                    <th scope="col" style="text-align: center">Type</th>
                    <th scope="col" style="text-align: center">Value</th>
                     <th scope="col" style="text-align: center">Prefix</th>
                    <th scope="col" style="text-align: center">Suffix</th>
                    <th scope="col" style="text-align: center">Output limit</th>
                    <th scope="col" style="width: 5%;text-align: center"></th>
                    <th scope="col" style="width: 5%;text-align: center"></th>
                </tr>
                </thead>
                <tbody id="cpf_custom_feed_config_body">
                <tr>
                    <td style="text-align: center;width:100%">' . $merchantAttributes . '</td>
                    <td style="text-align: center">
                    <select name="cpf_type " class="cpf_change_type" onchange="cpf_changeType(this);"><option value="0">Attributes</option><option value="1">Custom Value</option></select></td>
                    <td style="text-align: center" id="cpf_attrdropdownlist" class="cpf_value_td">
                        <span class="cpf_default_attributes">' . $this->createDropdownAttr($FoundAttributes, $thisAttributeMapping->attributeName, $thisAttributeMapping->mapTo) . '</span>
                        <span class="cpf_custom_value_span" style="display:none;"><input type="text" class="cpf_custom_value_attr" name="cpf_custom_value" style="width:100%"/></span>
                    </td>
                     <td style="text-align: center"><input type="text" class="cpf_prefix" name="cpf_prefix" style="width:100%"/></td>
                    <td style="text-align: center"><input type="text" class="cpf_suffix" name="cpf_suffix" style="width:100%" /></td>
                    <td style="text-align: center"><input type="text" id="cpf_feed_output_limit" name="cpf_feed_output_limit" style="width:100%" /></td>
                    <td style="width: 5%;text-align: center"><span class="dashicons dashicons-plus" onclick="addRows(this);" title="Add rows."></span></td>
                    <td style="width: 5%;text-align: center"><span class="dashicons dashicons-trash" onclick="removeRows(this); title="Delete this rows."></span></td>
                </tr>
                </tbody>
            </table>
         </div>  <!--#cpf_custom_feed_config -->
         
         <table  id="cpf-generate-table" style="float: right;padding-top: 70px;">
            <tbody>
                <tr valign="top">
                    <th style="line-height: 2em;" scope="row" class="titledesc"><label for="feed_filename">File name for feed :</label><span style="padding : 8px 0 0 3px" class="cpf-help-tip" title="Enter the file name for the feed."></span></th>
                    <td class="forminp">
                        <input type="search" style="width:100%" name="feed_filename" id="feed_filename" class="text_big" value="' . "$this->initial_filename" . '" autocomplete="off" placeholder="Enter file name for feed you want to create">
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
                            <input class="button-primary" type="button" onclick="doGetCustomFeed(' . "'{$this->service_name}'" . ')" value="Get Feed" style="width:65%">
                            <div id="feed-error-display">&nbsp;</div>
                            <div id="feed-status-display">&nbsp;</div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

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

    public function mainDialog($source_feed = null, $feed_type = null)
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
            $this->cbUnique = ' <div><label ><input type = "checkbox" id = "cbUniqueOverride" ' . $strChecked . ' />Advanced commands unique to this feed </label ></div > ';
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
        $this->feed_type = $feed_type;


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