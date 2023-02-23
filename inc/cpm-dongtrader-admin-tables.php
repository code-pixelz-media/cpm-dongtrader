<?php

/**
 * Constructor will create the menu item
 * 
 */
global $wpdb;

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

    if (isset($_REQUEST['s'])) {
        $s = sanitize_text_field($_REQUEST['s']);
        //echo $s;
    } else {
        $s = false;
    }


    if (isset($_REQUEST['filter'])) {
        $get_filter = sanitize_text_field($_REQUEST['filter']);

        if ($get_filter == "all") {

            $start = '';
            $enddate = date("Y-m-d");
            $all_selected = "selected";
            $date_selected = "";

            /*  echo "filter all"; */
        } elseif ($get_filter == "within-a-date-range") {

            $start = sanitize_text_field($_REQUEST['start-month']);
            $enddate = sanitize_text_field($_REQUEST['end-month']);
            $date_selected = "selected";
            $all_selected = "";
            /* echo "filter with date"; */
        }
    } else {
        $start = '';
        $enddate = date("Y-m-d");
        $date_selected = "";
        $all_selected = "";
        /* echo "none of above"; */
    }

?>
    <form id="posts-filter" method="get" action="">

        <h1 class="wp-heading-inline">Dongtraders Product Orders Details</h1>

        <p class="search-box">
            <label class="hidden" for="post-search-input">Search Orders:</label>
            <input type="hidden" name="page" value="dongtraders-orders-list">
            <input id="post-search-input" type="text" value="<?php echo esc_attr(wp_unslash($s)); ?>" name="s">
            <input class="button" type="submit" value="Search Orders">
        </p>

        <div class="tablenav top">
            Show <select id="filter" name="filter">
                <option value="all" <?php echo $all_selected; ?>>All</option>
                <option value="within-a-date-range" <?php echo $date_selected; ?>>Within a Date Range</option>

            </select>


            <span id="from" style="display: none;">From</span>
            <input id="start-month" name="start-month" type="date" size="2" value="<?php echo $start; ?>" style="display: none;">
            <span id="to" style="display: none;">To</span>
            <input id="end-month" name="end-month" type="date" size="2" value="<?php echo $enddate; ?>" style="display: none;">
            <span id="filterby" style="display: none;">filter by </span>
            <input id="submit" class="button" type="submit" value="Filter">
            <br>
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
                    }
                }

                pmpro_ShowMonthOrYear();
            </script>

            <div class="tablenav-pages one-page">


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
                $post_per_page = 2;
                $paged = isset($_GET['dongtraders-orders-list']) ? abs((int) $_GET['dongtraders-orders-list']) : 1;
                $args = array(
                    'post_type'         => 'shop_order',
                    'posts_per_page'    => $post_per_page,
                    'post_status'    => 'wc-completed',
                    'paged' => $paged,

                    'meta_query' => array(
                        array(
                            'key' => '_paid_date',
                            'value' => array($start, $enddate),
                            'compare' => 'BETWEEN',
                            'type' => 'DATE'
                        ),
                        array(
                            // 'key'     => 'city',
                            'value'   => $s,
                            'compare' => 'LIKE',
                        ),

                    )
                );

                //$dong_orders = get_posts($args);
                $dong_orders = new WP_Query($args);

                /*  var_dump($dong_orders);
                die; */
                $price_symbol = get_woocommerce_currency_symbol();
                /* foreach ($dong_orders as $dong_order) { */
                while ($dong_orders->have_posts()) : $dong_orders->the_post();
                    $order_id = get_the_ID();
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
/*
        'dong_reabate'   => $rebate_amount,  
        'dong_processamt'=> $process_amount,
        'dong_profitamt' => $remining_profit_amount,
        'dong_profit_di' => $profit_amt_individual,
        'dong_profit_dg' => $profit_amt_group,
        'dong_profit_dca'=> $profit_commission_amt,
        'dong_comm_cdi'  => $commission_amt_to_individual,
        'dong_comm_cdg'  => $commission_amt_to_group,
        'dong_treasury'  => $treasury_amount,
        'dong_earnings'  => $earnings,
        'dong_discounts' => $early_discount,
 */
                    $rebate         = $order->get_meta('dong_reabate') ? $order->get_meta('dong_reabate') : 0;
                    $dong_processamt = $order->get_meta('dong_processamt') ? $order->get_meta('dong_processamt') : 0;
                    $dong_profitamt = $order->get_meta('dong_profitamt') ? $order->get_meta('dong_profitamt') : 0;
                    $dong_profit_indivudual = $order->get_meta('dong_profit_di') ? $order->get_meta('dong_profit_di') : 0;
                    $profit_amt_group = $order->get_meta('dong_profit_dg') ? $order->get_meta('dong_profit_dg') : 0;
                    //$30 ko 
                    $order_reserve_amt = $order->get_meta('dong_reserve') ? $order->get_meta('dong_reserve') : 0;
                    $order_earnings_amt = $order->get_meta('dong_earnings') ? $order->get_meta('dong_earnings') : 0;
                    $order_cost_amt = $order->get_meta('dong_cost') ? $order->get_meta('dong_cost') : 0; 
                    
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
                        <td class="column-reserve" data-colname="Reserve">' . $price_symbol . $order_reserve_amt . ' </td>
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
                //}
                endwhile;
                /* wp_reset_query(); */

                $total_order = $dong_orders->found_posts;
                if (empty($total_order)) {
                    echo '<tr><td colspan="100%">' . esc_html__('No Dongtraders Order found.', 'pmpro-affiliates') . '</td></tr>';
                }

                $totalPage         = ceil($total_order / $post_per_page);

                if ($totalPage > 1) {
                    $dongtraders_order_pagination     =  '<div class="dong-pagination"><span id="table-paging" >Page ' . $paged  . ' of ' . $totalPage . '</span>' . paginate_links(array(
                        'base' => add_query_arg('dongtraders-orders-list', '%#%'),
                        'format' => '',
                        'type' => 'plain',
                        'prev_text' => __('<'),
                        'next_text' => __('>'),
                        'total' => $totalPage,
                        'current' => $paged
                    )) . '</div>';
                }

                wp_reset_postdata();

                ?>

            </tbody>
        </table>

    </form>
<?php
    if (!empty($dongtraders_order_pagination)) {
        echo $dongtraders_order_pagination;
    }
}
