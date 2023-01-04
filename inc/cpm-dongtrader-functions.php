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
    $qrtiger_defaults = [
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
    $body = wp_json_encode(wp_parse_args($qrtiger_defaults, $bodyParams));

    /* Setting the options for the request. */
    $options = [
        'body'        => $method == "POST" ? $body : '',
        'headers'     => [
            'Authorization' => 'Bearer ' . $qrtiger_api_key,
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
