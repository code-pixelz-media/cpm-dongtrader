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
    if (!$apiname) {
        return;
    }

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
    if (!$checkFields) {
        return;
    }

    /* Get the API URL from the database. */
    $qrtiger_api_root_url = $qrtiger_creds['qrtiger-api-url'];
    /* Getting the API key from the database. */
    $qrtiger_api_key = $qrtiger_creds['qrtiger-api-key'];
    /* Concatenating the API root URL with the endpoint. */
    $build_url = $qrtiger_api_root_url . $endpoint;
    /* A default array. */

    /* Taking the default array and merging it with the  array. */
    //$body = wp_json_encode(wp_parse_args($qrtiger_defaults, $bodyParams));
    $body = wp_json_encode($bodyParams);
    /* Setting the options for the request. */
    $options = [
        'body' => $method == "POST" ? $body : '',
        'headers' => [
            'Authorization' => 'Bearer ' . $qrtiger_api_key,
            'Content-Type' => 'application/json',
        ],
        'timeout' => 30,

    ];

    // $build_url = 'https://stoplight.io/mocks/qrtiger/qrtiger-api/7801905';

    /* A ternary operator to check get or post parameter and use functions accordingly*/
    $response_received = $method == 'POST' ? wp_remote_post($build_url, $options) : wp_remote_get($build_url, $options);
    /* Get the response code from the response received. */
    $response_status = wp_remote_retrieve_response_code($response_received);
    /* Checking if the response status is 200 or not. If it is 200 then it will return the body of the response. */
    $response_body = $response_status == '200' ? wp_remote_retrieve_body($response_received) : false;
    /* Checking if the response body is not empty and then decoding the response body. */
    $response_object = $response_body ? json_decode($response_body) : false;

    $resp = $response_object->status != 403 ? $response_object : false;

    return $resp;
}

/* A function that is used to make the Glassfrog API requests. */
function glassfrog_api_request($endpoint = '', $str = '', $method = "GET")
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
    if (!$checkFields) {
        return;
    }

    /* Build Url for api request */
    $build_url = $gf_api_url . $endpoint;
    /* Params For API requests */
    $options = [
        'body' => $method == "POST" ? $str : '',
        'headers' => [
            'X-Auth-Token' => $gf_api_key,
            'Content-Type' => 'application/json',

        ],

    ];

    /* A ternary operator to check get or post parameter and use functions accordingly*/
    $response_received = $method == 'POST' ? wp_remote_post($build_url, $options) : wp_remote_get($build_url, $options);
    /* Get the response code from the response received. */
    $response_status = wp_remote_retrieve_response_code($response_received);
    /* Checking if the response status is 200 or not. If it is 200 then it will return the body of the response. */
    $response_body = $response_status == '200' ? wp_remote_retrieve_body($response_received) : false;
    /* Checking if the response body is not empty and then decoding the response body. */
    $response_object = $response_body ? json_decode($response_body) : false;

    return $response_object;
}

/*This function is used to make ajax call on woocommerce my account page.
 *It calls qrtiger api and fetches qrcodes data
 */
add_action('wp_ajax_dongtrader_generate_qr2', 'dongtrader_generate_qr2');

function dongtrader_generate_qr2()
{
    $qr_size = sanitize_text_field($_POST['qrsize']);
    $qr_url = sanitize_url($_POST['qrurl']);
    $qr_color = sanitize_text_field($_POST['qrcolor']);
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
            "qrCategory" => "url",
        ];
        $qrtiger_api_call = qrtiger_api_request('/api/campaign/', $qrtiger_array, 'POST');

        if ($qrtiger_api_call) {
            $notify_to_js['apistatus'] = true;
            $current_dong_qr_array = array(
                'created_by' => $dong_user_id,
                'qr_image_url' => $qrtiger_api_call->data->qrImage,
                'created_at' => $qrtiger_api_call->data->createdAt,
                'updated_at' => $qrtiger_api_call->data->updatedAt,
                'qr_id' => $qrtiger_api_call->data->qrId,
            );

            $old_dong_qr_array = get_option('dong_user_qr_values');
            $prev_value_check = !empty($old_dong_qr_array) ? true : false;
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

    $index = esc_attr($_POST['qrIndex']);
    $dong_qr_array = get_option('dong_user_qr_values');
    $resp_array = array('success' => false, 'd' => $dong_qr_array, 'i' => $index);

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

function dongtrader_ajax_test_helper($color = '', $url = '', $size = '')
{

    return array(
        'qr_image_url' => 'https://qrtiger.com/qr/OXX3.png',
        'created_at' => 'date',
        'updated_at' => 'date',
        'qr_id' => '0XX3',
    );
}

function dongtrader_ajax_helper($color, $url, $size = '500')
{

    $current_dong_qr_array = true;
    $qrtiger_array = [
        "qr" => [
            "size" => $size,
            "colorDark" => $color,
            "transparentBkg" => false,
        ],
        "qrUrl" => $url,
        "qrType" => "qr2",
        "qrCategory" => "url",
    ];
    $qrtiger_api_call = qrtiger_api_request('/api/campaign/', $qrtiger_array, 'POST');

    if ($qrtiger_api_call) {
        $current_dong_qr_array = array(
            "qr_image_url" => $qrtiger_api_call->data->qrImage,
            "created_at" => $qrtiger_api_call->data->createdAt,
            "updated_at" => $qrtiger_api_call->data->updatedAt,
            "qr_id" => $qrtiger_api_call->data->qrId,
        );
    } else {

        $current_dong_qr_array = false;
    }

    return $current_dong_qr_array;
}

add_action('wp_ajax_dongtrader_meta_qr_generator', 'dongtrader_meta_qr_generator');

function dongtrader_meta_qr_generator()
{

    $intiator = esc_attr($_POST['intiator']);
    $productnum = esc_attr($_POST['productnums']);
    $product = wc_get_product($productnum);
    $product_url = get_permalink($productnum);
    $check_out_Url = get_permalink($productnum) . '?add=1';

    //add your datas here
    $resp = array(
        'success' => false,
        'template' => '',
        'pid' => $productnum,
        'initiator' => $intiator,
        "purl" => $product_url,
    );

    //for products qr code
    if ($intiator == '_product_qr_codes') {

        $current_data = dongtrader_ajax_helper('rgb(87, 3, 48)', $product_url);
        if (!empty($current_data)) {
            $update_data = json_encode($current_data);
            $resp['success'] = true;
            $resp['template'] = '<div id="" class="dong-qr-components">
            <img src="' . $current_data['qr_image_url'] . '" alt="" width="200" height="200">
            <button data-url= "' . $current_data['qr_image_url'] . '" class="button button-primary button-large url-copy" >Copy QR URL</button>
            <input type= "hidden" data-id="' . esc_attr($productnum) . '"  name ="_product_qr_codes" value="' . esc_attr($update_data) . '">
            <button data-meta="_product_qr_codes" data-remove="' . $productnum . '" class="button-primary button-large qr-remover" style="" >Remove</button></div>';

            update_post_meta($productnum, '_product_qr_codes', $update_data);
        }
        //for direct checkout
    } elseif ($intiator == '_product-qr-direct-checkouts') {
        //call api helper function
        $current_data = dongtrader_ajax_helper('rgb(0, 102, 204)', $check_out_Url);
        if (!empty($current_data)) {
            $update_data = json_encode($current_data);
            update_post_meta($productnum, '_product-qr-direct-checkouts', $update_data);
            $resp['success'] = true;
            $resp['template'] = '<div id="" class="dong-qr-components">
            <img src="' . $current_data['qr_image_url'] . '" alt="" width="200" height="200">
            <button data-url= "' . $current_data['qr_image_url'] . '" class="button button-primary button-large url-copy" >Copy QR URL</button>
            <input type= "hidden" data-id="' . esc_attr($productnum) . '"  name ="_product-qr-direct-checkouts" value="' . esc_attr($update_data) . '">
            <button data-meta="_product_qr_codes" data-remove="' . $productnum . '" class="button-primary button-large qr-remover" style="" >Remove</button></div>';
        }
        //for variable products
    } elseif ($intiator == '_product-qr-variabled') {
        $variations = esc_attr($_POST['variations']);
        $loop = esc_attr($_POST['loop']);
        $html = '';
        if (!empty($variations)) {
            $get_url = get_permalink($variations);
            $html = '';
            $modfied_url = $get_url . '&varid=' . $variations;
            $attr_color = get_post_meta($variations, 'attribute_color', true);
            $current__array = dongtrader_ajax_helper(dongtrader_variable_color_to_rgb_color($attr_color), $modfied_url);
            if ($current__array) {
                $update_data = json_encode($current__array);
                update_post_meta($variations, 'variable_product_qr_data', esc_attr($update_data));
                $html .= '<div data-color="' . $attr_color . '" id="dong-qr-components' . $loop . '" class="dong-qr-components dong-qr-components-var">';
                $html .= '<div class="qr-img-container-var">';
                //qr image
                $html .= '<img src="' . $current__array['qr_image_url'] . '' . '" alt="" width="100" height="100">';
                $html .= '</div>';
                //url copp
                $html .= '<div class="qr-urlbtn-container-var">';
                $html .= '<button data-url="' . $current__array['qr_image_url'] . '" class="button-primary button-large url-copy" >Copy QR URL</button>';
                //remover
                $html .= '<button data-index="' . $loop . '" id="variable_product_qr_data' . $loop . '" data-meta="variable_product_qr_data" data-remove="' . $variations . '" class="button-primary button-large qr-remover"  style="margin-left:10px" >Remove</button>';
                $html .= '</div>';
                //hidden field
                $html .= '<input data-id="' . esc_attr($productnum) . '" type="hidden" name ="variable_product_qr_data" value="' . esc_attr($update_data) . '">';
                $html .= '</div>';
            }
            $resp['success'] = true;
            $resp['template'] = $html;
        }
    }

    echo json_encode($resp);
    wp_die();
}

// Remove functionality for qr codes
add_action('wp_ajax_dongtrader_delete_qr_fields', 'dongtrader_delete_qr_fields');
function dongtrader_delete_qr_fields()
{
    $variation_id = esc_attr($_POST['itemID']);
    $variation_meta_key = esc_attr($_POST['metakey']);
    $ajax_values = array('resp' => false, 'html' => false);
    if (!empty($variation_id) && !empty($variation_meta_key)) {
        $status = delete_post_meta($variation_id, $variation_meta_key);
        if ($status) {
            $ajax_values['status'] = true;
            $ajax_values['html'] = true;
        }
    }
    wp_send_json($ajax_values);
    wp_die();
}


/* add custom field to save user role on user profile */
add_action('show_user_profile', 'dong_show_user_role');
add_action('edit_user_profile', 'dong_show_user_role');
function dong_show_user_role($user)
{

    $dong_user_role = get_user_meta($user->ID, 'dong_user_role', true);
    /*   echo $dong_user_role . '-dong user role'; */

    ?>
    <table class="form-table">
        <tr>
            <th><label for="city">Dong User Role</label></th>
            <td>
                <select name="dong_user_role" id="">
                    <option value="Planning" <?php if ($dong_user_role == "Planning") {
        echo 'selected="selected"';
    }
    ?>>Planning (Purple)</option>
                    <option value="Budget" <?php if ($dong_user_role == "Budget") {
        echo 'selected="selected"';
    }
    ?>>Budget (Orange)</option>
                    <option value="Media" <?php if ($dong_user_role == "Media") {
        echo 'selected="selected"';
    }
    ?>>Media (Red)</option>
                    <option value="Distribution" <?php if ($dong_user_role == "Distribution") {
        echo 'selected="selected"';
    }
    ?>>Distribution (Green)</option>
                    <option value="Membership" <?php if ($dong_user_role == "Membership") {
        echo 'selected="selected"';
    }
    ?>>Membership (Blue)</option>
                </select>
                </select>
            </td>
        </tr>
    </table>
<?php

}

add_action('personal_options_update', 'dong_user_role_save_profile_fields');
add_action('edit_user_profile_update', 'dong_user_role_save_profile_fields');

function dong_user_role_save_profile_fields($user_id)
{

    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'update-user_' . $user_id)) {
        return;
    }

    if (!current_user_can('edit_user', $user_id)) {
        return;
    }

    update_user_meta($user_id, 'dong_user_role', sanitize_text_field($_POST['dong_user_role']));
}

/* Functions to set dong uer role */

function dong_set_user_role($user_id, $product_id)
{
    $get_varition_color = get_post_meta($product_id, 'attribute_pa_color', true);
    $get_color_role = dongtrader_get_product_color($get_varition_color);
    update_user_meta($user_id, 'dong_user_role', $get_color_role);
}

function dongtrader_get_product_color($role)
{
    switch ($role) {
        case 'orange':
            $role = 'Budget';
            break;
        case 'purple':
            $role = 'Planning';
            break;
        case 'red':
            $role = 'Media';
            break;
        case 'blue':
            $role = 'Membership';
            break;
        case 'green':
            $role = 'Distribution';
            break;
        default:
            $role = 'Membership';
    }

    return $role;
}

/**
 * It adds a new schedule to the list of schedules that WordPress uses when scheduling events
 * 
 * @param @schedules The name of the schedule.
 * 
 * @return @the  array.
 */
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


/*Create database tables where we can save api response details*/
function dongtrader_create_dbtable()
{

    global $wpdb;
    $table_name = $wpdb->prefix . 'manage_users_gf';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
            id INT NOT NULL AUTO_INCREMENT ,
            product_id INT ,
            order_id INT ,
            gf_person_id INT NOT NULL ,
            gf_role_id INT NOT NULL ,
            gf_role_assigned VARCHAR(255) NOT NULL ,
            gf_name VARCHAR(255) NOT NULL ,
            gf_circle_name VARCHAR(255) NOT NULL ,
            created_at DATETIME(5) NOT NULL,
            in_circle INT NOT NULL,
            user_id INT NOT NULL , PRIMARY KEY (id)) $charset_collate;";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}
add_action('plugins_loaded', 'dongtrader_create_dbtable');

/**
 * Step 1 : Create a database table where we can save circles each circle will have 5 members each(https://i.imgur.com/i5dECgu.png).
 * Step 2 : Save user details from response received from glassfrog api to the above table
 * Step 3 : Create meta on products for variations and simple products so that user can be assigned to specific roles
 */
function dongtrader_user_registration_hook($customer_id , $p_id , $oid)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'manage_users_gf';
    $user_info = get_userdata($customer_id);
    $username = $user_info->user_login;
    $first_name = $user_info->first_name;
    $last_name = $user_info->last_name;
    $full_name = $first_name . ' ' . $last_name;
    $email = $user_info->user_email;
    $str = '{"people": [{
        "name": "' . $user_info->display_name . '",
        "email": "' . $email . '",
        "external_id": "' . $customer_id . '",
        "tag_names": ["tag 1", "tag 2"]
        }]
        }';
    $samp = glassfrog_api_request('people', $str, "POST");
    if ($samp && isset($samp)) {
        $gf_id = $samp->people[0]->id;
        $gf_name = $samp->people[0]->name;
        $result = $wpdb->get_row("SELECT gf_circle_name  FROM $table_name ORDER BY id DESC LIMIT 1;");
        $last_circle_name = isset($result) ? $result->gf_circle_name : '1';
        $circle_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE gf_circle_name = %s", $last_circle_name));
        $new_circle_name = $circle_count < 5 ? $last_circle_name : $last_circle_name + 1;
        $in_circle = 1;
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO $table_name
               (
                gf_person_id,
                product_id,
                order_id,
                gf_role_id,
                gf_role_assigned,
                gf_name,
                gf_circle_name ,
                created_at,
                in_circle,
                user_id
               )
               VALUES ( %d,%d,%d,%d,%s,%s,%s,%s,%d,%d )",
                esc_attr($gf_id),
                $p_id,
                $oid,
                1,
                'false',
                esc_attr($gf_name),
                esc_attr($new_circle_name),
                current_time('mysql', 1),
                $in_circle,
                $customer_id
    
            )
        );
    }
}

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
                       $update_query = $wpdb->prepare("UPDATE $table_name SET in_circle = %d WHERE user_id = %d", 1, $uid);
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

