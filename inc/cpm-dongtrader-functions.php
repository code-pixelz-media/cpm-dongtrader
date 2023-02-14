<?php

/**
 * It returns the API credentials for the API name passed to it
 * 
 * @param apiname The name of the API you're using.
 * 
 * @return The API credentials for the API name passed in.
 */

function dongtrader_get_api_cred($apiname)
{
    if (!$apiname) return;
    if ($apiname == 'qrtiger') {
        $api_utils = get_option('dongtrader_api_settings_qrtiger');
    } else if ($apiname == 'glassfrog') {
        $api_utils = get_option('dongtrader_api_settings_glassfrog');
    } else if ($apiname == 'crowdsignal') {
        $api_utils = get_option('dongtrader_api_settings_crowdsignal');
    }
    return $api_utils;
}


/*This function is used to make the Qrtiger API requests. 
 *These are valid urls . Requests might be  costly so plz use mock url from stoplight api
 *GET URL : https://qrtiger.com/data/6BF7
 *POST URL  : https://qrtiger.com/api/campaign/ 
 *Function Call Process is Here with the endpoints
 *$POST = dongtrader_http_requests('/api/campaign/', array(), 'POST');
 *$GET = dongtrader_http_requests('/data/6BF7', array(), 'GET');    
 */
function qrtiger_api_request($endpoint = '', $bodyParams = array(), $method = "GET")
{
    /* Get the API credentials from the database. */
    $qrtiger_creds = get_option('dongtraders_api_settings_fields');
    /* Check if the API credentials are empty or not. */
    $checkFields = !empty($qrtiger_creds['qrtiger-api-key']) && !empty($qrtiger_creds['qrtiger-api-url']) ? true : false;
    /* Check if the API credentials are empty or not. */
    if (!$checkFields) return;
    /* Get the API URL from the database. */
    $qrtiger_api_root_url = $qrtiger_creds['qrtiger-api-url'];
    /* Getting the API key from the database. */
    $qrtiger_api_key      = $qrtiger_creds['qrtiger-api-key'];
    /* Concatenating the API root URL with the endpoint. */
    $build_url = $qrtiger_api_root_url . $endpoint;
    /* A default array. */

    /* Taking the default array and merging it with the  array. */
    //$body = wp_json_encode(wp_parse_args($qrtiger_defaults, $bodyParams));
    $body = wp_json_encode($bodyParams);
    /* Setting the options for the request. */
    $options = [
        'body'        => $method == "POST" ? $body : '',
        'headers'     => [
            'Authorization' => 'Bearer ' . $qrtiger_api_key,
            'Content-Type' => 'application/json',
        ],
        'timeout'     => 30,

    ];

    // $build_url = 'https://stoplight.io/mocks/qrtiger/qrtiger-api/7801905';

    /* A ternary operator to check get or post parameter and use functions accordingly*/
    $response_received  = $method == 'POST' ? wp_remote_post($build_url, $options) : wp_remote_get($build_url, $options);
    /* Get the response code from the response received. */
    $response_status    = wp_remote_retrieve_response_code($response_received);
    /* Checking if the response status is 200 or not. If it is 200 then it will return the body of the response. */
    $response_body      = $response_status == '200' ? wp_remote_retrieve_body($response_received) : false;
    /* Checking if the response body is not empty and then decoding the response body. */
    $response_object     = $response_body ? json_decode($response_body) : false;

    $resp = $response_object->status != 403 ? $response_object : false;

    return $resp;
}



/* A function that is used to make the Glassfrog API requests. */
function glassfrog_api_request($endpoint = '', $str, $method = "GET")
{
    /* Get the API credentials from the database. */
    $api_creds = get_option('dongtraders_api_settings_fields');
    /*Get API Key*/
    $gf_api_key = $api_creds['glassfrog-api-key'];
    /*Get API Url*/
    $gf_api_url = $api_creds['glassfrog-api-url'];
    /* Check if the API credentials are empty or not. */
    $checkFields = !empty($gf_api_key) && !empty($gf_api_url) ? true : false;
    /* if not valid return false */
    if (!$checkFields) return;
     /* Build Url for api request */
    $build_url = $gf_api_url . $endpoint;
    /* Params For API requests */
    $options = [
        'body' => $method == "POST" ? $str : '',
        'headers'     => [
            'X-Auth-Token' => $gf_api_key,
            'Content-Type' => 'application/json',
           
        ],
        

    ];

    /* A ternary operator to check get or post parameter and use functions accordingly*/
    $response_received  = $method == 'POST' ? wp_remote_post($build_url, $options) : wp_remote_get($build_url, $options);
    /* Get the response code from the response received. */
    $response_status    = wp_remote_retrieve_response_code($response_received);
    /* Checking if the response status is 200 or not. If it is 200 then it will return the body of the response. */
    $response_body      = $response_status == '200' ? wp_remote_retrieve_body($response_received) : false;
    /* Checking if the response body is not empty and then decoding the response body. */
    $response_object     = $response_body ? json_decode($response_body) : false;

    return $response_object;
}



/*This function is used to make ajax call on woocommerce my account page. 
 *It calls qrtiger api and fetches qrcodes data
 */
add_action('wp_ajax_dongtrader_generate_qr2', 'dongtrader_generate_qr2');

function dongtrader_generate_qr2()
{
    $qr_size =  sanitize_text_field($_POST['qrsize']);
    $qr_url  =  sanitize_url($_POST['qrurl']);
    $qr_color =  sanitize_text_field($_POST['qrcolor']);
    $dong_user_id = get_current_user_id();
    $response = !empty($qr_size) && !empty($qr_url) && !empty(trim($qr_color)) ? true : false;
    $notify_to_js = array(
        'dataStatus' => $response,
        'user' => $dong_user_id,
        'apistatus' => false,
    );
    if ($response) {
        //either background color or qr elements color can be set
        $qrtiger_array = [
            "qr" => [
                "size" => $qr_size,
                "colorDark" => $qr_color,
                "backgroundColor" => '',
                "transparentBkg" => false,
            ],
            "qrUrl" => $qr_url,
            "qrType" => "qr2",
            "qrCategory" => "url"
        ];
        $qrtiger_api_call = qrtiger_api_request('/api/campaign/', $qrtiger_array, 'POST');

        if ($qrtiger_api_call) {
            $notify_to_js['apistatus'] = true;
            $current_dong_qr_array = array(
                'created_by'    => $dong_user_id,
                'qr_image_url'  => $qrtiger_api_call->data->qrImage,
                'created_at'    => $qrtiger_api_call->data->createdAt,
                'updated_at'    => $qrtiger_api_call->data->updatedAt,
                'qr_id'         => $qrtiger_api_call->data->qrId,
            );

            $old_dong_qr_array = get_option('dong_user_qr_values');
            $prev_value_check  = !empty($old_dong_qr_array) ? true : false;
            if ($prev_value_check) {
                array_push($old_dong_qr_array, $current_dong_qr_array);
                // update_user_meta($dong_user_id, 'dong_user_qr_values', $old_dong_qr_array);
                update_option('dong_user_qr_values', array_reverse($old_dong_qr_array));
            } else {
                // update_user_meta($dong_user_id, 'dong_user_qr_values', [$current_dong_qr_array]);
                update_option('dong_user_qr_values', [$current_dong_qr_array]);
            }
        }
    }

    echo wp_json_encode($notify_to_js);


    wp_die();
}

//dongtrader_delete_qr

add_action('wp_ajax_dongtrader_delete_qr', 'dongtrader_delete_qr');

function dongtrader_delete_qr()
{


    $index          = esc_attr($_POST['qrIndex']);
    $dong_qr_array  = get_option('dong_user_qr_values');
    $resp_array     = array('success' => false, 'd' => $dong_qr_array, 'i' => $index);

    if ($dong_qr_array && !empty($index) || $index == '0') {

        unset($dong_qr_array[$index]);
        $new_arrray = $dong_qr_array;
        update_option('dong_user_qr_values', $new_arrray);
        $resp_array['success'] = true;
    }
    echo wp_json_encode($resp_array);
    wp_die();
}

//Simple function to make api calls with parameters as an array

function dongtrader_variable_color_to_rgb_color($color)
{
    switch ($color) {
        case 'Orange':
            $color = 'rgb(255, 51, 0)';
            break;
        case 'Purple':
            $color = 'rgb(245,66,245)';
            break;
        case 'Red':
            $color = 'rgb(255, 0, 0)';
            break;

        case 'Blue':
            $color = 'rgb(36, 36, 143)';
            break;

        case 'Green':
            $color = 'rgb(0, 204, 0)';
            break;

        default:
            $color = 'rgb(38, 38, 38)';
    }

    return $color;
}


function dongtrader_ajax_test_helper($color='', $url='' , $size=''){


    return array(
            'qr_image_url'  => 'https://qrtiger.com/qr/OXX3.png',
            'created_at'    =>'date',
            'updated_at'    => 'date',
            'qr_id' => '0XX3'
    );

}

function dongtrader_ajax_helper($color,$url,$size='500'){
    
    $current_dong_qr_array = true;
    $qrtiger_array = [
        "qr" => [
            "size" => $size,
            "colorDark" => $color,
            "transparentBkg" => false,
        ],
        "qrUrl" => $url,
        "qrType" => "qr2",
        "qrCategory" => "url"
    ];
    $qrtiger_api_call = qrtiger_api_request('/api/campaign/', $qrtiger_array, 'POST');

    if($qrtiger_api_call){
        $current_dong_qr_array = array(
            "qr_image_url"  => $qrtiger_api_call->data->qrImage,
            "created_at"    => $qrtiger_api_call->data->createdAt,
            "updated_at"    => $qrtiger_api_call->data->updatedAt,
            "qr_id"         => $qrtiger_api_call->data->qrId,
        );

    }else{

        $current_dong_qr_array = false;
    }

    return $current_dong_qr_array;

}

add_action('wp_ajax_dongtrader_meta_qr_generator', 'dongtrader_meta_qr_generator');

function dongtrader_meta_qr_generator()
{

    $intiator       = esc_attr($_POST['intiator']);
    $productnum     = esc_attr($_POST['productnums']);
    $product        = wc_get_product($productnum);
    $product_url    = get_permalink($productnum);
    $check_out_Url  = get_permalink($productnum) . '?add=1';

    //add your datas here
    $resp = array(
        'success' => false,
        'template' => '',
        'pid' => $productnum,
        'initiator' => $intiator,
        "purl" => $product_url
    );
 
//for products qr code
    if($intiator == '_product_qr_codes'){
    
        $current_data = dongtrader_ajax_helper('rgb(87, 3, 48)',$product_url);
        if(!empty($current_data)){
            $update_data = json_encode($current_data);
            $resp['success'] = true;
            $resp['template'] = '<div id="" class="dong-qr-components">
            <img src="'.$current_data['qr_image_url'].'" alt="" width="200" height="200">
            <button data-url= "'.$current_data['qr_image_url'].'" class="button button-primary button-large url-copy" >Copy QR URL</button>
            <input type= "hidden" data-id="' . esc_attr($productnum) . '"  name ="_product_qr_codes" value="'.esc_attr($update_data).'">
            <button data-meta="_product_qr_codes" data-remove="'.$productnum . '" class="button-primary button-large qr-remover" style="" >Remove</button></div>';

            update_post_meta($productnum, '_product_qr_codes', $update_data);
            
        }
//for direct checkout
    }elseif($intiator == '_product-qr-direct-checkouts'){
        //call api helper function
        $current_data = dongtrader_ajax_helper('rgb(0, 102, 204)',$check_out_Url);
        if(!empty($current_data)){
            $update_data = json_encode($current_data);
            update_post_meta($productnum, '_product-qr-direct-checkouts',$update_data);
            $resp['success'] = true;
            $resp['template']= '<div id="" class="dong-qr-components">
            <img src="'.$current_data['qr_image_url'].'" alt="" width="200" height="200">
            <button data-url= "'.$current_data['qr_image_url'].'" class="button button-primary button-large url-copy" >Copy QR URL</button>
            <input type= "hidden" data-id="' . esc_attr($productnum) . '"  name ="_product-qr-direct-checkouts" value="'.esc_attr($update_data).'">
            <button data-meta="_product_qr_codes" data-remove="'.$productnum . '" class="button-primary button-large qr-remover" style="" >Remove</button></div>';
        }
//for variable products
    }elseif($intiator == '_product-qr-variabled'){
        $variations =  esc_attr($_POST['variations']);
        $loop = esc_attr($_POST['loop']);
        $html= '';
        if(!empty($variations)){
            $get_url     = get_permalink($variations);
            $html        = '';
            $modfied_url = $get_url .'&varid='.$variations;
            $attr_color       = get_post_meta($variations , 'attribute_color' ,true);
            $current__array = dongtrader_ajax_helper(dongtrader_variable_color_to_rgb_color($attr_color),$modfied_url); 
            if($current__array){
                $update_data = json_encode($current__array);
                update_post_meta($variations, 'variable_product_qr_data', esc_attr($update_data));
                $html .= '<div data-color="'.$attr_color.'" id="dong-qr-components'.$loop.'" class="dong-qr-components dong-qr-components-var">';
                $html.= '<div class="qr-img-container-var">';
				//qr image
				$html.= '<img src="' . $current__array['qr_image_url'] . '' . '" alt="" width="100" height="100">';
				$html.= '</div>';
				//url copp
				$html.= '<div class="qr-urlbtn-container-var">';
				$html.= '<button data-url="'.$current__array['qr_image_url'] . '" class="button-primary button-large url-copy" >Copy QR URL</button>';
				//remover
				$html.= '<button data-index="'.$loop.'" id="variable_product_qr_data'.$loop.'" data-meta="variable_product_qr_data" data-remove="'.$variations. '" class="button-primary button-large qr-remover"  style="margin-left:10px" >Remove</button>';
				$html.= '</div>';
				//hidden field
				$html.= '<input data-id="' . esc_attr($productnum) . '" type="hidden" name ="variable_product_qr_data" value="' . esc_attr($update_data) . '">';
                $html .= '</div>';
                
            }
            $resp['success'] = true;
            $resp['template'] =$html;
        }
    }
 
    echo json_encode($resp);
    wp_die();
}

// Remove functionality for qr codes
add_action( 'wp_ajax_dongtrader_delete_qr_fields', 'dongtrader_delete_qr_fields' );
function dongtrader_delete_qr_fields() {
    $variation_id = esc_attr($_POST['itemID']);
    $variation_meta_key = esc_attr($_POST['metakey']);
    $ajax_values = array( 'resp' => false,'html' => false);
    if(!empty($variation_id) && !empty($variation_meta_key)){
        $status = delete_post_meta( $variation_id,  $variation_meta_key);
        if($status) {
            $ajax_values['status'] = true;
            $ajax_values['html'] = true;
        }

    }
    wp_send_json($ajax_values);
    wp_die();
}


/*Create database tables where we can save api response details*/
function dongtrader_create_dbtable() {

	global $wpdb;
    $table_name = $wpdb->prefix . 'manage_users_gf';
        $charset_collate = $wpdb->get_charset_collate();
        $sql="CREATE TABLE $table_name ( 
            id INT NOT NULL AUTO_INCREMENT ,
            gf_person_id INT NOT NULL ,
            gf_role_id INT NOT NULL , 
            gf_role_assigned VARCHAR(255) NOT NULL ,
            gf_name VARCHAR(255) NOT NULL ,
            gf_circle_name VARCHAR(255) NOT NULL ,
            created_at DATETIME(5) NOT NULL,
            user_id INT NOT NULL , PRIMARY KEY (id)) $charset_collate;";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

}
add_action( 'plugins_loaded', 'dongtrader_create_dbtable');

function dongtrader_divide_product_price($price,$latestmembs){
        if(!$price) return;

        //Send 40% to site owner
        $forty_percent_to_owner = $price * 40/100;

        //remaining prices after deduction by sending to site owner

        $deducted_price = $price-$forty_percent_to_owner;

        //Circle lead Amount

        $for_circle_lead = '';

        //distribute dedducted price among the 5 members of the circle

        $divided_price =  $deducted_price/$latestmembs;

        return [
            'siteowner-amt'=> $forty_percent_to_owner,
            'members-amt'=> $divided_price,
        ];
}

// add_action('wp_footer', function(){
//     // $attr_colora = dongtrader_divide_product_price(100,5);
//     // var_dump($attr_colora);

//     $meta =  get_post_meta(1319,'test_order',true);
//     var_dump($meta);

// });


/**
 * Step 1 : Create a database table where we can save circles each circle will have 5 members each(https://i.imgur.com/i5dECgu.png).
 * Step 2 : Save user details from response received from glassfrog api to the above table
 * Step 3 : Create meta on products for variations and simple products so that user can be assigned to specific roles  
 */
//add_action( 'user_register', 'dongtrader_user_registration_hook', 10, 1 );

add_action('woocommerce_created_customer', 'dongtrader_user_registration_hook', 10, 3);

function dongtrader_user_registration_hook($customer_id, $new_customer_data, $password_generated) {

    global $wpdb;
    $table_name = $wpdb->prefix . 'manage_users_gf';
    $user_info = get_userdata($customer_id);
    $username = $user_info->user_login;
    $first_name = $user_info->first_name;
    $last_name = $user_info->last_name;
    $full_name = $first_name . ' ' . $last_name;
    $email = $user_info->user_email;
    $str ='{"people": [{
        "name": "'.$full_name.'",
        "email": "'.$email.'",
        "external_id": "'.$customer_id.'",
        "tag_names": ["tag 1", "tag 2"]
        }]
        }';
    $samp= glassfrog_api_request('people', $str, "POST");

    
    //update_post_meta($_POST['product_id'],'test_order',wp_json_encode($new_customer_data));
    // print_r();
    if($samp && isset($samp)){
        $gf_id = $samp->people[0]->id;
        $gf_name = $samp->people[0]->name;
        $email = $samp->people[0]->email;
        $result = $wpdb->get_row( "SELECT gf_circle_name  FROM $table_name ORDER BY id DESC LIMIT 1;" );
        $last_circle_name = isset($result) ? $result->gf_circle_name : '1';
        $circle_count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE gf_circle_name = %s" ,$last_circle_name) );
        $new_circle_name = $circle_count < 5 ? $last_circle_name : $last_circle_name+1;
        $wpdb->query(
            $wpdb->prepare(
               "INSERT INTO $table_name
               (
                gf_person_id,
                gf_role_id,
                gf_role_assigned,
                gf_name,
                gf_circle_name ,
                created_at,
                user_id 
               )
               VALUES ( %d, %d, %s, %s, %s, %s, %d )",
               esc_attr($gf_id),
               1,
               'false',
               esc_attr($gf_name),
               esc_attr($new_circle_name),
               current_time('mysql', 1),
               $customer_id
               
            )
        );
    }
}


/**
 * Step 1: Give current user a role of affilaiate
 * Step 2 : Get the cost of current producct
 * 
 */