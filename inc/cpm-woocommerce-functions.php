<?php


/* add v card option for my-account page */

add_filter('woocommerce_account_menu_items', 'cpm_dong_show_membership_vcard', 40);
function cpm_dong_show_membership_vcard($menu_links)
{

    $menu_links = array_slice($menu_links, 0, 5, true)
        + array('show-membership-v-card' => 'My VCard')
        + array_slice($menu_links, 5, NULL, true);

    return $menu_links;
}
// register permalink endpoint
add_action('init', 'cpm_dong_my_membership_vcard_endpoint');
function cpm_dong_my_membership_vcard_endpoint()
{

    add_rewrite_endpoint('show-membership-v-card', EP_PAGES);
}
// content for the new page in My Account, woocommerce_account_{ENDPOINT NAME}_endpoint
add_action('woocommerce_account_show-membership-v-card_endpoint', 'cpm_dong_my_membership_vcard_endpoint_content');
function cpm_dong_my_membership_vcard_endpoint_content()
{

    // of course you can print dynamic content here, one of the most useful functions here is get_current_user_id()
?>
    <div class='qr-tiger-vcard-code-generator'>
        <h2><?php _e('My Vcard ', 'cpm-dongtrader') ?></h2>
        <form action="" method="post">
            <label for="vcard-url"><?php _e('Enter VCard', 'cpm-dongtrader') ?></label>
            <div class="input-form-dong">
                <input type="url" class="vcard-url" name="vcard-url" placeholder="<?php _e('Enter Vcard Url', 'cpm-dongtrader') ?>" required><input type="submit" value="Update Vcard" name="add_vcard">
            </div>
        </form>
    </div>


    <?php

    /* add/update Vcard to user meta  */
    $user_id = get_current_user_id();
    if (isset($_POST['add_vcard'])) {
        $get_vcard_url = $_POST['vcard-url'];

        update_user_meta($user_id, '_dongtraders_user_vcard', $get_vcard_url);
    }
    $user_meta_qrs = get_user_meta($user_id, '_dongtraders_user_vcard', true);
    //echo $user_meta_qrs;
    if (!empty($user_meta_qrs)) {
        echo '<div class="qr_vcard_section">
        <div class="card card-1">
        <img style="height:auto; width:50%;" src="' . $user_meta_qrs . '" alt="Invalid QR Image">
        </div>
        <p id="copy_url_id" style="display:none;">' . $user_meta_qrs . '</p>
        </div>
        <style>
            .qr-tiger-vcard-code-generator{
                display: none;
            }
        </style>';
    ?>
        <div class="vcards_buttons">
            <a href="JavaScript:Void(0);" class="copy_qr_image_url" onclick="dong_traders_url_copy('#copy_url_id')">Copy QR URL</a> <a class="update_card" href="JavaScript:Void(0);">Update Vcard</a>
        </div>
    <?php
    } else {
        echo '<style>
	.qr-tiger-vcard-code-generator{
		display: block;
	}
</style>';
    }
}

/* show memebership data on woocommerce tab */

add_filter('woocommerce_account_menu_items', 'cpm_dong_show_membership_data', 40);
function cpm_dong_show_membership_data($menu_links)
{

    $menu_links = array_slice($menu_links, 0, 5, true)
        + array('show-membership-data' => 'My Memberships')
        + array_slice($menu_links, 5, NULL, true);

    return $menu_links;
}
// register permalink endpoint
add_action('init', 'cpm_dong_my_membership_endpoint');
function cpm_dong_my_membership_endpoint()
{

    add_rewrite_endpoint('show-membership-data', EP_PAGES);
}
// content for the new page in My Account, woocommerce_account_{ENDPOINT NAME}_endpoint
add_action('woocommerce_account_show-membership-data_endpoint', 'cpm_dong_my_membership_endpoint_content');
function cpm_dong_my_membership_endpoint_content()
{

    // of course you can print dynamic content here, one of the most useful functions here is get_current_user_id()
?>
    <div class='qr-tiger-code-generator'>
        <p><?php _e('My Memberships ', 'cpm-dongtrader') ?></p>
        <?php echo do_shortcode('[pmpro_account]');
        ?>
    </div>

<?php
}
/* show memebership data on woocommerce tab  ends*/
/* show custom order export on woocommerce tab */

add_filter('woocommerce_account_menu_items', 'cpm_dong_show_custom_order_form', 40);
function cpm_dong_show_custom_order_form($menu_links)
{

    $menu_links = array_slice($menu_links, 0, 5, true)
        + array('show-order-form' => 'Add Your Affiliate Order')
        + array_slice($menu_links, 5, NULL, true);

    return $menu_links;
}
// register permalink endpoint
add_action('init', 'cpm_dong_custom_order_form_endpoint');
function cpm_dong_custom_order_form_endpoint()
{

    add_rewrite_endpoint('show-order-form', EP_PAGES);
}
// content for the new page in My Account, woocommerce_account_{ENDPOINT NAME}_endpoint
add_action('woocommerce_account_show-order-form_endpoint', 'cpm_dong_custom_order_form_endpoint_content');
function cpm_dong_custom_order_form_endpoint_content()
{

    // of course you can print dynamic content here, one of the most useful functions here is get_current_user_id()
?>
    <div class='dongtraders_order_export'>

        <?php dongtraders_order_export_form();
        ?>
    </div>

<?php
}



/* show custom order exporter on woocommerce tab */

add_filter('woocommerce_account_menu_items', 'cpm_dong_show_custom_order_csv_exporter', 60);
function cpm_dong_show_custom_order_csv_exporter($menu_links)
{

    $menu_links = array_slice($menu_links, 0, 5, true)
        + array('export-affiliate-order' => 'Export Your Affiliate Order')
        + array_slice($menu_links, 5, NULL, true);

    return $menu_links;
}
// register permalink endpoint
add_action('init', 'dong_exporter_order_csv_endpoint');
function dong_exporter_order_csv_endpoint()
{

    add_rewrite_endpoint('export-affiliate-order', EP_PAGES);
}
// content for the new page in My Account, woocommerce_account_{ENDPOINT NAME}_endpoint
add_action('woocommerce_account_export-affiliate-order_endpoint', 'dong_exporter_order_csv_endpoint_content');
function dong_exporter_order_csv_endpoint_content()
{

    // of course you can print dynamic content here, one of the most useful functions here is get_current_user_id()
?>
    <div class='dongtraders_order_export'>

        <?php dongtraders_show_user_affilate_order();
        ?>
    </div>

<?php
}


/**
 * Automatically add product to cart on visit
 */

add_action('template_redirect', 'dongtraders_product_link_with_membership_goes_checkoutpage');
function dongtraders_product_link_with_membership_goes_checkoutpage()
{
    if (class_exists('WooCommerce')) {
        if (is_product()) {
            WC()->cart->empty_cart();
            global $product;
            $checkout_url = wc_get_checkout_url();
            $product_id = $product->get_id();
            if (!empty(($_GET['add']))) {
                $check_add_product = $_GET['add'];
                if ($check_add_product == '1') {
                    $has_already_bought_product = dongtraders_has_bought_product_items($product->get_id());

                    if ($has_already_bought_product) {
                        $product_page = get_permalink($product_id);
                        wp_redirect($product_page);
                        exit();
                    } else {
                        WC()->cart->add_to_cart($product_id);
                        wp_redirect($checkout_url);
                        exit();
                    }
                }
            }
            //For varaitions direct check out

            if (isset($_GET['varid'])) {
                $check_add_varition_product = $_GET['varid'];
                $variation = wc_get_product($check_add_varition_product);

                if($variation) {
                    $product = wc_get_product($variation->get_parent_id());
                    $parent_id = $product->id;

                    $get_quantity_yam =  dongtraders_set_product_quantity($parent_id);

                    if (!empty($get_quantity_yam)) {
                        WC()->cart->add_to_cart($check_add_varition_product, $get_quantity_yam);
                    } else {
                        WC()->cart->add_to_cart($check_add_varition_product);
                    }



                    wp_redirect($checkout_url);
                    exit();
                }else{
                    
                    exit();
                }
            }
        }
    }
    return;
}







// add_action('woocommerce_before_checkout_form', 'dongtraders_check_user_bought_product_already_bought', 12);
// add_action('woocommerce_after_shop_loop_item', 'dongtraders_check_user_bought_product_already_bought', 30);
// add_action('woocommerce_before_add_to_cart_quantity', 'dongtraders_check_user_bought_product_already_bought', 30);

function dongtraders_check_user_bought_product_already_bought()
{
    global $product;
    if (!is_user_logged_in()) return;
    if (!empty(dongtraders_get_membership_link_product($product->get_id()))) {
        $accountpage = get_permalink(wc_get_page_id('myaccount'));
        echo '<div>Your Account is already register As Membership. Please Visit <a href="' . $accountpage . '">Account page</a></div>';
        echo '<style>
        .quantity, .cart .single_add_to_cart_button, .ajax_add_to_cart {
            display: none !important;
        }
        table.variations {
        display: none;
        }
    
        </style>';
    }
}


/* Checking if the user is logged in and has a membership level. */
function dongtraders_check_user_membership_level()
{
    if (is_user_logged_in() && function_exists('pmpro_hasMembershipLevel') && pmpro_hasMembershipLevel()) {
        global $current_user;
        $current_user->membership_level = pmpro_getMembershipLevelForUser($current_user->ID);
        $get_membership_level = $current_user->membership_level;
        return $get_membership_level->name;
    }
}



/* Getting the membership level of the product. */
function dongtraders_get_membership_link_product($product_id)
{
    $get_product_member_level =  get_post_meta($product_id, '_membership_product_level', true);
    return $get_product_member_level;
}


function dongtraders_has_bought_product_items($product_id)
{
    global $woocommerce;
    $user_id = get_current_user_id();
    if (0 == $user_id) return false;

    // $current_user = wp_get_current_user();
    // $customer_email = $current_user->email;

    $customer_orders = get_posts(array(
        'numberposts' => -1,
        'meta_key'    => '_customer_user',
        'meta_value'  => $user_id,
        'post_type'   => wc_get_order_types(),
        'post_status' => array_keys(wc_get_is_paid_statuses())
    ));

    $get_product_member_level =  get_post_meta($product_id, '_membership_product_level', true);

    if (count($customer_orders) > 1 && (bool)$get_product_member_level) {
        return true;
    } else {
        return false;
    }
}



// Dongtrader fields used in both pmpro settings and order meta

add_filter('membership_level_fields', 'dongtrader_membership_level_fields', 10, 2);

function dongtrader_membership_level_fields($f, $type)
{
    //$type must be true or false if pmpro true else if order false
    $display_text = $type ? __('Rate (%):', 'cpm-dongtrader') : __('Total Amount:', 'cpm-dongtrader');
    $fields = array(
        'mega_cashback_v'  => sprintf(__('Cashback To Voter  %s', 'cpm-dongtrader'), $display_text),
        'mega_cashback_d'  => sprintf(__('Cashback To Distributor  %s', 'cpm-dongtrader'), $display_text),
        'mega_reserve'     => sprintf(__('Reserve Savings %s ', 'cpm-dongtrader'),$display_text),
        'mega_cashout_e'   => sprintf(__('Early Cashout %s ', 'cpm-dongtrader'), $display_text),
        'mega_platform_c'  => sprintf(__('Platform Costs %s', 'cpm-dongtrader'), $display_text),
        'mega_members_r'   => sprintf(__('Members Reward %s', 'cpm-dongtrader'), $display_text),
        'mega_mr_di'       => sprintf(__('Reward To Individual %s', 'cpm-dongtrader'), $display_text),
        'mega_mr_dg'       => sprintf(__('Reward To Group  %s', 'cpm-dongtrader'), $display_text),
        'mega_mr_dca'      => sprintf(__('Commision From Reward %s', 'cpm-dongtrader'), $display_text),
        'mega_mr_c_di'     => sprintf(__('Commision To Individual %s', 'cpm-dongtrader'), $display_text),
        'mega_mr_c_dg'     => sprintf(__('Commision To Group %s', 'cpm-dongtrader'), $display_text),
        'mega_comm_c_ds'   => sprintf(__('Commission To Smallstreet  %s', 'cpm-dongtrader'), $display_text),
        'mega_affid'       => sprintf(__('Affiliate Id :', 'cpm-dongtrader')),
        'mega_treasury'    => sprintf(__('Treasury :', 'cpm-dongtrader'))
        

    );

    //fields not to display on pmpro backend
    if ($type)  unset($fields['mega_affid']) ; unset($fields['mega_treasury']) ;
    
    return $fields;
}


// Adds new field for order metas backend
add_action('woocommerce_admin_order_data_after_order_details', 'dong_editable_order_meta_general');

function dong_editable_order_meta_general($order)
{

?>
    <br class="clear" />
    <h3>
        <?php _e('Display Order Trading Details', 'cpm-dongtrader') ?>
        <a href="#" class="edit_address"><?php _e('Edit More Trading Details', 'cpm-dongtrader') ?></a>
    </h3>
    <?php
    $fields = apply_filters('membership_level_fields', array(), false);
    ?>
    <div class="address">
        <!-- Items Here will appear on blur -->
        <?php
        foreach ($fields as $key => $value) {
            $meta_val = !empty($order->get_meta($key)) ? $order->get_meta($key) : 0;
            $printables = $key != 'mega_affid' ? '<p>' . $value . ' $' . $meta_val . '</p>' : '<p>' . $value . ' ' . $meta_val . '</p>';
            echo $printables;
        }
        ?>
    </div>
    <div class="edit_address">
        <?php
        foreach ($fields as $key => $value) {
            $meta_vals = $order->get_meta($key);
            woocommerce_wp_text_input(array(
                'id' => $key,
                'label' => $value,
                'value' => $meta_vals,
                'wrapper_class' => 'form-field-wide'
            ));
        }
        ?>
    </div>
<?php
}

// Save meta fields values for order
add_action('woocommerce_process_shop_order_meta', 'dong_save_general_details');

function dong_save_general_details($order_id)
{

    $fields = apply_filters('membership_level_fields', array(), false);

    foreach ($fields as $k => $v) {

        update_post_meta($order_id, $k, wc_clean($_POST[$k]));
    }
}

add_action('pmpro_membership_level_before_billing_information', 'dongtrader_pmpro_memberships_custom_fields', 10, 1);

function dongtrader_pmpro_memberships_custom_fields($lv)
{
    $fields = apply_filters('membership_level_fields', array(), true);
?>
    <div id="membreship-extra-details" class="pmpro_section" data-visibility="shown" data-activated="true">
        <div class="pmpro_section_toggle">
            <button class="pmpro_section-toggle-button" type="button" aria-expanded="true">
                <span class="dashicons dashicons-arrow-up-alt2"></span>
                <?php esc_html_e('Membership Extra Settings', 'cpm-dongtrader'); ?>
            </button>
        </div>
        <div class="pmpro_section_inside">
            <table class="form-table">
                <tbody>
                    <?php foreach ($fields as $value => $key) {
                        $current_saved_val = get_pmpro_membership_level_meta($lv->id, $value, true);

                    ?>
                        <tr>
                            <th scope="row" valign="top">
                                <label for="<?php echo $value ?>"><?php echo $key; ?></label>
                            </th>
                            <td>
                                <input name="<?php echo $value ?>" type="number" value="<?php echo $current_saved_val; ?>" class="regular-text" autocomplete="off" step="0.01" />
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
<?php
}

add_action("pmpro_save_membership_level", 'dongtrader_save_membership_level_custom_fields', 10, 1);

function dongtrader_save_membership_level_custom_fields($id)
{
    $fields = apply_filters('membership_level_fields', array(), true);
    foreach ($fields as $value => $key) {
        $prev_value = get_pmpro_membership_level_meta($id, $value);
        $posted_vals = wc_clean($_POST[$value]);
        update_pmpro_membership_level_meta($id, $value, $posted_vals);
    }
}

function get_pmpro_extrafields_meta($memId)
{
    $pm_fields      = apply_filters('membership_level_fields', array(), true);
    $new_pm_vals = [];
    foreach (array_keys($pm_fields) as $p) {
        $new_pm_vals[$p] = get_pmpro_membership_level_meta($memId, $p, true);
    }

    return $new_pm_vals;
}

function round_decimal_ifneeded($number) {
    $integerPart = floor($number);
    $decimalPart = $number - $integerPart;
    
    if ($decimalPart > 0.5) {
        $roundedNumber = ceil($number);
    } else {
        $roundedNumber = $integerPart + $decimalPart;
    }
    
    return $roundedNumber;
}

function get_numeric_price($price) {
    $formatted_price = wc_price($price); // Format the price

    // Remove any HTML tags from the formatted price
    $plain_text_price = strip_tags($formatted_price);

    $currency_symbol = get_woocommerce_currency_symbol(); // Get the currency symbol

    // Remove the currency symbol from the plain text price
    $numeric_value = str_replace($currency_symbol, '', $plain_text_price);

    return $numeric_value;
}


/**
 * Step 1 :When Order is received get mebership level id from product meta
 * Step 2 :From  the membership level id above get all values of membership extra details
 * Step 3 : Make the money distibution functionality from the membership level data
 * Step 4 : Update to order meta 
 * Distribution Formula : rebate=7 process=3 profit(50i , 40g ,10c) comm(50i,40g,10t) , fmla for profit calculation second case reserve+cost+earning- total price 
 */
function dongtrader_product_price_distribution($price, $proId, $oid, $cid)
{

    $gf_membership_checkbox = get_post_meta($proId, '_glassfrog_checkbox', true);
    // Get boolean by checking checkbox
    $checkgf = $gf_membership_checkbox == 'on' ? true : false;

    // Get membership level of the product
    $member_level = get_post_meta($proId, '_membership_product_level', true);

    // Get the pmpro custom fields in an array
    $pm_meta_vals = get_pmpro_extrafields_meta($member_level);

    //convert pmpro rates to the values
    $cashback_v     = $pm_meta_vals['mega_cashback_v']/100*$price;
    $cashback_d     = $pm_meta_vals['mega_cashback_d']/100*$price;
    $reserve        = $pm_meta_vals['mega_reserve']/100*$price;
    $cashout_e      = $pm_meta_vals['mega_cashout_e']/100*$price;
    $platform_c     = $pm_meta_vals['mega_platform_c']/100*$price;
    $members_reward = $pm_meta_vals['mega_members_r']/100*$price;
    $mr_di          = $pm_meta_vals['mega_mr_di']/100*$members_reward;
    $mr_dg          = $pm_meta_vals['mega_mr_dg']/100*$members_reward;
    $mr_c           = $pm_meta_vals['mega_mr_dca']/100*$members_reward;
    $mr_c_di        = $pm_meta_vals['mega_mr_c_di']/100*$mr_c;
    $mr_c_dg        = $pm_meta_vals['mega_mr_c_dg']/100*$mr_c;
    $mr_c_ds        = $pm_meta_vals['mega_comm_c_ds']/100*$mr_c;
    $treasury       = $price-$cashback_v+$cashback_d;


    $aid = get_post_meta($oid, 'mega_affid', true);

    if($checkgf) :

        $order_items=[
            'mega_cashback_v' =>get_numeric_price($cashback_v), 
            'mega_cashback_d' =>get_numeric_price($cashback_d),
            'mega_reserve'    =>get_numeric_price($reserve),
            'mega_cashout_e'  =>get_numeric_price($cashout_e),
            'mega_platform_c' =>get_numeric_price($platform_c),
            'mega_members_r'  =>get_numeric_price($members_reward),
            'mega_mr_di'      =>get_numeric_price($mr_di),
            'mega_mr_dg'      =>get_numeric_price($mr_dg),
            'mega_mr_dca'     =>get_numeric_price($mr_c),
            'mega_mr_c_di'    =>get_numeric_price($mr_c_di),
            'mega_mr_c_dg'    =>get_numeric_price($mr_c_dg),
            'mega_comm_c_ds'  =>get_numeric_price($mr_c_ds),
            'mega_affid'      =>$aid
        ];
    
        // $order_items = [
        //     'dong_reabate'    => number_format($rebate_amount, 2),
        //     'dong_process'    => number_format($process_amount, 2),
        //     'dong_reserve'    => number_format(round_decimal_ifneeded($reserve_amount), 2),
        //     'dong_cost'       => number_format(round_decimal_ifneeded($cost_amount), 2),
        //     'dong_profit'     => number_format(round_decimal_ifneeded($profit_amount), 2),
        //     'dong_earning'    => number_format(round_decimal_ifneeded($earning_amount), 2),
        //     'dong_profit_di'  => number_format(round_decimal_ifneeded($dong_profit_affiliate), 2),
        //     'dong_profit_dg'  => number_format(round_decimal_ifneeded($dong_profit_group), 2),
        //     'dong_profit_dca' => number_format(round_decimal_ifneeded($dong_profit_commission), 2),
        //     'dong_comm_cdi'   => number_format(round_decimal_ifneeded($dong_commission_affiliate), 2),
        //     'dong_comm_cdg'   => number_format(round_decimal_ifneeded($dong_commission_group), 2),
        //     'dong_comm_cde'   => number_format(round_decimal_ifneeded($dong_commission_earning), 2),
        //     'dong_treasury'   => 0, 
        //     'mega_affid'      => $aid
        // ];
    else :

        // $order_items = [
        //     'dong_reabate'    => number_format($rebate_amount, 2),
        //     'dong_process'    => number_format($process_amount, 2),
        //     'dong_reserve'    => number_format(round_decimal_ifneeded($reserve_amount), 2),
        //     'dong_cost'       => number_format(round_decimal_ifneeded($cost_amount), 2),
        //     'dong_profit'     => number_format(round_decimal_ifneeded($profit_amount), 2),
        //     'dong_earning'    => number_format(round_decimal_ifneeded($earning_amount), 2),
        //     'dong_profit_di'  => 0,
        //     'dong_profit_dg'  => 0,
        //     'dong_profit_dca' => 0,
        //     'dong_comm_cdi'   => 0,
        //     'dong_comm_cdg'   => 0,
        //     'dong_comm_cde'   => 0, 
        //     'dong_treasury'   => number_format($profit_amount, 2),
        //     'mega_affid'      => $aid
        // ];

        $order_items=[
            'mega_cashback_v' =>get_numeric_price($cashback_v), 
            'mega_cashback_d' =>get_numeric_price($cashback_d),
            'mega_reserve'    =>get_numeric_price($reserve),
            'mega_cashout_e'  =>get_numeric_price($cashout_e),
            'mega_platform_c' =>get_numeric_price($platform_c),
            'mega_members_r'  =>get_numeric_price($members_reward),
            'mega_mr_di'      =>get_numeric_price($mr_di),
            'mega_mr_dg'      =>get_numeric_price($mr_dg),
            'mega_mr_dca'     =>get_numeric_price($mr_c),
            'mega_mr_c_di'    =>get_numeric_price($mr_c_di),
            'mega_mr_c_dg'    =>get_numeric_price($mr_c_dg),
            'mega_comm_c_ds'  =>get_numeric_price($mr_c_ds),
            'mega_affid'      => $aid,
            'mega_treasury'   => get_numeric_price($treasury)
        ];

    endif;


    // Update all data to order meta
    foreach ($order_items as $k => $v) {
        update_post_meta($oid, $k, sanitize_text_field($v));
    }
}

/* check if provided product id from csv is on product */
if (!function_exists('is_product_check')) {
    function is_product_check($product_id)
    {
        return 'product' === get_post_type($product_id);
    }
}
/* uploader order csv  */
function dongtraders_csv_order_importer1()
{

    if (isset($_POST['import_csv'])) {

        $upload_dir = wp_upload_dir();
        // $upload_dir = $uploads['baseurl'];
        $file = $upload_dir['basedir'] . '/' . $_FILES['get_file']['name'];
        $fileurl = $upload_dir['baseurl'] . '/' . $_FILES['get_file']['name'];
        if (!move_uploaded_file(
            $_FILES['get_file']['tmp_name'],
            $file
        )) {
            print_r('Failed to move uploaded file.');
        }

        if (($open = fopen($fileurl, "r")) !== FALSE) {
            // Skip the first line
            $first_row = true;
            $get_orders = array();
            $headers = array();

            while (($data = fgetcsv($open, 1000, ",")) !== FALSE) {

                if ($first_row) {
                    $headers = $data;
                    $first_row = false;
                } else {
                    $get_orders[] = array_combine($headers, array_values($data));
                }
            }


            fclose($open);
            /* echo "<pre>";
							var_dump($get_orders);
							echo "</pre>"; */
            $order_status_msg = '<div class="error-box">Order Data could not Imported ! Please Try again</div>';
            foreach ($get_orders as $get_order) {
                # code...

                $billing_first_name = $get_order['billing_first_name'];
                $billing_last_name = $get_order['billing_last_name'];
                $billing_phone = $get_order['billing_phone'];
                $billing_address_1 = $get_order['billing_address_1'];
                $billing_postcode = $get_order['billing_postcode'];
                $billing_city = $get_order['billing_city'];
                $billing_state = $get_order['billing_state'];
                $billing_country = $get_order['billing_country'];
                $product_id = $get_order['product_id'];
                $variation_id = $get_order['variation_id'];
                $customer_email = $get_order['customer_email'];
                $affiliate_user_id = $get_order['affilate_user_id'];
                // echo $product_id . 'this is product id';

                if ($variation_id == '0') {
                    $product_id_csv =  $product_id;
                } else {
                    $product_id_csv = $variation_id;
                }

                if (is_product_check($product_id)) {

                    //echo 'Yes-' . $product_id_csv;
                } else {

                    echo 'Invalid Product ID: ' . $product_id_csv . '<br>';
                }

                if (is_product_check($product_id) && !email_exists($customer_email)) {
                    $random_password = wp_generate_password();
                    $get_product_membership_level = get_post_meta($product_id, '_membership_product_level', true);


                    $user_id = wc_create_new_customer($customer_email, $billing_first_name . rand(10, 100), $random_password);
                    pmpro_changeMembershipLevel($get_product_membership_level, $user_id);

                    // dongtrader_user_registration_hook($user_id);
                    // add billing and shipping addresses

                    $address = array(
                        'first_name' => $billing_first_name,
                        'last_name'  => $billing_last_name,
                        'company'    => '',
                        'email'      => $customer_email,
                        'phone'      => $billing_phone,
                        'address_1'  => $billing_address_1,
                        'address_2'  => '',
                        'city'       => $billing_city,
                        'state'      => $billing_state,
                        'postcode'   => $billing_postcode,
                        'country'    => $billing_country
                    );

                    $order = wc_create_order();
                    
                    $order->set_customer_id($user_id);

                    $order->update_meta_data('mega_affid', $affiliate_user_id);
                    
                    $get_quantity_yam = get_post_meta($product_id, '_qty_args', true);
                    
                    if (is_array($get_quantity_yam)) {
                        $get_quantity_yam_no = dongtraders_set_product_quantity($product_id);
                        $order->add_product(wc_get_product($product_id_csv), $get_quantity_yam_no);
                    } else {
                        $order->add_product(wc_get_product($product_id_csv), 1);
                    }


                    // add shipping
                    $shipping = new WC_Order_Item_Shipping();
                    $shipping->set_method_title('Free shipping');
                    $shipping->set_method_id('free_shipping:1'); // set an existing Shipping method ID
                    $shipping->set_total(0); // optional
                    $order->add_item($shipping);

                    $order->set_address($address, 'billing');
                    $order->set_address($address, 'shipping');

                    // add payment method
                    $order->set_payment_method('cod');
                    //$order->set_payment_method_title('Credit/Debit card');

                    // order status
                    $order->set_status('wc-completed', 'Order is created From Importer');
                    // calculate and save
                    $order->calculate_totals();

                    
                    $order->save();
                    

                    $product = wc_get_product($product_id_csv);
                    $product_price =  $product->get_price();
                    /*  echo $product_price . '-product price'; */

                    $order_id = $order->get_id();
                    if ($order_id) {
                        $order_status_msg = '<div class="success-box">Order Data Imported Sucessfully. Please Refresh Order Table.</div>';
                          $order_obj= wc_get_order($order_id); 
                          
                          mega_manage_custom_mlm_operations($order_obj);
                    }
                  
                }
                //}
            }
            echo $order_status_msg;
        }
    }
}
/* end of original  */


/* edited by dr start */


// Step 1: Import CSV and parse the data
function dongtraders_csv_order_importer() {
    if (isset($_POST['import_csv'])) {
        $upload_dir = wp_upload_dir();
        $file = $upload_dir['basedir'] . '/' . $_FILES['get_file']['name'];
        $fileurl = $upload_dir['baseurl'] . '/' . $_FILES['get_file']['name'];
        if (!move_uploaded_file($_FILES['get_file']['tmp_name'], $file)) {
            print_r('Failed to move the uploaded file.');
        }

        if (($open = fopen($fileurl, "r")) !== FALSE) {
            $first_row = true;
            $get_orders = array();
            $headers = array();

            while (($data = fgetcsv($open, 1000, ",")) !== FALSE) {
                if ($first_row) {
                    $headers = $data;
                    $first_row = false;
                } else {
                    $get_orders[] = array_combine($headers, array_values($data));
                }
            }
            fclose($open);

            // Step 2: Process each order from CSV data
            $order_status_msg = '<div class="error-box">Order Data could not be imported! Please try again.</div>';
            foreach ($get_orders as $get_order) {
                $billing_first_name = $get_order['billing_first_name'];
                $billing_last_name = $get_order['billing_last_name'];
                $billing_phone = $get_order['billing_phone'];
                $billing_address_1 = $get_order['billing_address_1'];
                $billing_postcode = $get_order['billing_postcode'];
                $billing_city = $get_order['billing_city'];
                $billing_state = $get_order['billing_state'];
                $billing_country = $get_order['billing_country'];
                $product_id = $get_order['product_id'];
                $variation_id = $get_order['variation_id'];
                $customer_email = $get_order['customer_email'];
                $affiliate_user_id = $get_order['affilate_user_id'];

                // Step 3: Check if the product exists and customer email doesn't exist
                if (is_product_check($product_id) && !email_exists($customer_email)) {
                    $random_password = wp_generate_password();
                    $get_product_membership_level = get_post_meta($product_id, '_membership_product_level', true);
                    $display_name = $billing_first_name . ' ' . $billing_last_name;
                   // $user_id = wc_create_new_customer($customer_email, $billing_first_name . rand(10, 100), $random_password);
                    $user_id = wc_create_new_customer($customer_email, $display_name, $random_password);

                    // Set the display name

//wp_update_user(array('ID' => $user_id, 'display_name' => $display_name));

                    pmpro_changeMembershipLevel($get_product_membership_level, $user_id);

                    // Step 4: Create the order
                    $order = wc_create_order();
                    $order->set_customer_id($user_id);
                    $order->update_meta_data('mega_affid', $affiliate_user_id);

                    // Step 5: Add products to the order
                    $get_quantity_yam = get_post_meta($product_id, '_qty_args', true);
                    $product_id_csv = $variation_id == '0' ? $product_id : $variation_id;
                    if (is_array($get_quantity_yam) && !empty($get_quantity_yam)) {
                        $get_quantity_yam_no = dongtraders_set_product_quantity($product_id);
                       // $get_quantity_yam_no = '7';
                        $order->add_product(wc_get_product($product_id_csv), $get_quantity_yam_no);
                    } else {
                        $get_quantity_yam_no = '1'; 
                        $order->add_product(wc_get_product($product_id_csv), 1);
                    }

                    // Step 6: Add shipping
                    $shipping = new WC_Order_Item_Shipping();
                    $shipping->set_method_title('Free shipping');
                    $shipping->set_method_id('free_shipping:1'); // set an existing Shipping method ID
                    $shipping->set_total(0); // optional
                    $order->add_item($shipping);

                    // Step 7: Set billing and shipping addresses
                    $address = array(
                        'first_name' => $billing_first_name,
                        'last_name'  => $billing_last_name,
                        'company'    => '',
                        'email'      => $customer_email,
                        'phone'      => $billing_phone,
                        'address_1'  => $billing_address_1,
                        'address_2'  => '',
                        'city'       => $billing_city,
                        'state'      => $billing_state,
                        'postcode'   => $billing_postcode,
                        'country'    => $billing_country
                    );
                    $order->set_address($address, 'billing');
                    $order->set_address($address, 'shipping');

                    // Step 8: Add payment method and set order status
                    $order->set_payment_method('cod');
                    $order->set_status('wc-completed', 'Order is created From Importer');

                    // Step 9: Calculate and save the order
                    $order->calculate_totals();
                    $order->save();
  // Step 10: Handle variation product price and quantity
  if ($variation_id != '0') {
    $order_items = $order->get_items();
    foreach ($order_items as $order_item_id => $order_item) {
        $product_id_in_order = $order_item->get_product_id();
        if ($product_id_in_order == $product_id_csv) {
            $product = wc_get_product($product_id_csv);
            $order_item->set_quantity($get_quantity_yam_no);

            // Update variation data
            $variation_data = array(
                'variation_id' => $variation_id,
                'quantity' => $get_quantity_yam_no,
            );

            // Update the order item with variation data
            wc_update_order_item($order_item_id, $variation_data);
        }
    }
}

                    // Step 11: Update the order status message
                    $order_id = $order->get_id();
                    if ($order_id) {
                        $order_status_msg = '<div class="success-box">Order Data Imported Successfully. Please Refresh the Order Table.</div>';
                        $order_obj = wc_get_order($order_id);
                        mega_manage_custom_mlm_operations($order_obj);
                    }
                }
            }
            echo $order_status_msg;
        }
    }
}


/* edited by dr end */


/**
 * Save User data to glassfrog api from orderid
 *  
 */
// add_action('woocommerce_thankyou', 'dongtrader_after_order_received_process', 10);

function dongtrader_after_order_received_process($order_id)
{
    $order          = wc_get_order($order_id);

    $customer_id    = $order->get_user_id();

    $items          =  $order->get_items();

    $product_info = [];

    foreach ($items as $item) {
        
        $product = $item->get_product();

        if($product->is_type('variation')){

            $product_info[] = [ 'var' => true ,'var_id'=>$product->get_id() , 'parent_id' => $item->get_product_id() ];

        }else{

            $product_info[] = [ 'var' => false ,'var_id'=>null , 'parent_id' => $item->get_product_id() ];
        }

    }

    $filtered_id = $product_info[0]['var'] ? $product_info[0]['var_id'] : $product_info[0]['parent_id'];

    // dongtrader_user_registration_hook($customer_id, $filtered_id, $order_id );

    // mega_manage_mlm_user($customer_id, $filtered_id ,$order_id);
   
    $current_pro = wc_get_product($filtered_id);

    $current_price = $current_pro->get_price();

    dongtrader_product_price_distribution($current_price,$product_info[0]['parent_id'], $order_id, $customer_id);
    
    dong_set_user_role($customer_id, $filtered_id);
}

add_action('woocommerce_checkout_order_created', 'mega_manage_custom_mlm_operations');
function mega_manage_custom_mlm_operations($order){

    //global database variable
    global $wpdb;

    //mlm customer user table from our database
    $mlm_users_table = $wpdb->prefix . 'mega_mlm_customers';

    //mlm customer sales custom table
    $mlm_sales_table = $wpdb->prefix . 'mega_mlm_purchases';

    // mlm downline table
    $mlm_downline_table = $wpdb->prefix . 'mega_mlm_downline';

    // mlm groups table
    $mlm_group_table  = $wpdb->prefix . 'mega_mlm_groups';
    
    // Customer id
    $customer_id    = $order->get_user_id();

    //all items of an order
    $items          =  $order->get_items();

    // get sponsor id 
    $sponsor_id     =  $order->get_meta('mega_affid');

    //set booleans to check
    $refferal =  !empty($sponsor_id) ? (int) $sponsor_id : null;
    
    // looping inside all items inside order
    foreach($items as $item){

        $product            = $item->get_product();

        $parent_product_id  = $item->get_product_id();

        $actual_product_id  = $product->is_type('variation') ? $item->get_variation_id() : $item->get_product_id();

        $product_name       = $item->get_name();

        $price              = $product->get_price();

        $product_quantity   = $item->get_quantity();

        dongtrader_product_price_distribution($price, $parent_product_id, $order->get_id(), $customer_id);

        // lets check if the customer already exists
        $check_customer = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT user_id FROM $mlm_users_table WHERE user_id = %d",
                $customer_id
            )
        );

        dong_set_user_role($customer_id, $actual_product_id);
        //if $check_customer == null customer doesnot exists and vice versa 
        $customer_exists = is_null($check_customer) ? false : true;

        //insertable data on mlm_sales_table
        $mlm_purchase_data = array(
            'customer_id' => (int) $check_customer,
            'order_id' => (int) $order->get_id(),
            'allocation_status' =>0
        );

        if(!is_null($refferal)) $mlm_purchase_data['sponsor_id'] = $refferal;

        //if customer alredy in mlm database
        if($customer_exists){

            $group_id =  $wpdb->get_var($wpdb->prepare("SELECT customer_group_id FROM $mlm_users_table WHERE user_id=%d", (int) $check_customer));

            $wpdb->insert($mlm_sales_table, $mlm_purchase_data); 

            if(isset($group_id)){

                // prepare for update to group table
                $group_update = $wpdb->prepare("UPDATE $mlm_group_table SET distribution_status = 2 WHERE group_id = %d", (int) $group_id);
                
                $wpdb->query($group_update);
            }

        } 

        if(!$customer_exists) {

            //for this case orders datas doesnt exists on our custom database table so we need to call the glassfrog api and insert data accordingly
            $gf_checkbox = get_post_meta($parent_product_id, '_glassfrog_checkbox', true);

            //bool to check meta
            $gf_check = $gf_checkbox == 'on' ? true : false;

            if ($gf_check) {

                //get user object
                $user_info = get_userdata($customer_id);

                //get user email
                $email = $user_info->user_email;

                //api request string
                $str = '{"people": [{
                    "name": "' . $user_info->display_name . '",
                    "email": "' . $email . '",
                    "external_id": "' . $customer_id . '",
                    "tag_names": ["tag 1", "tag 2"]
                    }]
                    }';

                //api call
                $samp = glassfrog_api_request('people', $str, "POST");

                if ($samp && isset($samp)):

                    // glassfrog id from the api
                    $gf_id = $samp->people[0]->id;

                    // glassfrog persons  name
                    $gf_name = $samp->people[0]->name;

                    // inserting data to the customers
                    $customer_data = array(
                        'user_id'             => (int) $customer_id,
                        'upline_id'           => $refferal,
                        'glassfrog_person_id' => (int) $gf_id,
                        'person_name'         => $gf_name,	
                        'user_status'         => 0,
                    );

                    if(is_null($refferal)) unset($customer_data['upline_id']);

                    $wpdb->insert($mlm_users_table, $customer_data);

                    $downline_data = array(
                        'user_id' => $refferal,
                        'downline_user_id' => $customer_id,
                    );
                    
                    $wpdb->insert($mlm_downline_table, $downline_data);

                    $mlm_purchase_data['customer_id'] =  (int) $customer_id;

                    $mlm_purchase_data['allocation_status'] = 1;

                    $wpdb->insert($mlm_sales_table, $mlm_purchase_data);

                endif;

            } 

        }

    }


}
