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
            $product_id = $product->get_id();
            if (!empty(($_GET['add']))) {
                $check_add_product = $_GET['add'];
                if ($check_add_product == '1') {
                    /*  check if user already bought product */
                    if (wc_customer_bought_product('', get_current_user_id(), $product->get_id())) {
                        // if (!empty(dongtraders_get_membership_link_product($product->get_id()))) {
                        $product_page = get_permalink($product->get_id());
                        wp_redirect($product_page);
                    } else {
                        WC()->cart->add_to_cart($product_id);
                        wp_redirect(home_url('/checkout/'));
                    }
                }
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
