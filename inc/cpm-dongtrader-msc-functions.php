<?php

 //All about cron job which is used for price distribution an tabs creation in woocommerce my account page
 

/**
 * Interval Settings filters For the cron job
 *
 * @param [array] $schedules
 * @return array
 */
function dongtrader_one_minutes_interval($schedules)
{

    $schedules['5_minutes'] = array(
        'interval' => 5 * 60,
        'display' => __('Every 5 minutes', 'cpm-dongtrader'),
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

        wp_schedule_event(time(), '5_minutes', 'dongtrader_cron_job_hook');

    }
}

/**
 * Add a custom hook to the cron job, and then run a function when that hook is called.
 */
add_action('dongtrader_cron_job_hook', 'dongtrader_cron_job');
function dongtrader_cron_job()
{
    glassfrog_api_get_persons_of_circles();
    dongtrader_rotate_leadership();
    dongtrader_distribute_product_prices_to_circle_members();

}

add_action('wp_head', function(){
// always required for debugging purpose

//    $user_id = [166,167,168];

//    foreach($user_id as $u){
//     delete_user_meta($u,'_buyer_details');
//     delete_user_meta($u,'_treasury_details');
//     delete_user_meta($u,'_group_details');
//     delete_user_meta($u,'_commission_details');
//    } 

        // glassfrog_api_get_persons_of_circles();
        // dongtrader_rotate_leadership();
        //dongtrader_distribute_product_prices_to_circle_members();
});

/**
 *communicates and syncs with glassfrog api
 * @return void
 */
function glassfrog_api_get_persons_of_circles()
{
    global $wpdb;

    //Our Custom table name from the database.
    $table_name = $wpdb->prefix . 'glassfrog_user_data';

    //get glassfrog id and user id from custom table manage_glassfrogs_api
    $results = $wpdb->get_results("SELECT gf_person_id , user_id FROM $table_name WHERE in_glassfrog = 0  LIMIT 5", ARRAY_A);

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

            $count_people_in_circle = count($all_people_in_circle);
 
            //check if five members rule is accomplished in the circle
            if ($count_people_in_circle == 3):

                //exact circle name in the api from api obj
                $peoples_circle_name = $api_call->roles[0]->name;

                //circle id 
                $circle_id = $api_call->roles[0]->id;

                //looping inisde the circle
                foreach ($all_people_in_circle as $ap):

                    //update to custom database
                    if ($ap->external_id == $uid):

                        //prepare to update to custom database
                        $update_query = $wpdb->prepare("UPDATE $table_name SET in_glassfrog = %d , circle_id= %d  WHERE user_id = %d", 1, $circle_id , $uid);

                        //updated
                        $wpdb->query($update_query);

                        //get wp user id stored as external id from the api
                        $members[] = array(
                            'user_id' => $ap->external_id,
                            'circle_id' => $circle_id,
                            'circle_name' => $peoples_circle_name
                        );
                        
                    endif;

                    //end foreach loop started for looping inside circle members
                endforeach;

                //five members rule check condition ends
            endif;
        else:
            //do nothing
        endif;

    }

    dongtrader_save_glassfrog_details_to_db($members);


}

function dongtrader_save_glassfrog_details_to_db($members){

    if(empty($members)) return;

    global $wpdb;

    $group_table_name = $wpdb->prefix . 'glassfrog_group_data';

    $userdetails_table = $wpdb->prefix . 'glassfrog_user_data';

    $separated_arrays = array_reduce($members, function($result, $item) {

        $circle_id = $item['circle_id'];
        
        // If the circle_id has not been seen before, create a new array for it
        if (!isset($result[$circle_id]))  $result[$circle_id] = array();

        // Add the current item to the array for the current circle_id
        $result[$circle_id][] = $item;
        
        return $result;

    }, array());


    $i=0;
    foreach ($separated_arrays as $circle_id => $users) {
        // Get the current date and the date one month from now
        $created_date = date('Y-m-d H:i:s');
        $expires_date = date('Y-m-d H:i:s', strtotime('+1 month'));
        
        // Set the group leader as the first user in the array
        $group_leader = $users[0]['user_id'];

        // Serialize the user_id array
        $group_array = serialize(array_column($users, 'user_id'));
        
        // Insert the data into the group details table
        $wpdb->insert($group_table_name, array(
            'group_array'   => sanitize_text_field($group_array),
            'circle_id'     => (int) $circle_id,
            'circle_name'   => $users[$i]['circle_name'],
            'created_date'  => sanitize_text_field($created_date),
            'group_leader'  => $group_leader,
            'leader_since'  => $created_date,
            'leadership_expires' => $expires_date,
            'distribution_status' => 'false'
        ));

        //group details id
        $group_details_id = (int) $wpdb->get_var("SELECT id FROM $group_table_name WHERE circle_id=$circle_id");

        //update to user table query
        $update_user_group = $wpdb->prepare("UPDATE $userdetails_table SET group_id = %d  WHERE circle_id = %d", $group_details_id, (int) $circle_id);

        //update user to db
        $wpdb->query($update_user_group);

    $i++;
    }


}

/**
 * Rotate leaderships from database created from the glassfrog  
 *
 * @return void          
 */

function dongtrader_rotate_leadership(){

    global $wpdb;

    $table_name = $wpdb->prefix . 'glassfrog_group_data';

    $current_leaders = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table_name WHERE leadership_expires <= %s",
            current_time('mysql')
        )
    );

    if ($current_leaders) {

        foreach ($current_leaders as $current_leader) {

            //convert serilized string to array
            $group_array = unserialize($current_leader->group_array);

            //get indexe position of user in the array 
            $current_leader_index = array_search($current_leader->group_leader, $group_array);

            //the total members in an  array
            $total_members = count($group_array);

            if ($current_leader_index !== false && $current_leader_index + 1 < $total_members) :

                //set new leader that needs to be updated in dbase
                $new_leader = $group_array[$current_leader_index + 1];

            else :

                //create new array if not fount
                $new_leader = $group_array[0];

            endif;

            // Sanitize the new leader and the updated date
            $new_leader = intval($new_leader);
            
             // Assuming the leader ID is an integer
            $updated_date = current_time('mysql');

            // Calculate the new expiry date (1 month from the updated date)
            $expiry_date = date('Y-m-d H:i:s', strtotime('+1 month', strtotime($updated_date)));

            //update to the database
            $result = $wpdb->update(
                $table_name,
                array(
                    'group_leader' => $new_leader,
                    'updated_date' => $updated_date,
                    'leader_since' => $updated_date, // Update the leader_since column with the new date
                    'leadership_expires' => $expiry_date // Update the leadership_expires column with the new expiry date
                ),
                array('id' => $current_leader->id),
                array(
                    '%d', // leader ID format
                    '%s', // updated_date format
                    '%s', // leader_since format
                    '%s' // leadership_expires format
                ),
                array('%d') // id format
            );
        }
    }


}

function dongtrader_get_orders_by_user($user_id , $group_table_orders) {

    if(empty($user_id) && empty($group_table_orders)) return;
   
    // Create an instance of WC_Order_Query
    $order_query = new WC_Order_Query(array(
        'limit' => -1,
        'customer_id' => $user_id,
        'status' => 'wc-completed',
    ));
    
    // Get the orders
    $orders = $order_query->get_orders();
    
    // Extract order IDs into an array
    $order_ids = array();
    foreach ($orders as $order) {
        $order_ids[] = $order->get_id();
    }
    
    $difference = array_diff($order_ids , $group_table_orders);
    
    return $difference;
}

/**
 * Cron job function to excute distribution process
 *
 * @return void
 */
function dongtrader_distribute_product_prices_to_circle_members(){

    global $wpdb;

    //get group details table name
    $group_data_table = $wpdb->prefix . 'glassfrog_group_data';
    
    //get user detail table name
    $group_user_table = $wpdb->prefix . 'glassfrog_user_data';
    
    //sql query to get list of groups and group leader where distribution is not done or just a update is required
    $group_prepared_query = $wpdb->prepare("SELECT id, group_array, group_leader , distribution_status FROM $group_data_table WHERE distribution_status='false' OR distribution_status='update_required'");
    
    //get results from above sql query
    $group_results = $wpdb->get_results($group_prepared_query, ARRAY_A);

    //if group is not empty
    if (!empty($group_results)) {

        //create a new array in required fromat by manipulating group details table results 
        $groups_details = array_map(function($group_result) use($wpdb, $group_user_table , $group_data_table) {
            
            //unserilize group lsit from a serilized string(saved as serilized string in database)
            $group_array = unserialize($group_result['group_array']);
            
            //group id from query result
            $group_id = $group_result['id'];

            //get distribution status
            $d_status = $group_result['distribution_status'];

            //get all related members from the group
            $related = array_values(array_diff($group_array, array($group_result['group_leader'])));

            //get circle name from group_data_table
            $group_name =  $wpdb->get_var($wpdb->prepare("SELECT circle_name FROM $group_data_table WHERE id = %d",  (int) $group_id));
            
            //intilized new array to make its format = [order_id1 => user_id1 , order_id2=>user_id1, order_id3=>user_id2]
            $orders_user = array();

            //intialized new array to make its format = [user_id1 => [order_id2 ,order_id3]]
            $users_order  = array();

            //looping inside each group array
            foreach ($group_array as $user_id) {
                
                //get all order foreach group member from group_user_table
                $user_orders = $wpdb->get_var($wpdb->prepare("SELECT all_orders FROM $group_user_table WHERE user_id = %s", $user_id));
                
                //if the user exists in group_user_table
                if ($user_orders !== null) {
                    
                    //unserlize the serilized orders
                    $unserialized_orders = unserialize($user_orders);

                    //if the row requires a update
                    if($d_status == 'update_required'){

                        //check all orders by user id and orders stored in our custom $group_user_table and find a different order id
                        $diff = dongtrader_get_orders_by_user($user_id , $unserialized_orders);

                        //check if differences between two arrays is empty or not
                        if(!empty($diff)) {
                            
                            //add differed element to previous unserialized_orders
                            $new_unnserialized_orders = $diff + $unserialized_orders;

                            //serialize the new array
                            $new_serialized_orders = serialize($new_unnserialized_orders);

                            //preapre for updating serialized data
                            $update_query = $wpdb->prepare("UPDATE $group_user_table SET all_orders = %s WHERE user_id = %d", $new_serialized_orders, (int) $user_id);
                            
                            //update data finally
                            $update = $wpdb->query($update_query);

                            if($update === 1) {
                             
                                // prepare for update to group table
                                $group_update = $wpdb->prepare("UPDATE $group_data_table SET distribution_status = 'true' WHERE id = %d", (int) $group_id);

                                // update the group_data_table distribution status to true for last user_id finally
                                $wpdb->query($group_update);

                            }
                            //update $users_order array format = [user_id1 => [order_id2 order_id3]]
                            $users_order[$user_id] = $diff;

                            //again looping inside serilized orders
                            foreach($diff as $uo){

                                //update $orders_user in format of [order_id1 => user_id1 , order_id2=>user_id1, order_id3=>user_id2]
                                $orders_user[$uo] = $user_id;
                            }
                        
                        } 
                    }else{

                        //update $users_order array format = [user_id1 => [order_id2 order_id3]]
                        $users_order[$user_id] = $unserialized_orders;


                        // if(!is_array($unserialized_orders)) continue;
                        //again looping inside serilized orders
                        foreach($unserialized_orders as $uo){

                            //update $orders_user in format of [order_id1 => user_id1 , order_id2=>user_id1, order_id3=>user_id2]
                            $orders_user[$uo] = $user_id;
                        }
                    }
                   
                }
         
            }
          
            //create and return the manipulated array to required format
            return array(
                'parent_user_id' => $group_result['group_leader'],
                'related' => $related,
                'all_members' => $group_array,
                'group_id' => $group_id,
                'group_name' => $group_name,
                'user_orders' =>$users_order,
                'orders_user' => $orders_user
            );

        }, $group_results);

        //looping inside the created array format 
        foreach($groups_details as $gd){

            //saving all order details
            detente_save_order_details( $gd['user_orders']);

            //saving all treasury  details
            detente_save_treasury_details($gd['orders_user'] , $gd['all_members']);

            //saving all commission details
            detente_save_commission_details($gd['orders_user'] , $gd['all_members']);

            //saving all group details
            detente_save_group_details($gd['parent_user_id'], $gd['orders_user'],  $gd['group_name'] , $gd['all_members']);

            //set the group data table row distribution status to true so that it wont loop in forever every time
            $wpdb->update($group_data_table,
                array('distribution_status' => 'true'),
                array('id' => (int) $gd['group_id']),
                array('%s'),
                array('%d')
            );
        }

    }

}


function detente_save_order_details($members_and_orders){

    foreach($members_and_orders as $member=>$order){

        //if order is empty skip this loop
        if(!$order) continue;

        $buyer_meta =  get_user_meta($member , '_buyer_details', true);

        //assign empty array if $buyer_meta is empty 
        $buyer_metas = !empty($buyer_meta) ? $buyer_meta : [];

        //get buyer or customer name
        $buyer_name   = dongtrader_check_user($member , false);

        //looping inside all orders of this 
        foreach($order as $ao){

            //get affiliate from order meta
            $seller_id    = dongtrader_get_order_meta($ao, 'dong_affid');
            
            //get seller or affiliate id we might not need this for instance
            $seller_name  = dongtrader_check_user($seller_id , false);

            //rebate amount from order meta distributeable to user whom has brought the product
            $rebate       = dongtrader_get_order_meta($ao, 'dong_reabate');
        
            //get title of the product
            $product_name = dongtrader_get_product($ao,true);

            //get actual process amount from order meta
            $process_amt  = dongtrader_get_order_meta($ao,'dong_processamt');

            //get seller or affiliate profit amount from order meta
            $seller_profit= dongtrader_get_order_meta($ao,'dong_profit_di'); 

            //if seller is buyer himself compare ids and then set process amount 
            $process_amount  = $member == $seller_id ? $process_amt : 0;

            //total amount 
            $totals = number_format($rebate + $process_amount + $seller_profit, 2);

            //new array for new order which we will append to 
            $buyer_metas[] = [
                'order_id'      => $ao,
                'name'          => $buyer_name,
                'product_title' => $product_name,
                'rebate'        => $rebate,
                'process'       => $process_amount,
                'seller_profit' => $seller_profit,
                'total'         => $totals,
               
            ];

            update_user_meta($member,'_buyer_details',$buyer_metas);

        }

    }
 
}

function detente_save_commission_details($order_members ,$allmems) {

    foreach ($order_members as $order => $user) {

        $commission_meta = get_user_meta($user, '_commission_details', true);
            
        //assign empty array if $commission_meta is empt
        $commission_metas = !empty($commission_meta) ? $commission_meta : [];

        //get name of the product
        $product_name = dongtrader_get_product($order, true);
        
        //buyers name
        $buyer_name = dongtrader_check_user($user, false);
        
        //commission to seller
        $seller_com = dongtrader_get_order_meta($order, 'dong_profit_di');
        
        //commission to group
        $group_com = dongtrader_get_order_meta($order, 'dong_profit_dg');
        
        //commission to owner
        $owner_com = dongtrader_get_order_meta($order, 'dong_earning_amt');

        //commission to group
        $c_d_g = number_format($group_com / 5, 2);
        
        //totals
        $total = number_format($seller_com + $c_d_g + $owner_com, 2);

        // Distribute the commission equally to each member
        $commission_metas[] = [
            'order_id' => $order,
            'name' => dongtrader_check_user($user, false),
            'product_title' => $product_name,
            'seller_com' => $seller_com,
            'group_com' =>  $c_d_g,
            'site_com' => $owner_com,
            'total' => $total
            
        ];
        foreach($allmems as $m){
            update_user_meta($m, '_commission_details', $commission_metas);
        }

    }
}

function detente_save_treasury_details($orders_members , $allmems) {

    foreach($orders_members as $order=>$user){

         //get previous seller trading details saved in user meta
         $treasury_meta  = get_user_meta($user, '_treasury_details', true);

         //assign empty array if $treasury_meta is empty 
         $treasury_metas = !empty($treasury_meta) ? $treasury_meta : [];
 
         //get title of the product
         $product_name = dongtrader_get_product($order,true);
 
         //product id
         $v_product_id = dongtrader_get_product($order);
 
         //get buyer or customer name
         $buyer_name   = dongtrader_check_user($user , false);
 
         //get product price by id
         $product_obj = wc_get_product( $v_product_id );
 
         //variation's parent product id
         $p_p_id = $product_obj->get_parent_id();
 
         //get product price
         $product_price = intval($product_obj->get_price());
 
         //rebate amount from order meta distributeable to user whom has brought the product
         $rebate       = dongtrader_get_order_meta($order, 'dong_reabate');
 
         //rebate amount from order meta distributeable to user whom has brought the product
         $process       = dongtrader_get_order_meta($order, 'dong_processamt');
 
         //get membership level
         $member_level   = get_post_meta($p_p_id, '_membership_product_level', true);
 
         //paid membership pro extra fields
         $pm_meta_vals   = get_pmpro_extrafields_meta($member_level);
 
         //this value is not working
         $cost           = $pm_meta_vals['dong_cost'];
 
         $individual_profit = dongtrader_get_order_meta($order, 'dong_profit_di'); 
 
         $distributed_total_amt = $rebate + $process + $individual_profit + $cost;
 
         $remaining_total_amt   = $product_price - $distributed_total_amt;
 
         $treasury_metas[] = [
             'order_id'      => $order,
             'name'          => $buyer_name,
             'product_title' => $product_name,
             'total_amt'     => $product_price,
             'distrb_amt'    => $distributed_total_amt,
             'rem_amt'       => $remaining_total_amt,
         ];


         foreach($allmems as $am) {
            update_user_meta($am,'_treasury_details', $treasury_metas);
         }
 
    }
}
  
  
function detente_save_group_details($leader,$orders_members,$group_name , $allmembers ){

    foreach($orders_members as $order=>$user){

            //get previous seller trading details saved in user meta
            $group_meta  = get_user_meta($user, '_group_details', true);

            //assign empty array if $treasury_meta is empty 
            $group_metas = !empty($group_meta) ? $group_meta : [];

            $orderobj = new WC_Order($order);

            $formatted_order_date   = wc_format_datetime($orderobj->get_date_created(), 'Y-m-d');

            $group_profit_amount    = dongtrader_get_order_meta($order, 'dong_profit_dg'); 

            $check_release_status   = dongtrader_get_order_meta($order, 'release_profit_amount'); 
            
            $check_release_status_bool = $check_release_status == '1' ? true : false;
            
            $release_note           = $check_release_status_bool ? get_post_meta((int) $order, 'release_note',true) : false;

            $gp = $user == $leader ? $group_profit_amount : 0;

            $group_metas[] = [
                'order_id'      => $check_release_status_bool ? '--' :$order,
                'order_date'    => $formatted_order_date,
                'gf_name'       => $group_name    ,
                'profit_amount'     => $gp,
                'release'    => ['enabled' => $check_release_status_bool , 'note' => $release_note ],
            ];

            foreach($allmembers as $m){
                update_user_meta($m , '_group_details' , $group_metas );
            }
    }
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
 * Adding tabs and tables for woocommerce my account page documetations can be easily found for those.
 *
 * @return void
 */
function add_custom_tab_to_my_account()
{

    $all_my_account_tabs = [
        [
            'name' => __('My Orders', 'cpm-dongtrader'),
            'slug' => 'detente-orders',
            'position' => 1,

        ],

        [
            'name' => __('Treasury', 'cpm-dongtrader'),
            'slug' => 'detente-treasury',
            'position' => 2,
        ],

        [
            'name' => __('Group', 'cpm-dongtrader'),
            'slug' => 'detente-group',
            'position' => 3,
        ],

        [
            'name' => __('Commission', 'cpm-dongtrader'),
            'slug' => 'detente-commission',
            'position' => 4,
        ],
    ];

    usort($all_my_account_tabs, function($a, $b) {

        return $a['position'] - $b['position'];

    });

    $dongtraders_setting_data = get_option('dongtraders_api_settings_fields');

    $currency_rate_check      = !empty($dongtraders_setting_data['dong_enable_currency']) ? $dongtraders_setting_data['dong_enable_currency'] :false;

    $actual_vnd_rate          = $currency_rate_check == 'on' ?  $dongtraders_setting_data['vnd_rate'] : 1;

    $currency_symbol          = $currency_rate_check == 'on' ?  '₫' :  get_woocommerce_currency_symbol();

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


