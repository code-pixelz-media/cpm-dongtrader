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
function dongtrader_one_minutes_interval( $schedules ) {

    $schedules['1_minutes'] = array(
        'interval' => 3 * 60,
        'display'  => __( 'Every 1 minutes', 'cpm-dongtrader' ),
    );

    return $schedules;
}

add_filter( 'cron_schedules', 'dongtrader_one_minutes_interval' );

/**
 * If the cron job isn't scheduled, schedule it.
 */
add_action( 'wp', 'dongtrader_schedule_cron_job' );
function dongtrader_schedule_cron_job() {
    if ( ! wp_next_scheduled( 'dongtrader_cron_job_hook' ) ) {
        wp_schedule_event( time(), '1_minutes', 'dongtrader_cron_job_hook' );
    }
}

/**
 * Add a custom hook to the cron job, and then run a function when that hook is called.
 */
add_action( 'dongtrader_cron_job_hook', 'dongtrader_cron_job');
function dongtrader_cron_job() {

   glassfrog_api_management();

}

/**
 * It checks if the user is in a circle with 5 members, if so, it distributes the price of the product
 * to the user and the other members of the circle
 */
function glassfrog_api_management()
{
    global $wpdb;

    //Our Custom table name from the database.
    $table_name = $wpdb->prefix . 'manage_users_gf';

    //get glassfrog id and user id from custom table manage_users_gf
    $results = $wpdb->get_results("SELECT gf_person_id , user_id FROM $table_name WHERE in_circle = 0 LIMIT 5",ARRAY_A);

    //if not results exit
    if(!$results) return;

    //extract glassfrog id from the results in the above custom query
    $glassfrog_ids = wp_list_pluck($results,'gf_person_id');

    //extract user id from the results in the above custom query
    $user_ids      = wp_list_pluck($results,'user_id');

    //combine whole array into one
    $all_users     = array_combine($glassfrog_ids, $user_ids);

    //looping inside our all users
    foreach($all_users as $gfid=>$uid){

        //call the glassfrog api
        $api_call = glassfrog_api_request('people/'.$gfid.'/roles','' , 'GET');

        //check if api call is all good       
        if($api_call) :

            //get all people of the circle from api obj
            $all_people_in_circle   = $api_call->linked->people;

            //exact circle name in the api from api obj
            $peoples_circle_name    = $api_call->roles[0]->name;

            //check if five members rule is accomplished in the circle
            if(count($all_people_in_circle) == 5) :

                //looping inisde the circle
                foreach($all_people_in_circle as $ap):

                    //get product id from the custom table
                    $productid = $wpdb->get_row("SELECT product_id  FROM $table_name WHERE gf_person_id= $ap->id ")->product_id;
                    
                    //get order id from table
                    $orderid   = $wpdb->get_row("SELECT order_id  FROM $table_name WHERE gf_person_id= $ap->id ")->order_id;
                    
                    //check existence of product id and order id
                    if($productid && $orderid):
                       
                       //trading distribution function
                       $product = wc_get_product( $productid );
                       
                       //price distribution function older one
                       //dongtrader_product_price_distribution($price, $productid, $orderid, $uid);

                       //price split function
                       dongtrader_split_price($uid,$productid,$orderid);

                       //prepare to update to custom database
                       $update_query = $wpdb->prepare("UPDATE $table_name SET in_circle = %d , gf_role_assigned = %s WHERE user_id = %d", 1, $peoples_circle_name,$uid);
                       
                       //update to custom database
                       $wpdb->query($update_query);

                    //end check existence of product id and order id
                    endif;

                //end foreach loop started   
                endforeach;

            //five members rule check condition ends
            endif;
        else:
            //do nothing
        endif;

    }

}

/**
 * It gets the value of a meta key from a WooCommerce order object
 * 
 * @orderobj The order object.
 * @key The meta key to retrieve.
 * 
 * @returnThe value of the meta key.
 */
function dongtrader_get_order_meta($orderid, $key){

    //get order objects
    $orderobj = new WC_Order($orderid);

    //get value of meta
    return !empty($orderobj->get_meta($key)) ? $orderobj->get_meta($key) : 0;

}

function dongtrader_split_price($member,$product,$orderid){

    //get order object
    $order_obj = new WC_Order($orderid);

    //get  rebate amount
    $rebate          = dongtrader_get_order_meta($orderid , 'dong_reabate');

    //get process amount
    $dong_processamt = dongtrader_get_order_meta($orderid , 'dong_processamt');

    //get profit amount
    $dong_profitamt  = dongtrader_get_order_meta($orderid, 'dong_profitamt');

    //get profit distributed to individual
    $dong_profit_di  = dongtrader_get_order_meta($orderid,'dong_profit_di') ;

    //get profit distributed to group    
    $dong_profit_dg  = dongtrader_get_order_meta($orderid,'dong_profit_dg') ;

    //commisssion amount from profit
    $dong_profit_dca = dongtrader_get_order_meta($orderid,'dong_profit_dca') ;
    
    //commission distributed to individual
    $dong_comm_cdi   = dongtrader_get_order_meta($orderid,'dong_comm_cdi') ;
    
    //commission distributed to group
    $dong_comm_cdg   = dongtrader_get_order_meta($orderid,'dong_comm_cdg') ;

    //treasury from 12$ product refer to the table provide by client
    $dong_treasury   = dongtrader_get_order_meta($orderid,'dong_treasury') ;

    //some calculation done in pm pro
    $dong_earning_amt= dongtrader_get_order_meta($orderid,'dong_earning_amt') ;

    //not clarified yet
    $dong_discounts  = dongtrader_get_order_meta($orderid,'dong_discounts') ;

    //not clarified yet
    $dong_reserve    = dongtrader_get_order_meta($orderid,'dong_reserve') ;

    //unknown
    $dong_cost       = dongtrader_get_order_meta($orderid,'dong_cost') ;

    //affiliate id saved in order meta
    $dong_affid      = dongtrader_get_order_meta($orderid,'dong_affid') ;

    //get site owner 
    $site_owner = 1;

    //if site owner is not empty
    if(!empty($site_owner)):

        //check if siteowner exists
        $site_owner_check = get_user_by( 'id', $site_owner );

        //check if siteowner exists
        if($site_owner_check):
        
        //end siteowner check
        endif;

    //end site owner empty check
    endif;

    //get affiliate id
    

    //check the value of affiliate
    if(!empty($dong_affid)) :

        //check if user exists
        $user_check = get_user_by( 'id', $dong_affid );

        //if user exists
        if($user_check) :

             //check if data is stored previously on member meta
            $aff_user_trading_meta = get_user_meta($dong_affid, '_user_trading_details', true);

            //if data is previously stored if not set empty array
            $aff_trading_details_user_meta = !empty($aff_user_trading_meta) ? $aff_user_trading_meta : [];
           
            //meta update array
            $aff_trading_details_user_meta[] = [
                'order_id' => $orderid,
                'rebate' => 0,
                'dong_profit_dg' => 0,
                'dong_profit_di' => $dong_profit_di,
                'dong_comm_dg' => 0,
                'dong_comm_cdi' => $dong_comm_cdi,
                'dong_total'  => $dong_profit_di + $dong_comm_cdi

            ];

            //update to affiliate
            update_user_meta($dong_affid,'_user_trading_details',$aff_user_trading_meta);

        endif;

    //end 148
    endif;

    //get customer id from order id
    $customer_id = $order_obj->get_user_id();

    //if customer is member of same circle
    if($customer_id == $member) :

        //get previous member data
        $customer_and_member_meta = get_user_meta($customer_id, '_user_trading_details', true);

        //if previous meta is empty assign an empty array
        $customer_and_member_metas = !empty($customer_and_member_meta) ? $customer_and_member_meta : [];

        //profit distributed to group
        $p_d_g = $dong_profit_dg / 5;

        //commision amount distributed to group
        $c_d_g = $dong_comm_cdg / 5;

        //apend to previous array to update in  user meta
        $customer_and_member_metas[] = [
            'order_id' => $orderid,
            'rebate' => $rebate,
            'dong_profit_dg' => $p_d_g,
            'dong_profit_di' => 0,
            'dong_comm_dg' => $c_d_g,
            'dong_comm_cdi' => 0,
            'dong_total'  => $rebate + $p_d_g + $c_d_g
        ];

        //update array to user
        update_user_meta($member,'_user_trading_details',$customer_and_member_metas);

    //end customer exists check
    endif;

    //if customer is not the member of same circle rebate amount is empty
    if($customer_id != $member) :

        //get prev user meta
        $customer_not_mem_meta = get_user_meta($customer_id, '_user_trading_details', true);

        //if previous meta is empty assign an empty array
        $customer_not_mem_metas = !empty($customer_not_mem_meta) ? $customer_not_mem_meta : [];

        //profit amount that must be distributed to circle
        $p_a_d_c = $dong_profit_dg / 5;

        //commission amount that must be distributed to group
        $c_a_t_c = $dong_comm_cdg / 5;

        //apend to previous array to update in  user meta
        $customer_not_mem_metas[] = [
            'order_id' => $orderid,
            'rebate' => 0,
            'dong_profit_dg' => $p_a_d_c,
            'dong_profit_di' => 0,
            'dong_comm_dg' => $c_a_t_c,
            'dong_comm_cdi' => 0,
            'dong_total'  => 0 + $p_a_d_c + $c_a_t_c
        ];

        //update array to user meta
        update_user_meta($member,'_user_trading_details',$customer_not_mem_metas);

    endif;
}


add_action('wp_head', function(){

    // $gf_membership_checkbox = get_post_meta(1320, '_glassfrog_checkbox', true);

    // var_dump($gf_membership_checkbox);
    //$p = wc_get_product(1320);

    // var_dump($p);
});