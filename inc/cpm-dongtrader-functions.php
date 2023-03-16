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


/*Create database tables where we can save api response details*/
function dongtrader_create_dbtable()
{

    global $wpdb;
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

    /* create table to store custom order */
    $order_table_name = $wpdb->prefix . 'dong_order_export_table';
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
}
add_action('plugins_loaded', 'dongtrader_create_dbtable');


/**
 * Step 1 : Create a database table where we can save circles each circle will have 5 members each(https://i.imgur.com/i5dECgu.png).
 * Step 2 : Save user details from response received from glassfrog api to the above table
 * Step 3 : Create meta on products for variations and simple products so that user can be assigned to specific roles  
 */
function dongtrader_user_registration_hook($customer_id, $p_id, $oid)
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
        $in_circle = 0;
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
                        'post_type'      => 'product',
                        'posts_per_page' => -1,
                        'status' => 'published'
                    );

                    $loop = new WP_Query($args);
                    //var_dump($loop);
                    while ($loop->have_posts()) : $loop->the_post();
                        $product = new WC_Product_Variable(get_the_ID());
                        $terms = get_the_terms($product->get_id(), 'product_type');
                        $product_type = (!empty($terms)) ? sanitize_title(current($terms)->name) : 'simple';
                        echo '<option data-productType="' . $product_type . '" value="' . get_the_ID() . '">' . get_the_title() . '</option>';
                    endwhile;

                    wp_reset_query();

                    ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="">Select Variation Product</label>
            <div class="form-control-wrap">
                <?php

                $product = wc_get_product(1521);
                $current_products = $product->get_children();
                //var_dump($current_products);


                ?>
                <select name="variation-product" id="" class="form-control variation-product" required="">
                    <?php
                    echo '<option value="">Select Variation Product</option>';
                    foreach ($current_products as $current_product) {
                        $variation = wc_get_product($current_product);
                        $varition_name = $variation->get_name();
                        echo '<option value="' . $current_product . '">' . $varition_name . '</option>';
                    }

                    ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <?php $user_ID = get_current_user_id(); ?>
            <div class="form-control-wrap">
                <input type="hidden" name="affilate-user" class="form-control affilate-user" value="<?php echo $user_ID; ?>">
            </div>
        </div>
        <div class="form-group">

            <script language="javascript">
                print_country("country");
                jQuery('#country').val('USA');
                print_state('state', jQuery('#country')[0].selectedIndex);

                jQuery(".select-product").change(function() {
                    var get_p_id = jQuery(this).val();
                    //alert(get_p_id);
                });
            </script>
        </div>


        <div class="form-group">
            <input class="cpm-btn submit real-button" type="submit" value="Add Custom Order" name="set-order_export">
        </div>
    </form>

    <?php
    /* insert date to database table */
    $current_page = '';

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
        $customer_varition_product_id = $_POST['variation-product'];
        $customer_affilate_user = $_POST['affilate-user'];
        $created_date = date("Y-m-d");

        global $wpdb;
        $order_table_name = $wpdb->prefix . 'dong_order_export_table';
        $wpdb->query(
            $order_insert =   $wpdb->prepare(
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
                esc_attr($customer_varition_product_id),
                esc_attr($customer_affilate_user),
                $created_date

            )
        );
        if ($order_insert) {
            echo '<div class="success-box">Order Data inserted Sucessfully. Please Refresh Order Table.</div>';
        } else {
            echo '<div class="error-box">Order Data could not inserted ! Please Try again</div>';
        }
        wp_redirect($current_page);
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

                $get_order_results  = $wpdb->get_results("SELECT *  FROM $order_table_name ORDER BY id DESC;");
                //$get_url = home_url() . '/wp-admin/admin.php?page=dongtrader_api_settings';
                $current_page = admin_url("admin.php?page=" . $_GET["page"]);
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

/*ajax function to run csv exporter*/

if (!function_exists('dong_custom_order_exporter_csv_files')) {
    function dong_custom_order_exporter_csv_files($post)
    {

        $get_start_date = $_POST['start_date'];
        $get_end_date = $_POST['end_date'];
        // echo $get_table_name;

        global $wpdb;
        $get_table_name = $wpdb->prefix . 'dong_order_export_table';
        //$get_custom_orders = $wpdb->get_results("SELECT *  FROM $get_table_name ORDER BYorderC", ARRAY_A);
        $get_custom_orders = $wpdb->get_results("SELECT * FROM $get_table_name WHERE created_at BETWEEN  '$get_start_date' AND '$get_end_date'", ARRAY_A);
        //var_dump($get_custom_orders);
        $cpm_order_exporter_generate_csv_filename =  'dongtraders-custom-orders' . date('Ymd_His') . '-export.csv';
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename={$cpm_comment_exporter_generate_csv_filename}');
        $output = fopen('php://output', 'w');

        fputcsv($output, ['csv_id', 'customer_email',   'billing_first_name', 'billing_last_name', 'billing_phone',   'billing_address_1',   'billing_postcode',   'billing_city', 'billing_state', 'billing_country', 'product_id', 'variation_id',   'affilate_user_id']);

        if (!empty($get_custom_orders)) {
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

                fputcsv($output, [$export_id,  $export_customer_email, $export_customer_first_name, $export_customer_last_name, $export_customer_phone, $export_customer_address, $export_customer_postcode, $export_customer_city, $export_customer_state, $export_customer_country, $export_product_id, $export_product_varition_id, $export_affilate_user_id]);
            }
            fclose($output);
        }



        die();
    }

    add_action('wp_ajax_dong_custom_order_exporter_csv_files', 'dong_custom_order_exporter_csv_files');
}


