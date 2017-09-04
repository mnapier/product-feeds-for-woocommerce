<?php 

#include_once( dirname(__FILE__).'/common_header.php' ); ?>

<style type="text/css">
	
	#AuthSettingsBox ol li {
		margin-bottom: 25px;
	}

</style>


<?php $cpf_form_action = 'admin.php?page=eBay_settings_tabs&tab=accounts&action=fetchToken'?>
<form method="post" id="addAccountForm" action="<?php echo $cpf_form_action; ?>">
	<input type="hidden" name="action"  value="cpf_add_account" >
	<input type="hidden" name="site_id" id="frm_site_id" value="" >
	<input type="hidden" name="sandbox" id="frm_sandbox" value="" >

	<div class="postbox" id="AddAccountBox">
		<h3 class="hndle"><span><?php echo __('Add eBay Account','cart-product-strings') ?></span></h3>
		<div class="inside">

			<!-- <label for="wplister_account_title" class="text_label"><?php echo __('Title','wplister'); ?>:</label> -->
			<!-- <input type="text" name="wplister_account_title" value="<?php #echo @$wplister_account_title ?>" class="text_input" /> -->

			<label for="cpf-ebay_site_id" class="text_label"><?php echo __('eBay Site','cart-product-strings'); ?>:</label>
			<select id="cpf-ebay_site_id" name="cpf_ebay_site_id" title="Site" class=" required-entry select">
				<option value="">-- <?php echo __('Please select','cart-product-strings'); ?> --</option>
				<?php
				$cpf_ebay_sites = eBayController::getEbaySites();

				unset( $cpf_ebay_sites[100] ); // remove eBay Motors - signin url doesn't exist ?>
				<?php foreach ( $cpf_ebay_sites as $site_id => $site_title ) : ?>
					<option 
					value="<?php echo $site_id ?>" 
					><?php echo $site_title ?></option>					
				<?php endforeach; ?>
			</select>

			<div id="wrap_account_details" style="display:none">

				<div class="dev_box" style="display:none">
					<label for="wpl-sandbox_mode" class="text_label">
						<?php echo __('Sandbox','cart-product-strings'); ?>
						<?php // wplister_tooltip('') ?>
					</label>
					<select id="cpf-sandbox_mode" name="cpf_sandbox_mode" title="Type" class=" required-entry select">
						<option value="0" ><?php echo __('Production (default)','cart-product-strings'); ?></option>
						<option value="1" ><?php echo __('Sandbox enabled','cart-product-strings'); ?></option>
					</select>
				</div>

				<p style="padding-left:0.2em;">
                    In order to add a new eBay account to Cart Product Feed you need to:
                    <?php $cpf_auth_url = "admin.php?page=eBay_settings_tabs&tab=accounts&action=wplRedirectToAuthURL"; ?>
                <ol>
                    <li>
                        <a id="btn_connect" href="<?php echo $cpf_auth_url; ?>" class="button-primary" target="_blank" style="float:right;" >Connect with eBay</a>
                        <?php echo __('Click "Connect with eBay" to sign in to eBay and grant access for Cart Product Feed', 'cart-product-strings'); ?>
                        <br>
                        <small>This will open the eBay Sign In page in a new window.</small><br>
                        <small>Please sign in, grant access for Cart Product Feed and close the new window to come back here.</small>
                    </li>
                    <li>
                        <input  style="float:right;" type="submit" value="<?php echo __('Fetch eBay Token', 'cart-product-strings') ?>" name="submit" class="button">
                        <?php echo __('After linking Cart Product Feed with your eBay account, click here to fetch your token', 'cart-product-strings') ?>
                        <br>
                        <small>
                            After retrieving your token, we will proceed with the first time set up.
                        </small>
                    </li>

                </ol>


                </p>

				<p style=""><small>
					You can view and revoke this authorization by visiting: <br>&raquo; My eBay &raquo; Account &raquo; Site Preferences  &raquo; General Preferences  &raquo; Third-party authorizations
				</small>

				<!-- <a href="#" id="wplister_btn_add_account" class="button-secondary" style="float:left;">Add new account</a> -->
				<!-- <a href="#" id="wplister_btn_signin" class="button-primary" style="float:right;" target="_blank">Sign in with eBay</a> -->
				<br style="clear:both" />

			</div>

		</div>
	</div>


</form>


<div id="debug_output" style="display:none">
	<?php echo "<pre>";print_r($wpl_ebay_accounts);echo"</pre>"; ?>
</div>

<script type="text/javascript">

	var cpf_auth_url = "<?php echo $cpf_auth_url; ?>";

	function cpf_update_auth_url() {
		var site_id = jQuery('#cpf-ebay_site_id').val();
		var sandbox = jQuery('#cpf-sandbox_mode').val();
		jQuery('#btn_connect').attr('href',  cpf_auth_url + '&sandbox=' + sandbox + '&site_id=' + site_id );
		jQuery('#frm_site_id').attr('value', site_id );
		jQuery('#frm_sandbox').attr('value', sandbox );

	}

	jQuery( document ).ready( function () {
		
			// ebay site selector during install - update form on selection
			jQuery('#AddAccountBox #cpf-ebay_site_id').change( function(event, a, b) {					

				var site_id = event.target.value;
				if ( site_id ) {

					cpf_update_auth_url();

					jQuery('#wrap_account_details').slideDown(300);
				} else {
					jQuery('#wrap_account_details').slideUp(300);						
				}
				
			});
			jQuery('#AddAccountBox #wpl-sandbox_mode').change( function(e) {
				cpf_update_auth_url();
			});

			// add new account button
			// jQuery('#wplister_btn_add_account').click( function() {					
			// 	jQuery('#addAccountForm').first().submit();
			// 	return false;
			// });

		});
	

	</script>
