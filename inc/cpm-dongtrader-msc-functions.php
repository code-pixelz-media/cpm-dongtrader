<?php

/**
 * @author a2code 
 * 
 * It adds a new schedule to the list of schedules that WordPress uses.
 * 
 * @schedules This is the name of the hook that will be used to schedule the event.
 * 
 * @return @the  array.
 */
function dongtrader_one_minutes_interval($schedules)
{

    $schedules['1_minutes'] = array(
        'interval' => 3 * 60,
        'display'  => __('Every 1 minutes', 'cpm-dongtrader'),
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

    dongtrader_distribute_product_prices_to_circle_members();

    // delete_user_meta( 119, '_user_trading_details' );
    // delete_user_meta( 118, '_user_trading_details' );

}


function dongtrader_distribute_product_prices_to_circle_members(){

   
   //get all members from user id
   $members = glassfrog_api_get_persons_of_circles();

   //exit if false
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

    dongtrader_split_price_parent($parent_user_id, $order_id);
    
    dongtrader_split_price_childrens($children_users_id );

    dongtrader_split_price_affiliates($dong_affid , $order_id);

   endforeach;


}


function dongtrader_get_order_meta($orderid, $key)
{

    //get order objects
    $orderobj = new WC_Order($orderid);

    //get value of meta
    return !empty($orderobj->get_meta($key)) ? $orderobj->get_meta($key) : 0;

}



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
        'order_id' => $order_id,
        'rebate' => $rebate,
        'dong_profit_dg' => $p_d_g,
        'dong_profit_di' => 0,
        'dong_comm_dg' => $c_d_g,
        'dong_comm_cdi' => 0,
        'dong_total' => $rebate + $p_d_g + $c_d_g,
    ];

    //update array to user meta 
    update_user_meta($parent_user_id, '_user_trading_details', $customer_and_member_metas);

  
}

function dongtrader_split_price_childrens($childrens ){
   
  
   foreach($childrens as $k=>$v){

        //explode key into array to get parent order id and parent user id
        $string_array = explode('-',$k);

        //parent order
        $parent_order = $string_array[2];

        //parent user id
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
                'order_id' => $parent_order,
                'rebate' => 0,
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

function dongtrader_split_price_affiliates($aid , $oid){

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

        $new_array = array();

        $unique_members = array_unique($members);

        // Loop through each user_id
        foreach ($unique_members as $index => $user_id) {
            $related = array_diff($unique_members, [$user_id]);
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