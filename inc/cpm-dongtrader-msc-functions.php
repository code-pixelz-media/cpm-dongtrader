<?php

/**
 * @author anil 
 * All about cron job which is used for price distribution
 */

/**
 * Interval Settings filters For the cron job
 *
 * @param [array] $schedules
 * @return array
 */
function dongtrader_one_minutes_interval($schedules)
{

    $schedules['1_minutes'] = array(
        'interval' => 3 * 60,
        'display'  => __('Every 3 minutes', 'cpm-dongtrader'),
    );

    return $schedules;
}

add_filter('cron_schedules', 'dongtrader_one_minutes_interval');

/**
 * If the cron job isn't scheduled, schedule it.
 */
add_action('wp', 'dongtrader_schedule_cron_job');
function dongtrader_schedule_cron_job()
{
    if (!wp_next_scheduled('dongtrader_cron_job_hook')) {

        wp_schedule_event(time(), '1_minutes', 'dongtrader_cron_job_hook');

    }
}

/**
 * Add a custom hook to the cron job, and then run a function when that hook is called.
 */
add_action('dongtrader_cron_job_hook', 'dongtrader_cron_job');
function dongtrader_cron_job()
{
    //price distributing function
    dongtrader_distribute_product_prices_to_circle_members();

}

/**
 * Cron job function to excute distribution process
 *
 * @return void
 */
function dongtrader_distribute_product_prices_to_circle_members(){

   //get all members from user id
   $members = glassfrog_api_get_persons_of_circles();

   //exit if there are no members
   if(!$members) return;

   global $wpdb;

   //Our Custom table name from the database.
   $table_name = $wpdb->prefix . 'manage_users_gf';

   //looping through all members
    foreach($members as $m) :

        //if m is null continue to next loop
        if(!isset($m)) continue;

        //query to get order id by user id
        $order_id =  $wpdb->get_var("SELECT order_id FROM $table_name WHERE user_id= $m[user_id] ");

        //parent userid
        $parent_user_id = intval($m['user_id']);

        //get affiliate id 
        $dong_affid = dongtrader_get_order_meta($order_id, 'dong_affid');

        //childrens user ids
        $children_users_id = ['related-'.$parent_user_id .'-'.$order_id => $m['related'] ];

        //save distributed amount to current customer
        dongtrader_split_price_parent($parent_user_id, $order_id);
        
        //save distributed amount to current customers group members
        dongtrader_split_price_childrens($children_users_id );

        //Saves distributed amount to affiliates
        dongtrader_split_price_affiliates($dong_affid , $order_id);

        //Saves process amount to site owner
        split_process_charges_to_siteowner(1,$order_id);

    //end looping for all members
    endforeach;


}

/**
 * Retrieves meta values from order . If meta is empty returns 0 otherwise returns value
 *
 * @param [string] $orderid id of the order
 * @param [string] $key order's meta key
 * @return [meta_value]
 */
function dongtrader_get_order_meta($orderid, $key)
{
    //get order objects
    $orderobj = new WC_Order($orderid);

    //get value of meta
    return !empty($orderobj->get_meta($key)) ? $orderobj->get_meta($key) : 0;

}

/**
 * Price distrubution to current or exact customer
 *
 * @param [string] $parent_user_id main user id whom has created the order
 * @param [String] $order_id The order id
 * @return void
 */
function dongtrader_split_price_parent($parent_user_id , $order_id){

    //get previous member data
    $customer_and_member_meta = get_user_meta($parent_user_id, '_user_trading_details', true);

    //if previous meta is empty assign an empty array
    $customer_and_member_metas = !empty($customer_and_member_meta) ? $customer_and_member_meta : [];

    //rebate amount from order meta distributeable to user whom has brought the product
    $rebate = dongtrader_get_order_meta($order_id, 'dong_reabate');

    //profit amount distributeable to group
    $dong_profit_dg = dongtrader_get_order_meta($order_id, 'dong_profit_dg');

    //commission amount distributeable to group
    $dong_comm_cdg = dongtrader_get_order_meta($order_id, 'dong_comm_cdg');

    //profit distributed to group
    $p_d_g = $dong_profit_dg / 5;

    //commision amount distributed to group
    $c_d_g = $dong_comm_cdg / 5;

    //append to previous array to update in user meta 
    $customer_and_member_metas[] = [
        'order_id'      => $order_id,
        'rebate'        => $rebate,
        'procees_amount'=> 0,
        'dong_profit_dg'=> $p_d_g,
        'dong_profit_di'=> 0,
        'dong_comm_dg'  => $c_d_g,
        'dong_comm_cdi' => 0,
        'dong_total'    => $rebate + $p_d_g + $c_d_g,
    ];

    //update array to user meta 
    update_user_meta($parent_user_id, '_user_trading_details', $customer_and_member_metas);

  
}
/**
 * Price Distribution to group members
 *
 * @param [array] $childrens Childrens are other members of the circle 
 * @return void
 */
function dongtrader_split_price_childrens($childrens ){
  
    //if children array is empty dont do anything
   if(empty($childrens)) return;
   
   //looping inside the childrens array
   foreach($childrens as $k=>$v){

        //explode key into array to get parent order id and parent user id
        $string_array = explode('-',$k);

        //parent order
        $parent_order = $string_array[2];

        //parent user id (might be required for later)
        $parent_user  = $string_array[1];

        //looping inside all childrens
        foreach($v as $s){

            //profit amount distributable to all members from 
            $dong_profit_dg = dongtrader_get_order_meta($parent_order, 'dong_profit_dg');

            //commission distributed to group
            $dong_comm_cdg = dongtrader_get_order_meta($parent_order, 'dong_comm_cdg');

            //get previous stored data
            $customer_not_mem_meta = get_user_meta($s, '_user_trading_details', true);

            //if previous meta is empty assign an empty array
            $customer_not_mem_metas = !empty($customer_not_mem_meta) ? $customer_not_mem_meta : [];
    
            //profit amount that must be distributed to circle
            $p_a_d_c = $dong_profit_dg / 5;
    
            //commission amount that must be distributed to group
            $c_a_t_c = $dong_comm_cdg / 5;
    
            //apend to previous array to update in  user meta
            $customer_not_mem_metas[] = [
                'order_id'      => $parent_order,
                'rebate'        => 0,
                'procees_amount'=> 0,
                'dong_profit_dg'=> $p_a_d_c,
                'dong_profit_di'=> 0,
                'dong_comm_dg'  => $c_a_t_c,
                'dong_comm_cdi' => 0,
                'dong_total'    => 0 + $p_a_d_c + $c_a_t_c,
            ];

            //update array to user meta
            update_user_meta($s, '_user_trading_details', $customer_not_mem_metas);
            
        }
   }

}
/**
 * This function distributes prices to the siteowner . 
 *
 * @param integer $siteownerid
 * @return void
 */
function split_process_charges_to_siteowner($siteownerid = 1 , $order_id){

    //get previous stored data
    $site_owner = get_user_meta($siteownerid, '_user_trading_details', true);

    //Process Amount Site owner
    $dong_process_amt = dongtrader_get_order_meta($siteownerid, 'dong_processamt');

    //if previous meta is empty assign an empty array
    $site_owners_metas = !empty($site_owner) ? $site_owner : [];

    //apend to previous array to update in  user meta
    $site_owners_metas[] = [
        'order_id'      => $order_id,
        'rebate'        => 0,
        'procees_amount'=> $dong_process_amt,
        'dong_profit_dg'=> 0,
        'dong_profit_di'=> 0,
        'dong_comm_dg'  => 0,
        'dong_comm_cdi' => 0,
        'dong_total'    => $dong_process_amt,
    ];

    //update array to user meta
    update_user_meta($siteownerid, '_user_trading_details', $site_owners_metas);
}

/**
 * Price distribution for affiliate users
 *
 * @param [ string ] $aid:Affilate id that was stored in order meta
 * @param [ string ] $oid:Order id
 * @return void
 */
function dongtrader_split_price_affiliates($aid , $oid){

    //get user object
    $user_check = get_user_by('id', $aid);

    //if user exists
    if ($user_check):

        //check if data is stored previously on member meta
        $aff_user_trading_meta = get_user_meta($aid, '_user_trading_details', true);

        //if data is previously stored if not set empty array
        $aff_trading_details_user_meta = !empty($aff_user_trading_meta) ? $aff_user_trading_meta : [];

        //commission distributed to individual
        $dong_comm_cdi  = dongtrader_get_order_meta($oid, 'dong_comm_cdi');

        //get profit distributed to individual
        $dong_profit_di = dongtrader_get_order_meta($oid, 'dong_profit_di');

        //meta update array
        $aff_trading_details_user_meta[] = [
            'order_id'      => $oid,
            'rebate'        => 0,
            'procees_amount'=> 0,
            'dong_profit_dg'=> 0,
            'dong_profit_di'=> $dong_profit_di,
            'dong_comm_dg'  => 0,
            'dong_comm_cdi' => $dong_comm_cdi,
            'dong_total'    => $dong_profit_di + $dong_comm_cdi,

        ];

        //update array to affliate user 
        update_user_meta($aid, '_user_trading_details', $aff_trading_details_user_meta);
    endif;
}

function glassfrog_api_get_persons_of_circles()
{
    global $wpdb;

    //Our Custom table name from the database.
    $table_name = $wpdb->prefix . 'manage_users_gf';

    //get glassfrog id and user id from custom table manage_users_gf
    $results = $wpdb->get_results("SELECT gf_person_id , user_id FROM $table_name WHERE in_circle = 0 LIMIT 5", ARRAY_A);

    //if not results exit
    if (!$results) return;

    //extract glassfrog id from the results in the above custom query
    $glassfrog_ids = wp_list_pluck($results, 'gf_person_id');

    //extract user id from the results in the above custom query
    $user_ids = wp_list_pluck($results, 'user_id');

    //combine whole array into one
    $all_users = array_combine($glassfrog_ids, $user_ids);

    //empty array to push all user id from the loop
    $members = [];

    //looping inside our all users
    foreach ($all_users as $gfid => $uid) {

        //call the glassfrog api
        $api_call = glassfrog_api_request('people/' . $gfid . '/roles', '', 'GET');

        //check if api call is all good
        if ($api_call):

            //get all people of the circle from api obj
            $all_people_in_circle = $api_call->linked->people;

            //exact circle name in the api from api obj
            $peoples_circle_name = $api_call->roles[0]->name;

            //check if five members rule is accomplished in the circle

            if (count($all_people_in_circle) == 5):

                //looping inisde the circle
                foreach ($all_people_in_circle as $ap):

                    //prepare to update to custom database
                    $update_query = $wpdb->prepare("UPDATE $table_name SET in_circle = %d , gf_role_assigned = %s WHERE user_id = %d", 1, $peoples_circle_name, $uid);

                    //update to custom database
                    if($ap->external_id == $uid) :

                        //updated
                        $wpdb->query($update_query);
                        
                        //get wp user id stored as external id from the api
                        $members[] = $ap->external_id;

                    endif;
  
                    //end foreach loop started for looping inside circle members
                endforeach;

                //five members rule check condition ends
            endif;
        else:
            //do nothing
        endif;

    }

    if(!empty($members)):

        //intilized empty array
        $new_array = array();

        //remove duplicates in an array
        $unique_members = array_unique($members);

        // Loop through each user_id
        foreach ($unique_members as $index => $user_id) {

            // Removing the current user from the array of users.
            $related = array_diff($unique_members, [$user_id]);

            // Creating an array of user_id and related users.
            $new_array[] = [
                'user_id' => $user_id,
                'related' => array_values($related)
            ];
        }

        return $new_array;
        
    else :

        return false;

    endif;

}

function dong_display_trading_details($user_id){

    $user_trading_metas = get_user_meta($user_id, '_user_trading_details', true);

    //reverse array
    // array_reverse($user_trading_metas);

    // determine number of items per page
    $items_per_page = 5;

    // determine current page number from query parameter
    $current_page = isset($_GET['listpaged']) ? intval($_GET['listpaged']) : 1;

    // calculate start and end indices for items on current page
    $start_index = ($current_page - 1) * $items_per_page;

    $end_index = $start_index + $items_per_page;

    // slice the array to get items for current page
    $items_for_current_page = array_slice($user_trading_metas, $start_index, $items_per_page);
    
    if(!empty($items_for_current_page)) :
    ?>
    <div class="user-trading-details">
        <strong><?php esc_html_e('Your Trading Details', 'cpm-dongtrader');?></strong>
        <div id="member-history-orders" class="widgets-holder-wrap cpm-table-wrap">
            <table class="affilate-data" width="100%" cellpadding="0" cellspacing="0" border="0">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Order ID', 'cpm-dongtrader');?><span class="sorting-indicator"></span></th>
                        <th><?php esc_html_e('Initiator', 'cpm-dongtrader');?><span class="sorting-indicator"></span></th>
                        <th><?php esc_html_e('Created Date', 'cpm-dongtrader');?></th>
                        <th><?php esc_html_e('Rebate', 'cpm-dongtrader');?></th>
                        <th><?php esc_html_e('Profit', 'cpm-dongtrader');?></th>
                        <th><?php esc_html_e('Individual Profit', 'cpm-dongtrader');?></th>
                        <th><?php esc_html_e('Commission', 'cpm-dongtrader');?></th>
                        <th><?php esc_html_e('Individual Commission', 'cpm-dongtrader');?></th>
                        <th><?php esc_html_e('Total Amount', 'cpm-dongtrader');?></th>
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
                    $price_symbol = get_woocommerce_currency_symbol();
                    foreach ($items_for_current_page as $utm):
                        $order                  = new WC_Order($utm['order_id']);
                        $formatted_order_date   = wc_format_datetime($order->get_date_created(), 'Y-m-d');
                        $order_backend_link     = admin_url('post.php?post=' . $utm['order_id'] . '&action=edit');
                        $user_id                = $order->get_customer_id();
                        $user_details           = get_userdata($user_id);
                        $user_display_name      = $user_details->data->display_name;
                        $user_backend_edit_url  = get_edit_user_link($user_id);
                ?>
                        <tr class="enable-sorting">
                            <td>
                                <a href="<?php echo $order_backend_link; ?>">
                                    <?php echo $utm['order_id'] ?>
                                </a>
                            </td>
                            <td>
                                <a href="">
                                    <?php echo $user_display_name; ?>
                                </a>
                            </td>
                            <td>
                                <?php echo $formatted_order_date; ?>
                            </td>
                            <td>
                                <?php echo $price_symbol . $utm['rebate'] ?>
                            </td>
                            <!-- Profit -->
                            <td>
                                <?php echo $price_symbol . $utm['dong_profit_dg'] ?>
                            </td>
                            <td>
                                <?php echo $price_symbol . $utm['dong_profit_di']; ?>
                            </td>
                            <!-- Commission -->
                            <td>
                                <?php echo $price_symbol . $utm['dong_comm_dg'] ?>
                            </td>
                            <td>
                                <?php echo $price_symbol . $utm['dong_comm_cdi']; ?>
                            </td>
                            <td>
                                <?php echo $price_symbol . $utm['dong_total'] ?>
                            </td>
                        </tr>
                    <?php $i++; endforeach;?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3">All Totals</td>
                        <td><?php echo $price_symbol . array_sum($rebate_arr); ?></td>
                        <td><?php echo $price_symbol . array_sum($dong_profit_dg_arr); ?></td>
                        <td><?php echo $price_symbol . array_sum($dong_profit_di_arr); ?></td>
                        <td><?php echo $price_symbol . array_sum($dong_comm_dg_arr); ?></td>
                        <td><?php echo $price_symbol . array_sum($dong_comm_cdi_arr); ?></td>
                        <td><?php echo $price_symbol . array_sum($dong_total_arr); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="dong-pagination user-trading-list-paginate" style="float:right">
            <?php
                $num_items = count($user_trading_metas);
                $num_pages = ceil($num_items / $items_per_page);
                echo paginate_links(array(
                    'base' => add_query_arg('listpaged', '%#%'),
                    'format' => 'list',
                    'prev_text' => __('&laquo; Previous', 'cpm-dongtrader'),
                    'next_text' => __('Next &raquo;', 'cpm-dongtrader'),
                    'total' => $num_pages,
                    'current' => $current_page,
                ));
            ?>
        </div>
        <?php
        else:
            echo '<strong>Trading Details Not Found</strong>';

        endif;
        ?>
    </div>
<?php
}
add_action('dong_display_distribution_table', 'dong_display_trading_details');
