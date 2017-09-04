<style>
    .help_tip {
        float: right;
    }
</style>

<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/**
 * Created by PhpStorm.
 * User: subash
 * Date: 5/12/16
 * Time: 10:32 AM
 */
class PeBayFeedSettings
{
    public static function defaulteBaySettings()
    {
        global $wpdb;
        $helpImg = '<img class="help_tip" title="We ask for your age <br/> only for statistical purposes."  src="' . plugins_url('../images/help.png', dirname(__FILE__)) . '" />';
        global $wpdb;
        $space = " ";
        $tableName = $wpdb->prefix .'ebay_shipping';
        $results = $wpdb->get_row("SELECT * from {$tableName} WHERE id = 1");
       
        $paypalemailAddress = isset($results->paypal_email) ? $results->paypal_email : '';
        $dispatchTimeMax = isset($results->dispatchTime) ? $results->dispatchTime : '';
        $shippingfee = isset($results->shippingfee) ? $results->shippingfee : '';
        $shippingType = isset($results->ebayShippingType) ? $results->ebayShippingType : 'flat';
        $paypalAcceptance = isset($results->paypal_accept) ? $results->paypal_accept : 1 ;
        $shippingService = isset($results->shipping_service)  ? $results->shipping_service : '';
        $listingDuration = isset($results->listingDuration) ? $results->listingDuration : 'Days_7';
        $listingType = isset($results->listingType) ? $results->listingType : '';
        $refundOption = isset($results->refundOption) ? $results->refundOption : '' ;
        $refundDes = isset($results->refundDesc) ? $results->refundDesc : '';
        $postalcode = isset($results->postalcode) ? $results->postalcode : '';
        $returnswithin = isset($results->returnwithin) ? $results->returnwithin : '';
        $additionalshippingservice = isset($results->additionalshippingservice) ? $results->additionalshippingservice : '';
        $quantity = isset($results->quantity) ? $results->quantity : ''; 
        if($results->id >  1){
             $style = " style=display:none";
        }
        if($listingType == "FixedItemPrice") {$selected = "selected";}
        if($listingType == "Auction") { $selected = "selected"; }
         return '
        <div id="poststuff_1">
	    <div class="postbox">
		  <h3 class="hndle">Feed Default Settings</h3>
		  <div class="required_fields"><span class="star"> *</span>All Fields Required</div>
		  <div class="inside export-target">
		   <form name="ebaySettngs" id="ebaySettings" method="post" >
		    <table class="form-table">
		      <tbody>
			    <tr>
				    <th><label class="text_label" id="paypal_account">PayPal Accepted:</label></th>
				    <td id="paypal_account"><input type="radio" name="ebay_paypal_accepted" value="'.$paypalAcceptance.'" checked/>Yes
				        <input type="radio" name="ebay_paypal_accepted" value="0" /> No <br/>
				    </td>
                </tr>
                <tr id="paypal_emailaddress"> 
                    <th><label class="text_label">PayPal Email Address:</label></th>
                    <td><input type="text" name="ebay_paypal_email" value="'.$paypalemailAddress.'" ></td>
                    <td><div id="error_message_email"></div></td> 
                </tr>
                <tr>
                    <th><label class="text_label">Dispatch Time:</label></th>
                    <td><input type="text" name="ebay_dispatch_time" id="ebay_dispatch_time" value=" '.$dispatchTimeMax.' " ></td>
                </tr>
                 <tr id="conditionType">
                    <th><label class="text_label" id="conditionType">Condition Type:</label></th>
                    <td><select id="conditionType" name="conditionType">
                            <option value="" >Select Condition Type</option>
                            <option value="1000" selected = "selected">New</option>
                            <option value="3000" >Used</option>
                        </select>
                    </td>    
                </tr>
                <tr>
                    <th><label class="text_label">Shipping Type:</label></th>
                    <td><input type="radio" name="ebay_shipping_type" value="Flat" checked >Flat
                        <input type="radio" name="ebay_shipping_type" value="Calculated" >Calculated</td>
                </tr>
                <tr id="flatShippingFee">
                    <th><label class="text_label" id="flatShipping">Shipping Fee:</label></th>
                    <td><input type="text" name="flatShipping" id="flatShipping" value="'.$shippingfee.'" /></td>
                </tr>
                <tr>
                    <th><label class="text_label" id="shippingservice">Shipping service:</label></th>
                    <td><input type="text" name="shippingservice" id="shippingservice" value="'.$shippingService.'" /></td>
                </tr>
               <tr>
                    <th><label class="text_label" id="additionalshippingservice">Additional Shipping service:</label></th>
                    <td><input type="text" name="additionalshippingservice" id="additionalshippingservice" value="'.$additionalshippingservice.'" /></td>
                </tr>
                <tr id="duration_listing">
                    <th><label class="text_label" id="listing_duration">Listing Duration:</label></th>
                    <td><input type="text" name="listing_duration" id="listing_duration" value="'.$listingDuration.'" /></td>
                </tr>
                <tr id="listingType">
                    <th><label class="text_label" id="listing_type">Listing Type:</label></th>
                    <td><select id="listing_type" name="listing_type">
                            <option value="" >Select Listing Type </option>
                            <option value="FixedPriceItem" selected = "selected" >Fixed Item Price</option>
                            <option value="Auction">Auction</option>
                        </select>
                    </td>    
                </tr>
                <tr id="refundOption">
                    <th><label class="text_label" id="refundOption">Refund Option:</label></th>
                    <td><select id="refund_option" name="refund_option">
                            <option value="" >Select Refund Option </option>
                            <option value="MoneyBack" selected = "selected">Money Back</option>
                            <option value="Exchange" >Exchange</option>
                        </select>
                    </td>    
                </tr>
                <tr>
                     <th><label class="text_label" id="refund_desc">Refund Description:</label></th>
                     <td><textarea rows="4" cols="50" name="refund_desc" id="refund_desc">'.$refundDes.'</textarea></td>
                </tr>
                 
                <tr>
                     <th><label class="text_label" id="returnswithin">ReturnsWithIn:</label></th>
                     <td><input type="text" name="returnswithin" value="'.$returnswithin.'" ></td>
                </tr>
                <tr>
                     <th><label class="text_label" id="postalcode">Postal Code:</label></th>
                     <td><input type="text" name="postalcode" value="'.$postalcode.'" ></td>
                </tr>
                <tr>
                     <th><label class="text_label" id="quantity">Quantity:</label></th>
                     <td><input type="text" name="quantity" value="'.$quantity.'" ></td>
                </tr>
				  <td>
					<input class="button-primary" '.$style.' type="submit" value="Save Settings" id="save_settings" name="save_settings">
				  </td>
				  <td>
				    <input class ="button-primary" type="submit" value = "Edit Settings" id="edit_settings" name="edit_settings" />
				    <input type ="hidden" value="'.$results->id.'" name="hiddenID" />
				  </td>
				</tr>
			  </tbody>
			</table>
			 </form>
		  </div>
		 
		</div>
	  </div>
       ';
    }

    // custom tooltips
    public static function CPF_tooltip($desc)
    {
        $desc = apply_filters('cpf_tooltip_text', $desc);
        $img = '<img class="help_tip" title="' . esc_attr($desc) . '" src="' . plugins_url('../images/help.png', dirname(__FILE__)) . '" height="16" width="16" />';
        return $img;
    }
}

?>

<script>
    jQuery(document).ready(function () {
        var paypal_checked = jQuery("input[name=ebay_paypal_accepted]");
        var checked = jQuery("input[name='ebay_shipping_type']");
        jQuery(checked).change(function () {
            if (this.value != 'flat') {
                jQuery("#flatShippingFee").css('display', 'none');
            } else {
                jQuery("#flatShippingFee").removeAttr('style');
            }
        });

        jQuery(paypal_checked).change(function () {
           if(this.value != 1){
               jQuery("#paypal_emailaddress").css('display', 'none');
           }else{
               jQuery("#paypal_emailaddress").removeAttr('style');
           }
        });



        document.getElementById("ebaySettings").onsubmit = function onSubmit() {

            var msg = '';
            var form = document.getElementById("ebaySettings");
            // validation fails if the input is blank
            if (form.ebay_paypal_email.value == "") {
                alert("Email Address is required");
                form.ebay_paypal_email.focus();
                return false;
            }

            if (form.ebay_dispatch_time.value == "") {
                alert("Error: Dispatch time is reuired");
                form.ebay_dispatch_time.focus();
                return false;
            }
            if (form.flatShipping.value == "") {
                alert("Error: flatShipping field is empty!");
                form.flatShipping.focus();
                return false;
            }
            // validation was successful
            submitform(form);
        }

        function submitform(form){
            var payPal_email = form.ebay_paypal_email.value;
            var dispatchTime = form.ebay_dispatch_time.value;
            var flatShipping = form.flatShipping.value;
            var ebayShippingType = form.ebay_shipping_type.value;
            var ebayPaypalAccepted = form.ebay_paypal_accepted.value;
            var shippingService = form.shippingservice.value;
            var listing_duration = form.listing_duration.value;
            var listing_type = form.listing_type.value;
            var refund_option = form.refund_option.value;
            var refund_desc = form.refund_desc.value;
            var returnswithin = form.returnswithin.value;
            var postalcode = form.postalcode.value;
            var conditionType = form.conditionType.value;
            var quantity = form.quantity.value;
            var additionalshippingservice = form.additionalshippingservice.value;
            var ajaxhost = "<?php echo plugins_url("/" , dirname(__file__));?>";
            var hiddenId = form.hiddenID.value;
            var cmdGeneralSettingseBay = "ajax/wp/eBay_general_settings.php";
            jQuery.ajax({
                'type' : 'POST',
                'url' : ajaxhost + cmdGeneralSettingseBay,
                'data' : {  paypal_email : payPal_email ,
                            dispatchTime : dispatchTime ,
                            flatShipping : flatShipping,
                            ebayShippingType : ebayShippingType,
                            ebayPaypalAccepted : ebayPaypalAccepted,
                            shippingService : shippingService,
                            listingDuration : listing_duration,
                            listingType : listing_type,
                            refundOption : refund_option,
                            refundDesc : refund_desc,
                            returnwithin : returnswithin,
                            postalcode : postalcode,
                            additionalshippingservice : additionalshippingservice,
                            conditionType : conditionType,
                            quantity : quantity ,
                            hiddenId : hiddenId
                },
                success : function(res){
                    console.log("success");
                    console.log(res);
                }

            });
        }
    });
</script>
