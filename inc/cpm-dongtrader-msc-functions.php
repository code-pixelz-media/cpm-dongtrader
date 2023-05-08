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
        'display' => __('Every 3 minutes', 'cpm-dongtrader'),
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
function dongtrader_distribute_product_prices_to_circle_members()
{

    //get all members from user id
    $members = glassfrog_api_get_persons_of_circles();

    //exit if there are no members
    if (!$members) return;

    global $wpdb;

    //Our Custom table name from the database.
    $table_name = $wpdb->prefix . 'manage_glassfrogs_api';

    //looping through all members
    foreach ($members as $m):

        //if m is null continue to next loop
        if (!isset($m)) continue;
       
        //query to get order id by user id
        $order_id = intval($wpdb->get_var("SELECT order_id FROM $table_name WHERE user_id= $m[user_id] "));

        $order_obj = wc_get_order($order_id);

        //parent userid
        $parent_user_id = intval($m['user_id']);

        //get affiliate id
        $seller_id = dongtrader_get_order_meta($order_id, 'dong_affid');

        $check_buyer    = dongtrader_check_user($parent_user_id);

        //childrens user ids
        $children_users_id = ['related-' . $parent_user_id . '-' . $order_id => $m['related']];

        if($check_buyer) :

            dongtrader_save_all_orders_details($parent_user_id,$m['related']);

            dongtrader_save_all_treasury_details($parent_user_id,$m['related']);

            dongtrader_save_all_commission_details($parent_user_id, $m['related']);

            dongtrader_save_group_details($children_users_id);

        endif;
 
    endforeach;

}


function dongtrader_save_group_details($children_users_id){

  

}



function dongtrader_save_all_commission_details($b_id, $friends){

    global $wpdb;

    //var_dump($children_users_id);

    //Our Custom table name from the database.
    $table_name = $wpdb->prefix . 'manage_glassfrogs_api';

    $all_orders_ser = $wpdb->get_row($wpdb->prepare("SELECT all_orders FROM $table_name WHERE user_id = %d", $b_id),ARRAY_A);

    $all_orders     = unserialize($all_orders_ser['all_orders']); 

    if(empty($all_orders)) return;

    foreach($all_orders as $ao) :

            //get previous seller trading details saved in user meta
            $commission_meta  = get_user_meta($b_id, '_commission_details', true);

            //assign empty array if $commission_meta is empty 
            $commission_metas = !empty($commission_meta) ? $commission_meta : [];
        
            //get title of the product
            $product_name = dongtrader_get_product($ao,true);

            //get buyer or customer name
            $buyer_name   = dongtrader_check_user($b_id , false);

            //seller commission
            $seller_com = dongtrader_get_order_meta($ao,'dong_profit_di');

            $group_com  = dongtrader_get_order_meta($ao,'dong_profit_dg');

            $owner_com  = dongtrader_get_order_meta($ao,'dong_earning_amt');

            $c_d_g = $group_com / 5;

            $total = $seller_com + $c_d_g + $owner_com;

            $commission_metas[] = [
                'order_id'      => $ao,
                'name'          => $buyer_name,
                'product_title' => $product_name,
                'seller_com'    => $seller_com,
                'group_com'     => $c_d_g,
                'site_com'      => $owner_com,
                'total'         => $total
            ];
            

            update_user_meta($b_id,'_commission_details',$commission_metas);

            if(!empty($friends)) :

                foreach($friends as $f) :

                    update_user_meta($f,'_commission_details',$commission_metas);

                endforeach;

            endif;
          
            

    endforeach;



}

function dongtrader_save_all_treasury_details($b_id , $friends){

    global $wpdb;

    //Our Custom table name from the database.
    $table_name = $wpdb->prefix . 'manage_glassfrogs_api';

    $all_orders_ser = $wpdb->get_row($wpdb->prepare("SELECT all_orders FROM $table_name WHERE user_id = %d", $b_id),ARRAY_A);

    $all_orders     = unserialize($all_orders_ser['all_orders']); 

    if(empty($all_orders)) return;
    
    foreach($all_orders as $ao) :

        //get previous seller trading details saved in user meta
        $treasury_meta  = get_user_meta($b_id, '_treasury_details', true);

        //assign empty array if $treasury_meta is empty 
        $treasury_metas = !empty($treasury_meta) ? $treasury_meta : [];

        //get title of the product
        $product_name = dongtrader_get_product($ao,true);

        //product id
        $v_product_id = dongtrader_get_product($ao);

        //get buyer or customer name
        $buyer_name   = dongtrader_check_user($b_id , false);

        //get product price by id
        $product_obj = wc_get_product( $v_product_id );

        //variation's parent product id
        $p_p_id = $product_obj->get_parent_id();

        //get product price
        $product_price = intval($product_obj->get_price());

        //rebate amount from order meta distributeable to user whom has brought the product
        $rebate       = dongtrader_get_order_meta($ao, 'dong_reabate');

        //rebate amount from order meta distributeable to user whom has brought the product
        $process       = dongtrader_get_order_meta($ao, 'dong_processamt');

        //get membership level
        $member_level   = get_post_meta($p_p_id, '_membership_product_level', true);

        //paid membership pro extra fields
        $pm_meta_vals   = get_pmpro_extrafields_meta($member_level);

        //this value is not working
        $cost           = $pm_meta_vals['dong_cost'];

        $individual_profit = dongtrader_get_order_meta($ao, 'dong_profit_di'); 

        $distributed_total_amt = $rebate + $process + $individual_profit + $cost;

        $remaining_total_amt   = $product_price - $distributed_total_amt;

        $treasury_metas[] = [
            'order_id'      => $ao,
            'name'          => $buyer_name,
            'product_title' => $product_name,
            'total_amt'     => $product_price,
            'distrb_amt'    => $distributed_total_amt,
            'rem_amt'       => $remaining_total_amt,
        ];

        update_user_meta($b_id,'_treasury_details',$treasury_metas);
        
        if(!empty($friends)) :

            foreach($friends as $f) :

                update_user_meta($f,'_treasury_details',$treasury_metas);

            endforeach;

        endif;

    endforeach;

}


function dongtrader_save_all_orders_details($b_id, $friends){

    global $wpdb;

    //Our Custom table name from the database.
    $table_name = $wpdb->prefix . 'manage_glassfrogs_api';

    $all_orders_ser = $wpdb->get_row($wpdb->prepare("SELECT all_orders FROM $table_name WHERE user_id = %d", $b_id),ARRAY_A);

    $all_orders     = unserialize($all_orders_ser['all_orders']); 

    $check_buyer    = dongtrader_check_user($b_id);

    foreach($all_orders as $o) :

        //get affiliate id of the seller
        $seller_id    = dongtrader_get_order_meta($o, 'dong_affid');

        //get previous seller trading details saved in user meta
        $buyer_meta  = get_user_meta($b_id, '_buyer_details', true);

        //assign empty array if $buyer_meta is empty 
        $buyer_metas = !empty($buyer_meta) ? $buyer_meta : [];

        //get buyer or customer name
        $buyer_name   = dongtrader_check_user($b_id , false);
        
        //get seller or affiliate id we might not need this for instance
        //$seller_name  = dongtrader_check_user($seller_id , false);

        //rebate amount from order meta distributeable to user whom has brought the product
        $rebate       = dongtrader_get_order_meta($o, 'dong_reabate');
    
        //get title of the product
        $product_name = dongtrader_get_product($o,true);

        //get actual process amount from order meta
        $process_amt  = dongtrader_get_order_meta($o,'dong_processamt');

        //get seller or affiliate profit amount from order meta
        $seller_profit= dongtrader_get_order_meta($o,'dong_profit_di'); 

        //if seller is buyer himself compare ids and then set process amount 
        $process_amount  = $b_id == $seller_id ? $process_amt : 0;

        //new array for new order which we will append to 
        $buyer_metas[] = [
            'order_id'      => $o,
            'name'          => $buyer_name,
            'product_title' => $product_name,
            'rebate'        => $rebate,
            'process'       => $process_amount,
            'seller_profit' => $seller_profit,
            'total'         => $rebate + $process_amount + $seller_profit
        ];

        update_user_meta($b_id,'_buyer_details',$buyer_metas);

        // if(!empty($friends)) :

        //     foreach($friends as $f) :

        //         update_user_meta($f,'_treasury_details',$treasury_metas);

        //     endforeach;

        // endif;

    endforeach;
 
}

function dongtrader_get_product($order_id , $name=false){

    $order          = wc_get_order($order_id);

    $items          =  $order->get_items();

    foreach ($items as $item) {

        $product = $item->get_product();

        $product_id[] =  $product->get_id();

        if($name) $product_name[] = $product->get_name();

    }

    return $name ? $product_name[0] : $product_id[0];


}

function dongtrader_check_user($uid , $check_only = true ){

    global $wpdb;
   
    $table_name =  $wpdb->prefix .'users';

    $user_id_int = intval($uid);

    if($check_only ) :

        $check_user = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE ID = %d AND id !=0 ", $user_id_int));

        $val = intval($check_user) == 1 ? true : false;

    else :

        $user_name = $wpdb->get_var("SELECT display_name FROM $table_name WHERE id = $uid");
        
        $val = $user_name;

    endif;


    return $val;

}


/**
 * Saves trading details to seller
 *
 * @param [int] $b_id buyer user_id
 * @param [int] $o_id buyer's order_id
 * @return void
 */


function dongtrader_save_myorder_details($b_id,$o_id){

    //get affiliate id of the seller
    $seller_id    = dongtrader_get_order_meta($o_id, 'dong_affid');

    //check if buyer user_id exists
    $check_buyer  = dongtrader_check_user($b_id);

    //check if affiliate user_id exists
    $check_seller = dongtrader_check_user($seller_id);
   
    //if seller and buyer both user exists
    if($check_buyer ) :
        
        //get previous seller trading details saved in user meta
        $buyer_meta  = get_user_meta($b_id, '_buyer_details', true);

        //assign empty array if $buyer_meta is empty 
        $buyer_metas = !empty($buyer_meta) ? $buyer_meta : [];

        //get buyer or customer name
        $buyer_name   = dongtrader_check_user($b_id , false);
        
        //get seller or affiliate id we might not need this for instance
        //$seller_name  = dongtrader_check_user($seller_id , false);

        //rebate amount from order meta distributeable to user whom has brought the product
        $rebate       = dongtrader_get_order_meta($o_id, 'dong_reabate');
       
        //get title of the product
        $product_name = dongtrader_get_product($o_id,true);

        //get actual process amount from order meta
        $process_amt  = dongtrader_get_order_meta($o_id,'dong_processamt');

        //get seller or affiliate profit amount from order meta
        $seller_profit= dongtrader_get_order_meta($o_id,'dong_profit_di'); 

        //if seller is buyer himself compare ids and then set process amount 
        $process_amount  = $b_id == $seller_id ? $process_amt : 0;

        //new array for new order which we will append to 
        $buyer_metas[] = [
            'order_id'      => $o_id,
            'name'          => $buyer_name,
            'product_title' => $product_name,
            'rebate'        => $rebate,
            'process'       => $process_amount,
            'seller_profit' => $seller_profit,
            'total'         => $rebate + $process_amount + $seller_profit
        ];

       update_user_meta($b_id,'_buyer_details',$buyer_metas);

       

    endif;
    
}


function dongtrader_save_treasury_details($b_id , $o_id){

        //get previous seller trading details saved in user meta
        $treasury_meta  = get_user_meta($b_id, '_treasury_details', true);

        //assign empty array if $treasury_meta is empty 
        $treasury_metas = !empty($treasury_meta) ? $treasury_meta : [];

        //check if buyer user_id exists
        $check_buyer  = dongtrader_check_user($b_id);
        

        if($check_buyer) :

            //get title of the product
            $product_name = dongtrader_get_product($o_id,true);

            //product id
            $v_product_id = dongtrader_get_product($o_id);

            //get buyer or customer name
            $buyer_name   = dongtrader_check_user($b_id , false);

            //get product price by id
            $product_obj = wc_get_product( $v_product_id );

            //variation's parent product id
            $p_p_id = $product_obj->get_parent_id();

            //get product price
            $product_price = intval($product_obj->get_price());

            //rebate amount from order meta distributeable to user whom has brought the product
            $rebate       = dongtrader_get_order_meta($o_id, 'dong_reabate');

            //rebate amount from order meta distributeable to user whom has brought the product
            $process       = dongtrader_get_order_meta($o_id, 'dong_processamt');

            //get membership level
            $member_level   = get_post_meta($p_p_id, '_membership_product_level', true);

            //paid membership pro extra fields
            $pm_meta_vals   = get_pmpro_extrafields_meta($member_level);

            //this value is not working
            $cost           = $pm_meta_vals['dong_cost'];

            $individual_profit = dongtrader_get_order_meta($o_id, 'dong_profit_di'); 

            $distributed_total_amt = $rebate + $process + $individual_profit + $cost;

            $remaining_total_amt   = $product_price - $distributed_total_amt;


            $treasury_metas[] = [
                'order_id'      => $o_id,
                'name'          => $buyer_name,
                'product_title' => $product_name,
                // 'reserve'       => '',
                // 'cost'          => '',
                // 'earning'       => '',
                // 'profit'        => '',
                'total_amt'     => $product_price,
                'distrb_amt'    => $distributed_total_amt,
                'rem_amt'       => $remaining_total_amt,
            ];

            var_dump($treasury_metas);
           // update_user_meta($b_id,'_treasury_details',$treasury_metas);

        endif;
}


function dongtrader_save_commission_details($b_id,$o_id){

        //get previous seller trading details saved in user meta
        $commission_meta  = get_user_meta($b_id, '_commission_details', true);

        //assign empty array if $commission_meta is empty 
        $commission_metas = !empty($commission_meta) ? $commission_meta : [];

        //check if buyer user_id exists
        $check_buyer  = dongtrader_check_user($b_id);

        //check if buyer exists
        if($check_buyer) :

            //get title of the product
            $product_name = dongtrader_get_product($o_id,true);

            //get buyer or customer name
            $buyer_name   = dongtrader_check_user($b_id , false);

            //seller commission
            $seller_com = dongtrader_get_order_meta($o_id,'dong_profit_di');

            $group_com  = dongtrader_get_order_meta($o_id,'dong_profit_dg');

            $owner_com  = dongtrader_get_order_meta($o_id,'dong_earning_amt');

            $c_d_g = $group_com / 5;

            $total = $seller_com + $group_com + $owner_com;

            $commission_metas[] = [
                'order_id'      => $o_id,
                'name'          => $buyer_name,
                'product_title' => $product_name,
                'seller_com'    => $seller_com,
                'group_com'     => $c_d_g,
                'site_com'      => $owner_com,
                'total'         => $total
            ];
            
            update_user_meta($b_id,'_commission_details',$commission_metas);

        endif;
    
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
function dongtrader_split_price_parent($parent_user_id, $order_id)
{

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
        'order_id' => $order_id,
        'rebate' => $rebate,
        'procees_amount' => 0,
        'dong_profit_dg' => $p_d_g,
        'dong_profit_di' => 0,
        'dong_comm_dg' => $c_d_g,
        'dong_comm_cdi' => 0,
        'dong_total' => $rebate + $p_d_g + $c_d_g,
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
function dongtrader_split_price_childrens($childrens)
{

    //if children array is empty dont do anything
    if (empty($childrens)) {
        return;
    }

    //looping inside the childrens array
    foreach ($childrens as $k => $v) {

        //explode key into array to get parent order id and parent user id
        $string_array = explode('-', $k);

        //parent order
        $parent_order = $string_array[2];

        //parent user id (might be required for later)
        $parent_user = $string_array[1];

        //looping inside all childrens
        foreach ($v as $s) {

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
                'order_id' => $parent_order,
                'rebate' => 0,
                'procees_amount' => 0,
                'dong_profit_dg' => $p_a_d_c,
                'dong_profit_di' => 0,
                'dong_comm_dg' => $c_a_t_c,
                'dong_comm_cdi' => 0,
                'dong_total' => 0 + $p_a_d_c + $c_a_t_c,
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
function split_process_charges_to_siteowner($siteownerid = 1, $order_id)
{

    //get previous stored data
    $site_owner = get_user_meta($siteownerid, '_user_trading_details', true);

    //Process Amount Site owner
    $dong_process_amt = dongtrader_get_order_meta($siteownerid, 'dong_processamt');

    //if previous meta is empty assign an empty array
    $site_owners_metas = !empty($site_owner) ? $site_owner : [];

    //apend to previous array to update in  user meta
    $site_owners_metas[] = [
        'order_id' => $order_id,
        'rebate' => 0,
        'procees_amount' => $dong_process_amt,
        'dong_profit_dg' => 0,
        'dong_profit_di' => 0,
        'dong_comm_dg' => 0,
        'dong_comm_cdi' => 0,
        'dong_total' => $dong_process_amt,
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
function dongtrader_split_price_affiliates($aid, $oid)
{

    //get user object
    $user_check = get_user_by('id', $aid);

    //if user exists
    if ($user_check):

        //check if data is stored previously on member meta
        $aff_user_trading_meta = get_user_meta($aid, '_user_trading_details', true);

        //if data is previously stored if not set empty array
        $aff_trading_details_user_meta = !empty($aff_user_trading_meta) ? $aff_user_trading_meta : [];

        //commission distributed to individual
        $dong_comm_cdi = dongtrader_get_order_meta($oid, 'dong_comm_cdi');

        //get profit distributed to individual
        $dong_profit_di = dongtrader_get_order_meta($oid, 'dong_profit_di');

        //meta update array
        $aff_trading_details_user_meta[] = [
            'order_id' => $oid,
            'rebate' => 0,
            'procees_amount' => 0,
            'dong_profit_dg' => 0,
            'dong_profit_di' => $dong_profit_di,
            'dong_comm_dg' => 0,
            'dong_comm_cdi' => $dong_comm_cdi,
            'dong_total' => $dong_profit_di + $dong_comm_cdi,

        ];

        //update array to affliate user
        update_user_meta($aid, '_user_trading_details', $aff_trading_details_user_meta);
    endif;
}

function glassfrog_api_get_persons_of_circles()
{
    global $wpdb;

    //Our Custom table name from the database.
    $table_name = $wpdb->prefix . 'manage_glassfrogs_api';

    //get glassfrog id and user id from custom table manage_glassfrogs_api
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
            if (count($all_people_in_circle) == 3):

                //looping inisde the circle
                foreach ($all_people_in_circle as $ap):

                    //prepare to update to custom database
                    $update_query = $wpdb->prepare("UPDATE $table_name SET in_circle = %d , gf_role_assigned = %s WHERE user_id = %d", 1, $peoples_circle_name, $uid);

                    //update to custom database
                    if ($ap->external_id == $uid):

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

    if (!empty($members)):

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
                'related' => array_values($related),
            ];
        }

        return $new_array;

    else:

        return false;

    endif;

}

function dong_display_trading_details($user_id)
{

    //get user trading details
    $user_trading_metas = get_user_meta($user_id, '_user_trading_details', true);

    // $user_trading_meta = 'a:5:{i:0;a:7:{s:8:"order_id";s:4:"2016";s:6:"rebate";s:3:"2.1";s:14:"dong_profit_dg";d:0.8;s:14:"dong_profit_di";i:0;s:12:"dong_comm_dg";d:0.08;s:13:"dong_comm_cdi";i:0;s:10:"dong_total";d:2.9800000000000004;}i:1;a:7:{s:8:"order_id";s:4:"2015";s:6:"rebate";i:0;s:14:"dong_profit_dg";d:0.8;s:14:"dong_profit_di";i:0;s:12:"dong_comm_dg";d:0.08;s:13:"dong_comm_cdi";i:0;s:10:"dong_total";d:0.88;}i:2;a:7:{s:8:"order_id";s:4:"2015";s:6:"rebate";i:0;s:14:"dong_profit_dg";d:0.8;s:14:"dong_profit_di";i:0;s:12:"dong_comm_dg";d:0.08;s:13:"dong_comm_cdi";i:0;s:10:"dong_total";d:0.88;}i:3;a:7:{s:8:"order_id";s:4:"2013";s:6:"rebate";i:0;s:14:"dong_profit_dg";d:0.8;s:14:"dong_profit_di";i:0;s:12:"dong_comm_dg";d:0.08;s:13:"dong_comm_cdi";i:0;s:10:"dong_total";d:0.88;}i:4;a:7:{s:8:"order_id";s:4:"2013";s:6:"rebate";i:0;s:14:"dong_profit_dg";d:0.8;s:14:"dong_profit_di";i:0;s:12:"dong_comm_dg";d:0.08;s:13:"dong_comm_cdi";i:0;s:10:"dong_total";d:0.88;}}';

    // $user_trading_metas = unserialize($user_trading_meta);
    //if trading details are not empty
    if (!empty($user_trading_metas)):

        // determine number of items per page
        $items_per_page = 5;

        // determine current page number from query parameter
        $current_page = isset($_GET['listpaged']) ? intval($_GET['listpaged']) : 1;

        // calculate start and end indices for items on current page
        $start_index = ($current_page - 1) * $items_per_page;

        if (isset($_REQUEST['filter'])) {

            //get filter data from url parameters
            $get_filter = sanitize_text_field($_REQUEST['filter']);

            if ($get_filter == "all") {

                $all_selected = "selected";
                $date_selected = "";

            } elseif ($get_filter == "within-a-date-range") {

            //get start date
            $start = sanitize_text_field($_REQUEST['start-month']);

            //get end date
            $enddate = sanitize_text_field($_REQUEST['end-month']);
            $date_selected = "selected";
            $all_selected = "";

            if (strtotime($start) > strtotime($enddate)) {
                $temp_date = $start;
                $start = $enddate;
                $enddate = $temp_date;
            }

            $start_date_obj = strtotime($start);
            $end_date_obj = strtotime($enddate);

            if ($start_date_obj && $end_date_obj) {

                $results = array_filter($user_trading_metas, function ($item) use ($start_date_obj, $end_date_obj) {
                    $order = new WC_Order($item['order_id']);
                    $item_date = strtotime($order->get_date_created()->date('Y-m-d'));

                    return ($item_date >= $start_date_obj && $item_date <= $end_date_obj);
                });

                $user_trading_metas = $results;
                $start_index = 0;

            }
        }
    } else {
        $start = "";
        $enddate = "";
        $date_selected = "";
        $all_selected = "";

    }

    $items_for_current_page = array_slice($user_trading_metas, $start_index, $items_per_page);
    // slice the array to get items for current page

    ?>
    <div class="user-trading-details">
        <strong><?php esc_html_e('Your Trading Details', 'cpm-dongtrader');?></strong>
        <div id="member-history-orders" class="widgets-holder-wrap cpm-table-wrap">
        <form id="posts-filter" method="get" action="">
            <label for="filter"><?php _e("Show", 'cpm-dongtrader');?></label>
            <select id="filter" name="filter">
                <option value="all" <?php echo $all_selected; ?>>All</option>
                <option value="within-a-date-range" <?php echo $date_selected; ?>>Within a Date Range</option>
            </select>
            <span id="from" style="display: none;">From</span>
            <input id="start-month" name="start-month" type="date" size="2" value="<?php echo $start; ?>" style="display: none;">
            <span id="to" style="display: none;">To</span>
            <input id="end-month" name="end-month" type="date" size="2" value="<?php echo $enddate; ?>" style="display: none;">
            <span id="filterby" style="display: none;">filter by </span>
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
                    }
                }

                pmpro_ShowMonthOrYear();
            </script>
        </form>

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
if (!empty($items_for_current_page)):
        $rebate_arr = array_column($user_trading_metas, 'rebate');
        $dong_profit_dg_arr = array_column($user_trading_metas, 'dong_profit_dg');
        $dong_profit_di_arr = array_column($user_trading_metas, 'dong_profit_di');
        $dong_comm_dg_arr = array_column($user_trading_metas, 'dong_comm_dg');
        $dong_comm_cdi_arr = array_column($user_trading_metas, 'dong_comm_cdi');
        $dong_total_arr = array_column($user_trading_metas, 'dong_total');
        $i = 1;
        $price_symbol = get_woocommerce_currency_symbol();
        foreach ($items_for_current_page as $utm):
            $order = new WC_Order($utm['order_id']);
            $formatted_order_date = wc_format_datetime($order->get_date_created(), 'Y-m-d');
            $order_backend_link = admin_url('post.php?post=' . $utm['order_id'] . '&action=edit');
            $user_id = $order->get_customer_id();
            $user_details = get_userdata($user_id);
            $user_display_name = $user_details->data->display_name;
            $user_backend_edit_url = get_edit_user_link($user_id);
            ?>
		                        <tr class="enable-sorting">
		                            <td>
		                                <?php echo $utm['order_id']; ?>
		                            </td>
		                            <td>
		                                <?php echo $user_display_name; ?>
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
		                    <?php $i++;endforeach;else: ?>
                        <tr>
                            <td colspan="9"><strong><?php _e('Results Not Found', 'cpm-dongtrader')?></strong></td>
                        </tr>
                    <?php endif;?>
                </tbody>
                <?php if (!empty($items_for_current_page)): ?>
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
                <?php endif;?>
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

function add_custom_tab_to_my_account()
{

    $all_my_account_tabs = [
        [
            'name' => __('My Orders', 'cpm-dongtrader'),
            'slug' => 'dongtrader-orders',
            'position' => 1,

        ],

        [
            'name' => __('Treasury', 'cpm-dongtrader'),
            'slug' => 'dongtrader-treasury',
            'position' => 2,
        ],

        [
            'name' => __('Group', 'cpm-dongtrader'),
            'slug' => 'dongtrader-group',
            'position' => 3,
        ],

        [
            'name' => __('Commission', 'cpm-dongtrader'),
            'slug' => 'dongtrader-commission',
            'position' => 4,
        ],
    ];

    usort($all_my_account_tabs, function($a, $b) {

        return $a['position'] - $b['position'];

    });

    $dongtraders_setting_data = get_option('dongtraders_api_settings_fields');

    $currency_rate_check      = $dongtraders_setting_data['dong_enable_currency'];

    $actual_vnd_rate          = $currency_rate_check == 'on' ?  $dongtraders_setting_data['vnd_rate'] : 1;

    $currency_symbol          = $currency_rate_check == 'on' ?  'D' :  get_woocommerce_currency_symbol();

    $vnd_rate_array           = ['currency_enabled'=> $currency_rate_check, 'vnd_rate'=>$actual_vnd_rate , 'symbol'=>$currency_symbol];

    foreach ($all_my_account_tabs as $k => $v):


        add_rewrite_endpoint($v['slug'], EP_ROOT | EP_PAGES);

        add_filter('query_vars', function ($vars) use ( $v ) {

            $vars[] = $v['slug'];

            return $vars;
        });

        add_filter('woocommerce_account_menu_items', function ($menu_links) use ( $v ) {

            $menu_links = array_slice($menu_links, 0, $v['position'], true)
            + array($v['slug'] => $v['name'])
            + array_slice($menu_links, $v['position'], NULL, true);
    
        return $menu_links;
            
        });

        add_action('woocommerce_account_' . $v['slug'] . '_endpoint', function () use ( $v , $vnd_rate_array ) {

            $tem_path = CPM_DONGTRADER_PLUGIN_DIR.'template-parts'.DIRECTORY_SEPARATOR.'content-'.$v['slug'].'.php';
            
            if (file_exists($tem_path)) {
                load_template($tem_path,true, $vnd_rate_array);
            }
        });

    endforeach;
}
add_action('wp_loaded', 'add_custom_tab_to_my_account');



add_action( 'woocommerce_admin_order_data_after_order_details', 'add_checkbox_to_order_edit_page' );

function add_checkbox_to_order_edit_page( $order ) {
    $order_id = $order->get_id();
    $checked = get_post_meta( $order_id, 'release_profit_amount', true ) ? 'checked' : '';
    $text = get_post_meta( $order_id, 'release_note', true );
    ?>
    <p class="form-field " >
        <label for="release_profit_amount">Check To Release Profit Amount</label>
        <input type="checkbox"style="width:0%;" id="release_profit_amount" name="release_profit_amount" value="1" <?php echo $checked; ?>>
    </p>
    <p class="form-field form-field-wide" id="textarea_wrapper" style="<?php echo $checked ? '' : 'display: none;'; ?>">
        <label for="release_note">Release Note</label>
        <textarea id="release_note" name="release_note"><?php echo esc_html( $text ); ?></textarea>
    </p>
    <?php
}

// Enqueue jQuery and custom script
add_action( 'admin_enqueue_scripts', 'enqueue_scripts' );

function enqueue_scripts() {
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'custom-script', get_stylesheet_directory_uri() . '/custom.js', array('jquery'), '1.0', true );
}

// Add custom script to handle checkbox change
add_action( 'admin_footer', 'add_custom_script' );

function add_custom_script() {
    ?>
    <script>
        jQuery(document).ready(function($) {
            var $checkbox = $('#release_profit_amount');
            var $textarea_wrapper = $('#textarea_wrapper');

            $checkbox.on('change', function() {
                $textarea_wrapper.toggle(this.checked);
            });
        });
    </script>
    <?php
}

// Save checkbox and textarea values to order meta
add_action( 'woocommerce_process_shop_order_meta', 'save_checkbox_and_release_notes' );

function save_checkbox_and_release_notes( $order_id ) {
    if ( isset( $_POST['release_profit_amount'] ) ) {
        $release_profit_amount = '1';
    } else {
        $release_profit_amount = '0';
    }
    update_post_meta( $order_id, 'release_profit_amount', $release_profit_amount );

    $release_note = sanitize_text_field( $_POST['release_note'] );
    update_post_meta( $order_id, 'release_note', $release_note );
}



function test_filter($details, $par1, $par2){

    return $details;
}


function dongtrader_pagination_array($details, $items_per_page = 10 , $items_array=false){

    $current_page = isset($_GET['listpaged']) ? (int) $_GET['listpaged'] : 1;

    // calculate start and end indices for items on current page
    $start_index = ($current_page - 1) * $items_per_page;


    if (isset($_REQUEST['filter'])) {
                
        //get filter data from url parameters
        $get_filter = sanitize_text_field($_REQUEST['filter']);

        if ($get_filter == "all") {

            $all_selected = "selected";
            $date_selected = "";

        } elseif ($get_filter == "within-a-date-range") {

            //get start date
            $start = sanitize_text_field($_REQUEST['start-month']);

            //get end date
            $enddate = sanitize_text_field($_REQUEST['end-month']);
            $date_selected = "selected";
            $all_selected = "";

            if (strtotime($start) > strtotime($enddate)) {
                $temp_date = $start;
                $start = $enddate;
                $enddate = $temp_date;
            }

            $start_date_obj = strtotime($start);
            $end_date_obj = strtotime($enddate);

            if ($start_date_obj && $end_date_obj) {

                $results = array_filter($details, function ($item) use ($start_date_obj, $end_date_obj) {
                    $order = new WC_Order($item['order_id']);
                    $item_date = strtotime($order->get_date_created()->date('Y-m-d'));

                    return ($item_date >= $start_date_obj && $item_date <= $end_date_obj);
                });

                $details = $results;
                $start_index = 0;

            }
    }
    } else {
        $start          = "";
        $enddate        = "";
        $date_selected  = "";
        $all_selected   = "";

    }

    $paginated_array = array_slice($details, $start_index, $items_per_page);

    $params_arr = [
        'startdate'     => $start,
        'enddate'       => $enddate,
        'date_selected' => $date_selected,
        'all_selected'  => $all_selected
    ];

    return $items_array ? $paginated_array : $params_arr ;

}