<?php

function jade_start_wrap_content_func($content) {
    global $jadeOpenNewWindowJobId;

    if (!is_page(PAGEID_JADE_START)) return $content;

    $output = "<!-- AUTOMATED FORM WRAP though JADE PLUGIN -->";
    $output.= '<form id="jade-form" method="POST" target="_blank">';
    $output.= $content;
    $output.= '</form>';
    $output.= '<input type="hidden" id="nonce" name="nonce" value="' . wp_create_nonce('wp_rest') .'">';

    return $output;
}

add_action('the_content','jade_start_wrap_content_func');

function jade_wp_func() {
    global $wpdb, $jadeOpenNewWindowJobId;

    if (!is_page(PAGEID_JADE_START)) return;
    if ($_SERVER['REQUEST_METHOD'] != 'POST') return;

    $customer_data = array();
    $job_data = array();

    foreach ($_POST as $key => $value) {
        if (startsWith($key, 'jade-')) $job_data[$key] = $value;
        if (startsWith($key, 'stats-')) $customer_data[$key] = $value;
    }

    /*
    [jade-title] => test
    [jade-employer] =>
    [jade-jobdescription] =>
    [jade-requirementprofil] =>
    [jade-weoffer] =>
    [stats-business] => volvo
    [stats-companysize] => 1
    [stats-location] =>
    [stats-employmentform] =>
    [stats-professionalfield] => volvo
    [stats-qualificationlevel] => volvo
    [stats-year] => Select Year
    */

    $nextJobId = $wpdb->get_var("SELECT max(job_id) FROM " . JADE_TABLE_JOBS);
    if (empty($nextJobId) || $nextJobId == false) {
        $nextJobId = 1;
    } else {
        $nextJobId++;
    }

    $insert = array(
        'user_id' => get_current_user_id(),
        'job_id'  =>  $nextJobId,
        'customer_data' => json_encode($customer_data),
        'job_data' => json_encode($job_data),
        'analysis_data' => "",
    );

    $result = $wpdb->insert(JADE_TABLE_JOBS, $insert);

    if ($result === false) {
        throw new Exception("Insert New Job DB Error, please contact the administrator!");
    }

    wp_redirect(get_page_link(PAGEID_JADE_ANALYSIS) . "?job=" . $nextJobId);
    exit;
} 

add_action('wp','jade_wp_func');