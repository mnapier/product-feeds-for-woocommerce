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

if ($_REQUEST['q'] == 'ajax') {
    $keywords = isset($_POST['keyword']) ? $_POST['keyword'] : '';
    $filterTerm = isset($_POST['searchfilters']) ? $_POST['searchfilters'] : '';
    if ($filterTerm == 'sku') {
        $where = "meta_key = '_sku' AND meta_value LIKE '{$keywords}%'";
        $sql = "SELECT meta_id as meta_id ,post_id as id,  meta_value as title
                         FROM {$wpdb->prefix}postmeta
                         where {$where}
                 ";
    }

    if ($filterTerm == 'all') {
        $where = "post_title like '{$keywords}%' AND post_type='product'";
        $sql = "SELECT ID as id ,post_title as title
                         FROM {$wpdb->prefix}posts
                         where {$where}
                    ";
    }
    $result = $wpdb->get_results($sql, ARRAY_A); ?>
    <ul id="filters_results">
        <?php
        if (count($result) > 0) {
            foreach ($result as $data => $product) { ?>
                <li onclick="selectFilters('<?php echo $product['title']; ?>');"><?php echo $product['title']; ?></li>
                <input type="hidden" value="<?php echo $product['id']; ?>" name="cpf-hidden-id"/>
            <?php } ?>
        <?php } else { ?>
            <li><span class="no-search-results">No Record found</span></li>
        <?php } ?>
    </ul>
<?php } ?>

<?php
if ($_REQUEST['q'] == 'search') {
    $merchat_type = isset($_POST['merchat_type']) ? $_POST['merchat_type'] : $_POST['service_name'];
    $keywords = isset($_POST['keywords']) ? $_POST['keywords'] : "";
    $category = isset($_POST['category']) ? $_POST['category'] : '';
    $price_range = isset($_POST['price_range']) ? $_POST['price_range'] : '';
    $product_sku = isset($_POST['sku']) ? $_POST['sku'] : '';
    $limit = isset($_POST['limit']) ? $_POST['limit'] : '';
    $cats = "";
    $priceLimit = "";
    $skuQuery = "";
    if ($category) {
        $cats = " AND tblCategories.category_ids = {$category}";
    }
    if ($price_range) {
        $priceLimit = " AND postmeta_table.meta_value {$price_range}";
        if (strpos($price_range, '-')) {
            $price = explode('-', $price_range);
            $priceLimit = " AND (postmeta_table.meta_value >= {$price[0]} AND postmeta_table.meta_value <= {$price[1]})";
        }
    }
    if ($product_sku) {
        $skuQuery = " AND postmeta_table_1.meta_value = '{$product_sku}'";
    }

    global $wpdb;
    $sql = "SELECT 
				 {$wpdb->prefix}posts.ID, {$wpdb->prefix}posts.post_date, {$wpdb->prefix}posts.post_title, {$wpdb->prefix}posts.post_content,{$wpdb->prefix}posts.post_excerpt, {$wpdb->prefix}posts.post_name, 
					tblCategories.category_names, tblCategories.category_ids,
					details.name as product_type,
					attribute_details.attribute_details, 
					variation_id_table.variation_ids as variation_ids,
					postmeta_table.meta_value as price, postmeta_table_1.meta_value as sku
					FROM {$wpdb->prefix}posts
				#Categories
				LEFT JOIN
    (
        SELECT postsAsTaxo.ID, GROUP_CONCAT(category_terms.name) as category_names, GROUP_CONCAT(category_terms.term_id) as category_ids
						FROM {$wpdb->prefix}posts postsAsTaxo
						LEFT JOIN {$wpdb->prefix}term_relationships category_relationships ON (postsAsTaxo.ID = category_relationships.object_id)
						LEFT JOIN {$wpdb->prefix}term_taxonomy category_taxonomy ON (category_relationships.term_taxonomy_id = category_taxonomy.term_taxonomy_id)
						LEFT JOIN {$wpdb->prefix}terms category_terms ON (category_taxonomy.term_id = category_terms.term_id)
						WHERE (category_taxonomy.taxonomy = 'product_cat') 
						 # AND category_terms.term_id IN (6)
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
	  #postmeta
	  LEFT JOIN (
              SELECT postmeta.meta_key , postmeta.meta_value ,postmeta.post_id
              from {$wpdb->prefix}postmeta as postmeta
              WHERE (postmeta.meta_key = '_regular_price')
            ) as postmeta_table on postmeta_table.post_id = {$wpdb->prefix}posts.ID
      LEFT JOIN (
              SELECT postmeta_1.meta_key , postmeta_1.meta_value ,postmeta_1.post_id
              from {$wpdb->prefix}postmeta as postmeta_1
              WHERE (postmeta_1.meta_key = '_sku' )
            ) as postmeta_table_1 on postmeta_table_1.post_id = {$wpdb->prefix}posts.ID
            
        WHERE {$wpdb->prefix}posts.post_status = 'publish' AND {$wpdb->prefix}posts.post_type = 'product' AND {$wpdb->prefix}posts.post_title like '{$keywords}%' $cats  $priceLimit $skuQuery
				ORDER BY post_date ASC limit $limit";

    //echo $sql;
    $results = $wpdb->get_results($sql, ARRAY_A);
    $count = count($results);
    $html = '';
    if ($count > 0) {
        foreach ($results as $data => $product) : ?>
            <?php
            $html .= '<tr>
            <td style="width: 5%"><input type="checkbox"/></td>
            <td class="index">' . $product['post_title'] . '</td>
            <td class="index">' . $product['category_names'] . '
                <div class="cpf_selected_product_hidden_attr" style="display: none ;">

                    <span class="cpf_selected_product_id">' . $product['ID'] . '</span>
                    <span class="cpf_selected_product_title">' . $product['post_title'] . '</span>
                    <span class="cpf_selected_product_cat_names">' . $product['category_names'] . '</span>
                    <span class="cpf_selected_local_cat_ids">' . $product['category_ids'] . '</span>
                    <span class="cpf_selected_product_type">' . $product['product_type'] . '</span>
                    <span
                        class="cpf_selected_product_attributes_details">' . $product['attribute_details'] . '</span>
                    <span class="cpf_selected_product_variation_ids">' . $product['variation_ids'] . '</span>
                </div>
            </td>
            <td style="width: 40%;">
                <div><span><input type="search" name="categoryDisplayText" class="text_big" id="categoryDisplayText"
                                  onkeyup="doFetchCategory_timed_custom(' . "'{$merchat_type}'" . ',this)" value=""
                                  onclick = "doFetchCategory_timed_custom(' . "'{$merchat_type}'" . ',this)"
                                  autocomplete="off"
                                  placeholder="Start typing..." style="width: 100%;"></span>
                    <div class="categoryList"></div>
                    <div class="no_remote_category"></div>
                </div>
            </td>
            <td class="cpf-selected-parent" style="width: 7%"><span class="dashicons dashicons-trash "
                                                                    onclick="cpf_remove_feed_parent(this);" title="Delete this row."></span><span class="spinner"></span></td>
        </tr>';


        endforeach;

    }

    $data = [
        'html' => $html,
        'count' => $count,
    ];

    echo json_encode($data);

} ?>
<?php
if ($_REQUEST['q'] == 'savep') {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cpf_custom_products';
    if (($_POST['remote_category']) == '') {
        echo '<div id="no_remote_category_selected">Please select merchant category.</div>';
        die;
    }
    if ($_POST['local_cat_ids']) {
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
    }
    print_r($wpdb->last_query);
    die;
}

if ($_REQUEST['q'] == 'showT') {
    global $wpdb;

    $feed_id = isset($_POST['feed_id']) ? $_POST['feed_id'] : '';

    if ($feed_id) {
        $sql = "SELECT `product_details` from {$wpdb->prefix}cp_feeds where `feed_type`=1 AND `id` = {$feed_id} ";
        $res = $wpdb->get_var(($sql));
        $result = maybe_unserialize($res);
    } else {
        $table_name = $wpdb->prefix . 'cpf_custom_products';
        $sql = "
            SELECT id,product_title , category_name , remote_category , product_id
             FROM {$table_name}
             ORDER BY id
             ";
        $result = $wpdb->get_results($sql, ARRAY_A);
    }
    if (count($result)) {
        foreach ($result as $data => $product) { ?>
            <tr>
                <td style="width: 5%"><input type="checkbox"/></td>
                <td class="index"><?php echo $product['product_title']; ?><span class="cpf_product_id_hidden"
                                                                                style="display:none;"><?php echo $product['product_id']; ?></span>
                    <span class="cpf_feed_id_hidden"
                          style="display:none;"><?php echo $product['id']; ?></span>
                </td>
                <td class="index"><?php echo $product['category_name']; ?></td>
                <td style="width: 40%;"><?php echo $product['remote_category']; ?></td>
                <td class="cpf-selected-parent" style="width: 7%"><span class="dashicons dashicons-trash "
                                                                        onclick="cpf_remove_feed(this);"
                                                                        title="Delete this row."></span><span
                        class="spinner"></span></td>
            </tr>
        <?php }
    } else { ?>
        <tr id="cpf-no-products">
            <td colspan="5">No product selected.</td>
        </tr>
    <?php }

}

if ($_REQUEST['q'] == 'delR') {
    $id = $_POST['id'];
    if (is_array($id)) {
        $id = implode(',', $id);
    }
    global $pfcore;
    $tableName = $wpdb->prefix . 'cpf_custom_products';
    $sql = "DELETE FROM {$tableName} WHERE id IN ($id)";
    $wpdb->query($sql);
    $wpdb->last_errors;
    // $wpdb->delete($tableName, array('id' => $id));
    die;

}


if ($_REQUEST['q'] == 'saveEdit') {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cpf_custom_products';
    $feed_id = isset($_POST['feed_id']) ? $_POST['feed_id'] : '';
    $sql = "SELECT `product_details` from {$wpdb->prefix}cp_feeds where `feed_type`=1 AND `id` = {$feed_id} ";
    $res = $wpdb->get_var(($sql));
    $result = maybe_unserialize($res);

    $table_name = $wpdb->prefix . 'cpf_custom_products';
    foreach ($result as $data => $products) {
        $wpdb->insert(
            $table_name,
            array(
                'category' => $products['category'],
                'product_title' => $products['product_title'],
                'category_name' => $products['category_name'],
                'product_type' => $products['product_type'],
                'product_attributes' => $products['product_attributes'],
                'product_variation_ids' => $products['product_variation_ids'],
                'remote_category' => $products['remote_category'],
                'product_id' => $products['product_id']
            )
        );
    }

    print_r($wpdb->last_query);
    die;
}