<?php

// Api call utilities from settings page
function get_api_cred($apiname)
{

    if (!$apiname) return;
    if ($apiname == 'qrtiger') {
        $api_utils = get_option('dongtrader_api_settings_qrtiger');
    } else if ($apiname == 'glassfrog') {
        $api_utils = get_option('dongtrader_api_settings_glassfrog');
    }
    return $api_utils;
}

/* A function that is calling the Qriger API. */

function dongtrader_get_qrtiger_api_data($method = "POST", $databody = array(), $qrid = '')
{

    /* Getting the api credentials from the database. */
    $qrtiger_api_utils          = get_api_cred('qrtiger');
    /* Getting the api key from the database. */
    $qrtiger_api_key            = $qrtiger_api_utils['qrtiger-api-key'];
    /* Getting the api url from the database. */
    $qrtiger_api_root_url       = $qrtiger_api_utils['qrtiger-api-url'];
    /* The endpoint of the API. */
    $qrtiger_api_end_point_url  = $method == "POST" ? '/api/campaign/' : '/data/' . $qrid;
    /* Concatenating the api root url and the api endpoint url. */
    $qrtiger_build_url          = $qrtiger_api_root_url . $qrtiger_api_end_point_url;
    /* Checking if the api key and the api root url are empty. If they are empty, it will return nothing. */
    if (empty($qrtiger_api_key) && empty($qrtiger_api_root_url)) return;
    /* Initializing the curl. */
    $curl = curl_init();
    /* Setting the options for the curl. */
    curl_setopt_array($curl, [
        CURLOPT_URL             => $qrtiger_build_url,
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_ENCODING        => "",
        CURLOPT_MAXREDIRS       => 10,
        CURLOPT_TIMEOUT         => 30,
        CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST   => $method,
        CURLOPT_POSTFIELDS      =>  $method == 'POST' ? json_encode($databody) : false,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer " . $qrtiger_api_key,
            "Content-Type: application/json"
        ],
    ]);

    /* Executing the curl. */
    $response = curl_exec($curl);
    /* Checking if there is an error in the curl. */
    $err = curl_error($curl);
    /* Closing the curl. */
    curl_close($curl);

    return json_decode($response);
}



// add_action('qrtiger_request', 'dongtraders_qrtiger_request', 10, 2);

function dongtraders_qrtiger_request($type, $dataArray = array())
{

    $defaults = [
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
    $parsed_data      = wp_parse_args($dataArray, $defaults);
    $response_data    = dongtrader_get_qrtiger_api_data("POST", $parsed_data);
    return $response_data;
}




add_action('wp_footer', 'testing_guy');

function testing_guy()
{
    $rep = dongtraders_qrtiger_request('GET');
    echo '<pre>';
    //var_dump($rep->data->qrImage);
    echo '</pre>';
}
