<?php

// Add field
add_action('woocommerce_edit_account_form', 'misha_add_field_edit_account_form');
// or add_action( 'woocommerce_edit_account_form_start', 'misha_add_field_edit_account_form' );
function misha_add_field_edit_account_form()
{

    woocommerce_form_field(
        'phone_number',
        array(
            'type'        => 'tel',
            'required'    => true, // remember, this doesn't make the field required, just adds an "*"
            'label'       => 'Phone number'
        ),
        get_user_meta(get_current_user_id(), 'phone_number', true) // get the data
    );
}
// Save field value
add_action('woocommerce_save_account_details', 'misha_save_account_details');
function misha_save_account_details($user_id)
{

    update_user_meta($user_id, 'phone_number', wc_clean($_POST['phone_number']));
}
// Make it required
add_filter('woocommerce_save_account_details_required_fields', 'misha_make_field_required');
function misha_make_field_required($required_fields)
{

    $required_fields['phone_number'] = 'Phone number';
    return $required_fields;
}
