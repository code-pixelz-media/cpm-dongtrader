<?php

// /**
//  * tested with WooCommerce version 3.6.5
//  */

// // ------------------
// // STEP 1. Add new endpoint to use on the My Account page
// // IMPORTANT*: After uploading Permalinks needs to be rebuilt in order to avoid 404 error on the newly created endpoint

// function althemist_add_premium_support_endpoint()
// {
//     add_rewrite_endpoint('premium-support', EP_ROOT | EP_PAGES);
// }

// add_action('init', 'althemist_add_premium_support_endpoint');


// // ------------------
// // 2. Add new query var

// function althemist_premium_support_query_vars($vars)
// {
//     $vars[] = 'premium-support';
//     return $vars;
// }

// add_filter('query_vars', 'althemist_premium_support_query_vars', 0);


// // ------------------
// // 3. Insert the new endpoint into the My Account menu

// function althemist_add_premium_support_link_my_account($items)
// {
//     $items['premium-support'] = 'Premium Support';
//     return $items;
// }

// add_filter('woocommerce_account_menu_items', 'althemist_add_premium_support_link_my_account');


// // ------------------
// // 4. Add content to the new endpoint

// function althemist_premium_support_content()
// {
//     echo '<h3 style="text-align: center;">Premium Althemist Themes Support</h3>';
//     echo 'Your Althemist Themes Support Account Dashboard is the place to add your purchase codes, manage favorites and subscriptions to support topics and check all your support related topics and replies.';
//     echo ' your custom content goes here ';
// }

// add_action('woocommerce_account_premium-support_endpoint', 'althemist_premium_support_content');
