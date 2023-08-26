<?php
use const Automattic\Jetpack\Extensions\Story\IMAGE_BREAKPOINTS;

/**
 * It returns the API credentials for the API name passed to it
 *
 * @param [apiname] The name of the API you're using.
 *
 * @return [$api_utils] API credentials for the API name passed in.
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

function qrtiger_upload_logo()
{

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
    $qr_logo_url = plugin_dir_url(__FILE__) . 'assets/img/currency.png';
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
                "logo" => 'currency.png',
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
        case 'orange':
            $color = 'rgb(255, 51, 0)';
            break;
        case 'purple':
            $color = 'rgb(245,66,245)';
            break;
        case 'red':
            $color = 'rgb(255, 0, 0)';
            break;

        case 'blue':
            $color = 'rgb(36, 36, 143)';
            break;

        case 'green':
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
    $logo = "https://smallstreet.app/wp-content/uploads/2023/03/3D2-1-1.png";
    $current_dong_qr_array = true;
    $qrtiger_array = [
        "qr" => [
            "logo" => $logo,
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
            $attr_color = get_post_meta($variations, 'attribute_pa_color', true);
            //echo $attr_color . '-color';
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

//remove functionality for qr codes from settings page
// Remove functionality for qr codes
add_action('wp_ajax_dongtrader_delete_qr_items_settingspage', 'dongtrader_delete_qr_items_settingspage');
function dongtrader_delete_qr_items_settingspage()
{

    $dong_qr_array = get_option('dong_user_qr_values');
    $index = (int) esc_attr($_POST['index']);
    $ajax_values = array('resp' => false, 'reload' => false);
    if ($index >= 0) {

        unset($dong_qr_array[$index]);
        array_values($dong_qr_array);
        update_option('dong_user_qr_values', $dong_qr_array);
        $new_qr_array = get_option('dong_user_qr_values');

        $reload = count($new_qr_array) == 0 ? true : false;

        $ajax_values = array('resp' => true, 'reload' => $reload);

    }

    wp_send_json($ajax_values);

    wp_die();

}

/*Create database tables where we can save api response details*/
function dongtrader_create_dbtable()
{

    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    /* create table to store custom order */
    $order_table_name = $wpdb->prefix . 'dong_order_export_table';
    if ($wpdb->get_var("SHOW TABLES LIKE '{$order_table_name}'") != $order_table_name):
        $export_sql = "CREATE TABLE $order_table_name (
            id INT NOT NULL AUTO_INCREMENT ,
            customer_email VARCHAR(255) NOT NULL ,
            customer_first_name VARCHAR(255) NOT NULL ,
            customer_last_name VARCHAR(255) NOT NULL ,
            customer_phone VARCHAR(255) NOT NULL ,
            customer_country VARCHAR(255) NOT NULL ,
            customer_state VARCHAR(255) NOT NULL ,
            customer_address VARCHAR(255) NOT NULL ,
            customer_postcode VARCHAR(255) NOT NULL ,
            customer_city VARCHAR(255) NOT NULL ,
            product_id INT NOT NULL ,
            product_varition_id INT NOT NULL ,
            affilate_user_id INT NOT NULL ,
            created_at VARCHAR(255) NOT NULL,
            PRIMARY KEY (id)) $charset_collate;";
        dbDelta($export_sql);
    endif;

    // // Create users glassfrog details table
    // $user_details_table = $wpdb->prefix . 'glassfrog_user_data';
    // if ($wpdb->get_var("SHOW TABLES LIKE '{$user_details_table}'") != $user_details_table):
    //     $glassfrog_user_sql = "CREATE TABLE $user_details_table (
    //         id INT NOT NULL AUTO_INCREMENT ,
    //         user_id INT ,
    //         gf_person_id VARCHAR(255) ,
    //         circle_id	int(11),
    //         gf_name VARCHAR(255) ,
    //         in_glassfrog VARCHAR(255) NOT NULL ,
    //         all_orders VARCHAR(255) NOT NULL ,
    //         group_id INT,
    //         upline_id INT,
    //         PRIMARY KEY (id),
    //         INDEX (user_id),
    //         FOREIGN KEY (upline_id) REFERENCES {$user_details_table}(user_id)
    //         ) $charset_collate;";
    //     dbDelta($glassfrog_user_sql);
    // endif;




    $release_group_profit = $wpdb->prefix . 'release_groups_profit';
    if ($wpdb->get_var("SHOW TABLES LIKE '{$release_group_profit}'") != $release_group_profit) {
        $release_group_profit_query = "CREATE TABLE {$release_group_profit} (
            id INT NOT NULL AUTO_INCREMENT,
            release_date DATETIME,
            release_amount INT NOT NULL,
            release_note VARCHAR(255) NOT NULL,
            group_id INT,
            PRIMARY KEY (id)
        ) $charset_collate;";
        dbDelta($release_group_profit_query);
    }

    // Adding new database structure for mlm

    // M.l.m users table
    $mega_mlm_users =  $wpdb->prefix . 'mega_mlm_customers';

    $group_details_table = $wpdb->prefix . 'mega_mlm_groups';

    if ($wpdb->get_var("SHOW TABLES LIKE '{$mega_mlm_users}'") != $mega_mlm_users) {

        $mega_mlm_users_query = "CREATE TABLE {$mega_mlm_users} (
            id INT NOT NULL AUTO_INCREMENT,
            user_id INT,
            upline_id INT,
            customer_group_id INT(11),
            glassfrog_person_id BIGINT,
            person_name VARCHAR(255),
            circle_id INT(11),
            created_date TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            user_status INT(11) NOT NULL,
            PRIMARY KEY (id),
            UNIQUE(user_id),
            INDEX(user_id),
            FOREIGN KEY (customer_group_id) REFERENCES {$group_details_table}(group_id)
        ) $charset_collate;";
                
        dbDelta($mega_mlm_users_query);
    }

        //mlm tree data

        if ($wpdb->get_var("SHOW TABLES LIKE '{$group_details_table}'") != $group_details_table):
            $group_details_sql = "CREATE TABLE $group_details_table (
                group_id INT NOT NULL AUTO_INCREMENT,
                group_members VARCHAR(255) NOT NULL,
                circle_id INT NOT NULL,
                circle_name VARCHAR(255) NOT NULL,
                created_date DATETIME NOT NULL,
                group_leader INT NOT NULL,
                leader_since DATETIME NOT NULL,
                leadership_expires DATETIME NOT NULL,
                distribution_status TINYINT(2) NOT NULL,
                total_group_profit VARCHAR(255),
                PRIMARY KEY (group_id)
            ) $charset_collate;"; 
            dbDelta($group_details_sql);
        endif;

    //downline table
    // $mega_mlm_downline =  $wpdb->prefix . 'mega_mlm_downline';

    // if ($wpdb->get_var("SHOW TABLES LIKE '{$mega_mlm_downline}'") != $mega_mlm_downline) {

    //     $mega_mlm_downline_query = "CREATE TABLE {$mega_mlm_downline} (
    //         downline_id INT NOT NULL AUTO_INCREMENT,
    //         user_id INT,
    //         downline_user_id INT,
    //         user_level INT, 
    //         PRIMARY KEY (downline_id),
    //         INDEX (user_id),
    //         INDEX (downline_user_id),
    //         FOREIGN KEY (user_id) REFERENCES {$mega_mlm_users}(user_id),
    //         FOREIGN KEY (downline_user_id) REFERENCES {$mega_mlm_users}(user_id)
    //     ) $charset_collate;";
        
    //     dbDelta($mega_mlm_downline_query);
    // }

    $mega_mlm_sales = $wpdb->prefix . 'mega_mlm_purchases';
    if ($wpdb->get_var("SHOW TABLES LIKE '{$mega_mlm_sales}'") != $mega_mlm_sales) {
        $mega_mlm_sales_query = "CREATE TABLE {$mega_mlm_sales} (
            sale_id INT NOT NULL AUTO_INCREMENT,
            sponsor_id INT,
            customer_id INT,
            order_id INT,
            product_id INT,
            product_name varchar(255),
            product_price INT,
            allocation_status TINYINT(2) NOT NULL,
            PRIMARY KEY (sale_id),
            FOREIGN KEY (customer_id) REFERENCES {$mega_mlm_users}(user_id),
            FOREIGN KEY (sponsor_id) REFERENCES {$mega_mlm_users}(user_id)
        ) $charset_collate;";
       
        dbDelta($mega_mlm_sales_query);
    }

}
add_action('plugin_loaded', 'dongtrader_create_dbtable');

function dongtrader_user_registration_hook($customer_id, $p_id, $oid)
{

    //global database variable
    global $wpdb;

    //custom user table from our database
    $table_name = $wpdb->prefix . 'glassfrog_user_data';

    //custom group table
    $group_table_name = $wpdb->prefix . 'glassfrog_group_data';

    // Downline table 
    $downline_table = $wpdb->prefix . 'mega_mlm_downline';

    //check if a person already exists in a database
    $check_persons = $wpdb->get_row($wpdb->prepare("SELECT all_orders , group_id FROM $table_name WHERE user_id = %d", $customer_id), ARRAY_A);

    if (!empty($check_persons)) {

        if ($check_persons['group_id'] != 0) {
            //query to update group distribution status to update_required when the person has already brought new product after distribution from cron job
            $wpdb->query($wpdb->prepare("UPDATE $group_table_name SET distribution_status = 'update_required' WHERE group_id = %d", (int) $check_persons['group_id']));

        } elseif ($check_persons['group_id'] == 0) {

            $unserialized_orders = unserialize($check_persons['all_orders']);

            //check if order id is already on the list and append new order id to it
            if (!in_array($oid, $unserialized_orders)) {

                $unserialized_orders[] = $oid;

                //again serilize data to store in db
                $new_serilized_orders = serialize($unserialized_orders);

                //preapre and update serialized data
                $wpdb->query($wpdb->prepare("UPDATE $table_name SET all_orders = %s WHERE user_id = %d", $new_serilized_orders, $customer_id));

            }

        }

    } else {
        // Get the product object
        $product = wc_get_product($p_id);

        //get parent id if product is variable
        $parent_id = $product->is_type('variation') ? $product->get_parent_id() : $p_id;

        //for this case orders datas doesnt exists on our custom database table so we need to call the glassfrog api and insert data accordingly
        $gf_checkbox = get_post_meta($parent_id, '_glassfrog_checkbox', true);

        //bool to check meta
        $gf_check = $gf_checkbox == 'on' ? true : false;

        if ($gf_check) {
            //get user object
            $user_info = get_userdata($customer_id);

            //order object
            $orderobj = new WC_Order($oid);

            // get sponsor id 
            $sponsor_id =  $orderobj->get_meta('mega_affid');

            //refferal
            $refferal =  !empty($sponsor_id) ? (int) $sponsor_id : null;

            //get user email
            $email = $user_info->user_email;

            //api request string
            $str = '{"people": [{
                "name": "' . $user_info->display_name . '",
                "email": "' . $email . '",
                "external_id": "' . $customer_id . '",
                "tag_names": ["tag 1", "tag 2"]
                }]
                }';

            //api call
            $samp = glassfrog_api_request('people', $str, "POST");

            if ($samp && isset($samp)):

                //glassfrog id from the api
                $gf_id = $samp->people[0]->id;

                //glassfrog persons  name
                $gf_name = $samp->people[0]->name;

                $all_orders = serialize(array($oid));

                $wpdb->query("SET FOREIGN_KEY_CHECKS = 0");
                
                $wpdb->query(
                    $wpdb->prepare(
                        "INSERT INTO $table_name
                        (
                            user_id,
                            gf_person_id,
                            gf_name,
                            in_glassfrog,
                            all_orders,
                            group_id,
                            upline_id

                        )
                        VALUES ( %d,%d,%s,%d,%s,%d,%d)",
                        $customer_id, // d
                        $gf_id, // d
                        $gf_name, // s
                        0, // d
                        $all_orders, // s
                        0,// d
                        $refferal //d

                    )
                );

                if($refferal) {
                    $wpdb->query(
                        $wpdb->prepare(
                            "INSERT INTO $downline_table
                            (
                                user_id,
                                downline_user_id
                            )
                            VALUES ( %d,%d)",
                            $refferal,
                            $customer_id    
                        )
                    );
    
                }

                $wpdb->query("SET FOREIGN_KEY_CHECKS = 1");

            endif;
        } else {

            //when order need not to be sent to glass frog where and how to manage it??

        }

    }

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

/* dong order export form */

/**
 * It creates a form and inserts the data into the database.
 */
function dongtraders_order_export_form()
{
    ?>
    <form action="" method="POST" class="order-export-form">
        <!--   <div class="dong-notify-msg">

        </div> -->
        <div class="form-group">
            <label for="">Customer Email</label>
            <div class="form-control-wrap">
                <input name="customer-email" class="form-control customer-email" type="email" onfocus="this.placeholder=''" onblur="this.placeholder='Customer Email'" required="" value="">
            </div>
        </div>

        <div class="form-group">
            <label for="">Customer First Name</label>
            <div class="form-control-wrap">
                <input name="customer-first-name" class="form-control customer-first-name" type="text" onfocus="this.placeholder=''" onblur="this.placeholder='Customer First Name'" required="" value="">
            </div>
        </div>

        <div class="form-group">
            <label for="">Customer Last Name</label>
            <div class="form-control-wrap">
                <input name="customer-last-name" class="form-control customer-last-name" type="text" onfocus="this.placeholder=''" onblur="this.placeholder='Customer Last Name'" required="" value="">
            </div>
        </div>

        <div class="form-group">
            <label for="">Customer Phone</label>
            <div class="form-control-wrap">
                <input name="customer-phone" class="form-control customer-phone" type="number" onfocus="this.placeholder=''" onblur="this.placeholder='Customer Phone'" required="" value="">
            </div>
        </div>


        <div class="form-group">
            <label for="">Customer Country</label>
            <div class="form-control-wrap">

                <select onchange="print_state('state',this.selectedIndex);" id="country" name="customer-country" class="form-control"></select>
            </div>
        </div>
        <div class="form-group">
            <label for="">Customer State</label>
            <div class="form-control-wrap">
                <select name="customer-state" id="state" class="form-control"></select>
            </div>
        </div>
        <div class="form-group">
            <label for="">Customer Address</label>
            <div class="form-control-wrap">
                <input name="customer-address" class="form-control customer-address" type="text" onfocus="this.placeholder=''" onblur="this.placeholder='Customer Address'" required="" value="">
            </div>
        </div>
        <div class="form-group">
            <label for="">Customer Postcode</label>
            <div class="form-control-wrap">
                <input name="customer-postcode" class="form-control customer-postcode" type="text" onfocus="this.placeholder=''" onblur="this.placeholder='Customer Postcode'" required="" value="">
            </div>
        </div>
        <div class="form-group">
            <label for="">Customer City</label>
            <div class="form-control-wrap">
                <input name="customer-city" class="form-control customer-city" type="text" onfocus="this.placeholder=''" onblur="this.placeholder='Customer City'" required="" value="">
            </div>
        </div>

        <div class=" form-group">
            <label for="">Select Product</label>
            <div class="form-control-wrap">
                <select name="select-product" id="" class="form-control select-product" required="">
                    <?php
                        echo '<option value="">Select Product</option>';
                        $args = array(
                            'post_type' => 'product',
                            'posts_per_page' => -1,
                            'status' => 'published',
                        );

                        $loop = new WP_Query($args);
                        //var_dump($loop);
                        $product_ids = array();
                        while ($loop->have_posts()): $loop->the_post();
                            $product = new WC_Product_Variable(get_the_ID());
                            $terms = get_the_terms($product->get_id(), 'product_type');
                            $product_type = (!empty($terms)) ? sanitize_title(current($terms)->name) : 'simple';

                            echo '<option data-productType="' . $product_type . '" value="' . get_the_ID() . '">' . get_the_title() . '</option>';
                            $product_ids[] = $product->get_id();
                        endwhile;

                        wp_reset_query();

                    ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <!--  <label for="">Select Variation Product</label> -->
            <div class="form-control-wrap">
                <?php
                    foreach ($product_ids as $product_id) {
                            $product = new WC_Product_Variable($product_id);
                            $terms = get_the_terms($product_id, 'product_type');
                            $product_type = (!empty($terms)) ? sanitize_title(current($terms)->name) : 'simple';

                            if ($product_type == 'variable') {
                                # code...
                                $product = wc_get_product($product_id);
                                $current_products = $product->get_children();
                                ?>
                                            <div id="varition-<?php echo $product_id; ?>" class="export_variation_product_hide">
                                                <label for="">Select Variation Product</label>
                                                <div class="form-control-wrap">
                                                    <select name="variation-product[]" class="form-control variation-product">
                                                        <?php
                                                        echo '<option value="0">Choose Variation Product</option>';
                                                        foreach ($current_products as $current_product) {
                                                            $variation = wc_get_product($current_product);
                                                            $varition_name = $variation->get_name();
                                                            echo '<option value="' . $current_product . '">' . $varition_name . '</option>';
                                                        }

                                                        ?>
                                                    </select>
                                                </div>
                                            </div>

                                    <?php

                            }
                        }

    ?>

            </div>
        </div>
        <div class="form-group">
            <?php $user_ID = get_current_user_id();?>
            <div class="form-control-wrap">
                <input type="hidden" name="affilate-user" class="form-control affilate-user" value="<?php echo $user_ID; ?>">
            </div>
        </div>
        <div class="form-group">
            <input class="cpm-btn submit real-button" type="submit" value="Add Custom Order" name="set-order_export">
        </div>
    </form>

    <?php
/* insert date to database table */
    $current_page = home_url($_SERVER['REQUEST_URI']);
    //echo $current_page;

    if (isset($_POST['set-order_export'])) {
        $customer_email = $_POST['customer-email'];
        $customer_first_name = $_POST['customer-first-name'];
        $customer_last_name = $_POST['customer-last-name'];
        $customer_phone = $_POST['customer-phone'];
        $customer_country = $_POST['customer-country'];
        $customer_state = $_POST['customer-state'];
        $customer_address = $_POST['customer-address'];
        $customer_postcode = $_POST['customer-postcode'];
        $customer_city = $_POST['customer-city'];
        $customer_product_id = $_POST['select-product'];
        $customer_affilate_user_final = $_POST['affilate-user'];

        $created_date = date("Y-m-d");
        $customer_varition_product_id = $_POST['variation-product'];

        $get_final_vartion_id = (array_filter($customer_varition_product_id));

        $v_id = '';
        foreach ($get_final_vartion_id as $get_final_vartion_ids) {

            $v_id .= $get_final_vartion_ids;
            # code...
        }
        $varition_product_id_final = $v_id;
        if (!empty($varition_product_id_final)) {

            $final_v_id = $varition_product_id_final;
        } else {
            $final_v_id = 0;
        }
        /* echo $customer_product_id . '-Affiliate User<br>';
        echo $final_v_id . '-Varition ID<br>';
        print_r($_POST); */

        global $wpdb;
        $order_table_name = $wpdb->prefix . 'dong_order_export_table';
        $wpdb->query(
            $order_insert = $wpdb->prepare(
                "INSERT INTO $order_table_name
               (
                customer_email,
                customer_first_name,
                customer_last_name,
                customer_phone,
                customer_country,
                customer_state,
                customer_address,
                customer_postcode,
                customer_city,
                product_id,
                product_varition_id,
                affilate_user_id,
                created_at
               )
               VALUES ( %s, %s, %s, %d, %s, %s, %s, %s, %s, %d, %d, %d, %s )",
                esc_attr($customer_email),
                esc_attr($customer_first_name),
                esc_attr($customer_last_name),
                esc_attr($customer_phone),
                esc_attr($customer_country),
                esc_attr($customer_state),
                esc_attr($customer_address),
                esc_attr($customer_postcode),
                esc_attr($customer_city),
                esc_attr($customer_product_id),
                esc_attr($final_v_id),
                esc_attr($customer_affilate_user_final),
                $created_date

            )
        );
        if ($order_insert) {
            echo '<div class="success-box">Affiliate Order Data inserted Sucessfully</div>';
        } else {
            echo '<div class="error-box">Order Data could not inserted ! Please Try again</div>';
        }
    }
}

/* dongtraders custom export order list */

function dongtraders_custom_order_created_list()
{
    global $wpdb;
    $order_table_name = $wpdb->prefix . 'dong_order_export_table';
    ?>

    <div class=" cpm-table-wrap">
        <div class="export-section">

            <form action="" id="export-csv-order">
                <span id="from">From</span>
                <input id="start-month" name="start_month" type="date" size="2" required>
                <span id="to">To</span>
                <input id="end-month" name="end_month" type="date" size="2" required>
                <select name="affilate_id" id="affilate_id">
                    <?php
$get_all_users = get_users();
    //var_dump($get_all_users);
    echo '<option value="">Select Affilate User</option>';
    foreach ($get_all_users as $get_all_user) {
        echo '<option value="' . $get_all_user->ID . '">' . $get_all_user->user_login . '</option>';
    }
    ?>
                </select>
                <button type="submit" class="button button-primary buttonload">Export CSV<i class="fa fa-spinner fa-spin export-loader"></i></button>
            </form>
        </div>
        <table id="qr-all-list">
            <thead>
                <tr>
                    <th>id</th>
                    <th>Date</th>
                    <th>Email</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Phone No.</th>
                    <th>Country</th>
                    <th>State</th>
                    <th>Address</th>
                    <th>City</th>
                    <th>Postcode</th>
                    <th>Product Id</th>
                    <th>Variation Id</th>
                    <th>Affilate User</th>
                    <th>Remove</th>

                </tr>
            </thead>
            <tbody>
                <?php

    $get_order_results = $wpdb->get_results("SELECT *  FROM $order_table_name ORDER BY id DESC;");

    //$get_url = home_url() . '/wp-admin/admin.php?page=dongtrader_api_settings';
    /* $current_page = ''; */
    if (!empty($get_order_results)) {
        foreach ($get_order_results as $export_order) {

            echo '
                     <tr>
                    <td>' . $export_order->id . '</td>
                     <td>' . $export_order->created_at . '</td>
                   <td>' . $export_order->customer_email . '</td>
                   <td>' . $export_order->customer_first_name . '</td>
                   <td>' . $export_order->customer_last_name . '</td>
                   <td>' . $export_order->customer_phone . '</td>
                   <td>' . $export_order->customer_address . '</td>
                   <td>' . $export_order->customer_country . '</td>
                   <td>' . $export_order->customer_state . '</td>
                   <td>' . $export_order->customer_city . '</td>
                   <td>' . $export_order->customer_postcode . '</td>
                   <td>' . $export_order->product_id . '</td>
                   <td>' . $export_order->product_varition_id . '</td>
                   <td>' . $export_order->affilate_user_id . '</td>
                 <td>
                 <form action="" method="post">
                        <input type="hidden" name="export-id" value="' . $export_order->id . '">
                       <button type="submit" name="delete-export" value="Delete" class="cpm-btn export-delete dashicons-before dashicons-trash"></button>
                        </form>
                        </td>
                </tr>
                ';
        }
    } else {
        echo '<div class="error-box">No Records Found</div>';
    }

    // Handle delete
    if (isset($_POST['delete-export'])) {

        $delete_id = (int) $_POST['export-id'];

        // Delete data in mysql from row that has this id
        $result = $wpdb->delete($order_table_name, array('id' => $delete_id));

        // if successfully deleted
        if ($result) {

            echo '<div class="success-box">Deleted order ID-> ' . $delete_id . ' Successfully</div>';
        } else {
            echo '<div class="error-box">Order Data could not Deleted ! Please Try again</div>';
        }
        /*  wp_redirect($current_page); */
    }
    ?>


            </tbody>
        </table>
    </div>
<?php
}

/*ajax function to run csv exporter*/

if (!function_exists('dong_custom_order_exporter_csv_files')) {
    function dong_custom_order_exporter_csv_files($post)
    {

        $get_start_date = $_POST['start_date'];
        $get_end_date = $_POST['end_date'];
        $get_affilate_user_id = $_POST['user_id'];

        global $wpdb;
        $get_table_name = $wpdb->prefix . 'dong_order_export_table';

        if (!empty($get_start_date) && !empty($get_end_date) && !empty($get_affilate_user_id)) {
            $get_custom_orders = $wpdb->get_results("SELECT * FROM $get_table_name WHERE created_at BETWEEN  '$get_start_date' AND '$get_end_date' AND affilate_user_id =
            '$get_affilate_user_id' ", ARRAY_A);
        } elseif (!empty($get_start_date) && !empty($get_end_date) && empty($get_affilate_user_id)) {
            $get_custom_orders = $wpdb->get_results("SELECT * FROM $get_table_name WHERE created_at BETWEEN  '$get_start_date' AND '$get_end_date' ", ARRAY_A);
        }

        if (!empty($get_custom_orders)) {
            $cpm_order_exporter_generate_csv_filename = 'dongtraders-custom-orders' . date('Ymd_His') . '-export.csv';
            header('Content-Type: application/csv');
            header('Content-Disposition: attachment; filename={$cpm_comment_exporter_generate_csv_filename}');
            $output = fopen('php://output', 'w');

            fputcsv($output, ['csv_id', 'customer_email', 'billing_first_name', 'billing_last_name', 'billing_phone', 'billing_address_1', 'billing_postcode', 'billing_city', 'billing_state', 'billing_country', 'product_id', 'variation_id', 'affilate_user_id']);

            foreach ($get_custom_orders as $get_custom_order) {
                $export_id = $get_custom_order['id'];
                $export_customer_email = $get_custom_order['customer_email'];
                $export_customer_first_name = $get_custom_order['customer_first_name'];
                $export_customer_last_name = $get_custom_order['customer_last_name'];
                $export_customer_phone = $get_custom_order['customer_phone'];
                $export_customer_address = $get_custom_order['customer_address'];
                $export_customer_country = $get_custom_order['customer_country'];
                $export_customer_state = $get_custom_order['customer_state'];
                $export_customer_postcode = $get_custom_order['customer_postcode'];
                $export_customer_city = $get_custom_order['customer_city'];
                $export_product_id = $get_custom_order['product_id'];
                $export_product_varition_id = $get_custom_order['product_varition_id'];
                $export_affilate_user_id = $get_custom_order['affilate_user_id'];

                fputcsv($output, [$export_id, $export_customer_email, $export_customer_first_name, $export_customer_last_name, $export_customer_phone, $export_customer_address, $export_customer_postcode, $export_customer_city, $export_customer_state, $export_customer_country, $export_product_id, $export_product_varition_id, $export_affilate_user_id]);
            }
            fclose($output);
        }

        die();
    }

    add_action('wp_ajax_dong_custom_order_exporter_csv_files', 'dong_custom_order_exporter_csv_files');
}

/* show current user added affilate order and exporter */

function dongtraders_show_user_affilate_order()
{
    global $wpdb;
    $order_table_name = $wpdb->prefix . 'dong_order_export_table';
    $user_ID = get_current_user_id();

    ?>
    <div class="cpm-table-wrap">
        <div class="export-section">

            <form action="" id="export-csv-order">
                <div class=" export-date export-date-from">
                    <span id="from">From</span>
                    <input id="start-month" name="start_month" type="date" size="2" required>
                </div>
                <div class="export-date export-date-to">
                    <span id="to">To</span>
                    <input id="end-month" name="end_month" type="date" size="2" required>
                </div>

                <input id="end-month" name="affilate_id" type="hidden" size="2" value="<?php echo $user_ID; ?>">
                <button type="submit" class="button button-primary buttonload">Export CSV<i class="fa fa-spinner fa-spin export-loader"></i></button>
            </form>
        </div>
        <table id="my-account-affilate-order">
            <thead>
                <tr>
                    <th>id</th>
                    <th>Date</th>
                    <th>Email</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Phone No.</th>
                    <th>Country</th>
                    <th>State</th>
                    <th>Address</th>
                    <th>City</th>
                    <th>Postcode</th>
                    <th>Product Id</th>
                    <th>Variation Id</th>
                    <th>Affilate User</th>
                    <th>Remove</th>

                </tr>
            </thead>
            <tbody>
                <?php

    $get_order_results = $wpdb->get_results("SELECT *  FROM $order_table_name WHERE affilate_user_id = '$user_ID' ORDER BY id DESC;");
    //$get_url = home_url() . '/wp-admin/admin.php?page=dongtrader_api_settings';
    $current_page = home_url($_SERVER['REQUEST_URI']);
    if (!empty($get_order_results)) {
        foreach ($get_order_results as $export_order) {

            echo '
                     <tr>
                    <td>' . $export_order->id . '</td>
                     <td>' . $export_order->created_at . '</td>
                   <td>' . $export_order->customer_email . '</td>
                   <td>' . $export_order->customer_first_name . '</td>
                   <td>' . $export_order->customer_last_name . '</td>
                   <td>' . $export_order->customer_phone . '</td>
                   <td>' . $export_order->customer_address . '</td>
                   <td>' . $export_order->customer_country . '</td>
                   <td>' . $export_order->customer_state . '</td>
                   <td>' . $export_order->customer_city . '</td>
                   <td>' . $export_order->customer_postcode . '</td>
                   <td>' . $export_order->product_id . '</td>
                   <td>' . $export_order->product_varition_id . '</td>
                   <td>' . $export_order->affilate_user_id . '</td>
                 <td>
                 <form action="" method="post">
                        <input type="hidden" name="export-id" value="' . $export_order->id . '">
                       <button type="submit" name="delete-export" value="Delete" class="cpm-btn export-delete dashicons-before dashicons-trash"></button>
                        </form>
                        </td>
                </tr>
                ';
        }
    } else {
        echo '<div class="error-box">No Records Found</div>';
    }

    // Handle delete
    if (isset($_POST['delete-export'])) {

        $delete_id = (int) $_POST['export-id'];

        // Delete data in mysql from row that has this id
        $result = $wpdb->delete($order_table_name, array('id' => $delete_id));

        // if successfully deleted
        if ($result) {

            echo '<div class="success-box">Deleted order ID-> ' . $delete_id . ' Successfully</div>';
        } else {
            echo '<div class="error-box">Order Data could not Deleted ! Please Try again</div>';
        }
        wp_redirect($current_page);
    }
    ?>


            </tbody>
        </table>
    </div>

    <?php
}

// set quantity product for yam sticker as 10 quantity

// Displaying quantity setting fields on admin product pages
add_action('woocommerce_product_options_pricing', 'wc_qty_add_product_field');
function wc_qty_add_product_field()
{
    global $product_object;

    $values = $product_object->get_meta('_qty_args');

    echo '</div><div class="options_group quantity hide_if_grouped">
    <style>div.qty-args.hidden { display:none; }</style>';

    woocommerce_wp_checkbox(array( // Checkbox.
        'id' => 'qty_args',
        'label' => __('Quantity settings', 'woocommerce'),
        'value' => empty($values) ? 'no' : 'yes',
        'description' => __('Enable this to show and enable the additional quantity setting fields.', 'woocommerce'),
    ));

    echo '<div class="qty-args hidden">';

    woocommerce_wp_text_input(array(
        'id' => 'qty_min',
        'type' => 'number',
        'label' => __('Set Quantity', 'woocommerce-max-quantity'),
        'placeholder' => '',
        'desc_tip' => 'true',
        'description' => __('Set a minimum allowed quantity limit (a number greater than 0).', 'woocommerce'),
        'custom_attributes' => array('step' => 'any', 'min' => '0'),
        'value' => isset($values['qty_min']) && $values['qty_min'] > 0 ? (int) $values['qty_min'] : 10,
    ));

    echo '</div>';
}

// Show/hide setting fields (admin product pages)
add_action('admin_footer', 'product_type_selector_filter_callback');
function product_type_selector_filter_callback()
{
    global $pagenow, $post_type;

    if (in_array($pagenow, array('post-new.php', 'post.php')) && $post_type === 'product'):
    ?>
        <script>
            jQuery(function($) {
                if ($('input#qty_args').is(':checked') && $('div.qty-args').hasClass('hidden')) {
                    $('div.qty-args').removeClass('hidden')
                }
                $('input#qty_args').click(function() {
                    if ($(this).is(':checked') && $('div.qty-args').hasClass('hidden')) {
                        $('div.qty-args').removeClass('hidden');
                    } else if (!$(this).is(':checked') && !$('div.qty-args').hasClass('hidden')) {
                        $('div.qty-args').addClass('hidden');
                    }
                });
            });
        </script>
<?php
endif;
}

// Save quantity setting fields values
add_action('woocommerce_admin_process_product_object', 'wc_save_product_quantity_settings');
function wc_save_product_quantity_settings($product)
{
    if (isset($_POST['qty_args'])) {
        $values = $product->get_meta('_qty_args');

        $product->update_meta_data('_qty_args', array(
            'qty_min' => isset($_POST['qty_min']) && $_POST['qty_min'] > 0 ? (int) wc_clean($_POST['qty_min']) : 0,
            // 'qty_max' => isset($_POST['qty_max']) && $_POST['qty_max'] > 0 ? (int) wc_clean($_POST['qty_max']) : -1,
            // 'qty_step' => isset($_POST['qty_step']) && $_POST['qty_step'] > 1 ? (int) wc_clean($_POST['qty_step']) : 1,
        ));
    } else {
        $product->update_meta_data('_qty_args', array());
    }
}

// The quantity settings in action on front end
add_filter('woocommerce_quantity_input_args', 'filter_wc_quantity_input_args', 99, 2);
function filter_wc_quantity_input_args($args, $product)
{
    if ($product->is_type('variation')) {
        $parent_product = wc_get_product($product->get_parent_id());
        $values = $parent_product->get_meta('_qty_args');
    } else {
        $values = $product->get_meta('_qty_args');
    }

    if (!empty($values)) {
        // Min value
        if (isset($values['qty_min']) && $values['qty_min'] > 1) {
            $args['min_value'] = $values['qty_min'];

            if (!is_cart()) {
                $args['input_value'] = $values['qty_min']; // Starting value
            }
        }
    }
    return $args;
}

// Ajax add to cart, set "min quantity" as quantity on shop and archives pages
add_filter('woocommerce_loop_add_to_cart_args', 'filter_loop_add_to_cart_quantity_arg', 10, 2);
function filter_loop_add_to_cart_quantity_arg($args, $product)
{
    $values = $product->get_meta('_qty_args');

    if (!empty($values)) {
        // Min value
        if (isset($values['qty_min']) && $values['qty_min'] > 1) {
            $args['quantity'] = $values['qty_min'];
        }
    }
    return $args;
}

// The quantity settings in action on front end (For variable productsand their variations)
add_filter('woocommerce_available_variation', 'filter_wc_available_variation_price_html', 10, 3);
function filter_wc_available_variation_price_html($data, $product, $variation)
{
    $values = $product->get_meta('_qty_args');

    if (!empty($values)) {
        if (isset($values['qty_min']) && $values['qty_min'] > 1) {
            $data['min_qty'] = $values['qty_min'];
        }
    }

    return $data;
}
function dongtraders_set_product_quantity($product_id)
{
    $get_qty = get_post_meta($product_id, '_qty_args', true);
    if (!empty($get_qty)) {
        $get_qty_no = $get_qty['qty_min'];
        return $get_qty_no;
    }
    //return 0;
}

function dongtrader_release_funds_tablelist()
{
    global $wpdb;

    $release_fund = $wpdb->prefix . 'release_groups_profit';
    $group_table = $wpdb->prefix . 'mega_mlm_groups';

    // Filter variables
    $filter_type = isset($_GET['filter_type']) ? sanitize_text_field($_GET['filter_type']) : '';
    $group_name = isset($_GET['group_name']) ? sanitize_text_field($_GET['group_name']) : '';
    $start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '';
    $end_date = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : '';

    // Build the SQL query with filter conditions
    $release_fund_prepared_query = "SELECT * FROM $release_fund";

    if ($filter_type === 'date-range' && $start_date && $end_date) {
        $release_fund_prepared_query .= $wpdb->prepare(" WHERE release_date BETWEEN %s AND %s", $start_date, $end_date);
    } elseif ($filter_type === 'group-name' && $group_name) {
        $release_fund_prepared_query .= $wpdb->prepare(" WHERE group_id IN (SELECT group_id FROM $group_table WHERE circle_name LIKE %s)", "%{$group_name}%");
    } elseif ($filter_type === 'all') {
        // No specific filter selected, return all data
    }

    $release_fund_prepared_query .= " ORDER BY release_date DESC";

    // Get results from the SQL query
    $release_fund_results = $wpdb->get_results($release_fund_prepared_query, ARRAY_A);

    $items_per_page = 10; // Number of items to display per page
    $paged = isset($_GET['rfapaged']) ? (int) $_GET['rfapaged'] : 1; // Get current page number


    ?>
    <h3>Disaster Relief Funds List</h3>
    <form method="get" action="<?php echo admin_url('admin.php'); ?>" class="filter-form" id="posts-filter">
        <input type="hidden" name="page" value="dongtrader_api_settings">
        <div class="tablenav top">
        <div class="post_filter">
            <select name="filter_type" id="filter-type">
                <option value="all" <?php selected($filter_type, 'all');?>>All</option>
                <option value="group-name" <?php selected($filter_type, 'group-name');?>>By Group Name</option>
                <option value="date-range" <?php selected($filter_type, 'date-range');?>>By Date Range</option>
            </select>
            <div id="group-name-filter" class="filter-field" <?php if ($filter_type !== 'group-name') {?>style="display: none;"<?php }?>>
                <input type="text" name="group_name" id="group-name-input" placeholder="Enter group name" value="<?php echo $group_name; ?>">
            </div>
            <div id="date-range-filter" class="filter-field" <?php if ($filter_type !== 'date-range') {?>style="display: none;"<?php }?>>
                <input type="date" name="start_date" id="start-date-input" placeholder="Start date" value="<?php echo $start_date; ?>">
                <input type="date" name="end_date" id="end-date-input" placeholder="End date" value="<?php echo $end_date; ?>">
            </div>
            <button type="submit" id="apply-filter">Apply Filter</button>
            <button type="reset" id="reset-filter">Reset Filter</button>
        </div>
        </div>
    </form>
    <div class="cpm-table-wrap">
        <table>
            <thead>
                <tr>
                    <th>S.N.</th>
                    <th>Released Date</th>
                    <th>Group Name</th>
                    <th>Released Amount</th>
                    <th>Release Cause</th>
                    <th>Remove</th>
                </tr>
            </thead>
            <tbody>
                <?php
if (!empty($release_fund_results)) {
        $symbol = get_woocommerce_currency_symbol();
        // Split array into chunks based on items per page
        $paginated_release_fund_results = array_chunk($release_fund_results, $items_per_page);

        $current_items = $paginated_release_fund_results[$paged - 1]; // Get the items for the current page
        $i=1;
        foreach ($current_items as $gr) {

            $group_name = $wpdb->get_var($wpdb->prepare("SELECT circle_name FROM $group_table WHERE group_id = %d", (int) $gr['group_id']));
            ?>
                        <tr>
                            <td><?php echo $i; ?></td>
                            <td><?php echo $gr['release_date'] ?></td>
                            <td><?php echo $group_name ?></td>
                            <td><?php echo $symbol.$gr['release_amount'] ?></td>
                            <td><?php echo $gr['release_note'] ?></td>
                            <td><button class="rf-del" data-rfid="<?php echo $gr['id'] ?>">Delete</td>
                        </tr>
                    <?php $i++;}?>
                <?php } else {?>
                    <tr>
                        <td style="text-align:center;" colspan="4">Details Not Found</td>
                    </tr>
                <?php }?>
            </tbody>
        </table>
    </div>

    <?php
if (!empty($release_fund_results)) {
        // Pagination links
        echo '<div class="pagination" style="float:right">';
        echo paginate_links(array(
            'base' => add_query_arg('rfapaged', '%#%'), // Base URL with page number placeholder
            'format' => '&rfapaged=%#%', // Add the paged parameter to the URL
            'prev_text' => __('&laquo; Previous', 'text-domain'),
            'next_text' => __('Next &raquo;', 'text-domain'),
            'total' => count($paginated_release_fund_results), // Total number of pages
            'current' => $paged, // Current page number
        ));
        echo '</div>';
    }
    ?>
    <script>
       jQuery(document).ready(function($) {
    // Show/hide filter fields based on selected filter type
    $('#filter-type').on('change', function() {
        var filterType = $(this).val();
        $('.filter-field').hide();
        $('#' + filterType + '-filter').val('');
        $('#' + filterType + '-filter').show();
    });

    // Reset filter form
    $('#reset-filter').on('click', function() {
        $('.filter-form')[0].reset();
        $('.filter-field').hide(); // Hide all filter fields
        $('#filter-type').trigger('change'); // Trigger change event to show/hide fields based on selected filter type

        var url = removeURLParameter(window.location.href, 'filter_type');
        url = removeURLParameter(url, 'group_name');
        url = removeURLParameter(url, 'start_date');
        url = removeURLParameter(url, 'end_date');

        window.history.replaceState({}, document.title, url);

        window.location.reload();
    });

    // On page load, trigger change event to show/hide fields based on selected filter type
    $('#filter-type').trigger('change');

    // Helper function to remove a parameter from the URL
    function removeURLParameter(url, parameter) {
    var urlParts = url.split('?');
    if (urlParts.length >= 2) {
        var prefix = encodeURIComponent(parameter) + '=';
        var params = urlParts[1].split(/[&;]/g);

        for (var i = params.length - 1; i >= 0; i--) {
            if (params[i].lastIndexOf(prefix, 0) !== -1) {
                params.splice(i, 1);
            }
        }

        url = urlParts[0] + (params.length > 0 ? '?' + params.join('&') : '');
        url = url.replace(/[?&]$/, ''); // Remove trailing ? or & if present
    }
    return url;
}
});

    </script>
    <?php
}

add_action('wp_ajax_dongtrader_delete_funds', 'dongtrader_delete_funds');
function dongtrader_delete_funds() {
    $row_id = isset($_POST['rowid']) ? absint($_POST['rowid']) : 0;

    if ($row_id != 0) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'release_groups_profit';

        // Delete the row with the specified ID
        $result = $wpdb->delete($table_name, array('id' => $row_id), array('%d'));

        if ($result !== false) {
            wp_send_json_success('Row with ID ' . $row_id . ' has been deleted.');
        } else {
            wp_send_json_error('Failed to delete the row.');
        }
    } else {
        wp_send_json_error('Invalid row ID.');
    }
}


function dongtrader_compare_released_funds($group_id, $current_releasing_amount){

    global $wpdb;

    $customers_table = $wpdb->prefix.'mega_mlm_customers';

    $rfund_table = $wpdb->prefix . 'release_groups_profit';


    $total_release_amount = $wpdb->get_var($wpdb->prepare(
        "SELECT SUM(release_amount) AS total_release_amount 
          FROM $rfund_table
          WHERE group_id = %d", (int) $group_id
    ));

    $current_amount = $total_release_amount + $current_releasing_amount;

    $all_gr_user = $wpdb->prepare("SELECT user_id FROM $customers_table WHERE customer_group_id=%d", (int) $group_id);

    $users =  $wpdb->get_results($all_gr_user,ARRAY_A);

    $users_arrays = array_column($users,'user_id');

    $total_group_profit_sum = NULL;

    foreach($users_arrays as $ua){
        
        $group_prof_metas =  get_user_meta($ua,'_group_details', true);

        if(empty($group_prof_metas)) continue;
        
        foreach($group_prof_metas as $gpm){

            $total_group_profit_sum += $gpm['profit_amount'];
           
        }
    }

    return array(
        'compare' => $total_group_profit_sum >= $current_amount,
        'total_profit'=> $total_group_profit_sum,
        'total_released'=>$total_release_amount,
        'total_releaseable' => $total_group_profit_sum-$total_release_amount
    );

    
}


add_action('wp_ajax_dongtrader_release_funds', 'dongtrader_release_funds');
function dongtrader_release_funds()
{

    global $wpdb;

    $table_name = $wpdb->prefix . 'release_groups_profit';

    $customers_table = $wpdb->prefix.'mega_mlm_groups';

    $form_data = $_POST['formdatas'];

    parse_str($form_data, $form_array);

    $rfund_group = intval($form_array['rfund-group']);

    $rfund_note = sanitize_text_field($form_array['rfund-note']);

    $rfund_amount = intval($form_array['rfund-amount']);

    if (empty($rfund_group) || empty($rfund_note) || empty($rfund_amount)) {
        wp_send_json_error('Please fill in all fields');
        return;
    }

    $compare = dongtrader_compare_released_funds($rfund_group , $rfund_amount);

    if($compare['compare']){

        $data = array(
            'release_date' => date('Y-m-d H:i:s'),
            'release_amount' => $rfund_amount,
            'release_note' => $rfund_note,
            'group_id' => $rfund_group,
        );
    
        
    
        $result = $wpdb->insert($table_name, $data);
    
        // Check if data insertion was successful
        if ($result === false) {
            wp_send_json_error('Some error occured.Please Try again');
            return;
        }
    
        wp_send_json_success('Group profit has been released successfully');

    }else{
        
        wp_send_json_error('The released amount must not exceed '.$compare['total_releaseable'] );
        return;
    }


}


function dongtrader_patron_form( $atts ) {

    // Attributes
    $atts = shortcode_atts(
        array(
        ),
        $atts,
        'patron_form'
    );
    $error_message = ' <span class="error-message"></span>';
    ob_start(); // Start output buffering

    ?>
    <style>
        /* Basic form styling */
        #mega-patron-credentials {
           
            margin: 0 auto;
            padding: 20px;
           
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        
        .form-group {
          
            border-radius: 5px;
            padding: 20px;
            background-color: #f9f9f9;
            margin: 20px auto;
            width: 80%;
        }

        .group-heading {
            font-size: 1.5rem;
            margin-bottom: 15px;
            text-align: center;
        }
        .error-message::after{
            content: "\A"; 
            white-space: pre;
        }
        
        .error-message{
            color: #d9534f;
        }
        
        .error-message{
            color: #d9534f;
        }
        
        .server-message{
            color: #d9534f;
        }
        label {
            font-weight: bold;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="password"]
        {
            width: 100%;
            padding: 10px;
            margin-bottom: 8px;
            border-radius: 3px;
        }

        input[type="submit"] {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        .toggle-password {
            position: relative;
            float: right;
            cursor: pointer;
            margin-right: 1px;
            margin-top: -40px;
            font-size: 15px;
        }
           
        .flex-container {
            display: flex;
            justify-content: space-between;
        }
 
   
        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        p {
            margin-top: 5px;
            margin-bottom: 15px;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
    <form action="" method="post" id= "mega-patron-credentials" autocomplete="off">
        <div class="form-group">
            <p class="group-heading"><b>Patron Complete Credentials</b></p>
            <p>
                Underlined links will require Patron applicants to submit these form fields with valid platform ID credentials or access. Individual accounts only.
            
            </p>
            <p>
                <b>All fields with * are mandatory.</b>
            </p>
            <div class="mega-notices"></div>
            <div class="flex-container">
                <div class="input-wrapper">
                    <label for="mega-email">Email*:</label>
                    <input type="text" id="mega-email" name="mega-email" placeholder="<?php _e('Enter Your Email Address', 'cpm-dongtrader') ?>" required >
                    <?php echo $error_message?>
                </div>
                <div class="input-wrapper">
                    <label for="mega-name">User Name*:</label>
                    <input type="text" id="mega-name" name="mega-name" placeholder="<?php _e('Enter Your Username', 'cpm-dongtrader') ?>"  required >
                    <?php echo $error_message?>
                </div>
            </div>
            <div class="flex-container">
                <div class="input-wrapper">
                    <label for="user_pass">Password*:</label>
                    <input type="password" id="mega-password" name="mega-password"  minlength="7"  placeholder="<?php _e('Enter Your Password', 'cpm-dongtrader') ?>" required >
                    <i class="toggle-password fa fa-fw fa-eye-slash"></i>
                    <?php echo $error_message?>
                </div>
                <div class="input-wrapper">
                    <label for="user_pass">Confirm Password*:</label>
                    <input type="password" id="mega-confirm-password" name="mega-confirm-password"  minlength="7"  placeholder="<?php _e('Retype Your Password', 'cpm-dongtrader') ?>" required >
                    <i class="toggle-password fa fa-fw fa-eye-slash"></i>
                    <?php echo $error_message?>
                    
                </div>
            </div>
            <label for="mega-mobile">Mobile number*:</label>
            <input type="tel" id="mega-mobile" name="mega-mobile" placeholder="<?php _e('Enter your telephone number', 'cpm-dongtrader') ?>">
            <?php echo $error_message?>
            
            <label for="mega-v-card">V Card*:</label>
            <input  type="text" id="mega-v-card" name="mega-v-card" placeholder="<?php _e('Enter your V-card URL', 'cpm-dongtrader') ?>" >
            <?php echo $error_message?>
            <p><a href="https://www.qrcode-tiger.com/payment" target="_blank">QR Tiger v-card with social media links (Free account)</a></p>
         
            
            <label for="mega-paypal">Paypal:</label>
            <input type="text" id="mega-paypal" name="mega-paypal" placeholder="<?php _e('Enter your paypal Id', 'cpm-dongtrader'); ?>" >
            <?php echo $error_message?>
            <p><a href="https://www.paypal.com/us/welcome/signup/#/login_info"target="_blank">PayPal with banking links</a></p>
            
            
            <label for="mega-venmo">Venmo:</label>
            <input type="text" id="mega-venmo" name="mega-venmo" placeholder="<?php _e('Enter your venmo Id', 'cpm-dongtrader'); ?>" >
            <?php echo $error_message?>
            <p><a href="https://venmo.com/signup/"target="_blank">Venmo with banking links</a></p>
           
            
            <label for="mega-glassfrog">Glassfrog Profile:</label>
            <input type="text" id="mega-glassfrog" name="mega-glassfrog" placeholder="<?php _e('Enter your glassfrog user id', 'cpm-dongtrader'); ?>">
            <?php echo $error_message?>
            <p><a href="https://app.glassfrog.com/accounts/new" target="_blank">Glassfrog (Free account)</a></p>
            
            
            <label for="mega-crowdsignal">Crowdsignal:</label>
            <input type="text" id="mega-crowdsignal" name="mega-crowdsignal" placeholder="<?php _e('Enter your crowdsignal id', 'cpm-dongtrader'); ?>" >
            <?php echo $error_message?>
            <p><a href="https://crowdsignal.com/pricing/" target="_blank">Crowdsignal (Free account)</a></p>
            
        </div>
        <!-- Grouped Form: Patron Organizing Communities (POC) Leadership -->
        <div class="form-group">
            <p class="group-heading"><b>Patron Organizing Communities (POC) Leadership</b></p>
                <label for="mega-precoro">Precoro.com:</label>
                <input type="text" id="mega-precoro" name="mega-precoro" placeholder="<?php _e('Enter your precoro details', 'cpm-dongtrader'); ?>">
                <p><a href="https://precoro.com/get-a-trial" target="_blank">Precoro.com (Get a Trial)</a></p>
                
    
                <label for="mega-amazon-business">Amazon Business:</label>
                <input type="text" id="mega-amazon-business" name="mega-amazon-business" placeholder="<?php _e('Enter your amazon business id', 'cpm-dongtrader'); ?>">
                <p><a href="https://business.amazon.com/en/find-solutions/punchout" target="_blank">Amazon Business (Punchout)</a></p>
        </div>
        <div class="form-group">
            <input type="submit" value="Submit">
            <div id="loader" class="wp-admin-loading" style="display: none;">
                <img src="<?php echo admin_url('images/loading.gif')?>" alt="loading">
            </div>
            <div class="server-message">
            </div>
        </div>

    </form>
    <script>
        jQuery(document).ready(function($) {
            $(".toggle-password").click(function() {
                $(this).toggleClass("fa-eye fa-eye-slash");
                input = $(this).parent().find("input");
                if (input.attr("type") == "password") {
                    input.attr("type", "text");
                } else {
                    input.attr("type", "password");
                }
            });
            $('#mega-patron-credentials').submit(function(event) {
                event.preventDefault(); 
                var loader = $('#loader');
                var form = $('#mega-patron-credentials');
                loader.show();
                $(this).find('input').each(function() {
                    var inputId = $(this).attr('id');
                    $('#'+ inputId).css('border-color', '');
                   
                });
                var formData = $(this).serialize();
                $.ajax({
                    url:  '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                    type: 'POST',
                    data: {
                        action : 'mega_credentials_save',
                        formdata:formData,
                    },
                    success: function(response) {
                        var parsed = JSON.parse(response);
                        console.log(parsed);
                        if ('errorClient' in parsed && parsed['errorClient'].length>0) {
                            parsed.errorClient.forEach(function(error) {
                                var $errorField = $('#' + error.field_name);
                                console.log($errorField);
                                if ($errorField.length > 0) {
                                    $('html, body').animate({
                                        scrollTop: $errorField.offset().top - 100 
                                    }, 1000); 
                                    $errorField.focus();
                                    $errorField.css('border-color', '#d9534f');
                                    $errorField.next('.error-message').text(error.message);
                                    console.log(error.message);
                                }
                            });
                               
                        }else if('errorServer' in parsed && parsed['errorServer'].length>0) {
                            parsed.errorServer.forEach(function(error) {
                               var serverErrorfield =  $('#server-message');
                               serverErrorfield.focus();
                               serverErrorfield.text(error.message);
                                
                            });
                        }else if('valid' in parsed && parsed['valid']){
                          form.empty();
                          form.text('Congartulations!! Now you are member of Megavoters');
                        }
                        
                       loader.hide();
                      
                    },

                    error: function() {
                        console.error('Form submission failed');
                        loader.hide();
                      
                    }
                });
            });
        });


    </script>
    <?php

    return ob_get_clean(); 
}
add_shortcode( 'patron_form', 'dongtrader_patron_form' );




add_action( 'wp_ajax_mega_credentials_save', 'mega_credentials_save' );
add_action( 'wp_ajax_nopriv_mega_credentials_save', 'mega_credentials_save' );

function mega_credentials_save(){
    
    if(!isset($_POST['formdata'])) return;
    
    parse_str($_POST['formdata'], $form_data_array);
    
    $sanitization_mapping = array(
        'mega_email'    =>'sanitize_email',
        'mega_name'     =>'sanitize_text_field',
        'mega_password' =>'sanitize_text_field',
        'mega-confirm-password'=> 'sanitize_text_field',
        'mega_mobile'   =>'sanitize_text_field',
        'mega_v-card'   =>'sanitize_text_field',
        'mega_paypal'   =>'sanitize_text_field',
        'mega_venmo'    =>'sanitize_text_field',
        'mega_glassfrog'=>'sanitize_text_field',
        'mega_crowdsignal'  =>'sanitize_text_field',
        'mega_precoro'      =>'sanitize_text_field',
        'mega_amazon-business'=>'sanitize_text_field'
    );

    $sanitized_data = array();
    foreach ($form_data_array as $field => $value) {
        if (isset($sanitization_mapping[$field]) && function_exists($sanitization_mapping[$field])) {
            $sanitized_data[$field] = call_user_func($sanitization_mapping[$field], $value);
        } else {
            $sanitized_data[$field] = $value;
        }
    }
        
   
        
    $client_validation_check = mega_validation_check($sanitized_data);
    
    if(empty($client_validation_check)) {
        $server_validation_check=[];
        $new_user_id = wp_create_user($sanitized_data['mega-name'],$sanitized_data['mega-password'], $sanitized_data['mega-email']);
        
        if(!is_wp_error($new_user_id)){
            
            $u_data = get_userdata( $new_user_id );
            
            $new_meta_array = array_slice($sanitized_data, 4, null, true);
            
            update_user_meta($new_user_id, 'patron_details' , $new_meta_array);
    
            if($u_data){
                $user_name = $u_data->display_name;
                $to = $u_data->user_email;
                $subject = 'Your Account Details';
                $tem_path = CPM_DONGTRADER_PLUGIN_DIR.'template-parts'.DIRECTORY_SEPARATOR.'content-email-welcome.php';
                ob_start();
                if (file_exists($tem_path)) load_template($tem_path,true);
                $message = ob_get_clean();
                $headers = array('Content-Type: text/html; charset=UTF-8');
                wp_mail( $to, $subject, $message, $headers );
            
            }
            if (class_exists('PMPro_Membership_Level')) {
                
                $current_level = pmpro_getMembershipLevelForUser($new_user_id);
                $level_id = 16;

                // If the user doesn't have the desired level, update it.
                if ($current_level->ID != $level_id) {
                    // Update membership level.
                    pmpro_changeMembershipLevel($level_id, $new_user_id);
                }
            }
            
        }else{
        
            $server_validation_check[] = [
                'valid'=>'false',
                'message' => __('User Cannot be created','cpm-dongtrader')
                
            ]; 
        }
        
    }
    
    $validity = !empty($client_validation_check) 
    ? ['errorClient' => $client_validation_check]
    : (!empty($server_validation_check) 
        ? ['errorServer' => $server_validation_check]
        : ['valid' => true]);
    
     echo  wp_json_encode($validity);
        
    wp_die();
}





function mega_validation_check($sanitized_data){
    $validation_check = [];
    
    foreach($sanitized_data as $attr=>$sv){
    
        if($attr == "mega-email"){
            if(email_exists($sv)){
                
                $validation_check[] = [
                    'valid'=> 'false',
                    'field_name' => $attr,
                    'message' => __("This email address is already registered","cpm-dongtrader")
                ];
              break;
                
            }
            if (!filter_var($sv, FILTER_VALIDATE_EMAIL)) {
            
                $validation_check[] = [
                    'valid'=> 'false',
                    'field_name' => $attr,
                    'message' => __("Please Enter Valid Email Address","cpm-dongtrader")
                ];
              break;
            }
            

        }
        
        if($attr == "mega-name" ){
        
            if(username_exists($sv)){
                $validation_check[] = [
                    'valid'=> 'false',
                    'field_name' => $attr,
                    'message' => __("Username already exist","cpm-dongtrader")
                ];
            }
            
            if (!preg_match('/^.{5,}$/', $sv)){
            
                $validation_check[] = [
                    'valid'=> 'false',
                    'field_name' => $attr,
                    'message' => __("Username must be at least 5 characters long.","cpm-dongtrader")
                ];
                
             break;
            } 
            
           
        
        }
        
        if($attr == "mega-password"){
            if (!preg_match('/^(?=.*[A-Z])(?=.*[!@#$%^&*()\-_=+{};:,<.>]).{7,}$/' , $sv)){
                $validation_check[] = [
                    'valid'=> 'false',
                    'field_name' => $attr,
                    'message' => __("Password must be at least 7 characters long and include at least one uppercase letter and one special character.","cpm-dongtrader")
                ];
                break;
            }
            
            
        }
        
        if($attr = "mega-confirm-password"){
        
            if($sanitized_data['mega-password'] != $sanitized_data['mega-confirm-password']){
                $validation_check[] = [
                    'valid'=> 'false',
                    'field_name' => $attr,
                    'message' => __("Passwords doesnt match ","cpm-dongtrader")
                ];
                break;
            }
        }
    }
        
        return $validation_check;
    
}

// Add a custom text area field to user profiles
function add_custom_user_profile_field($user) {


$metas =  get_user_meta($user->ID,'patron_details', true);

if(empty($metas)) return;
    ?>

    <h3><?php _e('Extra User Details', 'cpm-dongtrader'); ?></h3>
    <table class="form-table" role="presentation">
       <tbody>
       <?php foreach($metas as $k=>$v){ 
     
       $label_text = str_replace("mega-", "", $k);
       $capital_label =  ucwords($label_text);
        
       ?>
          <tr class="user-user-login-wrap">
             <th><label for="<?=$k?>"><?=$capital_label?></label></th>
             <td><input type="text" name="<?=$k?>" id="<?=$k?>" value="<?=$v?>" class="regular-text"></td>
          </tr>
        <?php } ?>
       </tbody>
    </table>
    <?php
}
add_action('show_user_profile', 'add_custom_user_profile_field');
add_action('edit_user_profile', 'add_custom_user_profile_field');

// Save the custom field data
function save_custom_user_profile_field($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }
    $metas =  get_user_meta($user_id,'patron_details', true);
    $array_keys = array_keys($metas);
    
    foreach($array_keys as $k){
        update_user_meta($user_id, $k, sanitize_textarea_field($_POST[$k]));
    }
    
}
add_action('personal_options_update', 'save_custom_user_profile_field');
add_action('edit_user_profile_update', 'save_custom_user_profile_field');
