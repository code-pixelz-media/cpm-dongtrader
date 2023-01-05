<?php

add_filter('woocommerce_account_menu_items', 'cpm_dong_generateqr', 40);
function cpm_dong_generateqr($menu_links)
{

    $menu_links = array_slice($menu_links, 0, 5, true)
        + array('generate-qrtiger' => 'Generate Qr Code')
        + array_slice($menu_links, 5, NULL, true);

    return $menu_links;
}
// register permalink endpoint
add_action('init', 'cpm_dong_add_endpoint');
function cpm_dong_add_endpoint()
{

    add_rewrite_endpoint('generate-qrtiger', EP_PAGES);
}
// content for the new page in My Account, woocommerce_account_{ENDPOINT NAME}_endpoint
add_action('woocommerce_account_generate-qrtiger_endpoint', 'cpm_dong_my_account_endpoint_content');
function cpm_dong_my_account_endpoint_content()
{

    // of course you can print dynamic content here, one of the most useful functions here is get_current_user_id()
?>
    <div class='qr-tiger-code-generator'>
        <p><?php _e('Generate Qr Code ', 'cpm-dongtrader') ?></p>
        <button class="btn btn-primary qrtiger-code-gen" type="submit"><?php _e('Generate', 'cpm-dongtrader') ?></button>
    </div>

<?php
}
