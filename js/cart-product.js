var ajaxhost = "";
var category_lookup_timer;
//the commands are WordPress defaults, declared as variables so Joomla can replace them
var cmdFetchCategory = "core/ajax/wp/fetch_category.php";
var cmdFetchCategory_custom = "core/ajax/wp/fetch_category_custom.php";
var cmdFetchLocalCategories = "core/ajax/wp/fetch_local_categories.php";

var cmdFetchTemplateDetails = "core/ajax/wp/fetch_template_details.php";
var cmdGetFeed = "core/ajax/wp/get_feed.php";
var cmdGetCustomFeed = "core/ajax/wp/get_custom_feed.php";
var cmdGetFeedStatus = "core/ajax/wp/get_feed_status.php";
var cmdMappingsErase = "core/ajax/wp/attribute_mappings_erase.php";
var cmdRemember = "core/ajax/wp/update_remember.php";
var cmdSearsPostByRestAPI = "core/ajax/wp/sears_post.php";
var cmdSaveAggregateFeedSetting = "core/ajax/wp/save_aggregate_feed_setting.php";
var cmdSelectFeed = "core/ajax/wp/select_feed.php";
var cmdSetAttributeOption = "core/ajax/wp/attribute_mappings_update.php";
var cmdSetAttributeUserMap = "core/ajax/wp/attribute_user_map.php";
var cmdUpdateAllFeeds = "core/ajax/wp/update_all_feeds.php";
var cmdUpdateSetting = "core/ajax/wp/update_setting.php";
var cmdUploadFeed = "core/ajax/wp/upload_feed.php";
var cmdUploadFeedStatus = "core/ajax/wp/upload_feed_status.php";

var feedIdentifier = 0; //A value we create and inform the server of that allows us to track errors during feed generation
var feed_id = 0; //A value the server gives us if we're in a feed that exists already. Will be needed when we want to set overrides specific to this feed
var feedFetchTimer = null;
var localCategories = {children: []};
window.feed_type = -1;

//Custom Feed Variable

var cmdFetchLocalCategories_custom = "core/ajax/wp/fetch_local_categories_custom.php";
var cmdSearchProductAjax = "core/ajax/wp/fetch_product_ajax.php";
var cmdFetchProductAjax = "core/ajax/wp/fetch_product_ajax.php";
var cmdSaveProductAjax = "core/ajax/wp/fetch_product_ajax.php";
var cmdShowProductAjax = "core/ajax/wp/fetch_product_ajax.php";
var cmdDeleteProductAjax = "core/ajax/wp/fetch_product_ajax.php";
var cmdGetProductAjax = "core/ajax/wp/fetch_product_ajax.php";
var cmdUpdateFeedConfig = "core/ajax/wp/update_feed_config.php";
var cmdFetchTableAjax = "core/ajax/wp/fetch_feed_table.php";

function parseFetchCategoryResult(res) {
    document.getElementById("categoryList").innerHTML = res;
    if (res.length > 0) {
        document.getElementById("categoryList").style.border = "1px solid #A5ACB2";
        document.getElementById("categoryList").style.display = "inline";
    } else {
        document.getElementById("categoryList").style.border = "0px";
        document.getElementById("categoryList").style.display = "none";
        document.getElementById("remote_category").value = "";
    }
}

function parseFetchLocalCategories(res) {
    localCategories = jQuery.parseJSON(res);
}

function parseGetFeedResults(res, selector) {

    //Stop the intermediate status interval
    window.clearInterval(feedFetchTimer);
    feedFetchTimer = null;
    jQuery(selector).parent().find("#feed-status-display").html("");

    results = jQuery.parseJSON(res);
    //Show results
    if (results.url.length > 0) {
        jQuery(selector).parent().find("#feed-status-display").html("&nbsp;");
        //window.open(results.url);
    }
    if (results.url.length > 0){
        var html = "<span>"+results.errors+"</span><br>";
        html += "If you have any trouble rectifying errors. Please <a href='https://www.exportfeed.com/contact/' target='_blank'>contact us</a>.";    
        // var html = '<a href="'+results.url+'" target="_blank">Click here</a> to view  your feed.'
        // jQuery(selector).parent().find("#feed-status-display").html(results.errors);
        jQuery(selector).parent().find("#feed-status-display").html(html);
    }

    return results;
}

function parseUploadFeedResults(res, provider) {

    //Stop the intermediate status interval
    window.clearInterval(feedFetchTimer);
    feedFetchTimer = null;
    jQuery('#feed-error-display2').html("");
    jQuery('#feed-status-display2').html("Uploading feed...");

    var results = jQuery.parseJSON(res);

    //Show results
    if (results.url.length > 0) {
        jQuery('#feed-error-display2').html("&nbsp;");
        window.open(results.url);
        var data = {
            content: results.url,
            provider: provider,
            action: cpf.action,
            security: cpf.cpf_nonce,
            feedpath: cmdUploadFeed
        };
        jQuery('.remember-field').each(function () {
            data[this.name] = this.value;
        });

        /** DO INVENTORY UPLOAD HERE **/
        if (provider == 'amazonsc') {
            jQuery.ajax({
                type: 'post',
                url: ajaxurl,
                data: data,
                success: function (result) {
                    console.log('success');
                    console.log(result);
                    parseUploadFeedResultStatus(data, result);
                },
                error: function (result) {
                    console.log('error');
                    console.log(result);
                }
            });
        } else if (provider == 'ebayupload') {
            jQuery.ajax({
                type: 'post',
                url: ajaxurl,
                data: data,
                success: function (result) {
                    console.log('success');
                    console.log(result);
                    parseUploadFeedResultStatus(data, result);
                },
                error: function (result) {
                    console.log('error');
                    console.log(result);
                }
            });
        } else console.log(provider);
    }
    if (results.errors.length > 0) {
        jQuery('#feed-error-display2').html(results.errors);
        jQuery('#feed-status-display2').html("");
    }
}

function parseUploadFeedResultStatus(data, id) {
    data.action = cpf.action;
    data.security = cpf.cpf_nonce;
    data.feedpath = cmdUploadFeedStatus;

    if (data.provider == 'amazonsc') {
        if (isNaN(result)) {
            var errors = JSON.parse(result);
            jQuery('#feed-status-display2').html("");
            jQuery('#feed-error-display2').html("ERROR: " + errors['Caught Exception']);
        } else {
            data['feedid'] = result;
            jQuery.ajax({
                type: 'post',
                url: ajaxurl,
                data: data,
                success: function (result) {
                    console.log('success');
                    console.log(result);
                    jQuery('#feed-status-display2').html(result);
                },
                error: function (result) {
                    console.log('error');
                    console.log(result);
                }
            });
        }
    } else if (data.provider == 'ebayupload') {
        console.log(result);
    }
}

function parseGetFeedStatus(res) {
    if (feedFetchTimer != null)

        jQuery('#feed-status-display').html(res);
}

function parseUploadFeedStatus(res) {
    if (feedFetchTimer != null)
        jQuery('#feed-status-display2').html(res);
}

function parseLicenseKeyChange(res) {
    jQuery("#tblLicenseKey").remove();
}

function parseSelectFeedChange(res) {
    jQuery('#feedPageBody').html(res);
    doFetchLocalCategories();
}

function parseUpdateSetting(res) {
    jQuery('#updateSettingMsg').html(res);
}

function parseUpdateSetting_custom(res) {
    jQuery('#updateCustomSettingMessage').html(res);
}
function doEraseMappings(service_name) {
    var r = confirm("This will clear your current Attribute Mappings including saved Maps from previous attributes. Proceed?");
    if (r == true) {
        jQuery.ajax({
            type: "post",
            url: ajaxurl,
            data: {
                service_name: service_name,
                feedpath: cmdMappingsErase,
                security: cpf.cpf_nonce,
                action: cpf.action
            },
            success: function (res) {
                showEraseConfirmation(res)
            }
        });
        //window.location.reload();
    }
}

function doFetchCategory(service_name, partial_data) {
    var shopID = jQuery("#edtRapidCartShop").val();
    if (shopID == null)
        shopID = "";
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            service_name: service_name,
            partial_data: partial_data,
            shop_id: shopID,
            feedpath: cmdFetchCategory,
            action: cpf.action,
            security: cpf.cpf_nonce
        },
        success: function (res) {
            parseFetchCategoryResult(res)
        }
    });
}

function doFetchCategory_timed(service_name, partial_data) {
    if (!category_lookup_timer) {
        window.clearTimeout(category_lookup_timer);
    }

    category_lookup_timer = setTimeout(function () {
        if (service_name == 'Miinto' || service_name == 'MiintoBrand') {
            doFetchCategory_Miinto(service_name, partial_data);
        } else {
            doFetchCategory(service_name, partial_data)
        }
    }, 100);
}

function doFetchCategory_Miinto(service_name, partial_data) {
    var cmdFetchMiintoCategory = "core/ajax/wp/fetch_miinto_category.php";
    var cpf_miinto_country = jQuery("#cpf_miinto_feed_country_list").val();
    var thisDate = new Date();
    feedIdentifier = thisDate.getTime();
    var shopID = jQuery("#edtRapidCartShop").val();
    if (shopID == null)
        shopID = "";
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            service_name: service_name,
            partial_data: partial_data,
            shop_id: shopID,
            country_code: cpf_miinto_country,
            security: cpf.cpf_nonce,
            action: cpf.action,
            feedpath: cmdFetchMiintoCategory

        },
        success: function (res) {
            parseFetchCategoryResult(res)
        }
    });
}


function doFetchCategory_timed_custom(service_name, partial_data) {
    if (!category_lookup_timer) {
        window.clearTimeout(category_lookup_timer);
    }

    category_lookup_timer = setTimeout(function () {
        if (service_name == 'Miinto' || service_name == 'MiintoBrand') {
            doFetchCategory_Miinto_1(service_name, partial_data);
        } else {
            doFetchCategory_1(service_name, partial_data);
        }
    }, 100);
}

function doFetchLocalCategories() {
    var shopID = jQuery("#edtRapidCartShop").val();
    if (shopID == null)
        shopID = "";

    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            shop_id: shopID,
            security: cpf.cpf_nonce,
            action: cpf.action,
            feedpath: cmdFetchLocalCategories
        },
        success: function (res) {
            parseFetchLocalCategories(res)
        }
    });
}

function parseFetchLocalCategories_1(res) {
    localCategories_custom = jQuery.parseJSON(res);
    chosen_merchant = jQuery("#selectFeedType").val();
    var html = '';
    html += '<select name="cpf_localcategories_filter" id="cpf_locacategories_filter"  style="width: 100%"><option value="">Select Category</option>';
    html += getLocalCategoryBranch_1(localCategories_custom.children, 0, chosen_merchant);
    html += '</select>';
    jQuery("#cpf_localcategory_list").html(html);
}
//jQuery(".chosen-select").chosen();
function getLocalCategoryBranch_1(branch, gap, chosen_merchant) {
    // var result = '';
    var select_html = '';
    var span = '<span style="width: ' + gap + 'px; display: inline-block;">&nbsp;</span>';
    select_html += '';
    for (var i = 0; i < branch.length; i++) {
        select_html += '' + span + '<option value="' + branch[i].id + '">' + branch[i].title + '</option>';
        select_html += getLocalCategoryBranch_1(branch[i].children, gap + 20, chosen_merchant);
    }
    return select_html;
    //return result;
}

function getLocalCategoryList(chosen_categories) {
    return getLocalCategoryBranch(localCategories.children, 0, chosen_categories);
}

function doUploadFeed(provider, service, userid) {

    jQuery('#feed-error-display2').html("Uploading feed...");
    var thisDate = new Date();
    feedIdentifier = thisDate.getTime();

    var shopID = jQuery("#edtRapidCartShop").val();
    if (shopID == null)
        shopID = "";

    var data = {
        userid: userid,
        remember: jQuery("#remember").is(":checked"),
        provider: service,
        security: cpf.cpf_nonce,
        action: cpf.action,
        feedpath: cmdRemember
    };

    jQuery('.remember-field').each(function () {
        data[this.name] = this.value;
    });

    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: data,
        success: function () {

        }
    });

    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            security: cpf.cpf_nonce,
            action: cpf.action,
            feedpath: cmdGetFeed,
            provider: provider,
            local_category: jQuery('#local_category').val(),
            remote_category: jQuery('#remote_category').val(),
            file_name: jQuery('#feed_filename').val(),
            feed_identifier: feedIdentifier,
            feed_id: feed_id,
            shop_id: shopID
        },
        success: function (res) {
            parseUploadFeedResults(res, provider)
        }
    });
    feedFetchTimer = window.setInterval(function () {
        updateUploadFeedStatus()
    }, 500);
}

function doGetFeed(provider, selector) {
    jQuery(selector).parent().find("#feed-error-display").html("Generating feed...");
    var thisDate = new Date();
    feedIdentifier = thisDate.getTime();
    var remote_category = '';
    var shopID = jQuery("#edtRapidCartShop").val();
    if (shopID == null)
        shopID = "";
    var country_code = jQuery("#cpf_miinto_feed_country_list").val();
    if (country_code == null)
        country_code = "";

    remote_category = jQuery('#remote_category').val();

    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        // dataType : 'json',
        data: {
            security: cpf.cpf_nonce,
            action: cpf.action,
            feedpath: cmdGetFeed,
            provider: provider,
            local_category: jQuery('#local_category').val(),
            remote_category: remote_category,
            remote_category_id: jQuery('#remote_category_id').val(),
            file_name: jQuery('#feed_filename_default').val(),
            feed_identifier: feedIdentifier,
            feed_id: feed_id,
            shop_id: shopID,
            country_code: country_code
        },
        success: function (res) {
            var results = parseGetFeedResults(res, selector);
            s = selector;
            var name_to_add = provider == 'Google' ? 'Google Shopping' : provider;
            var html = 'Feed created successfully. <a href="'+results.url+'" target="_blank">Click here</a> to view  your feed. ';

            if (results.errors.length == 0) {
                html += '<br><br>Follow <a href="'+results.upload_path+'" target="_blank">these instructions</a> to upload feed to the '+name_to_add+'. <br><br>';    
            }

            jQuery(selector).parent().find("#feed-error-display").hide();
            jQuery("#feed-message-display").show();
            jQuery("#cpf_feed_view").show();
            thistripnecessary = res;
            if (!results.feed_is_deleted) {
                jQuery(selector).parent().find("#feed-message-display").html(html);    
            }
            
            // jQuery(selector).parent().find("#cpf_feed_view").html('<input class="button-primary" type="button" id="cpf_view_feed" onclick="openFeed(results.url , this)" value="View Feed" style="width:50%;margin-top: 10px;">');
        }
    });
    feedFetchTimer = window.setInterval(function () {
        updateGetFeedStatus()
    }, 500);
}

function get_the_messages_of_get_feed(json_value){
    console.log(json_value.url);
}

function doGetAlternateFeed(provider, selector) {

    jQuery('#feed-error-display').html("Generating feed...");
    var thisDate = new Date();
    feedIdentifier = thisDate.getTime();

    var feeds = [];
    jQuery(".feedSetting:checked").each(function () {
        feeds.push(jQuery(this).val());
    });

    var shopID = jQuery("#edtRapidCartShop").val();
    if (shopID == null)
        shopID = "";

    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            security: cpf.cpf_nonce,
            action: cpf.action,
            feedpath: cmdGetFeed,
            provider: provider,
            local_category: "0",
            remote_category: "0",
            file_name: jQuery('#feed_filename_default').val(),
            feed_identifier: feedIdentifier,
            feed_id: feed_id,
            shop_id: shopID,
            feed_ids: feeds
        },
        success: function (res) {
            parseGetFeedResults(res, selector);
            s = selector;
            jQuery(selector).parent().find("#feed-error-display").hide();
            jQuery("#feed-message-display").show();
            jQuery("#cpf_feed_view").show();
            jQuery(selector).parent().find("#feed-message-display").html("Feed created successfully.Click View Feed to view created feed");
            jQuery(selector).parent().find("#cpf_feed_view").html('<input class="button-primary" type="button" id="cpf_view_feed" onclick="openFeed(results.url , this)" value="View Feed" style="width:50%;margin-top: 10px;">');
        }
    });
    feedFetchTimer = window.setInterval(function () {
        updateGetFeedStatus()
    }, 500);
}

function doSelectCategory_default(category, option, service_name) {
    var shopID = jQuery("#edtRapidCartShop").val();
    if (shopID == null)
        shopID = "";


    jQuery(category).parent().parent().find('#categoryDisplayText').val(category.innerHTML);
    //document.getElementById("categoryDisplayText").value = category.innerHTML;
    document.getElementById("remote_category").value = option;
    document.getElementById("categoryList").style.display = "none";
    document.getElementById("categoryList").style.border = "0px";

    if (service_name == "Amazonsc" || service_name == "kelkoo") {
        //The user has just selected a template.
        //Therefore, we must reload the Optional / Required Mappings
        jQuery.ajax({
            type: "post",
            url: ajaxurl,
            data: {
                security: cpf.cpf_nonce,
                action: cpf.action,
                feedpath: cmdFetchTemplateDetails,
                shop_id: shopID,
                template: option,
                provider: service_name
            },
            success: function (res) {
                console.log(res);
                jQuery("#attributeMappings").html(res);
            }
        });
    }
}

function doSelectLocalCategory(id) {

    //Build a list of checked boxes
    var category_string = "";
    var category_ids = "";
    jQuery(".cbLocalCategory").each(
        function (index) {
            tc = document.getElementById(jQuery(this).attr('id'));
            if (tc.checked) {
                //if (jQuery(this).attr('checked') == 'checked') {
                category_string += jQuery(this).val() + ", ";
                category_ids += jQuery(this).attr('category') + ",";
            }
        }
    );

    //Trim the trailing commas
    category_ids = category_ids.substring(0, category_ids.length - 1);
    category_string = category_string.substring(0, category_string.length - 2);

    //Push the results to the form
    jQuery("#local_category").val(category_ids);
    jQuery("#local_category_display").val(category_string);

}

function doSelectFeed() {
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            feedtype: jQuery('#selectFeedType').val(),
            security: cpf.cpf_nonce,
            action: cpf.action,
            feedpath: cmdSelectFeed
        },
        success: function (res) {
            parseSelectFeedChange(res);
            doFetchLocalCategories_custom();
        }
    });
}

function doUpdateAllFeeds(selector) {
    s = selector;
    jQuery(s).next('div.update-message').html("Updating feeds...");
    //in Joomla, this message is hidden, so unhide
    jQuery(s).next('div.update-message').css({
        "display": "block"
    });
    var cpf_manage_table_body = jQuery("#cpf_manage_table_originals");
    var feed_id = [];
    cpf_manage_table_body.find('input:checkbox').each(function (i, data) {
        if (this.checked) {
            var checked_feed_id = jQuery(this).parent().parent().find('.cpf_hidden_feed_id');
            jQuery(checked_feed_id).each(function (i, data) {
                feed_id.push(jQuery(data).val());
            });
        }
    });
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            feed_id: feed_id,
            security: cpf.cpf_nonce,
            action: cpf.action,
            feedpath: cmdUpdateAllFeeds
        },
        success: function (res) {
            jQuery(s).next('div.update-message').html(res);
        }
    });
}

function doUpdateSetting(source, settingName) {
    //Note: Value must always come last...
    //and &amp after value will be absorbed into value
    if (jQuery("#" + source).parent().find('div>label>input[type=checkbox]').attr('checked') == 'checked')
        unique_setting = '&feedid=' + feed_id;
    else
        unique_setting = '';
    var shopID = jQuery("#edtRapidCartShop").val();
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: "action=" + cpf.action + "&security=" + cpf.cpf_nonce + "&feedpath=" + cmdUpdateSetting + "&setting=" + settingName + unique_setting + "&shop_id=" + shopID + "&value=" + jQuery("#" + source).val(),
        success: function (res) {
            jQuery("#updateSettingMessage").html(res);
            jQuery("#set_interval_time").html(jQuery("#selectDelay option:selected").html());
            parseUpdateSetting(res);

        }
    });
}

function getLocalCategoryBranch(branch, gap, chosen_categories) {
    var result = '';
    var span = '<span style="width: ' + gap + 'px; display: inline-block;">&nbsp;</span>';
    for (var i = 0; i < branch.length; i++) {
        if (jQuery.inArray(branch[i].id, chosen_categories) > -1)
            checkedState = ' checked="true"';
        else
            checkedState = '';
        result += '<div>' + span + '<input type="checkbox" class="cbLocalCategory" id="cbLocalCategory' + branch[i].id + '" value="' + branch[i].title +
            '" onclick="doSelectLocalCategory(' + branch[i].id + ')" category="' + branch[i].id + '"' + checkedState + ' />' + branch[i].title + '(' + branch[i].tally + ')</div>';
        result += getLocalCategoryBranch(branch[i].children, gap + 20, chosen_categories);
    }
    return result;
}

function getLocalCategoryList(chosen_categories) {
    return getLocalCategoryBranch(localCategories.children, 0, chosen_categories);
}

function geteBayCategoryList() {
    var html;
    var cmdFetcheBayCategory = 'core/ajax/wp/fetch_ebay_category.php';
    var loading = document.getElementById('loading-gif');
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            service_name: 'ebaySeller',
            security: cpf.cpf_nonce,
            action: cpf.action,
            feedpath: cmdFetcheBayCategory
        },
        dataType: "html",
        success: function (res) {
            jQuery("#loading-gif").css('display', 'none');
            document.getElementById('eBayCategoryList').innerHTML = res;
        },
        error: function () {
            html += '<div class="error">No Category Found.</div>'
        }
    });
    return html;

}

function fetchChildCategory(parent_id, selector) {
    if (jQuery(selector).hasClass('active')) {
        jQuery("#child-" + parent_id).css('display', 'none');
        jQuery(selector).removeClass("dashicons dashicons-arrow-down-alt2");
        jQuery(selector).addClass("dashicons dashicons-arrow-right-alt2");
        jQuery(selector).removeClass('active');
        return;
    }

    jQuery(selector).addClass('active');
    var html;
    var cmdFetcheBayCategory = 'core/ajax/wp/fetch_ebay_category.php';
    var result = '';
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            service_name: 'ebaySeller',
            parent_id: parent_id,
            security: cpf.cpf_nonce,
            action: cpf.action,
            feedpath: cmdFetcheBayCategory
        },
        dataType: "html",
        success: function (res) {
            if (jQuery(selector).hasClass('active')) {
                jQuery(selector).removeClass("dashicons-arrow-right-alt2");
                jQuery(selector).addClass("dashicons dashicons-arrow-down-alt2");
            }
            jQuery("#child-" + parent_id).css('display', 'block');
            document.getElementById('child-' + parent_id).innerHTML = res;
        },
        error: function () {
            html += '<div class="error">No Category Found.</div>'
        }
    });

    return html;
}

function doSelecteBayCategories(id) {
    var selectCategory = document.getElementById('hiddenCategoryName-' + id).value;
    selectCategory = selectCategory.split(':');
    selectCategory = selectCategory.join(">");
    document.getElementById('categoryDisplayText').value = selectCategory;
    document.getElementById('categoryDisplayText').innerHTML = selectCategory;
    document.getElementById('remote_category').value = selectCategory + ':' + id;
    document.getElementById('remote_category_id').value = id;
    parent.jQuery.colorbox.close();

}

function searsPostByRestAPI() {
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            username: jQuery("#edtUsername").val(),
            password: jQuery("#edtPassword").val(),
            security: cpf.cpf_nonce,
            action: cpf.action,
            feedpath: cmdSearsPostByRestAPI
        },
        success: function (res) {
            searsPostByRestAPIResults(res)
        }
    });
}

function searsPostByRestAPIResults(res) {

}

function setAttributeOption(service_name, attribute, select_index) {
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            service_name: service_name,
            attribute: attribute,
            mapto: jQuery('#attribute_select' + select_index).val(),
            security: cpf.cpf_nonce,
            action: cpf.action,
            feedpath: cmdSetAttributeOption
        }
    });
}

function setAttributeOptionV2(sender) {
    var service_name = jQuery(sender).attr('service_name');
    var attribute_name = jQuery(sender).val();
    var mapto = jQuery(sender).attr('mapto');
    var shopID = jQuery("#edtRapidCartShop").val();
    if (shopID == null)
        shopID = "";
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            service_name: service_name,
            attribute: attribute_name,
            mapto: mapto,
            shop_id: shopID,
            security: cpf.cpf_nonce,
            action: cpf.action,
            feedpath: cmdSetAttributeUserMap
        }
    });
}

function submitLicenseKey(keyname) {
    var r = alert("License field will disappear if key is successful. Please reload the page.");
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            setting: keyname,
            value: jQuery("#edtLicenseKey").val(),
            security: cpf.cpf_nonce,
            action: cpf.action,
            feedpath: cmdUpdateSetting
        },
        success: function (res) {
            parseLicenseKeyChange(res)
        }
    });
    //window.location.reload(1);
}

function showEraseConfirmation(res) {
    //alert("Attribute Mappings Cleared"); //Dropped message and just reloaded instead
    if (document.getElementById("selectFeedType") == null)
        jQuery(".attribute_select").val("");
    else
        doSelectFeed();
}

function showLocalCategories(provider) {
    chosen_categories = jQuery("#local_category").val();
    chosen_categories = chosen_categories.split(",");
    jQuery.colorbox({
        close: "Close", overlayClose: true,
        html: "<div class='categoryListLocalFrame'><div class='categoryListLocal'><h1>Categories</h1>" + getLocalCategoryList(chosen_categories) + "</div></div>"
    });
}

function showeBayCategories(service_name) {
    jQuery.colorbox({
        width: "500",
        height: "500px",
        html: "<div class='categoryListeBayFrame'><div class='categoryeBayRemote'><h1>eBay Categories</h1><div id='loading-gif' style='margin-left: 75px;margin-top: 100px'><img src='http://localhost/wordpress_1/wp-admin/images/loading_1.gif' /> </div><div id='eBayCategoryList'></div>" + geteBayCategoryList(this) + "</div>"
    });
}

function toggleAdvancedDialog() {
    toggleButton = document.getElementById("toggleAdvancedSettingsButton");
    if (jQuery(toggleButton).val() == 'Open Advanced Commands') {
        toggleButton.value = "Close Advance Command";
        document.getElementById("feed-advanced").style.display = "inline";
    } else {
        toggleButton.value = "Open Advanced Commands";
        document.getElementById("feed-advanced").style.display = "none";
    }
}

function toggleAdvancedDialogDeafult() {
    toggleButton = document.getElementById("toggleAdvancedSettingsButtonDefault");
    if (jQuery(toggleButton).val() == 'Open Advanced Commands') {
        toggleButton.value = "Close Advance Command";
        document.getElementById("feed-advanced-default").style.display = "inline";
    } else {
        toggleButton.value = "Open Advanced Commands";
        document.getElementById("feed-advanced-default").style.display = "none";
    }
}
function toggleOptionalAttributes(selector) {
    toggleButton_opt = document.getElementById("optional-attributes");
    if (toggleButton_opt.style.display == "none") {
        jQuery("#optional-attributes").slideDown();
        jQuery(selector).val('Hide Additional Attributes');
        //document.getElementById("optional-attributes").style.display = "inline";
    } else {
        jQuery("#optional-attributes").slideUp();
        jQuery(selector).val('Show Additional Attributes');
        //document.getElementById("optional-attributes").style.display = "none";
    }

}//toggleOptionalAttributes

function toggleRequiredAttributes(selector) {
    toggleButton = document.getElementById("required-attributes");

    if (toggleButton.style.display == "none") {
        //Open the dialog
        jQuery("#required-attributes").slideDown();
        jQuery(selector).val('Hide Required Attributes');
        // document.getElementById("required-attributes").style.display = "inline";
    } else {
        //Close the dialog
        jQuery("#required-attributes").slideUp();
        jQuery(selector).val('Show Required Attributes');
        //document.getElementById("required-attributes").style.display = "none";
    }
}//toggleRequiredAttributes
function updateGetFeedStatus() {
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            feed_identifier: feedIdentifier,
            security: cpf.cpf_nonce,
            action: cpf.action,
            feedpath: cmdGetFeedStatus
        },
        success: function (res) {
            parseGetFeedStatus(res)
        }
    });
}

//Custom Feed JS-Subash
function doFetchLocalCategories_custom() {
    var shopID = jQuery("#edtRapidCartShop").val();
    if (shopID == null)
        shopID = "";

    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            shop_id: shopID,
            security: cpf.cpf_nonce,
            action: cpf.action,
            feedpath: cmdFetchLocalCategories_custom
        },
        success: function (res) {
            parseFetchLocalCategories_1(res)
        }
    });
}

function doFetchCategory_1(service_name, selector) {
    var partial_data = selector.value;
    var shopID = jQuery("#edtRapidCartShop").val();
    if (shopID == null)
        shopID = "";
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            service_name: service_name,
            partial_data: partial_data,
            shop_id: shopID,
            security: cpf.cpf_nonce,
            action: cpf.action,
            feedpath: cmdFetchCategory_custom
        },
        success: function (res) {
            parseFetchCategoryResult_1(res, selector)
        }
    });
}

function doFetchCategory_Miinto_1(service_name, selector) {
    var partial_data = selector.value;
    var cmdFetchMiintoCategory_custom = "core/ajax/wp/fetch_miinto_category_custom.php";
    var cpf_miinto_country = jQuery("#cpf_miinto_feed_country_list").val();
    var thisDate = new Date();
    feedIdentifier = thisDate.getTime();
    var shopID = jQuery("#edtRapidCartShop").val();
    if (shopID == null)
        shopID = "";
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            service_name: service_name,
            partial_data: partial_data,
            shop_id: shopID,
            country_code: cpf_miinto_country,
            security: cpf.cpf_nonce,
            action: cpf.action,
            feedpath: cmdFetchMiintoCategory_custom
        },
        success: function (res) {
            parseFetchCategoryResult_1(res, selector)
        }
    });
}

function parseFetchCategoryResult_1(res, selector) {
    jQuery('.no_remote_category').css('display', 'none');
    var list = jQuery(selector).parent().siblings('.categoryList');
    //console.log(list);
    jQuery(list).html(res);
    if (res.length > 0) {
        jQuery(list).css('border', '1px solid #A5ACB2');
        jQuery(list).css('display', 'inline');
    } else {
        jQuery(list).css('border', '0px');
        jQuery(list).css('display', 'none');
        jQuery(list).value = "";
    }
}

function doGetCustomFeed(provider, selector) {
    s = selector;
    if (jQuery('#feed_filename').val() == '') {
        alert("Please provide filename");
        jQuery('#feed_filename').focus();
        return;
    }
    jQuery('#feed-error-display').html("Generating feed...");
    var thisDate = new Date();
    feedIdentifier = thisDate.getTime();

    //var remote_category = jQuery("input[name='cpf_1']").val();
    var shopID = jQuery("#edtRapidCartShop").val();
    if (shopID == null)
        shopID = "";
    var country_code = jQuery("#cpf_miinto_feed_country_list").val();
    if (country_code == null)
        country_code = "";
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            provider: provider,
            feedLimit: jQuery("#cpf_feed_output_limit").val(),
            file_name: jQuery('#feed_filename').val(),
            feed_identifier: feedIdentifier,
            feed_id: feed_id,
            shop_id: shopID,
            country_code: country_code,
            security: cpf.cpf_nonce,
            action: cpf.action,
            feedpath: cmdGetCustomFeed

        },
        success: function (res) {
            var results = parseGetFeedResults(res, selector);
            jQuery("#feed-error-display").hide();
            jQuery("#feed-message-display").show();
            jQuery("#cpf_feed_view").show();
            jQuery(selector).parent().find("#feed-message-display").html("Feed created successfully.Click View Feed to view created feed");
            jQuery(selector).parent().find("#cpf_feed_view").html('<input class="button-primary" type="button" id="cpf_view_feed" onclick="openFeed(results.url , this)" value="View Feed" style="width:65%;margin-top: 10px;">');

        }
    });
    feedFetchTimer = window.setInterval(function () {
        updateGetFeedStatus()
    }, 500);
}

function openFeed(url, selector) {
    //window.location.reload();
    s = selector;
    var merchant = jQuery("#selectFeedType").val();
    jQuery(s).parent().parent().find("#feed-message-display").hide();
    jQuery(s).parent().hide();
    window.open(url);
    doSelectFeed(merchant);
}

function doSelectCategory_custom(category, option, service_name) {
    var shopID = jQuery("#edtRapidCartShop").val();
    if (shopID == null)
        shopID = "";
    cat = category;
    jQuery(cat).parent().parent().find('.text_big').val(category.innerHTML);
    //jQuery(category).parent().siblings('.text_big').val(category.innerHTML);
    //jQuery(category).html(res);
    // document.getElementById("categoryDisplayText").value = category.innerHTML;
    document.getElementById("remote_category").value = option;
    jQuery(cat).parent().parent().find('.categoryList').css('display', 'none');
    jQuery(cat).parent().parent().find('.categoryList').css('display', 'none');

    if (service_name == "Amazonsc" || service_name == "kelkoo") {
        //The user has just selected a template.
        //Therefore, we must reload the Optional / Required Mappings
        jQuery.ajax({
            type: "post",
            url: ajaxurl,
            data: {
                shop_id: shopID,
                template: option,
                provider: service_name,
                security: cpf.cpf_nonce,
                action: cpf.action,
                feedpath: cmdFetchTemplateDetails
            },
            success: function (res) {
                jQuery("#attributeMappings").html(res);
            }
        });
    }
}

function doCustomFeedSetting(source) {
    //Note: Value must always come last...
    //and &amp after value will be absorbed into value
    var feed_body = jQuery("#cpf_custom_feed_config_body");
    var cpf_merchant_attr = [];
    var cpf_feed_prefix = [];
    var cpf_feed_suffix = [];
    var cpf_feed_type = [];
    var cpf_feed_value_default = [];
    var cpf_feed_value_custom = [];

    jQuery(feed_body).find(".cpf_merchantAttributes").each(function (i, data) {
        cpf_merchant_attr.push(jQuery(data).val());
    });
    jQuery(feed_body).find(".cpf_prefix").each(function (i, data) {
        cpf_feed_prefix.push(jQuery(data).val());
    });

    jQuery(feed_body).find(".cpf_suffix").each(function (i, data) {
        cpf_feed_suffix.push(jQuery(data).val());
    });
    jQuery(feed_body).find(".cpf_change_type").each(function (i, data) {
        cpf_feed_type.push(jQuery(data).val());
    });
    var attr_html = jQuery("#cpf-sort_config").find(".attribute_select");
    var attr_custom = jQuery("#cpf-sort_config").find(".cpf_custom_value_attr");

    jQuery(attr_html).each(function (i, data) {
        //console.log(jQuery(data).val());
        cpf_feed_value_default.push(jQuery(data).val());
    });

    jQuery(attr_custom).each(function (i, data) {
        //console.log(jQuery(data).val());
        cpf_feed_value_custom.push(jQuery(data).val());
    });


    var shopID = jQuery("#edtRapidCartShop").val();
    s = source;
    var settingName = jQuery(source).parent().find("input[name='cpf_custom_merchant_type']").val();
    var feedLimit = jQuery("#cpf_feed_output_limit").val();
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            security: cpf.cpf_nonce,
            action: cpf.action,
            feedpath: cmdUpdateFeedConfig,
            setting: settingName,
            feedLimit: feedLimit,
            shop_id: shopID,
            cpf_merchant_attr: cpf_merchant_attr,
            cpf_feed_prefix: cpf_feed_prefix,
            cpf_feed_suffix: cpf_feed_suffix,
            cpf_feed_type: cpf_feed_type,
            cpf_feed_value_default: cpf_feed_value_default,
            cpf_feed_value_custom: cpf_feed_value_custom
        },
        success: function (res) {
            parseUpdateSetting_custom(res)
        }
    });
}

function submitForm(service_name, type) {
    var feedForm = jQuery("#cpf-custom-feed-form");
    jQuery(feedForm).find('.spinner').css('visibility', 'visible');
    jQuery(feedForm).find('.cpf_search_info').css('display', 'block');

    var lowerlimit = jQuery("#cpf_page_hidden_first").val();
    var upperlimit = jQuery("#cpf_page_hidden_page_item").val();
    var limit = lowerlimit + ',' + upperlimit;

    var keywords = jQuery("#cpf_keywords-filter").val();
    var category = jQuery("#cpf_locacategories_filter").val();
    var brand = jQuery("#cpf_brand-filter").val();
    var sku = jQuery("#cpf_sku_filter").val();
    var merchat_type = jQuery("#selectFeedType").val();

    var cpf_price_range;
    var price_option = jQuery("#cpf_price_filter_option").val();
    var cpf_less_than = jQuery("#cpf_price_filter_less_than");
    var cpf_more_than = jQuery("#cpf_price_filter_more_than");
    var cpf_first_price = jQuery("#cpf_price_filter_in_between_first");
    var cpf_second_price = jQuery("#cpf_price_filter_in_between_second");
    if (price_option == 'less_than') {
        if (cpf_less_than.val() == '') {
            alert("Enter amount");
            return;
        }
        cpf_price_range = "<=" + cpf_less_than.val();
    }
    if (price_option == 'more_than') {
        if (cpf_more_than.val() == '') {
            alert("Enter amount");
            return;
        }
        cpf_price_range = ">=" + cpf_more_than.val();
    }
    if (price_option == 'in_between') {
        if (cpf_first_price.val() == '') {
            alert("Enter first amount");
            return;
        }
        if (cpf_second_price.val() == '') {
            alert("Enter first amount");
            return;
        }
        var cpf_price_range_first = cpf_first_price.val();
        var cpf_price_range_second = cpf_second_price.val();
        if (cpf_price_range_first > cpf_price_range_second) {
            alert("Price range is not valid.First amount should be less than second amount");
            return;
        }
        cpf_price_range = cpf_price_range_first + '-' + cpf_price_range_second;
    }
    jQuery("#cpf-no-products-search").remove();
    jQuery.ajax({
        type: "POST",
        url: ajaxurl,
        data: {
            security: cpf.cpf_nonce,
            action: cpf.action,
            feedpath: cmdSearchProductAjax,
            keywords: keywords,
            category: category,
            brand: brand,
            sku: sku,
            price_range: cpf_price_range,
            merchat_type: merchat_type,
            service_name: service_name,
            limit: limit,
            q: 'search'
        },
        dataType: 'JSON',
        success: function (data) {
            jQuery(feedForm).find('.spinner').css('visibility', 'hidden');
            jQuery(feedForm).find('.cpf_search_info').css('display', 'none');
            if (type == 1) {
                jQuery("#cpf_load_more_pagination").find(".spinner").css('visibility', 'hidden');
            }
            var $_tr = jQuery("#cpf-the-list");
            jQuery("#cpf-no-results").remove();
            jQuery("#cpf_load_more_pagination").show();
            var html_1 = $_tr.html();
            $_tr.html(data.html + html_1);
            console.log(data.count);
            if (data.count == 0) {
                jQuery("#cpf_load_more_pagination").hide();
            }
            jQuery("#cpf-generate-table").show();
            jQuery($_tr).parent().parent().find(".tablenav").show();
            var divPosition = jQuery("#postbox-container-2").offset();
            jQuery('html, body').animate({scrollTop: divPosition.top}, "slow");
        }
    });

}

function selectFilters(val) {
    jQuery($_this).val(val);
    jQuery(suggestion_box).hide();
}

function loadMore(selector) {
    var firstLimitHtml = jQuery("#cpf_page_hidden_second");
    var pageLimit = jQuery("#cpf_page_hidden_page_item").val();
    var firstLimit = firstLimitHtml.val();
    jQuery("#cpf_page_hidden_first").val(firstLimit);
    var secondLimit = parseInt(firstLimit) + parseInt(pageLimit);
    firstLimitHtml.val(secondLimit);
    jQuery("#cpf_load_more_pagination").find(".spinner").css('visibility', 'visible');
    submitForm('', 1);
    //jQuery("#cpf_load_more_pagination").find(".spinner").css('visibility','hidden');

}

function moveSelected(e) {
    var b = false;
    var tBody = jQuery("#cpf-the-list");
    if ((tBody.find("input:checkbox:checked").length) == 0) {
        alert("Please select atleast one product from product search list.");
        return false;
    }
    tBody.find('input:checkbox').each(function (i, data) {
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
                url: ajaxurl,
                data: {
                    security: cpf.cpf_nonce,
                    action: cpf.action,
                    feedpath: cmdSaveProductAjax,
                    local_cat_ids: cpf_selected_local_cat_ids,
                    product_id: cpf_selected_product_id,
                    product_title: cpf_selected_product_title,
                    category_name: cpf_selected_product_cat_names,
                    product_type: cpf_selected_product_type,
                    product_attributes: cpf_selected_product_attributes_details,
                    product_variation_ids: cpf_selected_product_variation_ids,
                    remote_category: remote_category,
                    q: 'savep'
                },
                success: function (res) {
                    jQuery(".move-search-products").find(".spinner").css('visibility', 'hidden');
                }
            });
        }
        var feed_id = '';
        showSelectedProductTables(feed_id);
    });
}

function showSelectedProductTables(feed_id) {
    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
            params: 'listTables',
            feed_id: feed_id,
            security: cpf.cpf_nonce,
            action: cpf.action,
            feedpath: cmdShowProductAjax,
            q: 'showT'
        },
        success: function (res) {
            jQuery("#cpf-the-list_1").html((res));
        }
    });
}

function cpf_remove_feed(row) {
    // console.log(row);
    if (confirm("Are you sure you want to deleted this feed?")) {
        t = row;
        jQuery(t).parent().find('.spinner').css('visibility', 'visible');
        var product_id = jQuery(t).parent().parent().find(".cpf_feed_id_hidden").html();
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                id: product_id,
                security: cpf.cpf_nonce,
                action: cpf.action,
                feedpath: cmdDeleteProductAjax,
                q: 'delR'
            },
            success: function (res) {
                console.log("Deleted successsfully");
                jQuery(t).parent().find('.spinner').css('visibility', 'hidden');
                var feed_id = '';
                showSelectedProductTables(feed_id);
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

function selectAllProducts(selector) {
    var checked = jQuery("#cpf_select_all_checkbox").attr('checked');
    if (checked == 'checked') {
        jQuery("#cpf-the-list").find("input[type=checkbox]").attr('checked', true);
    } else {
        jQuery("#cpf-the-list").find("input[type=checkbox]").removeAttr('checked');
    }
}


function selectAllProducts_1(selector) {
    var checked = jQuery("#cpf_select_all_checkbox_1").attr('checked');
    if (checked == 'checked') {
        jQuery("#cpf-the-list_1").find("input[type=checkbox]").attr('checked', true);
    } else {
        jQuery("#cpf-the-list_1").find("input[type=checkbox]").removeAttr('checked');
    }
}

function cpf_check_all_feeds(selector) {
    var checked = jQuery("#cpf_select_all_feed").attr('checked');
    var tbody = jQuery("#cpf_manage_table_originals");
    if (checked == 'checked') {
        tbody.find("input[type=checkbox]").attr('checked', true);
    } else {
        tbody.find("input[type=checkbox]").removeAttr('checked');
    }
}

function cpf_check_all_feeds_1(selector) {
    // console.log(selector);
    var checked_1 = jQuery("#cpf_select_all_feed_1").attr('checked');
    var tbody_1 = jQuery("#cpf_manage_table_originals");
    t = tbody_1;
    if (checked_1 == 'checked') {
        tbody_1.find("input[type=checkbox]").attr('checked', true);
    } else {
        tbody_1.find("input[type=checkbox]").removeAttr('checked');
    }
}

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
    var cpf_custom_feed_config = jQuery("#cpf_custom_feed_config_body tr");
    var tr_length = cpf_custom_feed_config.length;
    if (tr_length == 1) {
        cpf_custom_feed_config.find("span.dashicons-trash").removeAttr('onclick');
    }
    var parent = jQuery(selector).parent().parent();
    jQuery(parent).remove();
}

function cpf_changeType(selector) {
    t = selector;
    console.log(selector.value);
    if (selector.value == 1) {
        //jQuery(t).parent().parent().find(".attribute_select").removeAttr('selected');
        jQuery(t).parent().parent().find(".cpf_custom_value_span").show();
        jQuery(t).parent().parent().find(".cpf_custom_value_attr").show();
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

function toggleFeedSettings() {
    var cpf_feed_config = jQuery("#cpf_custom_feed_config");
    var cpf_advance_section = jQuery("#cpf_advance_section");
    var display = cpf_feed_config.css('display');
    var event = jQuery("#cpf_feed_config_link");
    var advance_section = cpf_advance_section.css('display');
    var advance_section_button = jQuery("#cpf_advance_section_link");
    if (advance_section == 'block') {
        cpf_advance_section.slideUp();
        jQuery("#feed-advanced").slideUp();
        // jQuery("#bUpdateSetting").slideUp();
        jQuery(advance_section_button).attr('title', 'This will open feed advance command section where you can customize your feed using advanced command.');
        jQuery(advance_section_button).val('Show advance Command section');
    }
    if (display == 'none') {
        jQuery("#cpf_feed_config_desc").slideDown();
        cpf_feed_config.slideDown();
        jQuery(event).val('Hide Feed Config');
        jQuery(event).attr('title', 'Hide Feed config section');
        /* var divPosition = jQuery("#cpf_custom_feed_config").offset();
         jQuery('#custom_feed_settingd').animate({scrollBottom: divPosition.top}, "slow");*/
    }
    if (display == 'block') {
        cpf_feed_config.slideUp();
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
            url: ajaxurl,
            data: {
                id: feed_id,
                q: 'delR',
                action: cpf.action,
                security: cpf.cpf_nonce,
                feedpath: cmdDeleteProductAjax
            },
            success: function (res) {
                jQuery(selector).parent().parent().find(".spinner").css('visibility', 'hidden');
                jQuery(selector).parent().parent().find("#cpf_deleted_selected_from_list").html(checked_box_length + " Product(s) deleted successfully.");
                jQuery(selector).parent().parent().find("#cpf_deleted_selected_from_list").fadeOut(3000);
                jQuery(selector).parent().parent().parent().parent().parent().find("#cpf_select_all_checkbox_1").removeAttr('checked');
                var feed_id = '';
                showSelectedProductTables(feed_id);

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

function toggleAdvanceCommandSection(event) {
    var cpf_custom_feed = jQuery("#cpf_custom_feed_config");
    var cpf_advance_section = jQuery("#cpf_advance_section");
    var feed_config = cpf_custom_feed.css('display');
    var feed_config_button = jQuery("#cpf_feed_config_link");

    //First slideUp feed config section if displayed
    if (feed_config == "block") {
        cpf_custom_feed.slideUp();
        jQuery("#cpf_feed_config_desc").slideUp();
        jQuery(feed_config_button).attr('title', 'This will open feed config section below.You can provide suffix and prefix for the attribute to be included in feed.');
        jQuery(feed_config_button).val('Show Feed Config');
    }

    var display = cpf_advance_section.css('display');
    if (display == 'none') {
        cpf_advance_section.slideDown();
        jQuery(event).val('Hide Advance Section');
        jQuery(event).attr('title', 'Hide Feed config section');
        /* var divPosition = jQuery("#cpf_custom_feed_config").offset();
         jQuery('#custom_feed_settingd').animate({scrollBottom: divPosition.top}, "slow");*/
    }
    if (display == 'block') {
        cpf_advance_section.slideUp();
        jQuery("#feed-advanced").slideUp();
        // jQuery("#bUpdateSetting").slideUp();
        jQuery(event).attr('title', 'This will open feed advance command section where you can customize your feed using advanced command.');
        jQuery(event).val('Show advance Command section');
    }
}

function toggleAdvanceCommandSectionDefault(event) {
    var cpf_advance_section_default = jQuery("#cpf_advance_section_default");
    var display = cpf_advance_section_default.css('display');
    if (display == 'none') {
        cpf_advance_section_default.slideDown();
        jQuery(event).val('Hide Advance Section');
        jQuery(event).attr('title', 'Hide Feed config section');
        /* var divPosition = jQuery("#cpf_custom_feed_config").offset();
         jQuery('#custom_feed_settingd').animate({scrollBottom: divPosition.top}, "slow");*/
    }
    if (display == 'block') {
        cpf_advance_section_default.slideUp();
        jQuery("#feed-advanced-default").slideUp();
        // jQuery("#bUpdateSetting").slideUp();
        jQuery(event).attr('title', 'This will open feed advance command section where you can customize your feed using advanced command.');
        jQuery(event).val('Show advance Command section');
    }
}

function loadCustomFeedSection(feed_type) {

    var cpf_feeds_by_cats = jQuery("#cpf-feeds_by_cats");
    var category_feeds = jQuery("#cpf_feeds_by_category");
    var cpf_advance_command_default = jQuery("#cpf_advance_command_default");
    var cpf_custom_feed_generation = jQuery("#cpf-custom_feed_generation");
    var feed_right = jQuery(".feed-right");
    var cpf_custom_feed = jQuery("#cpf-custom-feed");

    if (feed_type == 1) {
        category_feeds.hide();
        feed_right.hide();
        cpf_advance_command_default.hide();
        cpf_custom_feed_generation.show();
    }
    if (feed_type == 0) {
        category_feeds.show();
        feed_right.show();
        cpf_advance_command_default.show();
        cpf_custom_feed_generation.hide();
    }
    if ((feed_type == '' || feed_type == -1)) {
        category_feeds.hide();
        feed_right.hide();
        cpf_advance_command_default.hide();
        cpf_custom_feed_generation.show();
    }

    cpf_feeds_by_cats.click(function () {

        category_feeds.show();
        cpf_advance_command_default.show();
        feed_right.show();
        if (category_feeds.css('display') == 'block') {
            cpf_feeds_by_cats.addClass('nav-tab-active');
            cpf_custom_feed.removeClass('nav-tab-active');
        }
        cpf_custom_feed_generation.hide();
    });

    cpf_custom_feed.click(function () {
        category_feeds.hide();
        feed_right.hide();
        cpf_advance_command_default.hide();
        cpf_custom_feed_generation.show();
        if (cpf_custom_feed_generation.css('display') == 'block') {
            cpf_custom_feed.addClass('nav-tab-active');
            cpf_feeds_by_cats.removeClass('nav-tab-active');
        }
    });
    if (cpf_custom_feed_generation.css('display') == 'block') {
        cpf_custom_feed.addClass('nav-tab-active');
        cpf_feeds_by_cats.removeClass('nav-tab-active');
    }

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
                url: ajaxurl,
                data: {
                    keyword: searchterm,
                    searchfilters: searchfilters,
                    security: cpf.cpf_nonce,
                    action: cpf.action,
                    feedpath: cmdGetProductAjax,
                    q: 'ajax'
                },
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
    jQuery("#cpf_price_filter_option").on('change', function () {
        var price_option = jQuery("#cpf_price_filter_option").val();
        var cpf_price_less_than = jQuery("#cpf_price_filter_less_than");
        var cpf_price_more_than = jQuery("#cpf_price_filter_more_than");
        var cpf_price_first = jQuery("#cpf_price_filter_in_between_first");
        var cpf_price_second = jQuery("#cpf_price_filter_in_between_second");
        if (price_option == '') {
            cpf_price_less_than.hide();
            cpf_price_more_than.hide();
            cpf_price_first.hide();
            cpf_price_second.hide();
            cpf_price_less_than.val('');
            cpf_price_more_than.val('');
            cpf_price_first.val('');
            cpf_price_second.val('');
        }
        if (price_option == 'less_than') {
            cpf_price_more_than.hide();
            cpf_price_first.hide();
            cpf_price_second.hide();
            cpf_price_more_than.val('');
            cpf_price_first.val('');
            cpf_price_second.val('');
            cpf_price_less_than.show();
        }
        if (price_option == 'more_than') {
            cpf_price_less_than.hide();
            cpf_price_first.hide();
            cpf_price_second.hide();
            cpf_price_less_than.val('');
            cpf_price_first.val('');
            cpf_price_second.val('');
            cpf_price_more_than.show();
        }
        if (price_option == 'in_between') {
            cpf_price_less_than.hide();
            cpf_price_more_than.hide();
            cpf_price_less_than.val('');
            cpf_price_more_than.val('');
            cpf_price_first.show();
            cpf_price_second.show();

        }
    });
}

function saveTocustomTable(feed_id) {
    jQuery.ajax({
        type: "POST",
        url: ajaxurl,
        data: {
            feed_id: feed_id,
            security: cpf.cpf_nonce,
            action: cpf.action,
            feedpath: cmdFetchProductAjax,
            q: 'saveEdit'
        },
        success: function (res) {
        }
    });
}

function cpf_remove_this_category(cpf_this) {
    jQuery(cpf_this).parent('span').remove();

}

function doSelectCategory_pricefalls(category, option, service_name) {
    cpf_category = category;
    cpf_option = option;
    cpf_service_name = service_name;
    var html = '<span><br>';
    html += '<input width="275" type="text" disabled class="remote_category" value="' + option + '">';
    html += '<span onclick="cpf_remove_this_category(this)" class="dashicons dashicons-minus" style="line-height: 35px;"></span>';
    html += '</span>';
    jQuery('#assined_category').append(html);
    document.getElementById("categoryList").style.display = "none";
    document.getElementById("categoryList").style.border = "0px";
}