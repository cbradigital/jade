<?php
// https://jade.cbra.digital/wp-json/jade-tool/v1/userdata/

function jade_get_userdata($request) {
    global $wpdb;

    $userId = get_current_user_id();
    // $userId = 19;

    if (empty($userId)) return array("error" => "no user id ... once problem?");

    $user_branche = $wpdb->get_var( "SELECT meta_value FROM b5qm_usermeta WHERE meta_key like 'user_registration_cbra_branche' " .
        "  AND user_id = " . $userId);
    $user_companysize = $wpdb->get_var( "SELECT meta_value FROM b5qm_usermeta WHERE meta_key like 'user_registration_cbra_unternehmen_groesse' " .
        "  AND user_id = " . $userId);

    return array(
        "user_branche" => $user_branche,
        "user_companysize" => $user_companysize
    );
}

add_action( 'rest_api_init', function () {
    //d(wp_create_nonce('wp_rest'));

    register_rest_route( 'jade-tool/v1', '/userdata/', array(
        'methods' => 'GET',
        'callback' => 'jade_get_userdata',
        'permission_callback' => function () { return get_current_user_id() !== 0; }
    ));
});