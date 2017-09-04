<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!is_admin()) {
    die('Permission Denied!');
}
define('XMLRPC_REQUEST', true);
ob_start();
//********************************************************************
//Load the products
//********************************************************************
global $wpdb;

if ($_GET['q'] == 'ajax') {
    $keywords = isset($_POST['keyword']) ? $_POST['keyword'] : '';
    global $wpdb;
    $sql = "SELECT 
				 {$wpdb->prefix}posts.ID, {$wpdb->prefix}posts.post_date, {$wpdb->prefix}posts.post_title, {$wpdb->prefix}posts.post_content,{$wpdb->prefix}posts.post_excerpt, {$wpdb->prefix}posts.post_name, 
					tblCategories.category_names, tblCategories.category_ids,
					details.name as product_type,
					attribute_details.attribute_details, 
					variation_id_table.variation_ids as variation_ids 
					FROM {$wpdb->prefix}posts
				#Categories
				LEFT JOIN
    (
        SELECT postsAsTaxo.ID, GROUP_CONCAT(category_terms.name) as category_names, GROUP_CONCAT(category_terms.term_id) as category_ids
						FROM {$wpdb->prefix}posts postsAsTaxo
						LEFT JOIN {$wpdb->prefix}term_relationships category_relationships ON (postsAsTaxo.ID = category_relationships.object_id)
						LEFT JOIN {$wpdb->prefix}term_taxonomy category_taxonomy ON (category_relationships.term_taxonomy_id = category_taxonomy.term_taxonomy_id)
						LEFT JOIN {$wpdb->prefix}terms category_terms ON (category_taxonomy.term_id = category_terms.term_id)
						WHERE category_taxonomy.taxonomy = 'product_cat' 
						$cats  # AND category_terms.term_id IN (6)
						GROUP BY postsAsTaxo.ID
					) as tblCategories ON tblCategories.ID = {$wpdb->prefix}posts.ID
				
				#Link in product type
				LEFT JOIN
    (
        SELECT a.ID, d.name FROM {$wpdb->prefix}posts a
						LEFT JOIN {$wpdb->prefix}term_relationships b ON (a.ID = b.object_id)
						LEFT JOIN {$wpdb->prefix}term_taxonomy c ON (b.term_taxonomy_id = c.term_taxonomy_id)
						LEFT JOIN {$wpdb->prefix}terms d ON (c.term_id = d.term_id)
						WHERE c.taxonomy = 'product_type'
					) as details ON details.ID = {$wpdb->prefix}posts.ID
				
				
				#Attributes in detail
				LEFT JOIN
    (
        SELECT a.ID, GROUP_CONCAT(CONCAT(c.taxonomy, '=', d.slug, '=', d.name)) as attribute_details
						FROM {$wpdb->prefix}posts a
						LEFT JOIN {$wpdb->prefix}term_relationships b ON (a.ID = b.object_id)
						LEFT JOIN {$wpdb->prefix}term_taxonomy c ON (b.term_taxonomy_id = c.term_taxonomy_id)
						LEFT JOIN {$wpdb->prefix}terms d ON (c.term_id = d.term_id)
						WHERE c.taxonomy LIKE 'pa\_%'
						GROUP BY a.ID
					) as attribute_details ON attribute_details.ID = {$wpdb->prefix}posts.ID

				#variations
				LEFT JOIN
    (
        SELECT GROUP_CONCAT(postvars.id) as variation_ids, postvars.post_parent
						FROM {$wpdb->prefix}posts postvars
						WHERE (postvars.post_type = 'product_variation') AND (postvars.post_status = 'publish')
						GROUP BY postvars.post_parent
					) as variation_id_table on variation_id_table.post_parent = {$wpdb->prefix}posts.ID
        WHERE {$wpdb->prefix}posts.post_status = 'publish' AND {$wpdb->prefix}posts.post_type = 'product' AND {$wpdb->prefix}posts.post_title like '{$keywords}%'
				ORDER BY post_date ASC";
    $result = $wpdb->get_results($sql, ARRAY_A);

    ?>
    <ul id="filters_results">
        <?php
        if (count($result) > 0) {
            foreach ($result as $data => $product) { ?>
                <li onclick="selectFilters('<?php echo $product['post_title']; ?>');"><?php echo $product['post_title']; ?></li>
                <input type="hidden" value="<?php echo $product[id]; ?>" name="cpf-hidden-id"/>
            <?php } ?>
        <?php } else { ?>
            <li><span class="no-search-results">No Record found</span></li>
        <?php } ?>
    </ul>
<?php } ?>

<?php
if ($_GET['q'] == 'search') {
    $merchat_type = isset($_POST['merchat_type']) ? $_POST['merchat_type'] : '';
    $keywords = isset($_POST['keywords']) ? $_POST['keywords'] : "";
    $category = isset($_POST['category']) ? $_POST['category'] : '';
    $cats = "";
    if ($category) {
        $cats = " AND tblCategories.category_ids = {$category}";
    }
    /*$brand = isset($_POST['brand']) ? $_POST['brand'] : '';
    $sku = isset($_POST['sku']) ? $_POST['sku'] : '';
    $price_range = isset($_POST['price_range']) ? $_POST['price_rance'] : '';
    $wheresku = "";
    $joinsku = "";
    if ($sku) {
        $joinsku = " LEFT JOIN {$wpdb->prefix}postmeta as postmeta ON posts.ID = postmeta.post_id";
        $wheresku = " AND meta_key= '_sku' AND meta_value = '{$sku}'";
    }
    $searchKeys = "";
    if($keywords){
        $searchKeys = " post_title like '{$keywords}%' AND ";
    }
    $sql = ("SELECT *
            FROM {$wpdb->prefix}posts as posts 
           $joinsku
           WHERE $searchKeys post_type = 'product'
            $wheresku
        ");
    echo $sql;*/
    global $wpdb;
    $sql = "SELECT 
				 {$wpdb->prefix}posts.ID, {$wpdb->prefix}posts.post_date, {$wpdb->prefix}posts.post_title, {$wpdb->prefix}posts.post_content,{$wpdb->prefix}posts.post_excerpt, {$wpdb->prefix}posts.post_name, 
					tblCategories.category_names, tblCategories.category_ids,
					details.name as product_type,
					attribute_details.attribute_details, 
					variation_id_table.variation_ids as variation_ids 
					FROM {$wpdb->prefix}posts
				#Categories
				LEFT JOIN
    (
        SELECT postsAsTaxo.ID, GROUP_CONCAT(category_terms.name) as category_names, GROUP_CONCAT(category_terms.term_id) as category_ids
						FROM {$wpdb->prefix}posts postsAsTaxo
						LEFT JOIN {$wpdb->prefix}term_relationships category_relationships ON (postsAsTaxo.ID = category_relationships.object_id)
						LEFT JOIN {$wpdb->prefix}term_taxonomy category_taxonomy ON (category_relationships.term_taxonomy_id = category_taxonomy.term_taxonomy_id)
						LEFT JOIN {$wpdb->prefix}terms category_terms ON (category_taxonomy.term_id = category_terms.term_id)
						WHERE category_taxonomy.taxonomy = 'product_cat' 
						GROUP BY postsAsTaxo.ID
					) as tblCategories ON tblCategories.ID = {$wpdb->prefix}posts.ID
				
				#Link in product type
				LEFT JOIN
    (
        SELECT a.ID, d.name FROM {$wpdb->prefix}posts a
						LEFT JOIN {$wpdb->prefix}term_relationships b ON (a.ID = b.object_id)
						LEFT JOIN {$wpdb->prefix}term_taxonomy c ON (b.term_taxonomy_id = c.term_taxonomy_id)
						LEFT JOIN {$wpdb->prefix}terms d ON (c.term_id = d.term_id)
						WHERE c.taxonomy = 'product_type'
					) as details ON details.ID = {$wpdb->prefix}posts.ID
				
				
				#Attributes in detail
				LEFT JOIN
    (
        SELECT a.ID, GROUP_CONCAT(CONCAT(c.taxonomy, '=', d.slug, '=', d.name)) as attribute_details
						FROM {$wpdb->prefix}posts a
						LEFT JOIN {$wpdb->prefix}term_relationships b ON (a.ID = b.object_id)
						LEFT JOIN {$wpdb->prefix}term_taxonomy c ON (b.term_taxonomy_id = c.term_taxonomy_id)
						LEFT JOIN {$wpdb->prefix}terms d ON (c.term_id = d.term_id)
						WHERE c.taxonomy LIKE 'pa\_%'
						GROUP BY a.ID
					) as attribute_details ON attribute_details.ID = {$wpdb->prefix}posts.ID

				#variations
				LEFT JOIN
    (
        SELECT GROUP_CONCAT(postvars.id) as variation_ids, postvars.post_parent
						FROM {$wpdb->prefix}posts postvars
						WHERE (postvars.post_type = 'product_variation') AND (postvars.post_status = 'publish')
						GROUP BY postvars.post_parent
					) as variation_id_table on variation_id_table.post_parent = {$wpdb->prefix}posts.ID
        WHERE {$wpdb->prefix}posts.post_status = 'publish' AND {$wpdb->prefix}posts.post_type = 'product' AND {$wpdb->prefix}posts.post_title like '{$keywords}%' $cats
				ORDER BY post_date ASC";
    //echo $sql;die;
    $results = $wpdb->get_results($sql, ARRAY_A);
    $i = 0;
    ?>
    <?php foreach ($results as $data => $product) : ?>
        <tr>
            <td style="width: 5%"><input type="checkbox"/></td>
            <td class="index"><?php echo $product['post_title']; ?></td>
            <td class="index"><?php echo $product['category_names']; ?>
                <div class="cpf_selected_product_hidden_attr" style="display: none ;">
                    <span class="cpf_selected_product_id"><?php echo $product['ID']; ?></span>
                    <span class="cpf_selected_product_title"><?php echo $product['post_title']; ?></span>
                    <span class="cpf_selected_product_cat_names"><?php echo $product['category_names']; ?></span>
                    <span class="cpf_selected_local_cat_ids"><?php echo $product['category_ids']; ?></span>
                    <span class="cpf_selected_product_type"><?php echo $product['product_type']; ?></span>
                    <span
                        class="cpf_selected_product_attributes_details"><?php echo $product['attribute_details']; ?></span>
                    <span class="cpf_selected_product_variation_ids"><?php echo $product['variation_ids']; ?></span>
                </div>
            </td>
            <td style="width: 40%;">
                <div><span><input type="search" name="categoryDisplayText" class="text_big" id="categoryDisplayText"
                                  onkeyup="doFetchCategory_timed_custom('<?php echo $merchat_type; ?>',this)" value=""
                                  autocomplete="off"
                                  placeholder="Start typing..." style="width: 100%;"></span>
                    <div id="categoryList" class="categoryList"></div>
            </td>
            <td class="cpf-selected-parent" style="width: 7%"><span class="dashicons dashicons-trash "
                                                                    onclick="cpf_remove_feed_parent(this);"></span></td>

            <!--<input type="hidden" name="cpf_product_id" value="<?php /*echo $product['ID'] */ ?>">
            <input type="hidden" id="cpf_product_name" name="cpf_product_name"
                   value="<?php /*echo $product['post_title']; */ ?>"/>
            <input type="hidden" id="cpf_local_category" name="cpf_local_category"
                   value="<?php /*echo $product['post_name']; */ ?>"/>
            <input type="hidden" id="cpf_remote_category" name="cpf_remote_category" value=""/>
            <input type="hidden" id="cpf_description" name="cpf_description"
                   value="<?php /*echo $product['post_content']; */ ?>"/>-->
        </tr>
        <?php $i++; ?>
    <?php endforeach; ?>
<?php } ?>

<?php

if ($_GET['q'] == 'savep') {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cpf_custom_products';
    $wpdb->insert(
        $table_name,
        array(
            'category' => $_POST['local_cat_ids'],
            'product_title' => $_POST['product_title'],
            'category_name' => $_POST['category_name'],
            'product_type' => $_POST['product_type'],
            'product_attributes' => $_POST['product_attributes'],
            'product_variation_ids' => $_POST['product_variation_ids'],
            'remote_category' => $_POST['remote_category'],
            'product_id' => $_POST['product_id']
        )

    );
    print_r($wpdb);
    die;
}

if ($_GET['q'] == 'showT') {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cpf_custom_products';
    $sql = "
            SELECT product_title , category_name , remote_category , product_id
             FROM {$table_name}
              ";
    $result = $wpdb->get_results($sql, ARRAY_A);

    if (count($result)) {
        foreach ($result as $data => $product) { ?>
            <tr>
                <td class="index"><?php echo $product['product_title']; ?><span class="cpf_product_id_hidden"
                                                                                style="display:none;"><?php echo $product['product_id']; ?></span>
                </td>
                <td class="index"><?php echo $product['category_name']; ?></td>
                <td style="width: 40%;"><?php echo $product['remote_category']; ?></td>
                <td class="cpf-selected-parent" style="width: 7%"><span class="dashicons dashicons-trash "
                                                                        onclick="cpf_remove_feed(this);"></span></td>
            </tr>
        <?php }
    } else { ?>
        <tr id="cpf-no-products">
            <td colspan="5">No product selected.</td>
        </tr>
    <?php }

}

if ($_GET['q'] == 'delR') {
    $id = $_POST['id'];
    global $pfcore;
    $tableName = $wpdb->prefix . 'cpf_custom_products';
    $wpdb->delete($tableName, array('product_id' => $id));
    die;

}
