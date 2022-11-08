<?php
// https://wordpress.org/support/article/roles-and-capabilities/
$jade_capability = JADE_BACKEND_CURRENT_USER_CAN;
$jadeSlug = sanitize_key('jade-main');

function jade_admin_menu() {
    global $jade_capability, $jadeSlug;
  // manage_options allows only Super Admin and Administrator to view plugin
  add_menu_page('JADE', 'JADE', $jade_capability, $jadeSlug, 'jade_page_jobs', 'dashicons-text-page', 3);
  add_submenu_page($jadeSlug, 'Dictionary', 'Dictionary', $jade_capability, 'jade-dict', 'jade_page_dictionary', 1);
}
 
add_action( 'admin_menu', 'jade_admin_menu' );