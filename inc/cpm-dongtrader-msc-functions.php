<?php

add_filter( 'cron_schedules', 'dongtrader_one_minutes_interval' );
function dongtrader_one_minutes_interval( $schedules ) {
    $schedules['1_minutes'] = array(
        'interval' => 1 * 60,
        'display'  => __( 'Every 1 minutes', 'cpm-dongtrader' ),
    );
    return $schedules;
}

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
//Check the api cron function
function glassfrog_api_management()
{
    global $wpdb;
    //Our Custom table name for the database.
    $table_name    = $wpdb->prefix . 'manage_users_gf';
    //get glassfrog id and user id from custom table manage_users_gf
    $results       = $wpdb->get_results("SELECT gf_person_id , user_id FROM $table_name WHERE in_circle = 0 LIMIT 5",ARRAY_A);
    //if not results exit
    if(!$results) return;
    //extract glssfrog id from the results in the above custom query
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
            //get all people of the circle
            $all_people_in_circle   = $api_call->linked->people;
            //exact circle name in the api
            $peoples_circle_name    = $api_call->roles[0]->name;
            //check if five members rule is accomplished in the circle
            if(count($all_people_in_circle) >= 5 ) :
                //looping inisde the circle
                foreach($all_people_in_circle as $ap):
                    //sync api external id and current user id and if not continue the loop
                   // if($ap->external_id != $uid) continue;
                    //get product id from the custom table
                    $productid = $wpdb->get_row("SELECT product_id  FROM $table_name WHERE gf_person_id= $ap->id ")->product_id;
                    //get order id from table
                    $orderid   = $wpdb->get_row("SELECT order_id  FROM $table_name WHERE gf_person_id= $ap->id ")->order_id;
                    //check existence of product id and order id
                    if($productid && $orderid):
                       //trading distribution function
                       $product = wc_get_product( $productid );
                       //get the price of the product
                       $price   = $product->get_price();
                       //price distribution function
                       dongtrader_product_price_distribution($price, $productid, $orderid, $uid);
                       //prepare to update to custom database
                       $update_query = $wpdb->prepare("UPDATE $table_name SET in_circle = %d , gf_role_assigned = %s WHERE user_id = %d", 1, $peoples_circle_name,$uid);
                       //update to custom database
                       $wpdb->query($update_query);
                    //end check existence of product id and order id
                    endif;
                //end  foreach loop started   
                endforeach;
            //five members rule check condition ends
            endif;
        else:
            //do nothing
        endif;

    }

}


// add_action('wp_head', function(){
//     // $api_call = glassfrog_api_request('people/511758/roles','' , 'GET'); 

//     // var_dump($api_call);
// });