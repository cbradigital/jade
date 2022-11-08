<?php

add_action('the_content','jade_profile_versions_wrap_content_func');

$jadeJobId = (!empty($_GET) && !empty($_GET["job"])) ? $_GET["job"] : false;


function jade_profile_versions_wrap_content_func($source_content) {
    global $jadeJobId, $wpdb;

    if (!is_page(PAGEID_JADE_PROFILE_VERSIONS)) return $source_content;

    // Check User Id
    if (empty(get_current_user_id())) {
        return '<p style="color: red;"><span class="dashicons dashicons-warning"></span> Kein Benutzer eingeloggt!</p>';
    }

    // Check Job Id
    if (empty($jadeJobId) || $jadeJobId === false) {
        return '<p style="color: red;"><span class="dashicons dashicons-warning"></span> Job Id Missing!</p>';
    }

    $content = '<input type="hidden" id="nonce" name="nonce" value="' . wp_create_nonce('wp_rest') .'">';
    $content.= '<input type="hidden" id="job_id" name="job_id" value="' . $jadeJobId .'">';
    $content.= '<input type="hidden" id="page_analysis" name="page_analysis" value="' . get_home_url() .'/?page_id=' . PAGEID_JADE_ANALYSIS . '">';
    $content.= '<input type="hidden" id="page_profile" name="page_profile_versions" value="' . get_home_url() .'/?page_id=' . PAGEID_JADE_PROFILE . '">';

    $content.= $source_content;


    return $content;
}