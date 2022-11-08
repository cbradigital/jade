<?php

add_action('the_content','jade_profile_wrap_content_func');

function jade_profile_wrap_content_func($source_content) {
    global $jadeJobId, $wpdb;

    if (!is_page(PAGEID_JADE_PROFILE)) return $source_content;

        if (empty(get_current_user_id())) {
            return '<p style="color: red;"><span class="dashicons dashicons-warning"></span> Kein Benutzer eingeloggt!!</p>';
        }

    $content = '<input type="hidden" id="nonce" name="nonce" value="' . wp_create_nonce('wp_rest') .'">';
    $content.= '<input type="hidden" id="page_analysis" name="page_analysis" value="' . get_home_url() .'/?page_id=' . PAGEID_JADE_ANALYSIS . '">';
    $content.= '<input type="hidden" id="page_profile_versions" name="page_profile_versions" value="' . get_home_url() .'/?page_id=' . PAGEID_JADE_PROFILE_VERSIONS . '">';

    $content.= $source_content;

    return $content;
}