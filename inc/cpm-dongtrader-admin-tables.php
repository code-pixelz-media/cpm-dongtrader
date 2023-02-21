<?php

/**
 * Constructor will create the menu item
 */

function dongtraders_order_listing_menu()
{
    add_submenu_page(
        'woocommerce',
        __('Dongtraders Orders List', 'cpm-dongtrader'),
        __('Dongtraders Orders List', 'cpm-dongtrader'),
        'manage_woocommerce',
        'dongtraders-orders-list',
        'dongtraders_list_order_meta_table'
    );
}
add_action('admin_menu',  'dongtraders_order_listing_menu');


function dongtraders_list_order_meta_table()
{

?>



    <form id="posts-filter" method="get" action="">

        <h1 class="wp-heading-inline">Dongtraders Product Orders Details</h1>

        <p class="search-box">
            <label class="hidden" for="post-search-input">Search Orders:</label>
            <input type="hidden" name="page" value="dongtraders-orders-list">
            <input id="post-search-input" type="text" value="" name="s">
            <input class="button" type="submit" value="Search Orders">
        </p>

        <div class="tablenav top">
            Show <select id="filter" name="filter">
                <option value="all" selected="selected">All</option>
                <option value="within-a-date-range">Within a Date Range</option>
                <option value="predefined-date-range">Predefined Date Range</option>
                <option value="within-a-level">Within a Level</option>
                <option value="with-discount-code">With a Discount Code</option>
                <option value="within-a-status">Within a Status</option>
                <option value="only-paid">Only Paid Orders</option>
                <option value="only-free">Only Free Orders</option>

            </select>

            <span id="from" style="display: none;">From</span>

            <select id="start-month" name="start-month" style="display: none;">
                <option value="1" selected="selected">January</option>
                <option value="2">February</option>
                <option value="3">March</option>
                <option value="4">April</option>
                <option value="5">May</option>
                <option value="6">June</option>
                <option value="7">July</option>
                <option value="8">August</option>
                <option value="9">September</option>
                <option value="10">October</option>
                <option value="11">November</option>
                <option value="12">December</option>
            </select>

            <input id="start-day" name="start-day" type="text" size="2" value="1" style="display: none;">
            <input id="start-year" name="start-year" type="text" size="4" value="2023" style="display: none;">


            <span id="to" style="display: none;">To</span>

            <select id="end-month" name="end-month" style="display: none;">
                <option value="1">January</option>
                <option value="2" selected="selected">February</option>
                <option value="3">March</option>
                <option value="4">April</option>
                <option value="5">May</option>
                <option value="6">June</option>
                <option value="7">July</option>
                <option value="8">August</option>
                <option value="9">September</option>
                <option value="10">October</option>
                <option value="11">November</option>
                <option value="12">December</option>
            </select>


            <input id="end-day" name="end-day" type="text" size="2" value="16" style="display: none;">
            <input id="end-year" name="end-year" type="text" size="4" value="2023" style="display: none;">

            <span id="filterby" style="display: none;">filter by </span>

            <select id="predefined-date" name="predefined-date" style="display: none;">

                <option value="This Month" selected="selected">This Month</option>
                <option value="Last Month">Last Month</option>
                <option value="This Year">This Year</option>
                <option value="Last Year">Last Year</option>

            </select>

            <select id="l" name="l" style="display: none;">
                <option value="1">Dong Traders 1 - Consumer</option>
                <option value="2">Dong Traders 1 - Patron</option>
                <option value="3">Dong Traders 2 - Buyer</option>
                <option value="4">Dong Traders 2 - Influencer</option>
                <option value="5">Dong Traders 3 - Buyer</option>
                <option value="6">Dong Traders 3 - Influencer</option>
                <option value="7">Dong Traders 4 - Buyer</option>
                <option value="8">Dong Traders 4 - Influencer</option>
                <option value="9">Dong Traders 5 - Buyer</option>
                <option value="10">Dong Traders 5 - Influencer</option>
                <option value="11">Annual Group Hug - Attendee - 2024</option>

            </select>


            <select id="status" name="status" style="display: none;">
                <option value="" selected="selected"></option>
                <option value="cancelled">cancelled</option>
                <option value="error">error</option>
                <option value="pending">pending</option>
                <option value="refunded">refunded</option>
                <option value="review">review</option>
                <option value="success">success</option>
                <option value="token">token</option>
            </select>

            <input id="submit" class="button" type="submit" value="Filter">

            <script>
                //update month/year when period dropdown is changed
                jQuery(document).ready(function() {
                    jQuery('#filter').change(function() {
                        pmpro_ShowMonthOrYear();
                    });
                });

                function pmpro_ShowMonthOrYear() {
                    var filter = jQuery('#filter').val();
                    if (filter == 'all') {
                        jQuery('#start-month').hide();
                        jQuery('#start-day').hide();
                        jQuery('#start-year').hide();
                        jQuery('#end-month').hide();
                        jQuery('#end-day').hide();
                        jQuery('#end-year').hide();
                        jQuery('#predefined-date').hide();
                        jQuery('#status').hide();
                        jQuery('#l').hide();
                        jQuery('#discount-code').hide();
                        jQuery('#from').hide();
                        jQuery('#to').hide();
                        jQuery('#submit').show();
                        jQuery('#filterby').hide();
                    } else if (filter == 'within-a-date-range') {
                        jQuery('#start-month').show();
                        jQuery('#start-day').show();
                        jQuery('#start-year').show();
                        jQuery('#end-month').show();
                        jQuery('#end-day').show();
                        jQuery('#end-year').show();
                        jQuery('#predefined-date').hide();
                        jQuery('#status').hide();
                        jQuery('#l').hide();
                        jQuery('#discount-code').hide();
                        jQuery('#submit').show();
                        jQuery('#from').show();
                        jQuery('#to').show();
                        jQuery('#filterby').hide();
                    } else if (filter == 'predefined-date-range') {
                        jQuery('#start-month').hide();
                        jQuery('#start-day').hide();
                        jQuery('#start-year').hide();
                        jQuery('#end-month').hide();
                        jQuery('#end-day').hide();
                        jQuery('#end-year').hide();
                        jQuery('#predefined-date').show();
                        jQuery('#status').hide();
                        jQuery('#l').hide();
                        jQuery('#discount-code').hide();
                        jQuery('#submit').show();
                        jQuery('#from').hide();
                        jQuery('#to').hide();
                        jQuery('#filterby').show();
                    } else if (filter == 'within-a-level') {
                        jQuery('#start-month').hide();
                        jQuery('#start-day').hide();
                        jQuery('#start-year').hide();
                        jQuery('#end-month').hide();
                        jQuery('#end-day').hide();
                        jQuery('#end-year').hide();
                        jQuery('#predefined-date').hide();
                        jQuery('#status').hide();
                        jQuery('#l').show();
                        jQuery('#discount-code').hide();
                        jQuery('#submit').show();
                        jQuery('#from').hide();
                        jQuery('#to').hide();
                        jQuery('#filterby').show();
                    } else if (filter == 'with-discount-code') {
                        jQuery('#start-month').hide();
                        jQuery('#start-day').hide();
                        jQuery('#start-year').hide();
                        jQuery('#end-month').hide();
                        jQuery('#end-day').hide();
                        jQuery('#end-year').hide();
                        jQuery('#predefined-date').hide();
                        jQuery('#status').hide();
                        jQuery('#l').hide();
                        jQuery('#discount-code').show();
                        jQuery('#submit').show();
                        jQuery('#from').hide();
                        jQuery('#to').hide();
                        jQuery('#filterby').show();
                    } else if (filter == 'within-a-status') {
                        jQuery('#start-month').hide();
                        jQuery('#start-day').hide();
                        jQuery('#start-year').hide();
                        jQuery('#end-month').hide();
                        jQuery('#end-day').hide();
                        jQuery('#end-year').hide();
                        jQuery('#predefined-date').hide();
                        jQuery('#status').show();
                        jQuery('#l').hide();
                        jQuery('#discount-code').hide();
                        jQuery('#submit').show();
                        jQuery('#from').hide();
                        jQuery('#to').hide();
                        jQuery('#filterby').show();
                    } else if (filter == 'only-paid' || filter == 'only-free') {
                        jQuery('#start-month').hide();
                        jQuery('#start-day').hide();
                        jQuery('#start-year').hide();
                        jQuery('#end-month').hide();
                        jQuery('#end-day').hide();
                        jQuery('#end-year').hide();
                        jQuery('#predefined-date').hide();
                        jQuery('#status').hide();
                        jQuery('#l').hide();
                        jQuery('#discount-code').hide();
                        jQuery('#submit').show();
                        jQuery('#from').hide();
                        jQuery('#to').hide();
                        jQuery('#filterby').hide();
                    }
                }

                pmpro_ShowMonthOrYear();
            </script>

            <div class="tablenav-pages one-page">
                <span class="displaying-num">15 orders found.</span>
            </div>
            <br class="clear">
        </div> <!-- end tablenav -->

        <table class="wp-list-table widefat striped">
            <thead>
                <tr class="thead">
                    <th class="column-order-id">Order ID</th>
                    <th class="column-contact-details">Contact Details</th>
                    <th class="column-order-date">Order Date</th>
                    <th class="column-rebate">Rebate</th>
                    <th class="column-process">Process</th>
                    <th class="column-profit">Profit</th>
                    <th class="column-cost">Cost</th>
                    <!-- $30 product column -->
                    <th class="column-reserve">Reserve</th>
                    <th class="column-dcost">Cost</th>
                    <th class="column-dprofit">Profit</th>
                    <th class="column-dgroup">Group</th>
                    <th class="column-dndividual">Individual</th>
                    <th class="column-dcommission">Commission</th>
                    <th class="column-dearning">Earning</th>
                    <th class="column-degroup">Group</th>
                    <th class="column-dendividual">Individual</th>
                    <th class="column-decommission">Commission</th>

                </tr>
            </thead>
            <tbody id="orders" class="list:order orders-list">
                <?php
                $args = array(
                    'post_type'         => 'shop_order',
                    'posts_per_page'    => -1,
                    'post_status'    => 'wc-completed'
                );

                $dong_orders = get_posts($args);
                $price_symbol = get_woocommerce_currency_symbol();
                foreach ($dong_orders as $dong_order) {
                    $order_id = $dong_order->ID;
                    $order = new WC_Order($order_id);
                    //var_dump($order);
                    $order_first_name = $order->get_billing_first_name() ?  $order->get_billing_first_name() : null;
                    $order_second_name = $order->get_billing_first_name() ? $order->get_billing_last_name() : null;
                    $order_full_name = $order_first_name . ' ' . $order_second_name;
                    $order_email = $order->get_billing_email() ? $order->get_billing_email() : null;
                    $order_date = $order->get_date_created();
                    $order_total = $order->get_formatted_order_total();
                    $createDate = new DateTime($order_date);
                    $o_date = $createDate->format('Y-m-d');
                    $rebate = $order->get_meta('dong_reabate') ? $order->get_meta('dong_reabate') : 0;
                    $dong_processamt = $order->get_meta('dong_processamt') ? $order->get_meta('dong_processamt') : 0;
                    $dong_profitamt = $order->get_meta('dong_profitamt') ? $order->get_meta('dong_profitamt') : 0;
                    $dong_profit_indivudual = $order->get_meta('dong_profit_di') ? $order->get_meta('dong_profit_di') : 0;
                    $profit_amt_group = $order->get_meta('dong_profit_dg') ? $order->get_meta('dong_profit_dg') : 0;
                    $profit_commission_amt = $order->get_meta('dong_profit_dca') ? $order->get_meta('dong_profit_dca') : 0;
                    $commission_amt_to_individual = $order->get_meta('dong_comm_cdi') ? $order->get_meta('dong_comm_cdi') : 0;
                    $commission_amt_to_group = $order->get_meta('dong_comm_cdg') ? $order->get_meta('dong_comm_cdg') : 0;
                    $treasury_amount = $order->get_meta('dong_treasury') ? $order->get_meta('dong_treasury') : 0;
                    $dong_earnings = $order->get_meta('dong_earnings') ? $order->get_meta('dong_earnings') : 0;
                    $dong_discounts = $order->get_meta('dong_discounts') ? $order->get_meta('dong_discounts') : 0;


                    echo '
                    <tr>
                        <td class="column-order-id has-row-actions" data-colname="Order ID">
                            <strong>' . $order_id . ' </strong>
                            <div class="row-actions">
                            </div>
                        </td>
                        <td class="column-contact-details" data-colname="Contact Details">
                            ' . $order_full_name . '
                            <br>
                            ' . $order_email . '
                        </td>
                        <td class="column-order-date" data-colname="Order Date">
                            ' . $o_date . ' </td>
                        <td class="column-rebate" data-colname="Rebate">' . $price_symbol . $rebate . '</td>
                        <td class="column-process" data-colname="Process">' . $price_symbol . $dong_processamt . '<br> </td>
                        <td class="column-profit" data-colname="Profit">' . $price_symbol . $dong_profitamt . ' </td>
                        <td class="column-cost" data-colname="Cost">
                            <span class="pmpro_order-status pmpro_order-status-success">
                                ' . $order_total . ' </span>
                        </td>
                        <!--   $30 td -->
                        <td class="column-reserve" data-colname="Reserve">' . $price_symbol . $rebate . ' </td>
                        <td class="column-dcost" data-colname="Cost">' . $price_symbol . $rebate . ' </td>
                        <!--  Profit -->
                        <td class="column-dprofit" data-colname="Profit">' . $price_symbol . $rebate . ' </td>
                        <td class="column-dgroup" data-colname="Group">' . $price_symbol . $profit_amt_group . ' </td>
                        <td class="column-dndividual" data-colname="Individual">' . $price_symbol . $dong_profit_indivudual . ' </td>
                        <td class="column-dcommission" data-colname="Commission">' . $price_symbol . $profit_commission_amt . ' </td>
                        <!--   earning -->
                        <td class="column-dearning" data-colname="Earning">' . $price_symbol . $dong_earnings . ' </td>
                        <td class="column-degroup" data-colname="Group">' . $price_symbol . $rebate . ' </td>
                        <td class="column-dendividual" data-colname="Individual">' . $price_symbol . $rebate . ' </td>
                        <td class="column-decommission" data-colname="Commission">' . $price_symbol . $rebate . ' </td>
                    </tr>';
                } ?>
            </tbody>
        </table>
    </form>
<?php

}
