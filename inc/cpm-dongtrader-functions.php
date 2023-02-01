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
function glassfrog_api_request($endpoint = '', $bodyParams = array(), $method = "GET")
{
    /* Get the API credentials from the database. */
    $glassfrog_creds = dongtrader_get_api_cred('glassfrog');
    /* Check if the API credentials are empty or not. */
    $checkFields = !empty($glassfrog_creds['glassfrog-api-url']) && !empty($glassfrog_creds['glassfrog-api-key']) ? true : false;
    /* Check if the API credentials are empty or not. */
    if (!$checkFields) return;
    /* Get the API URL from the database. */
    $glassfrog_api_root_url = $glassfrog_creds['glassfrog-api-url'];
    /* Getting the API key from the database. */
    $glassfrog_api_key      = $glassfrog_creds['glassfrog-api-key'];
    /* Concatenating the API root URL with the endpoint. */
    $build_url = $glassfrog_api_root_url . $endpoint;
    /* A default array. */
    $glassfrog_defaults = [
        "qr" => [
            "size" => 500,
            "colorDark" => "rgb(5,64,128)",
            "logo" => "",
            "eye_outer" => "eyeOuter2",
            "eye_inner" => "eyeInner1",
            "qrData" => "pattern0",
            "backgroundColor" => "rgb(255,255,255)",
            "transparentBkg" => false,
            "qrCategory" => "url",
            "text" => "https://www.qrcode-tiger.com.com/"
        ],
        "qrUrl" => "https://www.qrcode-tiger.com.com",
        "qrType" => "qr2",
        "qrCategory" => "url"
    ];

    /* Taking the default array and merging it with the  array. */
    $body = wp_json_encode(wp_parse_args($glassfrog_defaults, $bodyParams));

    /* Setting the options for the request. */
    $options = [
        'body'        => $method == "POST" ? $body : '',
        'headers'     => [
            'X-Auth-Token' => $glassfrog_api_key,
            'Content-Type' => 'application/json',
        ],
        'timeout'     => 30,

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

function dongtrader_ajax_helper($color , $url , $size='500'){
    
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
    $productnum     = $_POST['productnums'];
    $product        = wc_get_product($productnum);
    $checkout       = site_url('/checkout/?add-to-cart=' . $productnum); 
    $product_url    = get_permalink($productnum);
    $resp = array(
        'success' => 'false',
        'initiator' => $intiator,
        'template' => ''
    );
 
//for products qr code
    if($intiator == '_product_qr_codes'){
    
        $current_data = dongtrader_ajax_helper('rgb(51, 51, 255)',$checkout);
        if(!empty($current_data)){
            $update_data = json_encode($current_data);
            update_post_meta($productnum, '_product_qr_codes', $update_data);
            $resp['success'] = true;
            $resp['template'] = '<div id="" class="dong-qr-components">
            <img src="'.$current_data['qr_image_url'].'" alt="" width="200" height="200">
            <button data-url= "'.$current_data['qr_image_url'].'" class="button button-primary button-large url-copy" >Copy QR URL</button>
            <input type= "hidden" data-id="' . esc_attr($productnum) . '"  name ="_product_qr_codes" value="'.esc_attr($update_data).'">
            <button data-meta="_product_qr_codes" data-remove="'.$productnum . '" class="button-primary button-large qr-remover" style="" >Remove</button></div>';
            
         
        }
//for direct checkout
    }elseif($intiator == '_product-qr-direct-checkouts'){
        //call api helper function
        $current_data = dongtrader_ajax_helper('rgb(0, 255, 191)',$checkout);
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
            $get_url = get_permalink($variations);
            $query = parse_url($get_url, PHP_URL_QUERY);
            parse_str($query, $queryArray);
            $get_color = $queryArray['attribute_color'];
            $html = '';
            $current__array = dongtrader_ajax_helper(dongtrader_variable_color_to_rgb_color($get_color),$get_url);
            if($current__array){
                $update_data = json_encode($current__array);
                update_post_meta($variations, 'variable_product_qr_data', esc_attr($update_data));
                $html .= '<div id="dong-qr-components'.$loop.'" class="dong-qr-components dong-qr-components-var">';
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
    exit;
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

