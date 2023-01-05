<?php

// add menu link
add_filter('woocommerce_account_menu_items', 'dongtrater_qr_generate', 40);
function dongtrater_qr_generate($menu_links)
{

    $menu_links = array_slice($menu_links, 0, 5, true)
        + array('generate-qr' => 'Generate Qr Code')
        + array_slice($menu_links, 5, NULL, true);

    return $menu_links;
}
// register permalink endpoint
add_action('init', 'dongtrader_qr_endpoint');
function dongtrader_qr_endpoint()
{

    add_rewrite_endpoint('generate-qr', EP_PAGES);
}
// content for the new page in My Account, woocommerce_account_{ENDPOINT NAME}_endpoint
add_action('woocommerce_account_log-history_endpoint', 'misha_my_account_endpoint_content');
function misha_my_account_endpoint_content()
{

    // of course you can print dynamic content here, one of the most useful functions here is get_current_user_id()
    echo 'Generate Qr Code here';
}
