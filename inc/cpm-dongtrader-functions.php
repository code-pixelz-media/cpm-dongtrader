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
    $qrtiger_creds = dongtrader_get_api_cred('qrtiger');
    /* Check if the API credentials are empty or not. */
    $checkFields = !empty($qrtiger_creds['qrtiger-api-url']) && !empty($qrtiger_creds['qrtiger-api-key']) ? true : false;
    /* Check if the API credentials are empty or not. */
    if (!$checkFields) return;
    /* Get the API URL from the database. */
    $qrtiger_api_root_url = $qrtiger_creds['qrtiger-api-url'];
    /* Getting the API key from the database. */
    $qrtiger_api_key      = $qrtiger_creds['qrtiger-api-key'];
    /* Concatenating the API root URL with the endpoint. */
    $build_url = $qrtiger_api_root_url . $endpoint;
    /* A default array. */
    // $qrtiger_defaults = [
    //     "qr" => [
    //         "size" => 500,
    //         "colorDark" => "rgb(5,64,128)",
    //         "logo" => "",
    //         "eye_outer" => "eyeOuter2",
    //         "eye_inner" => "eyeInner1",
    //         "qrData" => "pattern0",
    //         "backgroundColor" => "rgb(255,255,255)",
    //         "transparentBkg" => false,
    //         "qrCategory" => "url",
    //         "text" => "https://www.qrcode-tiger.com.com/"
    //     ],
    //     "qrUrl" => "https://www.qrcode-tiger.com.com",
    //     "qrType" => "qr2",
    //     "qrCategory" => "url"
    // ];

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

    return $response_object;
}


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
    $qr_text =  sanitize_text_field($_POST['qrtext']);
    $dong_user_id = get_current_user_id();
    $response = !empty($qr_size) && !empty($qr_url) && !empty(trim($qr_text)) ? true : false;
    $notify_to_js = array(
        'dataStatus' => $response,
        'user' => $dong_user_id,
        'apistatus' => false
    );
    if ($response) {
        $qrtiger_array = [
            "qr" => [
                "size" => $qr_size,
                "text" => $qr_text
            ],
            "qrUrl" => $qr_url,
            "qrType" => "qr2",
            "qrCategory" => "url"
        ];
        $qrtiger_api_call = qrtiger_api_request('/api/campaign/', $qrtiger_array, 'POST');

        if ($qrtiger_api_call) {

            $notify_to_js['apistatus'] = true;

            $prev_user_qr_meta = get_user_meta($dong_user_id, 'dong_user_qr_data', true);
            $prev_qrs = !empty($prev_user_qr_meta) ? $prev_user_qr_meta : array();
            if (!in_array($prev_qrs, $prev_user_qr_meta)) array_push($prev_stories, $dong_user_id);

            $dong_qr_image_url = $qrtiger_api_call->data->qrImage;
            $dong_qr_id       = $qrtiger_api_call->data->qrId;
            $created_at       = $qrtiger_api_call->data->createdAt;
            $updated_at       = $qrtiger_api_call->data->updatedAt;




            // $dong_data_array = [
            //     $dong_qr_id => [
            //         'created_by' => $dong_user_id,
            //         'qr_image_url' => $dong_qr_image_url,
            //         'created_at' => $created_at,
            //         'updated_at' => $qrtiger_api_call->data->updatedAt,
            //         'qr_id'  => $dong_qr_id
            //     ]
            // ];

            // $new_array = array_push($dong_data_array, $dong_data_array_2);

            $prev_user_qr_meta = get_user_meta($dong_user_id, 'dong_user_qr_data');
            $prev_value_check  = !empty($prev_user_qr_meta) ? true : false;

            $dong_data_array = array(
                'created_by'    => $dong_user_id,
                'qr_image_url'  => $dong_qr_image_url,
                'created_at'    => $qrtiger_api_call->data->createdAt,
                'updated_at'    => $qrtiger_api_call->data->updatedAt,
                'qr_id'         => $dong_qr_id
            );
            if ($prev_value_check) {
                $saved_data = $prev_user_qr_meta;
                array_push($saved_data, $dong_data_array);
                update_user_meta($dong_user_id, 'dong_user_qr_data', $saved_data);
            } else {
                update_user_meta($dong_user_id, 'dong_user_qr_data', $dong_data_array);
            }
        }
    }

    echo wp_json_encode($notify_to_js);

    wp_die();
}
