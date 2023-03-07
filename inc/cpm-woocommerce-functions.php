<?php
/* Woocommerce hooks to add fields to my acount page */
/* Always Update Permalink after adding a new endpoint */

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
        <img src="' . $user_meta_qrs . '" alt="Invalid QR Image">
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



/* Show affiliate referrals menu  */

add_filter('woocommerce_account_menu_items', 'cpm_dong_show_membership_affilate_Data', 40);
function cpm_dong_show_membership_affilate_Data($menu_links)
{

    $menu_links = array_slice($menu_links, 0, 5, true)
        + array('show-membership-affiliate-data' => 'Affiliate Referrals')
        + array_slice($menu_links, 5, NULL, true);

    return $menu_links;
}
// register permalink endpoint
add_action('init', 'cpm_dong_my_membership_affilate_data_endpoint');
function cpm_dong_my_membership_affilate_data_endpoint()
{

    add_rewrite_endpoint('show-membership-affiliate-data', EP_PAGES);
}
// content for the new page in My Account, woocommerce_account_{ENDPOINT NAME}_endpoint
add_action('woocommerce_account_show-membership-affiliate-data_endpoint', 'cpm_dong_my_membership_affilate_data_endpoint_content');
function cpm_dong_my_membership_affilate_data_endpoint_content()
{
    $user_ID = get_current_user_id();
    $get_user_affilates = pmpro_affiliates_getAffiliatesForUser($user_ID);
    //var_dump($get_user_affilates);

    ?>
    <table class="affilate-data">
        <tr>
            <th>
                Referral Code
            </th>
            <th>
                Commission Rate
            </th>
        </tr>
        <tr>
            <?php
            foreach ($get_user_affilates as $get_user_affilate) {

                echo '<td>' . $get_user_affilate->code . '</td>';
                echo '<td>' . $get_user_affilate->commissionrate . '</td>';
            }

            ?>
        </tr>
    </table>

<?php

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
                WC()->cart->add_to_cart($check_add_varition_product);
                wp_redirect($checkout_url);
                exit();
            }
        }
    }
    return;
}



// add_action('woocommerce_before_checkout_form', 'dongtraders_check_user_bought_product_already_bought', 12);
add_action('woocommerce_after_shop_loop_item', 'dongtraders_check_user_bought_product_already_bought', 30);
add_action('woocommerce_before_add_to_cart_quantity', 'dongtraders_check_user_bought_product_already_bought', 30);

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
        'dong_reabate'      => sprintf(__('Rebate  %s', 'cpm-dongtrader'), $display_text),
        'dong_processamt'   => sprintf(__('Process %s', 'cpm-dongtrader'), $display_text),
        //Is used only for orders meta
        'dong_profitamt'    => sprintf(__('Profit %s', 'cpm-dongtrader'), $display_text),
        'dong_reserve'      => sprintf(__('Reserve Amount: ', 'cpm-dongtrader')),
        'dong_earning_amt'   => sprintf(__('Earnings Amount: ', 'cpm-dongtrader')),
        //Extra Fields Ends
        'dong_cost'         => sprintf(__('Total Cost: ', 'cpm-dongtrader')),
        'dong_profit_di'    => sprintf(__('Profit To Individual %s', 'cpm-dongtrader'), $display_text),
        'dong_profit_dg'    => sprintf(__('Profit To Group  %s', 'cpm-dongtrader'), $display_text),
        'dong_profit_dca'   => sprintf(__('Commision From Profit %s', 'cpm-dongtrader'), $display_text),
        'dong_comm_cdi'     => sprintf(__('Commision To Individual %s', 'cpm-dongtrader'), $display_text),
        'dong_comm_cdg'     => sprintf(__('Commision To Group %s', 'cpm-dongtrader'), $display_text),
        'dong_earning_per'  => sprintf(__('Earning %s', 'cpm-dongtrader'), $display_text),
        'dong_discounts'    => sprintf(__('Discount %s', 'cpm-dongtrader'), $display_text),
        //dong_affiliates
        'dong_affid'        => sprintf(__('Refferal Id :', 'cpm-dongtrader'))

    );
    if ($type) {
        unset($fields['dong_profitamt']);
        unset($fields['dong_earnings']);
        unset($fields['dong_affid']);
    } else {
        unset($fields['dong_reserve']);
        unset($fields['dong_earning_per']);
        unset($fields['dong_cost']);
    }

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
            $printables = $key != 'dong_affid' ? '<p>' . $value . ' $' . $meta_val . '</p>' : '<p>' . $value . ' ' . $meta_val . '</p>';
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
    $check = $gf_membership_checkbox == 'on' ? true : false;
    // $pm_fields      = apply_filters('membership_level_fields', array(), true);
    $member_level   = get_post_meta($proId, '_membership_product_level', true);
    // $order_fields   = apply_filters('membership_level_fields', array(), true);
    $pm_meta_vals   = get_pmpro_extrafields_meta($member_level);
    /*Rebate Calculation */
    $rebate_amount  = $pm_meta_vals['dong_reabate'] / 100 * $price;
    /*Process Amount */
    $process_amount = $pm_meta_vals['dong_processamt'] / 100 * $price;
    /**Sum of data recieved from pmpro membership levels */
    $constant_sum = $pm_meta_vals['dong_cost'] + $pm_meta_vals['dong_reserve'] + $pm_meta_vals['dong_earning_amt'];
    /*Profit after deduction from rebate and process */
    $remining_profit_amount = $price - $constant_sum;
    /*Total Profit that must be distributed to individual */
    $profit_amt_individual  = $remining_profit_amount * $pm_meta_vals['dong_profit_di'] / 100;
    /*Total Profit that must be distributed to group */
    $profit_amt_group       = $remining_profit_amount * $pm_meta_vals['dong_profit_dg'] / 100;
    /*commision amount from profit */
    $profit_commission_amt  = $remining_profit_amount * $pm_meta_vals['dong_profit_dca'] / 100;
    /*commision amount from individual */
    $commission_amt_to_individual = $profit_commission_amt * $pm_meta_vals['dong_comm_cdi'] / 100;
    /*commision amount from individual */
    $commission_amt_to_group      = $profit_commission_amt * $pm_meta_vals['dong_comm_cdg'] / 100;
    /*Treasury Amount Calculation */
    $treasury_amount = $check ? '0' : $remining_profit_amount;
    /*Discount Amount */
    $early_discount = $pm_meta_vals['dong_discounts'] / 100  * $remining_profit_amount;
    /**Earnings */
    $earnings = $check ? $pm_meta_vals['dong_earning_per'] / 100 * $pm_meta_vals['dong_earning_amt'] : '0';

    $aid = 1;//get_post_meta($oid, 'dong_affid', true);

    $order_items = [
        'dong_reabate'   => $rebate_amount,
        'dong_processamt' => $process_amount,
        'dong_profitamt' => $remining_profit_amount,
        'dong_profit_di' => $profit_amt_individual,
        'dong_profit_dg' => $profit_amt_group,
        'dong_profit_dca' => $profit_commission_amt,
        'dong_comm_cdi'  => $commission_amt_to_individual,
        'dong_comm_cdg'  => $commission_amt_to_group,
        'dong_treasury'  => $treasury_amount,
        'dong_earning_amt'  => $earnings,
        'dong_discounts' => $early_discount,
        'dong_reserve'   => $pm_meta_vals['dong_reserve'],
        'dong_cost'      => $pm_meta_vals['dong_cost'],
        'dong_affid'        => $aid

    ];

    //update all data to order meta
    foreach ($order_items as $k => $v) {
        update_post_meta($oid, $k, wc_clean($v));
    }
    //check if customer exists
    $customer = get_user_by('ID', $cid);
    //check if distribution is already done
    $check   =  get_post_meta($oid, 'distributn_succed', true);
    //get affiliate id


    if ($customer && $check != 'yes') :
        global $wpdb;
        //our custom table
        $table_name = $wpdb->prefix . 'manage_users_gf';
        //get circle name
        $circle_name  = $wpdb->get_row(
            "SELECT gf_circle_name  
            FROM $table_name 
            WHERE user_id= $cid 
            ORDER BY id 
            DESC LIMIT 1;"
        );

        $gf_circle_name = $circle_name->gf_circle_name;

        //Select All members for the circle name
        $members = $wpdb->get_results(
            "SELECT user_id
            FROM $table_name
            WHERE gf_circle_name = $gf_circle_name ",
            ARRAY_A
        );


        //Restucture members array received from database
        $mem_array = array_column($members, 'user_id');

        $aff_check = get_post_meta($oid, 'aff_distributn_succed', true);
        //push affiliate id members array
       if (!in_array($aid, $mem_array) && $aff_check != 'yes'){

         //check if data is stored previously on member meta
        $aff_user_trading_meta = get_user_meta($aid, '_user_trading_details', true);

         //if data is previously stored if not set empty array
        $aff_trading_details_user_meta = !empty($aff_user_trading_meta) ? $aff_user_trading_meta : [];
        $p_a_d_a = $profit_amt_individual;
        $c_a_d_a = $commission_amt_to_individual;
        $aff_trading_details_user_meta[] = [
            'order_id' => $oid,
            'rebate' => 0,
            'dong_profit_dg' => 0,
            'dong_profit_di' => $p_a_d_a,
            'dong_comm_dg' => 0,
            'dong_comm_cdi' => $c_a_d_a,
            'dong_total'  => $p_a_d_a + $c_a_d_a

        ];

        if(update_user_meta($aid, '_user_trading_details', $aff_trading_details_user_meta)){
            update_post_meta($oid, 'aff_distributn_succed', 'yes');
        }

       }
        //loop inside each members and update receiveables data
        foreach ($mem_array as $ma) {
            //check if data is stored previously on member meta
            $user_trading_meta = get_user_meta($ma, '_user_trading_details', true);

            //if data is previously stored if not set empty array
            $trading_details_user_meta = !empty($user_trading_meta) ? $user_trading_meta : [];

            //check if current customer is the member of the circle
            if ($ma != $cid) {
                //profit amount that must be distributed to circle
                $p_a_d_c = $profit_amt_group / 5;
                //commission amount that must be distributed to group
                $c_a_t_c = $commission_amt_to_group / 5;
                //receiveables details array
                $trading_details_user_meta[] = [
                    'order_id' => $oid,
                    'rebate' => 0,
                    'dong_profit_dg' => $p_a_d_c,
                    'dong_profit_di' => 0,
                    'dong_comm_dg' => $c_a_t_c,
                    'dong_comm_cdi' => 0,
                    'dong_total'  => 0 + $p_a_d_c + $c_a_t_c

                ];
            }
            //check if member is equal to customer then add rebate amount
            if ($ma == $cid) {
                //profit amount that must be distributed to circle
                $p_a_d_c = $profit_amt_group / 5;
                //commission amount that must be distributed to group
                $c_a_t_c = $commission_amt_to_group / 5;
                //receiveables details array
                $trading_details_user_meta[] = [
                    'order_id' => $oid,
                    'rebate' => $rebate_amount,
                    'dong_profit_dg' => $profit_amt_group / 5,
                    'dong_profit_di' => 0,
                    'dong_comm_dg' => $commission_amt_to_group / 5,
                    'dong_comm_cdi' => 0,
                    'dong_total'  => $rebate_amount + $p_a_d_c + $c_a_t_c

                ];
            }

            // if ($ma == $aid) {

            //     // Profit amount that must be distributed to affiliate
            //     $p_a_d_a = $profit_amt_individual;
            //     //commission amount that must be distributed to affiliate
            //     $c_a_d_a = $commission_amt_to_individual;

            //     $trading_details_user_meta[] = [
            //         'order_id' => $oid,
            //         'rebate' => 0,
            //         'dong_profit_dg' => 0,
            //         'dong_profit_di' => $p_a_d_a,
            //         'dong_comm_dg' => 0,
            //         'dong_comm_cdi' => $c_a_d_a,
            //         'dong_total'  => $p_a_d_a + $c_a_d_a

            //     ];
            // }

            // $aff_id = get_user_by('ID', $aid);
            // if($aff_id){
            //     $user_trading_meta = get_user_meta($aid, '_user_trading_details', true);
            //     $p_a_d_a = $profit_amt_individual;
            //     $c_a_d_a = $commission_amt_to_individual;
            //     $trading_details_user_meta[] = [
            //             'order_id' => $oid,
            //             'rebate' => 0,
            //             'dong_profit_dg' => 0,
            //             'dong_profit_di' => $p_a_d_a,
            //             'dong_comm_dg' => 0,
            //             'dong_comm_cdi' => $c_a_d_a,
            //             'dong_total'  => $p_a_d_a + $c_a_d_a
    
            //         ];
            // }
    
            // update array to members meta
            if (update_user_meta($ma, '_user_trading_details', $trading_details_user_meta)) {
                update_post_meta($oid, 'distributn_succed', 'yes');
            }
        }
    endif;
}

add_action('wp_head', function () {

    // function add_checkout_url_parameter( $url ) {
    //     $url = add_query_arg( 'my_param', '123', $url );
    //     return $url;
    // }
    // add_filter( 'woocommerce_get_checkout_url', 'add_checkout_url_parameter' );

    // // Retrieve the parameter value in the thank you page
    // function get_thankyou_page_parameter() {
    //     $my_param_value = isset( $_GET['my_param'] ) ? sanitize_text_field( $_GET['my_param'] ) : '';
    //     return $my_param_value;
    // }
    // add_action( 'woocommerce_thankyou', 'get_thankyou_page_parameter' );

});
/**
 * Save User data to glassfrog api from orderid
 *  
 */
add_action('woocommerce_thankyou', 'dongtrader_after_order_received_process', 10);

function dongtrader_after_order_received_process($order_id)
{
    $order = wc_get_order($order_id);
    $customer_id = $order->get_user_id();
    $items =  $order->get_items();
    $p_id = [];
    foreach ($items as $item) {
        $p_id[] = $item->get_product_id();
    }
    $gf_checkbox = get_post_meta($p_id[0], '_glassfrog_checkbox', true);
    // if($gf_checkbox == 'on'){
    dongtrader_user_registration_hook($customer_id);
    // }
    $current_pro = wc_get_product($p_id[0]);
    // if($current_pro->get_type())
    $current_price = $current_pro->get_price();

    dongtrader_product_price_distribution($current_price, $p_id[0], $order_id, $customer_id);
}


add_action('show_user_profile', 'custom_user_profile_fields');
add_action('edit_user_profile', 'custom_user_profile_fields');

//delete_user_meta(1,'_user_trading_details');
//_user_trading_details

function custom_user_profile_fields($user)
{
    $user_trading_metas = get_user_meta($user->ID, '_user_trading_details', true);
    if (empty($user_trading_metas)) return;
?>
    <hr />
    <h3><?php esc_html_e('Receiveable Amounts', 'cpm-dongtrader'); ?></h3>
    <p>
        <strong>
            <?php esc_html_e('All of the trading status are listed here', 'cpm-dongtrader'); ?>
        </strong>
    </p>
    <br class="clear" />
    <div id="member-history-orders" class="widgets-holder-wrap">
        <table class="wp-list-table widefat striped fixed trading-history" width="100%" cellpadding="0" cellspacing="0" border="0">
            <thead>
                <tr>
                    <th><?php esc_html_e('S.N.', 'cpm-dongtrader'); ?></th>
                    <th><?php esc_html_e('Order ID', 'cpm-dongtrader'); ?></th>
                    <th><?php esc_html_e('Created Date', 'cpm-dongtrader'); ?></th>
                    <th><?php esc_html_e('Rebate Receiveable', 'cpm-dongtrader'); ?></th>
                    <th><?php esc_html_e('Profit Receiveable', 'cpm-dongtrader'); ?></th>
                    <th><?php esc_html_e('Individual Profit Receiveable', 'cpm-dongtrader'); ?></th>
                    <th><?php esc_html_e('Commission Receiveable', 'cpm-dongtrader'); ?></th>
                    <th><?php esc_html_e('Individual Commission Receiveable', 'cpm-dongtrader'); ?></th>
                    <th><?php esc_html_e('Total Receiveable Amount', 'cpm-dongtrader'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $rebate_arr         = array_column($user_trading_metas, 'rebate');
                $dong_profit_dg_arr = array_column($user_trading_metas, 'dong_profit_dg');
                $dong_profit_di_arr = array_column($user_trading_metas, 'dong_profit_di');
                $dong_comm_dg_arr   = array_column($user_trading_metas, 'dong_comm_dg');
                $dong_comm_cdi_arr  = array_column($user_trading_metas, 'dong_comm_cdi');
                $dong_total_arr     = array_column($user_trading_metas, 'dong_total');



                $i = 1;
                foreach ($user_trading_metas as $utm) :
                    $order = new WC_Order($utm['order_id']);
                    $order_date = $order->order_date;
                ?>
                    <tr>
                        <td>
                            <?php echo $i; ?>
                        </td>
                        <td>
                            <?php echo $utm['order_id'] ?>
                        </td>
                        <td>
                            <?php echo $order_date; ?>
                        </td>
                        <td>
                            <?php echo '$' . $utm['rebate'] ?>
                        </td>
                        <!-- Profit -->
                        <td>
                            <?php echo '$' . $utm['dong_profit_dg'] ?>
                        </td>

                        <td>
                            <?php echo '$' . $utm['dong_comm_dg'] ?>
                        </td>
                        <!-- Commission -->
                        <td>
                            <?php echo '$' . $utm['dong_profit_di']; ?>
                        </td>
                        <td>
                            <?php echo '$' . $utm['dong_comm_cdi']; ?>
                        </td>
                        <td>
                            <?php echo '$' . $utm['dong_total'] ?>
                        </td>
                    </tr>

                <?php $i++;
                endforeach; ?>
                <tr rowspan="2" style="border: 1px solid blue;">
                    <td colspan="3">Totals</td>
                    <td><?php echo '$' . array_sum($rebate_arr); ?></td>
                    <td><?php echo '$' . array_sum($dong_profit_dg_arr); ?></td>
                    <td><?php echo '$' . array_sum($dong_comm_dg_arr); ?></td>
                    <td><?php echo '$' . array_sum($dong_profit_di_arr); ?></td>
                    <td><?php echo '$' . array_sum($dong_comm_cdi_arr); ?></td>
                    <td><?php echo '$' . array_sum($dong_total_arr); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
<?php
}


/* uploader order csv  */
function dongtraders_csv_order_importer()
{
    $msg = '';
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
            // var_dump('file-open');
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
            foreach ($get_orders as $get_order) {
                # code...
                /* $order_csv_id = $get_order['csv_id']; */
                $order_date = $get_order['order_date'];
                $billing_first_name = $get_order['billing_first_name'];
                $billing_last_name = $get_order['billing_last_name'];
                $billing_full_name = $billing_first_name . ' ' . $billing_last_name;
                $billing_phone = $get_order['billing_phone'];
                $billing_address_1 = $get_order['billing_address_1'];
                $billing_postcode = $get_order['billing_postcode'];
                $billing_city = $get_order['billing_city'];
                $billing_state = $get_order['billing_state'];
                $billing_country = $get_order['billing_country'];
                $product_id = $get_order['product_id'];
                $variation_id = $get_order['variation_id'];

                $paid_date = $get_order['paid_date'];
                $status = $get_order['status'];
                $order_total = $get_order['order_total'];
                $order_currency = $get_order['order_currency'];
                $payment_method = $get_order['payment_method'];
                $shipping_method = $get_order['shipping_method'];
                $customer_email = $get_order['customer_email'];
                $affiliate_user_id = $get_order['affilate_user_id'];
                $time = new DateTime($order_date);
                $date = $time->format("F d, Y");
                $time = $time->format('h:i A');
                $csv_paid_date = new DateTime($paid_date);
                $final_paid_date = $csv_paid_date->format("d-m-Y h:i:s");

                //echo $final_paid_date . '<br>';

                $post_title = 'Order &ndash; ' . $date . '@ ' . $time . '';
                if ($variation_id == '0') {

                    $product_name = get_the_title($product_id);
                    $product_id_csv =  $product_id;
                } else {
                    $product_name = get_the_title($variation_id);
                    $product_id_csv = $variation_id;
                }

                /* echo $customer_email . '<br>'; */
                if (!email_exists($customer_email)) {
                    $random_password = wp_generate_password();


                    $user_id = wc_create_new_customer($customer_email, $billing_first_name . rand(10, 100), $random_password);
                    /* echo $user_id . '-userid';
                    $chceck_data = dongtrader_user_registration_hook($user_id);
                    var_dump($chceck_data); */

                    dongtrader_user_registration_hook($user_id);
                    $order_id = wp_insert_post(array(
                        'post_type' => 'shop_order',
                        'post_title' => $post_title,
                        'post_status'    => $status,
                        'meta_input' => array(
                            /* '_csv_order_id' => $order_csv_id, */
                            '_billing_email' => $customer_email,
                            '_billing_first_name' => $billing_first_name,
                            '_billing_last_name' => $billing_last_name,
                            '_billing_address_1' => $billing_address_1,
                            '_billing_city' => $billing_city,
                            '_shipping_first_name' => $billing_first_name,
                            '_shipping_last_name' => $billing_last_name,
                            '_shipping_address_1' => $billing_address_1,
                            '_shipping_city' => $billing_city,
                            '_billing_postcode' => $billing_postcode,
                            '_payment_method' => $payment_method,
                            '_billing_state' => $billing_state,
                            '_billing_country' => $billing_country,
                            '_shipping_state' => $billing_state,
                            '_shipping_country' => $billing_country,
                            '_order_currency' => $order_currency,
                            '_order_total' => $order_total,
                            '_billing_phone' => $billing_phone,
                            '_paid_date' => $final_paid_date,
                            'dong_affid' => $affiliate_user_id
                        ),
                    ));

                    /* distrubution function  */
                    $price = $order_total;
                    $proId = $product_id_csv;
                    $oid = $order_id;
                    $cid = $user_id;
                    if (!empty($order_id)) {
                        $msg = '<div class="success-box">Order Data Imported Sucessfully</div>';
                    } else {
                        $msg = '<div class="error-box">Order Data could not Imported ! Please Try again</div>';
                    }
                 

                    dongtrader_product_price_distribution($price, $proId, $oid, $cid);
                    // create an order item first
                    $order_item_id = wc_add_order_item(
                        $order_id,
                        array(
                            'order_item_name' => $product_name, // may differ from the product name
                            'order_item_type' => 'line_item', // product
                        )
                    );
                    if ($order_item_id) {
                        // provide its meta information
                        wc_add_order_item_meta($order_item_id, '_qty', 1, true); // quantity

                        wc_add_order_item_meta($order_item_id, '_product_id', $product_id, true); // ID of the product
                        wc_add_order_item_meta($order_item_id, '_variation_id', $variation_id, true); // ID of the product
                        // you can also add "_variation_id" meta
                        wc_add_order_item_meta($order_item_id, '_line_subtotal', $order_total, true); // price per item
                        wc_add_order_item_meta($order_item_id, '_line_total', $order_total, true); // total price
                    }

                    $email = $customer_email;
                    $address = array(
                        'first_name' => $billing_first_name,
                        'last_name'  => $billing_last_name,
                        'email'      => $email,
                        'phone'      => $billing_phone,
                        'address_1'  => $billing_address_1,
                        'city'       => $billing_city,
                        'state'      => $billing_state,
                        'postcode'   => $billing_postcode,
                        'country'    => $billing_country,
                        //'affiliate_user' => $affiliate_user
                    );

                    update_post_meta($order_id, '_customer_user', $user_id);


                    update_user_meta($user_id, "billing_first_name", $address['first_name']);
                    update_user_meta($user_id, "billing_last_name", $address['last_name']);
                    update_user_meta($user_id, "billing_email", $address['email']);
                    update_user_meta($user_id, "billing_address_1", $address['address_1']);
                    update_user_meta($user_id, "billing_city", $address['city']);
                    update_user_meta($user_id, "billing_postcode", $address['postcode']);
                    update_user_meta($user_id, "billing_country", $address['country']);
                    update_user_meta($user_id, "billing_state", $address['state']);
                    update_user_meta($user_id, "billing_phone", $address['phone']);
                    update_user_meta($user_id, "shipping_first_name", $address['first_name']);
                    update_user_meta($user_id, "shipping_last_name", $address['last_name']);
                    update_user_meta($user_id, "shipping_address_1", $address['address_1']);
                    update_user_meta($user_id, "shipping_city", $address['city']);
                    update_user_meta($user_id, "shipping_postcode", $address['postcode']);
                    update_user_meta($user_id, "shipping_country", $address['country']);
                    update_user_meta($user_id, "shipping_state", $address['state']);
                    //update_user_meta($user_id, "dong_affid", $address['affiliate_user']);
                }
            }
            echo $msg;
        }
    }
}



add_action('wp_footer', function () {

    global $wpdb;
    $table_name = $wpdb->prefix . 'manage_users_gf';

    $result  = $wpdb->get_row("SELECT gf_circle_name  FROM $table_name ORDER BY id DESC LIMIT 1;");

    $last_circle_name = isset($result) ? $result->gf_circle_name : '1';
    $circle_count     = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE gf_circle_name = %s", $last_circle_name));
    $new_circle_name  = $circle_count < 5 ? $last_circle_name : $last_circle_name + 1;

    // $circle_name  = $wpdb->get_row
    // ( 
    //     "SELECT gf_circle_name  
    //     FROM $table_name 
    //     WHERE user_id= 74 
    //     ORDER BY id 
    //     DESC LIMIT 1;" 
    // );

    // $gf_circle_name = $circle_name->gf_circle_name;

    // var_dump($gf_circle_name);
});
