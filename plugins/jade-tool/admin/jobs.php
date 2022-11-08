<?php

function jade_page_jobs() {
    global $wpdb;

    $analytics = $wpdb->get_var("SELECT COUNT(*) FROM " . JADE_TABLE_JOBS);
    $jobs = $wpdb->get_results("SELECT job_id, count(*) FROM " . JADE_TABLE_JOBS . " GROUP BY job_id");
    $users = $wpdb->get_results("SELECT user_id, count(*) FROM ". JADE_TABLE_JOBS . " GROUP BY user_id");

    echo "<h1>Welcome to JADE</h1>";

    jade_form_table(array(
        "Jobs" => count($jobs),
        "Analysis" => $analytics,
        "Users" => count($users),
    ), 'small');

    if (current_user_can(JADE_BACKEND_CURRENT_USER_CAN)) {
        $wpnonce = wp_create_nonce('wp_rest');
        echo '<a class="button button-primary" href="/wp-json/jade-tool/v1/job-export?_wpnonce=' . $wpnonce . '" target="_blank">Export Jobs as CSV</a>';
    }
}